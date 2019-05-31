<?php
/**
* LimeSurvey
* Copyright (C) 2007-2016 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v3 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/
namespace LimeSurvey\Helpers;
use QuestionAttribute;
use Yii;
/**
 * General helper class for question + question setting system
 */
class questionHelper
{
    /* @var array[]|null The question attribute definition for this LimeSurvey installation */
    protected static $attributes;
    /* @var array[] The question attribute (settings) by question type*/
    protected static $questionAttributesSettings = array();

    /**
     * Return all the definitions of Question attributes core+extended value
     * @return array[]
     */
    public static function getAttributesDefinitions()
    {
        if (self::$attributes) {
            return self::$attributes;
        }

        self::$attributes = array();
        //For each question attribute include a key:
        // name - the display name
        // types - a string with one character representing each question typy to which the attribute applies
        // help - a short explanation

        // If you insert a new attribute please do it in correct alphabetical order!
        // Please also list the new attribute in the function &TSVSurveyExport($sid) in em_manager_helper.php,
        // so your new attribute will not be "forgotten" when the survey is exported to Excel/CSV-format!

        // If you need to create a new attribute selector rendering for question advanced attribute
        // Just add it to application/views/admin/survey/Question/advanced_settings_view
        self::$attributes["alphasort"] = array(
        "types"=>"!LOWZ",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'switch',
        'options'=>array(
            0=>gT('No'),
            1=>gT('Yes')
        ),
        'default'=>0,
        "help"=>gT("Sort the answer options alphabetically"),
        "caption"=>gT('Sort answers alphabetically'));

        self::$attributes["answer_width"] = array(
            "types"=>"ABCEF1:;",
            'category'=>gT('Display'),
            'sortorder'=>100,
            'inputtype'=>'integer',
            'min'=>'0',
            'max'=>'100',
            "help"=>gT('Set the percentage width of the (sub-)question column (1-100)'),
            "caption"=>gT('(Sub-)question width')
        );

        self::$attributes["answer_width_bycolumn"] = array(
            "types"=>"H",
            'category'=>gT('Display'),
            'sortorder'=>100,
            'inputtype'=>'integer',
            'min'=>'0',
            'max'=>'100',
            "help"=>gT('Set the percentage width of the answers column (1-100)'),
            "caption"=>gT('Answers column width')
        );

        self::$attributes["repeat_headings"] = array(
            "types"=>"F:1;",
            'category'=>gT('Display'),
            'sortorder'=>100,
            'inputtype'=>'integer',
            'default'=>'',
            "help"=>gT('Repeat answer options every X subquestions (Set to 0 to deactivate answer options repeat, deactivate minimum answer options repeat from config).'),
            "caption"=>gT('Repeat answer options')
        );

        self::$attributes["array_filter"] = array(
            "types"=>"1ABCEF:;MPLKQR",
            'category'=>gT('Logic'),
            'sortorder'=>100,
            'inputtype'=>'text',
            "help"=>gT("Enter the code(s) of Multiple choice question(s) (separated by semicolons) to only show the matching answer options in this question."),
            "caption"=>gT('Array filter')
        );

        self::$attributes["array_filter_exclude"] = array(
            "types"=>"1ABCEF:;MPLKQR",
            'category'=>gT('Logic'),
            'sortorder'=>100,
            'inputtype'=>'text',
            "help"=>gT("Enter the code(s) of Multiple choice question(s) (separated by semicolons) to exclude the matching answer options in this question."),
            "caption"=>gT('Array filter exclusion')
        );

        self::$attributes["array_filter_style"] = array(
            "types"=>"1ABCEF:;MPLKQR",
            'category'=>gT('Logic'),
            'sortorder'=>100,
            'inputtype'=>'buttongroup',
            'options'=>array(0=>gT('Hidden', 'unescaped'),
            1=>gT('Disabled', 'unescaped')),
            'default'=>0,
            "help"=>gT("Specify how array-filtered subquestions should be displayed"),
            "caption"=>gT('Array filter style')
        );

        self::$attributes["assessment_value"] = array(
            "types"=>"MP",
            'category'=>gT('Logic'),
            'sortorder'=>100,
            'default'=>'1',
            'inputtype'=>'integer',
            "help"=>gT("If one of the subquestions is marked then for each marked subquestion this value is added as assessment."),
            "caption"=>gT('Assessment value')
        );

        self::$attributes["category_separator"] = array(
            "types"=>"!",
            'category'=>gT('Display'),
            'sortorder'=>100,
            'inputtype'=>'text',
            "help"=>gT('Category separator'),
            "caption"=>gT('Category separator')
        );

        self::$attributes["code_filter"] = array(
            "types"=>"WZ",
            'category'=>gT('Logic'),
            'sortorder'=>100,
            'inputtype'=>'text',
            "help"=>gT('Filter the available answers by this value'),
            "caption"=>gT('Code filter')
        );

        self::$attributes["commented_checkbox"] = array(
            "types"=>"P",
            'category'=>gT('Logic'),
            'sortorder'=>110,
            'inputtype'=>'singleselect',
            'options'=>array(
                "allways"=>gT('No control on checkbox'),
                "checked"=>gT('Checkbox is checked'),
                "unchecked"=>gT('Checkbox is unchecked'),
                ),
            'default' => "checked",
            'help'=>gT('Choose when user can add a comment'),
            'caption'=>gT('Comment only when')
        );

        self::$attributes["commented_checkbox_auto"] = array(
            "types"=>"P",
            'category'=>gT('Logic'),
            'sortorder'=>111,
            'inputtype'=>'switch',
            'options'=>array(
                "0"=>gT('No'),
                "1"=>gT('Yes'),
                ),
            'default' => "1",
            'help'=>gT('Use javascript function to remove text and uncheck checkbox (or use Expression Manager only).'),
            'caption'=>gT('Remove text or uncheck checkbox automatically')
        );

        self::$attributes["display_columns"] = array(
            "types"=>"LM",
            'category'=>gT('Display'),
            'sortorder'=>100,
            'inputtype'=>'columns',
            'default'=>'',
            "help"=>gT('The answer options will be distributed across the number of columns set here'),
            "caption"=>gT('Display columns')
        );

        self::$attributes["display_rows"] = array(
            "types"=>"QSTU",
            'category'=>gT('Display'),
            'sortorder'=>100,
            'inputtype'=>'integer',
            "help"=>gT('How many rows to display'),
            "caption"=>gT('Display rows')
        );

        self::$attributes["dropdown_dates"] = array(
            "types"=>"D",
            'category'=>gT('Display'),
            'sortorder'=>100,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'),
            1=>gT('Yes')),
            'default'=>0,
            "help"=>gT('Use accessible dropdown boxes instead of calendar popup'),
            "caption"=>gT('Display dropdown boxes')
        );

