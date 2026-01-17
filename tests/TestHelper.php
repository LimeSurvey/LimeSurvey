<?php

namespace ls\tests;

use Survey;
use Yii;
use Exception;
use Facebook\WebDriver\Exception\WebDriverException;
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\Firefox\FirefoxDriver;
use Facebook\WebDriver\Firefox\FirefoxProfile;
use Facebook\WebDriver\Firefox\FirefoxPreferences;
use Facebook\WebDriver\Exception\WebDriverCurlException;
use Facebook\WebDriver\Exception\NoSuchDriverException;
use SurveyActivator;

class TestHelper extends TestCase
{
    /**
     * Import all helpers etc.
     * @return void
     */
    public function importAll()
    {
        Yii::import('application.helpers.common_helper', true);
        Yii::import('application.helpers.replacements_helper', true);
        Yii::import('application.helpers.surveytranslator_helper', true);
        Yii::import('application.helpers.admin.import_helper', true);
        Yii::import('application.helpers.expressions.em_manager_helper', true);
        Yii::import('application.helpers.expressions.em_manager_helper', true);
        Yii::import('application.helpers.qanda_helper', true);
        Yii::import('application.helpers.update.updatedb_helper', true);
        Yii::import('application.helpers.update.update_helper', true);
        Yii::import('application.helpers.SurveyRuntimeHelper', true);
        Yii::app()->loadHelper('admin.activate');
    }

    /**
     * Reset existing cache (file by default)
     */
    public function resetCache()
    {
        if (method_exists(Yii::app()->cache, 'flush')) {
            Yii::app()->cache->flush();
        }
        if (method_exists(Yii::app()->cache, 'gc')) {
            Yii::app()->cache->gc();
        }
    }

    /**
     * @param string $title
     * @param int $surveyId
     * @return array
     */
    public function getSgqa($title, $surveyId)
    {
        $question = \Question::model()->find(
            'title = :title AND sid = :sid',
            [
                'title' => $title,
                'sid'   => $surveyId
            ]
        );

        $this->assertNotEmpty($question);

        $group = \QuestionGroup::model()->find(
            'gid = :gid',
            [
                'gid' => $question->gid
            ]
        );

        $this->assertNotEmpty($group);

        $sgqa = sprintf(
            'Q%s',
            $question->qid
        );

        return [$question, $group, $sgqa];
    }

    /**
     * Get survey options for imported survey.
     * @param int $surveyId
     * @return array
     */
    public function getSurveyOptions($surveyId)
    {
        $force      = true;
        $language   = '';
        $thissurvey = \getSurveyInfo($surveyId, $language, $force);

        $radix = \getRadixPointData($thissurvey['surveyls_numberformat']);
        $radix = $radix['separator'];
        $LEMdebugLevel = 0;
        $surveyOptions = array(
            'active' => ($thissurvey['active'] == 'Y'),
            'allowsave' => ($thissurvey['allowsave'] == 'Y'),
            'anonymized' => ($thissurvey['anonymized'] != 'N'),
            'assessments' => ($thissurvey['assessments'] == 'Y'),
            'datestamp' => ($thissurvey['datestamp'] == 'Y'),
            'deletenonvalues' => Yii::app()->getConfig('deletenonvalues'),
            'hyperlinkSyntaxHighlighting' => (($LEMdebugLevel & LEM_DEBUG_VALIDATION_SUMMARY) == LEM_DEBUG_VALIDATION_SUMMARY),
            'ipaddr' => ($thissurvey['ipaddr'] == 'Y'),
            'radix' => $radix,
            // FIXME !! $LEMsessid is not defined
            'refurl' => (($thissurvey['refurl'] == "Y" && isset($_SESSION[$LEMsessid]['refurl'])) ? $_SESSION[$LEMsessid]['refurl'] : null),
            'savetimings' => ($thissurvey['savetimings'] == "Y"),
            'surveyls_dateformat' => (isset($thissurvey['surveyls_dateformat']) ? $thissurvey['surveyls_dateformat'] : 1),
            'startlanguage' => (isset(App()->language) ? App()->language : $thissurvey['language']),
            'target' => Yii::app()->getConfig('uploaddir') . DIRECTORY_SEPARATOR . 'surveys' . DIRECTORY_SEPARATOR . $thissurvey['sid'] . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR,
            'tempdir' => Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR,
            'timeadjust' => (isset($timeadjust) ? $timeadjust : 0),
            'token' => (isset($clienttoken) ? $clienttoken : null),
        );
        return $surveyOptions;
    }

