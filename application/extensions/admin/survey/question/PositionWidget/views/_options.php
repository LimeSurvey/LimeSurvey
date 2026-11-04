<?php
/**
 * Position Widget options, shared by the form_group and general_option views
 *
 * @var $aQuestions     A array containing the datas to create the options for each questions
 */
?>
<option value=''><?php eT("At end"); ?></option>
<option value='0'><?php eT("At beginning"); ?></option>
<?php foreach ($aQuestions as $oq) : ?>
    <option value='<?php echo $oq->attributes['question_order'] + 1; ?>'><?php eT("After"); ?>: <?php echo $oq->attributes['title']; ?></option>
<?php endforeach; ?>
