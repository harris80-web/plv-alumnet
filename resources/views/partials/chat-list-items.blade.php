{{--
    Initial server-rendered chat list. The polling loop in
    alumni/messages.blade.php replaces this container's contents with a
    JS-built equivalent (renderChatList()) on every sidebar-poll tick — keep
    the two markup shapes in sync if you change one.
--}}
@forelse ($conversations as $conv)
<a href="{{ route('messages.show', $conv['id']) }}" data-name="{{ mb_strtolower($conv['name']) }}"
    class="chat-list-item flex items-center gap-3 px-5 py-3 border-b border-gray-50 hover:bg-slate-50 transition-colors {{ $activeConversationId == $conv['id'] ? 'bg-orange-50' : '' }}">
    <div class="w-11 h-11 rounded-full bg-[#0E0F3B] flex items-center justify-center overflow-hidden shrink-0">
        @if($conv['photo'])
        <img src="{{ $conv['photo'] }}" class="w-full h-full object-cover">
        @else
        <i class="fas fa-user text-white text-sm"></i>
        @endif
    </div>
    <div class="flex-1 min-w-0">
        <div class="flex justify-between items-baseline">
            <p class="font-bold text-[#0E0F3B] text-sm truncate">{{ $conv['name'] }}</p>
            <span class="text-[10px] text-gray-400 shrink-0 ml-2">{{ $conv['time'] }}</span>
        </div>
        <div class="flex justify-between items-center">
            <p class="text-xs {{ $conv['unread'] > 0 && $activeConversationId != $conv['id'] ? 'font-bold text-[#0E0F3B]' : 'text-gray-500' }} truncate">
                {{ $conv['preview'] ?: 'No messages yet' }}
            </p>
            @if($conv['unread'] > 0 && $activeConversationId != $conv['id'])
            <span class="bg-[#C73D1A] text-white text-[9px] font-bold rounded-full w-5 h-5 flex items-center justify-center shrink-0 ml-2">{{ $conv['unread'] }}</span>
            @endif
        </div>
    </div>
</a>
@empty
<p class="text-center text-gray-400 text-sm py-10 px-5">No conversations yet. Click the compose icon to message an alumnus.</p>
@endforelse
