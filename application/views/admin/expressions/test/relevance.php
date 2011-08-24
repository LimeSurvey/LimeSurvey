<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>ExpressionManager:  Unit Test Relevance</title>
    </head>
<script type="text/javascript" src="<?php echo base_url() . '/scripts/admin/expressions/em_javascript.js'; ?>"></script>
<script type="text/javascript" src="<?php echo base_url() . '/scripts/jquery/jquery.js'; ?>"></script>
    <body onload="ExprMgr_process_relevance_and_tailoring();">
        <?php
            LimeExpressionManager::UnitTestRelevance();
        ?>
    </body>
</html>
