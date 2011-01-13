<?php   

require_once 'PHPUnit/Extensions/Database/TestCase.php';
require_once 'PHPUnit/Extensions/Database/DataSet/CsvDataSet.php';
require_once 'PHPUnit/Extensions/Database/DataSet/ReplacementDataSet.php';

/**
* Integration tests that check for accuracty of headers and results 
* given various options for exporting.  The datasets contain information that
* will allow checking of:
* 1. Proper newline conversions in comments
* 2. Quotation escaping in comments
* 3. Filtering results based on completion state.
* 4. Conversion of Yes/No to other values.
*/
class ExportResultsTest extends PHPUnit_Extensions_Database_TestCase
{       
    protected function setUp()
    {     
        parent::setUp();
        
        //HTTP_HOST needs to be present to avoid warnings.
        $_SERVER['HTTP_HOST'] = 'localhost';  
        global $connect;
        global $clang;
         

        require_once "classes/core/startup.php";
        require "config-defaults.php"; 
        require "config.php";                                
        require_once "common.php"; 
        require_once "admin/exportresults_objects.php";
        
        $connect=ADONewConnection($databasetype);
        $database_exists = FALSE;
        switch ($databasetype)
        {
            case "postgres":
            case "mysqli":
            case "mysql": if ($databaseport!="default") {$dbhost="$databaselocation:$databaseport";}
            else {$dbhost=$databaselocation;}
            break;
            case "mssql_n":
            case "mssqlnative":
            case "mssql": if ($databaseport!="default") {$dbhost="$databaselocation,$databaseport";}
            else {$dbhost=$databaselocation;}
            break;
            case "odbc_mssql": $dbhost="Driver={SQL Server};Server=$databaselocation;Database=".$databasename;
            break;

            default: safe_die("Unknown database type");
        }

        // Now try connecting to the database
        if ($databasepersistent==true)
        {
            if (@$connect->PConnect($dbhost, $databaseuser, $databasepass, $databasename))
            {
                $database_exists = TRUE;
            }
            else {
                // If that doesnt work try connection without database-name
                $connect->database = '';
                if (!@$connect->PConnect($dbhost, $databaseuser, $databasepass))
                {
                    safe_die("Can't connect to LimeSurvey database. Reason: ".$connect->ErrorMsg());
                }
            }
        }
        else
        {
            if (@$connect->Connect($dbhost, $databaseuser, $databasepass, $databasename))
            {
                $database_exists = TRUE;
            }
            else {
                // If that doesnt work try connection without database-name
                $connect->database = '';
                if (!@$connect->Connect($dbhost, $databaseuser, $databasepass))
                {
                    safe_die("Can't connect to LimeSurvey database. Reason: ".$connect->ErrorMsg());
                }
            }
        }
              
        // AdoDB seems to be defaulting to ADODB_FETCH_NUM and we want to be sure that the right default mode is set
        $connect->SetFetchMode(ADODB_FETCH_ASSOC);       
    }
    
    protected function getConnection()
    {
        $pdo = new PDO('mysql:host=localhost;dbname=limesurvey_unit_test', 'ls_unit_test', 'iuxpo91');
        return $this->createDefaultDBConnection($pdo, 'limesurvey_unit_test');
    }
    
    protected function getDataSet()
    {
        $dataSetDir = 'test/admin/exportresults_objects_datasets/';
        $dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet(',', '"', '"');
        $dataSet->addTable('answers', $dataSetDir.'answers.csv');
        $dataSet->addTable('assessments', $dataSetDir.'assessments.csv');
        $dataSet->addTable('conditions', $dataSetDir.'conditions.csv');
        $dataSet->addTable('defaultvalues', $dataSetDir.'defaultvalues.csv');
        $dataSet->addTable('groups', $dataSetDir.'groups.csv');
        $dataSet->addTable('labels', $dataSetDir.'labels.csv');
        $dataSet->addTable('labelsets', $dataSetDir.'labelsets.csv');
        $dataSet->addTable('question_attributes', $dataSetDir.'question_attributes.csv');
        $dataSet->addTable('questions', $dataSetDir.'questions.csv');
        $dataSet->addTable('quota', $dataSetDir.'quota.csv');
        $dataSet->addTable('quota_languagesettings', $dataSetDir.'quota_languagesettings.csv');
        $dataSet->addTable('saved_control', $dataSetDir.'saved_control.csv');
        $dataSet->addTable('sessions', $dataSetDir.'sessions.csv');
        $dataSet->addTable('settings_global', $dataSetDir.'settings_global.csv');
        $dataSet->addTable('survey_59864', $dataSetDir.'survey_59864.csv');
        $dataSet->addTable('surveys', $dataSetDir.'surveys.csv');
        $dataSet->addTable('surveys_languagesettings', $dataSetDir.'surveys_languagesettings.csv');
        $dataSet->addTable('survey_permissions', $dataSetDir.'survey_permissions.csv');
        $dataSet->addTable('templates', $dataSetDir.'templates.csv');
        $dataSet->addTable('templates_rights', $dataSetDir.'templates_rights.csv');
        $dataSet->addTable('tokens_59864', $dataSetDir.'tokens_59864.csv');
        $dataSet->addTable('user_groups', $dataSetDir.'user_groups.csv');
        $dataSet->addTable('user_in_groups', $dataSetDir.'user_in_groups.csv');
        $dataSet->addTable('users', $dataSetDir.'users.csv');
        
        $replacements = array('[NULL]' => null);
        $replacementSet = new PHPUnit_Extensions_Database_DataSet_ReplacementDataSet($dataSet, $replacements);
        return $replacementSet;
    }     
    
