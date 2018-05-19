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
        $oTemplate = Template::model()->getInstance();
        $oLayoutTemplate = $this->getTemplateForView($sLayout, $oTemplate);
        if ($oLayoutTemplate) {
            $line       = file_get_contents($oLayoutTemplate->viewPath.$sLayout);
            $sHtml      = $this->convertTwigToHtml($line, $aDatas, $oTemplate);
            $sEmHiddenInputs = LimeExpressionManager::FinishProcessPublicPage();
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
        $loader       = $this->_twig->getLoader(); // Twig Template loader
        $requiredView = Yii::getPathOfAlias('application.views').$sView; // By default, the required view is the core view
        $loader->setPaths(App()->getBasePath().'/views/'); // Core views path

        $oQuestionTemplate   = QuestionTemplate::getInstance(); // Question template instance has been created at top of qanda_helper::retrieveAnswers()

        // check if this method is called from theme editor
        if (empty($aData['bIsThemeEditor'])){
            $sTemplateFolderName = $oQuestionTemplate->getQuestionTemplateFolderName(); // Get the name of the folder for that question type.
        } else {
            $sTemplateFolderName = null;
        }
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
            
            // check if this method is called from theme editor
            if (empty($aData['bIsThemeEditor'])){
                    $aData['question_template_attribute'] = $oQuestionTemplate->getCustomAttributes();
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
    private function renderHtmlPage($sHtml, $oTemplate)
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
     * @param TemplateConfiguration $oTemplate
     * @return string
     */
    public function convertTwigToHtml($sString, $aDatas, $oTemplate)
    {
        // Twig init
        $this->_twig = $twig = parent::getTwig();

        // Get the additional infos for the view, such as language, direction, etc
        $aDatas = $this->getAdditionalInfos($aDatas, $oTemplate);

        // Add to the loader the path of the template and its parents.
        $this->addRecursiveTemplatesPath($oTemplate);

        // Plugin for blocks replacement
        list($sString, $aDatas) = $this->getPluginsData($sString, $aDatas);

        // Twig rendering
        $oTwigTemplate = $twig->createTemplate($sString);
        $sHtml         = $oTwigTemplate->render($aDatas, false);

        return $sHtml;
    }

    /**
     * Find which template should be used to render a given view
     * @param  string    $sView           the view (layout) to render
     * @param  Template  $oRTemplate    the template where the custom option page should be looked for
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
     * @param Template $oTemplate  the template where to start
     */
    private function addRecursiveTemplatesPath($oTemplate)
    {
        $oRTemplate   = $oTemplate;
        $loader       = $this->_twig->getLoader();
        $loader->addPath($oRTemplate->viewPath);
        while ($oRTemplate->oMotherTemplate instanceof TemplateConfiguration) {
            $oRTemplate = $oRTemplate->oMotherTemplate;
            $loader->addPath($oRTemplate->viewPath);
        }
    }

    /**
     * Plugin event, should be replaced by Question Template
     * @param string $sString
     */
    private function getPluginsData($sString, $aDatas)
    {
        $event = new PluginEvent('beforeTwigRenderTemplate');

        if (!empty($aDatas['aSurveyInfo']['sid'])) {
            $surveyid = $aDatas['aSurveyInfo']['sid'];
            $event->set('surveyId', $aDatas['aSurveyInfo']['sid']);

            if (isset($_SESSION['survey_'.$surveyid]['srid']) && $aDatas['aSurveyInfo']['active']=='Y') {
                $isCompleted = SurveyDynamic::model($surveyid)->isCompleted($_SESSION['survey_'.$surveyid]['srid']);
            } else {
                $isCompleted = false;
            }

            $aDatas['aSurveyInfo']['bShowClearAll'] = !$isCompleted;
        }

        App()->getPluginManager()->dispatchEvent($event);
        $aPluginContent = $event->getAllContent();
        if (!empty($aPluginContent['sTwigBlocks'])) {
            $sString = $sString.$aPluginContent['sTwigBlocks'];
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

        $languagecode = Yii::app()->getConfig('defaultlang');
        if (!empty($aDatas['aSurveyInfo']['sid'])) {
            if (Yii::app()->session['survey_'.$aDatas['aSurveyInfo']['sid']]['s_lang']) {
                $languagecode = Yii::app()->session['survey_'.$aDatas['aSurveyInfo']['sid']]['s_lang'];
            } elseif ($aDatas['aSurveyInfo']['sid'] && Survey::model()->findByPk($aDatas['aSurveyInfo']['sid'])) {
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
        }


        // Add the template options
        if ($oTemplate->oOptions) {
            foreach ($oTemplate->oOptions as $key => $value) {
                $aDatas["aSurveyInfo"]["options"][$key] = (string) $value;
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
}
