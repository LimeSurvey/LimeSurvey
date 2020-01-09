<?php
/**
 * Twig view renderer, LimeSurvey overload
 *
 * Allow to run sandbox Configuration
 * Provide different render methods for different context:
 *
 * - render()            : for general use
 * - renderQuestion()    : to render a question. It checks if a question template view should be use, else core's view (used from qanda helper).
 * - convertTwigToHtml() : to render a string without any file (used from replacement helper)
 *
 * The only tricky point here is the path problematic (where should be searched the views to render?)
 * @see: http://twig.sensiolabs.org/doc/2.x/api.html#loaders
 *
 * @author Leonid Svyatov <leonid@svyatov.ru>
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @link http://github.com/yiiext/twig-renderer
 * @link http://twig.sensiolabs.org
 *
 * @version 1.1.15
 */
class LSETwigViewRenderer extends ETwigViewRenderer
{
    /**
     * @var array Twig_Extension_Sandbox configuration
     */
    public  $sandboxConfig = array();
    private $_twig;

    /**
     * Main method to render a survey.
     * @param string  $sLayout the name of the layout to render
     * @param array   $aDatas  the datas needed to fill the layout
     * @param boolean $bReturn if true, it will return the html string without rendering the whole page. Usefull for debuging, and used for Print Answers
     */
    public function renderTemplateFromFile($sLayout, $aDatas, $bReturn)
    {
        $oTemplate = Template::getLastInstance();
        $oLayoutTemplate = $this->getTemplateForView($sLayout, $oTemplate);
        if ($oLayoutTemplate) {
            $line       = file_get_contents($oLayoutTemplate->viewPath.$sLayout);
            $sHtml      = $this->convertTwigToHtml($line, $aDatas, $oTemplate);
            $sEmHiddenInputs = LimeExpressionManager::FinishProcessPublicPage(true);
            if($sEmHiddenInputs) {
                $sHtml = str_replace("<!-- emScriptsAndHiddenInputs -->","<!-- emScriptsAndHiddenInputs updated -->\n".$sEmHiddenInputs,$sHtml);
            }
            if ($bReturn) {
                return $sHtml;
            } else {
                $this->renderHtmlPage($sHtml, $oTemplate);
            }
        } else {
            $templateDbConf = Template::getTemplateConfiguration($oTemplate->template_name, null, null, true);
            // A possible solution to this error is to re-install the template.
            if ($templateDbConf->config->metadata->version != $oTemplate->template->version) {
                throw new WrongTemplateVersionException(
                    sprintf(
                        gT("Can't render layout %s for template %s. Template version in database is %s, but in config.xml it's %s. Please re-install the template."),
                        $sLayout,
                        $oTemplate->template_name,
                        $oTemplate->template->version,
                        $templateDbConf->config->metadata->version
                    )
                );
            }
            // TODO: Panic or default to something else?
            throw new CException(
                sprintf(
                    gT("Can't render layout %s for template %s. Please try to re-install the template."),
                    $sLayout,
                    $oTemplate->template_name
                )
            );
        }
    }

    /**
     * Main method to render an admin page or block.
     * Extendable to use admin templates in the future currently running on pathes, like the yii render methods go.
     * @param string  $sLayout the name of the layout to render
     * @param array   $aDatas  the datas needed to fill the layout
     * @param boolean $bReturn if true, it will return the html string without rendering the whole page.
     *                         Usefull for debuging, and used for Print Answers
     * @param boolean $bUseRootDir Prepend application root dir to sLayoutFilePath if true.
     * @return string HTML
     */
    public function renderViewFromFile($sLayoutFilePath, $aDatas, $bReturn = false, $bUseRootDir = true)
    {
        if ($bUseRootDir) {
            $viewFile = Yii::app()->getConfig('rootdir').$sLayoutFilePath;
        } else {
            $viewFile = $sLayoutFilePath;
        }

        if (file_exists($viewFile)) {
            $line       = file_get_contents($viewFile);
            $sHtml      = $this->convertTwigToHtml($line, $aDatas);

            if ($bReturn) {
                return $sHtml;
            } else {
                $this->renderHtmlPage($sHtml, $oTemplate);
            }
        } else {
            throw new CException(
                sprintf(
                    gT("Can't render layout %s. Please check that the view exists or contact your admin."),
                    $viewFile
                )
            );
        }
    }

