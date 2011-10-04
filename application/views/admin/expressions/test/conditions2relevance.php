<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>LimeExpressionManager:  Preview Conditions to Relevance</title>
    </head>
    <body>
        <?php
            $data = LimeExpressionManager::UnitTestConvertConditionsToRelevance();
            echo count($data) . " question(s) in your database contain conditions.  Below is the mapping of question ID number to generated relevance equation<br/>";
            echo "<pre>";
            print_r($data);
            echo "</pre>";
        ?>
    </body>
</html>
