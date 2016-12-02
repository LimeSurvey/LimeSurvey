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
        <input
            type="hidden"
            name="java<?php echo $myfname2;?>"
            id="java<?php echo $myfname2;?>"
            value="<?php echo $value; ?>"
        />

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
            <input
                type='text'
                class="multiflexitext form-control <?php echo $kpclass;?> text-right"
                name="<?php echo $myfname2; ?>"
                id="answer<?php echo $myfname2;?>"
                <?php echo ($inputsize ? 'size="'.$inputsize.'"': '') ; ?>
                <?php echo ($maxlength ? 'maxlength='.$maxlength: ''); ?>
                value="<?php echo $value; ?>"
                data-number="true"
                />
        <?php endif; ?>
</td>
<!-- end of answer_td -->
