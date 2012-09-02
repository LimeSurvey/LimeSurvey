<script type='text/javascript'>
    if (navigator.userAgent.indexOf("Gecko") != -1)
    window.addEventListener("load", init_gecko_select_hack, false);

     var qtypes = new Array();
     var qnames = new Array();
     var qhelp = new Array();
     var qcaption = new Array();


    function OtherSelection(Class)
    {
    if (Class==undefined || Class=='') Class = qDescToCode[''+$("#question_type option:selected").text()];
        switch (Class)
        {
<?php
for ($i = 0; $i < 8; $i++)
{
    $exists = false;
    foreach ($selections as $key => $value)
    {
        if ($value['other'] == ($i/1)%2 &&
            $value['valid'] == ($i/2)%2 &&
            $value['mandatory'] == ($i/4)%2)
        {
            $exists = true;
            echo "case '{$key}':\n";
        }
    }
    if ($exists)
    {
?>
            document.getElementById('OtherSelection').style.display = '<?php echo ($i/1)%2?'':'none';?>';
            document.getElementById('Validation').style.display = '<?php echo ($i/2)%2?'':'none';?>';
            document.getElementById('MandatorySelection').style.display='<?php echo ($i/4)%2?'':'none';?>';
            break;
<?php
    }
}
?>
        }
    }
    OtherSelection('<?php echo $class; ?>');
</script>