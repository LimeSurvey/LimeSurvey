<?php

    /**
     * Class QuestionThemes
     *
     * @property int     $id
     * @property string  $name   Template name
     * @property string  $folder Template folder name eg: 'default'
     * @property string  $title
     * @property string  $creation_date
     * @property string  $author
     * @property string  $author_email
     * @property string  $author_url
     * @property string  $copyright
     * @property string  $license
     * @property string  $version
     * @property string  $view_folder
     * @property string  $files_folder
     * @property string  $description
     * @property string  $last_update
     * @property integer $owner_id
     * @property string  $theme_type
     * @property string  $type
     * @property string  $extends
     */
    class QuestionTheme extends LSActiveRecord
    {
        /**
         * Returns the static model of the specified AR class.
         * Please note that you should have this exact method in all your CActiveRecord descendants!
         *
         * @param string $className active record class name.
         *
         * @return Template the static model class
         */
        public static function model($className = __CLASS__)
        {
            return parent::model($className);
        }

        /**
         * @return string the associated database table name
         */
        public function tableName()
        {
            return '{{question_themes}}';
        }

        /**
         * @return array relational rules.
         */
        public function relations()
        {
            return array();
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

        public function rules()
        {
            return [
                [
                    'name',
                    'unique',
                    'caseSensitive' => false,
                    'criteria' => [
                        'condition' => '`extends`=:extends',
                        'params' => [
                            ':extends' => $this->extends
                        ]
                    ],
                ]
            ];
        }

        /**
         * Import all Questiontypes and Themes to the {{questions_themes}} table
         *
         * @param array $questionThemeDirectories
         *
         * @throws CException
         */
        public static function loadAllQuestionXMLConfigurationsIntoDatabase($questionThemeDirectories = null)
        {
            $questionDirectoriesAndPaths = [];
            $missingQuestionThemeAttributes = [];
            $coreQuestionsPath = App()->getConfig('corequestiontypedir') . '/survey/questions/answer';
            $customQuestionThemesPath = App()->getConfig('userquestionthemedir');
            $userQuestionThemesPath = App()->getConfig('userquestionthemerootdir');
            if (empty($questionThemeDirectories)) {
                if (!is_dir($userQuestionThemesPath)) {
                    mkdir($userQuestionThemesPath);
                }
                $questionThemeDirectories = [$coreQuestionsPath, $customQuestionThemesPath, $userQuestionThemesPath];
            }

            // Search all Question Theme Directories
            if (is_array($questionThemeDirectories) && !empty($questionThemeDirectories)) {

                foreach ($questionThemeDirectories as $questionThemeDirectory) {
                    $directory = new RecursiveDirectoryIterator($questionThemeDirectory);
                    $iterator = new RecursiveIteratorIterator($directory);
                    foreach ($iterator as $info) {
                        $ext = pathinfo($info->getPathname(), PATHINFO_EXTENSION);
                        if ($ext == 'xml') {
                            $questionDirectoriesAndPaths[$questionThemeDirectory][] = $info->getPathname();
                        }
                    }
                }
                // @see: http://phpsecurity.readthedocs.io/en/latest/Injection-Attacks.html#xml-external-entity-injection
                $bOldEntityLoaderState = libxml_disable_entity_loader(true);
                // process XML Question Files
                if (isset($questionDirectoriesAndPaths) && !empty($questionDirectoriesAndPaths)) {
//                    try {
                    $transaction = App()->db->beginTransaction();
                    foreach ($questionDirectoriesAndPaths as $directory => $questionConfigFilePaths) {
                        foreach ($questionConfigFilePaths as $questionConfigFilePath) {
                            $sQuestionConfigFile = file_get_contents(realpath($questionConfigFilePath));  // @see: Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string
                            $oQuestionConfig = simplexml_load_string($sQuestionConfigFile);
                            $questionMetaDatas = json_decode(json_encode($oQuestionConfig->metadata), true);

                            // set type and check if question is extended
                            $questionType = '';
                            $extendedQuestion = $questionMetaDatas['type'];
                            if ($directory == $coreQuestionsPath) {
                                $extendedQuestion = '';
                                $questionType = gT('Core theme');
                            }
                            if ($directory == $customQuestionThemesPath) {
                                $questionType = gT('Core theme');
                            }
                            if ($directory == $userQuestionThemesPath) {
                                $questionType = gT('User theme');
                            }

                            // test xml for required metaData
                            {
                                $requiredMetaDataArray = ['name', 'title', 'creationDate', 'author', 'authorEmail', 'authorUrl', 'copyright', 'copyright', 'license', 'version', 'apiVersion', 'description', 'type'];
                            }
                            foreach ($requiredMetaDataArray as $requiredMetaData) {
                                if (!array_key_exists($requiredMetaData, $questionMetaDatas)) {
                                    $missingQuestionThemeAttributes[$questionConfigFilePath][] = $requiredMetaData;
                                }
                            }
                            $questionTheme = QuestionTheme::model()->find('name=:name AND extends=:extends', [':name' => $questionMetaDatas['name'], ':extends' => $extendedQuestion]);
                            if ($questionTheme == null) {
                                $questionTheme = new QuestionTheme();
                            }
                            $questionTheme->setAttributes([
                                'name' => $questionMetaDatas['name'],
                                'visible' => 'Y',
                                'folder' => $questionConfigFilePath,
                                'title' => $questionMetaDatas['title'],
                                'creation_date' => $questionMetaDatas['creationDate'],
                                'author' => $questionMetaDatas['author'],
                                'author_email' => $questionMetaDatas['authorEmail'],
                                'author_url' => $questionMetaDatas['authorUrl'],
                                'copyright' => $questionMetaDatas['copyright'],
                                'license' => $questionMetaDatas['license'],
                                'version' => $questionMetaDatas['version'],
                                'api_version' => $questionMetaDatas['apiVersion'],
                                'description' => $questionMetaDatas['description'],
                                'last_update' => 'now',
                                'owner_id' => 1,
                                'theme_type' => $questionType,
                                'type' => $questionMetaDatas['type'],
                                'extends' => $extendedQuestion
                            ], false);
                            $questionTheme->save();
                        }
                    }
                    $transaction->commit();
//                    } catch (Exception $e) {
//                        //TODO: flashmessage for users
//                        echo $e->getMessage();
//                        var_dump($e->getTrace());
//                        echo $missingQuestionThemeAttributes;
//                        $transaction->rollback();
//                    }
                }
            }
            // Put back entity loader to its original state, to avoid contagion to other applications on the server
            libxml_disable_entity_loader($bOldEntityLoaderState);
        }

        public function search()
        {
            $pageSizeTemplateView = App()->user->getState('pageSizeTemplateView', App()->params['defaultPageSize']);

            $criteria = new CDbCriteria;
            return new CActiveDataProvider($this, array(
                'criteria' => $criteria,
                'pagination' => array(
                    'pageSize' => $pageSizeTemplateView,
                ),
            ));
        }


        // TODO: Enable when Configuration Model is ready
        public function getButtons()
        {
//            // don't show any buttons if user doesn't have update permission
//            if (!Permission::model()->hasGlobalPermission('templates', 'update')) {
//                return '';
//            }
//            $gsid = Yii::app()->request->getQuery('id', null);
//            $sEditorUrl = Yii::app()->getController()->createUrl('admin/themes/sa/view', array("templatename" => $this->template_name));
//            $sExtendUrl = Yii::app()->getController()->createUrl('admin/themes/sa/templatecopy');
//            $sOptionUrl = (App()->getController()->action->id == "surveysgroups") ? Yii::app()->getController()->createUrl('admin/themeoptions/sa/updatesurveygroup', array("id" => $this->id, "gsid" => $gsid)) : Yii::app()->getController()->createUrl('admin/themeoptions/sa/update', array("id" => $this->id));
//
//            $sEditorLink = "<a
//            id='template_editor_link_" . $this->template_name . "'
//            href='" . $sEditorUrl . "'
//            class='btn btn-default btn-block'>
//                <span class='icon-templates'></span>
//                " . gT('Theme editor') . "
//            </a>";
//
//            $OptionLink = '';
//            if ($this->hasOptionPage) {
//                $OptionLink .= "<a
//                id='template_options_link_" . $this->template_name . "'
//                href='" . $sOptionUrl . "'
//                class='btn btn-default btn-block'>
//                    <span class='fa fa-tachometer'></span>
//                    " . gT('Theme options') . "
//                </a>";
//            }
//
//
//            $sExtendLink = '<a
//            id="extendthis_' . $this->template_name . '"
//            href="' . $sExtendUrl . '"
//            data-post=\''
//                . json_encode([
//                    "copydir" => $this->template_name,
//                    "action" => "templatecopy",
//                    "newname" => ["value" => "extends_" . $this->template_name, "type" => "text", "class" => "form-control col-sm-12"]
//                ])
//                . '\'
//            data-text="' . gT('Please type in the new theme name above.') . '"
//            title="' . sprintf(gT('Type in the new name to extend %s'), $this->template_name) . '"
//            class="btn btn-primary btn-block selector--ConfirmModal">
//                <i class="fa fa-copy"></i>
//                ' . gT('Extend') . '
//            </a>';
//
//
//            if (App()->getController()->action->id == "surveysgroups") {
//                $sButtons = $OptionLink;
//            } else {
//                $sButtons = $sEditorLink . $OptionLink . $sExtendLink;
//
//            }
//
//
//
//
//            return $sButtons;
        }

        /**
         * Install Button for the available questions
         */
        public function getManifestButtons()
        {
            $sLoadLink = CHtml::form(array("/admin/themeoptions/sa/importmanifest/"), 'post', array('id' => 'forminstallquestiontheme', 'name' => 'forminstallquestiontheme')) .
                "<input type='hidden' name='templatefolder' value='" . $this->folder . "'>
                <input type='hidden' name='theme' value='questiontheme'>
                <button id='template_options_link_" . $this->name . "'class='btn btn-default btn-block'>
                    <span class='fa fa-download text-warning'></span>
                    " . gT('Install') . "
                </button>
                </form>";

            return $sLoadLink;
        }

        /**
         * @param $pathToXML
         *
         * @return bool
         */
        public static function importManifest($pathToXML)
        {
            if (empty($pathToXML)) {
                throw new InvalidArgumentException('$templateFolder cannot be empty');
            }

            $questionMetaData = self::getQuestionMetaData($pathToXML);
            $questionAdditionalData = self::getThemeTypeAndExtendedType($pathToXML, $questionMetaData);
            $questionMetaData = array_merge($questionMetaData, $questionAdditionalData);

            $questionTheme = QuestionTheme::model()->find('name=:name AND extends=:extends', [':name' => $questionMetaData['name'], ':extends' => $questionMetaData['type']]);
            if ($questionTheme == null) {
                $questionTheme = new QuestionTheme();

                $questionTheme->setAttributes([
                    'name' => $questionMetaData['name'],
                    'visible' => 'Y',
                    'folder' => $pathToXML,
                    'title' => $questionMetaData['title'],
                    'creation_date' => $questionMetaData['creationDate'],
                    'author' => $questionMetaData['author'],
                    'author_email' => $questionMetaData['authorEmail'],
                    'author_url' => $questionMetaData['authorUrl'],
                    'copyright' => $questionMetaData['copyright'],
                    'license' => $questionMetaData['license'],
                    'version' => $questionMetaData['version'],
                    'api_version' => $questionMetaData['apiVersion'],
                    'description' => $questionMetaData['description'],
                    'last_update' => 'todo insert time',
                    'owner_id' => 1,
                    'theme_type' => $questionMetaData['themeType'],
                    'type' => $questionMetaData['type'],
                    'extends' => $questionMetaData['extends']
                ], false);
                if ($questionTheme->save()) {
                    return $questionMetaData['title'];
                };
            }
            return null;
        }

        /**
         * @param $pathToXML         *
         * @param $questionMetaData
         *
         * @return string
         */
        public static function getThemeTypeAndExtendedType($pathToXML, $questionMetaData)
        {
            $questionAdditionalData = [];
            $coreQuestion = App()->getConfig('corequestiontypedir') . '/survey/questions/answer';
            $customCoreTheme = App()->getConfig('userquestionthemedir');
            $customUserTheme = App()->getConfig('userquestionthemerootdir');

            $questionAdditionalData['extends'] = $questionMetaData['type'];
            if (substr($pathToXML, 0, strlen($coreQuestion)) === $coreQuestion) {
                $questionAdditionalData['themeType'] = 'Core theme';
                $questionAdditionalData['extends'] = '';
            }
            if (substr($pathToXML, 0, strlen($customCoreTheme)) === $customCoreTheme) {
                $questionAdditionalData['themeType'] = 'Core theme';
            }
            if (substr($pathToXML, 0, strlen($customUserTheme)) === $customUserTheme) {
                $questionAdditionalData['themeType'] = 'User theme';
            }
            return $questionAdditionalData;
        }


        /**
         * Returns all Questions that can be installed
         */
        public function getAvailableQuestions()
        {
            $questionThemes = $installedQuestions = $availableQuestions = $questionKeys = [];
            $questionsMetaData = $this->getAllQuestionMetaData();
            $questionsInDB = $this->findAll();

            foreach ($questionsInDB as $questionInDB) {
                if (array_key_exists($questionKey = $questionInDB->name . '_' . $questionInDB->type, $questionsMetaData)) {
                    unset($questionsMetaData[$questionKey]);
                }
            }
            array_values($questionsMetaData);
            foreach ($questionsMetaData as $questionMetaData) {

                // TODO: replace by manifest
                $questionTheme = new QuestionTheme();
                $questionTheme->setAttributes([
                    'name' => $questionMetaData['name'],
                    'visible' => 'Y',
                    'folder' => $questionMetaData['folder'],
                    'title' => $questionMetaData['title'],
                    'creation_date' => $questionMetaData['creationDate'],
                    'author' => $questionMetaData['author'],
                    'author_email' => $questionMetaData['authorEmail'],
                    'author_url' => $questionMetaData['authorUrl'],
                    'copyright' => $questionMetaData['copyright'],
                    'license' => $questionMetaData['license'],
                    'version' => $questionMetaData['version'],
                    'api_version' => $questionMetaData['apiVersion'],
                    'description' => $questionMetaData['description'],
                    'last_update' => 'todo now',
                    'owner_id' => 1,
                    'theme_type' => 'XML Theme',
                    'type' => $questionMetaData['type'],
                    'extends' => $questionMetaData['extends']
                ], false);
                $questionThemes[] = $questionTheme;
            }

            return $questionThemes;
        }

        /**
         * @return array
         */
        public function getAllQuestionMetaData()
        {
            $questionsMetaData = [];
            $bOldEntityLoaderState = libxml_disable_entity_loader(true);
            $questionDirectoriesAndPaths = $this->getAllQuestionXMLPaths();
            $coreQuestionsPath = App()->getConfig('corequestiontypedir') . '/survey/questions/answer';

            if (isset($questionDirectoriesAndPaths) && !empty($questionDirectoriesAndPaths)) {
                foreach ($questionDirectoriesAndPaths as $directory => $questionConfigFilePaths) {
                    foreach ($questionConfigFilePaths as $questionConfigFilePath) {
                        $sQuestionConfigFile = file_get_contents(realpath($questionConfigFilePath));  // @see: Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string
                        $oQuestionConfig = simplexml_load_string($sQuestionConfigFile);
                        $questionMetaData = json_decode(json_encode($oQuestionConfig->metadata), true);
                        $questionMetaData['folder'] = $questionConfigFilePath;
                        $questionMetaData['extends'] = $questionMetaData['type'];
                        if ($directory == $coreQuestionsPath) {
                            $questionMetaData['extends'] = '';
                        }
                        $questionsMetaData[$questionMetaData['name'] . '_' . $questionMetaData['type']] = $questionMetaData;

                    }
                }
            }
            libxml_disable_entity_loader($bOldEntityLoaderState);
            return $questionsMetaData;
        }

        /**
         * @param $pathToXML
         *
         * @return array Question Meta Data
         */
        public static function getQuestionMetaData($pathToXML)
        {
            $bOldEntityLoaderState = libxml_disable_entity_loader(true);

            $sQuestionConfigFile = file_get_contents(realpath($pathToXML));  // @see: Now that entity loader is disabled, we can't use simplexml_load_file; so we must read the file with file_get_contents and convert it as a string
            $oQuestionConfig = simplexml_load_string($sQuestionConfigFile);
            $questionMetaData = json_decode(json_encode($oQuestionConfig->metadata), true);
            $questionMetaData['folder'] = $pathToXML;

            libxml_disable_entity_loader($bOldEntityLoaderState);
            return $questionMetaData;
        }

        /**
         * Find all XML paths for specified Question Root folders
         *
         * @param bool $core
         * @param bool $custom
         * @param bool $user
         *
         * @return array
         */
        public function getAllQuestionXMLPaths($core = true, $custom = true, $user = true)
        {
            $questionDirectoriesAndPaths = [];
            if ($core) {
                $coreQuestionsPath = App()->getConfig('corequestiontypedir') . '/survey/questions/answer';
                $questionThemeDirectories[] = $coreQuestionsPath;
            }
            if ($custom) {
                $customQuestionThemesPath = App()->getConfig('userquestionthemedir');
                $questionThemeDirectories[] = $customQuestionThemesPath;
            }
            if ($user) {
                $userQuestionThemesPath = App()->getConfig('userquestionthemerootdir');
                if (!is_dir($userQuestionThemesPath)) {
                    mkdir($userQuestionThemesPath);
                }
                $questionThemeDirectories[] = $userQuestionThemesPath;
            }

            if (isset($questionThemeDirectories)) {
                foreach ($questionThemeDirectories as $questionThemeDirectory) {
                    $directory = new RecursiveDirectoryIterator($questionThemeDirectory);
                    $iterator = new RecursiveIteratorIterator($directory);
                    foreach ($iterator as $info) {
                        $ext = pathinfo($info->getPathname(), PATHINFO_EXTENSION);
                        if ($ext == 'xml') {
                            $questionDirectoriesAndPaths[$questionThemeDirectory][] = $info->getPathname();
                        }
                    }
                }
            }
            return $questionDirectoriesAndPaths;
        }


        public static function uninstall($templatename)
        {
            if (Permission::model()->hasGlobalPermission('templates', 'delete')) {
                $oTemplate = self::model()->findByAttributes(array('name' => $templatename));
                return $oTemplate->delete();
            }
            return false;
        }

    }