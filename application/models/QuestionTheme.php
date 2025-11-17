<?php

use LimeSurvey\Helpers\questionHelper;

/**
 * This is the model class for table "{{question_themes}}".
 *
 * The following are the available columns in table '{{question_themes}}':
 *
 * @property integer $id
 * @property string  $name
 * @property string  $visible
 * @property string  $xml_path
 * @property string  $image_path
 * @property string  $title
 * @property string  $creation_date
 * @property string  $author
 * @property string  $author_email
 * @property string  $author_url
 * @property string  $copyright
 * @property string  $license
 * @property string  $version
 * @property string  $api_version
 * @property string  $description
 * @property string  $last_update
 * @property integer $owner_id
 * @property string  $theme_type
 * @property string  $question_type
 * @property integer $core_theme
 * @property string  $extends
 * @property string  $group
 * @property string  $settings
 */
class QuestionTheme extends LSActiveRecord
{
    const THEME_TYPE_CORE = 'coreQuestion';
    const THEME_TYPE_CUSTOM = 'customCoreTheme';
    const THEME_TYPE_USER = 'customUserTheme';

    /**
     * Returns the table name for this model.
     * 
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{question_themes}}';
    }

    /**
     * Returns the validation rules for this model.
     * 
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            [
                'name',
                'unique',
                'caseSensitive' => false,
                'criteria'      => [
                    'condition' => 'extends=:extends',
                    'params'    => [
                        ':extends' => $this->extends
                    ]
                ],
            ],
            array('name, title, api_version, question_type', 'required'),
            array('owner_id', 'numerical', 'integerOnly' => true),
            array('core_theme', 'boolean'),
            array('name, author, theme_type, question_type, extends, group', 'length', 'max' => 150),
            array('visible', 'length', 'max' => 1),
            array('xml_path, image_path, author_email, author_url', 'length', 'max' => 255),
            array('title', 'length', 'max' => 100),
            array('version, api_version', 'length', 'max' => 45),
            array('creation_date, copyright, license, description, last_update, settings', 'safe'),
            // The following rule is used by search().
            array('id, name, visible, xml_path, image_path, title, creation_date, author, author_email, author_url, copyright, license, version, api_version, description, last_update, owner_id, theme_type, question_type, core_theme, extends, group, settings', 'safe', 'on' => 'search'),
        );
    }

    /**
     * Returns the relations for this model.
     * 
     * @return array relational rules.
     */
    public function relations()
    {
        return array();
    }

    /**
     * Defines the named scopes available for this model.
     *
     * This method overrides CActiveRecord::scopes() and provides additional
     * query scopes that can be used when retrieving question themes.
     *
     * Currently it defines the "base" scope, which selects all core question
     * themes that do not extend any other theme or question type.
     *
     * Example usage:
     *     QuestionTheme::model()->base()->findAll();
     *
     * @inheritdoc
     *
     * @return array<string, array<string, mixed>>  An array of named scopes.
     */
    public function scopes()
    {
        return array(
            // 'base' themes are the ones that don't extend any question type/theme.
            'base' => array(
                'condition' => 'core_theme = :true AND extends = :extends',
                'params' => array(':true' => true, ':extends' => '')
            ),
        );
    }

    /**
     * Returns the attribute labels.
     * 
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'            => 'ID',
            'name'          => 'Name',
            'visible'       => 'Visible',
            'xml_path'      => 'Xml Path',
            'image_path'    => 'Image Path',
            'title'         => 'Title',
            'creation_date' => 'Creation Date',
            'author'        => 'Author',
            'author_email'  => 'Author Email',
            'author_url'    => 'Author Url',
            'copyright'     => 'Copyright',
            'license'       => 'License',
            'version'       => 'Version',
            'api_version'   => 'Api Version',
            'description'   => 'Description',
            'last_update'   => 'Last Update',
            'owner_id'      => 'Owner',
            'theme_type'    => 'Theme Type',
            'question_type' => 'Question Type',
            'core_theme'    => 'Core Theme',
            'extends'       => 'Extends',
            'group'         => 'Group',
            'settings'      => 'Settings',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        $pageSizeTemplateView = App()->user->getState('pageSizeTemplateView', App()->params['defaultPageSize']);

        $criteria = new LSDbCriteria();
        $criteria->compare('id', $this->id);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('visible', $this->visible, true);
        $criteria->compare('xml_path', $this->xml_path, true);
        $criteria->compare('image_path', $this->image_path, true);
        $criteria->compare('title', $this->title, true);
        $criteria->compare('creation_date', $this->creation_date, true);
        $criteria->compare('author', $this->author, true);
        $criteria->compare('author_email', $this->author_email, true);
        $criteria->compare('author_url', $this->author_url, true);
        $criteria->compare('copyright', $this->copyright, true);
        $criteria->compare('license', $this->license, true);
        $criteria->compare('version', $this->version, true);
        $criteria->compare('api_version', $this->api_version, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('last_update', $this->last_update, true);
        $criteria->compare('owner_id', $this->owner_id);
        $criteria->compare('theme_type', $this->theme_type, true);
        $criteria->compare('question_type', $this->question_type, true);
        $criteria->compare('core_theme', $this->core_theme);
        $criteria->compare('extends', $this->extends, true);
        $criteria->compare('group', $this->group, true);
        $criteria->compare('settings', $this->settings, true);
        $sort = new CSort();
        $sort->defaultOrder = 'name';
        return new CActiveDataProvider(
            $this, array(
            'criteria'   => $criteria,
            'sort'      => $sort,
            'pagination' => array(
                'pageSize' => $pageSizeTemplateView,
            ),
            )
        );
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     *
     * @param string $className active record class name.
     *
     * @return static
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Returns this table's primary key
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return 'id';
    }

    /**
     * Returns visibility button.
     *
     * @return string|array
     */
    public function getVisibilityButton()
    {
        // don't show any buttons if user doesn't have update permission
        if (!Permission::model()->hasGlobalPermission('templates', 'update')) {
            return '';
        }
        $bVisible = $this->visible == 'Y' ? true : false;
        $aButtons = [
            'visibility_button' => [
                'url'     => $sToggleVisibilityUrl = App()->getController()->createUrl('admin/questionthemes/sa/togglevisibility', ['id' => $this->id]),
                'visible' => $bVisible
            ]
        ];
        $sButtons = App()->getController()->renderPartial('./theme_buttons', ['id' => $this->id, 'buttons' => $aButtons], true);
        return $sButtons;
    }

