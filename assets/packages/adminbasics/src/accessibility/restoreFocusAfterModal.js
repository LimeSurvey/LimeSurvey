/**
 * Return the keyboard focus to the element that opened a Bootstrap 5 modal once it is closed
 * (WCAG 2.4.3 Focus Order).
 *
 * Bootstrap only does this for modals opened through data-bs-toggle with a visible trigger.
 * When the modal was opened by a script, or from an item of a dropdown menu that is closed now,
 * the focus was lost. In that case the focus goes back to the trigger, or to the toggle of
 * its dropdown menu when the trigger itself is hidden.
 *
 * Safe to call multiple times — the listeners are only attached once per page load.
 */
const isVisible = (element) => !!element && element.isConnected
    && !!(element.offsetWidth || element.offsetHeight || element.getClientRects().length)
    && getComputedStyle(element).visibility !== 'hidden';

/**
 * The toggle of the dropdown menu the hidden trigger belongs to, if any.
 *
 * @param {HTMLElement} trigger
 * @return {HTMLElement|null}
 */
const getDropdownToggle = (trigger) => {
    // LimeSurvey action dropdowns can render their menu away from the toggle, linked by an id
    const menu = trigger.closest('[data-for-ls-dropdown-toggle-id]');
    if (menu) {
        const id = menu.getAttribute('data-for-ls-dropdown-toggle-id');
        const toggle = document.querySelector('[data-ls-dropdown-toggle-id="' + CSS.escape(id) + '"]');
        if (toggle) {
            return toggle;
        }
    }
    const dropdown = trigger.closest('.dropdown, .dropup, .dropstart, .dropend, .btn-group');
    return dropdown ? dropdown.querySelector('[data-bs-toggle="dropdown"]') : null;
};

const restoreFocusAfterModal = () => {
    if (window.LS && LS._restoreFocusAfterModalBound) {
        return;
    }
    LS._restoreFocusAfterModalBound = true;

    const triggers = new WeakMap();

    document.addEventListener('show.bs.modal', function (e) {
        const trigger = e.relatedTarget instanceof HTMLElement ? e.relatedTarget : document.activeElement;
        if (trigger && trigger !== document.body && !e.target.contains(trigger)) {
            triggers.set(e.target, trigger);
        }
    });

    document.addEventListener('hidden.bs.modal', function (e) {
        const trigger = triggers.get(e.target);
        triggers.delete(e.target);
        if (!trigger) {
            return;
        }
        // Bootstrap or the page already put the focus on a visible element outside the modal
        const active = document.activeElement;
        if (active && active !== document.body && !e.target.contains(active) && isVisible(active)) {
            return;
        }
        const target = isVisible(trigger) ? trigger : getDropdownToggle(trigger);
        if (target && isVisible(target)) {
            target.focus();
        }
    });
};

export default restoreFocusAfterModal;