        self::$attributes["date_min"] = array(
            "types"=>"D",
            'category'=>gT('Display'),
            'sortorder'=>110,
            'inputtype'=>'text',
            'expression'=>2, /* What for "tomorrow" etc ....*/
            "help"=>gT('Minimum date, valide date in YYYY-MM-DD format or any English textual datetime description. Expression Managed can be used (only with YYYY-MM-DD format). For dropdown : only the year is restricted if date use variable not in same page.'),
            "caption"=>gT('Minimum date')
        );

        self::$attributes["date_max"] = array(
            "types"=>"D",
            'category'=>gT('Display'),
            'sortorder'=>111,
            'inputtype'=>'text',
            'expression'=>2,
            "help"=>gT('Maximum date, valide date in any English textual datetime description (YYYY-MM-DD for example). Expression Managed can be used (only with YYYY-MM-DD format) value. For dropdown : only the year is restricted if date use variable not in same page.'),
            "caption"=>gT('Maximum date')
        );

        self::$attributes["dropdown_prepostfix"] = array(
            "types"=>"1",
            'category'=>gT('Display'),
            'sortorder'=>112,
            'inputtype'=>'text',
            'i18n'=>true,
            "help"=>gT('Prefix|Suffix for dropdown lists'),
            "caption"=>gT('Dropdown prefix/suffix')
        );

        self::$attributes["dropdown_separators"] = array(
            "types"=>"1",
            'category'=>gT('Display'),
            'sortorder'=>120,
            'inputtype'=>'text',
            "help"=>gT('Text shown on each subquestion row between both scales in dropdown mode'),
            "caption"=>gT('Dropdown separator')
        );

        self::$attributes["dualscale_headerA"] = array(
            "types"=>"1",
            'category'=>gT('Display'),
            'sortorder'=>110,
            'inputtype'=>'text',
            'expression'=>1,
            'i18n'=>true,
            "help"=>gT('Enter a header text for the first scale'),
            "caption"=>gT('Header for first scale')
        );

        self::$attributes["dualscale_headerB"] = array(
            "types"=>"1",
            'category'=>gT('Display'),
            'sortorder'=>111,
            'inputtype'=>'text',
            'expression'=>1,
            'i18n'=>true,
            "help"=>gT('Enter a header text for the second scale'),
            "caption"=>gT('Header for second scale')
        );

        self::$attributes["equation"] = array(
            "types"=>"*",
            'category'=>gT('Logic'),
            'sortorder'=>100,
            'inputtype'=>'textarea',
            'expression'=>1,
            "help"=>gT('Final equation to set in database, defaults to question text.'),
            "caption"=>gT('Equation'),
            "default"=>""
        );

        self::$attributes["equals_num_value"] = array(
            "types"=>"K",
            'category'=>gT('Input'),
            'sortorder'=>100,
            'inputtype'=>'text',
            'expression'=>2,
            "help"=>gT('Multiple numeric inputs sum must equal this value'),
            "caption"=>gT('Equals sum value')
        );

        self::$attributes["em_validation_q"] = array(
            "types"=>":;ABCDEFHKMNOPQRSTU"."L!", // separate question with REAL subqs (in EM) and with FALSE subsq (where subqs are answer â€¦)
            'category'=>gT('Logic'),
            'sortorder'=>200,
            'inputtype'=>'textarea',
            'expression'=>2,
            "help"=>gT('Enter a boolean equation to validate the whole question.'),
            "caption"=>gT('Question validation equation')
        );

        self::$attributes["em_validation_q_tip"] = array(
            "types"=>":;ABCDEFHKMNOPQRSTU"."L!", // separate question with subqs (in EM) and without
            'category'=>gT('Logic'),
            'sortorder'=>210,
            'inputtype'=>'textarea',
            'expression'=>1,
            'i18n'=>true,
            "help"=>gT('This is a hint text that will be shown to the participant describing the question validation equation.'),
            "caption"=>gT('Question validation tip')
        );

        self::$attributes["em_validation_sq"] = array(
            "types"=>";:KQSTUN",
            'category'=>gT('Logic'),
            'sortorder'=>220,
            'inputtype'=>'textarea',
            'expression'=>2,
            "help"=>gT('Enter a boolean equation to validate each subquestion.'),
            "caption"=>gT('Subquestion validation equation')
        );

        self::$attributes["em_validation_sq_tip"] = array(
            "types"=>";:KQSTUN",
            'category'=>gT('Logic'),
            'sortorder'=>230,
            'inputtype'=>'textarea',
            'expression'=>1,
            'i18n'=>true,
            "help"=>gT('This is a tip shown to the participant describing the subquestion validation equation.'),
            "caption"=>gT('Subquestion validation tip')
        );

        self::$attributes["exclude_all_others"] = array(
            "types"=>"ABCEFMPKQ",
            'category'=>gT('Logic'),
            'sortorder'=>130,
            'inputtype'=>'text',
            "help"=>gT('Excludes all other options if a certain answer is selected - just enter the answer code(s) separated with a semicolon.'),
            "caption"=>gT('Exclusive option')
        );

        self::$attributes["exclude_all_others_auto"] = array(
            "types"=>"MP",
            'category'=>gT('Logic'),
            'sortorder'=>131,
            'inputtype'=>'switch',
            'options'=>array(1=>gT('Yes'), 0=>gT('No')),
            'default'=>0,
            "help"=>gT('If the participant marks all options, uncheck all and check the option set in the "Exclusive option" setting'),
            "caption"=>gT('Auto-check exclusive option if all others are checked')
        );

        // Map Options

        self::$attributes["location_city"] = array(
            "types"=>"S",
            'readonly_when_active'=>true,
            'category'=>gT('Location'),
            'sortorder'=>100,
            'inputtype'=>'singleselect',
            'default'=>0,
            'options'=>array(1=>gT('Yes'), 0=>gT('No')),
            "help"=>gT("Store the city?"),
            "caption"=>gT("Save city")
        );

        self::$attributes["location_state"] = array(
            "types"=>"S",
            'readonly_when_active'=>true,
            'category'=>gT('Location'),
            'sortorder'=>100,
            'default'=>0,
            'inputtype'=>'singleselect',
            'options'=>array(1=>gT('Yes'), 0=>gT('No')),
            "help"=>gT("Store the state?"),
            "caption"=>gT("Save state")
        );

        self::$attributes["location_postal"] = array(
            "types"=>"S",
            'readonly_when_active'=>true,
            'category'=>gT('Location'),
            'sortorder'=>100,
            'inputtype'=>'singleselect',
            'default'=>0,
            'options'=>array(1=>gT('Yes'), 0=>gT('No')),
            "help"=>gT("Store the postal code?"),
            "caption"=>gT("Save postal code")
        );

        self::$attributes["location_country"] = array(
            "types"=>"S",
            'readonly_when_active'=>true,
            'category'=>gT('Location'),
            'sortorder'=>100,
            'inputtype'=>'singleselect',
            'default'=>0,
            'options'=>array(1=>gT('Yes'), 0=>gT('No')),
            "help"=>gT("Store the country?"),
            "caption"=>gT("Save country")
        );