    /**
     * Install Button for the available questions
     * 
     * @return string
     */
    public function getManifestButtons()
    {
        $sLoadLink = CHtml::form(array("themeOptions/importManifest/"), 'post', array('id' => 'forminstallquestiontheme', 'name' => 'forminstallquestiontheme')) .
            "<input type='hidden' name='templatefolder' value='" . $this->getRelativeXmlPath() . "'>
            <input type='hidden' name='theme_type' value='" . $this->getThemeType() . "'>
            <input type='hidden' name='theme' value='questiontheme'>
            <button id='template_options_link_" . $this->name . "'class='btn btn-outline-secondary btn-block'>
            <span class='ri-download-fill'></span>
            " . gT('Install') . "
            </button>
            </form>";

        return $sLoadLink;
    }

    /**
     * Import config manifest to database.
     *
     * @param string $sXMLDirectoryPath         the relative path to the Question Theme XML directory
     * @param bool   $bSkipConversion           If converting should be skipped
     * @param bool   $bThrowConversionException If true, throws exception instead of redirecting
     * 
     * @return bool|string
     * @throws Exception
     * @todo   Please never redirect at this level, only from controllers.
     * @todo   Please refactor this function. Meaningful param names. Variable names too.
     */
    public function importManifest($sXMLDirectoryPath, $bSkipConversion = false, $bThrowConversionException = false)
    {
        if (empty($sXMLDirectoryPath)) {
            throw new InvalidArgumentException('$templateFolder cannot be empty');
        }

        // convert Question Theme
        if ($bSkipConversion === false) {
            $aConvertSuccess = self::convertLS3toLS5($sXMLDirectoryPath);
            if (!$aConvertSuccess['success']) {
                if ($bThrowConversionException) {
                    throw new Exception($aConvertSuccess['message']);
                } else {
                    App()->setFlashMessage($aConvertSuccess['message'], 'error');
                    App()->getController()->redirect(array("themeOptions/index#questionthemes"));
                }
            }
        }

        /** 
         * Question Meta Data
         * 
         * @var array 
         */
        $aQuestionMetaData = self::getQuestionMetaData($sXMLDirectoryPath);

        if (empty($aQuestionMetaData)) {
            throw new Exception('Found no question theme metadata');
        }

        /**
         * Meta Data
         * 
         * @var array<string, mixed> 
         */
        // todo proper error handling should be done before in getQuestionMetaData via validate()
        $aMetaDataArray = self::getMetaDataArray($aQuestionMetaData);

        $this->setAttributes($aMetaDataArray, false);
        if ($this->save()) {
            return $aQuestionMetaData['title'];
        } else {
            throw new Exception('Could not save question theme metadata: ' . json_encode($this->errors));
        }
    }

    /**
     * Returns question themes available in the filesystem AND installed in the database
     *
     * @return array
     * @throws Exception
     */
    public function getAvailableQuestionThemes()
    {
        $aAvailableThemes = [];
        $aThemes = $this->getAllQuestionMetaData();
        $questionsInDB = $this->findAll();

        if (!empty($aThemes['available_themes'])) {
            if (!empty($questionsInDB)) {
                foreach ($questionsInDB as $questionInDB) {
                    if (array_key_exists($questionKey = $questionInDB->name . '_' . $questionInDB->question_type, $aThemes['available_themes'])) {
                        unset($aThemes['available_themes'][$questionKey]);
                    }
                }
            }
            foreach ($aThemes['available_themes'] as $questionMetaData) {
                // TODO: replace by manifest
                $questionTheme = new QuestionTheme();
                $metaDataArray = self::getMetaDataArray($questionMetaData);
                $questionTheme->setAttributes($metaDataArray, false);
                $aAvailableThemes[] = $questionTheme;
            }
        }

        return [
            'available_themes' => $aAvailableThemes,
            'broken_themes' => $aThemes['broken_themes']
        ];
    }

