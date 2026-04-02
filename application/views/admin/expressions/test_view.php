<?php
/* @var $this AdminController */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('expressionsTest');

?>
<table class="table table-striped ">
    <thead>
    <tr>
        <th>Test</th>
        <th>Description</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><a href="<?php echo $this->createUrl('admin/expressions/sa/functions'); ?>">Available Functions</a></td>
        <td>Show the list of functions available within ExpressionScript Engine.</td>
    </tr>
    <tr>
        <td><a href="<?php echo $this->createUrl('admin/expressions/sa/strings_with_expressions'); ?>">Unit Tests of Expressions Within Strings</a></td>
        <td>Test how ExpressionScript Engine can process strings containing one or more variable, token, or expression replacements surrounded by curly braces.</td>
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
        <td><a href="<?php echo $this->createUrl('admin/expressions/sa/survey_logic_file'); ?>">Show Survey logic overview</a></td>
        <td>Shows the logic for a survey (e.g. relevance, validation), and all tailoring</td>
    </tr>
    </tbody>
</table><?php
/* @var $this AdminController */

// DO NOT REMOVE This is for automated testing to validate we see that page
echo viewHelper::getViewTestTag('expressionsTest');

?>
    <table class="table table-striped ">
        <thead>
        <tr>
            <th>Test</th>
            <th>Description</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><a href="<?php echo $this->createUrl('admin/expressions/sa/functions'); ?>">Available Functions</a></td>
            <td>Show the list of functions available within ExpressionScript Engine.</td>
        </tr>
        <tr>
            <td><a href="<?php echo $this->createUrl('admin/expressions/sa/strings_with_expressions'); ?>">Unit Tests of Expressions Within Strings</a></td>
            <td>Test how ExpressionScript Engine can process strings containing one or more variable, token, or expression replacements surrounded by curly braces.</td>
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
            <td><a href="<?php echo $this->createUrl('admin/expressions/sa/survey_logic_file'); ?>">Show Survey logic overview</a></td>
            <td>Shows the logic for a survey (e.g. relevance, validation), and all tailoring</td>
        </tr>
        </tbody>
    </table>
