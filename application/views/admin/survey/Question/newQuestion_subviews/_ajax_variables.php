<?php
/**
 * This view render the needed variables for the ajax process of the creation of a new question
 */
?>
<script type='text/javascript'>
    var attr_url = "<?php echo $this->createUrl('admin/questions', array('sa' => 'ajaxquestionattributes')); ?>";
    var imgurl = '<?php echo Yii::app()->getConfig('imageurl'); ?>';
    var validateUrl = "<?php echo $sValidateUrl; ?>";
    <?php echo $qTypeOutput; ?>
</script>