    /**
     * Returns an array of all question themes and their metadata, split into available_themes and broken_themes
     *
     * @param bool $core   is Core question theme
     * @param bool $custom is custom question theme
     * @param bool $user   is user question theme
     * 
     * @return array
     * @todo   Move to service class
     * @todo   Please refactor this function. Example: More meaningfull param names.
     */
    public function getAllQuestionMetaData($core = true, $custom = true, $user = true)
    {
        $questionsMetaData = $aBrokenQuestionThemes = [];
        $questionDirectoriesAndPaths = $this->getAllQuestionXMLPaths($core, $custom, $user);
        if (isset($questionDirectoriesAndPaths) && !empty($questionDirectoriesAndPaths)) {
            foreach ($questionDirectoriesAndPaths as $directory => $questionConfigFilePaths) {
                foreach ($questionConfigFilePaths as $questionConfigFilePath) {
                    try {
                        $questionMetaData = self::getQuestionMetaData($questionConfigFilePath);
                        $questionsMetaData[$questionMetaData['name'] . '_' . $questionMetaData['questionType']] = $questionMetaData;
                    } catch (Exception $e) {
                        array_push(
                            $aBrokenQuestionThemes, [
                            'path'    => $questionConfigFilePath,
                            'exception' => $e
                            ]
                        );
                    }
                }
            }
        }
        return $aQuestionThemes = [
            'available_themes' => $questionsMetaData,
            'broken_themes'    => $aBrokenQuestionThemes
        ];
    }

    /**
     * Read all the MetaData for given Question XML definition
     *
     * @param string $pathToXmlFolder Path to XML Folder
     * 
     * @return array Question Meta Data
     * @throws Exception
     * @todo   Replace assoc array with DTO
     */
    public static function getQuestionMetaData($pathToXmlFolder)
    {
        $questionDirectories = self::getQuestionThemeDirectories();
        foreach ($questionDirectories as $key => $questionDirectory) {
            $questionDirectories[$key] = str_replace('\\', '/', (string) $questionDirectory);
        }

        $pathToXmlFolder = str_replace('\\', '/', $pathToXmlFolder);
        if (\PHP_VERSION_ID < 80000) {
            $bOldEntityLoaderState = libxml_disable_entity_loader(true);
        }
        $sQuestionConfigFilePath = $pathToXmlFolder . DIRECTORY_SEPARATOR . 'config.xml';
        if (!file_exists($sQuestionConfigFilePath)) {
            throw new Exception(sprintf(gT('Extension configuration file is missing at %s.'), $sQuestionConfigFilePath));
        }
        $sQuestionConfigFile = file_get_contents($sQuestionConfigFilePath);  // @see: Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string
        $oQuestionConfig = simplexml_load_string($sQuestionConfigFile);

        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader($bOldEntityLoaderState);
        }

        // read all metadata from the provided $pathToXmlFolder
        $questionMetaData = json_decode(json_encode($oQuestionConfig->metadata), true);
        if (!isset($questionMetaData['questionType'])) {
            throw new Exception('Missing attribute questionType in meta data');
        }

        $aQuestionThemes = QuestionTheme::model()->findAll(
            '(question_type = :question_type AND extends = :extends)',
            [
                ':question_type' => $questionMetaData['questionType'],
                ':extends'       => '',
            ]
        );
        //set extends if there is allready an existing Question with this type
        if (empty($aQuestionThemes)) {
            $questionMetaData['extends'] = '';
        } else {
            $questionMetaData['extends'] = $questionMetaData['questionType'];
        }

        // get custom previewimage if defined
        if (!empty($oQuestionConfig->files->preview->filename)) {
            $previewFileName = json_decode(json_encode($oQuestionConfig->files->preview->filename), true)[0];
            $questionMetaData['image_path'] = DIRECTORY_SEPARATOR . $pathToXmlFolder . '/assets/' . $previewFileName;
        }

        $questionMetaData['xml_path'] = $pathToXmlFolder;

        // set settings as json
        $questionMetaData['settings'] = json_encode(
            [
            'subquestions'     => $questionMetaData['subquestions'] ?? 0,
            'other'            => $questionMetaData['other'] ?? false,
            'answerscales'     => $questionMetaData['answerscales'] ?? 0,
            'hasdefaultvalues' => $questionMetaData['hasdefaultvalues'] ?? 0,
            'assessable'       => $questionMetaData['assessable'] ?? 0,
            'class'            => $questionMetaData['class'] ?? '',
            ]
        );

        // override MetaData depending on directory
        if (substr($pathToXmlFolder, 0, strlen((string) $questionDirectories[self::THEME_TYPE_CORE])) === $questionDirectories[self::THEME_TYPE_CORE]) {
            $questionMetaData['coreTheme'] = 1;
            $questionMetaData['image_path'] = App()->getConfig("imageurl") . '/screenshots/' . self::getQuestionThemeImageName($questionMetaData['questionType']);
        }
        if (substr($pathToXmlFolder, 0, strlen((string) $questionDirectories[self::THEME_TYPE_CUSTOM])) === $questionDirectories[self::THEME_TYPE_CUSTOM]) {
            $questionMetaData['coreTheme'] = 1;
        }
        if (substr($pathToXmlFolder, 0, strlen((string) $questionDirectories[self::THEME_TYPE_USER])) === $questionDirectories[self::THEME_TYPE_USER]) {
            $questionMetaData['coreTheme'] = 0;
        }

        // get Default Image if undefined
        if (empty($questionMetaData['image_path']) || !file_exists(App()->getConfig('rootdir') . $questionMetaData['image_path'])) {
            $questionMetaData['image_path'] = App()->getConfig("imageurl") . '/screenshots/' . self::getQuestionThemeImageName($questionMetaData['questionType']);
        }

