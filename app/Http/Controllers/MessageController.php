<?php

namespace App\Http\Controllers;

use App\Models\ChatbotSetting;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageFlag;
use App\Services\MessageAuditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    private function authorizeParticipant(Conversation $conversation): void
    {
        abort_unless(Auth::user()->user_role === 'alumni', 403);

        $userId = Auth::id();
        abort_unless(
            $conversation->conversation_user_a === $userId || $conversation->conversation_user_b === $userId,
            403
        );
    }

    /** AJAX send — returns the created message as JSON so the composer can append it without a page reload. */
    public function store(Request $request, Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);

        if (Auth::user()->isMuted()) {
            return response()->json(['error' => 'Your account has been muted from messaging. Contact the alumni office if you believe this is a mistake.'], 403);
        }

        $validated = $request->validate([
            'message_content' => ['nullable', 'string', 'max:4000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,zip'],
        ]);

        if (empty($validated['message_content']) && !$request->hasFile('attachment')) {
            return response()->json(['error' => 'Message cannot be empty.'], 422);
        }

        $senderId = Auth::id();
        $receiver = $conversation->otherUser($senderId);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('messageAttachments', 'public');
        }

        try {
            $message = DB::transaction(function () use ($conversation, $senderId, $receiver, $validated, $request, $attachmentPath) {
                $message = Message::create([
                    'conversation_id' => $conversation->conversation_id,
                    'sender_id' => $senderId,
                    'receiver_id' => $receiver->user_id,
                    'message_content' => $validated['message_content'] ?? '',
                    'message_read' => false,
                    'attachment_path' => $attachmentPath,
                    'attachment_original_name' => $request->hasFile('attachment') ? $request->file('attachment')->getClientOriginalName() : null,
                    'attachment_mime_type' => $request->hasFile('attachment') ? $request->file('attachment')->getMimeType() : null,
                ]);

                $conversation->touch('conversation_last_message_at');

                return $message;
            });
        } catch (\Exception $e) {
            if ($attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }
            return response()->json(['error' => 'Failed to send message.'], 500);
        }

        // Outside the transaction — a flagging hiccup should never roll back
        // a legitimate send. Skipped entirely (no query) when auditing is off.
        if (ChatbotSetting::current()->chat_auditing_enabled && $message->message_content !== '') {
            $reasons = app(MessageAuditor::class)->scan($message->message_content);
            if (!empty($reasons)) {
                MessageFlag::create([
                    'message_id' => $message->message_id,
                    'flag_reasons' => $reasons,
                    'status' => 'pending',
                ]);
            }
        }

        return response()->json($message->toChatArray(), 201);
    }

    /**
     * Polling endpoint — the browser calls this every few seconds with the
     * highest message id it already has and gets back only what's new.
     * Deliberately minimal (no eager-loaded relations, indexed lookup on
     * conversation_id + message_id) so frequent polling across many open
     * conversations stays cheap.
     */
    public function poll(Request $request, Conversation $conversation)
    {
        $this->authorizeParticipant($conversation);

        $afterId = (int) $request->query('after', 0);
        $userId = Auth::id();

        $messages = $conversation->messages()
            ->where('message_id', '>', $afterId)
            ->orderBy('message_id')
            ->get();

        if ($messages->isNotEmpty()) {
            $conversation->messages()
                ->where('receiver_id', $userId)
                ->where('message_read', false)
                ->update(['message_read' => true]);
        }

        return response()->json($messages->map->toChatArray());
    }
}
