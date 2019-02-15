<?php

echo '<link rel="stylesheet" type="text/css" href="' . getTemplateURL(getGlobalSetting('defaulttheme')) . '/print_template.css" />';

echo $surveydesc . "<br />";
echo $welcome . "<br /><br />";

echo $numques;

foreach ($survey_output as $key => $val)
{
    if ($key == "GROUPS")
    {

        echo "$val<br>";
    }
}

echo $survey_output['END'] . "<br />";
echo $survey_output['SUBMIT_BY'] . "<br /><br />";
echo $survey_output['SUBMIT_TEXT'];
echo "<br />";
echo $survey_output['THANKS'];
?>