        return $questionMetaData;
    }

    /**
     * Find all XML paths for specified Question Root folders
     *
     * @param bool $core   is core question theme
     * @param bool $custom is custom question theme
     * @param bool $user   is user question theme
     *
     * @return array
     * @todo   Please update PHPDoc. 
     */
    public static function getAllQuestionXMLPaths($core = true, $custom = true, $user = true)
    {
        $questionDirectories = self::getQuestionThemeDirectories();
        $questionDirectoriesAndPaths = [];
        if ($core) {
            $coreQuestionsPath = $questionDirectories[self::THEME_TYPE_CORE];
            $selectedQuestionDirectories[] = $coreQuestionsPath;
        }
        if ($custom) {
            $customQuestionThemesPath = $questionDirectories[self::THEME_TYPE_CUSTOM];
            $selectedQuestionDirectories[] = $customQuestionThemesPath;
        }
        if ($user) {
            $userQuestionThemesPath = $questionDirectories[self::THEME_TYPE_USER];
            if (!is_dir($userQuestionThemesPath)) {
                mkdir($userQuestionThemesPath, 0777, true);
            }
            $selectedQuestionDirectories[] = $userQuestionThemesPath;
        }

        if (isset($selectedQuestionDirectories)) {
            foreach ($selectedQuestionDirectories as $questionThemeDirectory) {
                $directory = new RecursiveDirectoryIterator($questionThemeDirectory);
                $iterator = new RecursiveIteratorIterator($directory);
                foreach ($iterator as $info) {
                    $ext = pathinfo((string) $info->getPathname(), PATHINFO_EXTENSION);
                    if ($ext == 'xml') {
                        $questionDirectoriesAndPaths[$questionThemeDirectory][] = dirname((string) $info->getPathname());
                    }
                }
            }
        }
        return $questionDirectoriesAndPaths;
    }


    /**
     * Uninstalls a question theme.
     * 
     * @param QuestionTheme $oQuestionTheme Question theme
     *
     * @return array|false
     * @todo   move actions to its controller and split between controller and model, related search for: 1573123789741
     * @todo   Move to QuestionThemeInstaller
     * @todo   Refactor this function. It should only return one type. Boolean would be great as return type.
     */
    public static function uninstall($oQuestionTheme)
    {
        if (!Permission::model()->hasGlobalPermission('templates', 'delete')) {
            return false;
        }

        // Don't allow deletion of core question themes (themes delivered with Lime)
        if ($oQuestionTheme->core_theme == 1) {
            return [
                'error'  => gT('Core question themes cannot be uninstalled.'),
                'result' => false
            ];
        }

        // TODO: Now that core question themes can't be deleted, the following check
        //       doesn't seem necessary because, at least for now, user question themes
        //       always extend a question type.

        // if this questiontype is extended, it cannot be deleted
        if (empty($oQuestionTheme->extends)) {
            $aQuestionThemes = self::model()->findAll(
                'extends = :extends AND NOT id = :id',
                [
                    ':extends' => $oQuestionTheme->question_type,
                    ':id'      => $oQuestionTheme->id
                ]
            );
            if (!empty($aQuestionThemes)) {
                return [
                    'error'  => gT('Question type is being extended and cannot be uninstalled'),
                    'result' => false
                ];
            };
        }

        // todo optimize function for very big surveys, eventually in yii 2 or 3 with batch processing / if this is breaking in Yii 1 use CDbDataReader $query = new CDbDataReader($command), $query->read()
        $aQuestions = Question::model()->count(
            'type = :type AND question_theme_name = :theme AND parent_qid = :parent_qid',
            [
                ':type'       => $oQuestionTheme->question_type,
                ':theme'      => $oQuestionTheme->name,
                ':parent_qid' => 0
            ]
        );
        if (!empty($aQuestions)) {
            // There are questions using this theme. Don't delete it
            $bDeleteTheme = false;
        }

        // Just in case, if this is a core (base) theme we also check if there are any questions without theme name (this shouldn't happen)
        if (empty($oQuestionTheme->extends) && $bDeleteTheme !== false) {
            $aQuestions = Question::model()->findAll(
                "type = :type AND (question_theme_name = '' OR question_theme_name IS NULL) AND parent_qid = :parent_qid",
                [
                    ':type'       => $oQuestionTheme->question_type,
                    ':parent_qid' => 0
                ]
            );
            if (!empty($aQuestions)) {
                // There are questions using this theme. Don't delete it
                $bDeleteTheme = false;
            }
        }

        // if this questiontheme is used, it cannot be deleted
        if (isset($bDeleteTheme) && !$bDeleteTheme) {
            return [
                'error'  => gT('Question theme is used in a Survey and cannot be uninstalled'),
                'result' => false
            ];
        }

        // delete questiontheme if it is not used
        try {
            return [
                'result' => $oQuestionTheme->delete()
            ];
        } catch (CDbException $e) {
            return [
                'error'  => $e->getMessage(),
                'result' => false
            ];
        }
    }

    /**
     * Returns all base question themes as an array indexed by question type
     * (all entries in table question_themes extends='')
     *
     * @return array<string, QuestionTheme>
     */
    public static function findQuestionMetaDataForAllTypes()
    {
        // Getting all question_types which are NOT extended
        /** 
         * Question Theme
         *
         * @var QuestionTheme[] $baseQuestions 
         */
        $baseQuestions = self::model()->findAllByAttributes(['extends' => '']);
        $aQuestionsIndexedByType = [];

        foreach ($baseQuestions as $baseQuestion) {
            $baseQuestion->settings = json_decode((string) $baseQuestion['settings']);
            $aQuestionsIndexedByType[$baseQuestion->question_type] = $baseQuestion;
        }

        return $aQuestionsIndexedByType;
    }

    /**
     * Returns all QuestionTheme settings
     *
     * @param string $question_type       Question theme
     * @param string $question_theme_name Name of the question theme
     * @param string $language            Language
     * 
     * @return QuestionTheme
     */
    public static function findQuestionMetaData($question_type, $question_theme_name = null, $language = '')
    {
        if (empty($question_type)) {
            throw new InvalidArgumentException('question_type cannot be empty');
        }

        if (empty($question_theme_name) || $question_theme_name === 'core') {
            $questionTheme = self::model()->base()->findByAttributes(['question_type' => $question_type]);
        } else {
            $criteria = new CDbCriteria();
            $criteria->addCondition('question_type = :question_type AND name = :name');
            $criteria->params = [':question_type' => $question_type, ':name' => $question_theme_name];
            $questionTheme = self::model()->query($criteria, false);
        }

        if (empty($questionTheme)) {
            return self::getDummyInstance($question_type);
        }

        // language settings
        $questionTheme->title = gT($questionTheme->title, "html", $language);
        $questionTheme->group = gT($questionTheme->group, "html", $language);

        // decode settings json
        $questionTheme->settings = json_decode((string) $questionTheme->settings);

        return $questionTheme;
    }

    /**
     * Returns all Question Meta Data for the question type selector
     *
     * @return QuestionTheme[]
     */
    public static function findAllQuestionMetaDataForSelector()
    {
        $criteria = new CDbCriteria();
        //            $criteria->condition = 'extends = :extends';
        $criteria->addCondition('visible = :visible', 'AND');
        $criteria->params = [':visible' => 'Y'];

        /**
         * QuestionTheme
         * 
         * @var QuestionTheme[] 
         */
        $baseQuestions = self::model()->query($criteria, true);

        if (\PHP_VERSION_ID < 80000) {
            $bOldEntityLoaderState = libxml_disable_entity_loader(true);
        }

        $baseQuestionsModified = [];
        foreach ($baseQuestions as $baseQuestion) {
            //TODO: should be moved into DB column (question_theme_settings table)
            $sQuestionConfigFile = @file_get_contents($baseQuestion->getXmlPath() . DIRECTORY_SEPARATOR . 'config.xml');  // @see: Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string
            if (!$sQuestionConfigFile) {
                /* Not readable file : don't break */
                continue;
            }
            $oQuestionConfig = simplexml_load_string($sQuestionConfigFile);
            $questionEngineData = json_decode(json_encode($oQuestionConfig->engine), true);
            $showAsQuestionType = $questionEngineData['show_as_question_type'];

            // if an extended Question should not be shown as a selectable questiontype skip it
            if (!empty($baseQuestion['extends']) && !$showAsQuestionType) {
                continue;
            }

            // language settings
            $baseQuestion['title'] = gT($baseQuestion['title'], "html");
            $baseQuestion['group'] = gT($baseQuestion['group'], "html");

            // decode settings json
            $baseQuestion['settings'] = json_decode((string) $baseQuestion['settings']);

            $baseQuestion['image_path'] = str_replace(
                '//',
                '/',
                Yii::app()->baseUrl . '/' . $baseQuestion['image_path']
            );
            $baseQuestionsModified[] = $baseQuestion;
        }
        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader($bOldEntityLoaderState);
        }

        $baseQuestions = $baseQuestionsModified;

        return $baseQuestions;
    }

    /**
     * Returns the directories for the question theme.
     * 
     * @return array
     */
    public static function getQuestionThemeDirectories()
    {
        $questionThemeDirectories[self::THEME_TYPE_CORE] = App()->getConfig('corequestiontypedir') . '/survey/questions/answer';
        $questionThemeDirectories[self::THEME_TYPE_CUSTOM] = App()->getConfig('customquestionthemedir');
        $questionThemeDirectories[self::THEME_TYPE_USER] = App()->getConfig('userquestionthemerootdir');

        return $questionThemeDirectories;
    }

    /**
     * Returns QuestionMetaData Array for use in ->save operations
     *
     * @param array $questionMetaData Question Meta Data
     *
     * @return array $questionMetaData
     * @todo   Naming is wrong, it does not "get", it "convertTo"
     * @todo   Possibly make a DTO for question metadata instead, and implement the ArrayAccess interface or "toArray()"
     */
    public static function getMetaDataArray($questionMetaData)
    {
        $questionMetaData = [
            'name'          => $questionMetaData['name'],
            'visible'       => 'Y',
            'xml_path'      => $questionMetaData['xml_path'],
            'image_path'    => $questionMetaData['image_path'] ?? '',
            'title'         => $questionMetaData['title'] ?? '',
            'creation_date' => date('Y-m-d H:i:s', strtotime((string) $questionMetaData['creationDate'])),
            'author'        => $questionMetaData['author'] ?? '',
            'author_email'  => $questionMetaData['authorEmail'] ?? '',
            'author_url'    => $questionMetaData['authorUrl'] ?? '',
            'copyright'     => $questionMetaData['copyright'] ?? '',
            'license'       => $questionMetaData['license'] ?? '',
            'version'       => $questionMetaData['version'],
            'api_version'   => $questionMetaData['apiVersion'],
            'description'   => $questionMetaData['description'],
            'last_update'   => date('Y-m-d H:i:s'), //todo
            'owner_id'      => 1, //todo
            'theme_type'    => $questionMetaData['type'],
            'question_type' => $questionMetaData['questionType'],
            'core_theme'    => $questionMetaData['coreTheme'],
            'extends'       => $questionMetaData['extends'],
            'group'         => $questionMetaData['group'] ?? '',
            'settings'      => $questionMetaData['settings'] ?? ''
        ];
        return $questionMetaData;
    }

    /**
     * Return the question Theme preview URL
     *
     * @param $sType : type of question
     *
     * @return string : question theme preview URL
     */
    public static function getQuestionThemeImageName($sType = null)
    {
        if ($sType == '*') {
            $preview_filename = 'EQUATION.png';
        } elseif ($sType == ':') {
            $preview_filename = 'COLON.png';
        } elseif ($sType == '|') {
            $preview_filename = 'PIPE.png';
        } elseif (!empty($sType)) {
            $preview_filename = $sType . '.png';
        } else {
            $preview_filename = '.png';
        }

        return $preview_filename;
    }

    /**
     * Returns the answer column definition for a given question theme and type.
     *
     * The result is read from the question theme's config.xml and cached per
     * (name, type) combination. If no definition is found, an empty string
     * is returned.
     *
     * @param string $name Question theme name, or empty/core to use the base theme.
     * @param string $type Question type code used to look up the theme.
     *
     * @return string  The answer column definition, or an empty string if none is defined.
     */
    public static function getAnswerColumnDefinition($name, $type)
    {
        // cache the value between function calls
        static $cacheMemo = [];
        $cacheKey = $name . '_' . $type;
        if (isset($cacheMemo[$cacheKey])) {
            return $cacheMemo[$cacheKey];
        }

        if (empty($name) || $name == 'core') {
            $questionTheme = self::model()->base()->findByAttributes(['question_type' => $type, 'extends' => '']);
        } else {
            $questionTheme = self::model()->findByAttributes([], 'name=:name AND question_type=:question_type', ['name' => $name, 'question_type' => $type]);
        }

        $answerColumnDefinition = '';
        $xmlPath = $questionTheme->getXmlPath();
        if (isset($xmlPath)) {
            if (\PHP_VERSION_ID < 80000) {
                $bOldEntityLoaderState = libxml_disable_entity_loader(true);
            }
            // If xml_path is relative, cwd is assumed to be ROOTDIR.
            // TODO: Make it always relative depending on question theme type (core, custom, user).
            $sQuestionConfigFile = file_get_contents($xmlPath . DIRECTORY_SEPARATOR . 'config.xml');  // @see: Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string
            $oQuestionConfig = simplexml_load_string($sQuestionConfigFile);
            if (isset($oQuestionConfig->metadata->answercolumndefinition)) {
                // TODO: Check json_last_error.
                $answerColumnDefinition = json_decode(json_encode($oQuestionConfig->metadata->answercolumndefinition), true)[0];
            }

            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader($bOldEntityLoaderState);
            }
        }

        $cacheMemo[$cacheKey] = $answerColumnDefinition;
        return $answerColumnDefinition;
    }

    /**
     * Returns the Config Path for the selected Question Type base definition
     *
     * @param string $type Question type
     *
     * @return string Path to config XML
     * @throws CException
     */
    public static function getQuestionXMLPathForBaseType($type)
    {
        /**
         * Question Theme
         *
         * @var QuestionTheme|null 
         */
        $questionTheme = QuestionTheme::model()->findByAttributes([], 'question_type = :question_type AND extends = :extends', ['question_type' => $type, 'extends' => '']);
        if (empty($questionTheme)) {
            throw new \CException("The Database definition for Questiontype: " . $type . " is missing");
        }
        $configXMLPath = App()->getConfig('rootdir') . '/' . $questionTheme->getXmlPath() . '/config.xml';

        return $configXMLPath;
    }

    /**
     * Converts LS3 Question Theme to LS5
     *
     * @param string $sXMLDirectoryPath Path to XML
     *
     * @return array $success Returns an array with the conversion status
     */
    public static function convertLS3toLS5($sXMLDirectoryPath)
    {
        $sXMLDirectoryPath = str_replace('\\', '/', $sXMLDirectoryPath);
        $sConfigPath = $sXMLDirectoryPath . DIRECTORY_SEPARATOR . 'config.xml';
        if (\PHP_VERSION_ID < 80000) {
            $bOldEntityLoaderState = libxml_disable_entity_loader(true);
        }

        $sQuestionConfigFilePath = $sConfigPath;
        if (!file_exists($sQuestionConfigFilePath)) {
            throw new Exception('Found no config.xml file at ' . $sQuestionConfigFilePath);
        }
        $sQuestionConfigFile = file_get_contents($sQuestionConfigFilePath);  // @see: Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string

        if (!$sQuestionConfigFile) {
            if (\PHP_VERSION_ID < 80000) {
                libxml_disable_entity_loader($bOldEntityLoaderState);
            }
            return $aSuccess = [
                'message' => sprintf(
                    gT('Configuration file %s could not be found or read.'),
                    $sConfigPath
                ),
                'success' => false
            ];
        }

        // replace custom_attributes with attributes
        if (preg_match('/<custom_attributes>/', $sQuestionConfigFile)) {
            $sQuestionConfigFile = preg_replace('/<custom_attributes>/', '<attributes>', $sQuestionConfigFile);
            $sQuestionConfigFile = preg_replace('/<\/custom_attributes>/', '</attributes>', $sQuestionConfigFile);
        };
        $oThemeConfig = simplexml_load_string($sQuestionConfigFile);
        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader($bOldEntityLoaderState);
        }

        // get type from core theme
        if (isset($oThemeConfig->metadata->type)) {
            $oThemeConfig->metadata->type = 'question_theme';
        } else {
            $oThemeConfig->metadata->addChild('type', 'question_theme');
        };

        // set compatibility version
        if ($oThemeConfig->compatibility->version
            && count($oThemeConfig->compatibility->version) > 1
        ) {
            $length = count($oThemeConfig->compatibility->version);
            $compatibility = $oThemeConfig->addChild('compatibility');
            $compatibility->addChild('version');
            $oThemeConfig->compatibility->version[$length] = '5.0';
        } elseif ($oThemeConfig->compatibility->version
            && count($oThemeConfig->compatibility->version) === 1
        ) {
            $oThemeConfig->compatibility->version = '5.0';
        } else {
            $compatibility = $oThemeConfig->addChild('compatibility');
            $compatibility->addChild('version');
            $oThemeConfig->compatibility->version = '5.0';
        }

        $sThemeDirectoryName = self::getThemeDirectoryPath($sQuestionConfigFilePath);
        $sPathToCoreConfigFile = str_replace('\\', '/', App()->getConfig('rootdir') . '/application/views/survey/questions/answer/' . $sThemeDirectoryName . '/config.xml');
        // check if core question theme can be found to fill in missing information
        if (!is_file($sPathToCoreConfigFile)) {
            return $aSuccess = [
                'message' => sprintf(
                    gT("Question theme could not be converted to the latest LimeSurvey version. Reason: No matching core theme with the name %s could be found"),
                    $sThemeDirectoryName
                ),
                'success' => false
            ];
        }
        if (\PHP_VERSION_ID < 80000) {
            $bOldEntityLoaderState = libxml_disable_entity_loader(true);
        }
        $sThemeCoreConfigFile = file_get_contents($sPathToCoreConfigFile);  // @see: Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string
        $oThemeCoreConfig = simplexml_load_string($sThemeCoreConfigFile);
        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader($bOldEntityLoaderState);
        }

        // get questiontype from core if it is missing
        if (!isset($oThemeConfig->metadata->questionType)) {
            $oThemeConfig->metadata->addChild('questionType', $oThemeCoreConfig->metadata->questionType);
        };

        // search missing new tags and copy theme from the core theme
        $aNewMetadataTagsToRecoverFromCoreType = ['group', 'subquestions', 'answerscales', 'hasdefaultvalues', 'assessable', 'class'];
        foreach ($aNewMetadataTagsToRecoverFromCoreType as $sMetaTag) {
            if (!isset($oThemeConfig->metadata->$sMetaTag)) {
                $oThemeConfig->metadata->addChild($sMetaTag, $oThemeCoreConfig->metadata->$sMetaTag);
            }
        }

        // write everything back to to xml file
        $oThemeConfig->saveXML($sQuestionConfigFilePath);

        return $aSuccess = [
            'message' => gT('Question theme has been successfully converted to the latest LimeSurvey version.'),
            'success' => true
        ];
    }

    /**
     * Return the question theme custom attributes values
     * -- gets coreAttributes from xml-file
     * -- gets additional attributes from extended theme (if theme is extended)
     * -- gets "own" attributes via plugin
     *
     * @param string $type               question type (this is the attribute 'question_type' in table question_theme)
     * @param string $sQuestionThemeName : question theme name
     *
     * @return array : the attribute settings for this question type
     * @throws Exception when question type attributes are not available
     */
    public static function getQuestionThemeAttributeValues($type, $sQuestionThemeName = null)
    {
        $aQuestionAttributes = array();
        $xmlConfigPath = self::getQuestionXMLPathForBaseType($type);

        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader(false);
        }
        $oCoreConfig = simplexml_load_file($xmlConfigPath);
        $aCoreAttributes = json_decode(json_encode((array)$oCoreConfig), true);
        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader(true);
        }
        if (!isset($aCoreAttributes['attributes']['attribute'])) {
            throw new Exception("Question type attributes not available!");
        }
        foreach ($aCoreAttributes['attributes']['attribute'] as $aCoreAttribute) {
            $aQuestionAttributes[$aCoreAttribute['name']] = $aCoreAttribute;
        }

        $additionalAttributes = array();
        if ($sQuestionThemeName !== null) {
            $additionalAttributes = self::getAdditionalAttrFromExtendedTheme($sQuestionThemeName, $type);
        }

        return array_merge(
            $aQuestionAttributes,
            $additionalAttributes,
            QuestionAttribute::getOwnQuestionAttributesViaPlugin()
        );
    }

    /**
     * Gets the additional attributes for an extended theme from xml file.
     * If there are no attributes, an empty array is returned
     *
     * @param string $sQuestionThemeName the question theme name (see table question theme "name")
     * @param string $type               the extended typ (see table question_themes "extends")
     * 
     * @return array additional attributes for an extended theme or empty array
     */
    public static function getAdditionalAttrFromExtendedTheme($sQuestionThemeName, $type)
    {
        $additionalAttributes = array();
        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader(false);
        }
        $questionTheme = QuestionTheme::model()->findByAttributes([], 'name = :name AND extends = :extends', ['name' => $sQuestionThemeName, 'extends' => $type]);
        if ($questionTheme !== null) {
            $xml_config = simplexml_load_file($questionTheme->getXmlPath() . '/config.xml');
            $attributes = json_decode(json_encode((array)$xml_config->attributes), true);
        }
        if (\PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader(true);
        }

        if (!empty($attributes)) {
            if (!empty($attributes['attribute']['name'])) {
                // Only one attribute set in config: need an array of attributes
                $attributes['attribute'] = array($attributes['attribute']);
            }
            // Create array of attribute with name as key
            $defaultQuestionAttributeValues = QuestionAttribute::getDefaultSettings();
            foreach ($attributes['attribute'] as $attribute) {
                if (!empty($attribute['name'])) {
                    // inputtype is text by default
                    $additionalAttributes[$attribute['name']] = array_merge($defaultQuestionAttributeValues, $attribute);
                }
            }
        }

        $questionAttributeHelper = new LimeSurvey\Models\Services\QuestionAttributeHelper();
        $additionalAttributes = $questionAttributeHelper->fillMissingCategory($additionalAttributes, gT('Template'));
        $additionalAttributes = $questionAttributeHelper->sanitizeQuestionAttributes($additionalAttributes);

        return $additionalAttributes;
    }

    /**
     * Extracts the question theme directory name from a config.xml path.
     *
     * Example:
     *   /path/to/questions/answer/multiplechoice/config.xml
     *   → returns "multiplechoice"
     *
     * If the path does not match the expected pattern, an empty string is returned.
     *
     * @param string $sQuestionConfigFilePath Full filesystem path to a question's config.xml file.
     *
     * @return string  The extracted theme directory name, or an empty string if no match is found.
     *
     * @todo Consider switching to a safer delimiter in the regex
     *       (e.g. "#...#" instead of "$...$") for better readability.
     */
    public static function getThemeDirectoryPath($sQuestionConfigFilePath)
    {
        $sQuestionConfigFilePath = str_replace('\\', '/', (string) $sQuestionConfigFilePath);
        $aMatches = array();
        $sThemeDirectoryName = '';
        if (preg_match('$questions/answer/(.*)/config.xml$', $sQuestionConfigFilePath, $aMatches)) {
            $sThemeDirectoryName = $aMatches[1];
        }
        return $sThemeDirectoryName;
    }

    /**
     * Returns the name of the base question theme for the question type $questionType
     *
     * @param string $questionType Question Type
     * 
     * @return string|null question theme name or null if no question theme is found
     */
    public function getBaseThemeNameForQuestionType($questionType)
    {
        $questionTheme = $this->base()->findByAttributes(['question_type' => $questionType]);
        if (!empty($questionTheme)) {
            return $questionTheme->name;
        }
    }

    /**
     * Returns the settings attribute decoded
     *
     * @return mixed
     */
    public function getDecodedSettings()
    {
        if (is_object($this->settings)) {
            return $this->settings;
        } else {
            return json_decode($this->settings);
        }
    }

    /**
     * Returns a dummy instance of QuestionTheme, with
     * the question type $questionType.
     *
     * @param string $questionType Type of question
     * 
     * @return QuestionTheme
     */
    public static function getDummyInstance($questionType)
    {
        $settings = new StdClass();
        $settings->class = '';
        $settings->answerscales = 0;
        $settings->subquestions = 0;

        $questionTheme = new self();
        $questionTheme->title = gT('Question theme error: Missing metadata');
        $questionTheme->name = gT('Question theme error: Missing metadata');
        $questionTheme->question_type = $questionType;
        $questionTheme->settings = $settings;
        return $questionTheme;
    }

    /**
     * Returns the type of question theme (coreQuestion, customCoreTheme, customUserTheme)
     *
     * CoreQuestion = Themes shipped with Limesurvey that don't extend other theme
     * customCoreTheme = Themes shipped with Limesurvey that extend other theme
     * customUserTheme = User provided question themes
     *
     * @return string
     */
    public function getThemeType()
    {
        if ($this->core_theme) {
            if (empty($this->extends)) {
                return self::THEME_TYPE_CORE;
            } else {
                return self::THEME_TYPE_CUSTOM;
            }
        } else {
            return self::THEME_TYPE_USER;
        }
    }

    /**
     * Returns the XML path relative to the path configured for the question theme type
     *
     * @return string
     */
    public function getRelativeXmlPath()
    {
        $type = $this->getThemeType();
        $typeDirectory = self::getQuestionThemeDirectoryForType($type);

        // xml_path is supposed to contain the type directory, so we extract the rest of the path
        $relativePath = substr($this->xml_path, strpos($this->xml_path, $typeDirectory) + strlen($typeDirectory));
        $relativePath = ltrim($relativePath, "\\/");
        return $relativePath;
    }

    /**
     * Returns the XML path
     * It may be absolute or relative to the Limesurvey root
     *
     * @return string
     */
    public function getXmlPath()
    {
        return $this->xml_path;
    }

    /**
     * Returns the path for the specified question theme type
     *
     * @param string $themeType Type of theme
     * 
     * @return string
     * @throws Exception if no directory is found for the given type
     */
    public static function getQuestionThemeDirectoryForType($themeType)
    {
        $directories = self::getQuestionThemeDirectories();
        if (!isset($directories[$themeType])) {
            throw new Exception(sprintf(gT("No question theme directory found for theme type '%s'"), $themeType));
        }
        return $directories[$themeType];
    }

    /**
     * Returns the corresponding absolute path, given a relative path and the theme type.
     *
     * @param string $relativePath Relative Path
     * @param string $themeType    Type of theme
     * 
     * @return string
     */
    public static function getAbsolutePathForType($relativePath, $themeType)
    {
        return self::getQuestionThemeDirectoryForType($themeType) . '/' . $relativePath;
    }
}