        self::$attributes["statistics_showmap"] = array(
            "types"=>"S",
            'category'=>gT('Statistics'),
            'inputtype'=>'switch',
            'sortorder'=>100,
            'options'=>array(1=>gT('Yes'), 0=>gT('No')),
            'help'=>gT("Show a map in the statistics?"),
            'caption'=>gT("Display map"),
            'default'=>1
        );

        self::$attributes["statistics_showgraph"] = array(
            'types'=>'15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*',
            'category'=>gT('Statistics'),
            'inputtype'=>'switch',
            'sortorder'=>101,
            'options'=>array(1=>gT('Yes'), 0=>gT('No')),
            'help'=>gT("Display a chart in the statistics?"),
            'caption'=>gT("Display chart"),
            'default'=>1
        );

        self::$attributes["statistics_graphtype"] = array(
            "types"=>'15ABCDEFGHIKLMNOQRSTUWXYZ!:;|*',
            'category'=>gT('Statistics'),
            'inputtype'=>'singleselect',
            'sortorder'=>102,
            'options'=>array(
                0=>gT('Bar chart'),
                1=>gT('Pie chart'),
                2=>gT('Radar'),
                3=>gT('Line'),
                4=>gT('PolarArea'),
                5=>gT('Doughnut'),
            ),
            'help'=>gT("Select the type of chart to be displayed"),
            'caption'=>gT("Chart type"),
            'default'=>0
        );

        self::$attributes["location_mapservice"] = array(
            "types"=>"S",
            'category'=>gT('Location'),
            'sortorder'=>90,
            'inputtype'=>'buttongroup',
            'options'=>array(
                0=>gT('Off'),
                100=>gT('OpenStreetMap via MapQuest', 'unescaped'),
                1=>gT('Google Maps', 'unescaped')
            ),
            'default' => 0,
            "help"=>gT("Activate this to show a map above the input field where the user can select a location"),
            "caption"=>gT("Use mapping service")
        );

        self::$attributes["location_mapwidth"] = array(
            "types"=>"S",
            'category'=>gT('Location'),
            'sortorder'=>102,
            'inputtype'=>'text',
            'default'=>'500',
            "help"=>gT("Map width in pixel"),
            "caption"=>gT("Map width")
        );

        self::$attributes["location_mapheight"] = array(
            "types"=>"S",
            'category'=>gT('Location'),
            'sortorder'=>103,
            'inputtype'=>'text',
            'default'=>'300',
            "help"=>gT("Map height in pixel"),
            "caption"=>gT("Map height")
        );

        self::$attributes["location_nodefaultfromip"] = array(
            "types"=>"S",
            'category'=>gT('Location'),
            'sortorder'=>91,
            'inputtype'=>'singleselect',
            'options'=>array(0=>gT('Yes'), 1=>gT('No')),
            'default' => 0,
            "help"=>gT("Get the default location using the user's IP address?"),
            "caption"=>gT("IP as default location")
        );

        self::$attributes["location_defaultcoordinates"] = array(
            "types"=>"S",
            'category'=>gT('Location'),
            'sortorder'=>101,
            'inputtype'=>'text',
            'expression'=>1,/* As static */
            "help"=>gT('Default coordinates of the map when the page first loads. Format: latitude [space] longtitude'),
            "caption"=>gT('Default position')
        );

        self::$attributes["location_mapzoom"] = array(
            "types"=>"S",
            'category'=>gT('Location'),
            'sortorder'=>101,
            'inputtype'=>'text',
            'default'=>'11',
            "help"=>gT("Map zoom level"),
            "caption"=>gT("Zoom level")
        );

        // End Map Options

        self::$attributes["hide_tip"] = array(
            "types"=>"15ABCDEFGHIKLMNOPQRSTUXY!:;|",
            'category'=>gT('Display'),
            'sortorder'=>100,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT('Hide the tip that is normally shown with a question'),
            "caption"=>gT('Hide tip')
        );

        self::$attributes['hidden'] = array(
            'types'=>'15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*',
            'category'=>gT('Display'),
            'sortorder'=>101,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            'help'=>gT('Hide this question at any time. This is useful for including data using answer prefilling.'),
            'caption'=>gT('Always hide this question')
        );

        self::$attributes['cssclass'] = array(
            'types'=>'15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|*',
            'category'=>gT('Display'),
            'sortorder'=>102,
            'inputtype'=>'text',
            'expression'=>1, /* As static */
            'help'=>gT('Add additional CSS class(es) for this question. Use a space between multiple CSS class names. You may use expressions - remember this part is static.'),
            'caption'=>gT('CSS class(es)')
        );

        self::$attributes["max_answers"] = array(
            "types"=>"MPR1:;ABCEFKQ",
            'category'=>gT('Logic'),
            'sortorder'=>11,
            'inputtype'=>'text',
            'expression'=>2,
            "help"=>gT('Limit the number of possible answers'),
            "caption"=>gT('Maximum answers')
        );

        self::$attributes["max_num_value"] = array(
            "types"=>"K",
            'category'=>gT('Input'),
            'sortorder'=>100,
            'inputtype'=>'text',
            'expression'=>2,
            "help"=>gT('Maximum sum value of multiple numeric input'),
            "caption"=>gT('Maximum sum value')
        );

        self::$attributes["max_num_value_n"] = array(
            "types"=>"NK",
            'category'=>gT('Input'),
            'sortorder'=>110,
            'inputtype'=>'text',
            'expression'=>2,
            "help"=>gT('Maximum value of the numeric input'),
            "caption"=>gT('Maximum value')
        );

        /* Ranking specific : max DB answer */
        self::$attributes["max_subquestions"] = array(
            "types"=>"R",
            'readonly_when_active'=>true,
            'category'=>gT('Logic'),
            'sortorder'=>12,
            'inputtype'=>'integer',
            'default'=>'',
            "help"=>gT('Limit the number of possible answers fixed by number of columns in database'),
            "caption"=>gT('Maximum columns for answers')
        );

        self::$attributes["maximum_chars"] = array(
            "types"=>"STUNQK:;",
            'category'=>gT('Input'),
            'sortorder'=>100,
            'inputtype'=>'integer',
            'default'=>'',
            "help"=>gT('Maximum characters allowed'),
            "caption"=>gT('Maximum characters')
        );

        self::$attributes["min_answers"] = array(
            "types"=>"MPR1:;ABCEFKQ",
            'category'=>gT('Logic'),
            'sortorder'=>10,
            'inputtype'=>'text',
            'expression'=>2,
            "help"=>gT('Ensure a minimum number of possible answers (0=No limit)'),
            "caption"=>gT('Minimum answers')
        );

