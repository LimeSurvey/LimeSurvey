<?php
/**
 * List with comment, dropdown style, header Html
 * @var $name                           $ia[1]
 * @var $id                             answer'.$ia[1].'
 * @var $checkconditionFunction         $checkconditionFunction.'(this.value, this.name, this.type)
 * @var $show_noanswer                  is_null($_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]])
 */
?>
<p class="select answer-item dropdown-item">
    <select
            class="select form-control"
            name="<?php echo $name;?>"
            id="<?php echo $id;?>"
            onchange="<?php echo $checkconditionFunction;?>" >

<?php if($show_noanswer):?>
    <option class="noanswer-item" value="" SELECTED>
        <?php eT('Please choose...');?>
    </option>
<?php endif;?>
