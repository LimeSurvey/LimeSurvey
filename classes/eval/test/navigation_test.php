<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>LEM Navigation Test</title>
    </head>
    <body>
        <?php
            include_once('../LimeExpressionManager.php');
            require_once('../../../classes/core/startup.php');
            require_once('../../../config-defaults.php');
            require_once('../../../common.php');
            require_once('../../../classes/core/language.php');

            $clang = new limesurvey_lang("en");

            if (count($_POST) == 0) {
                $query = "select a.surveyls_survey_id as sid, a.surveyls_title as title, b.datecreated "
                . "from " . db_table_name('surveys_languagesettings') . " as a join ". db_table_name('surveys') . " as b on a.surveyls_survey_id = b.sid"
                . " where a.surveyls_language='en' order by a.surveyls_title, b.datecreated";
        		$data = db_execute_assoc($query);
                $surveyList='';
                foreach($data->GetRows() as $row) {
                    $surveyList .= "<option value='" . $row['sid'] . "'>#" . $row['sid'] . " [" . $row['datecreated'] . '] ' . FlattenText($row['title']) . "</option>\n";
                }

                $form = <<< EOD
<form method='post' action='navigation_test.php'>
<h3>Enter the following variables to test navigation for a survey using different styles</h3>
<table border='1'>
<tr><th>Parameter</th><th>Value</th></tr>
<tr><td>Survey ID (SID)</td>
<td><select name='sid' id='sid'>
$surveyList
</select></td></tr>
<tr><td>Navigation Style</td>
<td><select name='surveyMode' id='surveyMode'>
    <option value='question'>Question (One-at-a-time)</option>
    <option value='group'>Group (Group-at-a-time)</option>
    <option value='survey'>Survey (All-in-one)</option>
</select></td></tr>
<tr><td>Debug Log Level</td>
<td>
Specify which debugging features to use
<ul>
<li><input type='checkbox' name='LEM_DEBUG_TIMING' id='LEM_DEBUG_TIMING' value='Y'/>Detailed Timing</li>
<li><input type='checkbox' name='LEM_DEBUG_VALIDATION_SUMMARY' id='LEM_DEBUG_VALIDATION_SUMMARY' value='Y' checked/>Validation Summary</li>
<li><input type='checkbox' name='LEM_DEBUG_VALIDATION_DETAIL' id='LEM_DEBUG_VALIDATION_DETAIL' value='Y' checked/>Validation Detail (Validation Summary must also be checked to see detail)</li>
<li><input type='checkbox' name='LEM_DEBUG_NOCACHING' id='LEM_DEBUG_NOCACHING' value='Y'/>No Caching</li>
<li><input type='checkbox' name='LEM_DEBUG_TRANSLATION_DETAIL' id='LEM_DEBUG_TRANSLATION_DETAIL' value='Y'/>Translation Detail</li>
</ul></td>
</tr>
<tr><td colspan='2'><input type='submit'/></td></tr>
</table>
</form>
EOD;
                echo $form;
            }
            else {
                $surveyid = $_POST['sid'];
                $surveyMode = $_POST['surveyMode'];
                $LEMdebugLevel = (
                        ((isset($_POST['LEM_DEBUG_TIMING']) && $_POST['LEM_DEBUG_TIMING'] == 'Y') ? LEM_DEBUG_TIMING : 0) +
                        ((isset($_POST['LEM_DEBUG_VALIDATION_SUMMARY']) && $_POST['LEM_DEBUG_VALIDATION_SUMMARY'] == 'Y') ? LEM_DEBUG_VALIDATION_SUMMARY : 0) +
                        ((isset($_POST['LEM_DEBUG_VALIDATION_DETAIL']) && $_POST['LEM_DEBUG_VALIDATION_DETAIL'] == 'Y') ? LEM_DEBUG_VALIDATION_DETAIL : 0) +
                        ((isset($_POST['LEM_DEBUG_NOCACHING']) && $_POST['LEM_DEBUG_NOCACHING'] == 'Y') ? LEM_DEBUG_NOCACHING : 0) +
                        ((isset($_POST['LEM_DEBUG_TRANSLATION_DETAIL']) && $_POST['LEM_DEBUG_TRANSLATION_DETAIL'] == 'Y') ? LEM_DEBUG_TRANSLATION_DETAIL : 0)
                        );

                $surveyOptions = array(
                    'active'=>false,
                    'allowsave'=>true,
                    'anonymized'=>false,
                    'datestamp'=>true,
                    'hyperlinkSyntaxHighlighting'=>true,
                    'ipaddr'=>true,
                    'rooturl'=>'../../..',
                );

                print '<h3>Starting survey ' . $surveyid . " using Survey Mode '". $surveyMode . "'</h3>";
                $now = microtime(true);
                LimeExpressionManager::StartSurvey($surveyid, $surveyMode, $surveyOptions, true,$LEMdebugLevel);
                print '<b>[StartSurvey() took ' . (microtime(true) - $now) . ' seconds]</b><br/>';

                while(true) {
                    $now = microtime(true);
//                    LimeExpressionManager::StartProcessingPage();
                    $result = LimeExpressionManager::NavigateForwards(true);
                    print $result['message'] . "<br/>";
//                    LimeExpressionManager::FinishProcessingGroup(); // move this internally?  This is what is needed to save group data so visible to GetRelevanceAndTailoringJavaScript()
                    LimeExpressionManager::FinishProcessingPage();
//                    print LimeExpressionManager::GetRelevanceAndTailoringJavaScript();
                    if (($LEMdebugLevel & LEM_DEBUG_TIMING) == LEM_DEBUG_TIMING) {
                        print LimeExpressionManager::GetDebugTimingMessage();
                    }
                    print '<b>[NavigateForwards() took ' . (microtime(true) - $now) . ' seconds]</b><br/>';
                    if (is_null($result) || $result['finished'] == true) {
                        break;
                    }
                }
                print "<h3>Finished survey " . $surveyid . "</h3>";
            }
        ?>
    </body>
</html>
