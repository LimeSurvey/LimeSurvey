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
            animation: 150,
            onEnd: updateMoveButtons
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

    var updateMoveButtons = function () {
        var items = $list.children('.organize-columns-item');
        items.each(function (index) {
            $(this).find('.organize-columns-up').prop('disabled', index === 0);
            $(this).find('.organize-columns-down').prop('disabled', index === items.length - 1);
        });
    };
    updateMoveButtons();

    $list.off('click.organize').on('click.organize', '.organize-columns-move', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $item = $btn.closest('.organize-columns-item');
        if ($btn.hasClass('organize-columns-up')) {
            $item.prev('.organize-columns-item').before($item);
        } else {
            $item.next('.organize-columns-item').after($item);
        }
        updateMoveButtons();
        if ($btn.prop('disabled')) {
            $item.find('.organize-columns-move:not(:disabled)').first().trigger('focus');
        } else {
            $btn.trigger('focus');
        }
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
            updateMoveButtons();
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
