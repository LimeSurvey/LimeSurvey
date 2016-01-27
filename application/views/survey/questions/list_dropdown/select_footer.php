<?php
/**
 * List DropDown select footer Html
 * @var $name       $ia[1]
 * @var $value      $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]]
 */
?>
<!-- list_dropdown select_footer -->
</select>
<input
    type="hidden"
    name="java<?php echo $name; ?>"
    id="java<?php echo $name; ?>"
    value="<?php echo $value; ?>"
/>
