<?PHP

/*
* LimeSurvey
* Copyright (C) 2007-2011 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

use LimeSurvey\Helpers\Update\DatabaseUpdateBase;

/* Rules:
- Never use models in the upgrade process - never ever!
- Use the provided addColumn, alterColumn, dropPrimaryKey etc. functions where applicable - they ensure cross-DB compatibility
- Never use foreign keys
- Use only the field types listed here:

    pk: auto-incremental primary key type (“int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY”).
    string: string type (“varchar(255)”).
    text: a long string type (“text”) - MySQL: max size 64kb - Postgres: unlimited - MSSQL: max size 2.1GB
    mediumtext: a long string type (“text”) - MySQL: max size 16MB - Postgres: unlimited - MSSQL: max size 2.1GB
    longtext: a long string type (“text”) - MySQL: max size 2.1 GB - Postgres: unlimited - MSSQL: max size 2.1GB
    integer: integer type (“int(11)”).
    boolean: boolean type (“tinyint(1)”).
    float: float number type (“float”).
    decimal: decimal number type (“decimal”).
    datetime: datetime type (“datetime”).
    time: time type (“time”).
    date: date type (“date”).
    binary: binary data type (“blob”).

    These are case-sensitive - only use lowercase!
- If you want to use database functions make sure they exist on all three supported database types
- Always prefix key names by using curly brackets {{ }}

*/

/**
* @param integer $iOldDBVersion The previous database version
* @param boolean $bSilent Run update silently with no output - this checks if the update can be run silently at all. If not it will not run any updates at all.
*/
function db_upgrade_all($iOldDBVersion, $bSilent = false)
{
    /**
     * If you add a new database version add any critical database version numbers to this array. See link
     * @link https://www.limesurvey.org/manual/Database_versioning for explanations
     * @var array $aCriticalDBVersions An array of cricital database version.
     */
    $aCriticalDBVersions = array(310, 400, 450, 600);
    $aAllUpdates         = range($iOldDBVersion + 1, Yii::app()->getConfig('dbversionnumber'));

    // If trying to update silenty check if it is really possible
    if ($bSilent && (count(array_intersect($aCriticalDBVersions, $aAllUpdates)) > 0)) {
        return false;
    }
    // If DBVersion is older than 184 don't allow database update
    if ($iOldDBVersion < 132) {
        return false;
    }

    /// This function does anything necessary to upgrade
    /// older versions to match current functionality

    Yii::app()->loadHelper('database');
    Yii::import('application.helpers.admin.import_helper', true);
    $oDB                        = Yii::app()->getDb();
    $oDB->schemaCachingDuration = 0; // Deactivate schema caching
    Yii::app()->setConfig('Updating', true);
    $options = "";
    // The engine has to be explicitely set because MYSQL 8 switches the default engine to INNODB
    if (Yii::app()->db->driverName == 'mysql') {
        $options = 'ENGINE=' . Yii::app()->getConfig('mysqlEngine') . ' DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
        if (Yii::app()->getConfig('mysqlEngine') == 'INNODB') {
            $options .= ' ROW_FORMAT=DYNAMIC'; // Same than create-database
        }
    }
    try {
        // Get all relevant files from updates/ folder
        $updates = getRelevantUpdates($iOldDBVersion, Yii::app()->db, $options);
        foreach ($updates as $update) {
            // NB: safeUp() wraps up() inside a transaction and also updates DBVersion.
            $update->safeUp();
        }
    } catch (Exception $e) {
        Yii::app()->setConfig('Updating', false);
        // Activate schema caching
        $oDB->schemaCachingDuration = 3600;
        // Load all tables of the application in the schema
        $oDB->schema->getTables();
        // clear the cache of all loaded tables
        $oDB->schema->refresh();
        $trace = $e->getTrace();
        $fileInfo = explode('/', $trace[1]['file']);
        $file = end($fileInfo);
        Yii::app()->user->setFlash(
            'error',
            gT('An non-recoverable error happened during the update. Error details:')
            . '<p>'
            . htmlspecialchars($e->getMessage())
            . '</p><br />'
            . sprintf(gT('File %s, line %s.'), $file, $trace[1]['line'])
        );
        // If we're debugging, re-throw the exception.
        if (defined('YII_DEBUG') && YII_DEBUG) {
            throw $e;
        }
        return false;
    }

    // Activate schema cache first - otherwise it won't be refreshed!
    $oDB->schemaCachingDuration = 3600;
    // Load all tables of the application in the schema
    $oDB->schema->getTables();
    // clear the cache of all loaded tables
    $oDB->schema->refresh();
    $oDB->active = false;
    $oDB->active = true;

    // Force User model to refresh meta data (for updates from very old versions)
    User::model()->refreshMetaData();
    Yii::app()->db->schema->getTable('{{surveys}}', true);
    Yii::app()->db->schema->getTable('{{templates}}', true);
    Survey::model()->refreshMetaData();
    Notification::model()->refreshMetaData();

    // Try to clear tmp/runtime (database cache files).
    // Related to problems like https://bugs.limesurvey.org/view.php?id=13699.
    // Some cache implementations may not have 'flush' method. Only call flush if method exists.
    if (method_exists(Yii::app()->cache, 'flush')) {
        Yii::app()->cache->flush();
    }
    // Some cache implementations (ex: CRedisCache, CDummyCache) may not have 'gc' method. Only call gc if method exists.
    if (method_exists(Yii::app()->cache, 'gc')) {
        Yii::app()->cache->gc();
    }

    // Inform superadmin about update
    $superadmins = User::model()->getSuperAdmins();
    $currentDbVersion = $oDB->createCommand()->select('stg_value')->from('{{settings_global}}')->where("stg_name=:stg_name", array('stg_name' => 'DBVersion'))->queryRow();
    // Update the global config object because it is static and set at start of App
    Yii::app()->setConfig('DBVersion', $currentDbVersion['stg_value']);

    Notification::broadcast(array(
        'title' => gT('Database update'),
        'message' => sprintf(gT('The database has been updated from version %s to version %s.'), $iOldDBVersion, $currentDbVersion['stg_value'])
        ), $superadmins);

    fixLanguageConsistencyAllSurveys();

    Yii::app()->setConfig('Updating', false);
    return true;
}

/**
 * Update previous encrpted values to new encryption
 * @param CDbConnection $oDB
 * @throws CException
 */
function updateEncryptedValues450(CDbConnection $oDB)
{
    Yii::app()->sodium;
    // All these functions decrypt and then re-encrypt the values.
    decryptarchivedtables450($oDB);
    decryptResponseTables450($oDB);
    decryptParticipantTables450($oDB);
    decryptCPDBTable450($oDB);
}

/**
 * Update encryption for CPDB participants
 *
 * @param CDbConnection $oDB
 * @return void
 * @throws CException
 */
function decryptCPDBTable450($oDB)
{
    // decrypt CPDB participants
    $CPDBParticipants = $oDB->createCommand()
        ->select('*')
        ->from('{{participants}}')
        ->queryAll();
    $participantAttributeNames = $oDB->createCommand()
        ->select('*')
        ->from('{{participant_attribute_names}}')
        ->queryAll();
    foreach ($CPDBParticipants as $CPDBParticipant) {
        $extraAttributes = $oDB->createCommand()
            ->select('*')
            ->from('{{participant_attribute}}')
            ->where('participant_id =:participant_id', ['participant_id' => $CPDBParticipant['participant_id']])
            ->queryAll();
        $recryptedParticipant = [];
        foreach ($participantAttributeNames as $key => $participantAttributeValue) {
            if ($participantAttributeValue['encrypted'] === 'Y') {
                if ($participantAttributeValue['core_attribute'] === 'N') {
                    foreach ($extraAttributes as $extraAttribute) {
                        if ($extraAttribute['attribute_id'] === $participantAttributeValue['attribute_id']) {
                            $encryptedValue = $extraAttribute['value'];
                            $decrypedParticipantAttribute = LSActiveRecord::decryptSingleOld($encryptedValue);
                            $recryptedParticipantAttribute['value'] = LSActiveRecord::encryptSingle($decrypedParticipantAttribute);
                            $oDB->createCommand()->update('{{participant_attribute}}', $recryptedParticipantAttribute, 'participant_id=' . $oDB->quoteValue($CPDBParticipant['participant_id']) . 'AND attribute_id=' . $oDB->quoteValue($extraAttribute['attribute_id']));
                            break;
                        }
                    }
                } else {
                    $encryptedValue = $CPDBParticipant[$participantAttributeValue['defaultname']];
                    $decrypedParticipantAttribute = LSActiveRecord::decryptSingleOld($encryptedValue);
                    $recryptedParticipant[$participantAttributeValue['defaultname']] = LSActiveRecord::encryptSingle($decrypedParticipantAttribute);
                }
            }
        }
        if ($recryptedParticipant) {
            $oDB->createCommand()->update('{{participants}}', $recryptedParticipant, 'participant_id=' . $oDB->quoteValue($CPDBParticipant['participant_id']));
        }
    }
}

/**
 * Update encryption for survey participants
 * @param CDbConnection $oDB
 * @return void
 */
function decryptParticipantTables450($oDB)
{
    // decrypt survey participants
    $surveys = $oDB->createCommand()
        ->select('*')
        ->from('{{surveys}}')
        ->queryAll();
    foreach ($surveys as $survey) {
        $tableExists = tableExists("{{tokens_{$survey['sid']}}}");
        if (!$tableExists) {
            continue;
        }
        $tableSchema = $oDB->getSchema()->getTable("{{tokens_{$survey['sid']}}}");
        $tokens = $oDB->createCommand()
            ->select('*')
            ->from("{{tokens_{$survey['sid']}}}")
            ->queryAll();
        $tokenencryptionoptions = json_decode((string) $survey['tokenencryptionoptions'], true);

        // default attributes
        if (!empty($tokenencryptionoptions)) {
            foreach ($tokenencryptionoptions['columns'] as $column => $encrypted) {
                $columnEncryptions[$column]['encrypted'] = $encrypted;
            }
        }

        // find custom attribute column names
        $aCustomAttributes = array_filter(array_keys($tableSchema->columns), 'filterForAttributes');

        // custom attributes
        foreach ($aCustomAttributes as $attributeName) {
            if (isset(json_decode((string) $survey['attributedescriptions'])->$attributeName->encrypted)) {
                $columnEncryptions[$attributeName]['encrypted'] = json_decode((string) $survey['attributedescriptions'], true)[$attributeName]['encrypted'];
            } else {
                $columnEncryptions[$attributeName]['encrypted'] = 'N';
            }
        }

        if (isset($columnEncryptions) && $columnEncryptions) {
            foreach ($tokens as $token) {
                $recryptedToken = [];
                foreach ($columnEncryptions as $column => $value) {
                    if ($columnEncryptions[$column]['encrypted'] === 'Y' && isset($token[$column])) {
                        $decryptedTokenColumn = LSActiveRecord::decryptSingleOld($token[$column]);
                        $recryptedToken[$column] = LSActiveRecord::encryptSingle($decryptedTokenColumn);
                    }
                }
                if ($recryptedToken) {
                    $oDB->createCommand()->update("{{tokens_{$survey['sid']}}}", $recryptedToken, 'tid=' . $token['tid']);
                }
            }
        }
    }
}

/**
 * Update encryption for survey responses
 *
 * @param CDbConnection $oDB
 * @return void
 * @throws CException
 */
function decryptResponseTables450($oDB)
{
    $surveys = $oDB->createCommand()
        ->select('*')
        ->from('{{surveys}}')
        ->where('active =:active', ['active' => 'Y'])
        ->queryAll();
    foreach ($surveys as $survey) {
        $tableExists = tableExists("{{survey_{$survey['sid']}}}");
        if (!$tableExists) {
            continue;
        }
        $responsesCount = $oDB->createCommand()
            ->select('count(*)')
            ->from("{{survey_{$survey['sid']}}}")
            ->queryScalar();
        if ($responsesCount) {
            $maxRows = 100;
            $maxPages = ceil($responsesCount / $maxRows);

            for ($i = 0; $i < $maxPages; $i++) {
                $offset = $i * $maxRows;
                $responses = $oDB->createCommand()
                    ->select('*')
                    ->from("{{survey_{$survey['sid']}}}")
                    ->offset($offset)
                    ->limit($maxRows)
                    ->queryAll();
                $fieldmapFields = createFieldMap450($survey);
                foreach ($responses as $response) {
                    $recryptedResponse = [];
                    foreach ($fieldmapFields as $fieldname => $field) {
                        if (array_key_exists('encrypted', $field) && $field['encrypted'] === 'Y') {
                            $decryptedResponseField = LSActiveRecord::decryptSingleOld($response[$fieldname]);
                            $recryptedResponse[$fieldname] = LSActiveRecord::encryptSingle($decryptedResponseField);
                        }
                    }
                    if ($recryptedResponse) {
                        // use createUpdateCommand() because the update() function does not properly escape auto generated params causing errors
                        $criteria = $oDB->getCommandBuilder()->createCriteria('id=:id', ['id' => $response['id']]);
                        $oDB->getCommandBuilder()->createUpdateCommand("{{survey_{$survey['sid']}}}", $recryptedResponse, $criteria)->execute();
                    }
                }
            }
        }
    }
}

/**
 * Update Encryption for archived tables
 *
 * @param CDbConnection $oDB
 * @return void
 * @throws CDbException
 * @throws CException
 */
function decryptArchivedTables450($oDB)
{
    $archivedTablesSettings = $oDB->createCommand('SELECT * FROM {{archived_table_settings}}')->queryAll();
    foreach ($archivedTablesSettings as $archivedTableSettings) {
        $tableExists = tableExists("{{{$archivedTableSettings['tbl_name']}}}");
        if (!$tableExists) {
            continue;
        }
        $archivedTableSettingsProperties = json_decode((string) $archivedTableSettings['properties'], true);
        $archivedTableSettingsAttributes = json_decode((string) $archivedTableSettings['attributes'], true);

        // recrypt tokens
        if ($archivedTableSettings['tbl_type'] === 'token') {
            // skip if the encryption status is unknown, use reset because of mixed array types
            if (!empty($archivedTableSettingsProperties) && reset($archivedTableSettingsProperties) !== 'unknown') {
                $tokenencryptionoptions = $archivedTableSettingsProperties;

                // default attributes
                foreach ($tokenencryptionoptions['columns'] as $column => $encrypted) {
                    $columnEncryptions[$column]['encrypted'] = $encrypted;
                }
            }
            // skip if the encryption status is unknown, use reset because of mixed array types
            if (!empty($archivedTableSettingsAttributes) && reset($archivedTableSettingsAttributes) !== 'unknown') {
                // find custom attribute column names
                $table = tableExists("{{{$archivedTableSettings['tbl_name']}}}");
                if (!$table) {
                    $aCustomAttributes = [];
                } else {
                    $aCustomAttributes = array_filter(array_keys($oDB->schema->getTable("{{{$archivedTableSettings['tbl_name']}}}")->columns), 'filterForAttributes');
                }

                // custom attributes
                foreach ($aCustomAttributes as $attributeName) {
                    if (isset(json_decode((string) $archivedTableSettings['attributes'])->$attributeName->encrypted)) {
                        $columnEncryptions[$attributeName]['encrypted'] = $archivedTableSettingsAttributes[$attributeName]['encrypted'];
                    } else {
                        $columnEncryptions[$attributeName]['encrypted'] = 'N';
                    }
                }
            }
            if (isset($columnEncryptions) && $columnEncryptions) {
                $archivedTableRows = $oDB
                    ->createCommand()
                    ->select('*')
                    ->from("{{{$archivedTableSettings['tbl_name']}}}")
                    ->queryAll();
                foreach ($archivedTableRows as $archivedToken) {
                    $recryptedToken = [];
                    foreach ($columnEncryptions as $column => $value) {
                        if ($value['encrypted'] === 'Y') {
                            $decryptedTokenColumn = LSActiveRecord::decryptSingleOld($archivedToken[$column]);
                            $recryptedToken[$column] = LSActiveRecord::encryptSingle($decryptedTokenColumn);
                        }
                    }
                    if ($recryptedToken) {
                        $oDB->createCommand()->update("{{{$archivedTableSettings['tbl_name']}}}", $recryptedToken, 'tid=' . $archivedToken['tid']);
                    }
                }
            }
        }

        // recrypt responses // skip if the encryption status is unknown, use reset because of mixed array types
        if ($archivedTableSettings['tbl_type'] === 'response' && !empty($archivedTableSettingsProperties) && reset($archivedTableSettingsProperties) !== 'unknown') {
            $responsesCount = $oDB->createCommand()
                ->select('count(*)')
                ->from("{{{$archivedTableSettings['tbl_name']}}}")
                ->queryScalar();
            if ($responsesCount) {
                $responseTableSchema = $oDB->schema->getTable("{{{$archivedTableSettings['tbl_name']}}}");
                $encryptedResponseAttributes = $archivedTableSettingsProperties;

                $fieldMap = [];
                foreach ($responseTableSchema->getColumnNames() as $name) {
                    // Skip id field.
                    if ($name === 'id') {
                        continue;
                    }
                    $fieldMap[$name] = $name;
                }

                $maxRows = 100;
                $maxPages = ceil($responsesCount / $maxRows);
                for ($i = 0; $i < $maxPages; $i++) {
                    $offset = $i * $maxRows;
                    $archivedTableRows = $oDB
                        ->createCommand()
                        ->select('*')
                        ->from("{{{$archivedTableSettings['tbl_name']}}}")
                        ->offset($offset)
                        ->limit($maxRows)
                        ->queryAll();
                    foreach ($archivedTableRows as $archivedResponse) {
                        $recryptedResponseValues = [];
                        foreach ($fieldMap as $column) {
                            if (in_array($column, $encryptedResponseAttributes, false)) {
                                $decryptedColumnValue = LSActiveRecord::decryptSingleOld($archivedResponse[$column]);
                                $recryptedResponseValues[$column] = LSActiveRecord::encryptSingle($decryptedColumnValue);
                            }
                        }
                        if ($recryptedResponseValues) {
                            // use createUpdateCommand() because the update() function does not properly escape auto generated params causing errors
                            $criteria = $oDB->getCommandBuilder()->createCriteria('id=:id', ['id' => $archivedResponse['id']]);
                            $oDB->getCommandBuilder()->createUpdateCommand("{{{$archivedTableSettings['tbl_name']}}}", $recryptedResponseValues, $criteria)->execute();
                        }
                    }
                }
            }
        }
    }
}

