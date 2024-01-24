<?php
/**
 * Create survey
 * @var SurveyAdministrationController $this
 * @var Survey $oSurvey
 * @var array $arrayed_data
 */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('createSurvey');

?>
<!-- new survey view -->
<?php
extract($arrayed_data, EXTR_OVERWRITE);
$active = Yii::app()->request->getParam('tab', 'create');
?>
<script type="text/javascript">
    standardthemerooturl = '<?php echo Yii::app()->getConfig('standardthemerooturl');?>';
    templaterooturl = '<?php echo Yii::app()->getConfig('userthemerooturl');?>';
</script>
<div class="row">
    <div class="col-12">
        <!-- tabs -->
        <?php $this->renderPartial('tab_survey_view', $data); ?>

        <!-- tabs content -->
        <div class="tab-content">
            <!-- General Tab (contains accrodion) -->
            <div id="general" class="tab-pane fade <?= $active === 'create' ? 'show active' : '' ?>">
                <?php $this->renderPartial('tabCreate_view', ['data' => $data]); ?>
            </div>

            <!-- Import -->
            <div id='import' class="tab-pane fade <?= $active === 'import' ? 'show active' : '' ?>">
                <?php $this->renderPartial('tabImport_view', $data); ?>
            </div>

            <!-- Copy -->
            <div id='copy' class="tab-pane fade <?= $active === 'copy' ? 'show active' : '' ?>">
                <?php $this->renderPartial('tabCopy_view', $data); ?>
            </div>
        </div>
    </div>
</div>


