<?php
if (isset($alt))
{
?>
<p class="question answer-item text-item "><label class="hide label" for="answer1295X1X2"><?php $clang->eT('Answer') ?></label>
<textarea cols="40" rows="5" alt="<?php $clang->eT('Answer') ?>" id="answer1295X1X2" name="1295X1X2" class="textarea"><?php $clang->eT('Some text in this answer') ?></textarea></p>
<?php
}else{
?>
<ul class="answers-list radio-list">
    <li id="javatbd1295X1X1A1" class="answer-item radio-item">
        <input class="radio" type="radio" name='answer1295X1X1' value='A1' id='answer1295X1X1A1' />
        <label class='answertext' for='radio1'><?php $clang->eT('One') ?></label>
    </li>
    <li id="javatbd1295X1X1A1" class="answer-item radio-item">
        <input type='radio' class='radio' name='answer1295X1X1' value='A2' id='answer1295X1X1A2' />
        <label class='answertext' for='radio2'><?php $clang->eT('Two') ?></label>
    </li>
    <li id="javatbd1295X1X1A1" class="answer-item radio-item">
        <input type='radio' class='radio' name='answer1295X1X1' value='A3' id='answer1295X1X1A3' />
        <label class='answertext' for='radio3'><?php $clang->eT('Three') ?></label>
    </li>
</ul>
<?php
}
?>
