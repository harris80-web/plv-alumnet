{{--
    Shared modal open/close animation helpers.

    Every modal in this app is a `<div class="... hidden ...">` overlay
    toggled by ID — but several different hand-rolled versions of that toggle
    accumulated over time, and most never actually animate: they either have
    no transition classes at all, or they do (`transition-all duration-300
    scale-95 opacity-0`) but the JS removes the hidden state and the
    transition-triggering classes in the same tick, so the browser collapses
    both into one paint and the modal just snaps instead of animating.

    Fix: force a layout read (`getBoundingClientRect()`) between un-hiding
    and flipping the transition classes, so the "closed" state actually
    paints first. Mirrors the showToast/dismissToast fix already proven in
    auth/register.blade.php and auth/login.blade.php.

    Usage — overlay/panel must already carry their own transition classes
    plus a closed-state baseline (e.g. overlay: "transition-opacity
    duration-200 opacity-0", panel: "transition-all duration-200 scale-95
    opacity-0"):

        openAnimatedModal(document.getElementById('fooOverlay'), document.getElementById('fooPanel'));
        closeAnimatedModal(document.getElementById('fooOverlay'), document.getElementById('fooPanel'));

    Pass a third argument to override the panel's closed-state classes for
    variants like slide-in panels, e.g. ['translate-x-full'] instead of the
    ['opacity-0','scale-95'] default.
--}}
<script>
    window.openAnimatedModal = function (overlay, panel, panelHiddenClasses) {
        panelHiddenClasses = panelHiddenClasses || ['opacity-0', 'scale-95'];
        overlay.classList.remove('hidden');
        overlay.getBoundingClientRect(); // force reflow so the hidden state actually paints first
        requestAnimationFrame(function () {
            overlay.classList.remove('opacity-0');
            panel.classList.remove.apply(panel.classList, panelHiddenClasses);
        });
        document.body.style.overflow = 'hidden';
    };

    window.closeAnimatedModal = function (overlay, panel, panelHiddenClasses, onDone) {
        panelHiddenClasses = panelHiddenClasses || ['opacity-0', 'scale-95'];
        overlay.classList.add('opacity-0');
        panel.classList.add.apply(panel.classList, panelHiddenClasses);
        var ms = parseFloat(getComputedStyle(panel).transitionDuration) * 1000 || 300;
        setTimeout(function () {
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
            if (onDone) onDone();
        }, ms);
    };
</script>
