{{--
    ALMA (Alumni Management Assistant): the floating chat widget for alumni and employers.
    Front end only: it talks to the same widget.start / send / poll endpoints as before and
    never changes stored conversations. Images live in public/assets/chatbot (originals) and
    public/assets/chatbot/web (small copies this widget loads).
--}}
@php
    $almaUser = auth()->user();
    // "Hello, [Alumni / Business Name]": an alumnus's first name, an employer's business name.
    $almaName = $almaUser?->user_role === 'employer'
        ? ($almaUser->employer->employer_company_name ?? $almaUser->user_first_name)
        : $almaUser?->user_first_name;
    $almaAsset = fn (string $file) => asset('assets/chatbot/web/' . $file) . '?v=' . @filemtime(public_path('assets/chatbot/web/' . $file));
@endphp

<style>
    #chatWidgetButton,
    #chatWidgetPanel {
        --alma-navy: #0E0F3B;
        --alma-navy-2: #1D264F;
        --alma-blue: #1D46A4;
        --alma-rust: #C73D1A;
        --alma-orange: #ED7A07;
        --alma-ease: cubic-bezier(.16, 1, .3, 1);
        font-family: 'Montserrat', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
    }
    /* Mascot size and spot. The panel and the back-to-top button are placed from these. */
    :root {
        --alma-l-size: 69px;
        --alma-l-right: 24px;
        --alma-l-bottom: 12px;
        --alma-gap: 12px;
    }
    @media (min-width: 1024px) {
        :root { --alma-l-size: 84px; }
    }
    @media (max-width: 639px) {
        :root { --alma-l-size: 64px; --alma-l-right: 16px; --alma-l-bottom: 14px; }
    }
    #chatWidgetButton [hidden],
    #chatWidgetPanel [hidden] { display: none !important; }
    .alma-sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
    }

    /* ---------- Launcher (mascot) ---------- */
    .alma-launcher {
        position: fixed;
        right: var(--alma-l-right);
        bottom: var(--alma-l-bottom);
        z-index: 90;
        width: var(--alma-l-size);
        height: var(--alma-l-size);
        padding: 0;
        border: 0;
        background: transparent;
        cursor: pointer;
        touch-action: none;
        -webkit-tap-highlight-color: transparent;
    }
    .alma-launcher-art {
        position: absolute;
        inset: 0;
        filter: drop-shadow(0 6px 10px rgba(14, 15, 59, .28));
        transition: transform .28s var(--alma-ease), filter .28s ease;
    }
    .alma-launcher-img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        pointer-events: none;
        user-select: none;
        -webkit-user-drag: none;
        transition: opacity .22s ease;
    }
    .alma-launcher-img--hover { opacity: 0; }
    .alma-launcher.is-open .alma-launcher-img--hover { opacity: 1; }
    .alma-launcher.is-open .alma-launcher-img--rest { opacity: 0; }
    @media (hover: hover) {
        .alma-launcher:hover .alma-launcher-art {
            transform: translateY(-3px) scale(1.04);
            filter: drop-shadow(0 10px 16px rgba(14, 15, 59, .32));
        }
        .alma-launcher:hover .alma-launcher-img--hover { opacity: 1; }
        .alma-launcher:hover .alma-launcher-img--rest { opacity: 0; }
    }
    .alma-launcher:active .alma-launcher-art {
        transform: scale(.94);
        transition-duration: .12s;
    }
    .alma-launcher:focus-visible {
        outline: 2px solid var(--alma-orange);
        outline-offset: 4px;
        border-radius: 18px;
    }

    /* "Ask ALMA!" tooltip, above the mascot (same look as the header icon tooltips) */
    .alma-tooltip {
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translate(-50%, 4px);
        padding: 6px 10px;
        border-radius: 6px;
        background: var(--alma-navy);
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        line-height: 1;
        letter-spacing: .01em;
        white-space: nowrap;
        box-shadow: 0 6px 16px rgba(14, 15, 59, .25);
        opacity: 0;
        pointer-events: none;
        transition: opacity .18s ease, transform .22s var(--alma-ease);
    }
    .alma-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border: 5px solid transparent;
        border-top-color: var(--alma-navy);
    }
    @media (hover: hover) {
        .alma-launcher:hover .alma-tooltip { opacity: 1; transform: translate(-50%, 0); transition-delay: .06s; }
    }
    .alma-launcher:focus-visible .alma-tooltip { opacity: 1; transform: translate(-50%, 0); }
    .alma-launcher.is-open .alma-tooltip { opacity: 0 !important; }

    /* Unread badge (same colour and shape as the header notification badge) */
    .alma-badge {
        position: absolute;
        top: -2px;
        right: -2px;
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        border-radius: 999px;
        background: var(--alma-rust);
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        line-height: 20px;
        text-align: center;
        box-shadow: 0 0 0 2px #fff, 0 2px 6px rgba(199, 61, 26, .45);
        pointer-events: none;
    }
    .alma-badge.is-bump { animation: almaBadgePop .5s var(--alma-ease); }
    @keyframes almaBadgePop {
        0% { transform: scale(.3); opacity: 0; }
        55% { transform: scale(1.18); opacity: 1; }
        100% { transform: scale(1); }
    }

    /* ---------- Panel ---------- */
    .alma-panel {
        --alma-p-bottom: calc(var(--alma-l-bottom) + var(--alma-l-size) + var(--alma-gap)); /* just above the mascot */
        position: fixed;
        right: var(--alma-l-right);
        bottom: var(--alma-p-bottom);
        z-index: 90;
        width: 24rem;
        max-width: calc(100vw - 2rem);
        height: 34rem;
        /* never taller than the space above the mascot (dvh follows the phone's URL bar and keyboard) */
        max-height: calc(100vh - var(--alma-p-bottom) - 16px);
        max-height: calc(100dvh - var(--alma-p-bottom) - 16px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 24px 48px -12px rgba(14, 15, 59, .38), 0 0 0 1px rgba(14, 15, 59, .06);
        transform-origin: calc(100% - 30px) 100%;
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transform: translateY(14px) scale(.95);
        transition: opacity .18s ease, transform .2s ease, visibility 0s linear .2s;
    }
    .alma-panel.is-open {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
        transform: none;
        transition: opacity .24s ease, transform .42s var(--alma-ease), visibility 0s;
    }

    .alma-head {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 80px;
        padding: 10px 14px 10px 10px;
        flex-shrink: 0;
        color: #fff;
        background-color: var(--alma-navy);
        background-image: url("{{ $almaAsset('chatbotHeaderBg.jpg') }}"), linear-gradient(135deg, var(--alma-blue), var(--alma-navy));
        background-size: cover;
        background-position: center;
    }
    .alma-head-logo {
        width: 60px;
        height: 60px;
        flex-shrink: 0;
        object-fit: contain;
        filter: drop-shadow(0 4px 8px rgba(0, 0, 0, .3));
    }
    .alma-head-text { min-width: 0; }
    .alma-title { font-size: 20px; font-weight: 800; line-height: 1.1; letter-spacing: .04em; }
    .alma-subtitle { margin-top: 2px; font-size: 12px; font-weight: 500; line-height: 1.3; color: rgba(255, 255, 255, .92); }
    .alma-status {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 5px;
        font-size: 10.5px;
        font-weight: 600;
        line-height: 1.2;
        color: rgba(255, 255, 255, .82);
    }
    .alma-status-dot {
        width: 7px;
        height: 7px;
        flex-shrink: 0;
        border-radius: 50%;
        background: #4ade80;
        box-shadow: 0 0 0 2px rgba(74, 222, 128, .28);
        transition: background .3s ease, box-shadow .3s ease;
    }
    .alma-status[data-tone="waiting"] .alma-status-dot { background: #fbbf24; box-shadow: 0 0 0 2px rgba(251, 191, 36, .3); animation: almaPulse 1.6s ease-in-out infinite; }
    .alma-status[data-tone="connecting"] .alma-status-dot { background: #cbd5e1; box-shadow: 0 0 0 2px rgba(203, 213, 225, .3); animation: almaPulse 1.2s ease-in-out infinite; }
    .alma-status[data-tone="off"] .alma-status-dot { background: #9ca3af; box-shadow: none; }
    @keyframes almaPulse { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }

    .alma-close {
        position: relative;
        margin-left: auto;
        width: 30px;
        height: 30px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: rgba(255, 255, 255, .94);
        color: #6b7280;
        box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
        transition: background .2s ease, color .2s ease, transform .3s var(--alma-ease);
    }
    .alma-close::before { content: ''; position: absolute; inset: -7px; } /* 44px tap area */
    .alma-close:hover { background: #fff; color: var(--alma-navy); transform: rotate(90deg); }
    .alma-close:focus-visible { outline: 2px solid #fff; outline-offset: 2px; }

    .alma-notice {
        position: relative;
        z-index: 2;
        flex-shrink: 0;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 8px 16px;
        background: #fff;
        color: #6b7280;
        font-size: 10.5px;
        line-height: 1.45;
        box-shadow: 0 6px 12px -8px rgba(14, 15, 59, .3);
    }
    .alma-notice-icon { flex-shrink: 0; width: 14px; height: 14px; margin-top: 1px; color: inherit; }
    .alma-agent-banner {
        position: relative;
        z-index: 1;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 7px 16px;
        background: #f0fdf4;
        border-bottom: 1px solid #dcfce7;
        color: #15803d;
        font-size: 10.5px;
        font-weight: 700;
        animation: almaSlideDown .35s var(--alma-ease) both;
    }
    @keyframes almaSlideDown { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }

    /* ---------- Thread ---------- */
    .alma-thread {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overscroll-behavior: contain;
        display: flex;
        flex-direction: column;
        gap: 14px;
        padding: 18px 16px 12px;
        background: #fff;
        scrollbar-width: thin;
        scrollbar-color: #d1d5db transparent;
    }
    .alma-thread::-webkit-scrollbar { width: 6px; }
    .alma-thread::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 999px; }

    .alma-group { display: flex; align-items: flex-start; gap: 10px; }
    .alma-group--user { justify-content: flex-end; }
    .alma-group-body {
        min-width: 0;
        max-width: calc(100% - 50px);
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
    .alma-group--user .alma-group-body { max-width: 82%; align-items: flex-end; }
    .alma-avatar {
        width: 40px;
        height: 40px;
        margin-top: 19px; /* lines up with the first bubble, under the sender name */
        flex-shrink: 0;
        overflow: hidden;
        border-radius: 50%;
        background: var(--alma-orange);
        box-shadow: 0 2px 6px rgba(237, 122, 7, .35);
    }
    .alma-avatar img { width: 100%; height: 100%; object-fit: cover; object-position: center bottom; }
    .alma-avatar--agent {
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--alma-navy-2);
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        box-shadow: 0 2px 6px rgba(14, 15, 59, .3);
    }
    .alma-sender {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        margin: 0 0 5px 2px;
        color: var(--alma-navy);
        font-size: 11px;
        font-weight: 700;
        line-height: 1.3;
    }
    .alma-tag {
        padding: 2px 6px;
        border-radius: 999px;
        background: #dcfce7;
        color: #15803d;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .alma-bubbles { display: flex; flex-direction: column; align-items: flex-start; gap: 6px; max-width: 100%; }
    .alma-group--user .alma-bubbles { align-items: flex-end; }
    .alma-bubble {
        max-width: 100%;
        padding: 10px 14px;
        border-radius: 16px;
        background: #E9E9EC;
        color: #1f2937;
        font-size: 13px;
        line-height: 1.55;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }
    .alma-bubble strong { font-weight: 700; color: var(--alma-navy); }
    .alma-bubble--prompt { font-weight: 600; color: var(--alma-navy); }
    .alma-group--user .alma-bubble { background: var(--alma-navy-2); color: #fff; }
    .alma-bubble.is-pending { opacity: .7; }
    .alma-bubble.is-failed { background: #fee2e2; color: #991b1b; }
    .alma-time { margin: 4px 4px 0; color: #9ca3af; font-size: 10px; line-height: 1.2; }
    .alma-time:empty { display: none; }

    /* Dated day separator — drawn wherever the day changes between two
       consecutive messages, so bubbles only ever show a clock time. */
    .alma-day { display: flex; align-items: center; gap: 10px; }
    .alma-day::before,
    .alma-day::after { content: ''; flex: 1; height: 1px; background: #e5e7eb; }
    .alma-day span {
        flex-shrink: 0;
        color: #9ca3af;
        font-size: 10px;
        font-weight: 600;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .alma-failed-note { display: flex; align-items: center; gap: 6px; margin: 4px 4px 0; color: #b91c1c; font-size: 10.5px; font-weight: 600; }
    .alma-retry {
        padding: 2px 8px;
        border-radius: 999px;
        border: 1px solid #fecaca;
        background: #fff;
        color: #b91c1c;
        font-size: 10.5px;
        font-weight: 700;
        transition: background .2s ease;
    }
    .alma-retry:hover { background: #fef2f2; }
    .alma-system-note {
        align-self: center;
        max-width: 90%;
        padding: 8px 12px;
        border-radius: 10px;
        background: #f9fafb;
        color: #6b7280;
        font-size: 11px;
        line-height: 1.45;
        text-align: center;
    }

    /* Typing indicator: three dots in the brand colours */
    .alma-typing-bubble { display: inline-flex; align-items: center; gap: 5px; padding: 14px 16px; }
    .alma-typing-bubble span { width: 7px; height: 7px; border-radius: 50%; animation: almaDot 1.2s ease-in-out infinite; }
    .alma-typing-bubble span:nth-child(1) { background: var(--alma-navy); }
    .alma-typing-bubble span:nth-child(2) { background: var(--alma-rust); animation-delay: .15s; }
    .alma-typing-bubble span:nth-child(3) { background: var(--alma-orange); animation-delay: .3s; }
    @keyframes almaDot {
        0%, 60%, 100% { transform: translateY(0); opacity: .45; }
        30% { transform: translateY(-4px); opacity: 1; }
    }

    /* Message entrances */
    .alma-anim-in { animation: almaIn .4s var(--alma-ease) both; transform-origin: 0 100%; }
    .alma-anim-in-user { animation: almaInUser .34s var(--alma-ease) both; transform-origin: 100% 100%; }
    .alma-anim-text { animation: almaText .32s ease both; transform-origin: 0 50%; }
    @keyframes almaIn { from { opacity: 0; transform: translateY(10px) scale(.97); } to { opacity: 1; transform: none; } }
    @keyframes almaInUser { from { opacity: 0; transform: translate(12px, 6px) scale(.97); } to { opacity: 1; transform: none; } }
    @keyframes almaText { from { opacity: 0; transform: scale(.98); } to { opacity: 1; transform: none; } }

    /* ---------- Composer ---------- */
    .alma-composer { flex-shrink: 0; padding: 10px 14px 14px; background: #fff; }
    .alma-input-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 5px 5px 5px 18px;
        border: 1px solid #C9CBD3;
        border-radius: 999px;
        background: #fff;
        transition: border-color .2s ease, box-shadow .2s ease;
    }
    .alma-input-wrap:focus-within { border-color: var(--alma-blue); box-shadow: 0 0 0 3px rgba(29, 70, 164, .12); }
    .alma-input {
        flex: 1;
        min-width: 0;
        padding: 9px 0;
        border: 0;
        outline: none;
        background: transparent;
        color: #111827;
        font-size: 14px;
    }
    .alma-input::placeholder { color: #9ca3af; }
    .alma-input { text-overflow: ellipsis; } /* narrow phones: "Welcome! How can I help…" rather than a hard cut */
    .alma-send {
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: var(--alma-navy);
        color: #fff;
        transition: background .2s ease, transform .25s var(--alma-ease), opacity .2s ease;
    }
    .alma-send:hover:not([aria-disabled="true"]) { background: var(--alma-navy-2); transform: translateY(-1px); }
    .alma-send:active:not([aria-disabled="true"]) { transform: scale(.92); }
    .alma-send[aria-disabled="true"] { cursor: default; }
    .alma-send:disabled { opacity: .4; cursor: not-allowed; }
    .alma-send:focus-visible { outline: 2px solid var(--alma-orange); outline-offset: 2px; }
    .alma-ended { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 2px 0; text-align: center; }
    .alma-ended p { color: #6b7280; font-size: 12px; }
    .alma-new-chat {
        padding: 9px 18px;
        border-radius: 999px;
        background: var(--alma-navy);
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        transition: background .2s ease, transform .25s var(--alma-ease);
    }
    .alma-new-chat:hover { background: var(--alma-navy-2); transform: translateY(-1px); }

    /* ---------- Screen sizes ---------- */
    /* Phones: the panel spans the screen with 12px margins, above the mascot. */
    @media (max-width: 639px) {
        .alma-panel { right: 12px; width: calc(100vw - 24px); max-width: none; height: 36rem; }
    }
    /* Desktop: the panel opens to the LEFT of the mascot, bottoms lined up, and slides out from it.
       Its top stays below the site's navigation bar. */
    @media (min-width: 1024px) {
        .alma-panel {
            --alma-p-right: calc(var(--alma-l-right) + var(--alma-l-size) + var(--alma-gap));
            right: var(--alma-p-right);
            bottom: var(--alma-l-bottom);
            max-height: calc(100vh - var(--alma-l-bottom) - 96px);
            max-height: calc(100dvh - var(--alma-l-bottom) - 96px);
            transform-origin: 100% 100%;
            transform: translateX(16px) scale(.95);
        }
    }
    /* Small phones (320-380px): tighter header and bubbles so nothing wraps awkwardly. */
    @media (max-width: 380px) {
        .alma-head { gap: 10px; min-height: 72px; padding: 8px 12px 8px 8px; }
        .alma-head-logo { width: 50px; height: 50px; }
        .alma-title { font-size: 18px; }
        .alma-subtitle { font-size: 11px; }
        .alma-notice { padding: 7px 12px; }
        .alma-thread { padding: 14px 12px 10px; gap: 12px; }
        .alma-group { gap: 8px; }
        .alma-avatar { width: 34px; height: 34px; }
        .alma-group-body { max-width: calc(100% - 42px); }
        .alma-bubble { padding: 9px 12px; font-size: 12.5px; }
        .alma-composer { padding: 8px 10px 12px; }
        .alma-input-wrap { padding-left: 14px; }
    }
    /* Large monitors: a little more room to read. */
    @media (min-width: 1536px) and (min-height: 900px) {
        .alma-panel { width: 26rem; height: 38rem; }
    }
    /* Short screens (phones and small tablets held sideways, very short windows): the panel opens beside
       the mascot, using the full height, with a compact header. */
    @media (max-height: 560px) {
        .alma-panel {
            --alma-p-right: calc(var(--alma-l-right) + var(--alma-l-size) + var(--alma-gap));
            right: var(--alma-p-right);
            bottom: 12px;
            width: min(24rem, calc(100vw - var(--alma-p-right) - 12px));
            max-width: none;
            height: calc(100vh - 24px);
            height: calc(100dvh - 24px);
            max-height: none;
            transform-origin: 100% 100%;
            transform: translateX(16px) scale(.95);
        }
        .alma-head { min-height: 58px; padding: 6px 12px 6px 8px; gap: 10px; }
        .alma-head-logo { width: 44px; height: 44px; }
        .alma-title { font-size: 17px; }
        .alma-subtitle { margin-top: 1px; }
        .alma-status { margin-top: 2px; }
        .alma-notice { padding: 6px 14px; }
        .alma-thread { padding: 12px 14px 8px; gap: 10px; }
        .alma-composer { padding: 8px 12px 10px; }
        .alma-input { padding: 7px 0; }
    }
    /* Back-to-top button (partials/back-to-top): just left of the mascot, centres lined up. While the
       panel is open beside the mascot it would sit under the panel, so it moves up above the mascot
       (the "Ask ALMA!" tooltip is hidden while the panel is open, so they never meet). */
    #backToTopBtn {
        right: calc(var(--alma-l-right) + var(--alma-l-size) + var(--alma-gap));
        bottom: calc(var(--alma-l-bottom) + (var(--alma-l-size) - 44px) / 2);
    }
    @media (min-width: 1024px), (max-height: 560px) {
        html.alma-chat-open #backToTopBtn {
            right: calc(var(--alma-l-right) + (var(--alma-l-size) - 44px) / 2);
            bottom: calc(var(--alma-l-bottom) + var(--alma-l-size) + var(--alma-gap));
        }
    }
    @media (prefers-reduced-motion: reduce) {
        #chatWidgetButton, #chatWidgetButton *, #chatWidgetPanel, #chatWidgetPanel * {
            animation-duration: 1ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 1ms !important;
        }
    }
</style>

<!-- ══════════════════════ ALMA CHAT WIDGET ══════════════════════ -->
<button type="button" id="chatWidgetButton" class="alma-launcher" onclick="toggleChatWidget()"
    aria-label="Ask ALMA, the Alumni Management Assistant" aria-controls="chatWidgetPanel" aria-expanded="false">
    <span class="alma-launcher-art" aria-hidden="true">
        <img src="{{ $almaAsset('chatbot1.png') }}" alt="" width="69" height="69" draggable="false" class="alma-launcher-img alma-launcher-img--rest">
        <img src="{{ $almaAsset('chatbot2.png') }}" alt="" width="69" height="69" draggable="false" class="alma-launcher-img alma-launcher-img--hover">
    </span>
    <span class="alma-tooltip" aria-hidden="true">Ask ALMA!</span>
    <span id="chatWidgetBadge" class="alma-badge" hidden></span>
</button>

<div id="chatWidgetPanel" class="alma-panel" role="dialog" aria-modal="false" aria-labelledby="almaTitle" aria-hidden="true">
    <header class="alma-head">
        <img src="{{ $almaAsset('chatbotHeader.png') }}" alt="" width="60" height="60" draggable="false" class="alma-head-logo">
        <div class="alma-head-text">
            <p id="almaTitle" class="alma-title">ALMA</p>
            <p class="alma-subtitle">Alumni Management Assistant</p>
            <p class="alma-status" id="chatWidgetStatusRow" data-tone="connecting">
                <span class="alma-status-dot" aria-hidden="true"></span>
                <span id="chatWidgetStatus">Connecting...</span>
            </p>
        </div>
        <button type="button" class="alma-close" onclick="toggleChatWidget()" aria-label="Close chat">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
    </header>

    <div id="chatWidgetNotice" class="alma-notice">
        <svg class="alma-notice-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
        <span>If our AI assistant can't answer your question after 3 tries, a live agent will automatically take over the conversation.</span>
    </div>

    <div id="chatWidgetAgentBanner" class="alma-agent-banner" hidden>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="7" r="4"/><path d="M2 21v-1a7 7 0 0 1 14 0v1"/><path d="M16 11l2 2 4-4"/></svg>
        <span>A live agent has joined this conversation.</span>
    </div>

    <div id="chatWidgetThread" class="alma-thread" role="log" aria-live="polite" aria-relevant="additions"></div>

    <form id="chatWidgetForm" class="alma-composer" autocomplete="off">
        <div class="alma-input-wrap" id="chatWidgetInputWrap">
            <label for="chatWidgetInput" class="alma-sr-only">Message ALMA</label>
            <input type="text" id="chatWidgetInput" class="alma-input" placeholder="Welcome! How can I help you?" maxlength="1000">
            <button type="submit" class="alma-send" id="chatWidgetSend" aria-label="Send message" aria-disabled="true" disabled>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
            </button>
        </div>
        <div class="alma-ended" id="chatWidgetEnded" hidden>
            <p>This conversation has ended.</p>
            <button type="button" class="alma-new-chat" id="chatWidgetNewChat">Start a new conversation</button>
        </div>
    </form>
</div>

<script>
    (function () {
        const CSRF_TOKEN = '{{ csrf_token() }}';
        const START_URL = @json(route('widget.start'));
        const BASE_URL = @json(url('/chatbot'));
        const USER_ID = @json(auth()->id());
        const GREETING_NAME = @json($almaName);
        const AVATAR_URL = @json($almaAsset('chatbotProfile.png'));
        const ALMA_LABEL = 'ALMA - Alumni Management Assistant';
        // The server opens every new conversation with this greeting. The widget shows the ALMA
        // welcome in its place (display only; the stored message is left as it is).
        const SERVER_GREETING_PREFIX = "Hi! I'm the PLV-AlumNet assistant";
        const STORE_KEY = 'almaChat:' + USER_ID;
        const POLL_OPEN_MS = 4000;
        const POLL_CLOSED_MS = 8000;
        const MIN_TYPING_MS = 700;
        const STAGGER_MS = 420;

        const btn = document.getElementById('chatWidgetButton');
        const panel = document.getElementById('chatWidgetPanel');
        const thread = document.getElementById('chatWidgetThread');
        const form = document.getElementById('chatWidgetForm');
        const input = document.getElementById('chatWidgetInput');
        const sendBtn = document.getElementById('chatWidgetSend');
        const inputWrap = document.getElementById('chatWidgetInputWrap');
        const endedBox = document.getElementById('chatWidgetEnded');
        const badge = document.getElementById('chatWidgetBadge');
        const statusRow = document.getElementById('chatWidgetStatusRow');
        const statusText = document.getElementById('chatWidgetStatus');
        const agentBanner = document.getElementById('chatWidgetAgentBanner');

        let ticketId = null;
        let ticketStatus = null;
        let lastMessageId = 0;
        let started = false;
        let starting = null;
        let sending = false;
        let isOpen = false;
        // Sticks once true: a conversation that reached a live agent keeps the banner even after it ends.
        let hasHadAgent = false;
        let pollTimer = null;
        let pollEvery = 0;
        let polling = false;
        const renderedIds = new Set();
        // Day of the last message put in the thread, so a dated separator is
        // drawn only when the day actually changes (see maybeAddDayDivider).
        let lastDateKey = null;

        const STATUS = {
            ai_active: { label: 'Online', tone: 'online' },
            waiting_agent: { label: 'Waiting for a live agent...', tone: 'waiting' },
            with_agent: { label: 'Connected with a live agent', tone: 'online' },
            resolved: { label: 'Conversation ended', tone: 'off' },
        };

        const sleep = ms => new Promise(r => setTimeout(r, ms));

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function initials(name) {
            const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
            return ((parts[0] || 'P')[0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase();
        }

        // ── Saved state (per user, this browser): unread badge + last ticket ──────────
        function loadState() {
            try { return JSON.parse(localStorage.getItem(STORE_KEY)) || {}; } catch (e) { return {}; }
        }
        function saveState(patch) {
            try { localStorage.setItem(STORE_KEY, JSON.stringify(Object.assign(loadState(), patch))); } catch (e) { /* storage blocked: badge just won't survive a page change */ }
        }

        let unread = Number(loadState().unread) || 0;

        function updateBadge(bump) {
            badge.hidden = unread === 0;
            badge.textContent = unread > 9 ? '9+' : String(unread);
            btn.setAttribute('aria-label', 'Ask ALMA, the Alumni Management Assistant' + (unread ? ` (${unread} new ${unread === 1 ? 'reply' : 'replies'})` : ''));
            if (bump && unread) {
                badge.classList.remove('is-bump');
                void badge.offsetWidth;
                badge.classList.add('is-bump');
            }
        }

        function addUnread(count) {
            if (!count) return;
            unread += count;
            // Remember the newest message counted, so the next page's check doesn't count it again.
            saveState({ unread, knownMaxId: Math.max(Number(loadState().knownMaxId) || 0, lastMessageId) });
            updateBadge(true);
        }

        function markSeen() {
            const st = loadState();
            const seen = Math.max(Number(st.lastSeenId) || 0, Number(st.knownMaxId) || 0, lastMessageId);
            unread = 0;
            saveState({ lastSeenId: seen, knownMaxId: seen, unread: 0 });
            updateBadge(false);
        }

        // ── Thread rendering ────────────────────────────────────────────────────────
        function isNearBottom() {
            return thread.scrollHeight - thread.scrollTop - thread.clientHeight < 80;
        }
        function scrollToBottom(smooth) {
            thread.scrollTo({ top: thread.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
        }

        function senderKey(msg) {
            return msg.senderType === 'agent' ? 'agent:' + (msg.senderName || '') : msg.senderType;
        }

        function createGroup(msg) {
            const type = msg.senderType;
            const group = document.createElement('div');
            group.className = 'alma-group ' + (type === 'user' ? 'alma-group--user' : 'alma-group--bot');
            group.dataset.sender = senderKey(msg);
            let avatar = '';
            let label = '';
            if (type === 'ai') {
                avatar = `<div class="alma-avatar" aria-hidden="true"><img src="${AVATAR_URL}" alt="" draggable="false"></div>`;
                label = `<p class="alma-sender">${ALMA_LABEL}</p>`;
            } else if (type === 'agent') {
                avatar = `<div class="alma-avatar alma-avatar--agent" aria-hidden="true">${escapeHtml(initials(msg.senderName))}</div>`;
                label = `<p class="alma-sender">${escapeHtml(msg.senderName || 'PLV-AlumNet Team')} <span class="alma-tag">Live Agent</span></p>`;
            }
            group.innerHTML = `${avatar}<div class="alma-group-body">${label}<div class="alma-bubbles"></div><p class="alma-time"></p></div>`;
            return group;
        }

        function lastGroup() {
            const g = thread.lastElementChild;
            return g && g.classList.contains('alma-group') && g.id !== 'chatWidgetTyping' ? g : null;
        }

        // Draws a dated separator when `dateKey` differs from the last message
        // already in the thread, and reports whether it did — a true result
        // also means the next message must start its own group rather than
        // merging into one that now sits above the divider. Inserted before
        // the typing indicator when it's up, so ALMA's reply lands under the
        // divider rather than above it.
        function maybeAddDayDivider(dateKey, dateLabel) {
            if (!dateKey || dateKey === lastDateKey) return false;
            lastDateKey = dateKey;

            const divider = document.createElement('div');
            divider.className = 'alma-day';
            divider.innerHTML = '<span></span>';
            divider.querySelector('span').textContent = dateLabel || dateKey;

            const typing = document.getElementById('chatWidgetTyping');
            if (typing) thread.insertBefore(divider, typing);
            else thread.appendChild(divider);
            return true;
        }

        // Server-side dateKeys come from Asia/Manila (App\Support\ChatDate), so
        // an optimistic bubble sent before the server echo has to use the same
        // zone or it could draw a second divider for the same day.
        function currentDayKey() {
            // en-CA formats as YYYY-MM-DD, matching ChatDate::key().
            return new Intl.DateTimeFormat('en-CA', {
                timeZone: 'Asia/Manila', year: 'numeric', month: '2-digit', day: '2-digit',
            }).format(new Date());
        }

        function showTyping() {
            if (document.getElementById('chatWidgetTyping')) return;
            const near = isNearBottom();
            const group = createGroup({ senderType: 'ai' });
            group.id = 'chatWidgetTyping';
            group.classList.add('alma-anim-in');
            group.setAttribute('aria-label', 'ALMA is typing');
            group.querySelector('.alma-bubbles').innerHTML = '<div class="alma-bubble alma-typing-bubble"><span></span><span></span><span></span></div>';
            thread.appendChild(group);
            if (near) scrollToBottom(true);
        }
        function hideTyping() {
            const t = document.getElementById('chatWidgetTyping');
            if (t) t.remove();
        }
        // Turns the typing indicator into ALMA's reply, so the avatar and name stay put and only the dots change into text.
        function takeTypingGroup() {
            const t = document.getElementById('chatWidgetTyping');
            if (!t) return null;
            t.removeAttribute('id');
            t.removeAttribute('aria-label');
            t.classList.remove('alma-anim-in');
            t.querySelector('.alma-bubbles').innerHTML = '';
            return t;
        }

        function addBubble(group, html, extraClass, animClass) {
            const bubble = document.createElement('div');
            bubble.className = 'alma-bubble' + (extraClass ? ' ' + extraClass : '');
            bubble.innerHTML = html;
            if (animClass) bubble.classList.add(animClass);
            group.querySelector('.alma-bubbles').appendChild(bubble);
            return bubble;
        }

        // countUnread is false for history loaded on open: only replies that arrive later raise the badge.
        function renderMessage(msg, animate, countUnread = true) {
            if (renderedIds.has(msg.id)) return;
            renderedIds.add(msg.id);
            lastMessageId = Math.max(lastMessageId, msg.id);
            const near = isNearBottom();
            const isUser = msg.senderType === 'user';

            const dayChanged = maybeAddDayDivider(msg.dateKey, msg.dateLabel);

            let group = null;
            let newGroup = false;
            if (msg.senderType === 'ai') group = takeTypingGroup();
            if (!group) {
                hideTyping();
                const last = lastGroup();
                if (last && !dayChanged && last.dataset.sender === senderKey(msg)) {
                    group = last;
                } else {
                    group = createGroup(msg);
                    thread.appendChild(group);
                    newGroup = true;
                }
            }
            group.dataset.sender = senderKey(msg);
            const anim = !animate ? null : (newGroup ? null : (isUser ? 'alma-anim-in-user' : 'alma-anim-in'));
            if (animate && newGroup) group.classList.add(isUser ? 'alma-anim-in-user' : 'alma-anim-in');
            const bubble = addBubble(group, escapeHtml(msg.message), '', anim);
            bubble.dataset.msgId = msg.id;
            group.querySelector('.alma-time').textContent = msg.timeLabel || '';

            if (isUser || near) scrollToBottom(animate);
            if (countUnread && !isUser && !isOpen) addUnread(1);
        }

        function renderWelcome(group) {
            group = group || createGroup({ senderType: 'ai' });
            group.dataset.welcome = '1';
            if (!group.isConnected) thread.prepend(group);
            const name = GREETING_NAME ? `Hello, ${escapeHtml(GREETING_NAME)}` : 'Hello!';
            return {
                group,
                first: () => addBubble(group, `${name}\n\nWelcome to <strong>PLV-AlumNet</strong>!\nI can help you learn more about our university's alumni network and employment platform.`, '', group.dataset.animate ? 'alma-anim-text' : null),
                second: () => addBubble(group, 'What would you like to explore today?', 'alma-bubble--prompt', group.dataset.animate ? 'alma-anim-in' : null),
            };
        }

        function isServerGreeting(msg, index) {
            return index === 0 && msg.senderType === 'ai' && String(msg.message || '').startsWith(SERVER_GREETING_PREFIX);
        }

        // History (or a fresh conversation) right after the panel opens.
        async function renderInitialThread(messages, openedAt) {
            const shown = messages.filter((m, i) => {
                if (isServerGreeting(m, i)) {
                    renderedIds.add(m.id);
                    lastMessageId = Math.max(lastMessageId, m.id);
                    return false;
                }
                return true;
            });

            if (!shown.length) {
                // Fresh conversation: the typing dots turn into the welcome, then the question follows.
                const wait = MIN_TYPING_MS - (performance.now() - openedAt);
                if (wait > 0) await sleep(wait);
                const typing = takeTypingGroup();
                if (typing) typing.dataset.animate = '1';
                const w = renderWelcome(typing);
                w.first();
                scrollToBottom(true);
                await sleep(STAGGER_MS);
                w.second();
                scrollToBottom(true);
                return;
            }

            hideTyping();
            const w = renderWelcome();
            w.first();
            w.second();
            shown.forEach(m => renderMessage(m, false, false));
            scrollToBottom(false);
        }

        // ── Status / composer ───────────────────────────────────────────────────────
        function setStatus(status) {
            ticketStatus = status;
            const s = STATUS[status] || { label: status, tone: 'online' };
            statusText.textContent = s.label;
            statusRow.dataset.tone = s.tone;
            if (status === 'with_agent') hasHadAgent = true;
            agentBanner.hidden = !hasHadAgent;
            const resolved = status === 'resolved';
            input.disabled = resolved;
            input.placeholder = resolved ? 'This conversation has ended.' : 'Welcome! How can I help you?';
            inputWrap.hidden = resolved;
            endedBox.hidden = !resolved;
            refreshSendButton();
            saveState({ ticketId, status });
            schedulePolling();
        }

        // Navy like the design even while the box is empty (an empty send does nothing); faded only while a message is on its way.
        function refreshSendButton() {
            sendBtn.disabled = sending || !ticketId || ticketStatus === 'resolved';
            sendBtn.setAttribute('aria-disabled', String(sendBtn.disabled || !input.value.trim()));
        }

        function showSystemNote(text, retry) {
            hideTyping();
            const note = document.createElement('div');
            note.className = 'alma-system-note alma-anim-in';
            note.innerHTML = escapeHtml(text) + (retry ? ' <button type="button" class="alma-retry">Try again</button>' : '');
            if (retry) note.querySelector('button').addEventListener('click', () => { note.remove(); retry(); });
            thread.appendChild(note);
            scrollToBottom(true);
        }

        // ── Server calls (same endpoints as before) ─────────────────────────────────
        async function startSession() {
            if (started) return;
            if (starting) return starting;
            starting = (async () => {
                const openedAt = performance.now();
                statusText.textContent = 'Connecting...';
                statusRow.dataset.tone = 'connecting';
                showTyping();
                try {
                    const st = loadState();
                    let data;
                    if (st.ticketId && st.status === 'resolved' && (Number(st.unread) || 0) > 0) {
                        // A conversation ended while the panel was closed and has replies the user hasn't seen:
                        // show it (read only) instead of opening a brand-new one.
                        const res = await fetch(`${BASE_URL}/${st.ticketId}/poll?after=0`, { headers: { 'Accept': 'application/json' } });
                        if (!res.ok) throw new Error('poll ' + res.status);
                        data = await res.json();
                        data.ticketId = st.ticketId;
                    } else {
                        const res = await fetch(START_URL, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
                        });
                        if (!res.ok) throw new Error('start ' + res.status);
                        data = await res.json();
                    }
                    if (st.ticketId && st.ticketId !== data.ticketId) saveState({ lastSeenId: 0, knownMaxId: 0, unread: 0 });
                    ticketId = data.ticketId;
                    started = true;
                    hasHadAgent = (data.messages || []).some(m => m.senderType === 'agent');
                    await renderInitialThread(data.messages || [], openedAt);
                    setStatus(data.status);
                    if (isOpen) markSeen();
                } catch (e) {
                    started = false;
                    statusText.textContent = 'Unable to connect';
                    statusRow.dataset.tone = 'off';
                    showSystemNote("We couldn't reach ALMA. Please check your connection.", () => startSession());
                } finally {
                    starting = null;
                }
            })();
            return starting;
        }

        function needsPolling() {
            const s = started ? ticketStatus : loadState().status;
            return !!(started ? ticketId : loadState().ticketId) && (s === 'waiting_agent' || s === 'with_agent');
        }
        function stopPolling() {
            if (pollTimer) clearInterval(pollTimer);
            pollTimer = null;
            pollEvery = 0;
        }
        function schedulePolling() {
            if (!needsPolling()) { stopPolling(); return; }
            const every = isOpen ? POLL_OPEN_MS : POLL_CLOSED_MS;
            if (pollTimer && pollEvery === every) return;
            stopPolling();
            pollEvery = every;
            pollTimer = setInterval(pollTick, every);
        }
        async function pollTick() {
            if (polling || sending) return;
            polling = true;
            try { started ? await pollWidget() : await backgroundCheck(); } finally { polling = false; }
        }

        // Thread on screen (opened on this page): render new messages.
        async function pollWidget() {
            if (!ticketId) return;
            try {
                const res = await fetch(`${BASE_URL}/${ticketId}/poll?after=${lastMessageId}`, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                for (const m of data.messages) {
                    if (reconcilePending(m)) continue;
                    renderMessage(m, true);
                }
                if (isOpen) markSeen();
                if (data.status !== ticketStatus) setStatus(data.status);
            } catch (e) { /* network hiccup: the next tick retries */ }
        }

        // Panel not opened on this page yet: only count replies for the badge.
        async function backgroundCheck() {
            const st = loadState();
            if (!st.ticketId) return;
            const after = Math.max(Number(st.lastSeenId) || 0, Number(st.knownMaxId) || 0);
            try {
                const res = await fetch(`${BASE_URL}/${st.ticketId}/poll?after=${after}`, { headers: { 'Accept': 'application/json' } });
                if (res.status === 403 || res.status === 404) { saveState({ ticketId: null, status: null, unread: 0 }); unread = 0; updateBadge(false); stopPolling(); return; }
                if (!res.ok) return;
                const data = await res.json();
                const ids = data.messages.map(m => m.id);
                const replies = data.messages.filter(m => m.senderType !== 'user').length;
                saveState({ status: data.status, knownMaxId: Math.max(after, ...ids), awaitingReply: false });
                addUnread(replies);
                schedulePolling();
            } catch (e) { /* try again on the next tick */ }
        }

        // ── Sending ─────────────────────────────────────────────────────────────────
        const pendingBubbles = [];

        function addPendingUserBubble(text) {
            hideTyping();
            const dayChanged = maybeAddDayDivider(currentDayKey(), 'Today');
            let group = lastGroup();
            let newGroup = false;
            if (dayChanged || !group || group.dataset.sender !== 'user') {
                group = createGroup({ senderType: 'user' });
                thread.appendChild(group);
                newGroup = true;
            }
            if (newGroup) group.classList.add('alma-anim-in-user');
            const bubble = addBubble(group, escapeHtml(text), 'is-pending', newGroup ? null : 'alma-anim-in-user');
            bubble.dataset.pendingText = text;
            pendingBubbles.push(bubble);
            scrollToBottom(true);
            return bubble;
        }

        // The server echoes the user's own message back with its id: attach it to the bubble already on screen.
        // allowFallback (send response only): if the server tidied the text, take the oldest pending bubble.
        function reconcilePending(msg, allowFallback = false) {
            if (msg.senderType !== 'user' || renderedIds.has(msg.id)) return false;
            const i = pendingBubbles.findIndex(b => b.dataset.pendingText === msg.message);
            const idx = i >= 0 ? i : (allowFallback && pendingBubbles.length ? 0 : -1);
            if (idx < 0) return false;
            const bubble = pendingBubbles.splice(idx, 1)[0];
            bubble.classList.remove('is-pending');
            bubble.dataset.msgId = msg.id;
            delete bubble.dataset.pendingText;
            renderedIds.add(msg.id);
            lastMessageId = Math.max(lastMessageId, msg.id);
            const time = bubble.closest('.alma-group').querySelector('.alma-time');
            if (time) time.textContent = msg.timeLabel || '';
            return true;
        }

        function markFailed(bubble, text) {
            const idx = pendingBubbles.indexOf(bubble);
            if (idx >= 0) pendingBubbles.splice(idx, 1);
            bubble.classList.remove('is-pending');
            bubble.classList.add('is-failed');
            const note = document.createElement('p');
            note.className = 'alma-failed-note alma-anim-in';
            note.innerHTML = 'Not sent. <button type="button" class="alma-retry">Retry</button>';
            note.querySelector('button').addEventListener('click', () => {
                const group = bubble.closest('.alma-group');
                note.remove();
                bubble.remove();
                if (group && !group.querySelector('.alma-bubble')) group.remove();
                sendMessage(text);
            });
            bubble.insertAdjacentElement('afterend', note);
            scrollToBottom(true);
        }

        async function sendMessage(text) {
            if (!ticketId || sending || ticketStatus === 'resolved') return;
            sending = true;
            refreshSendButton();
            const bubble = addPendingUserBubble(text);
            const expectsAi = ticketStatus === 'ai_active';
            let typingShownAt = 0;
            let typingTimer = null;
            if (expectsAi) {
                // A short beat after the user's bubble lands, ALMA starts "typing".
                typingTimer = setTimeout(() => { showTyping(); typingShownAt = performance.now(); }, 260);
                saveState({ awaitingReply: true });
            }

            const formData = new FormData();
            formData.append('_token', CSRF_TOKEN);
            formData.append('message', text);

            try {
                const res = await fetch(`${BASE_URL}/${ticketId}/send`, { method: 'POST', headers: { 'Accept': 'application/json' }, body: formData });
                if (res.status === 409) {
                    clearTimeout(typingTimer);
                    hideTyping();
                    markFailed(bubble, text);
                    setStatus('resolved');
                    return;
                }
                if (!res.ok) throw new Error('send ' + res.status);
                const data = await res.json();

                const fresh = [];
                for (const m of data.messages) {
                    if (renderedIds.has(m.id)) continue;
                    if (reconcilePending(m, true)) continue;
                    fresh.push(m);
                }
                if (fresh.length) lastMessageId = Math.max(lastMessageId, ...fresh.map(m => m.id));

                if (expectsAi) {
                    clearTimeout(typingTimer);
                    if (!typingShownAt) { showTyping(); typingShownAt = performance.now(); }
                    const wait = MIN_TYPING_MS - (performance.now() - typingShownAt);
                    if (wait > 0) await sleep(wait);
                }
                for (let i = 0; i < fresh.length; i++) {
                    if (i > 0) await sleep(STAGGER_MS);
                    renderMessage(fresh[i], true);
                }
                hideTyping();
                if (isOpen) markSeen();
                if (data.status !== ticketStatus) setStatus(data.status);
            } catch (e) {
                clearTimeout(typingTimer);
                hideTyping();
                markFailed(bubble, text);
            } finally {
                sending = false;
                saveState({ awaitingReply: false });
                refreshSendButton();
            }
        }

        function resetConversation() {
            stopPolling();
            thread.innerHTML = '';
            renderedIds.clear();
            lastDateKey = null;
            pendingBubbles.length = 0;
            ticketId = null;
            ticketStatus = null;
            lastMessageId = 0;
            hasHadAgent = false;
            started = false;
            agentBanner.hidden = true;
            inputWrap.hidden = false;
            endedBox.hidden = true;
            input.disabled = false;
            input.placeholder = 'Welcome! How can I help you?';
            saveState({ ticketId: null, status: null, lastSeenId: 0, knownMaxId: 0, unread: 0, awaitingReply: false });
            unread = 0;
            updateBadge(false);
            refreshSendButton();
        }

        // ── Open / close ────────────────────────────────────────────────────────────
        function openPanel() {
            isOpen = true;
            document.documentElement.classList.add('alma-chat-open');
            panel.classList.add('is-open');
            panel.setAttribute('aria-hidden', 'false');
            btn.classList.add('is-open');
            btn.setAttribute('aria-expanded', 'true');
            if (started) {
                markSeen();
                scrollToBottom(false);
            } else {
                startSession();
            }
            schedulePolling();
            // Keyboard focus on desktop only, so phones don't pop the keyboard up on every open.
            if (window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                setTimeout(() => { if (isOpen && !input.disabled) input.focus({ preventScroll: true }); }, 260);
            }
        }

        function closePanel() {
            isOpen = false;
            document.documentElement.classList.remove('alma-chat-open');
            const hadFocus = panel.contains(document.activeElement);
            panel.classList.remove('is-open');
            panel.setAttribute('aria-hidden', 'true');
            btn.classList.remove('is-open');
            btn.setAttribute('aria-expanded', 'false');
            if (hadFocus) btn.focus({ preventScroll: true });
            schedulePolling(); // keeps checking (more slowly) while a live agent may reply
        }

        function toggleChatWidget() {
            isOpen ? closePanel() : openPanel();
        }
        window.toggleChatWidget = toggleChatWidget;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const text = input.value.trim();
            if (!text || sending || !ticketId) return;
            input.value = '';
            refreshSendButton();
            sendMessage(text);
        });
        input.addEventListener('input', refreshSendButton);
        document.getElementById('chatWidgetNewChat').addEventListener('click', function () {
            resetConversation();
            startSession();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen) closePanel();
        });
        // Another tab read the replies (or got new ones): keep this tab's badge in step.
        window.addEventListener('storage', function (e) {
            if (e.key !== STORE_KEY) return;
            const next = Number(loadState().unread) || 0;
            const grew = next > unread;
            unread = isOpen ? 0 : next;
            updateBadge(grew);
        });

        // On page load: show the saved badge, and check for replies that may have come in meanwhile.
        updateBadge(false);
        // Fetch ALMA's avatar now so the first reply never shows an empty orange circle.
        new Image().src = AVATAR_URL;
        (function initialCheck() {
            const st = loadState();
            if (!st.ticketId) return;
            if (st.awaitingReply || st.status === 'waiting_agent' || st.status === 'with_agent') backgroundCheck();
            schedulePolling();
        })();

        // ── Draggable vertical position (mobile) ──────────────────────
        // The button sits bottom-right and can cover a real button
        // underneath it on small screens (e.g. a chat page's own Send
        // button). Dragging is vertical-only — the button stays pinned to
        // the right edge, only its distance from the bottom changes — and
        // clamped so it can never be dragged partly or fully off-screen.
        // Touch events only (not mouse/pointer), so desktop click-to-open
        // is completely unaffected.
        (function initDraggableWidget() {
            const STORAGE_KEY = 'chatWidgetBottomOffset';
            const EDGE_MARGIN = 12; // px kept clear at both the top and bottom of the screen
            const PANEL_GAP = 12; // gap between the button and the panel above it (matches the CSS default)

            function maxOffset() {
                return Math.max(EDGE_MARGIN, window.innerHeight - btn.offsetHeight - EDGE_MARGIN);
            }

            // Desktop and short screens open the panel beside the mascot (CSS): keep its bottom level with the mascot.
            const besideLayout = window.matchMedia('(min-width: 1024px), (max-height: 560px)');
            const shortScreen = window.matchMedia('(max-height: 560px)');

            function applyOffset(px) {
                const clamped = Math.min(Math.max(px, EDGE_MARGIN), maxOffset());
                btn.style.bottom = clamped + 'px';
                if (besideLayout.matches) {
                    panel.style.bottom = clamped + 'px';
                    panel.style.maxHeight = Math.max(200, window.innerHeight - clamped - (shortScreen.matches ? EDGE_MARGIN : 96)) + 'px';
                } else {
                    const panelBottom = clamped + btn.offsetHeight + PANEL_GAP;
                    panel.style.bottom = panelBottom + 'px';
                    // Keep the whole panel on screen when the button has been moved up.
                    panel.style.maxHeight = Math.max(240, window.innerHeight - panelBottom - 16) + 'px';
                }
                return clamped;
            }

            // Restore the last dragged position (per-device, via
            // localStorage) — otherwise it would snap back to the default
            // corner on every page navigation, right back over whatever it
            // was moved away from.
            let saved = NaN;
            try { saved = parseFloat(localStorage.getItem(STORAGE_KEY)); } catch (e) { /* storage blocked */ }
            if (!isNaN(saved)) applyOffset(saved);

            // Lets a page park the button somewhere specific (e.g. above a
            // bottom-anchored composer) without touching the saved drag spot.
            window.setChatWidgetBottom = applyOffset;

            let dragging = false;
            let moved = false;
            let startY = 0;
            let startOffset = 0;

            btn.addEventListener('touchstart', function (e) {
                dragging = true;
                moved = false;
                startY = e.touches[0].clientY;
                // Read the button's actual current position rather than
                // trust btn.style.bottom — that's unset on the very first
                // touch of a page load with no saved offset yet.
                startOffset = window.innerHeight - btn.getBoundingClientRect().bottom;
            }, { passive: true });

            btn.addEventListener('touchmove', function (e) {
                if (!dragging) return;
                const deltaY = e.touches[0].clientY - startY;
                // A finger moving DOWN the screen should move the button
                // DOWN too, i.e. decrease its distance from the bottom —
                // hence subtracting, not adding, the delta.
                if (Math.abs(deltaY) > 6) moved = true; // small threshold so a normal tap never gets misread as a drag
                if (moved) applyOffset(startOffset - deltaY);
            }, { passive: true });

            btn.addEventListener('touchend', function (e) {
                if (!dragging) return;
                dragging = false;
                if (moved) {
                    // A genuine drag — save the new spot, and swallow the
                    // click that would otherwise follow this touch and
                    // toggle the panel open/closed unintentionally.
                    try { localStorage.setItem(STORAGE_KEY, parseFloat(btn.style.bottom)); } catch (err) { /* storage blocked */ }
                    e.preventDefault();
                }
                moved = false;
            });

            // Keep it fully on-screen through rotation/resize/keyboard
            // pop-up rather than only re-clamping on the next drag.
            window.addEventListener('resize', function () {
                const current = parseFloat(btn.style.bottom);
                if (!isNaN(current)) applyOffset(current);
            });
        })();
    })();
</script>
