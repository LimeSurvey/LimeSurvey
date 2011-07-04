<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Test Suite for ExpressionManager</title>
    </head>
    <body>
        <table border="1">
            <tr><th>Test</th><th>Description</th></tr>
            <tr>
                <td><a href="Test_ExpressionManager_Tokenizer.php">Tokenizer</a></td>
                <td>Demonstrates that ExpressionManager properly detects and categorizes tokens (e.g. variables, string, functions, operators)</td>
            </tr>
            <tr>
                <td><a href="Test_ExpressionManager_Evaluate.php">Unit Tests</a></td>
                <td>Unit tests of each of ExpressionManager's features.  Color coding shows whether any tests fail.  Syntax highlighting shows cases where ExpressionManager properly detects bad syntax.</td>
            </tr>
            <tr>
                <td><a href="Test_ExpressionManager_StringSplitter.php">String Splitter</a></td>
                <td>Unit test of String Splitter to ensure splits source into Strings vs. Expressions.  Expressions are surrounded by un-escaped curly braces</td>
            </tr>
            <tr>
                <td><a href="Test_ExpressionManager_ProcessStringContainingExpressions.php">Integration Tests</a></td>
                <td>Integration tests showing how Expression Manager can process strings containing one or more variable, token, or expression replacements surrounded by curly braces.</td>
            </tr>
            <tr>
                <td>Ad Hoc Unit Tests</td>
                <td>Paste own tests into form to see results.  Syntax is Answer~Expression, with one test performing.</td>
            </tr>
            <tr>
                <td>Ad Hoc Integration Tests</td>
                <td>Paste own tests into form to see results.</td>
            </tr>
        </table>
        <?php
        // put your code here
        ?>
    </body>
</html>
