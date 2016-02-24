<?php
/**
 * Language Html
 * @var $name                        $ia[1]
 * @var $checkconditionFunction      $checkconditionFunction(this.value, this.name, this.type);
 * @var $answerlangs
 * @var $sLang
 */
?>
<p class="question answer-item dropdown-item langage-item">
    <label for='answer<?php echo $name; ?>' class='hide label'>
        <?php eT('Choose your language'); ?>
    </label>

    <select name="<?php echo $name;?>" id="answer<?php echo $name;?>" onchange="<?php echo $checkconditionFunction;?>" class="languagesurvey form-control" >
        <?php foreach ($answerlangs as $ansrow):?>
            <option value="<?php echo $ansrow; ?>" <?php if ($sLang == $ansrow):?> SELECTED <?php endif;?>>
                <?php $aLanguage=getLanguageNameFromCode($ansrow, true); ?>
                <?php echo $aLanguage[1];?>
            </option>
        <?php endforeach;?>
    </select>
    <input type="hidden" name="java<?php echo $name; ?>" id="java<?php echo $name; ?>" value="<?php echo $sLang; ?>" />
</p>