    /**
    * The six tests following this comment are intended to test output for all combinations
    * of the $options->answerFormat and $options->headingFormat values.
    */
    public function testCsvWriterWithShortAnswersHeadCodesShowAllRecordsWithoutYesNoConversion()
    {
        $id = 59864;
        $surveyDao = new SurveyDao();
        $survey = $surveyDao->loadSurveyById($id);
        $surveyDao->loadSurveyResults($survey);  
        
        $writer = new CsvWriter();
        $options = new FormattingOptions();
        $options->responseMinRecord = 0;
        $options->answerFormat = 'short';
        $options->convertN = false;
        $options->nValue = 0;
        $options->convertY = false;
        $options->yValue = 1;
        $options->format = 'csv';
        $options->headerSpacesToUnderscores = false;
        $options->headingFormat = 'headcodes';
        $options->responseCompletionState = 'show';
        
        $columns = array();
        foreach ($survey->fieldMap as $field) 
        {
            $columns[] = $field['fieldname'];
        }

        $options->selectedColumns = $columns;
        
        $writer->write($survey, 'en', $options);
        $output = $writer->close();   
                                               
        $expected = '"id","Completed","Last page seen","Start language","1","2 [SQ001]","2 [SQ002]","3 [SQ001][Scale 1]","3 [SQ001][Scale 2]","3 [SQ002][Scale 1]","3 [SQ002][Scale 2]","4 [SQ001]","4 [SQ002]","5","6","7","8","8 [Other]","9","9 - comment","10 [SQ001]","10 [SQ002]","11 [SQ001]","11 - comment","11 [SQ002]","11 - comment","11 [SQ003]","11 - comment","11 [Other]","11 [Other] - comment","12","13 [Ranking 1]","13 [Ranking 2]","13 [Ranking 3]","13 [Ranking 4]","14","15","16 [SQ001][SQ001]","16 [SQ001][SQ002]","16 [SQ002][SQ001]","16 [SQ002][SQ002]","16 [SQ003][SQ001]","16 [SQ003][SQ002]","16 [SQ004][SQ001]","16 [SQ004][SQ002]"
"4","1980-01-01 00:00:00","2","en","1","1","2","A1","A1","A2","A2","A5","A1","","2010-11-08","M","","other","A1","Testing a comment with a newline character","1","2","Y","1","","","Y","3","Other","other","15","A2","A1","A4","A3","Y","short free text response","6","6","8","8","4","9","3","2"
"5","1980-01-01 00:00:00","2","en","4","5","4","A3","A3","A3","A2","A4","A3","","2010-12-17","F","A2","","A1","This is a comment!","27","15","Y","Comment on subquestion 1","","","Y","Comment on subquestion 3","","","315","A2","A1","A4","A3","N","Testing ""quotations"" in a free text field","3","7","8","1","4","2","8","8"
"6","","2","en","3","2","4","A2","A2","A3","A3","A1","A4","","2013-12-09","F","A2","","","","","","","","","","","","","","","","","","","","","","","","","","","",""';
        $this->assertEquals($expected, $output);
    }
    
