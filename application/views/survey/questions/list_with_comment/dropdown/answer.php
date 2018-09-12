<?php
/**
 * List with comment, dropdown layout, Html
 *
 * @var $sOptions         : the select options, generated with the view rows/option.php
 *
 * @var $name
 * @var $id
 * @var $checkconditionFunction
 * @var $show_noanswer
 * @var $label_text
 * @var $kpclass
 * @var $tarows
 * @var $maxoptionsize
 * @var $has_comment_saved
 * @var $comment_saved      htmlspecialchars( $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$fname2])
 * @var $value     $_SESSION['survey_'.Yii::app()->getConfig('surveyID')][$ia[1]] /// SHOULD BE CALL VALUE

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

            <?php
                // rows/option.php
                echo $sOptions;
            ?>

    </select>
</p>


<p class="comment answer-item text-item">
    <label for="answer<?php echo $name ;?>comment">
        <?php echo $label_text;?>:
    </label>

    <textarea
                        class="form-control textarea <?php echo $kpclass; ?>"
                        name="<?php echo $name;?>comment"
                        id="answer<?php echo $name ;?>comment"
                        rows="<?php echo $tarows; ?>"
                        cols="<?php echo $maxoptionsize; ?>"
    ><?php if($has_comment_saved):?><?php echo $comment_saved; ?><?php endif;?></textarea>

    <input class="radio" type="hidden" name="java<?php echo $name?>" id="java<?php echo $name?>" value="<?php echo $value;?>" />
</p>
