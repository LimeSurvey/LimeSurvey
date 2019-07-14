<?php
$surveyid = App()->request->getParam('surveyid');
$surveyColumns = isset($filterableColumns) ? $filterableColumns : null;
?>

<!-- Button trigger modal -->
<a class="btn btn-default" data-toggle="modal" data-target="#responses-column-filter-modal" id="responses-column-filter-button">
    <span class="fa fa-columns"></span>
</a>

<!-- Modal -->
<div class="modal fade" id="responses-column-filter-modal" tabindex="-1" role="dialog" aria-labelledby="responses-column-filter-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?=App()->createUrl('/admin/responses/sa/setfilteredcolumns/', ['surveyid' => $surveyid])?>" class="pjax" method="POST">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="responses-column-filter-modal"><?php eT("Select columns") ?></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="responses-column-filter-select"></label>
                        <select multiple name="columns" id="responses-column-filter-select" class="form-control">
                            <?php foreach ($surveyColumns as $surveyColumn): ?>
                                <option <?php echo in_array($surveyColumn, $filteredColumns) ? 'selected' : '' ?>><?php echo $surveyColumn ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="surveyid" value="<?=$surveyid?>" />
                        <input type="hidden" name="<?=Yii::app()->request->csrfTokenName?>" value="<?=App()->request->csrfToken?>" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT("Close"); ?></button>
                    <button type="submit" class="btn btn-primary"><?php eT('Ok'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>