/**
 * Returns the fieldmap for responses
 *
 * @param $survey
 * @return array
 * @throws CException
 * @psalm-suppress RedundantCondition
 */
function createFieldMap450($survey): array
{
    // Main query
    $style = 'full';
    $defaultValues = null;
    $quotedGroups = Yii::app()->db->quoteTableName('{{groups}}');
    $aquery = 'SELECT g.*, q.*, gls.*, qls.*, qa.attribute, qa.value'
        . " FROM $quotedGroups g"
        . ' JOIN {{questions}} q on q.gid=g.gid '
        . ' JOIN {{group_l10ns}} gls on gls.gid=g.gid '
        . ' JOIN {{question_l10ns}} qls on qls.qid=q.qid '
        . " LEFT JOIN {{question_attributes}} qa ON qa.qid=q.qid AND qa.attribute='question_template' "
        . " WHERE qls.language='{$survey['language']}' and gls.language='{$survey['language']}' AND"
        . " g.sid={$survey['sid']} AND"
        . ' q.parent_qid=0'
        . ' ORDER BY group_order, question_order';
    $questions = Yii::app()->db->createCommand($aquery)->queryAll();
    $questionSeq = -1; // this is incremental question sequence across all groups
    $groupSeq = -1;
    $_groupOrder = -1;

    //getting all question_types which are NOT extended
    $baseQuestions = Yii::app()->db->createCommand()
        ->select('*')
        ->from('{{question_themes}}')
        ->where('extends = :extends', ['extends' => ''])
        ->queryAll();
    $questionTypeMetaData = [];
    foreach ($baseQuestions as $baseQuestion) {
        $baseQuestion['settings'] = json_decode((string) $baseQuestion['settings']);
        $questionTypeMetaData[$baseQuestion['question_type']] = $baseQuestion;
    }

    foreach ($questions as $arow) {
        //For each question, create the appropriate field(s))

        ++$questionSeq;

        // fix fact that the group_order may have gaps
        if ($_groupOrder !== $arow['group_order']) {
            $_groupOrder = $arow['group_order'];
            ++$groupSeq;
        }
        // Condition indicators are obsolete with EM.  However, they are so tightly coupled into LS code that easider to just set values to 'N' for now and refactor later.
        $conditions = 'N';
        $usedinconditions = 'N';

        // Check if answertable has custom setting for current question
        if (isset($arow['attribute']) && isset($arow['type']) && $arow['attribute'] === 'question_template') {
            // cache the value between function calls
            static $cacheMemo = [];
            $cacheKey = $arow['value'] . '_' . $arow['type'];
            if (isset($cacheMemo[$cacheKey])) {
                $answerColumnDefinition = $cacheMemo[$cacheKey];
            } else {
                if ($arow['value'] === 'core') {
                    $questionTheme = Yii::app()->db->createCommand()
                        ->select('*')
                        ->from('{{question_themes}}')
                        ->where('question_type=:question_type AND extends=:extends', ['question_type' => $arow['type'], 'extends' => ''])
                        ->queryAll();
                } else {
                    $questionTheme = Yii::app()->db->createCommand()
                        ->select('*')
                        ->from('{{question_themes}}')
                        ->where('name=:name AND question_type=:question_type', ['name' => $arow['value'], 'question_type' => $arow['type']])
                        ->queryAll();
                }

                $answerColumnDefinition = '';
                if (isset($questionTheme['xml_path'])) {
                    if (PHP_VERSION_ID < 80000) {
                        $bOldEntityLoaderState = libxml_disable_entity_loader(true);
                    }
                    $sQuestionConfigFile = file_get_contents(App()->getConfig('rootdir') . DIRECTORY_SEPARATOR . $questionTheme['xml_path'] . DIRECTORY_SEPARATOR . 'config.xml');  // @see: Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string
                    $oQuestionConfig = simplexml_load_string($sQuestionConfigFile);
                    if (isset($oQuestionConfig->metadata->answercolumndefinition)) {
                        $answerColumnDefinition = json_decode(json_encode($oQuestionConfig->metadata->answercolumndefinition), true)[0];
                    }
                    if (PHP_VERSION_ID < 80000) {
                        libxml_disable_entity_loader($bOldEntityLoaderState);
                    }
                }
                $cacheMemo[$cacheKey] = $answerColumnDefinition;
            }
        }

        // Field identifier
        // GXQXSXA
        // G=Group  Q=Question S=Subquestion A=Answer Option
        // If S or A don't exist then set it to 0
        // Implicit (subqestion intermal to a question type) or explicit qubquestions/answer count starts at 1

        // Types "L", "!", "O", "D", "G", "N", "X", "Y", "5", "S", "T", "U"
        $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}";

        if ($questionTypeMetaData[$arow['type']]['settings']->subquestions == 0 && $arow['type'] != Question::QT_R_RANKING && $arow['type'] != Question::QT_VERTICAL_FILE_UPLOAD) {
            if (isset($fieldmap[$fieldname])) {
                $aDuplicateQIDs[$arow['qid']] = ['fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']];
            }

            $fieldmap[$fieldname] = ["fieldname" => $fieldname, 'type' => "{$arow['type']}", 'sid' => $survey['sid'], "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => ""];
            if (isset($answerColumnDefinition)) {
                $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
            }

            if ($style === 'full') {
                $fieldmap[$fieldname]['title'] = $arow['title'];
                $fieldmap[$fieldname]['question'] = $arow['question'];
                $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                $fieldmap[$fieldname]['hasconditions'] = $conditions;
                $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                if (isset($defaultValues[$arow['qid'] . '~0'])) {
                    $fieldmap[$fieldname]['defaultvalue'] = $defaultValues[$arow['qid'] . '~0'];
                }
            }
            switch ($arow['type']) {
                case Question::QT_L_LIST:  //RADIO LIST
                case Question::QT_EXCLAMATION_LIST_DROPDOWN:  //DROPDOWN LIST
                    if ($arow['other'] === 'Y') {
                        $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other";
                        if (isset($fieldmap[$fieldname])) {
                            $aDuplicateQIDs[$arow['qid']] = ['fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']];
                        }

                        $fieldmap[$fieldname] = [
                            "fieldname" => $fieldname,
                            'type'      => $arow['type'],
                            'sid'       => $survey['sid'],
                            "gid"       => $arow['gid'],
                            "qid"       => $arow['qid'],
                            "aid"       => "other"
                        ];
                        if (isset($answerColumnDefinition)) {
                            $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                        }

                        // dgk bug fix line above. aid should be set to "other" for export to append to the field name in the header line.
                        if ($style === 'full') {
                            $fieldmap[$fieldname]['title'] = $arow['title'];
                            $fieldmap[$fieldname]['question'] = $arow['question'];
                            $fieldmap[$fieldname]['subquestion'] = gT("Other");
                            $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                            $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                            $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                            $fieldmap[$fieldname]['hasconditions'] = $conditions;
                            $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                            $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                            $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                            if (isset($defaultValues[$arow['qid'] . '~other'])) {
                                $fieldmap[$fieldname]['defaultvalue'] = $defaultValues[$arow['qid'] . '~other'];
                            }
                        }
                    }
                    break;
                case Question::QT_O_LIST_WITH_COMMENT: //DROPDOWN LIST WITH COMMENT
                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}comment";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = ['fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']];
                    }

                    $fieldmap[$fieldname] = [
                        "fieldname" => $fieldname,
                        'type'      => $arow['type'],
                        'sid'       => $survey['sid'],
                        "gid"       => $arow['gid'],
                        "qid"       => $arow['qid'],
                        "aid"       => "comment"
                    ];
                    if (isset($answerColumnDefinition)) {
                        $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                    }

                    // dgk bug fix line below. aid should be set to "comment" for export to append to the field name in the header line. Also needed set the type element correctly.
                    if ($style === 'full') {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion'] = gT("Comment");
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                        $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                        $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    }
                    break;
            }
        } elseif ($questionTypeMetaData[$arow['type']]['settings']->subquestions == 2 && $questionTypeMetaData[$arow['type']]['settings']->answerscales == 0) {
            //MULTI FLEXI
            $abrows = getSubQuestions($survey['sid'], $arow['qid'], $survey['language']);
            //Now first process scale=1
            $answerset = [];
            $answerList = [];
            foreach ($abrows as $key => $abrow) {
                if ($abrow['scale_id'] == 1) {
                    $answerset[] = $abrow;
                    $answerList[] = [
                        'code'   => $abrow['title'],
                        'answer' => $abrow['question'],
                    ];
                    unset($abrows[$key]);
                }
            }
            reset($abrows);
            foreach ($abrows as $abrow) {
                foreach ($answerset as $answer) {
                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}_{$answer['title']}";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = ['fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']];
                    }
                    $fieldmap[$fieldname] = [
                        "fieldname" => $fieldname,
                        'type'      => $arow['type'],
                        'sid'       => $survey['sid'],
                        "gid"       => $arow['gid'],
                        "qid"       => $arow['qid'],
                        "aid"       => $abrow['title'] . "_" . $answer['title'],
                        "sqid"      => $abrow['qid']
                    ];
                    if (isset($answerColumnDefinition)) {
                        $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                    }

                    if ($style === 'full') {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion1'] = $abrow['question'];
                        $fieldmap[$fieldname]['subquestion2'] = $answer['question'];
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                        $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                        $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                        $fieldmap[$fieldname]['preg'] = $arow['preg'];
                        $fieldmap[$fieldname]['answerList'] = $answerList;
                        $fieldmap[$fieldname]['SQrelevance'] = $abrow['relevance'];
                    }
                }
            }
            unset($answerset);
        } elseif ($arow['type'] === Question::QT_1_ARRAY_DUAL) {
            $abrows = getSubQuestions($survey['sid'], $arow['qid'], $survey['language']);
            foreach ($abrows as $abrow) {
                $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}#0";
                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = ['fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']];
                }

                $fieldmap[$fieldname] = ["fieldname" => $fieldname, 'type' => $arow['type'], 'sid' => $survey['sid'], "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => $abrow['title'], "scale_id" => 0];
                if (isset($answerColumnDefinition)) {
                    $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                }

                if ($style === 'full') {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['scale'] = gT('Scale 1');
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    $fieldmap[$fieldname]['SQrelevance'] = $abrow['relevance'];
                }

                $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}#1";
                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = ['fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']];
                }
                $fieldmap[$fieldname] = ["fieldname" => $fieldname, 'type' => $arow['type'], 'sid' => $survey['sid'], "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => $abrow['title'], "scale_id" => 1];
                if (isset($answerColumnDefinition)) {
                    $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                }

                if ($style === 'full') {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['scale'] = gT('Scale 2');
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                }
            }
        } elseif ($arow['type'] === Question::QT_R_RANKING) {
            // Sub question by answer number OR attribute
            $answersCount = Yii::app()->db->createCommand()
                ->select('count(*)')
                ->from('{{answers}}')
                ->where('qid = :qid', ['qid' => $arow['qid']])
                ->queryScalar();
            $maxDbAnswer = Yii::app()->db->createCommand()
                ->select('*')
                ->from('{{question_attributes}}')
                ->where("qid = :qid AND attribute = 'max_subquestions'", [':qid' => $arow['qid']])
                ->queryRow();
            $columnsCount = (!$maxDbAnswer || (int)$maxDbAnswer['value'] < 1) ? $answersCount : (int)$maxDbAnswer['value'];
            $columnsCount = min($columnsCount, $answersCount); // Can not be upper than current answers #14899
            for ($i = 1; $i <= $columnsCount; $i++) {
                $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}$i";
                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = ['fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']];
                }
                $fieldmap[$fieldname] = ["fieldname" => $fieldname, 'type' => $arow['type'], 'sid' => $survey['sid'], "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => $i];
                if (isset($answerColumnDefinition)) {
                    $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                }

                if ($style === 'full') {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['subquestion'] = sprintf(gT('Rank %s'), $i);
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                }
            }
        } elseif ($arow['type'] === Question::QT_VERTICAL_FILE_UPLOAD) {
            $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}";
            $fieldmap[$fieldname] = [
                "fieldname" => $fieldname,
                'type'      => $arow['type'],
                'sid'       => $survey['sid'],
                "gid"       => $arow['gid'],
                "qid"       => $arow['qid'],
                "aid"       => ''
            ];
            if (isset($answerColumnDefinition)) {
                $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
            }

            if ($style === 'full') {
                $fieldmap[$fieldname]['title'] = $arow['title'];
                $fieldmap[$fieldname]['question'] = $arow['question'];
                $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                $fieldmap[$fieldname]['hasconditions'] = $conditions;
                $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
            }
            $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}" . "_filecount";
            $fieldmap[$fieldname] = [
                "fieldname" => $fieldname,
                'type'      => $arow['type'],
                'sid'       => $survey['sid'],
                "gid"       => $arow['gid'],
                "qid"       => $arow['qid'],
                "aid"       => "filecount"
            ];
            if (isset($answerColumnDefinition)) {
                $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
            }

            if ($style === 'full') {
                $fieldmap[$fieldname]['title'] = $arow['title'];
                $fieldmap[$fieldname]['question'] = "filecount - " . $arow['question'];
                $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                $fieldmap[$fieldname]['hasconditions'] = $conditions;
                $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
            }
        } else {
            // Question types with subquestions and one answer per subquestion  (M/A/B/C/E/F/H/P)
            //MULTI ENTRY
            $abrows = getSubQuestions($survey['sid'], $arow['qid'], $survey['language']);
            foreach ($abrows as $abrow) {
                $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}";

                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = ['fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']];
                }
                $fieldmap[$fieldname] = [
                    "fieldname" => $fieldname,
                    'type'      => $arow['type'],
                    'sid'       => $survey['sid'],
                    'gid'       => $arow['gid'],
                    'qid'       => $arow['qid'],
                    'aid'       => $abrow['title'],
                    'sqid'      => $abrow['qid']
                ];
                if (isset($answerColumnDefinition)) {
                    $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                }

                if ($style === 'full') {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    $fieldmap[$fieldname]['preg'] = $arow['preg'];
                    // get SQrelevance from DB
                    $fieldmap[$fieldname]['SQrelevance'] = $abrow['relevance'];
                    if (isset($defaultValues[$arow['qid'] . '~' . $abrow['qid']])) {
                        $fieldmap[$fieldname]['defaultvalue'] = $defaultValues[$arow['qid'] . '~' . $abrow['qid']];
                    }
                }
                if ($arow['type'] === Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS) {
                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}{$abrow['title']}comment";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = ['fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']];
                    }
                    $fieldmap[$fieldname] = ["fieldname" => $fieldname, 'type' => $arow['type'], 'sid' => $survey['sid'], "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => $abrow['title'] . "comment"];
                    if (isset($answerColumnDefinition)) {
                        $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                    }
                    if ($style === 'full') {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion1'] = gT('Comment');
                        $fieldmap[$fieldname]['subquestion'] = $abrow['question'];
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                        $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                        $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    }
                }
            }
            if ($arow['other'] === 'Y' && ($arow['type'] === Question::QT_M_MULTIPLE_CHOICE || $arow['type'] === Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS)) {
                $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}other";
                if (isset($fieldmap[$fieldname])) {
                    $aDuplicateQIDs[$arow['qid']] = ['fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']];
                }
                $fieldmap[$fieldname] = ["fieldname" => $fieldname, 'type' => $arow['type'], 'sid' => $survey['sid'], "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => "other"];
                if (isset($answerColumnDefinition)) {
                    $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                }

                if ($style === 'full') {
                    $fieldmap[$fieldname]['title'] = $arow['title'];
                    $fieldmap[$fieldname]['question'] = $arow['question'];
                    $fieldmap[$fieldname]['subquestion'] = gT('Other');
                    $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                    $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                    $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                    $fieldmap[$fieldname]['hasconditions'] = $conditions;
                    $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                    $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                    $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                    $fieldmap[$fieldname]['other'] = $arow['other'];
                }
                if ($arow['type'] === Question::QT_P_MULTIPLE_CHOICE_WITH_COMMENTS) {
                    $fieldname = "{$arow['sid']}X{$arow['gid']}X{$arow['qid']}othercomment";
                    if (isset($fieldmap[$fieldname])) {
                        $aDuplicateQIDs[$arow['qid']] = ['fieldname' => $fieldname, 'question' => $arow['question'], 'gid' => $arow['gid']];
                    }
                    $fieldmap[$fieldname] = ["fieldname" => $fieldname, 'type' => $arow['type'], 'sid' => $survey['sid'], "gid" => $arow['gid'], "qid" => $arow['qid'], "aid" => "othercomment"];
                    if (isset($answerColumnDefinition)) {
                        $fieldmap[$fieldname]['answertabledefinition'] = $answerColumnDefinition;
                    }

                    if ($style === 'full') {
                        $fieldmap[$fieldname]['title'] = $arow['title'];
                        $fieldmap[$fieldname]['question'] = $arow['question'];
                        $fieldmap[$fieldname]['subquestion'] = gT('Other comment');
                        $fieldmap[$fieldname]['group_name'] = $arow['group_name'];
                        $fieldmap[$fieldname]['mandatory'] = $arow['mandatory'];
                        $fieldmap[$fieldname]['encrypted'] = $arow['encrypted'];
                        $fieldmap[$fieldname]['hasconditions'] = $conditions;
                        $fieldmap[$fieldname]['usedinconditions'] = $usedinconditions;
                        $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
                        $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
                        $fieldmap[$fieldname]['other'] = $arow['other'];
                    }
                }
            }
        }
        if (isset($fieldmap[$fieldname])) {
            //set question relevance (uses last SQ's relevance field for question relevance)
            $fieldmap[$fieldname]['relevance'] = $arow['relevance'];
            $fieldmap[$fieldname]['grelevance'] = $arow['grelevance'];
            $fieldmap[$fieldname]['questionSeq'] = $questionSeq;
            $fieldmap[$fieldname]['groupSeq'] = $groupSeq;
            $fieldmap[$fieldname]['preg'] = $arow['preg'];
            $fieldmap[$fieldname]['other'] = $arow['other'];
            $fieldmap[$fieldname]['help'] = $arow['help'];
            // Set typeName
        } else {
            --$questionSeq; // didn't generate a valid $fieldmap entry, so decrement the question counter to ensure they are sequential
        }

        if (isset($fieldmap[$fieldname]['typename'])) {
            $fieldmap[$fieldname]['typename'] = $typename[$fieldname] = $arow['typename'];
        }
    }
    return $fieldmap;
}

