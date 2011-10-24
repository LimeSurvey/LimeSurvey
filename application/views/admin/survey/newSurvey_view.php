<?php PrepareEditorScript(); ?>
<script type="text/javascript">
    standardtemplaterooturl='<?php echo $this->config->item('standardtemplaterooturl');?>';
    templaterooturl='<?php echo $this->config->item('usertemplaterooturl');?>';
</script>
<div class='header ui-widget-header'><?php $clang->eT("Create, import, or copy survey"); ?></div>
<?php
    $data['clang'] = $clang;
    $data['action'] = $action;
    $this->load->view('admin/survey/subview/tab_view',$data);
    $this->load->view('admin/survey/subview/tabGeneralNewSurvey_view',$data);
    $this->load->view('admin/survey/subview/tabPresentation_view',$data);
    $this->load->view('admin/survey/subview/tabPublication_view',$data);
    $this->load->view('admin/survey/subview/tabNotification_view',$data);
    $this->load->view('admin/survey/subview/tabTokens_view',$data);
?>

<input type='hidden' id='surveysettingsaction' name='action' value='insertsurvey' />
</form>
<?php
    $this->load->view('admin/survey/subview/tabImport_view',$data);
    $this->load->view('admin/survey/subview/tabCopy_view',$data);
?>
</div>

<p><button onclick="if (isEmpty(document.getElementById('surveyls_title'), '<?php $clang->eT("Error: You have to enter a title for this survey.", 'js');?>')) { document.getElementById('addnewsurvey').submit();}" class='standardbtn' >
        <?php $clang->eT("Save");?>
    </button>
</p>