    public function testCsvWriterWithShortAnswersAbbrevHeadingsShowAllRecordsWithoutYesNoConversion()
    {
        $id = 59864;
        $surveyDao = new SurveyDao();
        $survey = $surveyDao->loadSurveyById($id);
        $surveyDao->loadSurveyResults($survey);  
        
        $writer = new CsvWriter();
        $options = new FormattingOptions();
        $options->responseMinRecord = 0;
        $options->answerFormat = 'short';
        $options->convertN = false;
        $options->nValue = 0;
        $options->convertY = false;
        $options->yValue = 1;
        $options->format = 'csv';
        $options->headerSpacesToUnderscores = false;
        $options->headingFormat = 'abrev';
        $options->responseCompletionState = 'show';
        
        $columns = array();
        foreach ($survey->fieldMap as $field) 
        {
            $columns[] = $field['fieldname'];
        }
        
        $options->selectedColumns = $columns;
        
        $writer->write($survey, 'en', $options);
        $output = $writer->close();    
        
        $expected = '"id","Completed","Last page seen","Start language","5 Point Choice.. ","Array (5 point .. [SQ001]","Array (5 point .. [SQ002]","Array Dual Scal.. [SQ001]","Array Dual Scal.. [SQ001]","Array Dual Scal.. [SQ002]","Array Dual Scal.. [SQ002]","Array by column.. [SQ001]","Array by column.. [SQ002]","Boilerplate Que.. ","Date.. ","Gender.. ","List (radio) wi.. ","List (radio) wi.. [other]","List with comme.. ","List with comme.. [comment]","Multiple numeri.. [SQ001]","Multiple numeri.. [SQ002]","Multiple option.. [SQ001]","Multiple option.. [SQ001comment]","Multiple option.. [SQ002]","Multiple option.. [SQ002comment]","Multiple option.. [SQ003]","Multiple option.. [SQ003comment]","Multiple option.. [other]","Multiple option.. [othercomment]","Numerical Input.. ","Ranking.. [1]","Ranking.. [2]","Ranking.. [3]","Ranking.. [4]","Yes/No.. ","Short Free Text.. ","Array (Numbers).. [SQ001_SQ001]","Array (Numbers).. [SQ001_SQ002]","Array (Numbers).. [SQ002_SQ001]","Array (Numbers).. [SQ002_SQ002]","Array (Numbers).. [SQ003_SQ001]","Array (Numbers).. [SQ003_SQ002]","Array (Numbers).. [SQ004_SQ001]","Array (Numbers).. [SQ004_SQ002]"
"4","1980-01-01 00:00:00","2","en","1","1","2","A1","A1","A2","A2","A5","A1","","2010-11-08","M","","other","A1","Testing a comment with a newline character","1","2","Y","1","","","Y","3","Other","other","15","A2","A1","A4","A3","Y","short free text response","6","6","8","8","4","9","3","2"
"5","1980-01-01 00:00:00","2","en","4","5","4","A3","A3","A3","A2","A4","A3","","2010-12-17","F","A2","","A1","This is a comment!","27","15","Y","Comment on subquestion 1","","","Y","Comment on subquestion 3","","","315","A2","A1","A4","A3","N","Testing ""quotations"" in a free text field","3","7","8","1","4","2","8","8"
"6","","2","en","3","2","4","A2","A2","A3","A3","A1","A4","","2013-12-09","F","A2","","","","","","","","","","","","","","","","","","","","","","","","","","","",""';
        $this->assertEquals($expected, $output);   
    }
    
    public function testCsvWriterWithShortAnswersFullHeadingsShowAllRecordsWithoutYesNoConversion()
    {
        $id = 59864;
        $surveyDao = new SurveyDao();
        $survey = $surveyDao->loadSurveyById($id);
        $surveyDao->loadSurveyResults($survey);  
        
        $writer = new CsvWriter();
        $options = new FormattingOptions();
        $options->responseMinRecord = 0;
        $options->answerFormat = 'short';
        $options->convertN = false;
        $options->nValue = 0;
        $options->convertY = false;
        $options->yValue = 1;
        $options->format = 'csv';
        $options->headerSpacesToUnderscores = false;
        $options->headingFormat = 'full';
        $options->responseCompletionState = 'show';
        
        $columns = array();
        foreach ($survey->fieldMap as $field) 
        {
            $columns[] = $field['fieldname'];
        }
        
        $options->selectedColumns = $columns;
        
        $writer->write($survey, 'en', $options);
        $output = $writer->close();  
        
        $expected = '"id","Completed","Last page seen","Start language","5 Point Choice","Array (5 point choice) [Some example subquestion]","Array (5 point choice) [Another subquestion]","Array Dual Scale [Subquestion 1][Scale 1]","Array Dual Scale [Subquestion 1][Scale 2]","Array Dual Scale [Subquestion 2][Scale 1]","Array Dual Scale [Subquestion 2][Scale 2]","Array by column [subquestion 1]","Array by column [subquestion 2]","Boilerplate Question","Date","Gender","List (radio) with \'Other\'","List (radio) with \'Other\' [Other]","List with comment","List with comment - comment","Multiple numerical input [Subquestion 1]","Multiple numerical input [Subquestion 2]","Multiple options with comments and \'Other\' [Subquestion 1]","Multiple options with comments and \'Other\' - comment","Multiple options with comments and \'Other\' [Subquestion 2]","Multiple options with comments and \'Other\' - comment","Multiple options with comments and \'Other\' [Subquestion 3]","Multiple options with comments and \'Other\' - comment","Multiple options with comments and \'Other\' [Other]","Multiple options with comments and \'Other\' [Other] - comment","Numerical Input","Ranking [Ranking 1]","Ranking [Ranking 2]","Ranking [Ranking 3]","Ranking [Ranking 4]","Yes/No","Short Free Text","Array (Numbers) [Some example subquestion][X1]","Array (Numbers) [Some example subquestion][X2]","Array (Numbers) [New answer option][X1]","Array (Numbers) [New answer option][X2]","Array (Numbers) [New option 2][X1]","Array (Numbers) [New option 2][X2]","Array (Numbers) [New option 3][X1]","Array (Numbers) [New option 3][X2]"
"4","1980-01-01 00:00:00","2","en","1","1","2","A1","A1","A2","A2","A5","A1","","2010-11-08","M","","other","A1","Testing a comment with a newline character","1","2","Y","1","","","Y","3","Other","other","15","A2","A1","A4","A3","Y","short free text response","6","6","8","8","4","9","3","2"
"5","1980-01-01 00:00:00","2","en","4","5","4","A3","A3","A3","A2","A4","A3","","2010-12-17","F","A2","","A1","This is a comment!","27","15","Y","Comment on subquestion 1","","","Y","Comment on subquestion 3","","","315","A2","A1","A4","A3","N","Testing ""quotations"" in a free text field","3","7","8","1","4","2","8","8"
"6","","2","en","3","2","4","A2","A2","A3","A3","A1","A4","","2013-12-09","F","A2","","","","","","","","","","","","","","","","","","","","","","","","","","","",""';
        $this->assertEquals($expected, $output);   
    }
    
