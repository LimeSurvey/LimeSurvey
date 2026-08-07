/**
 * Focus the admin status message or notification alert so that screen readers
 * announce it when it appears (e.g. after a redirect following a form action).
 *
 * Safe to call multiple times — each element is only focused once per page load.
 * Elements are marked with a data attribute after being focused to prevent
 * repeated announcements when triggered by multiple events (ready, ajaxStop, etc.).
 *
 * The element must have tabindex="-1" and role="status" / aria-live set in the
 * template (see application/views/admin/super/messagebox.php).
 */
const focusStatusMessage = () => {
    const statusMessage = document.getElementById('admin-status-message');
    if (statusMessage && statusMessage.dataset.announced !== 'true') {
        statusMessage.dataset.announced = 'true';
        statusMessage.focus();
        return;
    }

    const notifAlert = document.querySelector('#notif-container .alert:not([data-announced])');
    if (notifAlert) {
        notifAlert.dataset.announced = 'true';
        notifAlert.focus();
    }
};

export default focusStatusMessage;