        self::$attributes["min_num_value"] = array(
            "types"=>"K",
            'category'=>gT('Input'),
            'sortorder'=>100,
            'inputtype'=>'text',
            'expression'=>2,
            "help"=>gT('The sum of the multiple numeric inputs must be greater than this value'),
            "caption"=>gT('Minimum sum value')
        );

        self::$attributes["min_num_value_n"] = array(
            "types"=>"NK",
            'category'=>gT('Input'),
            'sortorder'=>100,
            'inputtype'=>'text',
            'expression'=>2,
            "help"=>gT('Minimum value of the numeric input'),
            "caption"=>gT('Minimum value')
        );

        self::$attributes["multiflexible_max"] = array(
            "types"=>":",
            'category'=>gT('Display'),
            'sortorder'=>112,
            'inputtype'=>'text',
            'expression'=>2, // Really ? Only if shown as text then
            "help"=>gT('Maximum value for array(mult-flexible) question type'),
            "caption"=>gT('Maximum value')
        );

        self::$attributes["multiflexible_min"] = array(
            "types"=>":",
            'category'=>gT('Display'),
            'sortorder'=>110,
            'inputtype'=>'text',
            'expression'=>2, // Really ? Only if shown as text then
            "help"=>gT('Minimum value for array(multi-flexible) question type'),
            "caption"=>gT('Minimum value')
        );

        self::$attributes["multiflexible_step"] = array(
            "types"=>":",
            'category'=>gT('Display'),
            'sortorder'=>111,
            'inputtype'=>'float',
            'default'=>'',
            "help"=>gT('Step value'),
            "caption"=>gT('Step value')
        );

        self::$attributes["multiflexible_checkbox"] = array(
            "types"=>":",
            'category'=>gT('Display'),
            'sortorder'=>100,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT('Use checkbox layout'),
            "caption"=>gT('Checkbox layout')
        );

        self::$attributes["reverse"] = array(
            "types"=>"D:",
            'category'=>gT('Display'),
            'sortorder'=>100,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT('Present answer options in reverse order'),
            "caption"=>gT('Reverse answer order')
        );

        self::$attributes["num_value_int_only"] = array(
            "types"=>"NK",
            'category'=>gT('Input'),
            'sortorder'=>100,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT('Restrict input to integer values'),
            "caption"=>gT('Integer only')
        );

        self::$attributes["numbers_only"] = array(
            "types"=>"Q;S*",
            'category'=>gT('Other'),
            'sortorder'=>150,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT('Allow only numerical input'),
            "caption"=>gT('Numbers only')
        );

        self::$attributes['show_totals'] = array(
            'types' =>    ';',
            'category' =>    gT('Other'),
            'sortorder' =>    151,
            'inputtype'    => 'buttongroup',
            'options' =>    array(
                'X' =>    gT('Off', 'unescaped'),
                'R' =>    gT('Rows', 'unescaped'),
                'C' =>    gT('Columns', 'unescaped'),
                'B' =>    gT('Rows & columns', 'unescaped')
            ),
            'default' =>    'X',
            'help' =>    gT('Show totals for either rows, columns or both rows and columns'),
            'caption' =>    gT('Show totals for')
        );

        self::$attributes['show_grand_total'] = array(
            'types' =>    ';',
            'category' =>    gT('Other'),
            'sortorder' =>    152,
            'inputtype' =>    'switch',
            'options' =>array(0=>gT('No'), 1=>gT('Yes')),
            'default' =>    0,
            'help' =>    gT('Show grand total for either columns or rows'),
            'caption' =>    gT('Show grand total')
        );

        self::$attributes["input_size"] = array(
            "types"=>"STUQNMK:;",
            'category'=>gT('Display'),
            'sortorder'=>100,
            'inputtype'=>'integer',
            'default'=>'',
            "help"=>gT("Set the size to the input or textarea, the input will be displayed with approximately this size in width."),
            "caption"=>gT("Text input size")
        );

        self::$attributes["input_boxes"] = array(
        "types"=>":",
            'category'=>gT('Display'),
            'sortorder'=>110,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT("Present as text input boxes instead of dropdown lists"),
            "caption"=>gT("Text inputs")
        );

        self::$attributes["other_comment_mandatory"] = array(
            "types"=>"PLW!Z",
            'category'=>gT('Logic'),
            'sortorder'=>100,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT("Make the 'Other:' comment field mandatory when the 'Other:' option is active"),
            "caption"=>gT("'Other:' comment mandatory")
        );

        self::$attributes["other_numbers_only"] = array(
            "types"=>"LMP",
            'category'=>gT('Logic'),
            'sortorder'=>100,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT("Allow only numerical input for 'Other' text"),
            "caption"=>gT("Numbers only for 'Other'")
        );

        self::$attributes["other_replace_text"] = array(
            "types"=>"LMPWZ!",
            'category'=>gT('Display'),
            'sortorder'=>100,
            'inputtype'=>'text',
            'expression'=>1,
            'i18n'=>true,
            "help"=>gT("Replaces the label of the 'Other:' answer option with a custom text"),
            "caption"=>gT("Label for 'Other:' option")
        );

        self::$attributes["page_break"] = array(
            "types"=>"15ABCDEFGHKLMNOPQRSTUWXYZ!:;|*",
            'category'=>gT('Other'),
            'sortorder'=>100,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT('Insert a page break before this question in printable view by setting this to Yes.'),
            "caption"=>gT('Insert page break in printable view')
        );

        self::$attributes["prefix"] = array(
            "types"=>"KNQS",
            'category'=>gT('Display'),
            'sortorder'=>10,
            'inputtype'=>'text',
            'expression'=>1,
            'i18n'=>true,
            "help"=>gT('Add a prefix to the answer field'),
            "caption"=>gT('Answer prefix')
        );

        self::$attributes["printable_help"] = array(
            "types"=>"15ABCDEFGHKLMNOPRWYZ!:*",
            'category'=>gT('Display'),
            'sortorder'=>201,
            "inputtype"=>"text",
            'expression'=>1, // Must control if yes
            'i18n'=>true,
            'default'=>"",
            "help"=>gT('In the printable version replace the relevance equation with this explanation text.'),
            "caption"=>gT("Relevance help for printable survey")
        );

        self::$attributes["public_statistics"] = array(
            "types"=>"15ABCEFGHKLMNOPRWYZ!:*",
            'category'=>gT('Statistics'),
            'sortorder'=>80,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT('Show statistics of this question in the public statistics page'),
            "caption"=>gT('Show in public statistics')
        );

        self::$attributes["random_order"] = array(
            "types"=>"!ABCEFHKLMOPQRWZ1:;",
            'category'=>gT('Display'),
            'sortorder'=>100,
            'inputtype'=>'singleselect',
            'options'=>array(0=>gT('No'), 1=>gT("Yes")),
            //1=>gT('Randomize on each page load')  // Shnoulle : replace by yes till we have only one solution
            //2=>gT('Randomize once on survey start')  //Mdekker: commented out as code to handle this was removed in refactoring
            'default'=>0,
            "help"=>gT('Present subquestions/answer options in random order'),
            "caption"=>gT('Random order')
        );

