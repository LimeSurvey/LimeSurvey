/**
 * Keep a Bootstrap 5 tooltip open while the pointer is on it (WCAG 1.4.13 Content on Hover or Focus).
 *
 * Bootstrap hides a hover tooltip as soon as the pointer leaves its trigger, so the pointer
 * can not move onto the tooltip. Here a hide is delayed a moment and cancelled while the pointer
 * is on the trigger or on the tooltip. Escape (see dismissTooltipsOnEscapePress) still hides it at once.
 *
 * Safe to call multiple times — the listeners are only attached once per page load.
 */
const HIDE_DELAY = 150;

const keepTooltipsHoverable = () => {
    if (window.LS && LS._tooltipHoverableBound) {
        return;
    }
    LS._tooltipHoverableBound = true;

    let escapePressed = false;
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            escapePressed = true;
            setTimeout(function () {
                escapePressed = false;
            }, 0);
        }
    }, true);

    document.addEventListener('hide.bs.tooltip', function (e) {
        const trigger = e.target;
        if (escapePressed || trigger.dataset.lsTooltipHide === 'true'
            || typeof bootstrap === 'undefined' || !bootstrap.Tooltip) {
            return;
        }
        const tooltip = bootstrap.Tooltip.getInstance(trigger);
        const tip = tooltip ? tooltip.getTipElement() : null;
        if (!tip || !tip.isConnected) {
            return;
        }
        e.preventDefault();

        const hideUnlessHovered = function () {
            if (!tip.isConnected || !bootstrap.Tooltip.getInstance(trigger)) {
                return;
            }
            if (trigger.matches(':hover') || tip.matches(':hover')) {
                // Check again once the pointer leaves the trigger or the tooltip
                const onLeave = function () {
                    trigger.removeEventListener('mouseleave', onLeave);
                    tip.removeEventListener('mouseleave', onLeave);
                    setTimeout(hideUnlessHovered, HIDE_DELAY);
                };
                trigger.addEventListener('mouseleave', onLeave);
                tip.addEventListener('mouseleave', onLeave);
                return;
            }
            trigger.dataset.lsTooltipHide = 'true';
            tooltip.hide();
            delete trigger.dataset.lsTooltipHide;
        };
        setTimeout(hideUnlessHovered, HIDE_DELAY);
    });
};

export default keepTooltipsHoverable;
