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

    var snapshot = [];
    var confirmed = false;
    $modal.off('show.bs.modal.organize hidden.bs.modal.organize')
        .on('show.bs.modal.organize', function () {
            confirmed = false;
            snapshot = $list.children('.organize-columns-item').toArray().map(function (item) {
                return {item: item, checked: $(item).find('input[type=checkbox]').prop('checked')};
            });
        })
        .on('hidden.bs.modal.organize', function () {
            if (confirmed) {
                return;
            }
            snapshot.forEach(function (state) {
                $list.append(state.item);
                $(state.item).find('input[type=checkbox]').prop('checked', state.checked);
            });
        });

    $('#' + modalId + '-submit').off('click.organize').on('click.organize', function (e) {
        e.preventDefault();
        var target = $modal.data('target') || 'survey-grid';
        var columns = $checkboxes().filter(':checked').map(function () {
            return $(this).val();
        }).get();
        confirmed = true;
        $modal.modal('hide');
        $.fn.yiiGridView.update(target, {
            data: {selectColumns: 'select', columnsSelected: columns}
        });
    });
}

$(function () {
    initColumnFilter();
});
$(document).on('pjax:scriptcomplete', function () {
    initColumnFilter();
});
