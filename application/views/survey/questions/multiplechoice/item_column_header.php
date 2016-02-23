<?php
/**
 * Multiple Choice Html : column containing item row
 * This view is used only if user set more than one column in the question attribute.
 *
 * @var $first           for the very first item, the bootstrap row containing the cols must be opened
 * @var $iColumnWidth
 */
 ?>
    <!-- on small screen, each column is full widht, so it look like a single colunm-->
    <div class="col-sm-<?php echo $iColumnWidth?> col-xs-12">
        <div class="row">
