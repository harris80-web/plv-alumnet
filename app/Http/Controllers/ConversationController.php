<?php

namespace App\Http\Controllers;

use App\Models\Alumnus;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ConversationController extends Controller
{
    private function authorizeAlumnus(): void
    {
        abort_unless(Auth::user()->user_role === 'alumni', 403);
    }

    /** Confirms the current user is actually a participant before letting them touch a conversation. */
    private function authorizeParticipant(Conversation $conversation): void
    {
        $userId = Auth::id();
        abort_unless(
            $conversation->conversation_user_a === $userId || $conversation->conversation_user_b === $userId,
            403
        );
    }

    /**
     * Left-sidebar list — one row per conversation with at least one
     * message, newest activity first. Kept to plain arrays (not full
     * models) since this gets rebuilt on every page load and every sidebar
     * poll tick.
     */
    private function sidebarConversations()
    {
        $userId = Auth::id();

        return Conversation::forUser($userId)
            ->withMessages()
            ->with(['userA', 'userB', 'latestMessage'])
            ->get()
            ->sortByDesc(fn (Conversation $c) => $c->latestMessage?->created_at ?? $c->created_at)
            ->values()
            ->map(function (Conversation $conversation) use ($userId) {
                $other = $conversation->otherUser($userId);
                $last = $conversation->latestMessage;

                $preview = '';
                if ($last) {
                    $preview = $last->hasAttachment()
                        ? ($last->isImageAttachment() ? '📷 Photo' : '📎 ' . $last->attachment_original_name)
                        : (string) $last->message_content;
                }

                return [
                    'id' => $conversation->conversation_id,
                    'name' => trim($other->user_first_name . ' ' . $other->user_last_name),
                    'photo' => $other->user_profile_picture ? asset('storage/' . $other->user_profile_picture) : null,
                    'preview' => $preview,
                    'time' => $last ? $last->created_at->format('h:i A') : '',
                    'unread' => $conversation->unreadCountFor($userId),
                ];
            })
            ->values();
    }

    /** Lighter-weight poll for the sidebar (list, not thread contents) — new messages in chats you don't currently have open. */
    public function sidebarPoll()
    {
        $this->authorizeAlumnus();

        return response()->json($this->sidebarConversations());
    }

    /** No conversation selected — the blank "To:" state. */
    public function index()
    {
        $this->authorizeAlumnus();

        return view('alumni.messages', [
            'conversations' => $this->sidebarConversations(),
            'activeConversation' => null,
            'activeContact' => null,
            'messages' => collect(),
        ]);
    }

    public function show(Conversation $conversation)
    {
        $this->authorizeAlumnus();
        $this->authorizeParticipant($conversation);

        $userId = Auth::id();

        // Opening a thread is what "reads" it.
        $conversation->messages()
            ->where('receiver_id', $userId)
            ->where('message_read', false)
            ->update(['message_read' => true]);

        $messages = $conversation->messages()->orderBy('message_id')->get();

        $sharedAttachments = $messages->filter->hasAttachment()->values();

        return view('alumni.messages', [
            'conversations' => $this->sidebarConversations(),
            'activeConversation' => $conversation,
            'activeContact' => $conversation->otherUser($userId),
            'messages' => $messages->map->toChatArray(),
            'sharedAttachments' => $sharedAttachments,
        ]);
    }

    /**
     * "To:" typeahead — small JSON search over alumni with a public profile
     * (same visibility rule as the Alumni Directory), excluding yourself.
     */
    public function searchContacts(Request $request)
    {
        $this->authorizeAlumnus();

        $query = trim((string) $request->input('q'));
        if ($query === '') {
            return response()->json([]);
        }

        $results = Alumnus::with('user')
            ->publicProfiles()
            ->where('user_id', '!=', Auth::id())
            ->whereHas('user', function ($q) use ($query) {
                $q->where('user_first_name', 'like', "%{$query}%")
                    ->orWhere('user_last_name', 'like', "%{$query}%");
            })
            ->limit(8)
            ->get()
            ->map(fn (Alumnus $alumnus) => [
                'id' => $alumnus->user_id,
                'name' => trim($alumnus->user->user_first_name . ' ' . $alumnus->user->user_last_name),
                'photo' => $alumnus->user->user_profile_picture ? asset('storage/' . $alumnus->user->user_profile_picture) : null,
            ]);

        return response()->json($results);
    }

    /** Finds-or-creates the conversation, then hands the alumnus straight to it. */
    public function start(Request $request)
    {
        $this->authorizeAlumnus();

        $validated = $request->validate([
            'recipient_id' => [
                'required',
                'integer',
                Rule::exists('users', 'user_id')->where('user_role', 'alumni'),
            ],
        ]);

        abort_if((int) $validated['recipient_id'] === Auth::id(), 422, 'You cannot message yourself.');

        $conversation = Conversation::findOrCreateBetween(Auth::id(), (int) $validated['recipient_id']);

        return redirect()->route('messages.show', $conversation->conversation_id);
    }
}