/**
 * Import previously archived tables to ArchivedTableSettings
 *
 * @return void
 * @throws CException
 */
function upgradeArchivedTableSettings446()
{
    $db = Yii::app()->db;
    $DBPrefix = Yii::app()->db->tablePrefix;
    $datestamp = time();
    $DBDate = date('Y-m-d H:i:s', $datestamp);
    // TODO: Inject user model instead. Polling for user will create a session, which breaks on command-line.
    $userID = php_sapi_name() === 'cli' ? null : Yii::app()->user->getId();
    $forcedSuperadmin = Yii::app()->getConfig('forcedsuperadmin');
    $adminUserId = 1;

    if ($forcedSuperadmin && is_array($forcedSuperadmin)) {
        $adminUserId = $forcedSuperadmin[0];
    }
    $query = dbSelectTablesLike('{{old_}}%');
    $archivedTables = Yii::app()->db->createCommand($query)->queryColumn();
    $archivedTableSettings = Yii::app()->db->createCommand("SELECT * FROM {{archived_table_settings}}")->queryAll();
    foreach ($archivedTables as $archivedTable) {
        $tableName = substr((string) $archivedTable, strlen((string) $DBPrefix));
        $tableNameParts = explode('_', $tableName);
        $type = $tableNameParts[1] ?? '';
        $surveyID = $tableNameParts[2] ?? '';
        $typeExtended = $tableNameParts[3] ?? '';
        // skip if table entry allready exists
        foreach ($archivedTableSettings as $archivedTableSetting) {
            if ($archivedTableSetting['tbl_name'] === $tableName) {
                continue 2;
            }
        }

        $newArchivedTableSettings = [
            'survey_id'  => (int)$surveyID,
            'user_id'    => (int)($userID ?? $adminUserId),
            'tbl_name'   => $tableName,
            'created'    => $DBDate,
            'properties' => json_encode(['unknown'])
        ];
        if ($type === 'survey') {
            $newArchivedTableSettings['tbl_type'] = 'response';
            if ($typeExtended === 'timings') {
                $newArchivedTableSettings['tbl_type'] = 'timings';
                $db->createCommand()->insert('{{archived_table_settings}}', $newArchivedTableSettings);
                continue;
            }
            $db->createCommand()->insert('{{archived_table_settings}}', $newArchivedTableSettings);
            continue;
        }
        if ($type === 'tokens') {
            $newArchivedTableSettings['tbl_type'] = 'token';
            $db->createCommand()->insert('{{archived_table_settings}}', $newArchivedTableSettings);
            continue;
        }
    }
}

function extendDatafields429($oDB)
{
    if (Yii::app()->db->driverName == 'mysql' || Yii::app()->db->driverName == 'mysqi') {
        alterColumn('{{answer_l10ns}}', 'answer', "mediumtext", false);
        alterColumn('{{assessments}}', 'message', "mediumtext", false);
        alterColumn('{{group_l10ns}}', 'description', "mediumtext");
        alterColumn('{{notifications}}', 'message', "mediumtext", false);
        alterColumn('{{participant_attribute_values}}', 'value', "mediumtext", false);
        alterColumn('{{plugin_settings}}', 'value', "mediumtext");
        alterColumn('{{question_l10ns}}', 'question', "mediumtext", false);
        alterColumn('{{question_l10ns}}', 'help', "mediumtext");
        alterColumn('{{question_attributes}}', 'value', "mediumtext");
        alterColumn('{{quota_languagesettings}}', 'quotals_message', "mediumtext", false);
        alterColumn('{{settings_global}}', 'stg_value', "mediumtext", false);
        alterColumn('{{settings_user}}', 'stg_value', "mediumtext");
        alterColumn('{{surveymenu_entries}}', 'data', "mediumtext");
        // The following line fixes invalid entries having set 0000-00-00 00:00:00 as date
        $oDB->createCommand()->update('{{surveys}}', ['expires' => null], "expires=0");
        alterColumn('{{surveys}}', 'attributedescriptions', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'surveyls_description', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'surveyls_welcometext', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'surveyls_endtext', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'surveyls_policy_notice', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'surveyls_email_invite', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'surveyls_email_remind', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'surveyls_email_register', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'surveyls_email_confirm', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'email_admin_notification', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'email_admin_responses', "mediumtext");
        alterColumn('{{templates}}', 'license', "mediumtext");
        alterColumn('{{templates}}', 'description', "mediumtext");
        alterColumn('{{template_configuration}}', 'cssframework_css', "mediumtext");
        alterColumn('{{template_configuration}}', 'cssframework_js', "mediumtext");
        alterColumn('{{tutorials}}', 'settings', "mediumtext");
        alterColumn('{{tutorial_entries}}', 'content', "mediumtext");
        alterColumn('{{tutorial_entries}}', 'settings', "mediumtext");
    }
            alterColumn('{{surveys}}', 'additional_languages', "text");
}


/**
 * @param string $sMySQLCollation
 */
function upgradeSurveyTables402($sMySQLCollation)
{
    $oDB = Yii::app()->db;
    $oSchema = Yii::app()->db->schema;
    if (Yii::app()->db->driverName != 'pgsql') {
        $aTables = dbGetTablesLike("survey\_%");
        foreach ($aTables as $sTableName) {
            $oTableSchema = $oSchema->getTable($sTableName);
            if (!in_array('token', $oTableSchema->columnNames)) {
                continue;
            }
            removeMysqlZeroDate($sTableName, $oTableSchema, $oDB);
            // No token field in this table
            switch (Yii::app()->db->driverName) {
                case 'sqlsrv':
                case 'dblib':
                case 'mssql':
                    dropSecondaryKeyMSSQL('token', $sTableName);
                    alterColumn($sTableName, 'token', "string(36) COLLATE SQL_Latin1_General_CP1_CS_AS");
                    break;
                case 'mysql':
                    alterColumn($sTableName, 'token', "string(36) COLLATE '{$sMySQLCollation}'");
                    break;
                default:
                    die('Unknown database driver');
            }
        }
    }
}

/**
 * @param string $sMySQLCollation
 */
function upgradeTokenTables402($sMySQLCollation)
{
    $oDB = Yii::app()->db;
    if (Yii::app()->db->driverName != 'pgsql') {
        $aTables = dbGetTablesLike("tokens%");
        if (!empty($aTables)) {
            foreach ($aTables as $sTableName) {
                switch (Yii::app()->db->driverName) {
                    case 'sqlsrv':
                    case 'dblib':
                    case 'mssql':
                        dropSecondaryKeyMSSQL('token', $sTableName);
                        alterColumn($sTableName, 'token', "string(36) COLLATE SQL_Latin1_General_CP1_CS_AS");
                        break;
                    case 'mysql':
                        alterColumn($sTableName, 'token', "string(36) COLLATE '{$sMySQLCollation}'");
                        break;
                    default:
                        die('Unknown database driver');
                }
            }
        }
    }
}

function extendDatafields364($oDB)
{
    if (Yii::app()->db->driverName == 'mysql' || Yii::app()->db->driverName == 'mysqi') {
        alterColumn('{{answers}}', 'answer', "mediumtext", false);
        alterColumn('{{assessments}}', 'message', "mediumtext", false);
        alterColumn('{{groups}}', 'description', "mediumtext");
        alterColumn('{{notifications}}', 'message', "mediumtext", false);
        alterColumn('{{participant_attribute_values}}', 'value', "mediumtext", false);
        alterColumn('{{plugin_settings}}', 'value', "mediumtext");
        alterColumn('{{questions}}', 'question', "mediumtext", false);
        alterColumn('{{questions}}', 'help', "mediumtext");
        alterColumn('{{question_attributes}}', 'value', "mediumtext");
        alterColumn('{{quota_languagesettings}}', 'quotals_message', "mediumtext", false);
        alterColumn('{{settings_global}}', 'stg_value', "mediumtext", false);
        alterColumn('{{settings_user}}', 'stg_value', "mediumtext");
        alterColumn('{{surveymenu_entries}}', 'data', "mediumtext");
        alterColumn('{{surveys}}', 'attributedescriptions', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'surveyls_description', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'surveyls_welcometext', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'surveyls_endtext', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'surveyls_policy_notice', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'surveyls_email_invite', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'surveyls_email_remind', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'surveyls_email_register', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'surveyls_email_confirm', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'email_admin_notification', "mediumtext");
        alterColumn('{{surveys_languagesettings}}', 'email_admin_responses', "mediumtext");
        alterColumn('{{templates}}', 'license', "mediumtext");
        alterColumn('{{templates}}', 'description', "mediumtext");
        alterColumn('{{template_configuration}}', 'cssframework_css', "mediumtext");
        alterColumn('{{template_configuration}}', 'cssframework_js', "mediumtext");
        alterColumn('{{tutorials}}', 'settings', "mediumtext");
        alterColumn('{{tutorial_entries}}', 'content', "mediumtext");
        alterColumn('{{tutorial_entries}}', 'settings', "mediumtext");
    }
            alterColumn('{{surveys}}', 'additional_languages', "text");
}

function upgradeSurveyTimings350()
{
    $aTables = dbGetTablesLike("%timings");
    foreach ($aTables as $sTable) {
            alterColumn($sTable, 'id', "int", false);
    }
}



/**
 * @param CDbConnection $oDB
 *
 * @return void
 */
function resetTutorials337($oDB)
{
    $oDB->createCommand()->truncateTable('{{tutorials}}');
    $oDB->createCommand()->truncateTable('{{tutorial_entries}}');
    $oDB->createCommand()->truncateTable('{{tutorial_entry_relation}}');
}

/**
* @param CDbConnection $oDB
* @return void
*/
function upgrade333($oDB)
{
    $oDB->createCommand()->createTable('{{map_tutorial_users}}', array(
        'tid' => 'integer NOT NULL',
        'uid' => 'integer NOT NULL',
        'taken' => 'integer DEFAULT 1',
    ));

    $oDB->createCommand()->addPrimaryKey('{{map_tutorial_users_pk}}', '{{map_tutorial_users}}', ['uid', 'tid']);

    $oDB->createCommand()->createTable('{{tutorial_entry_relation}}', array(
        'teid' => 'integer NOT NULL',
        'tid' => 'integer NOT NULL',
        'uid' => 'integer DEFAULT NULL',
        'sid' => 'integer DEFAULT NULL',
    ));

    $oDB->createCommand()->addPrimaryKey('{{tutorial_entry_relation_pk}}', '{{tutorial_entry_relation}}', ['teid', 'tid']);
    $oDB->createCommand()->createIndex('{{idx1_tutorial_entry_relation}}', '{{tutorial_entry_relation}}', 'uid', false);
    $oDB->createCommand()->createIndex('{{idx2_tutorial_entry_relation}}', '{{tutorial_entry_relation}}', 'sid', false);
    $oDB->createCommand()->createIndex('{{idx1_tutorials}}', '{{tutorials}}', 'name', true);

    $oDB->createCommand()->dropColumn('{{tutorial_entries}}', 'tid');
    $oDB->createCommand()->addColumn('{{tutorial_entries}}', 'ordering', 'integer');
}

/**
* @param CDbConnection $oDB
* @return void
*/
function upgrade331($oDB)
{
    $oDB->createCommand()->update('{{templates}}', array(
        'name'        => 'bootswatch',
        'folder'      => 'bootswatch',
        'title'       => 'Bootswatch Theme',
        'description' => '<strong>LimeSurvey Bootwatch Theme</strong><br>Based on BootsWatch Themes: <a href=\'https://bootswatch.com/3/\'>Visit BootsWatch page</a>',
    ), "name='default'");

    $oDB->createCommand()->update('{{templates}}', array(
        'extends' => 'bootswatch',
    ), "extends='default'");

    $oDB->createCommand()->update('{{template_configuration}}', array(
            'template_name'   => 'bootswatch',
    ), "template_name='default'");

    $oDB->createCommand()->update('{{templates}}', array(
        'description' => '<strong>LimeSurvey Material Design Theme</strong><br> A theme based on FezVrasta\'s Material design for Bootstrap 3 <a href=\'https://cdn.rawgit.com/FezVrasta/bootstrap-material-design/gh-pages-v3/index.html\'></a>',
    ), "name='material'");

    $oDB->createCommand()->update('{{templates}}', array(
        'name'        => 'fruity',
        'folder'      => 'fruity',
        'title'       => 'Fruity Theme',
        'description' => '<strong>LimeSurvey Fruity Theme</strong><br>Some color themes for a flexible use. This theme offers many options.',
    ), "name='monochrome'");

    $oDB->createCommand()->update('{{templates}}', array(
        'extends' => 'fruity',
    ), "extends='monochrome'");

    $oDB->createCommand()->update('{{template_configuration}}', array(
            'template_name'   => 'fruity',
    ), "template_name='monochrome'");

    $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 'fruity'), "stg_name='defaulttheme'");
}

/**
* @param CDbConnection $oDB
* @return void
*/
function upgrade330($oDB)
{
    $oDB->createCommand()->update('{{template_configuration}}', array(
            'files_css'       => '{"add": ["css/animate.css","css/theme.css"]}',
            'files_js'        => '{"add": ["scripts/theme.js", "scripts/ajaxify.js"]}',
            'files_print_css' => '{"add":"css/print_theme.css"}',
    ), "template_name='default' AND  files_css != 'inherit' ");

    $oDB->createCommand()->update('{{template_configuration}}', array(
            'files_css'       => '{"add": ["css/bootstrap-material-design.css", "css/ripples.min.css", "css/theme.css"]}',
            'files_js'        => '{"add": ["scripts/theme.js", "scripts/material.js", "scripts/ripples.min.js", "scripts/ajaxify.js"]}',
            'files_print_css' => '{"add":"css/print_theme.css"}',
    ), "template_name='material' AND  files_css != 'inherit'");

    $oDB->createCommand()->update('{{template_configuration}}', array(
            'files_css'       => '{"add":["css/animate.css","css/ajaxify.css","css/sea_green.css", "css/theme.css"]}',
            'files_js'        => '{"add":["scripts/theme.js","scripts/ajaxify.js"]}',
            'files_print_css' => '{"add":"css/print_theme.css"}',
    ), "template_name='monochrome' AND  files_css != 'inherit'");

    $oDB->createCommand()->update('{{template_configuration}}', array(
            'files_css'         => '{"add":["css/ajaxify.css","css/theme.css","css/custom.css"]}',
            'files_js'          =>  '{"add":["scripts/theme.js","scripts/ajaxify.js","scripts/custom.js"]}',
            'files_print_css'   => '{"add":["css/print_theme.css"]}',
    ), "template_name='vanilla' AND  files_css != 'inherit'");
}

/**
* @param CDbConnection $oDB
* @return void
*/
function upgrade328($oDB)
{
    $oDB->createCommand()->update('{{templates}}', array(
            'description' =>  "<strong>LimeSurvey Advanced Theme</strong><br>A theme with custom options to show what it's possible to do with the new engines. Each theme provider will be able to offer its own option page (loaded from theme)",
    ), "name='default'");
}