    /**
     * @param int $surveyId
     * @return void
     */
    public function activateSurvey($surveyId)
    {
        $survey = \Survey::model()->findByPk($surveyId);
        if (empty($survey)) {
            throw new Exception('Found no survey with id ' . $surveyId);
        }
        $survey->anonymized = '';
        $survey->datestamp = 'Y';
        $survey->ipaddr = '';
        $survey->refurl = '';
        $survey->savetimings = '';
        $survey->save();
        // Make sure the saved values will be picked up
        Survey::model()->resetCache();

        $surveyActivator = new SurveyActivator($survey);
        $result = $surveyActivator->activate();
        // For Travis debugging.
        if (isset($result['error'])) {
            var_dump($result);
        }
        \SurveyDynamic::sid($surveyId);
        \SurveyDynamic::model()->refreshMetaData();

        $db = Yii::app()->getDb();
        $db->schema->getTables();
        $db->schema->refresh();
        $db->active = false;
        $db->active = true;

        $this->assertEquals(['status' => 'OK', 'pluginFeedback' => null, 'isAllowRegister' => false], $result, 'Activate survey is OK');
    }

    /**
     * @param int $surveyId
     * @return void
     */
    public function deactivateSurvey($surveyId)
    {
        $date     = date('YmdHis');
        $oldSurveyTableName = Yii::app()->db->tablePrefix . "responses_{$surveyId}";
        $newSurveyTableName = Yii::app()->db->tablePrefix . "old_responses_{$surveyId}_{$date}";
        Yii::app()->db->createCommand()->renameTable($oldSurveyTableName, $newSurveyTableName);
        $survey = \Survey::model()->findByPk($surveyId);
        $survey->active = 'N';
        $result = $survey->save();
        $this->assertTrue($result, 'Survey deactivated');
    }

    /**
     * Overwrite the db component with a new
     * configuration and database.
     * Before you run this, you might want to save
     * the old db config in a variable, so you can
     * reconnect to it after you're done with the new
     * database.
     *   $config = require(\Yii::app()->getBasePath() . '/config/config.php');
     *
     * @param string $databaseName
     * @return boolean | \CDbConnection
     */
    public function connectToNewDatabase($databaseName)
    {
        $db = Yii::app()->getDb();

        $config = require(Yii::app()->getBasePath() . '/config/config.php');

        // Check that we're using MySQL.
        $conStr = Yii::app()->db->connectionString;
        $isMysql = substr($conStr, 0, 5) === 'mysql';
        if (!$isMysql) {
            $this->markTestSkipped('Only works on MySQL');
            return false;
        }
        $this->assertTrue($isMysql, 'This test only works on MySQL');

        // Get database name.
        preg_match("/dbname=([^;]*)/", $config['components']['db']['connectionString'], $matches);
        $this->assertEquals(2, count($matches));
        $oldDatabase = $matches[1];

        try {
            $db->createCommand('DROP DATABASE ' . $databaseName)->execute();
        } catch (\CDbException $ex) {
            $msg = $ex->getMessage();
            // Only this error is OK.
            self::assertTrue(strpos($msg, 'database doesn\'t exist') !== false, 'Could drop database');
        }

        try {
            $result = $db->createCommand(
                sprintf(
                    'CREATE DATABASE %s DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
                    $databaseName
                )
            )->execute();
            $this->assertEquals(1, $result, 'Could create database');
        } catch (\CDbException $ex) {
            $msg = $ex->getMessage();
            // This error is OK.
            $this->assertTrue(strpos($msg, 'database exists') !== false, 'Could create database');
        }

        // Connect to new database.
        $db->setActive(false);
        $newConfig = $config;
        $newConfig['components']['db']['connectionString'] = str_replace(
            'dbname=' . $oldDatabase,
            'dbname=' . $databaseName,
            $config['components']['db']['connectionString']
        );
        Yii::app()->setComponent('db', $newConfig['components']['db'], false);
        $db->setActive(true);
        Yii::app()->db->schema->getTables();
        Yii::app()->db->schema->refresh();
        return Yii::app()->getDb();
    }

    /**
     * @return void
     */
    public function connectToOriginalDatabase()
    {
        Yii::app()->db->setActive(false);
        $config = require(Yii::app()->getBasePath() . '/config/config.php');
        Yii::app()->setComponent('db', $config['components']['db'], false);
        Yii::app()->db->setActive(true);
        Yii::app()->db->schema->getTables();
        Yii::app()->db->schema->refresh();
    }

