<?php
/**
 * Multiple Choice Html : item 'other' row
 *
 * @var $sDisplayStyle
 * @var $sDisable
 * @var $sDisplayStyle
 * @var $myfname
 * @var $nbColLabelXs
 * @var $nbColLabelLg
 * @var $othertext
 * @var $nbColInputLg
 * @var $nbColInputXs
 * @var $checkedState
 * @var $kpclass
 * @var $sValue
 * @var $oth_checkconditionFunction
 * @var $checkconditionFunction
 * @var $sValueHidden
 * @var $wrapper
 */
?>

<div id='javatbd<?php echo $myfname; ?>' class='question-item answer-item checkbox-item form-group' <?php echo $sDisplayStyle; ?> >
    <label for="<?php echo $myfname;?>cbox" class="answertext control-label col-xs-<?php echo $nbColLabelXs; ?> col-lg-<?php echo $nbColLabelLg; ?> ">
        <?php echo $othertext; ?>
    </label>
    <div class="col-lg-<?php echo $nbColInputLg; ?> col-xs-<?php echo $nbColInputXs; ?>">
        <input
            class="checkbox other-checkbox dontread"
            style="visibility:hidden"
            type="checkbox"
            name="<?php echo $myfname; ?>cbox"
            id="answer<?php echo $myfname; ?>cbox"
            <?php echo $checkedState; ?>
        />
    </div>

    <div class="col-lg-12 col-xs-12">
        <input
            class="text <?php echo $kpclass; ?>"
            type="text"
            name="<?php echo $myfname; ?>"
            id="answer$myfname"
            value="<?php echo $sValue; ?>"
        />

        <script type='text/javascript'>
            <![CDATA[
                $('#answer<?php echo $myfname; ?>cbox').prop('aria-hidden', 'true').css('visibility','');
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

                $('#answer{$myfname}cbox').click(function(event)
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
            ]]>
        </script>

        <input
            type="hidden"
            name="java<?php echo $myfname; ?>"
            id="java<?php echo $myfname; ?>"
            value="<?php echo $sValueHidden; ?>"
        />
    </div>
</div> <!-- Form group ; item row -->