/**
* @param CDbConnection $oDB
* @return void
*/
function upgrade327($oDB)
{
    // Update the box value so it uses to the the themeoptions controler
    $oDB->createCommand()->update('{{boxes}}', array(
        'position' =>  '6',
        'url'      =>  'admin/themeoptions',
        'title'    =>  'Themes',
        'ico'      =>  'templates',
        'desc'     =>  'Edit LimeSurvey Themes',
        'page'     =>  'welcome',
        'usergroup' => '-2',
    ), "url='admin/templateoptions'");
}

/**
 * @param CDbConnection $oDB
 */
function transferPasswordFieldToText($oDB)
{
    switch ($oDB->getDriverName()) {
        case 'mysql':
            $oDB->createCommand()->alterColumn('{{users}}', 'password', 'text NOT NULL');
            break;
        case 'pgsql':
            $userPasswords = $oDB->createCommand()->select(['uid', "encode(password::bytea, 'escape') as password"])->from('{{users}}')->queryAll();

            $oDB->createCommand()->renameColumn('{{users}}', 'password', 'password_blob');
            $oDB->createCommand()->addColumn('{{users}}', 'password', "text NOT NULL DEFAULT 'nopw'");

            foreach ($userPasswords as $userArray) {
                $oDB->createCommand()->update('{{users}}', ['password' => $userArray['password']], 'uid=:uid', [':uid' => $userArray['uid']]);
            }

            $oDB->createCommand()->dropColumn('{{users}}', 'password_blob');
            break;
        case 'sqlsrv':
        case 'dblib':
        case 'mssql':
        default:
            break;
    }
}

/**
* @param CDbConnection $oDB
* @return void
*/
function createSurveyMenuTable(CDbConnection $oDB)
{
    // NB: Need to refresh here, since surveymenu table is
    // created in earlier version in same script.
    $oDB->schema->getTables();
    $oDB->schema->refresh();

    // Drop the old surveymenu_entries table.
    if (tableExists('{surveymenu_entries}')) {
        $oDB->createCommand()->dropTable('{{surveymenu_entries}}');
    }

    // Drop the old surveymenu table.
    if (tableExists('{surveymenu}')) {
        $oDB->createCommand()->dropTable('{{surveymenu}}');
    }

    $oDB->createCommand()->createTable('{{surveymenu}}', array(
        'id' => "pk",
        'parent_id' => "integer NULL",
        'survey_id' => "integer NULL",
        'user_id' => "integer NULL",
        'name' => "string(128)",
        'ordering' => "integer NULL DEFAULT '0'",
        'level' => "integer NULL DEFAULT '0'",
        'title' => "string(168)  NOT NULL DEFAULT ''",
        'position' => "string(192)  NOT NULL DEFAULT 'side'",
        'description' => "text ",
        'active' => "integer NOT NULL DEFAULT '0'",
        'showincollapse' =>  "integer DEFAULT 0",
        'changed_at' => "datetime",
        'changed_by' => "integer NOT NULL DEFAULT '0'",
        'created_at' => "datetime",
        'created_by' => "integer NOT NULL DEFAULT '0'",
    ));

    $oDB->createCommand()->createIndex('{{surveymenu_name}}', '{{surveymenu}}', 'name', true);
    $oDB->createCommand()->createIndex('{{idx2_surveymenu}}', '{{surveymenu}}', 'title', false);

    $surveyMenuRowData = LsDefaultDataSets::getSurveyMenuData();
    switchMSSQLIdentityInsert('surveymenu', true);
    foreach ($surveyMenuRowData as $surveyMenuRow) {
        $oDB->createCommand()->insert("{{surveymenu}}", $surveyMenuRow);
    }
    switchMSSQLIdentityInsert('surveymenu', false);

    $oDB->createCommand()->createTable('{{surveymenu_entries}}', array(
        'id' =>  "pk",
        'menu_id' =>  "integer NULL",
        'user_id' =>  "integer NULL",
        'ordering' =>  "integer DEFAULT '0'",
        'name' =>  "string(168)  DEFAULT ''",
        'title' =>  "string(168)  NOT NULL DEFAULT ''",
        'menu_title' =>  "string(168)  NOT NULL DEFAULT ''",
        'menu_description' =>  "text ",
        'menu_icon' =>  "string(192)  NOT NULL DEFAULT ''",
        'menu_icon_type' =>  "string(192)  NOT NULL DEFAULT ''",
        'menu_class' =>  "string(192)  NOT NULL DEFAULT ''",
        'menu_link' =>  "string(192)  NOT NULL DEFAULT ''",
        'action' =>  "string(192)  NOT NULL DEFAULT ''",
        'template' =>  "string(192)  NOT NULL DEFAULT ''",
        'partial' =>  "string(192)  NOT NULL DEFAULT ''",
        'classes' =>  "string(192)  NOT NULL DEFAULT ''",
        'permission' =>  "string(192)  NOT NULL DEFAULT ''",
        'permission_grade' =>  "string(192)  NULL",
        'data' =>  "text ",
        'getdatamethod' =>  "string(192)  NOT NULL DEFAULT ''",
        'language' =>  "string(32)  NOT NULL DEFAULT 'en-GB'",
        'active' =>  "integer NOT NULL DEFAULT '0'",
        'showincollapse' =>  "integer DEFAULT 0",
        'changed_at' =>  "datetime NULL",
        'changed_by' =>  "integer NOT NULL DEFAULT '0'",
        'created_at' =>  "datetime NULL",
        'created_by' =>  "integer NOT NULL DEFAULT '0'",
    ));

    $oDB->createCommand()->createIndex('{{idx1_surveymenu_entries}}', '{{surveymenu_entries}}', 'menu_id', false);
    $oDB->createCommand()->createIndex('{{idx5_surveymenu_entries}}', '{{surveymenu_entries}}', 'menu_title', false);
    $oDB->createCommand()->createIndex('{{surveymenu_entries_name}}', '{{surveymenu_entries}}', 'name', true);

    foreach ($surveyMenuEntryRowData = LsDefaultDataSets::getSurveyMenuEntryData() as $surveyMenuEntryRow) {
        $oDB->createCommand()->insert("{{surveymenu_entries}}", $surveyMenuEntryRow);
    }
}

/**
* @param CDbConnection $oDB
* @return void
*/
function createSurveysGroupSettingsTable(CDbConnection $oDB)
{
    // Drop the old surveys_groupsettings table.
    if (tableExists('{surveys_groupsettings}')) {
        $oDB->createCommand()->dropTable('{{surveys_groupsettings}}');
    }

    // create surveys_groupsettings table
    $oDB->createCommand()->createTable('{{surveys_groupsettings}}', array(
        'gsid' => "integer NOT NULL",
        'owner_id' => "integer NULL DEFAULT NULL",
        'admin' => "string(50) NULL DEFAULT NULL",
        'adminemail' => "string(254) NULL DEFAULT NULL",
        'anonymized' => "string(1) NOT NULL DEFAULT 'N'",
        'format' => "string(1) NULL DEFAULT NULL",
        'savetimings' => "string(1) NOT NULL DEFAULT 'N'",
        'template' => "string(100) NULL DEFAULT 'default'",
        'datestamp' => "string(1) NOT NULL DEFAULT 'N'",
        'usecookie' => "string(1) NOT NULL DEFAULT 'N'",
        'allowregister' => "string(1) NOT NULL DEFAULT 'N'",
        'allowsave' => "string(1) NOT NULL DEFAULT 'Y'",
        'autonumber_start' => "integer NULL DEFAULT '0'",
        'autoredirect' => "string(1) NOT NULL DEFAULT 'N'",
        'allowprev' => "string(1) NOT NULL DEFAULT 'N'",
        'printanswers' => "string(1) NOT NULL DEFAULT 'N'",
        'ipaddr' => "string(1) NOT NULL DEFAULT 'N'",
        'refurl' => "string(1) NOT NULL DEFAULT 'N'",
        'showsurveypolicynotice' => "integer NULL DEFAULT '0'",
        'publicstatistics' => "string(1) NOT NULL DEFAULT 'N'",
        'publicgraphs' => "string(1) NOT NULL DEFAULT 'N'",
        'listpublic' => "string(1) NOT NULL DEFAULT 'N'",
        'htmlemail' => "string(1) NOT NULL DEFAULT 'N'",
        'sendconfirmation' => "string(1) NOT NULL DEFAULT 'Y'",
        'tokenanswerspersistence' => "string(1) NOT NULL DEFAULT 'N'",
        'assessments' => "string(1) NOT NULL DEFAULT 'N'",
        'usecaptcha' => "string(1) NOT NULL DEFAULT 'N'",
        'bounce_email' => "string(254) NULL DEFAULT NULL",
        'attributedescriptions' => "text NULL",
        'emailresponseto' => "text NULL",
        'emailnotificationto' => "text NULL",
        'tokenlength' => "integer NULL DEFAULT '15'",
        'showxquestions' => "string(1) NULL DEFAULT 'Y'",
        'showgroupinfo' => "string(1) NULL DEFAULT 'B'",
        'shownoanswer' => "string(1) NULL DEFAULT 'Y'",
        'showqnumcode' => "string(1) NULL DEFAULT 'X'",
        'showwelcome' => "string(1) NULL DEFAULT 'Y'",
        'showprogress' => "string(1) NULL DEFAULT 'Y'",
        'questionindex' => "integer NULL DEFAULT '0'",
        'navigationdelay' => "integer NULL DEFAULT '0'",
        'nokeyboard' => "string(1) NULL DEFAULT 'N'",
        'alloweditaftercompletion' => "string(1) NULL DEFAULT 'N'",
        'othersettings' => "mediumtext NULL"
    ));
    addPrimaryKey('surveys_groupsettings', array('gsid'));

    // insert settings for global level
    $settings1 = new SurveysGroupsettings();
    $settings1->setToDefault();
    $settings1->gsid = 0;
    // get global settings from db
    $globalSetting1 = $oDB->createCommand()->select('stg_value')->from('{{settings_global}}')->where("stg_name=:stg_name", array('stg_name' => 'showqnumcode'))->queryRow();
    $globalSetting2 = $oDB->createCommand()->select('stg_value')->from('{{settings_global}}')->where("stg_name=:stg_name", array('stg_name' => 'showgroupinfo'))->queryRow();
    $globalSetting3 = $oDB->createCommand()->select('stg_value')->from('{{settings_global}}')->where("stg_name=:stg_name", array('stg_name' => 'shownoanswer'))->queryRow();
    $globalSetting4 = $oDB->createCommand()->select('stg_value')->from('{{settings_global}}')->where("stg_name=:stg_name", array('stg_name' => 'showxquestions'))->queryRow();
    // set db values to model
    $settings1->showqnumcode = ($globalSetting1 === false || $globalSetting1['stg_value'] == 'choose') ? 'X' : str_replace(array('both', 'number', 'code', 'none'), array('B', 'N', 'C', 'X'), (string) $globalSetting1['stg_value']);
    $settings1->showgroupinfo = ($globalSetting2 === false || $globalSetting2['stg_value'] == 'choose') ? 'B' : str_replace(array('both', 'name', 'description', 'none'), array('B', 'N', 'D', 'X'), (string) $globalSetting2['stg_value']);
    $settings1->shownoanswer = ($globalSetting3 === false || $globalSetting3['stg_value'] == '2') ? 'Y' : str_replace(array('1', '0'), array('Y', 'N'), (string) $globalSetting3['stg_value']);
    $settings1->showxquestions = ($globalSetting4 === false || $globalSetting4['stg_value'] == 'choose') ? 'Y' : str_replace(array('show', 'hide'), array('Y', 'N'), (string) $globalSetting4['stg_value']);

    // Quick hack to remote ipanonymize.
    // TODO: Don't use models in updatedb_helper.
    $attributes = $settings1->attributes;
    unset($attributes['ipanonymize']);

    $oDB->createCommand()->insert("{{surveys_groupsettings}}", $attributes);

    //this will fail because of using model in updatedb_helper ...
    // insert settings for default survey group
    //$settings2 = new SurveysGroupsettings;
    //$settings2->gsid = 1;
    //$settings2->setToInherit(); //we can not use this function because of ipanonymize (again: never use models in update_helper)

    $attributes2 =  array(
        "gsid" => 1,
        "owner_id" => -1,
        "admin" => "inherit",
        "adminemail" => "inherit",
        "anonymized" => "I",
        "format" => "I",
        "savetimings" => "I",
        "template" => "inherit",
        "datestamp" => "I",
        "usecookie" => "I",
        "allowregister" => "I",
        "allowsave" => "I",
        "autonumber_start" => 0,
        "autoredirect" => "I",
        "allowprev" => "I",
        "printanswers" => "I",
        "ipaddr" => "I",
        "refurl" => "I",
        "showsurveypolicynotice" => 0,
        "publicstatistics" => "I",
        "publicgraphs" => "I",
        "listpublic" => "I",
        "htmlemail" => "I",
        "sendconfirmation" => "I",
        "tokenanswerspersistence" => "I",
        "assessments" => "I",
        "usecaptcha" => "E",
        "bounce_email" => "inherit",
        "attributedescriptions" => null,
        "emailresponseto" => "inherit",
        "emailnotificationto" => "inherit",
        "tokenlength" => -1,
        "showxquestions" => "I",
        "showgroupinfo" => "I",
        "shownoanswer" => "I",
        "showqnumcode" => "I",
        "showwelcome" => "I",
        "showprogress" => "I",
        "questionindex" => -1,
        "navigationdelay" => -1,
        "nokeyboard" => "I",
        "alloweditaftercompletion" => "I",
    );

    $oDB->createCommand()->insert("{{surveys_groupsettings}}", $attributes2);
}
/**
* @param CDbConnection $oDB
* @return void
*/
function createSurveyGroupTables306($oDB)
{
    // Drop the old survey groups table.
    if (tableExists('{surveys_groups}')) {
        $oDB->createCommand()->dropTable('{{surveys_groups}}');
    }


    // Create templates table
    $oDB->createCommand()->createTable('{{surveys_groups}}', array(
        'gsid'        => 'pk',
        'name'        => 'string(45) NOT NULL',
        'title'       => 'string(100) DEFAULT NULL',
        'description' => 'text DEFAULT NULL',
        'sortorder'   => 'integer NOT NULL',
        'owner_uid'   => 'integer DEFAULT NULL',
        'parent_id'   => 'integer DEFAULT NULL',
        'created'     => 'datetime',
        'modified'    => 'datetime',
        'created_by'  => 'integer NOT NULL'
    ));

    // Add default template
    $date = date("Y-m-d H:i:s");
    $oDB->createCommand()->insert('{{surveys_groups}}', array(
        'name'        => 'default',
        'title'       => 'Default Survey Group',
        'description' => 'LimeSurvey core default survey group',
        'sortorder'   => '0',
        'owner_uid'   => '1',
        'created'     => $date,
        'modified'    => $date,
        'created_by'  => '1'
    ));

    $oDB->createCommand()->addColumn('{{surveys}}', 'gsid', "integer DEFAULT 1");
}