    public function testCsvWriterWithFullAnswersHeadCodesShowAllRecordsWithoutYesNoConversion()
    {
        global $surveyid;
        $id = 59864;
        $surveyid = $id;
        $surveyDao = new SurveyDao();
        $survey = $surveyDao->loadSurveyById($id);
        $surveyDao->loadSurveyResults($survey);  
        
        $writer = new CsvWriter();
        $options = new FormattingOptions();
        $options->responseMinRecord = 0;
        $options->answerFormat = 'long';
        $options->convertN = false;
        $options->nValue = 0;
        $options->convertY = false;
        $options->yValue = 1;
        $options->format = 'csv';
        $options->headerSpacesToUnderscores = false;
        $options->headingFormat = 'headcodes';
        $options->responseCompletionState = 'show';
        
        $columns = array();
        foreach ($survey->fieldMap as $field) 
        {
            $columns[] = $field['fieldname'];
        }

        $options->selectedColumns = $columns;
        
        $writer->write($survey, 'en', $options);
        $output = $writer->close();   
                                                
        $expected = '"id","Completed","Last page seen","Start language","1","2 [SQ001]","2 [SQ002]","3 [SQ001][Scale 1]","3 [SQ001][Scale 2]","3 [SQ002][Scale 1]","3 [SQ002][Scale 2]","4 [SQ001]","4 [SQ002]","5","6","7","8","8 [Other]","9","9 - comment","10 [SQ001]","10 [SQ002]","11 [SQ001]","11 - comment","11 [SQ002]","11 - comment","11 [SQ003]","11 - comment","11 [Other]","11 [Other] - comment","12","13 [Ranking 1]","13 [Ranking 2]","13 [Ranking 3]","13 [Ranking 4]","14","15","16 [SQ001][SQ001]","16 [SQ001][SQ002]","16 [SQ002][SQ001]","16 [SQ002][SQ002]","16 [SQ003][SQ001]","16 [SQ003][SQ002]","16 [SQ004][SQ001]","16 [SQ004][SQ002]"
"4","1980-01-01 00:00:00","2","en","1","1","2","option 1","option 2-1","option 2","option 2-2","option 5","option 1","","2010-11-08","Male","Other","other","Option 1","Testing a comment with a newline character","1","2","Yes","1","No","","Yes","3","Other","other","15","Option 2","Option 1","Option 4","Option 3","Yes","short free text response","6","6","8","8","4","9","3","2"
"5","1980-01-01 00:00:00","2","en","4","5","4","option 3","option 2-3","option 3","option 2-2","option 4","option 3","","2010-12-17","Female","Option 2","","Option 1","This is a comment!","27","15","Yes","Comment on subquestion 1","No","","Yes","Comment on subquestion 3","","","315","Option 2","Option 1","Option 4","Option 3","No","Testing ""quotations"" in a free text field","3","7","8","1","4","2","8","8"
"6","","2","en","3","2","4","option 2","option 2-2","option 3","option 2-3","option 1","option 4","","2013-12-09","Female","Option 2","","","","","","No","","No","","No","","","","","","","","","N/A","","","","","","","","",""';
        $this->assertEquals($expected, $output);
    }
    
