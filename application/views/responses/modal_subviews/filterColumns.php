<?php
/**
 * @var $surveyId int
 * @var $filteredColumns array
 * @var $filterableColumns array
 */
?>

<!-- Button trigger modal -->
<button role="button" type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#responses-column-filter-modal" id="responses-column-filter-button">
    <span class="ri-layout-column-fill"></span>
</button>

<!-- Modal -->
<div class="modal fade" id="responses-column-filter-modal" tabindex="-1" role="dialog" aria-labelledby="responses-column-filter-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="<?= App()->createUrl('responses/setfilteredcolumns/', ['surveyId' => $surveyId]) ?>" class="pjax" method="POST" data-filtered-columns=<?php echo json_encode($filteredColumns) ?>>
                <?php
                Yii::app()->getController()->renderPartial(
                    '/layouts/partial_modals/modal_header',
                    ['modalTitle' => gT('Select columns')]
                );
                ?>
                <div class="modal-body">
                    <div class="responses-column-filter-modal-checkbox-buttons">
                        <button role="button" type="button" id="responses-column-filter-modal-selectall" class="btn btn-outline-secondary">
                            <span class="ri-check-fill"></span>
                            &nbsp;
                            <?php eT("Select all"); ?>
                        </button>
                        <button role="button" type="button" id="responses-column-filter-modal-clear" class="btn btn-outline-secondary">
                            <span class="ri-delete-bin-fill text-danger"></span>
                            &nbsp;
                            <?php eT("Clear selection"); ?>
                        </button>
                    </div>
                    <div class="mb-3 responses-multiselect-checkboxes">
                        <?php foreach ($filterableColumns as $columnName => $columnTitle): ?>
                            <div class="checkbox">
                                <label>
                                    <input name="columns[]" type="checkbox" value="<?php echo $columnName ?>"<?php echo !isset($filteredColumns) || in_array($columnName, $filteredColumns) ? 'checked' : '' ?>>
                                    <?php echo $columnTitle ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        <input type="hidden" name="surveyid" value="<?= $surveyId ?>"/>
                        <input type="hidden" name="<?= Yii::app()->request->csrfTokenName ?>" value="<?= htmlspecialchars(App()->request->csrfToken, ENT_QUOTES, null, false) ?>"/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button id="responses-column-filter-modal-cancel" type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT("Cancel"); ?></button>
                    <button role="button" type="button"id="responses-column-filter-modal-submit" class="btn btn-primary">
                        <?php eT('Select'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
