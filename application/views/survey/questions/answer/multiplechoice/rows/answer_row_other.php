<?php
/**
 * Multiple Choice Html : item 'other' row
 *
 * @var $sDisable
 * @var $myfname
 * @var $othertext
 * @var $checkedState
 * @var $kpclass
 * @var $sValue
 * @var $oth_checkconditionFunction
 * @var $checkconditionFunction
 * @var $sValueHidden
 * @var $wrapper
 */
?>

<!-- answer_row_other -->
    <li id='javatbd<?php echo $myfname; ?>' class='question-item answer-item checkbox-text-item form-group form-inline <?php echo $relevanceClass; ?>' >

        <!-- Checkbox + label -->
        <div class="form-group checkbox-item othertext-label-checkox-container">
            <!-- checkbox -->
            <input
                class="other-checkbox"
                type="checkbox"
                name="<?php echo $myfname; ?>cbox"
                id="answer<?php echo $myfname; ?>cbox"
                <?php echo $checkedState; ?>
                aria-hidden="true"
            />
            <label for="answer<?php echo $myfname;?>cbox" class="answertext" id="label-<?php echo $myfname; ?>-other"><?php echo $othertext; ?></label>
        </div>

        <!-- comment -->
        <div class="form-group text-item other-text-item">
            <input
                class="form-control input-sm <?php echo $kpclass; ?>"
                type="text"
                name="<?php echo $myfname; ?>"
                id="answer<?php echo $myfname; ?>"
                value="<?php echo $sValue; ?>"
                aria-labelledby="label-<?php echo $myfname; ?>-other"
            />
        </div>


        <!-- hidden input -->
        <input
            type="hidden"
            name="java<?php echo $myfname; ?>"
            id="java<?php echo $myfname; ?>"
            value="<?php echo $sValueHidden; ?>"
        />
<script type='text/javascript'>
    $('#answer<?php echo $myfname; ?>').on('keyup focusout',function(event)
    {
        if ($.trim($(this).val()).length>0)
        {
            $("#answer<?php echo $myfname; ?>cbox").prop("checked",true);
        }
        else
        {
            $("#answer<?php echo $myfname; ?>cbox").prop("checked",false);
        }
        $("#java<?php echo $myfname; ?>").val($(this).val());
        LEMflagMandOther("<?php echo $myfname; ?>",$('#answer<?php echo $myfname; ?>cbox').is(":checked"));
        <?php echo $oth_checkconditionFunction; ?>(this.value, this.name, this.type);
    });

    $('#answer<?php echo $myfname; ?>cbox').click(function(event)
    {
        if (($(this)).is(':checked') && $.trim($("#answer<?php echo $myfname; ?>").val()).length==0)
        {
            $("#answer<?php echo $myfname; ?>").focus();
            LEMflagMandOther("<?php echo $myfname; ?>",true);
            return false;
        }
        else
        {
            $("#answer<?php echo $myfname; ?>").val('');
            <?php echo $checkconditionFunction; ?>("", "<?php echo $myfname; ?>", "text");
            LEMflagMandOther("<?php echo $myfname; ?>",false);
            return true;
        };
    });
</script>
</li> <!-- Form group ; item row -->
<!-- end of answer_row_other -->
