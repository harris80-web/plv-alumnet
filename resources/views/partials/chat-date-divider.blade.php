{{--
    Dated day separator for the alumni message thread. Rendered wherever
    `dateKey` changes between two consecutive messages; the JS in
    alumni/messages.blade.php builds the same markup for messages that
    arrive after page load — keep both in sync if you change it.

    Params: $label (string, from Message::toChatArray()['dateLabel'])
--}}
<div class="flex items-center gap-3 py-1" data-chat-date-divider>
    <span class="flex-1 h-px bg-gray-200"></span>
    <span class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 shrink-0">{{ $label }}</span>
    <span class="flex-1 h-px bg-gray-200"></span>
</div>
