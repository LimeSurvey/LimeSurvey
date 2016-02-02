<?php
/**
 * Create survey
 */
?>
<!-- new survey view -->
<?php
    extract($data);
    Yii::app()->loadHelper('admin/htmleditor');
    PrepareEditorScript(false, $this);
    $active = (isset($_GET['tab']))?$_GET['tab']:'create';
?>
<script type="text/javascript">
    standardtemplaterooturl='<?php echo Yii::app()->getConfig('standardtemplaterooturl');?>';
    templaterooturl='<?php echo Yii::app()->getConfig('usertemplaterooturl');?>';
</script>

<h3 class="pagetitle"><?php eT("Create, import, or copy survey"); ?></h3>
<div class="row" style="margin-bottom: 100px">
    <div class="col-lg-12">
        <!-- tabs -->
        <?php $this->renderPartial('/admin/survey/subview/tab_survey_view',$data); ?>

        <!-- tabs content -->
        <div class="tab-content">
            <!-- General Tab (contains accrodion) -->
            <div id="general" class="tab-pane fade in <?php if($active=='create'){echo ' active ';}?>">
                <?php $this->renderPartial('/admin/survey/subview/tabCreate_view',array('data'=>$data));?>
            </div>

            <!-- Import -->
            <div id='import'  class="tab-pane fade in <?php if($active=='import'){echo ' active ';}?>">
                <?php $this->renderPartial('/admin/survey/subview/tabImport_view',$data); ?>
            </div>

            <!-- Copy -->
            <div id='copy' class="tab-pane fade in <?php if($active=='copy'){echo ' active ';}?>" >
                <?php $this->renderPartial('/admin/survey/subview/tabCopy_view',$data); ?>
            </div>
        </div>
    </div>
</div>
