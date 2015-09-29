<?php

$dir = realpath(dirname(__FILE__) . "/..");


$dir = new RecursiveDirectoryIterator($dir);


iterateList($dir);

function ignore($entry)
{
  // Check if directory is ignored.
  if (substr($entry, -9, 9) == 'libraries') return true;
}

function iterateList(Iterator $i)
{
    foreach ($i as $entry)
    {

        if ($i->hasChildren() && !ignore($entry))
        {
            iterateList($i->getChildren());
        }
        else
        {
            if (substr($entry, -4, 4) == '.php')
            {
                checkFile($entry);
            }
        }
    }
}

// Get all static calls in file.
function checkFile($filename)
{
    if ($filename == __FILE__)
    {
        return;
    }
    $file = file($filename, FILE_IGNORE_NEW_LINES);
    $file = array_filter($file, "checkStatic");
    
    if (!empty($file))
    {
        pr($filename);
        print_r($file);
    } 
}  

function checkStatic($line)
{
    $validStatics = array(
        'Yii::',
        'parent::',
        'LimeExpressionManager::',
'ls\models\Answer::',
'ls\models\Question::',
'ls\models\Survey::',
'ls\models\QuestionGroup::',
'self::',
'PDO::',
'Participants::',
'ls\models\SurveyLink::',
'ls\models\ParticipantAttribute::',
'Tokens::',
'ls\models\UserGroup::',
'Condition::',
'Survey_Common_Action::',
'ls\models\Quota::',
'SurveyURLParameter::',
'Survey_languagesettings::',
'Permission::',
'ls\models\SavedControl::',
'ls\models\QuotaMember::',
'ls\models\QuotaLanguageSetting::',
'ls\models\ParticipantAttributeName::',
'ls\models\User::',
'ls\models\SurveyLanguageSetting::',
'ls\models\QuestionAttribute::',
'ls\models\Assessment::',
'CDbConnection::',
'ParticipantShare::',
'\'{INSERTANS::',
'ls\models\DefaultValue::',
'CHtml::',
'ExpressionManager::',
'\'::\'',
'ls\models\LabelSet::',
'ls\models\SurveyDynamic::',
'PEAR::',
'ls\models\SettingGlobal::',
'Zend_Http_Client::',
'Zend_XmlRpc_Value::',
'Zend_XmlRpc_Server_Fault::',
'Zend_XmlRpc_Value::',
'Zend_Server_Cache::',
'Zend_XmlRpc_Server_Cache::',
'Label::',
'Assessments::',
'XMLReader::',
'LEM::',
'ls\models\Question::',
'DateTime::',
'Installer::',
'ls\models\Session::',
'dataentry::',
'Assessments::',
'Zend_Server_Reflection::',
'Participants::',
'jsonRPCServer::',
'FailedLoginAttempt::',
'survey::',
'tokens::',
'questiongroup::',
'printanswers::',
'imagick::',
':: ',
'Assessments::',
'InstallerConfigForm::',
'Database::',
'UserInGroups::',
'Usergroups::',
'ls\models\SurveyTimingDynamic::',
'::regClass',
'surveypermission::',
'ls\models\Template::',
'templates::',
'register::',
'::first',
'::before',
'::after',
'::reg',
'text::',
'httpCache::'
    );
    $replacements = array_pad(array(), count($validStatics), '');
    $line = str_replace($validStatics, $replacements, $line);
   
    return strpos($line, '::') !== false;
    
}
function pr($msg)
{
    echo $msg . "\n";
}

