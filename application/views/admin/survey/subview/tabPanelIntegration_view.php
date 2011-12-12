<?php
	$yii = Yii::app();
	$controller = $yii->getController();
?>
<script type="text/javascript">
    var jsonUrl = "<?php echo $yii->homeUrl.("/admin/survey/sa/getUrlParamsJSON/surveyid/{$surveyid}");?>";
    var imageUrl = "<?php echo $yii->getConfig("imageurl");?>";
    var sAction = "<?php $clang->eT('Action','js');?>";
    var sParameter = "<?php $clang->eT('Parameter','js');?>";
    var sTargetQuestion = "<?php $clang->eT('Target question','js');?>";
    var sURLParameters = "<?php $clang->eT('URL parameters','js');?>";
    var sNoParametersDefined = "<?php $clang->eT('No parameters defined','js');?>";
    var sSureDelete = "<?php $clang->eT('Are you sure you want to delete this URL parameter?','js');?>";
    var sEnterValidParam = "<?php $clang->eT('You have to enter a valid parameter name.','js');?>";
    var sAddParam = "<?php $clang->eT('Add URL parameter','js');?>";
    var sEditParam = "<?php $clang->eT('Edit URL parameter','js');?>";

</script>

<div id='panelintegration'>

    <table id="urlparams" style='margin:0 auto;'><tr><td>&nbsp;</td></tr></table>
    <div id="pagerurlparams"></div>
    <input type='hidden' id='allurlparams' name='allurlparams' value='' />

</div>