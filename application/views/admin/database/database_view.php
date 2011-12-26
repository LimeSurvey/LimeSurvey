<?php
if (isset($uqresult))
{?>
<script type="text/javascript"><br /><br /> alert("<?php $clang->gT("Question could not be updated","js"); ?>"<br />")<br /><br /></script><br />
<?php
}
if (isset($result)) // Checked
{
?>
<script type="text/javascript"><br /><br /> alert("<?php $clang->gT("Failed to update answers","js"); ?>" - ".$query." - ".$connect->ErrorMsg() ?>")<br /><br /></script><br />
<?php
}
if ($invalidCode == 1)
{
?>
<script type="text/javascript"><br /><br /> alert("<?php $clang->gT("Answers with a code of 0 (zero) or blank code are not allowed, and will not be saved","js"); ?>")<br /><br /></script><br />
<?php
}
if ($duplicateCode == 1)
{
?>
<script type="text/javascript"><br /><br /> alert("<?php $clang->gT("Duplicate codes found, these entries won't be updated","js"); ?>")<br /><br /></script><br />
<?php
}
if (isset($aresult))
{
?>
<script type="text/javascript"><br /><br /> alert("<?php $clang->gT("Failed to delete answer","js"); ?>" - ".$query." ")<br /> //<br /></script><br />
<?php
}
if (isset($strlen) && $strlen < 1)
{
?>
<script type="text/javascript"><br /><br /> "alert("<?php $clang->gT("The question could not be added. You must enter at least enter a question code.","js"); ?>")<br /> "//<br /></script><br />
<?php
}
if (isset($result2))
{
?>
<script type="text/javascript"><br /><br /> alert("<?php sprintf($clang->gT("Question in language %s could not be created.","js"),$alang); ?>"<br />")<br /><br /></script><br />
<?php
}
if (isset($result3))
{
?>
<script type="text/javascript"><br /><br /> alert("<?php $clang->gT("Question could not be created.","js"); ?>"<br />")<br /></script><br />
<?php
}
if (isset($cccount) && $cccount)
{
?>
<script type="text/javascript"><br /><br /> alert("<?php $clang->gT("Question could not be updated. There are conditions for other questions that rely on the answers to this question and changing the type will cause problems. You must delete these conditions before you can change the type of this question.","js"); ?>" ($qidlist)")<br /><br /></script><br />
<?php
}
if (isset($uqresult))
{
?>
<script type="text/javascript"><br /><br /> alert("<?php $clang->gT("Question could not be updated","js"); ?>"<br />")<br /></script><br />
<?php
}
if (isset($array_result) && !is_null($array_result['notAbove']) && is_null($array_result['notBelow']))
{
?>
<script type="text/javascript"><br /><br /> alert("$errormsg")<br /><br /></script><br />
    <?php $gid= $oldgid; // group move impossible ==> keep display on oldgid ?>
<?php
}
if (!isset($gid) || $gid != "")
{
?>
<script type="text/javascript"><br /><br /> alert("<?php $clang->gT("Question could not be updated","js"); ?>")<br /></script><br />
<?php
}
if (isset($usresult) &&$usresult)
{
?>
<script type="text/javascript"><br /><br /> alert("<?php $clang->gT("Survey could not be updated","js"); ?>"<br />")<br /><br /></script><br />
<?php
}
?>
<?php
if(isset($flag) && $flag=="y")
{
?>
<script type="text/javascript"><br /><br /> alert(<?php echo "'{$errormsg}'"; ?>)<br /><br /></script><br />
<?php
}
?>