/**
* @param CDbConnection $oDB
* @return void
*/
function upgradeTemplateTables304($oDB)
{
    // Drop the old survey rights table.
    if (tableExists('{{templates}}')) {
        $oDB->createCommand()->dropTable('{{templates}}');
    }

    if (tableExists('{{template_configuration}}')) {
        $oDB->createCommand()->dropTable('{{template_configuration}}');
    }

    // Create templates table
    $oDB->createCommand()->createTable('{{templates}}', array(
        'name'                   => 'string(150) NOT NULL',
        'folder'                 => 'string(45) DEFAULT NULL',
        'title'                  => 'string(100) NOT NULL',
        'creation_date'          => 'datetime',
        'author'                 => 'string(150) DEFAULT NULL',
        'author_email'           => 'string DEFAULT NULL',
        'author_url'             => 'string DEFAULT NULL',
        'copyright'              => 'text',
        'license'                => 'text',
        'version'                => 'string(45) DEFAULT NULL',
        'api_version'            => 'string(45) NOT NULL',
        'view_folder'            => 'string(45) NOT NULL',
        'files_folder'           => 'string(45) NOT NULL',
        'description'            => 'text',
        'last_update'            => 'datetime DEFAULT NULL',
        'owner_id'               => 'integer DEFAULT NULL',
        'extends_template_name' => 'string(150) DEFAULT NULL',
        'PRIMARY KEY (name)'
    ));

    // Add default template
    $oDB->createCommand()->insert('{{templates}}', array(
        'name'                   => 'default',
        'folder'                 => 'default',
        'title'                  => 'Advanced Template',
        'creation_date'          => '2017-07-12 12:00:00',
        'author'                 => 'LimeSurvey GmbH',
        'author_email'           => 'info@limesurvey.org',
        'author_url'             => 'https://www.limesurvey.org/',
        'copyright'              => 'Copyright (C) 2007-2017 The LimeSurvey Project Team\r\nAll rights reserved.',
        'license'                => 'License: GNU/GPL License v2 or later, see LICENSE.php\r\n\r\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.',
        'version'                => '1.0',
        'api_version'            => '3.0',
        'view_folder'            => 'views',
        'files_folder'           => 'files',
        'description'            => "<strong>LimeSurvey Advanced Template</strong><br>A template with custom options to show what it's possible to do with the new engines. Each template provider will be able to offer its own option page (loaded from template)",
        'owner_id'               => '1',
        'extends_template_name' => '',
    ));

    // Add minimal template
    $oDB->createCommand()->insert('{{templates}}', array(
        'name'                   => 'minimal',
        'folder'                 => 'minimal',
        'title'                  => 'Minimal Template',
        'creation_date'          => '2017-07-12 12:00:00',
        'author'                 => 'LimeSurvey GmbH',
        'author_email'           => 'info@limesurvey.org',
        'author_url'             => 'https://www.limesurvey.org/',
        'copyright'              => 'Copyright (C) 2007-2017 The LimeSurvey Project Team\r\nAll rights reserved.',
        'license'                => 'License: GNU/GPL License v2 or later, see LICENSE.php\r\n\r\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.',
        'version'                => '1.0',
        'api_version'            => '3.0',
        'view_folder'            => 'views',
        'files_folder'           => 'files',
        'description'            => '<strong>LimeSurvey Minimal Template</strong><br>A clean and simple base that can be used by developers to create their own solution.',
        'owner_id'               => '1',
        'extends_template_name' => '',
    ));



    // Add material template
    $oDB->createCommand()->insert('{{templates}}', array(
        'name'                   => 'material',
        'folder'                 => 'material',
        'title'                  => 'Material Template',
        'creation_date'          => '2017-07-12 12:00:00',
        'author'                 => 'LimeSurvey GmbH',
        'author_email'           => 'info@limesurvey.org',
        'author_url'             => 'https://www.limesurvey.org/',
        'copyright'              => 'Copyright (C) 2007-2017 The LimeSurvey Project Team\r\nAll rights reserved.',
        'license'                => 'License: GNU/GPL License v2 or later, see LICENSE.php\r\n\r\nLimeSurvey is free software. This version may have been modified pursuant to the GNU General Public License, and as distributed it includes or is derivative of works licensed under the GNU General Public License or other free or open source software licenses. See COPYRIGHT.php for copyright notices and details.',
        'version'                => '1.0',
        'api_version'            => '3.0',
        'view_folder'            => 'views',
        'files_folder'           => 'files',
        'description'            => "<strong>LimeSurvey Advanced Template</strong><br> A template extending default, to show the inheritance concept. Notice the options, differing from Default.<br><small>uses FezVrasta's Material design theme for Bootstrap 3</small>",
        'owner_id'               => '1',
        'extends_template_name' => 'default',
    ));


    // Add template configuration table
    $oDB->createCommand()->createTable('{{template_configuration}}', array(
        'id'                => 'pk',
        'templates_name'    => 'string(150) NOT NULL',
        'sid'               => 'integer DEFAULT NULL',
        'gsid'              => 'integer DEFAULT NULL',
        'uid'               => 'integer DEFAULT NULL',
        'files_css'         => 'text',
        'files_js'          => 'text',
        'files_print_css'   => 'text',
        'options'           => 'text',
        'cssframework_name' => 'string(45) DEFAULT NULL',
        'cssframework_css'  => 'text',
        'cssframework_js'   => 'text',
        'packages_to_load'  => 'text',
    ));

    // Add global configuration for Advanced Template
    $oDB->createCommand()->insert('{{template_configuration}}', array(
        'templates_name'    => 'default',
        'files_css'         => '{"add": ["css/template.css", "css/animate.css"]}',
        'files_js'          => '{"add": ["scripts/template.js"]}',
        'files_print_css'   => '{"add":"css/print_template.css"}',
        'options'           => '{"ajaxmode":"off","brandlogo":"on", "brandlogofile":"./files/logo.png", "boxcontainer":"on", "backgroundimage":"off","animatebody":"off","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}',
        'cssframework_name' => 'bootstrap',
        'cssframework_css'  => '{"replace": [["css/bootstrap.css","css/flatly.css"]]}',
        'cssframework_js'   => '',
        'packages_to_load'  => '["pjax"]',
    ));


    // Add global configuration for Minimal Template
    $oDB->createCommand()->insert('{{template_configuration}}', array(
        'templates_name'    => 'minimal',
        'files_css'         => '{"add": ["css/template.css"]}',
        'files_js'          => '{"add": ["scripts/template.js"]}',
        'files_print_css'   => '{"add":"css/print_template.css"}',
        'options'           => '{}',
        'cssframework_name' => 'bootstrap',
        'cssframework_css'  => '{}',
        'cssframework_js'   => '',
        'packages_to_load'  => '["pjax"]',
    ));

    // Add global configuration for Material Template
    $oDB->createCommand()->insert('{{template_configuration}}', array(
        'templates_name'    => 'material',
        'files_css'         => '{"add": ["css/template.css", "css/bootstrap-material-design.css", "css/ripples.min.css"]}',
        'files_js'          => '{"add": ["scripts/template.js", "scripts/material.js", "scripts/ripples.min.js"]}',
        'files_print_css'   => '{"add":"css/print_template.css"}',
        'options'           => '{"ajaxmode":"off","brandlogo":"on", "brandlogofile":"./files/logo.png", "animatebody":"off","bodyanimation":"fadeInRight","animatequestion":"off","questionanimation":"flipInX","animatealert":"off","alertanimation":"shake"}',
        'cssframework_name' => 'bootstrap',
        'cssframework_css'  => '{"replace": [["css/bootstrap.css","css/bootstrap.css"]]}',
        'cssframework_js'   => '',
        'packages_to_load'  => '["pjax"]',
    ));
}


/**
* @param CDbConnection $oDB
* @return void
*/
function upgradeTemplateTables298($oDB)
{
    // Add global configuration for Advanced Template
    $oDB->createCommand()->update('{{boxes}}', array(
        'url' => 'admin/templateoptions',
        'title' => 'Templates',
        'desc' => 'View templates list',
        ), "id=6");
}

function upgradeTokenTables256()
{
    $aTableNames = dbGetTablesLike("tokens%");
    $oDB = Yii::app()->getDb();
    foreach ($aTableNames as $sTableName) {
        try {
            setTransactionBookmark();
            $oDB->createCommand()->dropIndex("idx_lime_{$sTableName}_efl", $sTableName);
        } catch (Exception $e) {
            rollBackToTransactionBookmark();
        }
        alterColumn($sTableName, 'email', "text");
        alterColumn($sTableName, 'firstname', "string(150)");
        alterColumn($sTableName, 'lastname', "string(150)");
    }
}


function upgradeSurveyTables255()
{
    // We delete all the old boxes, and reinsert new ones
    Yii::app()->getDb()->createCommand(
        "DELETE FROM {{boxes}}"
    )->execute();

    // Then we recreate them
    $oDB = Yii::app()->db;
    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '1',
        'url'      => 'admin/survey/sa/newsurvey',
        'title'    => 'Create survey',
        'ico'      => 'add',
        'desc'     => 'Create a new survey',
        'page'     => 'welcome',
        'usergroup' => '-2',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '2',
        'url'      =>  'admin/survey/sa/listsurveys',
        'title'    =>  'List surveys',
        'ico'      =>  'list',
        'desc'     =>  'List available surveys',
        'page'     =>  'welcome',
        'usergroup' => '-1',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '3',
        'url'      =>  'admin/globalsettings',
        'title'    =>  'Global settings',
        'ico'      =>  'global',
        'desc'     =>  'Edit global settings',
        'page'     =>  'welcome',
        'usergroup' => '-2',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '4',
        'url'      =>  'admin/update',
        'title'    =>  'ComfortUpdate',
        'ico'      =>  'shield',
        'desc'     =>  'Stay safe and up to date',
        'page'     =>  'welcome',
        'usergroup' => '-2',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '5',
        'url'      =>  'admin/labels/sa/view',
        'title'    =>  'Label sets',
        'ico'      =>  'labels',
        'desc'     =>  'Edit label sets',
        'page'     =>  'welcome',
        'usergroup' => '-2',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '6',
        'url'      =>  'admin/themes/sa/view',
        'title'    =>  'Template editor',
        'ico'      =>  'templates',
        'desc'     =>  'Edit LimeSurvey templates',
        'page'     =>  'welcome',
        'usergroup' => '-2',
    ));
}

function upgradeSurveyTables254()
{
    Yii::app()->db->createCommand()->dropColumn('{{boxes}}', 'img');
    Yii::app()->db->createCommand()->addColumn('{{boxes}}', 'usergroup', 'integer');
}

function upgradeSurveyTables253()
{
    $oSchema = Yii::app()->db->schema;
    $aTables = dbGetTablesLike("survey\_%");
    $oDB = Yii::app()->db;
    foreach ($aTables as $sTable) {
        $oTableSchema = $oSchema->getTable($sTable);
        removeMysqlZeroDate($sTable, $oTableSchema, $oDB);
        if (in_array('refurl', $oTableSchema->columnNames)) {
            alterColumn($sTable, 'refurl', "text");
        }
        if (in_array('ipaddr', $oTableSchema->columnNames)) {
            alterColumn($sTable, 'ipaddr', "text");
        }
    }
}


function upgradeBoxesTable251()
{
    Yii::app()->db->createCommand()->addColumn('{{boxes}}', 'ico', 'string');
    Yii::app()->db->createCommand()->update(
        '{{boxes}}',
        array('ico' => 'add',
        'title' => 'Create survey'),
        "id=1"
    );
    Yii::app()->db->createCommand()->update(
        '{{boxes}}',
        array('ico' => 'list'),
        "id=2"
    );
    Yii::app()->db->createCommand()->update(
        '{{boxes}}',
        array('ico' => 'settings'),
        "id=3"
    );
    Yii::app()->db->createCommand()->update(
        '{{boxes}}',
        array('ico' => 'shield'),
        "id=4"
    );
    Yii::app()->db->createCommand()->update(
        '{{boxes}}',
        array('ico' => 'label'),
        "id=5"
    );
    Yii::app()->db->createCommand()->update(
        '{{boxes}}',
        array('ico' => 'templates'),
        "id=6"
    );
}

/**
* Create boxes table
*/
function createBoxes250()
{
    $oDB = Yii::app()->db;
    $oDB->createCommand()->createTable('{{boxes}}', array(
        'id' => 'pk',
        'position' => 'integer',
        'url' => 'text',
        'title' => 'text',
        'img' => 'text',
        'desc' => 'text',
        'page' => 'text',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '1',
        'url'      => 'admin/survey/sa/newsurvey',
        'title'    => 'Create survey',
        'img'      => 'add.png',
        'desc'     => 'Create a new survey',
        'page'     => 'welcome',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '2',
        'url'      =>  'admin/survey/sa/listsurveys',
        'title'    =>  'List surveys',
        'img'      =>  'surveylist.png',
        'desc'     =>  'List available surveys',
        'page'     =>  'welcome',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '3',
        'url'      =>  'admin/globalsettings',
        'title'    =>  'Global settings',
        'img'      =>  'global.png',
        'desc'     =>  'Edit global settings',
        'page'     =>  'welcome',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '4',
        'url'      =>  'admin/update',
        'title'    =>  'ComfortUpdate',
        'img'      =>  'shield&#45;update.png',
        'desc'     =>  'Stay safe and up to date',
        'page'     =>  'welcome',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '5',
        'url'      =>  'admin/labels/sa/view',
        'title'    =>  'Label sets',
        'img'      =>  'labels.png',
        'desc'     =>  'Edit label sets',
        'page'     =>  'welcome',
    ));

    $oDB->createCommand()->insert('{{boxes}}', array(
        'position' =>  '6',
        'url'      =>  'admin/themes/sa/view',
        'title'    =>  'Template editor',
        'img'      =>  'templates.png',
        'desc'     =>  'Edit LimeSurvey templates',
        'page'     =>  'welcome',
    ));
}

function fixKCFinder184()
{
    $sThirdPartyDir = Yii::app()->getConfig('homedir') . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR;
    rmdirr($sThirdPartyDir . 'ckeditor/plugins/toolbar');
    rmdirr($sThirdPartyDir . 'ckeditor/plugins/toolbar/ls-office2003');
    $aUnlink = glob($sThirdPartyDir . 'kcfinder/cache/*.js');
    if ($aUnlink !== false) {
        array_map('unlink', $aUnlink);
    }
    $aUnlink = glob($sThirdPartyDir . 'kcfinder/cache/*.css');
    if ($aUnlink !== false) {
        array_map('unlink', $aUnlink);
    }
    rmdirr($sThirdPartyDir . 'kcfinder/upload/files');
    rmdirr($sThirdPartyDir . 'kcfinder/upload/.thumbs');
}

function upgradeSurveyTables183()
{
    $oSchema = Yii::app()->db->schema;
    $aTables = dbGetTablesLike("survey\_%");
    $oDB = Yii::app()->db;
    if (!empty($aTables)) {
        foreach ($aTables as $sTableName) {
            $oTableSchema = $oSchema->getTable($sTableName);
            removeMysqlZeroDate($sTableName, $oTableSchema, $oDB);
            if (empty($oTableSchema->primaryKey)) {
                addPrimaryKey(substr((string) $sTableName, strlen((string) Yii::app()->getDb()->tablePrefix)), 'id');
            }
        }
    }
}

/**
* @param string $sMySQLCollation
*/
function upgradeSurveyTables181($sMySQLCollation)
{
    $oDB = Yii::app()->db;
    $oSchema = Yii::app()->db->schema;
    if (Yii::app()->db->driverName != 'pgsql') {
        $aTables = dbGetTablesLike("survey\_%");
        foreach ($aTables as $sTableName) {
            $oTableSchema = $oSchema->getTable($sTableName);
            removeMysqlZeroDate($sTableName, $oTableSchema, $oDB);
            if (!in_array('token', $oTableSchema->columnNames)) {
                continue;
            }
            // No token field in this table
            switch (Yii::app()->db->driverName) {
                case 'sqlsrv':
                case 'dblib':
                case 'mssql':
                    dropSecondaryKeyMSSQL('token', $sTableName);
                    alterColumn($sTableName, 'token', "string(35) COLLATE SQL_Latin1_General_CP1_CS_AS");
                    $oDB->createCommand()->createIndex("{{idx_{$sTableName}_" . rand(1, 40000) . '}}', $sTableName, 'token');
                    break;
                case 'mysql':
                case 'mysqli':
                    // Fixes 0000-00-00 00:00:00 datetime entries
                    // Startdate and datestamp field only existed in versions older that 1.90 if Datestamps were activated
                    try {
                        setTransactionBookmark();
                        $oDB->createCommand()->update($sTableName, array('startdate' => '1980-01-01 00:00:00'), "startdate=0");
                    } catch (Exception $e) {
                        rollBackToTransactionBookmark();
                    }
                    try {
                        setTransactionBookmark();
                        $oDB->createCommand()->update($sTableName, array('datestamp' => '1980-01-01 00:00:00'), "datestamp=0");
                    } catch (Exception $e) {
                        rollBackToTransactionBookmark();
                    }
                    alterColumn($sTableName, 'token', "string(35) COLLATE '{$sMySQLCollation}'");
                    break;
                default:
                    die('Unknown database driver');
            }
        }
    }
}

/**
* @param string $sMySQLCollation
*/
function upgradeTokenTables181($sMySQLCollation)
{
    $oDB = Yii::app()->db;
    if (Yii::app()->db->driverName != 'pgsql') {
        $aTables = dbGetTablesLike("tokens%");
        if (!empty($aTables)) {
            foreach ($aTables as $sTableName) {
                switch (Yii::app()->db->driverName) {
                    case 'sqlsrv':
                    case 'dblib':
                    case 'mssql':
                        dropSecondaryKeyMSSQL('token', $sTableName);
                        alterColumn($sTableName, 'token', "string(35) COLLATE SQL_Latin1_General_CP1_CS_AS");
                        $oDB->createCommand()->createIndex("{{idx_{$sTableName}_" . rand(1, 50000) . '}}', $sTableName, 'token');
                        break;
                    case 'mysql':
                        alterColumn($sTableName, 'token', "string(35) COLLATE '{$sMySQLCollation}'");
                        break;
                    default:
                        die('Unknown database driver');
                }
            }
        }
    }
}

function upgradeTokenTables179()
{
    $oDB = Yii::app()->db;
    $oSchema = Yii::app()->db->schema;
    switch (Yii::app()->db->driverName) {
        case 'pgsql':
            $sSubstringCommand = 'substr';
            break;
        default:
            $sSubstringCommand = 'substring';
    }
    $surveyidresult = dbGetTablesLike("tokens%");
    if ($surveyidresult) {
        foreach ($surveyidresult as $sTableName) {
            $oTableSchema = $oSchema->getTable($sTableName);
            foreach ($oTableSchema->columnNames as $sColumnName) {
                if (strpos((string) $sColumnName, 'attribute_') === 0) {
                    alterColumn($sTableName, $sColumnName, "text");
                }
            }
            $oDB->createCommand("UPDATE {$sTableName} set email={$sSubstringCommand}(email,1,254)")->execute();
            try {
                setTransactionBookmark();
                $oDB->createCommand()->dropIndex("idx_{$sTableName}_efl", $sTableName);
            } catch (Exception $e) {
                rollBackToTransactionBookmark();
            }
            try {
                setTransactionBookmark();
                alterColumn($sTableName, 'email', "string(254)");
            } catch (Exception $e) {
                rollBackToTransactionBookmark();
            }
            try {
                setTransactionBookmark();
                alterColumn($sTableName, 'firstname', "string(150)");
            } catch (Exception $e) {
                rollBackToTransactionBookmark();
            }
            try {
                setTransactionBookmark();
                alterColumn($sTableName, 'lastname', "string(150)");
            } catch (Exception $e) {
                rollBackToTransactionBookmark();
            }
        }
    }
}