    /**
     * This method is called from qanda helper to render a question view file.
     * It first checks if the question use a template (set in display attributes)
     * If it is the case, it will use the views of that template, else, it will render the core view.
     *
     * @param string   $sView           the view (layout) to render
     * @param array    $aData          the datas needed for the view rendering
     *
     * @return  string the generated html
     */
    public function renderQuestion($sView, $aData)
    {
        $this->_twig  = parent::getTwig(); // Twig object

        $oQuestionTemplate   = QuestionTemplate::getInstance(); // Question template instance has been created at top of qanda_helper::retrieveAnswers()
        $extraPath = array();
        // check if this method is called from theme editor
        $sTemplateFolderName = null;
        if (empty($aData['bIsThemeEditor'])){
            $sTemplateFolderName = $oQuestionTemplate->getQuestionTemplateFolderName(); // Get the name of the folder for that question type.
        }
        // Check if question use a custom template and that it provides its own twig view
        $sDirName = null; // Extra dir name to readed from template before question template
        if ($sTemplateFolderName) {
            $bTemplateHasThisView = $oQuestionTemplate->checkIfTemplateHasView($sView); // A template can change only one of the view of the question type. So other views should be rendered by core.
            if ($bTemplateHasThisView) {
                $sDirName = 'question'.DIRECTORY_SEPARATOR.$sTemplateFolderName;
                $extraPath[] = $oQuestionTemplate->getTemplatePath(); // Question template views path
            }
        }

        // We check if the file is a twig file or a php file
        // This allow us to twig the view one by one, from PHP to twig.
        // The check will be removed when 100% of the views will have been twig
        if ($this->getPathOfFile($sView.'.twig',null,$extraPath,$sDirName)) {
            // We're not using the Yii Theming system, so we don't use parent::renderFile
            // current controller properties will be accessible as {{ this.property }}
                        //  aData and surveyInfo variables are accessible from question type twig files
            $aData['aData'] = $aData;

            // check if this method is called from theme editor
            if (empty($aData['bIsThemeEditor'])){
                    $aData['question_template_attribute'] = $oQuestionTemplate->getCustomAttributes();
                    $sBaseLanguage = Survey::model()->findByPk($_SESSION['LEMsid'])->language;
                    $aData['surveyInfo'] = getSurveyInfo($_SESSION['LEMsid'], $sBaseLanguage);
                    $aData['this'] = Yii::app()->getController();
                } else {
                    $aData['question_template_attribute'] = null;
                }
            $template = $this->_twig->loadTemplate($sView.'.twig')->render($aData);
            return $template;
        } else {
            return Yii::app()->getController()->renderPartial($sView, $aData, true);
        }
    }

    /**
     * This method is used to render question's subquestions and answer options pages  .
     * It first checks if the question use a template (set in display attributes)
     * If it is the case, it will use the views of that template, else, it will render the core view.
     *
     * @param string   $sView           the view (layout) to render
     * @param array    $aData          the datas needed for the view rendering
     *
     * @return  string the generated html
     */
    public function renderAnswerOptions($sView, $aData)
    {
        $this->_twig  = parent::getTwig(); // Twig object
        $loader       = $this->_twig->getLoader(); // Twig Template loader
        $requiredView = Yii::getPathOfAlias('application.views').$sView; // By default, the required view is the core view
        $loader->setPaths(App()->getBasePath().'/views/'); // Core views path

        $oQuestionTemplate   = QuestionTemplate::getInstance(); // Question template instance has been created at top of qanda_helper::retrieveAnswers()

        // currently, question's subquestions and answer options pages are rendered only from core
        $sTemplateFolderName = null;

        // Check if question use a custom template and that it provides its own twig view
        if ($sTemplateFolderName) {
            $bTemplateHasThisView = $oQuestionTemplate->checkIfTemplateHasView($sView); // A template can change only one of the view of the question type. So other views should be rendered by core.

            if ($bTemplateHasThisView) {
                $sQTemplatePath = $oQuestionTemplate->getTemplatePath(); // Question template views path
                $loader->setPaths($sQTemplatePath); // Loader path
                $requiredView = $sQTemplatePath.ltrim($sView, '/'); // Complete path of the view
            }
        }

        // We check if the file is a twig file or a php file
        // This allow us to twig the view one by one, from PHP to twig.
        // The check will be removed when 100% of the views will have been twig
        if (file_exists($requiredView.'.twig')) {
            // We're not using the Yii Theming system, so we don't use parent::renderFile
            // current controller properties will be accessible as {{ this.property }}

            //  aData and surveyInfo variables are accessible from question type twig files
            $aData['aData'] = $aData;
            $sBaseLanguage = Survey::model()->findByPk($_SESSION['LEMsid'])->language;
            $aData['surveyInfo'] = getSurveyInfo($_SESSION['LEMsid'], $sBaseLanguage);
            $aData['this'] = Yii::app()->getController();

            $aData['question_template_attribute'] = null;

            $template = $this->_twig->loadTemplate($sView.'.twig')->render($aData);
            return $template;
        } else {
            return Yii::app()->getController()->renderPartial($sView, $aData, true);
        }
    }

