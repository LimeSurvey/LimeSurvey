<?php
/** @var array $questions */
?>
<div id="dlgEditParameter" role="dialog" tabindex="-1" class="modal fade"
     data-save-url='<?= Yii::app()->createUrl("surveyAdministration/saveUrlParam") ?>'
>

    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php eT("Add URL parameter"); ?> </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class='mb-3 row'>
                    <div id="parameterError"></div>
                    <label class='form-label' for='paramname'><?php eT('Parameter name:'); ?></label>
                    <div class=''>
                        <input class="form-control" name="paramname" id="paramname" type="text" size="20">
                    </div>
                </div>
                <div class='mb-3 row'>
                    <label class='form-label' for='targetquestion'>
                        <?php eT('Target (sub-)question:'); ?>
                    </label>
                    <div class=''>
                        <select class='form-select' name='targetquestion' id='targetquestion' size='1'>
                            <option value=''><?php eT('(No target question)'); ?></option>
                            <?php foreach ($questions as $question) : ?>
                                <option value='<?php echo $question['qid'] . '-' . $question['sqid']; ?>'>
                                    <?= $question['title'] . ': ' .
                                    ellipsize(
                                        flattenText(
                                            $question['question'],
                                            true,
                                            true
                                        ),
                                        43,
                                        .70

                                    );
                                    if ($question['sqquestion'] != '') {
                                        echo ' - ' . ellipsize(
                                            flattenText(
                                                $question['sqquestion'],
                                                true,
                                                true
                                            ),
                                            30,
                                            .75
                                        );
                                    }
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class='btn btn-cancel' id='btnCancelParams' data-bs-dismiss="modal">
                    <?php eT('Cancel'); ?>
                </button>
                <button class='btn btn-primary' id='btnSaveParams' type="button">
                    <?php eT('Save'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