function upgradeSurveys177()
{
    $oDB = Yii::app()->db;
    $sSurveyQuery = "SELECT surveyls_attributecaptions,surveyls_survey_id,surveyls_language FROM {{surveys_languagesettings}}";
    $oSurveyResult = $oDB->createCommand($sSurveyQuery)->queryAll();
    $sSurveyLSUpdateQuery = "update {{surveys_languagesettings}} set surveyls_attributecaptions=:attributecaptions where surveyls_survey_id=:surveyid and surveyls_language=:language";
    foreach ($oSurveyResult as $aSurveyRow) {
        $aAttributeDescriptions = decodeTokenAttributes($aSurveyRow['surveyls_attributecaptions'] ?? '');
        if (!$aAttributeDescriptions) {
            $aAttributeDescriptions = array();
        }
        $oDB->createCommand($sSurveyLSUpdateQuery)->execute(
            array(':language' => $aSurveyRow['surveyls_language'],
                ':surveyid' => $aSurveyRow['surveyls_survey_id'],
            ':attributecaptions' => json_encode($aAttributeDescriptions))
        );
    }
    $sSurveyQuery = "SELECT sid,attributedescriptions FROM {{surveys}}";
    $oSurveyResult = $oDB->createCommand($sSurveyQuery)->queryAll();
    $sSurveyUpdateQuery = "update {{surveys}} set attributedescriptions=:attributedescriptions where sid=:surveyid";
    foreach ($oSurveyResult as $aSurveyRow) {
        $aAttributeDescriptions = decodeTokenAttributes($aSurveyRow['attributedescriptions'] ?? '');
        if (!$aAttributeDescriptions) {
            $aAttributeDescriptions = array();
        }
        $oDB->createCommand($sSurveyUpdateQuery)->execute(array(':attributedescriptions' => json_encode($aAttributeDescriptions),':surveyid' => $aSurveyRow['sid']));
    }
}



/**
* This function removes the old CPDB fields in token tables
* replaces them with standard attribute fields
* and records the mapping information in the attributedescription field in the survey table instead
*/
function upgradeTokens176()
{
    $oDB = Yii::app()->db;
    $arSurveys = $oDB
    ->createCommand()
    ->select('*')
    ->from('{{surveys}}')
    ->queryAll();
    // Fix any active token tables
    foreach ($arSurveys as $arSurvey) {
        $sTokenTableName = 'tokens_' . $arSurvey['sid'];
        if (tableExists($sTokenTableName)) {
            $aColumnNames = $aColumnNamesIterator = $oDB->schema->getTable('{{' . $sTokenTableName . '}}')->columnNames;
            $aAttributes = $arSurvey['attributedescriptions'];
            foreach ($aColumnNamesIterator as $sColumnName) {
                // Check if an old atttribute_cpdb column exists in that token table
                if (strpos((string) $sColumnName, 'attribute_cpdb') !== false) {
                    $i = 1;
                    // Look for a an attribute ID that is available
                    while (in_array('attribute_' . $i, $aColumnNames)) {
                        $i++;
                    }
                    $sNewName = 'attribute_' . $i;
                    $aColumnNames[] = $sNewName;
                    $oDB->createCommand()->renameColumn('{{' . $sTokenTableName . '}}', $sColumnName, $sNewName);
                    // Update attribute descriptions with the new mapping
                    if (isset($aAttributes[$sColumnName])) {
                        $aAttributes[$sNewName]['cpdbmap'] = substr((string) $sColumnName, 15);
                        unset($aAttributes[$sColumnName]);
                    }
                }
            }
            // Add 'cpdbmap' if missing
            foreach ($aAttributes as &$aAttribute) {
                if (!isset($aAttribute['cpdbmap'])) {
                    $aAttribute['cpdbmap'] = '';
                }
            }
            $oDB->createCommand()->update('{{surveys}}', array('attributedescriptions' => serialize($aAttributes)), "sid=" . $arSurvey['sid']);
        }
    }
    unset($arSurveys);
    // Now fix all 'old' token tables
    $aTables = dbGetTablesLike("%old_tokens%");
    foreach ($aTables as $sTable) {
        $aColumnNames = $aColumnNamesIterator = $oDB->schema->getTable($sTable)->columnNames;
        foreach ($aColumnNamesIterator as $sColumnName) {
            // Check if an old atttribute_cpdb column exists in that token table
            if (strpos((string) $sColumnName, 'attribute_cpdb') !== false) {
                $i = 1;
                // Look for a an attribute ID that is available
                while (in_array('attribute_' . $i, $aColumnNames)) {
                    $i++;
                }
                $sNewName = 'attribute_' . $i;
                $aColumnNames[] = $sNewName;
                $oDB->createCommand()->renameColumn($sTable, $sColumnName, $sNewName);
            }
        }
    }
}

function upgradeCPDBAttributeDefaultNames173()
{
    $sQuery = "SELECT attribute_id,attribute_name,COALESCE(lang,NULL)
    FROM {{participant_attribute_names_lang}}
    group by attribute_id, attribute_name, lang
    order by attribute_id";
    $oResult = Yii::app()->db->createCommand($sQuery)->queryAll();
    foreach ($oResult as $aAttribute) {
        Yii::app()->getDb()->createCommand()->update('{{participant_attribute_names}}', array('defaultname' => substr((string) $aAttribute['attribute_name'], 0, 50)), "attribute_id={$aAttribute['attribute_id']}");
    }
}

/**
* Converts global permissions from users table to the new permission system,
* and converts template permissions from template_rights to new permission table
*/
function upgradePermissions166()
{
    Permission::model()->refreshMetaData();  // Needed because otherwise Yii tries to use the outdate permission schema for the permission table
    $oUsers = User::model()->findAll();
    foreach ($oUsers as $oUser) {
        if ($oUser->create_survey == 1) {
            $oPermission = new Permission();
            $oPermission->entity_id = 0;
            $oPermission->entity = 'global';
            $oPermission->uid = $oUser->uid;
            $oPermission->permission = 'surveys';
            $oPermission->create_p = 1;
            $oPermission->save();
        }
        if ($oUser->create_user == 1 || $oUser->delete_user == 1) {
            $oPermission = new Permission();
            $oPermission->entity_id = 0;
            $oPermission->entity = 'global';
            $oPermission->uid = $oUser->uid;
            $oPermission->permission = 'users';
            $oPermission->create_p = $oUser->create_user;
            $oPermission->delete_p = $oUser->delete_user;
            $oPermission->update_p = 1;
            $oPermission->read_p = 1;
            $oPermission->save();
        }
        if ($oUser->superadmin == 1) {
            $oPermission = new Permission();
            $oPermission->entity_id = 0;
            $oPermission->entity = 'global';
            $oPermission->uid = $oUser->uid;
            $oPermission->permission = 'superadmin';
            $oPermission->read_p = 1;
            $oPermission->save();
        }
        if ($oUser->configurator == 1) {
            $oPermission = new Permission();
            $oPermission->entity_id = 0;
            $oPermission->entity = 'global';
            $oPermission->uid = $oUser->uid;
            $oPermission->permission = 'settings';
            $oPermission->update_p = 1;
            $oPermission->read_p = 1;
            $oPermission->save();
        }
        if ($oUser->manage_template == 1) {
            $oPermission = new Permission();
            $oPermission->entity_id = 0;
            $oPermission->entity = 'global';
            $oPermission->uid = $oUser->uid;
            $oPermission->permission = 'templates';
            $oPermission->create_p = 1;
            $oPermission->read_p = 1;
            $oPermission->update_p = 1;
            $oPermission->delete_p = 1;
            $oPermission->import_p = 1;
            $oPermission->export_p = 1;
            $oPermission->save();
        }
        if ($oUser->manage_label == 1) {
            $oPermission = new Permission();
            $oPermission->entity_id = 0;
            $oPermission->entity = 'global';
            $oPermission->uid = $oUser->uid;
            $oPermission->permission = 'labelsets';
            $oPermission->create_p = 1;
            $oPermission->read_p = 1;
            $oPermission->update_p = 1;
            $oPermission->delete_p = 1;
            $oPermission->import_p = 1;
            $oPermission->export_p = 1;
            $oPermission->save();
        }
        if ($oUser->participant_panel == 1) {
            $oPermission = new Permission();
            $oPermission->entity_id = 0;
            $oPermission->entity = 'global';
            $oPermission->uid = $oUser->uid;
            $oPermission->permission = 'participantpanel';
            $oPermission->create_p = 1;
            $oPermission->save();
        }
    }
    $sQuery = "SELECT * FROM {{templates_rights}}";
    $oResult = Yii::app()->getDb()->createCommand($sQuery)->queryAll();
    foreach ($oResult as $aRow) {
        $oPermission = new Permission();
        $oPermission->entity_id = 0;
        $oPermission->entity = 'template';
        $oPermission->uid = $aRow['uid'];
        $oPermission->permission = $aRow['folder'];
        $oPermission->read_p = 1;
        $oPermission->save();
    }
}

/**
*  Make sure all active tables have the right sized token field
*
*  During a small period in the 2.0 cycle some survey tables got no
*  token field or a token field that was too small. This patch makes
*  sure all surveys that are not anonymous have a token field with the
*  right size
*
* @return string|null
*/
function upgradeSurveyTables164()
{
    $sQuery = "SELECT sid FROM {{surveys}} WHERE active='Y' and anonymized='N'";
    $aResult = Yii::app()->getDb()->createCommand($sQuery)->queryAll();
    if (!$aResult) {
        return "Database Error";
    } else {
        foreach ($aResult as $sv) {
            $sSurveyTableName = 'survey_' . $sv['sid'];
            $aColumnNames = $aColumnNamesIterator = Yii::app()->db->schema->getTable('{{' . $sSurveyTableName . '}}')->columnNames;
            if (!in_array('token', $aColumnNames)) {
                addColumn('{{survey_' . $sv['sid'] . '}}', 'token', 'string(36)');
            } else {
                alterColumn('{{survey_' . $sv['sid'] . '}}', 'token', 'string(36)');
            }
        }
    }
}


function upgradeSurveys156()
{
    $sSurveyQuery = "SELECT * FROM {{surveys_languagesettings}}";
    $oSurveyResult = Yii::app()->getDb()->createCommand($sSurveyQuery)->queryAll();
    foreach ($oSurveyResult as $aSurveyRow) {
        $aDefaultTexts = templateDefaultTexts($aSurveyRow['surveyls_language'], 'unescaped');
        if (trim(strip_tags((string) $aSurveyRow['surveyls_email_confirm'])) == '') {
            $sSurveyUpdateQuery = "update {{surveys}} set sendconfirmation='N' where sid=" . $aSurveyRow['surveyls_survey_id'];
            Yii::app()->getDb()->createCommand($sSurveyUpdateQuery)->execute();

            $aValues = array('surveyls_email_confirm_subj' => $aDefaultTexts['confirmation_subject'],
                'surveyls_email_confirm' => $aDefaultTexts['confirmation']);
            SurveyLanguageSetting::model()->updateAll($aValues, 'surveyls_survey_id=:sid', array(':sid' => $aSurveyRow['surveyls_survey_id']));
        }
    }
}

// Add the usesleft field to all existing token tables
function upgradeTokens148()
{
    $aTables = dbGetTablesLike("tokens%");
    foreach ($aTables as $sTable) {
        addColumn($sTable, 'participant_id', "string(50)");
        addColumn($sTable, 'blacklisted', "string(17)");
    }
}



function upgradeQuestionAttributes148()
{
    $sSurveyQuery = "SELECT sid,language,additional_languages FROM {{surveys}}";
    $oSurveyResult = dbExecuteAssoc($sSurveyQuery);
    $aAllAttributes = \LimeSurvey\Helpers\questionHelper::getAttributesDefinitions();
    foreach ($oSurveyResult->readAll() as $aSurveyRow) {
        $iSurveyID = $aSurveyRow['sid'];
        $aLanguages = array_merge(array($aSurveyRow['language']), explode(' ', (string) $aSurveyRow['additional_languages']));
        $sAttributeQuery = "select q.qid,attribute,value from {{question_attributes}} qa , {{questions}} q where q.qid=qa.qid and sid={$iSurveyID}";
        $oAttributeResult = dbExecuteAssoc($sAttributeQuery);
        foreach ($oAttributeResult->readAll() as $aAttributeRow) {
            if (isset($aAllAttributes[$aAttributeRow['attribute']]['i18n']) && $aAllAttributes[$aAttributeRow['attribute']]['i18n']) {
                Yii::app()->getDb()->createCommand("delete from {{question_attributes}} where qid={$aAttributeRow['qid']} and attribute='{$aAttributeRow['attribute']}'")->execute();
                foreach ($aLanguages as $sLanguage) {
                    $sAttributeInsertQuery = "insert into {{question_attributes}} (qid,attribute,value,language) VALUES({$aAttributeRow['qid']},'{$aAttributeRow['attribute']}','{$aAttributeRow['value']}','{$sLanguage}' )";
                    modifyDatabase("", $sAttributeInsertQuery);
                }
            }
        }
    }
}


function upgradeSurveyTimings146()
{
    $aTables = dbGetTablesLike("%timings");
    foreach ($aTables as $sTable) {
        Yii::app()->getDb()->createCommand()->renameColumn($sTable, 'interviewTime', 'interviewtime');
    }
}


// Add the usesleft field to all existing token tables
function upgradeTokens145()
{
    $aTables = dbGetTablesLike("tokens%");
    foreach ($aTables as $sTable) {
        addColumn($sTable, 'usesleft', "integer NOT NULL DEFAULT 1");
        Yii::app()->getDb()->createCommand()->update($sTable, array('usesleft' => '0'), "completed<>'N'");
    }
}


function upgradeSurveys145()
{
    $sSurveyQuery = "SELECT * FROM {{surveys}} where notification<>'0'";
    $oSurveyResult = dbExecuteAssoc($sSurveyQuery);
    foreach ($oSurveyResult->readAll() as $aSurveyRow) {
        if ($aSurveyRow['notification'] == '1' && trim((string) $aSurveyRow['adminemail']) != '') {
            $aEmailAddresses = explode(';', (string) $aSurveyRow['adminemail']);
            $sAdminEmailAddress = $aEmailAddresses[0];
            $sEmailnNotificationAddresses = implode(';', $aEmailAddresses);
            $sSurveyUpdateQuery = "update {{surveys}} set adminemail='{$sAdminEmailAddress}', emailnotificationto='{$sEmailnNotificationAddresses}' where sid=" . $aSurveyRow['sid'];
            Yii::app()->getDb()->createCommand($sSurveyUpdateQuery)->execute();
        } else {
            $aEmailAddresses = explode(';', (string) $aSurveyRow['adminemail']);
            $sAdminEmailAddress = $aEmailAddresses[0];
            $sEmailDetailedNotificationAddresses = implode(';', $aEmailAddresses);
            if (trim((string) $aSurveyRow['emailresponseto']) != '') {
                $sEmailDetailedNotificationAddresses = $sEmailDetailedNotificationAddresses . ';' . trim((string) $aSurveyRow['emailresponseto']);
            }
            $sSurveyUpdateQuery = "update {{surveys}} set adminemail='{$sAdminEmailAddress}', emailnotificationto='{$sEmailDetailedNotificationAddresses}' where sid=" . $aSurveyRow['sid'];
            Yii::app()->getDb()->createCommand($sSurveyUpdateQuery)->execute();
        }
    }
    $sSurveyQuery = "SELECT * FROM {{surveys_languagesettings}}";
    $oSurveyResult = Yii::app()->getDb()->createCommand($sSurveyQuery)->queryAll();
    foreach ($oSurveyResult as $aSurveyRow) {
        $sLanguage = App()->language;
        $aDefaultTexts = templateDefaultTexts($sLanguage, 'unescaped');
        unset($sLanguage);
        $aDefaultTexts['admin_detailed_notification'] = $aDefaultTexts['admin_detailed_notification'] . $aDefaultTexts['admin_detailed_notification_css'];
        $sSurveyUpdateQuery = "update {{surveys_languagesettings}} set
        email_admin_responses_subj=" . $aDefaultTexts['admin_detailed_notification_subject'] . ",
        email_admin_responses=" . $aDefaultTexts['admin_detailed_notification'] . ",
        email_admin_notification_subj=" . $aDefaultTexts['admin_notification_subject'] . ",
        email_admin_notification=" . $aDefaultTexts['admin_notification'] . "
        where surveyls_survey_id=" . $aSurveyRow['surveyls_survey_id'];
        Yii::app()->getDb()->createCommand()->update('{{surveys_languagesettings}}', array('email_admin_responses_subj' => $aDefaultTexts['admin_detailed_notification_subject'],
            'email_admin_responses' => $aDefaultTexts['admin_detailed_notification'],
            'email_admin_notification_subj' => $aDefaultTexts['admin_notification_subject'],
            'email_admin_notification' => $aDefaultTexts['admin_notification']
            ), "surveyls_survey_id={$aSurveyRow['surveyls_survey_id']}");
    }
}