        self::$attributes["showpopups"] = array(
            "types"=>"R",
            'category'=>gT('Display'),
            'sortorder'=>110,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>1,
            "caption"=>gT('Show javascript alert'),
            "help"=>gT('Show an alert if answers exceeds the number of max answers')
        );

        self::$attributes["samechoiceheight"] = array(
            "types"=>"R",
            'category'=>gT('Display'),
            'sortorder'=>120,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>1,
            "caption"=>gT('Same height for all answer options'),
            "help"=>gT('Force each answer option to have the same height.').' '.gT('If you have a lot of items and use a filter you can disable this to improve browser speed.')
        );

        self::$attributes["samelistheight"] = array(
            "types"=>"R",
            'category'=>gT('Display'),
            'sortorder'=>121,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>1,
            "caption"=>gT('Same height for lists'),
            "help"=>gT('Force the choice list and the rank list to have the same height.').' '.gT('If you have a lot of items and use a filter you can disable this to improve browser speed.')
        );

        self::$attributes["parent_order"] = array(
            "types"=>":",
            'category'=>gT('Display'),
            'sortorder'=>100,
            'inputtype'=>'text',
            "caption"=>gT('Get order from previous question'),
            "help"=>gT('Enter question ID to get subquestion order from a previous question')
        );

        self::$attributes["slider_layout"] = array(
        "types"=>"K",
            'category'=>gT('Slider'),
            'sortorder'=>100,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT('Use slider layout'),
            "caption"=>gT('Use slider layout')
        );

        self::$attributes["slider_min"] = array(
            "types"=>"K",
            'category'=>gT('Slider'),
            'sortorder'=>110,
            'inputtype'=>'text',
            'expression'=>2,
            "help"=>gT('You can use Expression manager, but this must be a number before showing the page else set to 0. If minimum value is not set, this value is used.'),
            "caption"=>gT('Slider minimum value')
        );

        self::$attributes["slider_max"] = array(
            "types"=>"K",
            'category'=>gT('Slider'),
            'sortorder'=>120,
            'inputtype'=>'text',
            'expression'=>2,
            "help"=>gT('You can use Expression manager, but this must be a number before showing the page else set to 100. If maximum value is not set, this value is used.'),
            "caption"=>gT('Slider maximum value')
        );

        self::$attributes["slider_accuracy"] = array(
            "types"=>"K",
            'category'=>gT('Slider'),
            'sortorder'=>130,
            'inputtype'=>'text',
            'expression'=>2,
            "help"=>gT('You can use Expression manager, but this must be a number before showing the page else set to 1.'),
            "caption"=>gT('Slider accuracy')
        );

        self::$attributes["slider_middlestart"] = array(
            "types"=>"K",
            'category'=>gT('Slider'),
            'sortorder'=>200,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT('The handle is displayed at the middle of the slider except if Slider initial value is set (this will not set the initial value).'),
            "caption"=>gT('Slider starts at the middle position')
        );

        self::$attributes["slider_default"] = array(
            "types"=>"K",
            'category'=>gT('Slider'),
            'sortorder'=>210,
            'inputtype'=>'text',
            'expression'=>2, // must be controlled : unsure
            'default'=>"",
            "help"=>gT('Slider start as this value. You can use Expression manager, but this must be a number before showing the page. This setting has priority over slider starts at the middle position.'),
            "caption"=>gT('Slider initial value')
        );

        self::$attributes["slider_default_set"] = array(
            "types"=>"K",
            'category'=>gT('Slider'),
            'sortorder'=>220,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>1, /* before 3.0 : this is the default behaviour */
            "help"=>gT('When using slider initial value set this value at survey start.'),
            "caption"=>gT('Slider initial value set at start')
        );

        self::$attributes["slider_orientation"] = array(
            "types"=>"K",
            'category'=>gT('Slider'),
            'sortorder'=>300,
            'inputtype'=>'buttongroup',
            'options'=>array(
                0=>gT('Horizontal', 'unescaped'),
                1=>gT('Vertical', 'unescaped')
            ),
            'default'=>0,
            "help"=>gT('Set the orientation.'),
            "caption"=>gT('Orientation')
        );

        self::$attributes["slider_handle"] = array(
            "types"=>"K",
            'category'=>gT('Slider'),
            'sortorder'=>320,
            'inputtype'=>'singleselect',
            'options'=>array(
                0=>gT('Circle'),
                1=>gT('Square'),
                2=>gT('Triangle'),
                3=>gT('Custom')
            ),
            'default'=>0,
            "help"=>gT("Set the handle shape. 'Custom' is defined in CSS using the Font Awesome font."),
            "caption"=>gT('Handle shape')
        );

        self::$attributes["slider_custom_handle"] = array(
            "types"=>"K",
            'category'=>gT('Slider'),
            'sortorder'=>321,
            'inputtype'=>'text',
            'default'=>'f1ae',
            "help"=>gT('Accepts Font Awesome Unicode characters.'),
            "caption"=>gT('Custom handle Unicode code')
        );

        self::$attributes["slider_rating"] = array(
            "types"=>"5",
            'category'=>gT('Display'),
            'sortorder'=>90,
            'inputtype'=>'buttongroup',
            'options'=>array(
                0=>gT('Off', 'unescaped'),
                1=>gT('Stars', 'unescaped'),
                2=>gT('Slider with emoticon', 'unescaped'),
            ),
            'default'=>0,
            "help"=>gT('Use slider layout'),
            "caption"=>gT('Use slider layout')
        );

        self::$attributes["slider_reversed"] = array(
            "types"=>"K",
            'category'=>gT('Slider'),
            'sortorder'=>310,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT('Reverses the slider direction and repositions the min/max text accordingly.'),
            "caption"=>gT('Reverse the slider direction')
        );

        self::$attributes["slider_reset"] = array(
            "types"=>"K",
            'category'=>gT('Slider'),
            'sortorder'=>230,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT('Add a button to reset the slider. If you choose an start value, it reset at start value, else empty the answer.'),
            "caption"=>gT('Allow reset the slider')
        );

        self::$attributes["slider_showminmax"] = array(
            "types"=>"K",
            'category'=>gT('Slider'),
            'sortorder'=>150,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT('Display min and max value under the slider'),
            "caption"=>gT('Display slider min and max value')
        );

        self::$attributes["slider_separator"] = array(
            "types"=>"K",
            'category'=>gT('Slider'),
            'sortorder'=>160,
            'inputtype'=>'text',
            "help"=>gT('Answer|Left-slider-text|Right-slider-text separator character'),
            'default'=>'|',
            "caption"=>gT('Slider left/right text separator')
        );