    public function testCsvWriterWithFullAnswersAbbrevHeadingsShowAllRecordsWithoutYesNoConversion()
    {
        global $surveyid;
        $id = 59864;
        $surveyid = $id;
        $surveyDao = new SurveyDao();
        $survey = $surveyDao->loadSurveyById($id);
        $surveyDao->loadSurveyResults($survey);  
        
        $writer = new CsvWriter();
        $options = new FormattingOptions();
        $options->responseMinRecord = 0;
        $options->answerFormat = 'long';
        $options->convertN = false;
        $options->nValue = 0;
        $options->convertY = false;
        $options->yValue = 1;
        $options->format = 'csv';
        $options->headerSpacesToUnderscores = false;
        $options->headingFormat = 'abrev';
        $options->responseCompletionState = 'show';
        
        $columns = array();
        foreach ($survey->fieldMap as $field) 
        {
            $columns[] = $field['fieldname'];
        }

        $options->selectedColumns = $columns;
        
        $writer->write($survey, 'en', $options);
        $output = $writer->close();   
                                             
        $expected = '"id","Completed","Last page seen","Start language","5 Point Choice.. ","Array (5 point .. [SQ001]","Array (5 point .. [SQ002]","Array Dual Scal.. [SQ001]","Array Dual Scal.. [SQ001]","Array Dual Scal.. [SQ002]","Array Dual Scal.. [SQ002]","Array by column.. [SQ001]","Array by column.. [SQ002]","Boilerplate Que.. ","Date.. ","Gender.. ","List (radio) wi.. ","List (radio) wi.. [other]","List with comme.. ","List with comme.. [comment]","Multiple numeri.. [SQ001]","Multiple numeri.. [SQ002]","Multiple option.. [SQ001]","Multiple option.. [SQ001comment]","Multiple option.. [SQ002]","Multiple option.. [SQ002comment]","Multiple option.. [SQ003]","Multiple option.. [SQ003comment]","Multiple option.. [other]","Multiple option.. [othercomment]","Numerical Input.. ","Ranking.. [1]","Ranking.. [2]","Ranking.. [3]","Ranking.. [4]","Yes/No.. ","Short Free Text.. ","Array (Numbers).. [SQ001_SQ001]","Array (Numbers).. [SQ001_SQ002]","Array (Numbers).. [SQ002_SQ001]","Array (Numbers).. [SQ002_SQ002]","Array (Numbers).. [SQ003_SQ001]","Array (Numbers).. [SQ003_SQ002]","Array (Numbers).. [SQ004_SQ001]","Array (Numbers).. [SQ004_SQ002]"
"4","1980-01-01 00:00:00","2","en","1","1","2","option 1","option 2-1","option 2","option 2-2","option 5","option 1","","2010-11-08","Male","Other","other","Option 1","Testing a comment with a newline character","1","2","Yes","1","No","","Yes","3","Other","other","15","Option 2","Option 1","Option 4","Option 3","Yes","short free text response","6","6","8","8","4","9","3","2"
"5","1980-01-01 00:00:00","2","en","4","5","4","option 3","option 2-3","option 3","option 2-2","option 4","option 3","","2010-12-17","Female","Option 2","","Option 1","This is a comment!","27","15","Yes","Comment on subquestion 1","No","","Yes","Comment on subquestion 3","","","315","Option 2","Option 1","Option 4","Option 3","No","Testing ""quotations"" in a free text field","3","7","8","1","4","2","8","8"
"6","","2","en","3","2","4","option 2","option 2-2","option 3","option 2-3","option 1","option 4","","2013-12-09","Female","Option 2","","","","","","No","","No","","No","","","","","","","","","N/A","","","","","","","","",""';
        $this->assertEquals($expected, $output);
    } 
    
    public function testCsvWriterWithFullAnswersFullHeadingsShowAllRecordsWithoutYesNoConversion()
    {
        global $surveyid;
        $id = 59864;
        $surveyid = $id;
        $surveyDao = new SurveyDao();
        $survey = $surveyDao->loadSurveyById($id);
        $surveyDao->loadSurveyResults($survey);  
        
        $writer = new CsvWriter();
        $options = new FormattingOptions();
        $options->responseMinRecord = 0;
        $options->answerFormat = 'long';
        $options->convertN = false;
        $options->nValue = 0;
        $options->convertY = false;
        $options->yValue = 1;
        $options->format = 'csv';
        $options->headerSpacesToUnderscores = false;
        $options->headingFormat = 'full';
        $options->responseCompletionState = 'show';
        
        $columns = array();
        foreach ($survey->fieldMap as $field) 
        {
            $columns[] = $field['fieldname'];
        }

        $options->selectedColumns = $columns;
        
        $writer->write($survey, 'en', $options);
        $output = $writer->close();   
                                             
        $expected = '"id","Completed","Last page seen","Start language","5 Point Choice","Array (5 point choice) [Some example subquestion]","Array (5 point choice) [Another subquestion]","Array Dual Scale [Subquestion 1][Scale 1]","Array Dual Scale [Subquestion 1][Scale 2]","Array Dual Scale [Subquestion 2][Scale 1]","Array Dual Scale [Subquestion 2][Scale 2]","Array by column [subquestion 1]","Array by column [subquestion 2]","Boilerplate Question","Date","Gender","List (radio) with \'Other\'","List (radio) with \'Other\' [Other]","List with comment","List with comment - comment","Multiple numerical input [Subquestion 1]","Multiple numerical input [Subquestion 2]","Multiple options with comments and \'Other\' [Subquestion 1]","Multiple options with comments and \'Other\' - comment","Multiple options with comments and \'Other\' [Subquestion 2]","Multiple options with comments and \'Other\' - comment","Multiple options with comments and \'Other\' [Subquestion 3]","Multiple options with comments and \'Other\' - comment","Multiple options with comments and \'Other\' [Other]","Multiple options with comments and \'Other\' [Other] - comment","Numerical Input","Ranking [Ranking 1]","Ranking [Ranking 2]","Ranking [Ranking 3]","Ranking [Ranking 4]","Yes/No","Short Free Text","Array (Numbers) [Some example subquestion][X1]","Array (Numbers) [Some example subquestion][X2]","Array (Numbers) [New answer option][X1]","Array (Numbers) [New answer option][X2]","Array (Numbers) [New option 2][X1]","Array (Numbers) [New option 2][X2]","Array (Numbers) [New option 3][X1]","Array (Numbers) [New option 3][X2]"
"4","1980-01-01 00:00:00","2","en","1","1","2","option 1","option 2-1","option 2","option 2-2","option 5","option 1","","2010-11-08","Male","Other","other","Option 1","Testing a comment with a newline character","1","2","Yes","1","No","","Yes","3","Other","other","15","Option 2","Option 1","Option 4","Option 3","Yes","short free text response","6","6","8","8","4","9","3","2"
"5","1980-01-01 00:00:00","2","en","4","5","4","option 3","option 2-3","option 3","option 2-2","option 4","option 3","","2010-12-17","Female","Option 2","","Option 1","This is a comment!","27","15","Yes","Comment on subquestion 1","No","","Yes","Comment on subquestion 3","","","315","Option 2","Option 1","Option 4","Option 3","No","Testing ""quotations"" in a free text field","3","7","8","1","4","2","8","8"
"6","","2","en","3","2","4","option 2","option 2-2","option 3","option 2-3","option 1","option 4","","2013-12-09","Female","Option 2","","","","","","No","","No","","No","","","","","","","","","N/A","","","","","","","","",""';
        $this->assertEquals($expected, $output);
    }
    