    /**
     * This method is called from Template Editor.
     * It returns the HTML string needed for the tmp file using the template twig file.
     * @param string   $sView           the view (layout) to render
     * @param array    $aDatas          the datas needed for the view rendering
     * @param Template $oEditedTemplate the template to use
     *
     * @return  string the generated html
     */
    public function renderTemplateForTemplateEditor($sView, $aDatas, $oEditedTemplate)
    {
        $oTemplate = $this->getTemplateForView($sView, $oEditedTemplate);
        if ($oTemplate) {
            $line      = file_get_contents($oTemplate->viewPath.$sView);
            $result    = $this->convertTwigToHtml($line, $aDatas, $oEditedTemplate);
            return $result;
        } else {
            trigger_error("Can't find a theme for view: ".$sView, E_USER_ERROR);
        }
    }

    /**
     * Render the option page of a template for the admin interface
     * @param Template $oTemplate    the template where the custom option page should be looked for
     * @param array    $renderArray  Array that will be passed to the options.twig as variables.
     * @return string
     */
    public function renderOptionPage($oTemplate, $renderArray = array())
    {
        $oRTemplate = $oTemplate;
        $sOptionFile = 'options/options.twig';
        $sOptionJS   = 'options/options.js';
        $sOptionsPath = $oRTemplate->sTemplateurl.'options';

        // We get the options twig file from the right template (local or mother template)
        while (!file_exists($oRTemplate->path.$sOptionFile)) {

            $oMotherTemplate = $oRTemplate->oMotherTemplate;
            if (!($oMotherTemplate instanceof TemplateConfiguration)) {
                return sprintf(gT('%s not found!', $oRTemplate->path.$sOptionFile));
                break;
            }
            $oRTemplate = $oMotherTemplate;
            $sOptionsPath = $oRTemplate->sTemplateurl.'options';
        }

        if (file_exists($oRTemplate->path.$sOptionJS)) {
            Yii::app()->getClientScript()->registerScriptFile($oRTemplate->sTemplateurl.$sOptionJS, LSYii_ClientScript::POS_BEGIN);
        }

        $this->_twig = $twig = parent::getTwig();
        $this->addRecursiveTemplatesPath($oRTemplate);
        $renderArray['optionsPath'] = $sOptionsPath;
        $renderArray['showpopups_disabled'] = (int)Yii::app()->getConfig('showpopups') < 2 ? 'disabled' : '';
        $renderArray['showpopups_disabled_qtip'] = (int)Yii::app()->getConfig('showpopups') < 2 ? 'data-hasqtip="true" title="'.gT("Disabled by configuration. Set 'showpopups' option in config.php file to enable this option. ").'"' : '';
        // Twig rendering
        $line         = file_get_contents($oRTemplate->path.$sOptionFile);
        $oTwigTemplate = $twig->createTemplate($line);
        $sHtml        = $oTwigTemplate->render($renderArray, false);

        return $sHtml;
    }

    /**
     * Render the survey page, with the headers, the css, and the script
     * If LS would use the normal Yii render flow, this function would not be necessary
     * In previous LS version, this logic was here: https://github.com/LimeSurvey/LimeSurvey/blob/700b20e2ae918550bfbf283f433f07622480978b/application/controllers/survey/index.php#L62-L71
     *
     * @param string $sHtml     The Html content of the page (it must not contain anymore any twig statement)
     * @param Template $oTemplate The name of the template to use to register the packages
     */
    public function renderHtmlPage($sHtml, $oTemplate)
    {
        Yii::app()->clientScript->registerPackage($oTemplate->sPackageName, LSYii_ClientScript::POS_BEGIN);

        ob_start(function($buffer, $phase)
        {
            App()->getClientScript()->render($buffer);
            App()->getClientScript()->reset();
            return $buffer;
        });

        ob_implicit_flush(false);
        echo $sHtml;
        ob_flush();

        Yii::app()->end();
    }