        self::$attributes["suffix"] = array(
            "types"=>"KNQS",
            'category'=>gT('Display'),
            'sortorder'=>11,
            'inputtype'=>'text',
            'expression'=>1,
            'i18n'=>true,
            "help"=>gT('Add a suffix to the answer field'),
            "caption"=>gT('Answer suffix')
        );

        self::$attributes["text_input_width"] = array(
        "types"=>"KNSTU",
        'category'=>gT('Display'),
        'sortorder'=>100,
        'inputtype'=>'singleselect',
        'default'=>'',
        'options'=>array(
            ''=>gT("Default"),
            1=>'8%',
            2=>'17%',
            3=>'25%',
            4=>'33%',
            5=>'41%',
            6=>'50%',
            7=>'58%',
            8=>'67%',
            9=>'75%',
            10=>'83%',
            11=>'92%',
            12=>'100%'
        ),
        "help"=>gT('Relative width of the text input wrapper element'),
        "caption"=>gT('Text input box width'));

        /* Do EXACTLY the same than text_input_width for K(multinum): must move K here and rename in a DB update and remove it + fix when import*/
        self::$attributes["text_input_columns"] = array(
        "types"=>"QP",
        'category'=>gT('Display'),
        'sortorder'=>90,
        'inputtype'=>'singleselect',
        'default'=>'',
        'options'=>array(
            ''=>gT("Default"),
            1=>'8%',
            2=>'17%',
            3=>'25%',
            4=>'33%',
            5=>'41%',
            6=>'50%',
            7=>'58%',
            8=>'67%',
            9=>'75%',
            10=>'83%',
            11=>'92%',
            12=>'100%'
        ),
        "help"=>gT('Relative width of the text input wrapper element'),
        "caption"=>gT('Text input box width'));

        self::$attributes["label_input_columns"] = array(
        "types"=>"KQ",
        'category'=>gT('Display'),
        'sortorder'=>91,
        'inputtype'=>'singleselect',
        'default'=>'',
        'options'=>array(
            ''=>gT("Default"),
            'hidden'=>gT("Hidden"), /* can not use 0, sometimes we don't test with === */
            1=>'8%',
            2=>'17%',
            3=>'25%',
            4=>'33%',
            5=>'41%',
            6=>'50%',
            7=>'58%',
            8=>'67%',
            9=>'75%',
            10=>'83%',
            11=>'92%',
            12=>'100%'
        ),
        "help"=>gT('Relative width of the labels'),
        "caption"=>gT('Label column width'));

        /* Same than label_input_columns for multiple choice*/
        self::$attributes["choice_input_columns"] = array(
        "types"=>"P",
        'category'=>gT('Display'),
        'sortorder'=>90,
        'inputtype'=>'singleselect',
        'default'=>'',
        'options'=>array(
            ''=>gT("Default"),
            1=>'8%',
            2=>'17%',
            3=>'25%',
            4=>'33%',
            5=>'41%',
            6=>'50%',
            7=>'58%',
            8=>'67%',
            9=>'75%',
            10=>'83%',
            11=>'92%',
            12=>'100%'
        ),
        "help"=>gT('Relative width of checkbox wrapper element'),
        "caption"=>gT('Choice column width'));

        self::$attributes["use_dropdown"] = array(
            "types"=>"1FO",
            'category'=>gT('Display'),
            'sortorder'=>112,
            'inputtype'=>'switch',
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT('Present dropdown control(s) instead of list of radio buttons'),
            "caption"=>gT('Use dropdown presentation')
        );


        self::$attributes["dropdown_size"] = array(
            "types"=>"!", // TODO add these later?  "1F",
            'category'=>gT('Display'),
            'sortorder'=>200,
            'inputtype'=>'text',
            'default'=>'',
            "help"=>gT('For list dropdown boxes, show up to this many rows'),
            "caption"=>gT('Height of dropdown')
        );

        self::$attributes["dropdown_prefix"] = array(
            "types"=>"!", // TODO add these later?  "1F",
            'category'=>gT('Display'),
            'sortorder'=>201,
            'inputtype'=>'buttongroup',
            'options'=>array(
                0=>gT('None', 'unescaped'),
                1=>gT('Order - like 3)', 'unescaped'),
                // 2=>gT('Code - like A1','unescaped'), // Just an idea ;)
            ),
            'default'=>0,
            "help"=>gT('Accelerator keys for list items'),
            "caption"=>gT('Prefix for list items')
        );

        self::$attributes["scale_export"] = array(
            "types"=>"CEFGHLMOPWYZ1!:*",
            'category'=>gT('Other'),
            'sortorder'=>100,
            'inputtype'=>'singleselect',
            'options'=>array(0=>gT('Default'),
            1=>gT('Nominal'),
            2=>gT('Ordinal'),
            3=>gT('Scale')),
            'default'=>0,
            "help"=>gT("Set a specific SPSS export scale type for this question"),
            "caption"=>gT('SPSS export scale type')
        );

        self::$attributes["choice_title"] = array(
            "types"=>"R",
            'category'=>gT('Other'),
            'sortorder'=>200,
            "inputtype"=>"text",
            'expression'=>1,
            'i18n'=>true,
            'default'=>"",
            "help"=>sprintf(gT("Replace choice header (default: \"%s\")"), gT("Your choices")),
            "caption"=>gT("Choice header")
        );

        self::$attributes["rank_title"] = array(
            "types"=>"R",
            'category'=>gT('Other'),
            'sortorder'=>201,
            "inputtype"=>"text",
            'expression'=>1,
            'i18n'=>true,
            'default'=>"",
            "help"=>sprintf(gT("Replace rank header (default: \"%s\")"), gT("Your ranking")),
            "caption"=>gT("Rank header")
        );

        //Timer attributes
        self::$attributes["time_limit"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>90,
            "inputtype"=>"integer",
            "help"=>gT("Limit time to answer question (in seconds)"),
            "caption"=>gT("Time limit")
        );

        self::$attributes["time_limit_action"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>92,
            'inputtype'=>'singleselect',
            'options'=>array(
                1=>gT('Warn and move on'),
                2=>gT('Move on without warning'),
                3=>gT('Disable only')
            ),
            "default" => 1,
            "help"=>gT("Action to perform when time limit is up"),
            "caption"=>gT("Time limit action")
        );

        self::$attributes["time_limit_disable_next"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>94,
            "inputtype"=>"switch",
            'default'=>0,
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            "help"=>gT("Disable the next button until time limit expires"),
            "caption"=>gT("Time limit disable next")
        );

        self::$attributes["time_limit_disable_prev"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>96,
            "inputtype"=>"switch",
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>0,
            "help"=>gT("Disable the prev button until the time limit expires"),
            "caption"=>gT("Time limit disable prev")
        );

        self::$attributes["time_limit_countdown_message"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>98,
            "inputtype"=>"textarea",
            'expression'=>1,
            'i18n'=>true,
            "help"=>gT("The text message that displays in the countdown timer during the countdown"),
            "caption"=>gT("Time limit countdown message")
        );

