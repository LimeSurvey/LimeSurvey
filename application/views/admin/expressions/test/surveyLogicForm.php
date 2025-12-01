<?php
/**
 * Outsourced get logic file form
 */
?>

<div id='edit-survey-text-element' class='side-body'>
    <h3><?php eT("Edit survey settings");?></h3>
    <div class="container">
        <div class="row">
            <div class="col-12 content-right">
                <h3><?= gT('Check logic for the survey'); ?></h3>
                <?=TbHtml::form(array('admin/expressions/sa/survey_logic_file'), 'post')?>
                    <div class="mb-3">
                        <label class="form-label" for="sid"> <?=gT('Survey ID (SID)')?></label>
                        <select name="sid" id="sid" class="form-select">
                            <?=getSurveyList()?>
                        </select>
                    </div>
                    <h4><?= gT('Debug log level'); ?></h4>
                    <h5><?= gT('Specify which debugging features to use'); ?></h5>
                    <div class="mb-3">
                        <?php echo TbHtml::label(gT("Detailed timing"), 'LEM_DEBUG_TIMING', ['class' => " form-label"]); ?>
                        <div>
                            <?php
                            $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                'name'          => 'LEM_DEBUG_TIMING',
                                'checkedOption' => 0,
                                'selectOptions' => [
                                    '1' => gT("Yes", 'unescaped'),
                                    '0' => gT("No", 'unescaped'),
                                ],
                            ]); ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <?php echo TbHtml::label(gT("Validation summary"), 'LEM_DEBUG_VALIDATION_SUMMARY', ['class' => " form-label"]); ?>
                        <div>
                            <?php
                            $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                'name'          => 'LEM_DEBUG_VALIDATION_SUMMARY',
                                'checkedOption' => 0,
                                'selectOptions' => [
                                    '1' => gT("Yes", 'unescaped'),
                                    '0' => gT("No", 'unescaped'),
                                ],
                            ]); ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <?php echo TbHtml::label(gT("Validation detail (Validation summary must also be checked to see detail)"), 'LEM_DEBUG_VALIDATION_DETAIL', ['class' => " form-label"]); ?>
                        <div>
                            <?php
                            $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                'name'          => 'LEM_DEBUG_VALIDATION_DETAIL',
                                'checkedOption' => 0,
                                'selectOptions' => [
                                    '1' => gT("Yes", 'unescaped'),
                                    '0' => gT("No", 'unescaped'),
                                ],
                            ]); ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <?php echo TbHtml::label(gT("Pretty print syntax"), 'LEM_PRETTY_PRINT_ALL_SYNTAX', ['class' => " form-label"]); ?>
                        <div>
                            <?php
                            $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', [
                                'name'          => 'LEM_PRETTY_PRINT_ALL_SYNTAX',
                                'checkedOption' => 1,
                                'selectOptions' => [
                                    '1' => gT("Yes", 'unescaped'),
                                    '0' => gT("No", 'unescaped'),
                                ],
                            ]); ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="submit" class="btn btn-outline-secondary"/>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