    /**
    * This is the ONLY current test for the response filtering for complete records.
    */
    public function testCsvWriterWithFullAnswersFullHeadingsShowCompleteRecordsWithoutYesNoConversion()
    {
        global $surveyid;
        $id = 59864;
        $surveyid = $id;
        $surveyDao = new SurveyDao();
        $survey = $surveyDao->loadSurveyById($id);
        $surveyDao->loadSurveyResults($survey);  
        
        $writer = new CsvWriter();
        $options = new FormattingOptions();
        $options->responseMinRecord = 0;
        $options->answerFormat = 'long';
        $options->convertN = false;
        $options->nValue = 0;
        $options->convertY = false;
        $options->yValue = 1;
        $options->format = 'csv';
        $options->headerSpacesToUnderscores = false;
        $options->headingFormat = 'full';
        $options->responseCompletionState = 'filter';
        
        $columns = array();
        foreach ($survey->fieldMap as $field) 
        {
            $columns[] = $field['fieldname'];
        }

        $options->selectedColumns = $columns;
        
        $writer->write($survey, 'en', $options);
        $output = $writer->close();   
                                             
        $expected = '"id","Completed","Last page seen","Start language","5 Point Choice","Array (5 point choice) [Some example subquestion]","Array (5 point choice) [Another subquestion]","Array Dual Scale [Subquestion 1][Scale 1]","Array Dual Scale [Subquestion 1][Scale 2]","Array Dual Scale [Subquestion 2][Scale 1]","Array Dual Scale [Subquestion 2][Scale 2]","Array by column [subquestion 1]","Array by column [subquestion 2]","Boilerplate Question","Date","Gender","List (radio) with \'Other\'","List (radio) with \'Other\' [Other]","List with comment","List with comment - comment","Multiple numerical input [Subquestion 1]","Multiple numerical input [Subquestion 2]","Multiple options with comments and \'Other\' [Subquestion 1]","Multiple options with comments and \'Other\' - comment","Multiple options with comments and \'Other\' [Subquestion 2]","Multiple options with comments and \'Other\' - comment","Multiple options with comments and \'Other\' [Subquestion 3]","Multiple options with comments and \'Other\' - comment","Multiple options with comments and \'Other\' [Other]","Multiple options with comments and \'Other\' [Other] - comment","Numerical Input","Ranking [Ranking 1]","Ranking [Ranking 2]","Ranking [Ranking 3]","Ranking [Ranking 4]","Yes/No","Short Free Text","Array (Numbers) [Some example subquestion][X1]","Array (Numbers) [Some example subquestion][X2]","Array (Numbers) [New answer option][X1]","Array (Numbers) [New answer option][X2]","Array (Numbers) [New option 2][X1]","Array (Numbers) [New option 2][X2]","Array (Numbers) [New option 3][X1]","Array (Numbers) [New option 3][X2]"
"4","1980-01-01 00:00:00","2","en","1","1","2","option 1","option 2-1","option 2","option 2-2","option 5","option 1","","2010-11-08","Male","Other","other","Option 1","Testing a comment with a newline character","1","2","Yes","1","No","","Yes","3","Other","other","15","Option 2","Option 1","Option 4","Option 3","Yes","short free text response","6","6","8","8","4","9","3","2"
"5","1980-01-01 00:00:00","2","en","4","5","4","option 3","option 2-3","option 3","option 2-2","option 4","option 3","","2010-12-17","Female","Option 2","","Option 1","This is a comment!","27","15","Yes","Comment on subquestion 1","No","","Yes","Comment on subquestion 3","","","315","Option 2","Option 1","Option 4","Option 3","No","Testing ""quotations"" in a free text field","3","7","8","1","4","2","8","8"';
        $this->assertEquals($expected, $output);
    }
    
