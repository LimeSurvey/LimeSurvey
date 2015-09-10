<?php
	extract($data);
	Yii::app()->loadHelper('admin/htmleditor');
	PrepareEditorScript(false, $this);
?>
<script type="text/javascript">
    standardtemplaterooturl='<?php echo Yii::app()->getConfig('standardtemplaterooturl');?>';
    templaterooturl='<?php echo Yii::app()->getConfig('usertemplaterooturl');?>';
</script>

<h3 class="pagetitle"><?php eT("Create, import, or copy survey"); ?></h3>
		
<div class="row" style="margin-bottom: 100px">
	<div class="col-lg-12 content-right">
		<?php $this->renderPartial('/admin/survey/subview/tab_create_view',$data); ?>
			<div class="tab-content">
				<div id="general" class="tab-pane fade in active">
					<?php $this->renderPartial('/admin/survey/subview/tabCreate_view',array('data'=>$data));?>
				</div>
				
				<?php
				    $this->renderPartial('/admin/survey/subview/tabImport_view',$data);
				    $this->renderPartial('/admin/survey/subview/tabCopy_view',$data);
				?>
			</div>
	</div>
</div>
		