        self::$attributes["time_limit_timer_style"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>100,
            "inputtype"=>"textarea",
            "help"=>gT("CSS Style for the message that displays in the countdown timer during the countdown"),
            "caption"=>gT("Time limit timer CSS style")
        );

        self::$attributes["time_limit_message_delay"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>102,
            "inputtype"=>"integer",
            "help"=>gT("Display the 'time limit expiry message' for this many seconds before performing the 'time limit action' (defaults to 1 second if left blank)"),
            "caption"=>gT("Time limit expiry message display time")
        );

        self::$attributes["time_limit_message"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>104,
            "inputtype"=>"textarea",
            'expression'=>1,
            'i18n'=>true,
            "help"=>gT("The message to display when the time limit has expired (a default message will display if this setting is left blank)"),
            "caption"=>gT("Time limit expiry message")
        );

        self::$attributes["time_limit_message_style"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>106,
            "inputtype"=>"textarea",
            "help"=>gT("CSS style for the 'time limit expiry message'"),
            "caption"=>gT("Time limit message CSS style")
        );

        self::$attributes["time_limit_warning"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>108,
            "inputtype"=>"integer",
            "help"=>gT("Display a 'time limit warning' when there are this many seconds remaining in the countdown (warning will not display if left blank)"),
            "caption"=>gT("1st time limit warning message timer")
        );

        self::$attributes["time_limit_warning_display_time"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>110,
            "inputtype"=>"integer",
            "help"=>gT("The 'time limit warning' will stay visible for this many seconds (will not turn off if this setting is left blank)"),
            "caption"=>gT("1st time limit warning message display time")
        );

        self::$attributes["time_limit_warning_message"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>112,
            "inputtype"=>"textarea",
            'expression'=>1,
            'i18n'=>true,
            "help"=>gT("The message to display as a 'time limit warning' (a default warning will display if this is left blank)"),
            "caption"=>gT("1st time limit warning message")
        );

        self::$attributes["time_limit_warning_style"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>114,
            "inputtype"=>"textarea",
            "help"=>gT("CSS style used when the 'time limit warning' message is displayed"),
            "caption"=>gT("1st time limit warning CSS style")
        );

        self::$attributes["time_limit_warning_2"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>116,
            "inputtype"=>"integer",
            "help"=>gT("Display the 2nd 'time limit warning' when there are this many seconds remaining in the countdown (warning will not display if left blank)"),
            "caption"=>gT("2nd time limit warning message timer")
        );

        self::$attributes["time_limit_warning_2_display_time"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>118,
            "inputtype"=>"integer",
            "help"=>gT("The 2nd 'time limit warning' will stay visible for this many seconds (will not turn off if this setting is left blank)"),
            "caption"=>gT("2nd time limit warning message display time")
        );

        self::$attributes["time_limit_warning_2_message"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>120,
            "inputtype"=>"textarea",
            'expression'=>1,
            'i18n'=>true,
            "help"=>gT("The 2nd message to display as a 'time limit warning' (a default warning will display if this is left blank)"),
            "caption"=>gT("2nd time limit warning message")
        );

        self::$attributes["time_limit_warning_2_style"] = array(
            "types"=>"STUXL!",
            'category'=>gT('Timer'),
            'sortorder'=>122,
            "inputtype"=>"textarea",
            "help"=>gT("CSS style used when the 2nd 'time limit warning' message is displayed"),
            "caption"=>gT("2nd time limit warning CSS style")
        );

        self::$attributes["date_format"] = array(
            "types"=>"D",
            'category'=>gT('Input'),
            'sortorder'=>100,
            "inputtype"=>"text",
            "help"=>gT("Specify a custom date/time format (the <i>d/dd m/mm yy/yyyy H/HH M/MM</i> formats and \"-./: \" characters are allowed for day/month/year/hour/minutes without or with leading zero respectively. Defaults to survey's date format"),
            "caption"=>gT("Date/Time format")
        );

        self::$attributes["dropdown_dates_minute_step"] = array(
            "types"=>"D",
            'category'=>gT('Input'),
            'sortorder'=>100,
            "inputtype"=>"integer",
            'default'=>1,
            "help"=>gT("Visual minute step interval"),
            "caption"=>gT("Minute step interval")
        );

        self::$attributes["dropdown_dates_month_style"] = array(
            "types"=>"D",
            'category'=>gT('Display'),
            'sortorder'=>100,
            "inputtype"=>"buttongroup",
            'options'=>array(
                0=>gT('Short names', 'unescaped'),
                1=>gT('Full names', 'unescaped'),
                2=>gT('Numbers', 'unescaped')
            ),
            'default'=>0,
            "help"=>gT("Change the display style of the month when using select boxes"),
            "caption"=>gT("Month display style")
        );

        self::$attributes["show_title"] = array(
            "types"=>"|",
            'category'=>gT('File metadata'),
            'sortorder'=>124,
            "inputtype"=>"switch",
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>1,
            "help"=>gT("Is the participant required to give a title to the uploaded file?"),
            "caption"=>gT("Show title")
        );

        self::$attributes["show_comment"] = array(
            "types"=>"|",
            'category'=>gT('File metadata'),
            'sortorder'=>126,
            "inputtype"=>"switch",
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>1,
            "help"=>gT("Is the participant required to give a comment to the uploaded file?"),
            "caption"=>gT("Show comment")
        );


        self::$attributes["max_filesize"] = array(
            "types"=>"|",
            'category'=>gT('Other'),
            'sortorder'=>128,
            "inputtype"=>"integer",
            'default'=>10240,
            "help"=>sprintf(gT("The participant cannot upload a single file larger than this size. Server configuration allow a maximum file size of %s KB."),getMaximumFileUploadSize()/1024),
            "caption"=>gT("Maximum file size allowed (in KB)")
        );

        self::$attributes["max_num_of_files"] = array(
            "types"=>"|",
            'category'=>gT('Other'),
            'sortorder'=>130,
            "inputtype"=>"integer",
            'min'=>1,
            'default'=>'1',
            "help"=>gT("Maximum number of files that the participant can upload for this question"),
            "caption"=>gT("Max number of files")
        );

        self::$attributes["min_num_of_files"] = array(
            "types"=>"|",
            'category'=>gT('Other'),
            'sortorder'=>132,
            "inputtype"=>"integer",
            'default'=>'0',
            'min'=>0,
            "help"=>gT("Minimum number of files that the participant must upload for this question"),
            "caption"=>gT("Min number of files")
        );

        self::$attributes["allowed_filetypes"] = array(
            "types"=>"|",
            'category'=>gT('Other'),
            'sortorder'=>134,
            "inputtype"=>"text",
            'default'=>"png, gif, doc, odt, jpg, pdf, png",
            "help"=>gT("Allowed file types in comma separated format. e.g. pdf,doc,odt"),
            "caption"=>gT("Allowed file types")
        );

