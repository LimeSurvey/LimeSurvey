<?php
/*
* Assessments edit
*/
?>
<div id="assesements-edit-add" class="modal fade" role="dialog" data-bs-focus="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo CHtml::form(["/assessment/insertUpdate/surveyid/$surveyid"], 'post', ['class' => 'form', 'id' => 'assessmentsform', 'name' => 'assessmentsform', 'role' => 'form']); ?>
            <?php
            Yii::app()->getController()->renderPartial(
                '/layouts/partial_modals/modal_header',
                ['modalTitle' => gT('Add assessment rule')]
            );
            ?>
            <div class="modal-body">
                <!-- Scope, Total, Group -->
                <div class='row'>
                    <div class='mb-3 col-12 row'>
                        <label class='form-label col-md-2'>
                            <?php eT("Scope:"); ?>
                        </label>
                        <div class='col-md-10 ls-flex wrap'>
                            <div class="btn-group" role="group">
                                <input class='btn-check' type='radio' id='radiototal' name='scope' value='T' checked='checked'/>
                                <label class='btn btn-outline-secondary' for="radiototal"><?php eT("Total"); ?></label>

                                <input class='btn-check' type='radio' id='radiogroup' name='scope' value='G'/>
                                <label class='btn btn-outline-secondary' for="radiogroup"><?php eT("Group"); ?></label>
                            </div>
                        </div>
                    </div>
                    <!-- Question group -->
                    <div class='mb-3 col-12 row'>
                        <label class='form-label col-md-2' for='gid'>
                            <?php eT("Question group:"); ?>
                        </label>
                        <div class='col-md-10'>
                            <?php if (isset($groups)) { ?>
                                <select name='gid' id='gid' class="form-select">
                                    <?php foreach ($groups as $groupId => $groupName) { ?>
                                        <option value="<?= $groupId ?>"><?= flattenText($groupName) ?></option>
                                    <?php } ?>
                                </select>
                            <?php } else {
                                echo eT("No question group found.");
                            } ?>
                        </div>
                        <div class='col-md-2 hide-xs'></div>
                    </div>

                </div>

                <!-- Minimum -->
                <div class='mb-3 col-12 row'>
                    <label class='form-label col-md-2' for='minimum'>
                        <?php eT("Minimum:"); ?>
                    </label>
                    <div class='col-md-10'>
                        <input class='form-control numbersonly' type='text' id='minimum' name='minimum'/>
                    </div>
                    <div class='col-md-2 hide-xs'></div>
                </div>

                <!-- Maximum -->
                <div class='mb-3 col-12 row'>
                    <label class='form-label col-md-2' for='maximum'>
                        <?php eT("Maximum:"); ?>
                    </label>
                    <div class='col-md-10'>
                        <input class='form-control numbersonly' type='text' id='maximum' name='maximum'/>
                    </div>
                    <div class='col-md-2 hide-xs'></div>
                </div>

                <!-- Languages tabs -->
                <div id="languagetabs" class="row">
                    <ul class="nav nav-tabs" id="edit-survey-text-element-language-selection">
                        <?php foreach ($assessmentlangs as $assessmentlang) {
                            $position = 0;
                            ?>
                            <li role="presentation" class="nav-item">
                                <a class="nav-link <?= ($assessmentlang == $baselang ? 'active' : '') ?>" data-bs-toggle="tab" href="#tablang<?= $assessmentlang ?>">
                                    <?php
                                    echo getLanguageNameFromCode($assessmentlang, false);
                                    if ($assessmentlang == $baselang) {
                                        echo ' (' . gT("Base language") . ')';
                                    }
                                    ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>

                    <div class="tab-content">
                        <?php
                        $count = 0;
                        foreach ($assessmentlangs as $assessmentlang) {
                            $heading = '';
                            $message = '';
                            ?>
                            <div id="tablang<?= $assessmentlang; ?>" class="tab-pane fade <?php if ($count == 0) {
                                echo "show active ";
                                $count++;
                            } ?>">
                                <div class='col-12'></div>
                                <div class='mb-3 col-12'>
                                    <label class='form-label col-12' for='name_<?= $assessmentlang ?>'>
                                        <?php eT("Heading"); ?>:</label>
                                    <div class='col-12'>
                                        <input class='form-control' type='text' name='name_<?= $assessmentlang ?>' id='name_<?= $assessmentlang ?>>' size='80' value='<?= $heading ?>'/>
                                    </div>
                                </div>
                                <div class='mb-3 col-12'>
                                    <label class='form-label col-12' for='assessmentmessage_<?= $assessmentlang ?>'>
                                        <?php eT("Message"); ?>:</label>
                                    <div class='col-12'>
                                        <div class="htmleditor input-group">
                                            <textarea name='assessmentmessage_<?= $assessmentlang ?>' class="form-control" id='assessmentmessage_<?= $assessmentlang ?>' rows='10'><?php echo $message; ?></textarea>
                                            <?php echo getEditor("assessment-text", "assessmentmessage_" . $assessmentlang, "[" . gT("Message:", "js") . "]", $surveyid, $gid, null, ''); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class='mb-3'>
                                    <div class='col-md-2'></div>
                                    <div class='col-md-4'>
                                        <input type='submit' class="btn btn-outline-secondary d-none" value='<?php eT("Save"); ?>'/>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- action buttons -->
                <div>
                    <input type='hidden' name='sid' value='<?php echo $surveyid; ?>'/>
                    <input type='hidden' name='action' value='assessmentadd'/>
                    <input type='hidden' name='id' value='<?php echo $editId; ?>'/>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal"><?php eT("Cancel"); ?></button>
                <button type="button" class="btn btn-primary" id="selector__assessments-save-modal">
                    <?php eT('Add'); ?>
                </button>
            </div>
            </form>
        </div>
    </div>
</div>