    /**
     * @param int $version Version to update FROM
     * @return \CDbConnection
     */
    public function updateDbFromVersion($version, $connection = null)
    {

        if (is_null($connection)) {
            $connection = $this->connectToNewDatabase('__test_update_helper_' . $version);
            $this->assertNotEmpty($connection, 'Could connect to new database');
        }

        // Check SQL file.
        $file = __DIR__ . '/data/sql/create-mysql.' . $version . '.sql';
        $this->assertFileExists($file, 'SQL file exists: ' . $file);

        // Run SQL install file.
        $result = self::executeSQLFile($file, $connection);
        $this->assertEquals([], $result, 'No error messages from executeSQLFile' . print_r($result, true));

        // Run upgrade.
        $result = \db_upgrade_all($version);

        // Check error messages.
        $flashes = Yii::app()->user->getFlashes();
        $this->assertEmpty($flashes, 'No flash error messages: ' . json_encode($flashes));
        $this->assertTrue($result, 'Upgrade successful');

        $versionConfig = require(Yii::app()->getBasePath() . '/config/version.php');
        $currentDbVersion = $connection
            ->createCommand()
            ->select('stg_value')
            ->from('{{settings_global}}')
            ->where("stg_name=:stg_name", ['stg_name' => 'DBVersion'])
            ->queryScalar();
        $this->assertEquals($versionConfig['dbversionnumber'], $currentDbVersion, 'Version in db is same as updated to');

        return $connection;
    }

    /**
     * Make sure Selenium can preview surveys without
     * being logged in.
     * @return void
     */
    public function enablePreview()
    {
        // Make sure we can preview without being logged in.
        $setting = \SettingGlobal::model()->findByPk('surveyPreview_require_Auth');

        // Possibly this setting does not exist yet.
        if (empty($setting)) {
            $setting = new \SettingGlobal();
            $setting->stg_name = 'surveyPreview_require_Auth';
            $setting->stg_value = 0;
            $setting->save();
        } else {
            $setting->stg_value = 0;
            $setting->save();
        }
    }

    /**
     * Drop database $databaseName.
     * Use in teardown methods.
     * @param string $databaseName
     * @return void
     */
    public function teardownDatabase($databaseName, $connection = null)
    {
        if (is_null($connection)) {
            $connection = Yii::app()->getDb();
        }

        $conStr = Yii::app()->db->connectionString;
        $isMysql = substr($conStr, 0, 5) === 'mysql';
        if (!$isMysql) {
            return;
        }

        try {
            $connection->createCommand('DROP DATABASE ' . $databaseName)->execute();
            $this->assertTrue(true);
        } catch (\CDbException $ex) {
            $msg = $ex->getMessage();
            // Only this error is OK.
            self::assertTrue(
                // MySQL
                strpos($msg, 'database doesn\'t exist') !== false ||
                // Postgres
                strpos($msg, "database \"$databaseName\" does not exist") !== false,
                'Unexpected exception: ' . $ex->getMessage()
            );
        }
    }

    /**
     * Use webdriver to put a screenshot in screenshot folder.
     * @param WebDriver $webDriver
     * @param string $name
     * @return void
     */
    public function takeScreenshot($webDriver, $name)
    {
        // Strip away namespace.
        $nameParts = explode('\\', $name);
        $name = $nameParts[count($nameParts) - 1];

        $tempFolder = Yii::app()->getBasePath() . '/../tests/tmp';
        $folder     = $tempFolder . '/screenshots/';
        $screenshot = $webDriver->takeScreenshot();
        $filename   = $folder . $name . '.png';
        $result     = file_put_contents($filename, $screenshot);
        $this->assertTrue($result > 0, 'Could not write screenshot to file ' . $filename);
    }

