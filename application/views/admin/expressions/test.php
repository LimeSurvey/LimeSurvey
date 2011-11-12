<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Test Suite for Expression Manager</title>
    </head>
    <body>
        <table border="1">
            <tr><th>Test</th><th>Description</th></tr>
            <tr>
                <td><a href="<?php echo site_url("admin/expressions/test/functions");?>">Available Functions</a></td>
                <td>Show the list of functions available within Expression Manager.</td>
            </tr>
            <tr>
                <td><a href="<?php echo site_url("admin/expressions/test/stringsplit");?>">String Splitter</a></td>
                <td>Unit test of String Splitter to ensure splits source into Strings vs. Expressions.  Expressions are surrounded by un-escaped curly braces</td>
            </tr>
            <tr>
                <td><a href="<?php echo site_url("admin/expressions/test/tokenizer");?>">Tokenizer</a></td>
                <td>Demonstrates that Expression Manager properly detects and categorizes tokens (e.g. variables, string, functions, operators)</td>
            </tr>
            <tr>
                <td><a href="<?php echo site_url("admin/expressions/test/unit");?>">Unit Tests of Isolated Expressions</a></td>
                <td>Unit tests of each of Expression Manager's features (e.g. all operators and functions).  Color coding shows whether any tests fail.  Syntax highlighting shows cases where Expression Manager properly detects bad syntax.</td>
            </tr>
            <tr>
                <td><a href="<?php echo site_url("admin/expressions/test/strings_with_expressions");?>">Unit Tests of Expressions Within Strings</a></td>
                <td>Test how Expression Manager can process strings containing one or more variable, token, or expression replacements surrounded by curly braces.</td>
            </tr>
            <tr>
                <td><a href="<?php echo site_url("admin/expressions/test/relevance");?>">Unit Test Dynamic Relevance Processing</a></td>
                <td>Questions and substitutions should dynamically change based upon values entered.</td>
            </tr>
            <tr>
                <td><a href="<?php echo site_url("admin/expressions/test/conditions2relevance");?>">Preview Conversion of Conditions to Relevance</a></td>
                <td>Shows Relevance equations for all conditions in the database, grouped by question id (and not pretty-printed)</td>
            </tr>
            <tr>
                <td><a href="<?php echo site_url("admin/expressions/test/upgrade_conditions2relevance");?>"><span style='background-color: red;'>**</span>Upgrade Conditions to Relevance</a></td>
                <td><span style='color: red;'>CAUTION: This function changes your database.</span> This upgrades all conditions within your database to Relevance.  Existing conditions are preserved, but any relevance fields in the questions table are overwritten by Conditions if there are any.</td>
            </tr>
            <tr>
                <td><a href="<?php echo site_url("admin/expressions/test/revert_upgrade_conditions2relevance");?>"><span style='background-color: red;'>**</span>Revert of Upgrade Conditions to Relevance</a></td>
                <td><span style='color: red;'>CAUTION: This function changes your database.</span> This deletes the relevance field for all questions that have Conditions (to avoid having run-time clashes between relevance and conditions)  This function will be removed once the back-end conditions processing is removed and testing is complete.</td>
            </tr>
            <tr>
                <td><a href="<?php echo site_url("admin/expressions/test/upgrade_relevance_location");?>"><span style='background-color: red;'>**</span>Move Relevance from Attribute to Question</a></td>
                <td><span style='color: red;'>CAUTION: This function changes your database.</span> This function copies existing relevance from Question Attribute to Question model.  This function will be removed once the back-end conditions processing is removed and testing is complete.</td>
            </tr>
            <tr>
                <td><a href="<?php echo site_url("admin/expressions/test/usage");?>">Running Log - Translations on this Page</a></td>
                <td>For this page group, shows all of the translation requests, the pretty-printed version of the request, and the translated results.  Note this is only visible if $debugLEM==true</td>
            </tr>
            <tr>
                <td><a href="<?php echo site_url("admin/expressions/test/data");?>">Running Log - Source Data</a></td>
                <td>Shows log of mapping of variable names to SGQA and JavaScript names, plus question, and current values.  Note, this is only visible if $debugLEM==true</td>
            </tr>
        </table>
    </body>
</html>
