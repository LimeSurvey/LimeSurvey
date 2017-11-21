<?php
/**
 * Cell when checkbox layout is not used in advanced question attribute
 *
 * @var $dataTitle
 * @var $ld
 * @var $answertypeclass
 * @var $extraclass
 * @var $myfname2
 * @var $answertext
 * @var $error
 * @var $myfname2_java_value
 * @var $inputboxlayout
 * @var $checkconditionFunction
 * @var $minvalue
 * @var $maxvalue
 * @var $reverse
 * @var $value
 * @var $sSeparator
 * @var $kpclass
 * @var $maxlength
 */
?>

<!-- answer_td -->
<td class="answer_cell_<?php echo $ld;?> answer-item <?php echo $answertypeclass; ?> <?php echo $extraclass; ?><?php if($error){ echo " has-error";} ?>">
        <?php
        /* Value for expression manager javascript ; no need to submit */
        echo \CHtml::hiddenField("java{$myfname2}",$value,array(
            'id' => "java{$myfname2}",
            'disabled' => true,
        ));
        ?>
        <label for="answer<?php echo $myfname2;?>" class='ls-label-xs-visibility'>
            <?php echo $dataTitle;?>
        </label>
        <?php if($inputboxlayout == false):?>
            <!-- InputBox Layout False -->
            <select
                class="multiflexiselect form-control text-right"
                name="<?php echo $myfname2; ?>"
                id="answer<?php echo $myfname2;?>"
            >
                <option value="">
                    <?php eT('...'); ?>
                </option>
                <?php for($ii=$minvalue; ($reverse?$ii>=$maxvalue:$ii<=$maxvalue); $ii+=$stepvalue): ?>
                    <?php $selected = (isset($value) && (string) $value == (string)$ii)?'SELECTED':''; ?>
                    <option value="<?php echo str_replace('.',$sSeparator,$ii); ?>" <?php echo $selected;?>>
                        <?php echo str_replace('.',$sSeparator,$ii); ?>
                    </option>
                <?php endfor; ?>
            </select>
        <?php elseif($inputboxlayout == true): ?>
            <!-- InputBox Layout -->
            <?php
            echo \CHtml::textField($myfname2,$value,array(
                'id' => "answer{$myfname2}",
                'class' => "multiflexitext form-control {$answertypeclass} text-right",
                'title' => gT('Only numbers may be entered in this field.'),
                'size' => ($inputsize ? $inputsize : null),
                'maxlength' => ($maxlength ? $maxlength : null),
                'data-number' => 1,
            ));
            ?>
        <?php endif; ?>
</td>
<!-- end of answer_td -->
