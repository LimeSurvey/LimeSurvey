<?php
/**
 * Cell when checkbox layout is used in advanced question attribute
 *
 * @var $dataTitle
 * @var $ld
 * @var $answertypeclass
 * @var $value $myvalue
 * @var $myfname2
 * @var $setmyvalue
 * @var $checkconditionFunction
 * @var $extraclass
 */
?>

<!-- answer_td_checkboxes -->
<td class="answer_cell_<?php echo $ld; ?> question-item answer-item <?php echo $answertypeclass;?>-item checkbox-item">
    <?php
    /* Value for expression manager javascript ; no need to submit */
    echo \CHtml::hiddenField("java{$myfname2}",$value,array(
        'id' => "java{$myfname2}",
        'disabled' => true,
    ));
    ?>
    <?php
    /* Value submited by default, replaced by next if checked */
    /* EM use javaXXXX, no need updating the value of this one, just submit '' if other one is unchecked , review JS ? */
    /* See http://www.yiiframework.com/doc/api/1.1/CHtml#checkBox-detail width uncheckValue for replacing ? */
    /* Add prefix 'answer' to make question relevance work in LEMval(). */
    echo \CHtml::hiddenField('answer' . $myfname2,"",array(
        'class' => "hidden",
    ));
    echo \CHtml::checkBox($myfname2,$value,array(
        'class' => $extraclass,
        'id' => "cbox_{$myfname2}",
        'value' => "1"
    ));
    ?>
    <label for="cbox_<?php echo $myfname2;?>" class="ls-label-xs-visibility">
        <?php echo $dataTitle;?>
    </label>
</td>
<!-- end of answer_td_checkboxes -->
