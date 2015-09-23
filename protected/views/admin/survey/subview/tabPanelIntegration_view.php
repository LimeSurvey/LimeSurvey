<?php
	$yii = Yii::app();
	$controller = $yii->getController();
?>
<script type="text/javascript">
    var jsonUrl = "<?php echo App()->createUrl('admin/survey', array('sa' => 'getUrlParamsJson', 'surveyid' => $surveyid))?>";
    var imageUrl = "<?php echo $yii->getConfig("adminimageurl");?>";
    var sAction = "<?php eT('Action','js');?>";
    var sParameter = "<?php eT('Parameter','js');?>";
    var sTargetQuestion = "<?php eT('Target question','js');?>";
    var sURLParameters = "<?php eT('URL parameters','js');?>";
    var sNoParametersDefined = "<?php eT('No parameters defined','js');?>";
    var sSureDelete = "<?php eT('Are you sure you want to delete this URL parameter?','js');?>";
    var sEnterValidParam = "<?php eT('You have to enter a valid parameter name.','js');?>";
    var sAddParam = "<?php eT('Add URL parameter','js');?>";
    var sEditParam = "<?php eT('Edit URL parameter','js');?>";

</script>

<div id='panelintegration'>

    <table id="urlparams" style='margin:0 auto;'><tr><td>&nbsp;</td></tr></table>
    <div id="pagerurlparams"></div>
    <input type='hidden' id='allurlparams' name='allurlparams' value='' />

</div>