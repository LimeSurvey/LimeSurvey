/**
 * Dismiss all visible Bootstrap 5 tooltips when the user presses Escape.
 *
 * Safe to call multiple times — a guard flag on the window object ensures
 * the keydown listener is only attached once per page load, regardless of
 * how many modules or scripts call this function.
 */
const dismissTooltipsOnEscapePress = () => {
    if (window.LS && LS._tooltipEscapeDismissBound) {
        return;
    }
    LS._tooltipEscapeDismissBound = true;
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape' && e.keyCode !== 27) {
            return;
        }
        if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) {
            return;
        }
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            var t = bootstrap.Tooltip.getInstance(el);
            if (t) {
                t.hide();
            }
        });
    });
};

export default dismissTooltipsOnEscapePress;

