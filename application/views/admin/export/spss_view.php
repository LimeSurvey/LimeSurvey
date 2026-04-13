<?php
/**
 * Export results to SPSS view
 * @var AdminController $this
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('exportSpss');

?>
<div class='side-body'>
    <h3><?php eT("Export response data to SPSS");?></h3>
    <?php echo CHtml::form(array("admin/export/sa/exportspss/sid/{$surveyid}/"), 'post', array('id'=>'exportspss', 'class'=>''));?>
    <div class="mb-3 row">
        <label for='completionstate' class='col-md-2  form-form-label'><?php eT("Data selection:");?></label>
        <div class="col-md-10">
            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', array(
                'name' => 'completionstate',
                'checkedOption'=> 'all' ,
                'selectOptions'=>array(
                    "all"=>gT("All responses",'unescaped'),
                    "complete"=>gT("Complete only",'unescaped'),
                    "incomplete"=>gT("Incomplete only",'unescaped'),
                )
            ));?>
        </div>
    </div>
    <div class="mb-3 row">
        <label for='spssver'  class='col-sm-2  form-label'><?php eT("SPSS version:");?></label>
        <div class="col-md-10">
            <?php $this->widget('ext.ButtonGroupWidget.ButtonGroupWidget', array(
                'name' => 'spssver',
                'checkedOption'=> $spssver ,
                'selectOptions'=> array(
                    "1"=>gT("Prior to 16 / PSPP",'unescaped'),
                    "2"=>gT("16 or up",'unescaped'),
                    "3"=>gT("16 or up with Python Plugin / Essentials",'unescaped')
                )
            ));?>
        </div>
    </div>
    <?php
    if (count($aLanguages)>1)
    { ?>
        <div class="mb-3 row">
            <label for='exportlang'  class='col-md-2  form-form-label'><?php eT("Language:");?></label>
            <div class="col-md-2">
                <?php echo CHtml::dropDownList('exportlang', $sBaseLanguage, $aLanguages, array('class'=>'form-select')); ?>
            </div>
        </div>
        <?php } else { ?>
            <?php echo CHtml::hiddenField('exportlang', $sBaseLanguage); ?>

        <?php } ?>
    <div class="mb-3 row">
        <label for='limit' class='col-md-2  form-form-label'><?php eT("Limit:");?></label>
        <div class="col-md-1">
            <?php
                echo CHtml::textField('limit',App()->getRequest()->getParam('limit'),array('class'=>'form-control'));
            ?>
        </div>
    </div>
    <div class="mb-3 row">
        <label for='offset' class='col-md-2  form-form-label'><?php eT("Offset:");?></label>
        <div class="col-md-1">
            <?php
                echo CHtml::textField('offset',App()->getRequest()->getParam('offset'),array('class'=>'form-control'));
            ?>
        </div>
    </div>

    <div class="mb-3 row">
        <label for='offset' class='col-md-2  form-form-label'><?php eT("No answer:");?></label>
        <div class="col-md-1">
            <?php
                echo CHtml::textField('noanswervalue',App()->getRequest()->getParam('noanswervalue'),array('class'=>'form-control'));
            ?>
        </div>
    </div>


    <div class="mb-3 row">
        <?php
            echo CHtml::hiddenField('sid',$surveyid);
            echo CHtml::hiddenField('action','exportspss');
        ?>
        <label for='dlstructure' class='col-md-1 form-form-label'><?php eT("Step 1:");?></label>
        <div class="col-md-10">
            <input class="btn btn-outline-secondary" type='submit' name='dlstructure' id='dlstructure' value='<?php eT("Export syntax");?>'/>
        </div>
    </div>
    <div class="mb-3 row">
        <label for='dldata' class='col-md-1  form-form-label'><?php eT("Step 2:");?></label>
        <div class="col-md-10">
            <input class="btn btn-outline-secondary" type='submit' name='dldata' id='dldata' value='<?php eT("Export data");?>'/>
        </div>
    </div>
    </form>
    <?php
    $message = '<div>' .
        '<p class="mb-3">' . gT("Instructions for the impatient") . ':</p>' .
        '<ol>' .
        '<li>' . gT("Download the data and the syntax file.") . '</li>' .
    '<li>' . gT("Open the syntax file in SPSS in Unicode mode.") . '</li>' .
    '<em>' . gT("The next step (editing the 'FILE=' line) is only necessary when you have selected a SPSS version without Python. If you selected the version for SPSS with the Python plugin / Essentials, just save the syntax and the data file in the same folder. If you use Python 3 you need to edit the syntax file: replace the line 'begin program.' with 'begin program PYTHON3.'. The full path will be automatically detected when you run the syntax.") . '</em>' .
    '<li>' . sprintf(gT("Edit the line starting with %s and complete the filename with a full path to the downloaded data file."), "'FILE='") . '</li>' .
    '<li>' . gT("Choose 'Run/All' from the menu to run the import.") . '</li>' .
    '</ol>' .
        gT("Your data should be imported now.") .
        '</div>';

    $this->widget('ext.AlertWidget.AlertWidget', [
        'text' => $message,
        'type' => 'info',
        'showCloseButton' => false,
    ]);
    ?>
</div>
