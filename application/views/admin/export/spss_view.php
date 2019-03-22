<?php
/**
 * Export results to SPSS view
 * @var AdminController $this
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('exportSpss');

?>
<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3><?php eT("Export response data to SPSS");?></h3>
    <?php echo CHtml::form(array("admin/export/sa/exportspss/sid/{$surveyid}/"), 'post', array('id'=>'exportspss', 'class'=>''));?>
    <div class="form-group row">
        <label for='completionstate' class='col-sm-2  form-control-label'><?php eT("Data selection:");?></label>
        <div class="col-sm-10">
            <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                'name' => 'completionstate',
                'value'=> 'all' ,
                'selectOptions'=>array(
                    "all"=>gT("All responses",'unescaped'),
                    "complete"=>gT("Complete only",'unescaped'),
                    "incomplete"=>gT("Incomplete only",'unescaped'),
                )
            ));?>
        </div>
    </div>
    <div class="form-group row">
        <label for='spssver'  class='col-sm-2  form-control-label'><?php eT("SPSS version:");?></label>
        <div class="col-sm-10">
            <?php $this->widget('yiiwheels.widgets.buttongroup.WhButtonGroup', array(
                'name' => 'spssver',
                'value'=> $spssver ,
                'selectOptions'=>array(
                    "1"=>gT("Prior to 16",'unescaped'),
                    "2"=>gT("16 or up",'unescaped')
                )
            ));?>
        </div>
    </div>
    <?php
    if (count($aLanguages)>1)
    { ?>
        <div class="form-group row">
            <label for='exportlang'  class='col-sm-2  form-control-label'><?php eT("Language:");?></label>
            <div class="col-sm-2">
                <?php echo CHtml::dropDownList('exportlang', $sBaseLanguage, $aLanguages, array('class'=>'form-control')); ?>
            </div>
        </div>
        <?php } else { ?>
            <?php echo CHtml::hiddenField('exportlang', $sBaseLanguage); ?>

        <?php } ?>
    <div class="form-group row">
        <label for='limit' class='col-sm-2  form-control-label'><?php eT("Limit:");?></label>
        <div class="col-sm-1">
            <?php
                echo CHtml::textField('limit',App()->getRequest()->getParam('limit'),array('class'=>'form-control'));
            ?>
        </div>
    </div>
    <div class="form-group row">
        <label for='offset' class='col-sm-2  form-control-label'><?php eT("Offset:");?></label>
        <div class="col-sm-1">
            <?php
                echo CHtml::textField('offset',App()->getRequest()->getParam('offset'),array('class'=>'form-control'));
            ?>
        </div>
    </div>

    <div class="form-group row">
        <label for='offset' class='col-sm-2  form-control-label'><?php eT("No answer:");?></label>
        <div class="col-sm-1">
            <?php
                echo CHtml::textField('noanswervalue',App()->getRequest()->getParam('noanswervalue'),array('class'=>'form-control'));
            ?>
        </div>
    </div>


    <div class="form-group row">
        <?php
            echo CHtml::hiddenField('sid',$surveyid);
            echo CHtml::hiddenField('action','exportspss');
        ?>
        <label for='dlstructure' class='col-sm-1 form-control-label'><?php eT("Step 1:");?></label>
        <div class="col-sm-10">
            <input class="btn btn-default" type='submit' name='dlstructure' id='dlstructure' value='<?php eT("Export syntax");?>'/>
        </div>
    </div>
    <div class="form-group row">
        <label for='dldata' class='col-sm-1  form-control-label'><?php eT("Step 2:");?></label>
        <div class="col-sm-10">
            <input class="btn btn-default" type='submit' name='dldata' id='dldata' value='<?php eT("Export data");?>'/>
        </div>
    </div>
    </form>

    <p>
    <div class="alert alert-info" role="alert"><?php eT("Instructions for the impatient");?> :
        <br/><br/>
        <ol>
            <li><?php eT("Download the data and the syntax file.");?></li>
            <li><?php eT("Open the syntax file in SPSS in Unicode mode.");?></li>
            <li><?php echo sprintf(gT("Edit the %s line and complete the filename with a full path to the downloaded data file."),"'FILE='");?></li>
            <li><?php eT("Choose 'Run/All' from the menu to run the import.");?></li>
        </ol>
    <?php eT("Your data should be imported now.");?></div>
</div>
<p>
