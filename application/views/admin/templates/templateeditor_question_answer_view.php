<?php
if (isset($alt))
{
?>
    <textarea class='textarea' rows='5' cols='40'>Some text in this answer</textarea>
<?php
    return;
}
?>
<ul>
    <li>
        <input type='radio' class='radio' name='1' value='1' id='radio1' />
        <label class='answertext' for='radio1'>One</label>
    </li>
    <li>
        <input type='radio' class='radio' name='1' value='2' id='radio2' />
        <label class='answertext' for='radio2'>Two</label>
    </li>
    <li>
        <input type='radio' class='radio' name='1' value='3' id='radio3' />
        <label class='answertext' for='radio3'>Three</label>
    </li>
</ul>