    /**
     * javaTrace() - provide a Java style exception trace
     *
     * Copied from here: http://php.net/manual/en/exception.gettraceasstring.php
     *
     * @param $exception
     * @param $seen      - array passed to recursive calls to accumulate trace lines already seen
     *                     leave as NULL when calling this function
     * @return string
     */
    public function javaTrace($ex, $seen = null)
    {
        $starter = $seen ? 'Caused by: ' : '';
        $result = array();
        if (!$seen) {
            $seen = array();
        }
        $trace  = $ex->getTrace();
        $prev   = $ex->getPrevious();
        $result[] = sprintf('%s%s: %s', $starter, get_class($ex), $ex->getMessage());
        $file = $ex->getFile();
        $line = $ex->getLine();
        while (true) {
            $current = "$file:$line";
            if (is_array($seen) && in_array($current, $seen)) {
                $result[] = sprintf(' ... %d more', count($trace) + 1);
                break;
            }
            $result[] = sprintf(
                ' at %s%s%s(%s%s%s)',
                count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
                count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '',
                count($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : '(main)',
                $line === null ? $file : basename($file),
                $line === null ? '' : ':',
                $line === null ? '' : $line
            );
            if (is_array($seen)) {
                $seen[] = "$file:$line";
            }
            if (!count($trace)) {
                break;
            }
            $file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';
            $line = array_key_exists('file', $trace[0]) && array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;
            array_shift($trace);
        }
        $result = join("\n", $result);
        if ($prev) {
            $result  .= "\n" . jTraceEx($prev, $seen);
        }

        return $result;
    }

    /**
     * @return WebDriver|null
     */
    public function getWebDriver()
    {
        // NB: Travis might be slow, better try more than once to connect.
        $tries = 0;
        $success = false;
        $webDriver = null;
        do {
            try {
                $address = getenv('WEBDRIVERHOST') ?: 'localhost';
                $host = 'http://' . $address . ':' . TestBaseClassWeb::$webPort . '/wd/hub'; // this is the default
                $capabilities = DesiredCapabilities::firefox();
                $capabilities->setCapability('acceptInsecureCerts', true);
                $profile = new FirefoxProfile();
                $profile->setPreference(FirefoxPreferences::READER_PARSE_ON_LOAD_ENABLED, false);

                // Open target="_blank" in new tab.
                $profile->setPreference('browser.link.open_newwindow', 3);

                // When set to 2, the location specified for the most recent download is utilized again.
                $profile->setPreference('browser.download.folderList', 2);

                // Further settings to automatically download exported theme files.
                // Test testExportAndImport() in ThemeControllerTest depends on these lines.
                $profile->setPreference('browser.download.dir', ROOT . '/tmp/');
                $profile->setPreference('browser.download.panel.shown', false);
                $profile->setPreference('browser.helperApps.neverAsk.saveToDisk', 'application/force-download');

                $profile->setPreference('browser.download.manager.showAlertOnComplete', false);
                $profile->setPreference('browser.download.manager.closeWhenDone', false);
                $profile->setPreference('browser.download.manager.showAlertInterval', 100);
                $profile->setPreference('browser.download.manager.resumeOnWakeDelay', 0);

                // This two lines are necessary to avoid issue https://github.com/SeleniumHQ/docker-selenium/issues/388.
                $profile->setPreference('browser.tabs.remote.autostart', false);
                $profile->setPreference('browser.tabs.remote.autostart.2', false);

                $capabilities->setCapability('acceptSslCerts', true);
                $capabilities->setCapability('acceptInsecureCerts', true);

                $capabilities->setCapability(FirefoxDriver::PROFILE, $profile);
                $webDriver = LimeSurveyWebDriver::create($host, $capabilities, 5000);

                $success = true;
            } catch (WebDriverException $ex) {
                $tries++;
                sleep(1);
            }
        } while (!$success && $tries < 5);

        return $webDriver;
    }

    /**
     * Executes an SQL file
     *
     * @param string $sFileName
     * @param \CDbConnection $connection
     * @return array|bool
     */
    private static function executeSQLFile($sFileName, $connection)
    {
        $aMessages = array();
        $sCommand = '';

        if (!is_readable($sFileName)) {
            return false;
        } else {
            $aLines = file($sFileName);
        }
        foreach ($aLines as $sLine) {
            $sLine = rtrim($sLine);
            $iLineLength = strlen($sLine);

            if ($iLineLength && $sLine[0] != '#' && substr($sLine, 0, 2) != '--') {
                if (substr($sLine, $iLineLength - 1, 1) == ';') {
                    $sCommand .= $sLine;
                    $sDatabasePrefix = Yii::app()->db->tablePrefix;
                    $sCommand = str_replace('prefix_', $sDatabasePrefix, $sCommand); // Table prefixes

                    try {
                        $connection->createCommand($sCommand)->execute();
                    } catch (Exception $e) {
                        $aMessages[] = "Executing: " . $sCommand . " failed! Reason: " . $e;
                    }

                    $sCommand = '';
                } else {
                    $sCommand .= $sLine;
                }
            }
        }
        return $aMessages;
    }

    /**
     * Reset the host info property of the request.
     */
    public function resetHostInfo()
    {
        // Use reflection to set the private "_hostInfo" property to null, as the public
        // setter does not allow setting it to null.
        $reflection = new \ReflectionClass("CHttpRequest");
        $property = $reflection->getProperty('_hostInfo');
        $property->setAccessible(true);
        $property->setValue(Yii::app()->getRequest(), null);
    }
}
