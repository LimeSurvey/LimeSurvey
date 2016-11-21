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
 * @var sgq : basename for input
 * @todo : control if dropdown need labelledby or if labelledby in group is OK. Think automatic system return needed ...
 */
?>

<div class="ls-answers row" role="group" aria-labelledby="ls-question-text-<?php echo $basename; ?>">

    <div class="answer-item dropdown-item col-sm-6 col-xs-12">
        <select
                class="select form-control"
                name="<?php echo $name;?>"
                id="<?php echo $id;?>"
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
        <!-- Input copy for EM : default is radio and EM use id -->
        <input type="hidden" name="java<?php echo $name?>" id="java<?php echo $name?>" value="<?php echo $value;?>" disabled />
    </div>

    <div class="answer-item text-item col-sm-6 col-xs-12">
        <label for="answer<?php echo $name ;?>comment">
            <?php echo $label_text;?>:
        </label>
        <textarea
            class="form-control textarea <?php echo $kpclass; ?>"
            name="<?php echo $name;?>comment"
            id="answer<?php echo $name ;?>comment"
            rows="<?php echo $tarows; ?>"
            cols="<?php echo $maxoptionsize; ?>"
        ><?php echo $comment_saved; ?></textarea>

    </div>
</div>