    /**
     * Convert a string containing twig tags to an HTML string.
     *
     * @param string $sString The string of HTML/Twig to convert
     * @param array $aDatas Array containing the datas needed to render the view ($thissurvey)
     * @param TemplateConfiguration|null $oTemplate
     * @return string
     */
    public function convertTwigToHtml($sString, $aDatas=array(), $oTemplate = null)
    {

        // Twig init
        $this->_twig = $twig = parent::getTwig();

        //Run theme related things only if a theme is provided!
        if ($oTemplate !== null) {
            // Get the additional infos for the view, such as language, direction, etc
            $aDatas = $this->getAdditionalInfos($aDatas, $oTemplate);

            // Add to the loader the path of the template and its parents.
            $this->addRecursiveTemplatesPath($oTemplate);

            // Plugin for blocks replacement

              list($sString, $aDatas) = $this->getPluginsData($sString, $aDatas);


        }

        // Twig rendering
        $oTwigTemplate = $twig->createTemplate($sString);
        $sHtml         = $oTwigTemplate->render($aDatas, false);

        return $sHtml;
    }

    /**
     * Find which template should be used to render a given view
     * @param  string    $sView           the view (layout) to render
     * @param  TemplateConfiguration  $oRTemplate    the template where the custom option page should be looked for
     * @return Template|boolean
     */
    private function getTemplateForView($sView, $oRTemplate)
    {
        while (!file_exists($oRTemplate->viewPath.$sView)) {
            $oMotherTemplate = $oRTemplate->oMotherTemplate;
            if (!($oMotherTemplate instanceof TemplateConfiguration)) {
                return false;
                break;
            }
            $oRTemplate = $oMotherTemplate;
        }

        return $oRTemplate;
    }

    /**
     * Twig can look for twig path in different path. This function will add the path of the template and all its parents to the load path
     * So if a twig file is inclueded, it will look in the local template directory and all its parents
     * @param TemplateConfiguration $oTemplate  the template where to start
     * @param string[] $extraPaths to be added before template, parent template plugin add and core views. Example : question template
     * @param string|null $dirName directory name to be added as extra directory inside template view
     */
    private function addRecursiveTemplatesPath($oTemplate,$extraPaths=array(),$dirName=null)
    {
        $oRTemplate   = $oTemplate;
        $loader       = $this->_twig->getLoader();
        $loader->setPaths(array()); /* Always reset (needed for Question template / $extraPaths, maybe in some other situation) */
        /* Event to add or replace twig views */


        if (! App()->getConfig('force_xmlsettings_for_survey_rendering')){
          $oEvent = new PluginEvent('getPluginTwigPath');
          App()->getPluginManager()->dispatchEvent($oEvent);
          $configTwigExtendsAdd = (array) $oEvent->get("add");
          $configTwigExtendsReplace = (array) $oEvent->get("replace");

          /* Forced twig by plugins (used to replace vanilla or core template …  don't like to force on user template, but else can extend current core twig) */
          foreach($configTwigExtendsReplace as $configTwigExtendReplace) {
              if(is_string($configTwigExtendReplace)) { // Need more control ?
                  $loader->addPath($configTwigExtendReplace);
              }
          }
        }

        if(!empty($dirName)) {
            /* This template for dirName template*/
            if(is_dir($oRTemplate->viewPath.$dirName)) {
                $loader->addPath($oRTemplate->viewPath.$dirName.DIRECTORY_SEPARATOR);
            }
            /* Parent template (for question)*/
            while ($oRTemplate->oMotherTemplate instanceof TemplateConfiguration) {
                $oRTemplate = $oRTemplate->oMotherTemplate;
                if(is_dir($oRTemplate->viewPath.$dirName)) {
                    $loader->addPath($oRTemplate->viewPath.$dirName.DIRECTORY_SEPARATOR);
                }
            }
        }
        /* Extra path (Question template Path for example)*/
        if(!empty($extraPaths)) {
            foreach($extraPaths as $extraPath) {
                $loader->addPath($extraPath);
            }
        }
        $oRTemplate   = $oTemplate;
        /* This template */
        $loader->addPath($oRTemplate->viewPath);
        /* Parent template */
        while ($oRTemplate->oMotherTemplate instanceof TemplateConfiguration) {
            $oRTemplate = $oRTemplate->oMotherTemplate;
            $loader->addPath($oRTemplate->viewPath);
        }
        /* Added twig by plugins, replaced by any template file or question template file*/
        if (isset($configTwigExtendsAdd)){
          foreach($configTwigExtendsAdd as $configTwigExtendAdd) {
              if(is_string($configTwigExtendAdd)) {
                  $loader->addPath($configTwigExtendAdd);
              }
          }
        }

        $loader->addPath(App()->getBasePath().'/views/'); // Core views path
    }