        self::$attributes["random_group"] = array(
            "types"=>"15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|",
            'category'=>gT('Logic'),
            'sortorder'=>180,
            'inputtype'=>'text',
            "help"=>gT("Place questions into a specified randomization group, all questions included in the specified group will appear in a random order"),
            "caption"=>gT("Randomization group name")
        );

        // This is added to support historical behavior.  Early versions of 1.92 used a value of "No", so if there was a min_sum_value or equals_sum_value, the question was not valid
        // unless those criteria were met.  In later releases of 1.92, the default was changed so that missing values were allowed even if those attributes were set
        // This attribute lets authors control whether missing values should be allowed in those cases without needing to set min_answers
        // Existing surveys will use the old behavior, but if the author edits the question, the default will be the new behavior.
        self::$attributes["value_range_allows_missing"] = array(
            "types"=>"K",
            'category'=>gT('Input'),
            'sortorder'=>100,
            "inputtype"=>"switch",
            'options'=>array(0=>gT('No'), 1=>gT('Yes')),
            'default'=>1,
            "help"=>gT("Is no answer (missing) allowed when either 'Equals sum value' or 'Minimum sum value' are set?"),
            "caption"=>gT("Value range allows missing")
        );
        /*
        Deactivated because it does not work properly
        self::$attributes["thousands_separator"] = array(
            'types' => 'NK',
            "help" => gT("Show a thousands separator when the user enters a value"),
            "caption" => gT("Thousands separator"),
            'category' => gT('Display'),
            'inputtype' => 'singleselect',
            'sortorder' => 100,
            'options' => array(
                0 => gT('No'),
                1 => gT('Yes')
            ),
            'default'=>0,
        );
        */

        self::$attributes["display_type"] = array(
            "types"=>"YG",
            'category'=>gT('Display'),
            'sortorder'=>90,
            'inputtype'=>'buttongroup',
            'options'=>array(
                0=>gT('Button group', 'unescaped'),
                1=>gT('Radio list', 'unescaped')
            ),
            'default'=>0,
            "help"=>gT('Use button group or radio list'),
            "caption"=>gT('Display type')
        );

        self::$attributes["question_template"] = array(
            "types"=>"15ABCDEFGHIKLMNOPQRSTUWXYZ!:;|",
            'category'=>gT('Display'),
            'sortorder'=>100,
            'inputtype'=>'question_template',
            'options'=>array(),
            'default' => "core",
            "help"=>gT('Use a customized question theme for this question'),
            "caption"=>gT('Question theme')
        );

        /**
         * New event to allow plugin to add own question attribute (settings)
         * Using $event->append('questionAttributes', $questionAttributes);
         * $questionAttributes=[
         *  attributeName=>[
         *      'types' : Aply to this question type
         *      'category' : Where to put it
         *      'sortorder' : Qort order in this category
         *      'inputtype' : type of input
         *      'expression' : 2 to force Exprerssion Manager when see the survey logic file (add { } and validate, 1 : allow it : validate in survey logic file
         *      'options' : optionnal options if input type need it
         *      'default' : the default value
         *      'caption' : the label
         *      'help' : an help
         *  ]
         */
        $event = new \LimeSurvey\PluginManager\PluginEvent('newQuestionAttributes');
        $result = App()->getPluginManager()->dispatchEvent($event);
        /* Cast as array , or test if exist , or set to an empty array at start (or to self::$attributes : and do self::$attributes=$result->get('questionAttributes') directly ) ? */
        $questionAttributes = (array) $result->get('questionAttributes');
        self::$attributes = array_merge(self::$attributes, $questionAttributes);

        return self::$attributes;
    }

    /**
     * Return the question attributes definition by question type
     * @param $sType: type pof question
     * @return array : the attribute settings for this question type
     */
    public static function getQuestionAttributesSettings($sType)
    {
        if (!isset(self::$questionAttributesSettings[$sType])) {
            self::$questionAttributesSettings[$sType] = array();
            self::getAttributesDefinitions(); /* we need to have self::$attributes */
            /* Filter to get this question type setting */
            $aQuestionTypeAttributes = array_filter(self::$attributes, function($attribute) use ($sType) {
                return stripos($attribute['types'], $sType) !== false;
            });
            foreach ($aQuestionTypeAttributes as $attribute=>$settings) {
                  self::$questionAttributesSettings[$sType][$attribute] = array_merge(
                      QuestionAttribute::getDefaultSettings(),
                      array("category"=>gT("Plugins")),
                      $settings,
                      array("name"=>$attribute)
                  );
            }
        }
        return self::$questionAttributesSettings[$sType];
    }

    /**
     * Return the question Theme custom attributes values
     * @param $sQuestionThemeName: question theme name
     * @return array : the attribute settings for this question type
     */
    public static function getQuestionThemeAttributeValues($sQuestionThemeName = null, $question_template = null)
    {
        libxml_disable_entity_loader(false);

        $sCoreThemeXmlPath = Yii::app()->getConfig('corequestionthemerootdir').'/'.$sQuestionThemeName.'/survey/questions/answer/'.$question_template.'/config.xml';
        $sUserThemeXmlPath = Yii::app()->getConfig("userquestionthemerootdir").'/'.$sQuestionThemeName.'/survey/questions/answer/'.$question_template.'/config.xml';

        $xml_config = is_file($sCoreThemeXmlPath) ? simplexml_load_file($sCoreThemeXmlPath) :  simplexml_load_file($sUserThemeXmlPath);
        $custom_attributes = json_decode(json_encode((array)$xml_config->custom_attributes), TRUE);
        libxml_disable_entity_loader(true);

        if(!empty($custom_attributes['attribute']['name'])) {
            // Only one attribute set in config : need an array of attributes
            $custom_attributes['attribute'] = array($custom_attributes['attribute']);
        }

        $defaultQuestionAttributeValues = QuestionAttribute::getDefaultSettings();
        $additionalAttributes = array();
        // Create array of attribute with name as key
        foreach($custom_attributes['attribute'] as $customAttribute) {
            if(!empty($customAttribute['name'])) {
                $additionalAttributes[$customAttribute['name']] = array_merge($defaultQuestionAttributeValues,$customAttribute);
            }
        }
        return $additionalAttributes;
    }

    /**
     * Return the question Theme preview URL
     * @param $sType: type pof question
     * @return string : question theme preview URL
     */
    public static function getQuestionThemePreviewUrl($sType = null)
    {
        if ($sType == '*'){
            $preview_filename = 'EQUATION.png';
        } elseif ($sType == ':'){
            $preview_filename = 'COLON.png';
        } elseif ($sType == '|'){
            $preview_filename = 'PIPE.png';
        } elseif (!empty($sType)) {
            $preview_filename = $sType.'.png';
        } else {
            $preview_filename = '.png';
        }

        return Yii::app()->getConfig("imageurl").'/screenshots/'.$preview_filename;
    }

}
