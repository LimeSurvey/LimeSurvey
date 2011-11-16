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
                27246,
                );

            foreach ($surveys as $surveyid)
            {
                LimeExpressionManager::StartSurvey($surveyid, 'group', false, true);
                print "<pre>\n";
                print 'Starting survey ' . $surveyid . "\n";
                while(true) {
                    $result = LimeExpressionManager::NavigateForwards();
                    if (is_null($result) || $result['finished'] == true) {
                        break;
                    }
                    print $result['message'];
                }
                print $result['message'];
                print "</pre>\n";
            }
        ?>
    </body>
</html>
