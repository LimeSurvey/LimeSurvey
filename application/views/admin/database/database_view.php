<?php
if (isset($uqresult))
{?>
<script type="text/javascript">alert("<?php eT("Question could not be updated", 'js'); ?>")</script><br />
<?php
}
if (isset($result)) // Checked
{
?>
<script type="text/javascript">alert("<?php eT("Failed to update answers", 'js') . " - " . $query; ?>")</script><br />
<?php
}
if (isset($invalidCode) && $invalidCode == 1)
{
?>
<script type="text/javascript">alert("<?php eT("Answers with a code of 0 (zero) or blank code are not allowed, and will not be saved", 'js'); ?>")</script><br />
<?php
}
if (isset($duplicateCode) && $duplicateCode == 1)
{
?>
<script type="text/javascript">alert("<?php eT("Duplicate codes found, these entries won't be updated", 'js'); ?>")</script><br />
<?php
}
if (isset($aresult))
{
?>
<script type="text/javascript">alert("<?php eT("Failed to delete answer", 'js') . " - ".$query; ?>")</script><br />
<?php
}
if (isset($strlen) && $strlen < 1)
{
?>
<script type="text/javascript">alert("<?php eT("The question could not be added. You must enter at least enter a question code.", 'js'); ?>")</script><br />
<?php
}
if (isset($result2))
{
?>
<script type="text/javascript">alert("<?php printf(gT("Question in language %s could not be created.", 'js'), $alang); ?>")</script><br />
<?php
}
if (isset($result3))
{
?>
<script type="text/javascript">alert("<?php eT("Question could not be created.", 'js'); ?>")</script><br />
<?php
}
if (isset($cccount) && $cccount)
{
?>
<script type="text/javascript">alert("<?php eT("Question could not be updated. There are conditions for other questions that rely on the answers to this question and changing the type will cause problems. You must delete these conditions before you can change the type of this question.", 'js'); ?> (<?php echo $qidlist; ?>)")</script><br />
<?php
}
if (isset($uqresult))
{
?>
<script type="text/javascript">alert("<?php eT("Question could not be updated", 'js'); ?>")</script><br />
<?php
}
if (isset($array_result) && !is_null($array_result['notAbove']) && is_null($array_result['notBelow']))
{
?>
<script type="text/javascript">alert("$errormsg")</script><br />
    <?php $gid= $oldgid; // group move impossible ==> keep display on oldgid ?>
<?php
}
if (!isset($gid) || $gid != "")
{
?>
<script type="text/javascript">alert("<?php eT("Question could not be updated", 'js'); ?>")</script><br />
<?php
}
if (isset($usresult) &&$usresult)
{
?>
<script type="text/javascript">alert("<?php eT("Survey could not be updated", 'js'); ?>")</script><br />
<?php
}
?>
<?php
if(isset($flag) && $flag=="y")
{
?>
<script type="text/javascript">alert(<?php echo "'{$errormsg}'"; ?>)</script><br />
<?php
}
?>