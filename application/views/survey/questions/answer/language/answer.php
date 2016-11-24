<?php
/**
 * Language HTML
 * @var $name
 * @var $checkconditionFunction
 * @var $answerlangs
 * @var $sLang
 */
?>
<!-- Language -->

<!-- answer -->
<div class="<?php echo $coreClass;?> form-group form-inline">
    <label for='answer<?php echo $name; ?>' class='sr-only control-label'>
        <?php eT('Choose your language'); ?>
    </label>

    <select
        name="<?php echo $name;?>"
        id="answer<?php echo $name;?>"
        class="languagesurvey form-control"
        aria-describedby="ls-question-text-<?php echo $name; ?>"
    >
        <?php foreach ($answerlangs as $ansrow):?>
            <option value="<?php echo $ansrow; ?>" <?php if ($sLang == $ansrow):?> SELECTED <?php endif;?>>
                <?php $aLanguage=getLanguageNameFromCode($ansrow, true); ?>
                <?php echo $aLanguage[1];?>
            </option>
        <?php endforeach;?>
    </select>
    <input type="hidden" name="java<?php echo $name; ?>" id="java<?php echo $name; ?>" value="<?php echo $sLang; ?>" />
</div>

<script type='text/javascript'>
/*<![CDATA[*/
    $('#answer<?php echo $name; ?>').change(function(){
        $('<input type="hidden">').attr('name','lang').val($(this).val()).appendTo($('form#limesurvey'));
    });
/*]]>*/
</script>
<!-- end of answer -->