    /**
    * This is the only test for incomplete record response filtering.
    */
    public function testCsvWriterWithFullAnswersFullHeadingsShowIncompleteRecordsWithoutYesNoConversion()
    {
        global $surveyid;
        $id = 59864;
        $surveyid = $id;
        $surveyDao = new SurveyDao();
        $survey = $surveyDao->loadSurveyById($id);
        $surveyDao->loadSurveyResults($survey);  
        
        $writer = new CsvWriter();
        $options = new FormattingOptions();
        $options->responseMinRecord = 0;
        $options->answerFormat = 'long';
        $options->convertN = false;
        $options->nValue = 0;
        $options->convertY = false;
        $options->yValue = 1;
        $options->format = 'csv';
        $options->headerSpacesToUnderscores = false;
        $options->headingFormat = 'full';
        $options->responseCompletionState = 'incomplete';
        
        $columns = array();
        foreach ($survey->fieldMap as $field) 
        {
            $columns[] = $field['fieldname'];
        }

        $options->selectedColumns = $columns;
        
        $writer->write($survey, 'en', $options);
        $output = $writer->close();   
                                             
        $expected = '"id","Completed","Last page seen","Start language","5 Point Choice","Array (5 point choice) [Some example subquestion]","Array (5 point choice) [Another subquestion]","Array Dual Scale [Subquestion 1][Scale 1]","Array Dual Scale [Subquestion 1][Scale 2]","Array Dual Scale [Subquestion 2][Scale 1]","Array Dual Scale [Subquestion 2][Scale 2]","Array by column [subquestion 1]","Array by column [subquestion 2]","Boilerplate Question","Date","Gender","List (radio) with \'Other\'","List (radio) with \'Other\' [Other]","List with comment","List with comment - comment","Multiple numerical input [Subquestion 1]","Multiple numerical input [Subquestion 2]","Multiple options with comments and \'Other\' [Subquestion 1]","Multiple options with comments and \'Other\' - comment","Multiple options with comments and \'Other\' [Subquestion 2]","Multiple options with comments and \'Other\' - comment","Multiple options with comments and \'Other\' [Subquestion 3]","Multiple options with comments and \'Other\' - comment","Multiple options with comments and \'Other\' [Other]","Multiple options with comments and \'Other\' [Other] - comment","Numerical Input","Ranking [Ranking 1]","Ranking [Ranking 2]","Ranking [Ranking 3]","Ranking [Ranking 4]","Yes/No","Short Free Text","Array (Numbers) [Some example subquestion][X1]","Array (Numbers) [Some example subquestion][X2]","Array (Numbers) [New answer option][X1]","Array (Numbers) [New answer option][X2]","Array (Numbers) [New option 2][X1]","Array (Numbers) [New option 2][X2]","Array (Numbers) [New option 3][X1]","Array (Numbers) [New option 3][X2]"
"6","","2","en","3","2","4","option 2","option 2-2","option 3","option 2-3","option 1","option 4","","2013-12-09","Female","Option 2","","","","","","No","","No","","No","","","","","","","","","N/A","","","","","","","","",""';
        $this->assertEquals($expected, $output);  
    }
    
    /**
    * The only test for Yes/No conversion to another value.
    */
    public function testCsvWriterWithShortAnswersFullHeadingsShowAllRecordsWithYesNoConversion()
    {
        $id = 59864;
        $surveyDao = new SurveyDao();
        $survey = $surveyDao->loadSurveyById($id);
        $surveyDao->loadSurveyResults($survey);  
        
        $writer = new CsvWriter();
        $options = new FormattingOptions();
        $options->responseMinRecord = 0;
        $options->answerFormat = 'short';
        $options->convertN = true;
        $options->nValue = 0;
        $options->convertY = true;
        $options->yValue = 1;
        $options->format = 'csv';
        $options->headerSpacesToUnderscores = false;
        $options->headingFormat = 'full';
        $options->responseCompletionState = 'show';
        
        $columns = array();
        foreach ($survey->fieldMap as $field) 
        {
            $columns[] = $field['fieldname'];
        }
        
        $options->selectedColumns = $columns;
        
        $writer->write($survey, 'en', $options);
        $output = $writer->close();  
        
        $expected = '"id","Completed","Last page seen","Start language","5 Point Choice","Array (5 point choice) [Some example subquestion]","Array (5 point choice) [Another subquestion]","Array Dual Scale [Subquestion 1][Scale 1]","Array Dual Scale [Subquestion 1][Scale 2]","Array Dual Scale [Subquestion 2][Scale 1]","Array Dual Scale [Subquestion 2][Scale 2]","Array by column [subquestion 1]","Array by column [subquestion 2]","Boilerplate Question","Date","Gender","List (radio) with \'Other\'","List (radio) with \'Other\' [Other]","List with comment","List with comment - comment","Multiple numerical input [Subquestion 1]","Multiple numerical input [Subquestion 2]","Multiple options with comments and \'Other\' [Subquestion 1]","Multiple options with comments and \'Other\' - comment","Multiple options with comments and \'Other\' [Subquestion 2]","Multiple options with comments and \'Other\' - comment","Multiple options with comments and \'Other\' [Subquestion 3]","Multiple options with comments and \'Other\' - comment","Multiple options with comments and \'Other\' [Other]","Multiple options with comments and \'Other\' [Other] - comment","Numerical Input","Ranking [Ranking 1]","Ranking [Ranking 2]","Ranking [Ranking 3]","Ranking [Ranking 4]","Yes/No","Short Free Text","Array (Numbers) [Some example subquestion][X1]","Array (Numbers) [Some example subquestion][X2]","Array (Numbers) [New answer option][X1]","Array (Numbers) [New answer option][X2]","Array (Numbers) [New option 2][X1]","Array (Numbers) [New option 2][X2]","Array (Numbers) [New option 3][X1]","Array (Numbers) [New option 3][X2]"
"4","1980-01-01 00:00:00","2","en","1","1","2","A1","A1","A2","A2","A5","A1","","2010-11-08","M","","other","A1","Testing a comment with a newline character","1","2","1","1","","","1","3","Other","other","15","A2","A1","A4","A3","1","short free text response","6","6","8","8","4","9","3","2"
"5","1980-01-01 00:00:00","2","en","4","5","4","A3","A3","A3","A2","A4","A3","","2010-12-17","F","A2","","A1","This is a comment!","27","15","1","Comment on subquestion 1","","","1","Comment on subquestion 3","","","315","A2","A1","A4","A3","0","Testing ""quotations"" in a free text field","3","7","8","1","4","2","8","8"
"6","","2","en","3","2","4","A2","A2","A3","A3","A1","A4","","2013-12-09","F","A2","","","","","","","","","","","","","","","","","","","","","","","","","","","",""';
        $this->assertEquals($expected, $output);   
    }
    
