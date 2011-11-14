<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>LEM Serialization Test</title>
    </head>
    <body>
        <?php
            include_once('../LimeExpressionManager.php');
            require_once('../../../classes/core/startup.php');
            require_once('../../../config-defaults.php');
            require_once('../../../common.php');
            require_once('../../../classes/core/language.php');

            $clang = new limesurvey_lang("en");

            $now = microtime(true);

            // Test with two real surveys
            // To customize this test, pick two real surveys, and real gid and qid values from those surveys
            // plus test strings that show validity of substitutions from those surveys.
            LimeExpressionManager::StartProcessingPage();
            LimeExpressionManager::StartProcessingGroup(67, false, 768959);
            $test1 = "{if((married=='Y' and max(kid1.NAOK,kid2.NAOK,kid3.NAOK,kid4.NAOK,kid5.NAOK)>yearsMarried),'Hmmm, your oldest child is older than your marriage. Interesting.','')}";

            $LEM =& LimeExpressionManager::singleton();
            $LEM1 = serialize($LEM);

            LimeExpressionManager::StartProcessingPage();
            LimeExpressionManager::StartProcessingGroup(62, false, 37171);
            $test2 = 'Your score is {MEQ}. YOUR MORNINGNESS-EVENINGNESS TYPE IS CONSIDERED TO BE {strtoupper(MEQ_type)}. {if((MEQstd>1.7)," HOWEVER, since your pattern of answers indicates a mix of strong morningness and strong eveningness scores, the predicted values for MELATONIN ONSET, NATURAL BEDTIME, and OPTIMUM 30-MIN LIGHT TREATMENT time might be inaccurate.","")}';

            $LEM =& LimeExpressionManager::singleton();
            $LEM2 = serialize($LEM);

            $start=$now;

            $iteration=array();
            for ($i=0;$i<10;++$i)  {
                if ($i % 2) {
                    $LEM =& LimeExpressionManager::singleton($LEM2);
                    LimeExpressionManager::ProcessString($test2,1018);
                    $result = LimeExpressionManager::GetLastPrettyPrintExpression();
                    $size = strlen($LEM2);
                }
                else {
                    $LEM =& LimeExpressionManager::singleton($LEM1);
                    LimeExpressionManager::ProcessString($test1,1037);
                    $result = LimeExpressionManager::GetLastPrettyPrintExpression();
                    $size = strlen($LEM1);
                }
                $now2 = microtime(true);
                $iteration[] = array(
                    'num'=>$i,
                    'duration'=>$now2-$now,
                    'memory'=>memory_get_usage(true),
                    'size'=>$size,
                    'test'=>$result,
                );
                $now=$now2;
            }

            print 'Total Time: ' . (microtime(true)-$start) . "\n";
            print "<table border='1'><tr><th>#</th><th>Duration</th><th>Total Memory</th><th>LEM size</th><th>Test String</th></tr>\n";
            foreach ($iteration as $it)
            {
                print "<tr><td>{$it['num']}</td><td>{$it['duration']}</td><td>{$it['memory']}</td><td>{$it['size']}<td>{$it['test']}</td></tr>\n";
            }
            print "</table>";
        ?>
    </body>
</html>