    /**
     * Plugin event, should be replaced by Question Template
     * @param string $sString
     */
    private function getPluginsData($sString, $aDatas)
    {
        $event = new PluginEvent('beforeTwigRenderTemplate');
        $aDatas['aSurveyInfo']['bShowClearAll'] = false; // default to not show "Exit and clear survey" button

        if (!empty($aDatas['aSurveyInfo']['sid'])) {
            $surveyid = $aDatas['aSurveyInfo']['sid'];
            $event->set('surveyId', $aDatas['aSurveyInfo']['sid']);

            // show "Exit and clear survey" button whenever there is 'srid' key set,
            // button won't be rendered on welcome and final page because 'srid' key doesn't exist on those pages
            // additionally checks for submit page to compensate when srid is needed to render other views
            if (
                isset($_SESSION['survey_' . $surveyid]['srid'])
                && isset($aDatas['aSurveyInfo']['active']) && $aDatas['aSurveyInfo']['active'] == 'Y'
                && isset($aDatas['aSurveyInfo']['include_content']) && $aDatas['aSurveyInfo']['include_content'] !== 'submit'
                && isset($aDatas['aSurveyInfo']['include_content']) && $aDatas['aSurveyInfo']['include_content'] !== 'submit_preview'
            ) {
                $aDatas['aSurveyInfo']['bShowClearAll'] = true;
            }
        }

        if (!App()->getConfig('force_xmlsettings_for_survey_rendering')){
          App()->getPluginManager()->dispatchEvent($event);
          $aPluginContent = $event->getAllContent();
          if (!empty($aPluginContent['sTwigBlocks'])) {
              $sString = $sString.$aPluginContent['sTwigBlocks'];
          }
        }


        return array($sString, $aDatas);
    }

    /**
     * In the LS2.x code, some of the render logic was duplicated on different files (surveyRuntime, frontend_helper, etc)
     * In LS3, we did a first cycle of refactorisation. Some logic common to the different files are for now here, in this function.
     * TODO: move all the display logic to surveyRuntime so we don't need this function here
     *
     * @param TemplateConfiguration $oTemplate
     */
    private function getAdditionalInfos($aDatas, $oTemplate)
    {
        // We retreive the definition of the core class and attributes (in the future, should be template dependant done via XML file)
        $aDatas["aSurveyInfo"] = array_merge($aDatas["aSurveyInfo"], $oTemplate->getClassAndAttributes());

        $languagecode = Yii::app()->getLanguage();
        if (!empty($aDatas['aSurveyInfo']['sid']) && Survey::model()->findByPk($aDatas['aSurveyInfo']['sid']) ) {
            if(!in_array($languagecode,Survey::model()->findByPk($aDatas['aSurveyInfo']['sid'])->getAllLanguages())) {
                $languagecode = Survey::model()->findByPk($aDatas['aSurveyInfo']['sid'])->language;
            }
        }

        $aDatas["aSurveyInfo"]['languagecode']     = $languagecode;
        $aDatas["aSurveyInfo"]['dir']              = (getLanguageRTL($languagecode)) ? "rtl" : "ltr";

        if (!empty($aDatas['aSurveyInfo']['sid'])) {
            $showxquestions                            = Yii::app()->getConfig('showxquestions');
            $aDatas["aSurveyInfo"]['bShowxquestions']  = ($showxquestions == 'show' || ($showxquestions == 'choose' && !isset($aDatas['aSurveyInfo']['showxquestions'])) || ($showxquestions == 'choose' && $aDatas['aSurveyInfo']['showxquestions'] == 'Y'));


            // NB: Session is flushed at submit, so sid is not defined here.
            if (isset($_SESSION['survey_'.$aDatas['aSurveyInfo']['sid']]) && isset($_SESSION['survey_'.$aDatas['aSurveyInfo']['sid']]['totalquestions'])) {
                $aDatas["aSurveyInfo"]['iTotalquestions'] = $_SESSION['survey_'.$aDatas['aSurveyInfo']['sid']]['totalquestions'];
            }

            // Add the survey theme options
            if ($oTemplate->oOptions) {
                foreach ($oTemplate->oOptions as $key => $value) {
                    $aDatas["aSurveyInfo"]["options"][$key] = (string) $value;
                }
            }
        } else {
            // Add the global theme options
            $oTemplateConfigurationCurrent = Template::getInstance($oTemplate->sTemplateName);
            $aDatas["aSurveyInfo"]["options"] = isJson($oTemplateConfigurationCurrent['options'])?(array)json_decode($oTemplateConfigurationCurrent['options']):$oTemplateConfigurationCurrent['options'];
        }

        $aDatas = $this->fixDataCoherence($aDatas);

        return $aDatas;
    }


