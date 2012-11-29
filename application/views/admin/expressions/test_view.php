<table border="1">
    <tr><th>Test</th><th>Description</th></tr>
    <tr>
        <td><a href="<?php echo $this->createUrl('admin/expressions/sa/functions'); ?>">Available Functions</a></td>
        <td>Show the list of functions available within Expression Manager.</td>
    </tr>
    <tr>
        <td><a href="<?php echo $this->createUrl('admin/expressions/sa/stringsplit'); ?>">String Splitter</a></td>
        <td>Unit test of String Splitter to ensure splits source into Strings vs. Expressions.  Expressions are surrounded by un-escaped curly braces</td>
    </tr>
    <tr>
        <td><a href="<?php echo $this->createUrl('admin/expressions/sa/tokenizer'); ?>">Tokenizer</a></td>
        <td>Demonstrates that Expression Manager properly detects and categorizes tokens (e.g. variables, string, functions, operators)</td>
    </tr>
    <tr>
        <td><a href="<?php echo $this->createUrl('admin/expressions/sa/unit'); ?>">Unit Tests of Isolated Expressions</a></td>
        <td>Unit tests of each of Expression Manager's features (e.g. all operators and functions).  Color coding shows whether any tests fail.  Syntax highlighting shows cases where Expression Manager properly detects bad syntax.</td>
    </tr>
    <tr>
        <td><a href="<?php echo $this->createUrl('admin/expressions/sa/strings_with_expressions'); ?>">Unit Tests of Expressions Within Strings</a></td>
        <td>Test how Expression Manager can process strings containing one or more variable, token, or expression replacements surrounded by curly braces.</td>
    </tr>
    <tr>
        <td><a href="<?php echo $this->createUrl('admin/expressions/sa/relevance'); ?>">Unit Test Dynamic Relevance Processing</a></td>
        <td>Questions and substitutions should dynamically change based upon values entered.</td>
    </tr>
    <tr>
        <td><a href="<?php echo $this->createUrl('admin/expressions/sa/conditions2relevance'); ?>">Preview Conversion of Conditions to Relevance</a></td>
        <td>Shows Relevance equations for all conditions in the database, grouped by question id (and not pretty-printed)</td>
    </tr>
    <tr>
        <td><a href="<?php echo $this->createUrl('admin/expressions/sa/upgrade_conditions2relevance'); ?>">Bulk Convert Conditions to Relevance</a></td>
        <td>Convert conditions to relevance for entire database</td>
    </tr>
    <tr>
        <td><a href="<?php echo $this->createUrl('admin/expressions/sa/navigation_test'); ?>">Test Navigation</a></td>
        <td>Tests whether navigation properly handles relevant and irrelevant groups</td>
    </tr>
    <tr>
        <td><a href="<?php echo $this->createUrl('admin/expressions/sa/survey_logic_file'); ?>">Show Survey logic file</a></td>
        <td>Shows the logic for a survey (e.g. relevance, validation), and all tailoring</td>
    </tr>
</table>
