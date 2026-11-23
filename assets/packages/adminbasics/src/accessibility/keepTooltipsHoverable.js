/**
 * Keep a Bootstrap 5 tooltip open while the pointer is on it (WCAG 1.4.13 Content on Hover or Focus).
 *
 * Bootstrap hides a hover tooltip as soon as the pointer leaves its trigger, so the pointer
 * can not move onto the tooltip. Here a hide is delayed a moment and cancelled while the pointer
 * is on the trigger or on the tooltip, or while the trigger is active again (e.g. focused).
 * A hide with data-ls-tooltip-hide="true" on the trigger (Escape, see dismissTooltipsOnEscapePress)
 * is not delayed.
 *
 * Safe to call multiple times — the listeners are only attached once per page load.
 */
const HIDE_DELAY = 150;

/**
 * Hide a tooltip right away, bypassing the delayed hide below.
 * @param {HTMLElement} trigger - The element the tooltip belongs to.
 * @param {Object} tooltip - The Bootstrap tooltip instance of the trigger.
 * @returns {void}
 */
const hideTooltipNow = (trigger, tooltip) => {
    trigger.dataset.lsTooltipHide = 'true';
    try {
        tooltip.hide();
    } finally {
        delete trigger.dataset.lsTooltipHide;
    }
};

/**
 * Attach the listeners that delay the hiding of tooltips.
 * @returns {void}
 */
const keepTooltipsHoverable = () => {
    if (window.LS && LS._tooltipHoverableBound) {
        return;
    }
    LS._tooltipHoverableBound = true;

    // One pending hide per trigger, so repeated hide events don't start parallel checks
    const pendingHides = new WeakMap();

    /**
     * Stop the pending hide of a trigger: Clear its timer and remove its mouseleave listeners.
     * @param {HTMLElement} trigger - The element the tooltip belongs to.
     * @returns {void}
     */
    const cancelPendingHide = (trigger) => {
        const pending = pendingHides.get(trigger);
        if (!pending) {
            return;
        }
        clearTimeout(pending.timer);
        pending.trigger.removeEventListener('mouseleave', pending.onLeave);
        pending.tip.removeEventListener('mouseleave', pending.onLeave);
        pendingHides.delete(trigger);
    };

    /**
     * Start the delayed hide of a tooltip, which checks again after every mouseleave
     * of the trigger or the tooltip until neither is hovered.
     * @param {HTMLElement} trigger - The element the tooltip belongs to.
     * @param {Object} tooltip - The Bootstrap tooltip instance of the trigger.
     * @param {HTMLElement} tip - The tooltip element.
     * @returns {void}
     */
    const startPendingHide = (trigger, tooltip, tip) => {
        const pending = { trigger: trigger, tip: tip, timer: null, onLeave: null };

        const hideUnlessHovered = function () {
            pending.timer = null;
            if (!tip.isConnected || bootstrap.Tooltip.getInstance(trigger) !== tooltip) {
                cancelPendingHide(trigger);
                return;
            }
            if (trigger.matches(':hover') || tip.matches(':hover')) {
                // Wait for the mouseleave listeners
                return;
            }
            cancelPendingHide(trigger);
            const isActiveAgain = typeof tooltip._isWithActiveTrigger === 'function'
                ? tooltip._isWithActiveTrigger()
                : trigger.matches(':focus');
            if (isActiveAgain) {
                // E.g. focused during the delay: Bootstrap hides it again when that ends
                return;
            }
            hideTooltipNow(trigger, tooltip);
        };

        pending.onLeave = function () {
            clearTimeout(pending.timer);
            pending.timer = setTimeout(hideUnlessHovered, HIDE_DELAY);
        };
        trigger.addEventListener('mouseleave', pending.onLeave);
        tip.addEventListener('mouseleave', pending.onLeave);
        pendingHides.set(trigger, pending);
        pending.timer = setTimeout(hideUnlessHovered, HIDE_DELAY);
    };

    document.addEventListener('hide.bs.tooltip', function (e) {
        const trigger = e.target;
        if (trigger.dataset.lsTooltipHide === 'true'
            || typeof bootstrap === 'undefined' || !bootstrap.Tooltip) {
            return;
        }
        const tooltip = bootstrap.Tooltip.getInstance(trigger);
        const tip = tooltip ? tooltip.getTipElement() : null;
        if (!tip || !tip.isConnected) {
            return;
        }
        e.preventDefault();
        if (!pendingHides.has(trigger)) {
            startPendingHide(trigger, tooltip, tip);
        }
    });

    // A tooltip hidden some other way needs no pending hide anymore
    document.addEventListener('hidden.bs.tooltip', function (e) {
        cancelPendingHide(e.target);
    });
};

export {hideTooltipNow};
export default keepTooltipsHoverable;
