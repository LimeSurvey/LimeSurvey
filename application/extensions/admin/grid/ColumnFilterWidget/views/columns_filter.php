<?php
/**
 * @var $model object
 * @var $modalId string
 * @var $filterableColumns array
 * @var $filteredColumns array
 * @var $columnsData array
 */
?>

<!-- Modal -->
<div class="modal fade" id="<?= $modalId ?>" tabindex="-1" role="dialog" aria-labelledby="responses-column-filter-label">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form
                class="pjax"
                method="POST"
                data-filtered-columns=<?php echo json_encode($filteredColumns) ?>>
                <?php
                Yii::app()->getController()->renderPartial(
                    '/layouts/partial_modals/modal_header',
                    ['modalTitle' => gT('Select columns')]
                );
                ?>
                <div class="modal-body">
                    <div class="<?= $modalId ?>-checkbox-buttons">
                        <button role="button" type="button" id="<?= $modalId ?>-selectall" class="btn btn-outline-secondary">
                            <span class="ri-check-fill"></span>
                            &nbsp;
                            <?php eT("Select all"); ?>
                        </button>
                        <button role="button" type="button" id="<?= $modalId ?>-clear" class="btn btn-outline-secondary">
                            <span class="ri-delete-bin-fill text-danger"></span>
                            &nbsp;
                            <?php eT("Clear selection"); ?>
                        </button>
                    </div>
                    <div class="mb-3 responses-multiselect-checkboxes">
                        <?php foreach ($columnsData as $column) : ?>
                            <?php if (!empty($column->header) && $column->name !== 'dropdown_actions' && !array_key_exists($column->name, $filterableColumns)) : ?>
                                <div class="checkbox">
                                    <label>
                                        <input name="columns[]" type="checkbox" value="" checked disabled>
                                        <?= $column->name ?>
                                    </label>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <?php foreach ($filterableColumns as $column) : ?>
                            <div class="checkbox">
                                <label>
                                    <input name="columns[]" type="checkbox" value='<?php echo $column["name"] ?>' <?php echo !isset($filteredColumns) || in_array($column['name'], $filteredColumns) ? 'checked' : '' ?>>
                                    <?php echo $column['header'] ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <input type="hidden" name="<?= Yii::app()->request->csrfTokenName ?>" value="<?= htmlspecialchars(App()->request->csrfToken, ENT_QUOTES, null, false) ?>"/>
                </div>
                <div class="modal-footer">
                    <button id="<?= $modalId ?>-cancel" type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT("Cancel"); ?></button>
                    <button role="button" type="submit" id="<?= $modalId ?>-submit" class="btn btn-primary" name="selectColumns" value="select" >
                        <?php eT('Select'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
