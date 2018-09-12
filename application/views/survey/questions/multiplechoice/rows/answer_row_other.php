<?php
/**
 * Multiple Choice Html : item 'other' row
 *
 * @var $sDisplayStyle
 * @var $sDisable
 * @var $sDisplayStyle
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
<div class="col-sm-12">
    <div id='javatbd<?php echo $myfname; ?>' class='question-item answer-item checkbox-item form-group checkbox' <?php echo $sDisplayStyle; ?> >

        <!-- Checkbox + label -->
        <div class="pull-left othertext-label-checkox-container">
            <!-- checkbox -->
            <input
                class="checkbox other-checkbox dontread"
                type="checkbox"
                name="<?php echo $myfname; ?>cbox"
                id="answer<?php echo $myfname; ?>cbox"
                <?php echo $checkedState; ?>
                aria-labelledby="label-<?php echo $myfname;?>cbox"
             />

             <!-- label -->
             <label for="answer<?php echo $myfname;?>cbox" class="answertext"></label>
             <!--
                  The label text is provided inside a div,
                  so final user can add paragraph, div, or whatever he wants in the subquestion text
                  This field is related to the input thanks to attribute aria-labelledby
             -->
             <div class="label-text label-clickable" id="label-<?php echo $myfname;?>cbox">
                     <?php echo $othertext; ?>
             </div>
        </div>

        <!-- comment -->
        <div class="pull-left">
            <input
                class="form-control input-sm text <?php echo $kpclass; ?>"
                type="text"
                name="<?php echo $myfname; ?>"
                id="answer<?php echo $myfname; ?>"
                value="<?php echo $sValue; ?>"
            />
        </div>


        <!-- hidden input -->
        <input
            type="hidden"
            name="java<?php echo $myfname; ?>"
            id="java<?php echo $myfname; ?>"
            value="<?php echo $sValueHidden; ?>"
        />

        </div> <!-- Form group ; item row -->
</div>

<script type='text/javascript'>
    $('#answer<?php echo $myfname; ?>').bind('keyup focusout',function(event)
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
<!-- end of answer_row_other -->
