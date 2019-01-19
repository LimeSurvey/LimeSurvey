<?php
/**
 * Export result view
 * @var AdminController $this
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('exportResults');

$scriptBegin = "var sMsgColumnCount = '".gT("%s of %s columns selected",'js')."';";
App()->getClientScript()->registerScript('ExportresultsVariables', $scriptBegin, LSYii_ClientScript::POS_BEGIN);


?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <h3>
        <?php eT("Export results");?>
        <?php
            if (isset($_POST['sql'])) {echo" - ".gT("Filtered from statistics script");}
            if ($SingleResponse)
            {
                echo " - ".sprintf(gT("Single response: ID %s"),$SingleResponse);
            }
        ?>
    </h3>

    <?php echo CHtml::form(array('admin/export/sa/exportresults/surveyid/'.$surveyid), 'post', array('id'=>'resultexport', 'class'=>''));?>
        <div class="row">
            <div class="col-sm-12 content-right">
                <div class="row">
                    <div class="col-sm-12 col-md-6">
                        <?php $this->renderPartial('/admin/export/exportresult_panels/_format', ['exports' => $exports,'defaultexport' => $defaultexport,'aCsvFieldSeparator' => $aCsvFieldSeparator ]); ?> 
                        <?php $this->renderPartial('/admin/export/exportresult_panels/_general', [ 'selecthide'  => $selecthide, 'selectshow'  => $selectshow, 'selectinc'  => $selectinc, 'aLanguages'  => $aLanguages]); ?>   
                        
                        <?php if (empty(Yii::app()->session['responsesid'])): // If called from massive action, it will be filled the selected answers ?>
                            <?php $this->renderPartial('/admin/export/exportresult_panels/_range', ['SingleResponse' => $SingleResponse, 'min_datasets' => $min_datasets, 'max_datasets' => $max_datasets]); ?> 
                        <?php else: ?>
                            <?php $this->renderPartial('/admin/export/exportresult_panels/_single-value', ['SingleResponse' => $SingleResponse, 'surveyid' => $surveyid]); ?> 
                        <?php endif;?>
                        
                        <?php $this->renderPartial('/admin/export/exportresult_panels/_responses', ['surveyid' => $surveyid]); ?> 
                        
                    </div>
                    <div class="col-sm-12 col-md-6">
                        <?php $this->renderPartial('/admin/export/exportresult_panels/_headings', [ 'headexports'  => $headexports]); ?>   
                        <?php $this->renderPartial('/admin/export/exportresult_panels/_columns-control', [ 'surveyid' => $surveyid, 'SingleResponse' => $SingleResponse, 'aFields' => $aFields, 'aFieldsOptions' => $aFieldsOptions]); ?>   
                        
                        <!-- Token control -->
                        <?php if ($thissurvey['anonymized'] == "N" && tableExists("{{tokens_$surveyid}}") && Permission::model()->hasSurveyPermission($surveyid,'tokens','read')): ?>
                            <?php $this->renderPartial('/admin/export/exportresult_panels/_token-control', ['surveyid' => $surveyid]); ?>
                        <?php endif;?>
                    </div>
                </div>
            </div>
        </div>
        <input type='submit' class="btn btn-default hidden" value='<?php eT("Export data");?>' id='exportresultsubmitbutton' />
    </form>
</div>
<?php
App()->getClientScript()->registerScript('ExportResultsBSSwitcher', "
LS.renderBootstrapSwitch();
", LSYii_ClientScript::POS_POSTSCRIPT);
?>