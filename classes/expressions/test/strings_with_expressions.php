<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<?php
if (!((isset($subaction) && $subaction == 'strings_with_expressions'))) {die("Cannot run this script directly");}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>ExpressionManager:  Test Evaluation of Strings Containing Expressions</title>
    </head>
    <body>
        <?php
//            include_once('../LimeExpressionManager.php');
            LimeExpressionManager::UnitTestProcessStringContainingExpressions();
        ?>
    </body>
</html>
