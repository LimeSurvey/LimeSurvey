$(function () {
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
