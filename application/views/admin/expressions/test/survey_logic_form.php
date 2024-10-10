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
                <h3>Generate a logic file for the survey</h3>
                <?=TbHtml::form(array('admin/expressions/sa/survey_logic_file'), 'post')?>
                    <div class="mb-3">
                        <label class="form-label" for="sid"> <?=gT('Survey ID (SID)')?></label>
                        <select name="sid" id="sid" class="form-select">
                            <?=getSurveyList()?>
                        </select>
                    </div>
                    <h4>Debug Log Level</h4>
                    <h5>Specify which debugging features to use</h5>
                    <div class="mb-3">
                        <label for="LEM_DEBUG_TIMING">Detailed Timing</lable>
                        <input type="checkbox" class="form-control" name="LEM_DEBUG_TIMING" id="LEM_DEBUG_TIMING" value="1"/>
                    </div>
                    <div class="mb-3">
                        <label for="LEM_DEBUG_VALIDATION_SUMMARY">Validation Summary</label>
                        <input class="form-control" type="checkbox" name="LEM_DEBUG_VALIDATION_SUMMARY" id="LEM_DEBUG_VALIDATION_SUMMARY" value="1"/>
                    </div>
                    <div class="mb-3">
                        <label for="LEM_DEBUG_VALIDATION_DETAIL">Validation Detail (Validation Summary must also be checked to see detail)</label>
                        <input class="form-control" type="checkbox" name="LEM_DEBUG_VALIDATION_DETAIL" id="LEM_DEBUG_VALIDATION_DETAIL" value="1"/>
                    </div>
                    <div class="mb-3">
                        <label for="LEM_PRETTY_PRINT_ALL_SYNTAX">Pretty Print Syntax</label>
                        <input class="form-control" type="checkbox" name="LEM_PRETTY_PRINT_ALL_SYNTAX" id="LEM_PRETTY_PRINT_ALL_SYNTAX" value="1" checked="checked"/>
                    </div>
                    <div class="mb-3">
                        <input type="submit" class="btn btn-outline-secondary"/></td>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