    /**
     * It can happen that user set incoherent values for options (like background is on, but no image file is selected)
     * With some server configuration, it can lead to critical errors : empty values in image src or url() can block submition
     * This function will check thoses cases. It can be used in the future for further checks
     * @param array $aDatas
     * @return array
     *
     */
    private function fixDataCoherence($aDatas)
    {
        // Clean option with files
        $aFilesOptions = array( 'brandlogo' => 'brandlogofile'  , 'backgroundimage' => 'backgroundimagefile' );

        foreach ($aFilesOptions as $sOption => $sFileOption) {
            if ( array_key_exists ( $sFileOption ,$aDatas["aSurveyInfo"]["options"]) )
                if ( empty ($aDatas["aSurveyInfo"]["options"][$sFileOption])  ){
                    $aDatas["aSurveyInfo"]["options"][$sOption] = "false";
                }
        }

        return $aDatas;
    }


    /**
     * Adds custom extensions.
     * It's different from the original Yii Twig Extension to take in account our custom sand box
     * @param array $extensions @see self::$extensions
     */
    public function addExtensions($extensions)
    {
        $this->_twig = parent::getTwig();
        foreach ($extensions as $extName) {
            if ($extName == "Twig_Extension_Sandbox") {
                // Process to load the sandBox
                $tags       = isset($this->sandboxConfig['tags']) ? $this->sandboxConfig['tags'] : array();
                $filters    = isset($this->sandboxConfig['filters']) ? $this->sandboxConfig['filters'] : array();
                $methods    = isset($this->sandboxConfig['methods']) ? $this->sandboxConfig['methods'] : array();
                $properties = isset($this->sandboxConfig['properties']) ? $this->sandboxConfig['properties'] : array();
                $functions  = isset($this->sandboxConfig['functions']) ? $this->sandboxConfig['functions'] : array();
                $policy     = new Twig_Sandbox_SecurityPolicy($tags, $filters, $methods, $properties, $functions);
                $sandbox    = new Twig_Extension_Sandbox($policy, true);

                $this->_twig->addExtension($sandbox);
            } else {
                $this->_twig->addExtension(new $extName());
            }
        }
    }

    /**
     * get a twig file and return html produced
     * @todo find a way to fix in beforeCloseHtml @see https://bugs.limesurvey.org/view.php?id=13889
     * @param string $twigView twigfile to be used (with twig extension)
     * @param array $aData to be used
     * @return string
     */
    public function renderPartial($twigView,$aData)
    {
        $oTemplate = Template::getLastInstance();
        $aData = $this->getAdditionalInfos($aData, $oTemplate);
        $this->addRecursiveTemplatesPath($oTemplate);
        return $this->_twig->loadTemplate($twigView)->render($aData);
    }

    /**
     * Get the final source file for current context
     * Currently used in theme editor
     * @param string $twigView twigfile to be used (with twig extension)
     * @param TemplateConfiguration $oTemplate
     * @param string[] $extraPath path to be added before plugins add and core views
     * @param string|null $dirName directory name to be added as extra directory inside template view
     * @return string complete filename to be used
     */
    public function getPathOfFile($twigView,$oTemplate=null,$extraPath=array(),$dirName = null)
    {
        if(!$oTemplate) {
            $oTemplate = Template::getLastInstance();
        }
        $this->addRecursiveTemplatesPath($oTemplate,$extraPath,$dirName);
        if(!$this->_twig->getLoader()->exists($twigView)) {
            return null;
        }
        return $this->_twig->getLoader()->getSourceContext($twigView)->getPath();
    }
}