    public function testDocWriterWithShortAnswersFullHeadingsShowAllRecordsWithoutYesNoConversion()
    {
        $id = 59864;
        $surveyDao = new SurveyDao();
        $survey = $surveyDao->loadSurveyById($id);
        $surveyDao->loadSurveyResults($survey);  
        
        $writer = new DocWriter();
        $options = new FormattingOptions();
        $options->responseMinRecord = 0;
        $options->answerFormat = 'short';
        $options->convertN = false;
        $options->nValue = 0;
        $options->convertY = false;
        $options->yValue = 1;
        $options->format = 'csv';
        $options->headerSpacesToUnderscores = false;
        $options->headingFormat = 'full';
        $options->responseCompletionState = 'show';
        
        $columns = array();
        foreach ($survey->fieldMap as $field) 
        {
            $columns[] = $field['fieldname'];
        }
        
        $options->selectedColumns = $columns;
        
        $writer->write($survey, 'en', $options);
        $output = $writer->close();  
        
        $expected = "4\t1980-01-01 00:00:00\t2\ten\t1\t1\t2\tA1\tA1\tA2\tA2\tA5\tA1\t\t2010-11-08\tM\t\tother\tA1\tTesting a comment with a newline character\t1\t2\tY\t1\t\t\tY\t3\tOther\tother\t15\tA2\tA1\tA4\tA3\tY\tshort free text response\t6\t6\t8\t8\t4\t9\t3\t2
5\t1980-01-01 00:00:00\t2\ten\t4\t5\t4\tA3\tA3\tA3\tA2\tA4\tA3\t\t2010-12-17\tF\tA2\t\tA1\tThis is a comment!\t27\t15\tY\tComment on subquestion 1\t\t\tY\tComment on subquestion 3\t\t\t315\tA2\tA1\tA4\tA3\tN\tTesting \"quotations\" in a free text field\t3\t7\t8\t1\t4\t2\t8\t8
6\t\t2\ten\t3\t2\t4\tA2\tA2\tA3\tA3\tA1\tA4\t\t2013-12-09\tF\tA2\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t";
        $this->assertEquals($expected, $output);   
    }
    
    public function testDocWriterWithLongAnswersFullHeadingsShowAllRecordsWithoutYesNoConversion()
    {
        //TODO Need to actually assert equality with expected results on this one.
        $id = 59864;
        $surveyDao = new SurveyDao();
        $survey = $surveyDao->loadSurveyById($id);
        $surveyDao->loadSurveyResults($survey);  
        
        $writer = new DocWriter();
        $options = new FormattingOptions();
        $options->responseMinRecord = 0;
        $options->answerFormat = 'long';
        $options->convertN = false;
        $options->nValue = 0;
        $options->convertY = false;
        $options->yValue = 1;
        $options->format = 'doc';
        $options->headerSpacesToUnderscores = false;
        $options->headingFormat = 'full';
        $options->responseCompletionState = 'show';
        
        $columns = array();
        foreach ($survey->fieldMap as $field) 
        {
            $columns[] = $field['fieldname'];
        }
        
        $options->selectedColumns = $columns;
        
        $writer->write($survey, 'en', $options);
        $output = $writer->close();  
        
        //echo $output;   
    }
    
    /*public function testExcelWriterWithLongAnswersFullHeadingsShowAllRecordsWithoutYesNoConversion()
    {
        //TODO Need to actually assert equality with expected results on this one.
        $id = 59864;
        $surveyDao = new SurveyDao();
        $survey = $surveyDao->loadSurveyById($id);
        $surveyDao->loadSurveyResults($survey);  
        
        $writer = new ExcelWriter('c:\users\davidwolff\desktop\test.xls');
        $options = new FormattingOptions();
        $options->responseMinRecord = 0;
        $options->answerFormat = 'long';
        $options->convertN = false;
        $options->nValue = 0;
        $options->convertY = false;
        $options->yValue = 1;
        $options->format = 'xls';
        $options->headerSpacesToUnderscores = false;
        $options->headingFormat = 'full';
        $options->responseCompletionState = 'show';
        
        $columns = array();
        foreach ($survey->fieldMap as $field) 
        {
            $columns[] = $field['fieldname'];
        }
        
        $options->selectedColumns = $columns;
        
        $writer->write($survey, 'en', $options);
        $output = $writer->close();  
    }*/
}

?>
