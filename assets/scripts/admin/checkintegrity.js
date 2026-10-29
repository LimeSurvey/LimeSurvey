// Also bind to pjax:scriptcomplete, not just ready: this admin theme uses pjax for
// navigation, and a script that only runs on 'ready' never re-attaches its handlers to
// content a pjax navigation swaps in afterwards - matching every other admin page
// script in this codebase. (The registerScriptFile() call for this file must be
// present on every action that (re-)renders #redundancy-check-form - see
// CheckIntegrity::fixintegrity() - since pjax only re-executes, and only fires
// pjax:scriptcomplete for, scripts present in the response it just swapped in.)
$(document).on('ready pjax:scriptcomplete', function () {
    var $form = $('#redundancy-check-form');

    if ($form.length === 0) {
        return;
    }

    var itemSelector = 'input[name="oldsmultidelete[]"]';

    function getGroupItems($groupToggle) {
        // Each group toggle points to the list it controls via data-target-list.
        return $form.find('.' + $groupToggle.data('target-list') + ' ' + itemSelector);
    }

    function syncGroupToggle($groupToggle) {
        var $groupItems = getGroupItems($groupToggle);
        var checkedItems = $groupItems.filter(':checked').length;

        // A group toggle is checked only when every item in that group is checked.
        $groupToggle.prop('checked', $groupItems.length > 0 && checkedItems === $groupItems.length);
    }

    function syncAllGroupToggles() {
        $form.find('.redundancy-group-toggle').each(function () {
            syncGroupToggle($(this));
        });
    }

    $form.on('change.checkintegrity', '.redundancy-group-toggle', function () {
        var $groupToggle = $(this);

        // Group toggles bulk-select or bulk-clear the existing table checkboxes.
        getGroupItems($groupToggle).prop('checked', $groupToggle.prop('checked'));
        syncGroupToggle($groupToggle);
    });

    $form.on('change.checkintegrity', itemSelector, syncAllGroupToggles);

    // Reflect any server-rendered checked state on initial page load.
    syncAllGroupToggles();
});
