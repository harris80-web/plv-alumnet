{{--
    Initial server-rendered message bubble. renderBubble() in
    alumni/messages.blade.php builds the same shape client-side for messages
    that arrive via send/poll after the page has loaded — keep both in sync
    if you change the markup.

    Params: $message (array from Message::toChatArray()), $contact (the
    other participant's User model, for their avatar/name), $currentUserId.
--}}
@php $isMine = $message['senderId'] == $currentUserId; @endphp
<div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $message['id'] }}">
    @unless ($isMine)
    <div class="w-8 h-8 rounded-full bg-[#0E0F3B] flex items-center justify-center overflow-hidden shrink-0 mr-2 self-end">
        @if($contact->user_profile_picture)
        <img src="{{ asset('storage/' . $contact->user_profile_picture) }}" class="w-full h-full object-cover">
        @else
        <i class="fas fa-user text-white text-[10px]"></i>
        @endif
    </div>
    @endunless
    <div class="max-w-[60%]">
        @unless ($isMine)
        <p class="text-xs font-bold text-[#0E0F3B] mb-1">{{ $contact->user_first_name }}</p>
        @endunless
        <div class="{{ $isMine ? 'bg-[#1D264F] text-white' : 'bg-gray-100 text-gray-800' }} rounded-2xl px-4 py-2.5 text-sm break-words">
            @if($message['hasAttachment'])
                @if($message['isImage'])
                <img src="{{ $message['attachmentUrl'] }}" class="rounded-lg max-w-full mb-1" style="max-height:200px">
                @else
                <a href="{{ $message['attachmentUrl'] }}" target="_blank" class="flex items-center gap-2 underline">
                    <i class="fas fa-file"></i> {{ $message['attachmentName'] }}
                </a>
                @endif
            @endif
            @if($message['content'])
            <p>{{ $message['content'] }}</p>
            @endif
        </div>
        <p class="text-[10px] text-gray-400 mt-1 {{ $isMine ? 'text-right' : '' }}">{{ $message['timeLabel'] }}</p>
    </div>
</div>