function upgradeSurveyPermissions145()
{
    $sPermissionQuery = "SELECT * FROM {{surveys_rights}}";
    $oPermissionResult = Yii::app()->getDb()->createCommand($sPermissionQuery)->queryAll();
    if (empty($oPermissionResult)) {
        return "Database Error";
    } else {
        $sTableName = '{{survey_permissions}}';
        foreach ($oPermissionResult as $aPermissionRow) {
            $sPermissionInsertQuery = Yii::app()->getDb()->createCommand()->insert($sTableName, array('permission' => 'assessments',
                'create_p' => $aPermissionRow['define_questions'],
                'read_p' => $aPermissionRow['define_questions'],
                'update_p' => $aPermissionRow['define_questions'],
                'delete_p' => $aPermissionRow['define_questions'],
                'sid' => $aPermissionRow['sid'],
                'uid' => $aPermissionRow['uid']));

            $sPermissionInsertQuery = Yii::app()->getDb()->createCommand()->insert($sTableName, array('permission' => 'quotas',
                'create_p' => $aPermissionRow['define_questions'],
                'read_p' => $aPermissionRow['define_questions'],
                'update_p' => $aPermissionRow['define_questions'],
                'delete_p' => $aPermissionRow['define_questions'],
                'sid' => $aPermissionRow['sid'],
                'uid' => $aPermissionRow['uid']));

            $sPermissionInsertQuery = Yii::app()->getDb()->createCommand()->insert($sTableName, array('permission' => 'responses',
                'create_p' => $aPermissionRow['browse_response'],
                'read_p' => $aPermissionRow['browse_response'],
                'update_p' => $aPermissionRow['browse_response'],
                'delete_p' => $aPermissionRow['delete_survey'],
                'export_p' => $aPermissionRow['export'],
                'import_p' => $aPermissionRow['browse_response'],
                'sid' => $aPermissionRow['sid'],
                'uid' => $aPermissionRow['uid']));

            $sPermissionInsertQuery = Yii::app()->getDb()->createCommand()->insert($sTableName, array('permission' => 'statistics',
                'read_p' => $aPermissionRow['browse_response'],
                'sid' => $aPermissionRow['sid'],
                'uid' => $aPermissionRow['uid']));

            $sPermissionInsertQuery = Yii::app()->getDb()->createCommand()->insert($sTableName, array('permission' => 'survey',
                'read_p' => 1,
                'delete_p' => $aPermissionRow['delete_survey'],
                'sid' => $aPermissionRow['sid'],
                'uid' => $aPermissionRow['uid']));

            $sPermissionInsertQuery = Yii::app()->getDb()->createCommand()->insert($sTableName, array('permission' => 'surveyactivation',
                'update_p' => $aPermissionRow['activate_survey'],
                'sid' => $aPermissionRow['sid'],
                'uid' => $aPermissionRow['uid']));

            $sPermissionInsertQuery = Yii::app()->getDb()->createCommand()->insert($sTableName, array('permission' => 'surveycontent',
                'create_p' => $aPermissionRow['define_questions'],
                'read_p' => $aPermissionRow['define_questions'],
                'update_p' => $aPermissionRow['define_questions'],
                'delete_p' => $aPermissionRow['define_questions'],
                'export_p' => $aPermissionRow['export'],
                'import_p' => $aPermissionRow['define_questions'],
                'sid' => $aPermissionRow['sid'],
                'uid' => $aPermissionRow['uid']));

            $sPermissionInsertQuery = Yii::app()->getDb()->createCommand()->insert($sTableName, array('permission' => 'surveylocale',
                'read_p' => $aPermissionRow['edit_survey_property'],
                'update_p' => $aPermissionRow['edit_survey_property'],
                'sid' => $aPermissionRow['sid'],
                'uid' => $aPermissionRow['uid']));

            $sPermissionInsertQuery = Yii::app()->getDb()->createCommand()->insert($sTableName, array('permission' => 'surveysettings',
                'read_p' => $aPermissionRow['edit_survey_property'],
                'update_p' => $aPermissionRow['edit_survey_property'],
                'sid' => $aPermissionRow['sid'],
                'uid' => $aPermissionRow['uid']));

            $sPermissionInsertQuery = Yii::app()->getDb()->createCommand()->insert($sTableName, array('permission' => 'tokens',
                'create_p' => $aPermissionRow['activate_survey'],
                'read_p' => $aPermissionRow['activate_survey'],
                'update_p' => $aPermissionRow['activate_survey'],
                'delete_p' => $aPermissionRow['activate_survey'],
                'export_p' => $aPermissionRow['export'],
                'import_p' => $aPermissionRow['activate_survey'],
                'sid' => $aPermissionRow['sid'],
                'uid' => $aPermissionRow['uid']));
        }
    }
}

function upgradeTables143()
{

    $aQIDReplacements = array();
    $answerquery = "select a.*, q.sid, q.gid from {{answers}} a,{{questions}} q where a.qid=q.qid and q.type in ('L','O','!') and a.default_value='Y'";
    $answerresult = Yii::app()->getDb()->createCommand($answerquery)->queryAll();
    foreach ($answerresult as $row) {
        modifyDatabase("", "INSERT INTO {{defaultvalues}} (qid, scale_id,language,specialtype,defaultvalue) VALUES ({$row['qid']},0," . dbQuoteAll($row['language']) . ",''," . dbQuoteAll($row['code']) . ")");
    }

    // Convert answers to subquestions

    $answerquery = "select a.*, q.sid, q.gid, q.type from {{answers}} a,{{questions}} q where a.qid=q.qid and a.language=q.language and q.type in ('1','A','B','C','E','F','H','K',';',':','M','P','Q')";
    $answerresult = Yii::app()->getDb()->createCommand($answerquery)->queryAll();
    foreach ($answerresult as $row) {
        $aInsert = array();
        if (isset($aQIDReplacements[$row['qid'] . '_' . $row['code']])) {
            $aInsert['qid'] = $aQIDReplacements[$row['qid'] . '_' . $row['code']];
        }
        $aInsert['sid'] = $row['sid'];
        $aInsert['gid'] = $row['gid'];
        $aInsert['parent_qid'] = $row['qid'];
        $aInsert['type'] = $row['type'];
        $aInsert['title'] = $row['code'];
        $aInsert['question'] = $row['answer'];
        $aInsert['question_order'] = $row['sortorder'];
        $aInsert['language'] = $row['language'];

        $iLastInsertID = Question::model()->insertRecords($aInsert);
        if (!isset($aInsert['qid'])) {
            $aQIDReplacements[$row['qid'] . '_' . $row['code']] = $iLastInsertID;
            $iSaveSQID = $aQIDReplacements[$row['qid'] . '_' . $row['code']];
        } else {
            $iSaveSQID = $aInsert['qid'];
        }
        if (($row['type'] == 'M' || $row['type'] == 'P') && $row['default_value'] == 'Y') {
            modifyDatabase("", "INSERT INTO {{defaultvalues}} (qid, sqid, scale_id,language,specialtype,defaultvalue) VALUES ({$row['qid']},{$iSaveSQID},0," . dbQuoteAll($row['language']) . ",'','Y')");
        }
    }
    // Sanitize data
    if (Yii::app()->db->driverName == 'pgsql') {
        modifyDatabase("", "delete from {{answers}} USING {{questions}} WHERE {{answers}}.qid={{questions}}.qid AND {{questions}}.type in ('1','F','H','M','P','W','Z')");
    } else {
        modifyDatabase("", "delete {{answers}} from {{answers}} LEFT join {{questions}} ON {{answers}}.qid={{questions}}.qid where {{questions}}.type in ('1','F','H','M','P','W','Z')");
    }

    // Convert labels to answers
    $answerquery = "select qid ,type ,lid ,lid1, language from {{questions}} where parent_qid=0 and type in ('1','F','H','M','P','W','Z')";
    $answerresult = Yii::app()->getDb()->createCommand($answerquery)->queryAll();
    foreach ($answerresult as $row) {
        $labelquery = "Select * from {{labels}} where lid={$row['lid']} and language=" . dbQuoteAll($row['language']);
        $labelresult = Yii::app()->getDb()->createCommand($labelquery)->queryAll();
        foreach ($labelresult as $lrow) {
            modifyDatabase("", "INSERT INTO {{answers}} (qid, code, answer, sortorder, language, assessment_value) VALUES ({$row['qid']}," . dbQuoteAll($lrow['code']) . "," . dbQuoteAll($lrow['title']) . ",{$lrow['sortorder']}," . dbQuoteAll($lrow['language']) . ",{$lrow['assessment_value']})");
            //$labelids[]
        }
        if ($row['type'] == '1') {
            $labelquery = "Select * from {{labels}} where lid={$row['lid1']} and language=" . dbQuoteAll($row['language']);
            $labelresult = Yii::app()->getDb()->createCommand($labelquery)->queryAll();
            foreach ($labelresult as $lrow) {
                modifyDatabase("", "INSERT INTO {{answers}} (qid, code, answer, sortorder, language, scale_id, assessment_value) VALUES ({$row['qid']}," . dbQuoteAll($lrow['code']) . "," . dbQuoteAll($lrow['title']) . ",{$lrow['sortorder']}," . dbQuoteAll($lrow['language']) . ",1,{$lrow['assessment_value']})");
            }
        }
    }

    // Convert labels to subquestions
    $answerquery = "select * from {{questions}} where parent_qid=0 and type in (';',':')";
    $answerresult = Yii::app()->getDb()->createCommand($answerquery)->queryAll();
    foreach ($answerresult as $row) {
        $labelquery = "Select * from {{labels}} where lid={$row['lid']} and language=" . dbQuoteAll($row['language']);
        $labelresult = Yii::app()->getDb()->createCommand($labelquery)->queryAll();
        foreach ($labelresult as $lrow) {
            $aInsert = array();
            if (isset($aQIDReplacements[$row['qid'] . '_' . $lrow['code'] . '_1'])) {
                $aInsert['qid'] = $aQIDReplacements[$row['qid'] . '_' . $lrow['code'] . '_1'];
            }
            $aInsert['sid'] = $row['sid'];
            $aInsert['gid'] = $row['gid'];
            $aInsert['parent_qid'] = $row['qid'];
            $aInsert['type'] = $row['type'];
            $aInsert['title'] = $lrow['code'];
            $aInsert['question'] = $lrow['title'];
            $aInsert['question_order'] = $lrow['sortorder'];
            $aInsert['language'] = $lrow['language'];
            $aInsert['scale_id'] = 1;
            $iLastInsertID = Question::model()->insertRecords($aInsert);

            if (isset($aInsert['qid'])) {
                $aQIDReplacements[$row['qid'] . '_' . $lrow['code'] . '_1'] = $iLastInsertID;
            }
        }
    }



    $updatequery = "update {{questions}} set type='!' where type='W'";
    modifyDatabase("", $updatequery);
    $updatequery = "update {{questions}} set type='L' where type='Z'";
    modifyDatabase("", $updatequery);
}


function upgradeQuestionAttributes142()
{
    $attributequery = "Select qid from {{question_attributes}} where attribute='exclude_all_other'  group by qid having count(qid)>1 ";
    $questionids = Yii::app()->getDb()->createCommand($attributequery)->queryRow();
    if (!is_array($questionids)) {
        return "Database Error";
    } else {
        foreach ($questionids as $questionid) {
            //Select all affected question attributes
            $attributevalues = Yii::app()->getDb()->createCommand("SELECT value from {{question_attributes}} where attribute='exclude_all_other' and qid=" . $questionid)->queryColumn();
            modifyDatabase("", "delete from {{question_attributes}} where attribute='exclude_all_other' and qid=" . $questionid);
            $record['value'] = implode(';', $attributevalues);
            $record['attribute'] = 'exclude_all_other';
            $record['qid'] = $questionid;
            Yii::app()->getDb()->createCommand()->insert('{{question_attributes}}', $record);
        }
    }
}

function upgradeSurveyTables139()
{
    $aTables = dbGetTablesLike("survey\_%");
    $oDB = Yii::app()->db;
    foreach ($aTables as $sTable) {
        $oSchema = Yii::app()->db->schema;
        $oTableSchema = $oSchema->getTable($sTable);
        removeMysqlZeroDate($sTable, $oTableSchema, $oDB);
        addColumn($sTable, 'lastpage', 'integer');
    }
}


// Add the reminders tracking fields
function upgradeTokenTables134()
{
    $aTables = dbGetTablesLike("tokens%");
    foreach ($aTables as $sTable) {
        addColumn($sTable, 'validfrom', "datetime");
        addColumn($sTable, 'validuntil', "datetime");
    }
}

/**
* @param string $sFieldType
* @param string $sColumn
*/
function alterColumn($sTable, $sColumn, $sFieldType, $bAllowNull = true, $sDefault = 'NULL')
{
    $oDB = Yii::app()->db;
    switch (Yii::app()->db->driverName) {
        case 'mysql':
            $sType = $sFieldType;
            if ($bAllowNull !== true) {
                $sType .= ' NOT NULL';
            }
            if ($sDefault != 'NULL') {
                $sType .= " DEFAULT '{$sDefault}'";
            }
            $oDB->createCommand()->alterColumn($sTable, $sColumn, $sType);
            break;
        case 'dblib':
        case 'sqlsrv':
        case 'mssql':
            dropDefaultValueMSSQL($sColumn, $sTable);
            $sType = $sFieldType;
            if ($bAllowNull != true && $sDefault != 'NULL') {
                $oDB->createCommand("UPDATE {$sTable} SET [{$sColumn}]='{$sDefault}' where [{$sColumn}] is NULL;")->execute();
            }
            if ($bAllowNull != true) {
                $sType .= ' NOT NULL';
            } else {
                $sType .= ' NULL';
            }
            $oDB->createCommand()->alterColumn($sTable, $sColumn, $sType);
            if ($sDefault != 'NULL') {
                $oDB->createCommand("ALTER TABLE {$sTable} ADD DEFAULT '{$sDefault}' FOR [{$sColumn}];")->execute();
            }
            break;
        case 'pgsql':
            $sType = $sFieldType;
            $oDB->createCommand()->alterColumn($sTable, $sColumn, $sType);

            try {
                setTransactionBookmark();
                $oDB->createCommand("ALTER TABLE {$sTable} ALTER COLUMN {$sColumn} DROP DEFAULT")->execute();
            } catch (Exception $e) {
                rollBackToTransactionBookmark();
            };

            try {
                setTransactionBookmark();
                $oDB->createCommand("ALTER TABLE {$sTable} ALTER COLUMN {$sColumn} DROP NOT NULL")->execute();
            } catch (Exception $e) {
                rollBackToTransactionBookmark();
            };

            if ($bAllowNull != true) {
                $oDB->createCommand("ALTER TABLE {$sTable} ALTER COLUMN {$sColumn} SET NOT NULL")->execute();
            }
            if ($sDefault != 'NULL') {
                $oDB->createCommand("ALTER TABLE {$sTable} ALTER COLUMN {$sColumn} SET DEFAULT '{$sDefault}'")->execute();
            }
            $oDB->createCommand()->alterColumn($sTable, $sColumn, $sType);
            break;
        default:
            die('Unknown database type');
    }
}

/**
* @param string $sType
*/
function addColumn($sTableName, $sColumn, $sType)
{
    Yii::app()->db->createCommand()->addColumn($sTableName, $sColumn, $sType);
}

/**
* Set a transaction bookmark - this is critical for Postgres because a transaction in Postgres cannot be continued unless you roll back to the transaction bookmark first
*
* @param mixed $sBookmark  Name of the bookmark
*/
function setTransactionBookmark($sBookmark = 'limesurvey')
{
    if (Yii::app()->db->driverName == 'pgsql') {
        Yii::app()->db->createCommand("SAVEPOINT {$sBookmark};")->execute();
    }
}

/**
* Roll back to a transaction bookmark
*
* @param mixed $sBookmark   Name of the bookmark
*/
function rollBackToTransactionBookmark($sBookmark = 'limesurvey')
{
    if (Yii::app()->db->driverName == 'pgsql') {
        Yii::app()->db->createCommand("ROLLBACK TO SAVEPOINT {$sBookmark};")->execute();
    }
}

/**
* Drop a default value in MSSQL
*
* @param string $fieldname
* @param mixed $tablename
*/
function dropDefaultValueMSSQL($fieldname, $tablename)
{
    // find out the name of the default constraint
    // Did I already mention that this is the most suckiest thing I have ever seen in MSSQL database?
    $dfquery = "SELECT c_obj.name AS constraint_name
    FROM sys.sysobjects AS c_obj INNER JOIN
    sys.sysobjects AS t_obj ON c_obj.parent_obj = t_obj.id INNER JOIN
    sys.sysconstraints AS con ON c_obj.id = con.constid INNER JOIN
    sys.syscolumns AS col ON t_obj.id = col.id AND con.colid = col.colid
    WHERE (c_obj.xtype = 'D') AND (col.name = '$fieldname') AND (t_obj.name='{$tablename}')";
    $defaultname = Yii::app()->getDb()->createCommand($dfquery)->queryRow();
    if ($defaultname != false) {
        Yii::app()->db->createCommand("ALTER TABLE {$tablename} DROP CONSTRAINT {$defaultname['constraint_name']}")->execute();
    }
}

