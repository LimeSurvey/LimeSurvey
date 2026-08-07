/**
 * Update the grid when the user selects a new page size from a .changePageSize
 * select element.
 *
 * Serialises any active filter inputs alongside the new page size and passes
 * them to yiiGridView.update() so filters are preserved across the page-size
 * change. The select element's name attribute is used as the page-size
 * parameter name, falling back to "pageSize" when absent.
 */
jQuery(document).on('change', '.grid-view-ls .changePageSize', function () {
    'use strict';
    var gridId = $(this).closest('.grid-view-ls').attr('id');
    var pageSizeName = $(this).attr('name') || 'pageSize';
    var data = $('#' + gridId + ' .filters input, #' + gridId + ' .filters select').serialize();
    data += (data ? '&' : '') + pageSizeName + '=' + $(this).val();
    $.fn.yiiGridView.update(gridId, {data: data});
});
