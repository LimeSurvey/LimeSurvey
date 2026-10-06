function initColumnFilter() {
    'use strict';
    var $modal = $('#' + modalId);
    if (!$modal.length) {
        return;
    }
    var $list = $modal.find('.organize-columns-sortable');
    var $checkboxes = function () {
        return $modal.find('.organize-columns-sortable input[type=checkbox]');
    };

    if (typeof Sortable !== 'undefined' && $list.length) {
        Sortable.create($list[0], {
            handle: '.organize-columns-handle',
            draggable: '.organize-columns-item',
            ghostClass: 'organize-columns-ghost',
            animation: 150
        });
    }

    $('#' + modalId + '-selectall').off('click.organize').on('click.organize', function (e) {
        e.preventDefault();
        $checkboxes().prop('checked', true);
    });

    $('#' + modalId + '-clear').off('click.organize').on('click.organize', function (e) {
        e.preventDefault();
        $checkboxes().prop('checked', false);
    });

    $('#' + modalId + '-cancel').off('click.organize').on('click.organize', function (e) {
        e.preventDefault();
        var form = $modal.find('form');
        var selected = form.data('filtered-columns') || [];
        var items = $list.children('.organize-columns-item');
        $checkboxes().each(function () {
            $(this).prop('checked', selected.indexOf($(this).val()) !== -1);
        });
        var ordered = items.toArray().sort(function (a, b) {
            var ia = selected.indexOf($(a).data('column'));
            var ib = selected.indexOf($(b).data('column'));
            if (ia === -1 && ib === -1) { return items.index(a) - items.index(b); }
            if (ia === -1) { return 1; }
            if (ib === -1) { return -1; }
            return ia - ib;
        });
        $list.append(ordered);
        $modal.modal('hide');
    });

    $('#' + modalId + '-submit').off('click.organize').on('click.organize', function (e) {
        e.preventDefault();
        var target = $modal.data('target') || 'survey-grid';
        var columns = $checkboxes().filter(':checked').map(function () {
            return $(this).val();
        }).get();
        $modal.modal('hide');
        $.fn.yiiGridView.update(target, {
            data: {selectColumns: 'select', columnsSelected: columns}
        });
    });
}

$(document).on('ready pjax:scriptcomplete', function () {
    initColumnFilter();
});
