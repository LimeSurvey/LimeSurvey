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

            // List surveys to test here
            $surveys = array(
                768959,
                37171,
//                27246,
//                26834,
//                24811,
                );

            foreach ($surveys as $surveyid)
            {
                print '<h3>Starting survey ' . $surveyid . "</h3>";
                $now = microtime(true);
                LimeExpressionManager::StartSurvey($surveyid, 'group', false, true);
                print '<b>[StartSurvey() took ' . (microtime(true) - $now) . ' seconds]</b><br/>';

                while(true) {
                    $now = microtime(true);
                    $result = LimeExpressionManager::NavigateForwards(true,false);
                    print $result['message'] . "<br/>";
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
