<?php
$surveyid = App()->request->getParam('surveyid');
$filterableColumns = isset($filterableColumns) ? $filterableColumns : null;
?>

<!-- Button trigger modal -->
<a class="btn btn-default" data-toggle="modal" data-target="#responses-column-filter-modal" id="responses-column-filter-button">
    <span class="fa fa-columns"></span>
</a>

<!-- Modal -->
<div class="modal fade" id="responses-column-filter-modal" tabindex="-1" role="dialog" aria-labelledby="responses-column-filter-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= App()->createUrl('/admin/responses/sa/setfilteredcolumns/', ['surveyid' => $surveyid]) ?>" class="pjax" method="POST" data-filtered-columns=<?php echo json_encode($filteredColumns) ?>>
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><?php eT("Select columns") ?></h4>
                </div>
                <div class="modal-body">
                    <div class="responses-column-filter-modal-checkbox-buttons">
                        <button id="responses-column-filter-modal-selectall" class="btn btn-default">
                            <span class="fa fa-check"></span>&nbsp;<?php eT("Select all"); ?>
                        </button>
                        <button id="responses-column-filter-modal-clear" class="btn btn-default">
                            <span class="fa fa-trash text-danger"></span>&nbsp;<?php eT("Clear selection"); ?>
                        </button>
                    </div>
                    <div class="form-group responses-multiselect-checkboxes">
                        <?php foreach ($filterableColumns as $columnName => $columnTitle): ?>
                            <div class="checkbox">
                                <label>
                                    <input name="columns[]" type="checkbox" value="<?php echo $columnName ?>"<?php echo !isset($filteredColumns) || in_array($columnName, $filteredColumns) ? 'checked' : '' ?>>
                                    <?php echo $columnTitle ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <input type="hidden" name="surveyid" value="<?= $surveyid ?>"/>
                        <input type="hidden" name="<?= Yii::app()->request->csrfTokenName ?>" value="<?= App()->request->csrfToken ?>"/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="responses-column-filter-modal-submit" class="btn btn-primary"><?php eT('Ok'); ?></button>
                    <button id="responses-column-filter-modal-cancel" type="button" class="btn btn-default" data-dismiss="modal"><?php eT("Cancel"); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>