/**
* This function drops a unique Key of an MSSQL database field by using the field name and the table name
*
* @param string $sFieldName
* @param string $sTableName
*/
function dropUniqueKeyMSSQL($sFieldName, $sTableName)
{
    $sQuery = "select TC.Constraint_Name, CC.Column_Name from information_schema.table_constraints TC
    inner join information_schema.constraint_column_usage CC on TC.Constraint_Name = CC.Constraint_Name
    where TC.constraint_type = 'Unique' and Column_name='{$sFieldName}' and TC.TABLE_NAME='{$sTableName}'";
    $aUniqueKeyName = Yii::app()->getDb()->createCommand($sQuery)->queryRow();
    if ($aUniqueKeyName != false) {
        Yii::app()->getDb()->createCommand("ALTER TABLE {$sTableName} DROP CONSTRAINT {$aUniqueKeyName['Constraint_Name']}")->execute();
    }
}

/**
* This function drops a secondary key of an MSSQL database field by using the field name and the table name
*
* @param string $sFieldName
* @param mixed $sTableName
*/
function dropSecondaryKeyMSSQL($sFieldName, $sTableName)
{
    $oDB = Yii::app()->getDb();
    $sQuery = "select
    i.name as IndexName
    from sys.indexes i
    join sys.objects o on i.object_id = o.object_id
    join sys.index_columns ic on ic.object_id = i.object_id
    and ic.index_id = i.index_id
    join sys.columns co on co.object_id = i.object_id
    and co.column_id = ic.column_id
    where i.[type] = 2
    and i.is_unique = 0
    and i.is_primary_key = 0
    and o.[type] = 'U'
    and ic.is_included_column = 0
    and o.name='{$sTableName}' and co.name='{$sFieldName}'";
    $aKeyName = Yii::app()->getDb()->createCommand($sQuery)->queryScalar();
    if ($aKeyName != false) {
        try {
            $oDB->createCommand()->dropIndex($aKeyName, $sTableName);
        } catch (Exception $e) {
        }
    }
}

/**
* Drops the primary key of a table
*
* @param string $sTablename
* @param string $oldPrimaryKeyColumn
*/
function dropPrimaryKey($sTablename, $oldPrimaryKeyColumn = null)
{
    switch (Yii::app()->db->driverName) {
        case 'mysql':
            if ($oldPrimaryKeyColumn !== null) {
                $sQuery = "ALTER TABLE {{" . $sTablename . "}} MODIFY {$oldPrimaryKeyColumn} INT NOT NULL";
                Yii::app()->db->createCommand($sQuery)->execute();
            }
            $sQuery = "ALTER TABLE {{" . $sTablename . "}} DROP PRIMARY KEY";
            Yii::app()->db->createCommand($sQuery)->execute();
            break;
        case 'pgsql':
        case 'sqlsrv':
        case 'dblib':
        case 'mssql':
            $pkquery = "SELECT CONSTRAINT_NAME "
            . "FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS "
            . "WHERE (TABLE_NAME = '{{{$sTablename}}}') AND (CONSTRAINT_TYPE = 'PRIMARY KEY')";

            $primarykey = Yii::app()->db->createCommand($pkquery)->queryRow(false);
            if ($primarykey !== false) {
                $sQuery = "ALTER TABLE {{" . $sTablename . "}} DROP CONSTRAINT " . $primarykey[0];
                Yii::app()->db->createCommand($sQuery)->execute();
            }
            break;
        default:
            die('Unknown database type');
    }
}

/**
* @param string $sTablename
*/
function addPrimaryKey($sTablename, $aColumns)
{
    return Yii::app()->db->createCommand()->addPrimaryKey('PK_' . $sTablename . '_' . randomChars(12, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'), '{{' . $sTablename . '}}', $aColumns);
}

/**
* Modifies a primary key in one command  - this is only tested on MySQL
*
* @param string $sTablename The table name
* @param string[] $aColumns Column names to be in the new key
*/
function modifyPrimaryKey($sTablename, $aColumns)
{
    switch (Yii::app()->db->driverName) {
        case 'mysql':
            Yii::app()->db->createCommand("ALTER TABLE {{" . $sTablename . "}} DROP PRIMARY KEY, ADD PRIMARY KEY (" . implode(',', $aColumns) . ")")->execute();
            break;
        case 'pgsql':
        case 'sqlsrv':
        case 'dblib':
        case 'mssql':
            $pkquery = "SELECT CONSTRAINT_NAME "
            . "FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS "
            . "WHERE (TABLE_NAME = '{{{$sTablename}}}') AND (CONSTRAINT_TYPE = 'PRIMARY KEY')";

            $primarykey = Yii::app()->db->createCommand($pkquery)->queryRow(false);
            if ($primarykey !== false) {
                Yii::app()->db->createCommand("ALTER TABLE {{" . $sTablename . "}} DROP CONSTRAINT " . $primarykey[0])->execute();
                Yii::app()->db->createCommand("ALTER TABLE {{" . $sTablename . "}} ADD PRIMARY KEY (" . implode(',', $aColumns) . ")")->execute();
            }
            break;
        default:
            die('Unknown database type');
    }
}



/**
* @param string $sEncoding
* @param string $sCollation
*/
function fixMySQLCollations($sEncoding, $sCollation)
{
    $surveyidresult = dbGetTablesLike("%");
    foreach ($surveyidresult as $sTableName) {
        try {
            Yii::app()->getDb()->createCommand("ALTER TABLE {$sTableName} CONVERT TO CHARACTER SET {$sEncoding} COLLATE {$sCollation};")->execute();
        } catch (Exception $e) {
            // There are some big survey response tables that cannot be converted because the new charset probably uses
            // more bytes per character than the old one - we just leave them as they are for now.
        };
    }
    $sDatabaseName = getDBConnectionStringProperty('dbname');
    Yii::app()->getDb()->createCommand("ALTER DATABASE `$sDatabaseName` DEFAULT CHARACTER SET {$sEncoding} COLLATE {$sCollation};");
}

/**
*  Drops a column, automatically removes blocking default value on MSSQL
 * @param string $sTableName
 * @param string $sColumnName
 */
function dropColumn($sTableName, $sColumnName)
{
    if (Yii::app()->db->getDriverName() == 'mssql' || Yii::app()->db->getDriverName() == 'sqlsrv' || Yii::app()->db->getDriverName() == 'dblib') {
        dropDefaultValueMSSQL($sColumnName, $sTableName);
    }
    try {
        Yii::app()->db->createCommand()->dropColumn($sTableName, $sColumnName);
    } catch (Exception $e) {
       // If it cannot be dropped we assume it is already gone
    };
}


/**
*  Renames a language code in the whole LimeSurvey database
 * @param string $sOldLanguageCode
 * @param string $sNewLanguageCode
 */
function alterLanguageCode($sOldLanguageCode, $sNewLanguageCode)
{
    $oDB = Yii::app()->db;
    $oDB->createCommand()->update('{{answers}}', array('language' => $sNewLanguageCode), 'language=:lang', array(':lang' => $sOldLanguageCode));
    $oDB->createCommand()->update('{{questions}}', array('language' => $sNewLanguageCode), 'language=:lang', array(':lang' => $sOldLanguageCode));
    $oDB->createCommand()->update('{{groups}}', array('language' => $sNewLanguageCode), 'language=:lang', array(':lang' => $sOldLanguageCode));
    $oDB->createCommand()->update('{{labels}}', array('language' => $sNewLanguageCode), 'language=:lang', array(':lang' => $sOldLanguageCode));
    $oDB->createCommand()->update('{{surveys}}', array('language' => $sNewLanguageCode), 'language=:lang', array(':lang' => $sOldLanguageCode));
    $oDB->createCommand()->update('{{surveys_languagesettings}}', array('surveyls_language' => $sNewLanguageCode), 'surveyls_language=:lang', array(':lang' => $sOldLanguageCode));
    $oDB->createCommand()->update('{{users}}', array('lang' => $sNewLanguageCode), 'lang=:language', array(':language' => $sOldLanguageCode));

    $resultdata = $oDB->createCommand("select * from {{labelsets}}");
    foreach ($resultdata->queryAll() as $datarow) {
        $aLanguages = explode(' ', (string) $datarow['languages']);
        foreach ($aLanguages as &$sLanguage) {
            if ($sLanguage == $sOldLanguageCode) {
                $sLanguage = $sNewLanguageCode;
            }
        }
        $toreplace = implode(' ', $aLanguages);
        $oDB->createCommand()->update('{{labelsets}}', array('languages' => $toreplace), 'lid=:lid', array(':lid' => $datarow['lid']));
    }

    $resultdata = $oDB->createCommand("select * from {{surveys}}");
    foreach ($resultdata->queryAll() as $datarow) {
        $aLanguages = explode(' ', (string) $datarow['additional_languages']);
        foreach ($aLanguages as &$sLanguage) {
            if ($sLanguage == $sOldLanguageCode) {
                $sLanguage = $sNewLanguageCode;
            }
        }
        $toreplace = implode(' ', $aLanguages);
        $oDB->createCommand()->update('{{surveys}}', array('additional_languages' => $toreplace), 'sid=:sid', array(':sid' => $datarow['sid']));
    }
}


function fixLanguageConsistencyAllSurveys()
{
    $surveyidquery = "SELECT sid,additional_languages FROM " . App()->db->quoteColumnName('{{surveys}}');
    $surveyidresult = Yii::app()->db->createCommand($surveyidquery)->queryAll();
    foreach ($surveyidresult as $sv) {
        fixLanguageConsistency($sv['sid'], $sv['additional_languages']);
    }
}

/**
 * This function fixes Postgres sequences for one/all tables in a database
 * This is necessary if a table is renamed. If tablename is given then only that table is fixed
 * @param string $tableName Table name without prefix
 * @return void
 */
function fixPostgresSequence($tableName = null)
{
    $oDB = Yii::app()->getDb();
    $query = "SELECT 'SELECT SETVAL(' ||
                quote_literal(quote_ident(PGT.schemaname) || '.' || quote_ident(S.relname)) ||
                ', COALESCE(MAX(' ||quote_ident(C.attname)|| '), 1) ) FROM ' ||
                quote_ident(PGT.schemaname)|| '.'||quote_ident(T.relname)|| ';'
            FROM pg_class AS S,
                pg_depend AS D,
                pg_class AS T,
                pg_attribute AS C,
                pg_tables AS PGT
            WHERE S.relkind = 'S'
                AND S.oid = D.objid
                AND D.refobjid = T.oid
                AND D.refobjid = C.attrelid
                AND D.refobjsubid = C.attnum
                AND T.relname = PGT.tablename";
    if ($tableName != null) {
        $query .= " AND PGT.tablename= '{{" . $tableName . "}}' ";
    }
    $query .= "ORDER BY S.relname;";
    $FixingQueries = Yii::app()->db->createCommand($query)->queryColumn();
    foreach ($FixingQueries as $fixingQuery) {
        $oDB->createCommand($fixingQuery)->execute();
    }
}

function runAddPrimaryKeyonAnswersTable400(&$oDB)
{
    if (!in_array($oDB->getDriverName(), array('mssql', 'sqlsrv', 'dblib'))) {
        dropPrimaryKey('answers');
        addColumn('{{answers}}', 'aid', 'pk');
        modifyPrimaryKey('answers', array('aid'));
        $oDB->createCommand()->createIndex('answer_idx_10', '{{answers}}', ['qid', 'code', 'scale_id']);
        $dataReader = $oDB->createCommand("SELECT qid, code, scale_id FROM {{answers}} group by qid, code, scale_id")->query();
        $iCounter = 1;
        while (($row = $dataReader->read()) !== false) {
            $oDB->createCommand("UPDATE {{answers}} SET aid={$iCounter} WHERE qid={$row['qid']} AND code='{$row['code']}' AND scale_id={$row['scale_id']}")->execute();
            $iCounter++;
        }
        $oDB->createCommand()->dropindex('answer_idx_10', '{{answers}}');
    } else {
        $oDB->createCommand()->renameTable('{{answers}}', 'answertemp');
        $oDB->createCommand()->createIndex('answer_idx_10', 'answertemp', ['qid', 'code', 'scale_id']);

        $dataReader = $oDB->createCommand("SELECT qid, code, scale_id FROM answertemp group by qid, code, scale_id")->query();

        $oDB->createCommand()->createTable('{{answers}}', [
            'aid' =>  "pk",
            'qid' => 'integer NOT NULL',
            'code' => 'string(5) NOT NULL',
            'sortorder' => 'integer NOT NULL',
            'assessment_value' => 'integer NOT NULL DEFAULT 0',
            'scale_id' => 'integer NOT NULL DEFAULT 0',
            'answer' => 'text NOT NULL',
            'language' =>  "string(20) NOT NULL DEFAULT 'en'"
        ]);

        $dataReader = $oDB->createCommand("SELECT qid, code, scale_id FROM answertemp group by qid, code, scale_id")->query();
        $iCounter = 1;
        while (($row = $dataReader->read()) !== false) {
            $dataBlock = $oDB->createCommand("SELECT * FROM answertemp WHERE qid={$row['qid']} AND code='{$row['code']}' AND scale_id={$row['scale_id']}")->queryRow();
            $oDB->createCommand()->insert('{{answers}}', $dataBlock);
        }
        $oDB->createCommand()->dropindex('answer_idx_10', 'answertemp');
        $oDB->createCommand()->dropTable('answertemp');
    }
}

/**
 * Regenerate codes for problematic label sets
 * Helper function (TODO: Put in separate class)
 * Fails silently
 *
 * @param int $lid Label set id
 * @param bool $hasLanguageColumn Should be true before dbversion 400 is finished, false after
 * @return void
 */
function regenerateLabelCodes400(int $lid, $hasLanguageColumn = true)
{
    $oDB = Yii::app()->getDb();

    $labelSet = $oDB->createCommand(
        sprintf("SELECT * FROM {{labelsets}} WHERE lid = %d", $lid)
    )->queryRow();
    if (empty($labelSet)) {
        // No belonging label set, remove orphan labels.
        // @see https://bugs.limesurvey.org/view.php?id=17608
        $oDB->createCommand(
            sprintf(
                'DELETE FROM {{labels}} WHERE lid = %d',
                $lid
            )
        )->execute();
        return;
    }

    foreach (explode(' ', (string) $labelSet['languages']) as $lang) {
        if ($hasLanguageColumn) {
            $query = sprintf(
                "SELECT * FROM {{labels}} WHERE lid = %d AND language = %s",
                $lid,
                $oDB->quoteValue($lang)
            );
        } else {
            // When this function is used in update 475, the language column is already moved.
            $query = sprintf("SELECT * FROM {{labels}} WHERE lid = %d", $lid);
        }
        $labels = $oDB->createCommand($query)->queryAll();
        if (empty($labels)) {
            continue;
        }
        foreach ($labels as $key => $label) {
            $oDB->createCommand(
                sprintf(
                    "UPDATE {{labels}} SET code = %s WHERE id = %d",
                    $oDB->quoteValue("L" . (string) ((int) $key + 1)),
                    $label['id']
                )
            )->execute();
        }
    }
}

/**
 * Remove all zero-dates in $tableName by checking datetime columns from $tableSchema
 * Zero-dates are replaced with null where possible; otherwise 1970-01-01
 *
 * @param string $tableName
 * @param CDbTableSchema $tableSchema
 * @param CDbConnection $oDB
 * @return void
 */
function removeMysqlZeroDate($tableName, CDbTableSchema $tableSchema, CDbConnection $oDB)
{
    // Do nothing if we're not using MySQL
    if (Yii::app()->db->driverName !== 'mysql') {
        return;
    }

    foreach ($tableSchema->columns as $columnName => $info) {
        if ($info->dbType === 'datetime') {
            try {
                $oDB->createCommand()->update($tableName, [$columnName => null], "$columnName = 0");
            } catch (Exception $e) {
                // $columnName might not be allowed to be null, then try with 1970-01-01 Unix 0 date instead.
                $oDB->createCommand()->update($tableName, [$columnName => '1970-01-01 00:00:00'], "$columnName = 0");
            }
        }
    }
}

/**
 * Returns a sorted array of update objects with version higher than $iOldDBVersion
 *
 * @param int $iOldDBVersion
 * @param CDbConnection $db
 * @param string $options
 * @return DatabaseUpdateBase[]
 * @todo Move to class?
 */
function getRelevantUpdates($iOldDBVersion, CDbConnection $db, $options)
{
    $updates = [];
    $dir = new DirectoryIterator(dirname(__FILE__) . '/updates/');
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot()) {
            $info = $fileinfo->getFileInfo();
            $basename = $info->getBasename(".php");
            $fullname = 'LimeSurvey\\Helpers\\Update\\' . $basename;
            $update = new $fullname($db, $options);
            $version = $update->getVersion();
            // Only add if version is newer than $iOldDBVersion
            if ($version > $iOldDBVersion) {
                $updates[$version] = $update;
            }
        }
    }
    ksort($updates, SORT_NUMERIC);
    return $updates;
}
