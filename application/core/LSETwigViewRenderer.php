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
    public $sandboxConfig = array();

    /**
     * @var Twig_Environment|null
     */
    private $_twig;

    /**
     * @var array Custom LS Users Extensions
     * Example: array('HelloWorld_Twig_Extension')
     */
    public $user_extensions = [];

    /**
     * @inheritdoc
     */
    function init()
    {
        parent::init();
        // Adding user custom extensions
        if (!empty($this->user_extensions)) {
            $this->addUserExtensions($this->user_extensions);
        }
    }

    /**
     * Main method to render a survey.
     * @param string $sLayout the name of the layout to render
     * @param array $aData the datas needed to fill the layout
     * @param boolean $bReturn if true, it will return the html string without
     *                         rendering the whole page. Usefull for debuging, and used for Print Answers
     * @return mixed|string
     * @throws CException
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     * @throws WrongTemplateVersionException
     */
    public function renderTemplateFromFile($sLayout, $aData, $bReturn)
    {
        $oTemplate = Template::getLastInstance();
        $oLayoutTemplate = $this->getTemplateForView($sLayout, $oTemplate);
        if ($oLayoutTemplate) {
            $line       = file_get_contents($oLayoutTemplate->viewPath . $sLayout);
            $sHtml      = $this->convertTwigToHtml($line, $aData, $oTemplate);
            $sEmHiddenInputs = LimeExpressionManager::FinishProcessPublicPage(true);
            if ($sEmHiddenInputs) {
                $sHtml = str_replace(
                    "<!-- emScriptsAndHiddenInputs -->",
                    "<!-- emScriptsAndHiddenInputs updated -->\n" .
                    $sEmHiddenInputs,
                    $sHtml
                );
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
     * Main method to render a question in the question editor preview.
     * @param string $sLayout the name of the layout to render
     * @param array $aData the datas needed to fill the layout
     * @param bool $root
     * @param boolean $bReturn if true, it will return the html string without
     *                         rendering the whole page. Usefull for debuging, and used for Print Answers
     * @return mixed|string
     * @throws CException
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     * @throws WrongTemplateVersionException
     */
    public function renderTemplateForQuestionEditPreview($sLayout, $aData, $root = false, $bReturn = false)
    {
        $root = (bool) $root;
        $oTemplate = Template::getInstance(
            $aData['aSurveyInfo']['template'],
            null,
            null,
            null,
            null,
            true
        );
        $oLayoutTemplate = $this->getTemplateForView($sLayout, $oTemplate);
        if ($oLayoutTemplate) {
            $postMessageScripts = "
var eventset = true;
window.addEventListener('message', function(event) {
    if(eventset){ console.log(event); eventset=!eventset;}
    event.source.postMessage({msg: 'EVENT COLLECTED', event: event.data}, '*');
    var getUrl = window.location;
    var baseUrl = getUrl.protocol + '//' + getUrl.host + '/' + getUrl.pathname.split('/')[1];
    
    if (baseUrl.match(event.origin) && event.data.run == 'trigger::pjax:scriptcomplete' ) {
        console.log('runScriptcomplete');
        jQuery(document).trigger('pjax:scriptcomplete');
    }

    if (baseUrl.match(event.origin) && event.data.run == 'trigger::newContent' ) {
        console.log('replaceContent');
        jQuery('#questionPreview--content').text('');
        jQuery('#questionPreview--content').html(event.data.content);
    }
}, false);  
";
            App()->getClientScript()->registerScript(
                'postMessageScripts',
                $postMessageScripts,
                CClientScript::POS_HEAD
            );
            $line = '<div class="{{ aSurveyInfo.class.outerframe }}  {% if (aSurveyInfo.options.container == "on") %} container {% else %} container-fluid {% endif %} " id="{{ aSurveyInfo.id.outerframe }}" {{ aSurveyInfo.attr.outerframe }} >';
            $line .= file_get_contents($oLayoutTemplate->viewPath . $sLayout);
            $line .= '</div>';
            if ($root === true) {
                $line = '<html lang="{{ aSurveyInfo.languagecode }}" dir="{{ aSurveyInfo.dir }}" class="{{ aSurveyInfo.languagecode }} dir-{{ aSurveyInfo.dir }} {{ aSurveyInfo.class.html }}" {{ aSurveyInfo.attr.html }}>'
                    . file_get_contents($oLayoutTemplate->viewPath . '/subviews/header/head.twig')
                    . '<body style="padding-top: 0px !important;" class=" {{ aSurveyInfo.class.body }} font-{{  aSurveyInfo.options.font }} lang-{{aSurveyInfo.languagecode}} {{aSurveyInfo.surveyformat}} {% if( aSurveyInfo.options.brandlogo == "on") %}brand-logo{%endif%}" {{ aSurveyInfo.attr.body }} >'
                    . $line;
                $line .= '</body>';
                $line .= '</html>';
            }

            $sHtml     = $this->convertTwigToHtml($line, $aData, $oTemplate);

            $sEmHiddenInputs = LimeExpressionManager::FinishProcessPublicPage(true);
            if ($sEmHiddenInputs) {
                $sHtml = str_replace(
                    "<!-- emScriptsAndHiddenInputs -->",
                    "<!-- emScriptsAndHiddenInputs updated -->\n"
                    . $sEmHiddenInputs,
                    $sHtml
                );
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
     * @param $sLayoutFilePath
     * @param array $aData the datas needed to fill the layout
     * @param boolean $bReturn if true, it will return the html string without rendering the whole page.
     *                         Usefull for debuging, and used for Print Answers
     * @param boolean $bUseRootDir Prepend application root dir to sLayoutFilePath if true.
     * @return string HTML
     * @throws CException
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     * @todo missing return statement (php warning)
     */
    public function renderViewFromFile($sLayoutFilePath, $aData, $bReturn = false, $bUseRootDir = true)
    {
        if ($bUseRootDir) {
            $viewFile = App()->getConfig('rootdir') . $sLayoutFilePath;
        } else {
            $viewFile = $sLayoutFilePath;
        }

        if (file_exists($viewFile)) {
            $line       = file_get_contents($viewFile);
            $sHtml      = $this->convertTwigToHtml($line, $aData);

            if ($bReturn) {
                return $sHtml;
            } else {
                /** @psalm-suppress UndefinedVariable TODO: $oTemplate is never defined */
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
     * @param string $sView the view (layout) to render
     * @param array $aData the datas needed for the view rendering
     *
     * @return  string the generated html
     * @throws CException
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     */
    public function renderQuestion($sView, $aData)
    {
        $this->_twig  = parent::getTwig(); // Twig object

        // Question template instance has been created at top of qanda_helper::retrieveAnswers()
        $oQuestionTemplate   = QuestionTemplate::getInstance();
        $extraPath = array();
        // check if this method is called from theme editor
        $sTemplateFolderName = null;
        if (empty($aData['bIsThemeEditor'])) {
            // Get the name of the folder for that question type.
            $sTemplateFolderName = $oQuestionTemplate->getQuestionTemplateFolderName();
        }
        // Check if question use a custom template and that it provides its own twig view
        $sDirName = null; // Extra dir name to readed from template before question template
        if ($sTemplateFolderName) {
            // A template can change only one of the view of the question type.
            // So other views should be rendered by core.
            $bTemplateHasThisView = $oQuestionTemplate->checkIfTemplateHasView($sView);
            if ($bTemplateHasThisView) {
                $sDirName = 'question' . DIRECTORY_SEPARATOR . $sTemplateFolderName;
                $extraPath[] = $oQuestionTemplate->getTemplatePath(); // Question template views path
            }
        }

        // We check if the file is a twig file or a php file
        // This allow us to twig the view one by one, from PHP to twig.
        // The check will be removed when 100% of the views will have been twig
        if ($this->getPathOfFile($sView . '.twig', null, $extraPath, $sDirName)) {
            // We're not using the Yii Theming system, so we don't use parent::renderFile
            // current controller properties will be accessible as {{ this.property }}
                        //  aData and surveyInfo variables are accessible from question type twig files
            $aData['aData'] = $aData;

            // check if this method is called from theme editor
            if (empty($aData['bIsThemeEditor'])) {
                    $aData['question_template_attribute'] = $oQuestionTemplate->getCustomAttributes();
                    $sBaseLanguage = Survey::model()->findByPk($_SESSION['LEMsid'])->language;
                    $aData['surveyInfo'] = getSurveyInfo($_SESSION['LEMsid'], $sBaseLanguage);
                    $aData['this'] = App()->getController();
            } else {
                $aData['question_template_attribute'] = null;
            }
            $template = $this->_twig->render($sView . '.twig', $aData);
            return $template;
        } else {
            return App()->getController()->renderPartial($sView, $aData, true);
        }
    }

    /**
     * This method is used to render question's subquestions and answer options pages  .
     * It first checks if the question use a template (set in display attributes)
     * If it is the case, it will use the views of that template, else, it will render the core view.
     *
     * @param string $sView the view (layout) to render
     * @param array $aData the datas needed for the view rendering
     *
     * @return  string the generated html
     * @throws CException
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     */
    public function renderAnswerOptions($sView, $aData)
    {
        $this->_twig  = parent::getTwig(); // Twig object
        $loader       = $this->_twig->getLoader(); // Twig Template loader
        // By default, the required view is the core view
        $requiredView = Yii::getPathOfAlias('application.views') . $sView;
        $loader->setPaths(App()->getBasePath() . '/views/'); // Core views path

        // Question template instance has been created at top of qanda_helper::retrieveAnswers()
        $oQuestionTemplate   = QuestionTemplate::getInstance();

        // currently, question's subquestions and answer options pages are rendered only from core
        $sTemplateFolderName = null;

        // Check if question use a custom template and that it provides its own twig view
        if ($sTemplateFolderName) {
            // A template can change only one of the view of the question type. So other
            // views should be rendered by core.
            $bTemplateHasThisView = $oQuestionTemplate->checkIfTemplateHasView($sView);

            if ($bTemplateHasThisView) {
                $sQTemplatePath = $oQuestionTemplate->getTemplatePath(); // Question template views path
                $loader->setPaths($sQTemplatePath); // Loader path
                $requiredView = $sQTemplatePath . ltrim($sView, '/'); // Complete path of the view
            }
        }

        // We check if the file is a twig file or a php file
        // This allow us to twig the view one by one, from PHP to twig.
        // The check will be removed when 100% of the views will have been twig
        if (file_exists($requiredView . '.twig')) {
            // We're not using the Yii Theming system, so we don't use parent::renderFile
            // current controller properties will be accessible as {{ this.property }}

            //  aData and surveyInfo variables are accessible from question type twig files
            $aData['aData'] = $aData;
            $sBaseLanguage = Survey::model()->findByPk($_SESSION['LEMsid'])->language;
            $aData['surveyInfo'] = getSurveyInfo($_SESSION['LEMsid'], $sBaseLanguage);
            $aData['this'] = App()->getController();

            $aData['question_template_attribute'] = null;

            $template = $this->_twig->render($sView . '.twig', $aData);
            return $template;
        } else {
            return App()->getController()->renderPartial($sView, $aData, true);
        }
    }

    /**
     * This method is called from Template Editor.
     * It returns the HTML string needed for the tmp file using the template twig file.
     * @param string $sView the view (layout) to render
     * @param array $aData the datas needed for the view rendering
     * @param Template $oEditedTemplate the template to use
     *
     * @return  string the generated html
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     * @todo missing return statement (php warning)
     */
    public function renderTemplateForTemplateEditor($sView, $aData, $oEditedTemplate)
    {
        $oTemplate = $this->getTemplateForView($sView, $oEditedTemplate);
        if ($oTemplate) {
            $line      = file_get_contents($oTemplate->viewPath . $sView);
            $result    = $this->convertTwigToHtml($line, $aData, $oEditedTemplate);
            return $result;
        } else {
            trigger_error("Can't find a theme for view: " . $sView, E_USER_ERROR);
        }
    }

    /**
     * Render the option page of a template for the admin interface
     * @param Template $oTemplate the template where the custom option page should be looked for
     * @param array $renderArray Array that will be passed to the options.twig as variables.
     * @return string
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     */
    public function renderOptionPage($oTemplate, $renderArray = array())
    {
        $oRTemplate = $oTemplate;
        $sOptionFile = 'options/options.twig';
        $sOptionJS   = 'options/options.js';
        $sOptionsPath = $oRTemplate->sTemplateurl . 'options';

        // We get the options twig file from the right template (local or mother template)
        while (!file_exists($oRTemplate->path . $sOptionFile)) {
            $oMotherTemplate = $oRTemplate->oMotherTemplate;
            if (!($oMotherTemplate instanceof TemplateConfiguration)) {
                return sprintf(gT('%s not found!', $oRTemplate->path . $sOptionFile));
                break;
            }
            $oRTemplate = $oMotherTemplate;
            $sOptionsPath = $oRTemplate->sTemplateurl . 'options';
        }

        if (file_exists($oRTemplate->path . $sOptionJS)) {
            App()->getClientScript()->registerScriptFile(
                $oRTemplate->sTemplateurl . $sOptionJS,
                LSYii_ClientScript::POS_END
            );
        }

        $this->_twig = $twig = parent::getTwig();
        $this->addRecursiveTemplatesPath($oRTemplate);
        $renderArray['optionsPath'] = $sOptionsPath;
        $renderArray['showpopups_disabled'] = (int) App()->getConfig('showpopups') < 2 ? 'disabled' : '';
        $renderArray['showpopups_disabled_qtip'] = (int) App()->getConfig('showpopups') < 2
            ? 'data-hasqtip="true" title="' .
            gT("Disabled by configuration. Set 'showpopups' option in config.php file to enable this option. ") .
            '"'
            : '';
        // Twig rendering
        $line         = file_get_contents($oRTemplate->path . $sOptionFile);
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
        App()->clientScript->registerPackage($oTemplate->sPackageName, LSYii_ClientScript::POS_BEGIN);

        ob_start(function ($buffer, $phase) {
            App()->getClientScript()->render($buffer);
            App()->getClientScript()->reset();
            return $buffer;
        });

        ob_implicit_flush(false);
        echo $sHtml;
        ob_flush();

        App()->end();
    }


    /**
     * Convert a string containing twig tags to an HTML string.
     *
     * @param string $sString The string of HTML/Twig to convert
     * @param array $aData Array containing the datas needed to render the view ($thissurvey)
     * @param TemplateConfiguration|null $oTemplate
     * @return string
     * @throws Throwable
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     */
    public function convertTwigToHtml($sString, $aData = array(), $oTemplate = null)
    {

        // Twig init
        $this->_twig = $twig = parent::getTwig();

        //Run theme related things only if a theme is provided!
        if ($oTemplate !== null) {
            // Get the additional infos for the view, such as language, direction, etc
            $aData = $this->getAdditionalInfos($aData, $oTemplate);

            // Add to the loader the path of the template and its parents.
            $this->addRecursiveTemplatesPath($oTemplate);

            // Plugin for blocks replacement
            list($sString, $aData) = $this->getPluginsData($sString, $aData);
        }

        // Twig rendering
        $oTwigTemplate = $twig->createTemplate($sString);
        $sHtml         = $oTwigTemplate->render($aData, false);

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
        while (!file_exists($oRTemplate->viewPath . $sView)) {
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
     * Twig can look for twig path in different path. This function will add the path of the template and all its
     * parents to the load path
     * So if a twig file is inclueded, it will look in the local template directory and all its parents
     * @param TemplateConfiguration $oTemplate  the template where to start
     * @param string[] $extraPaths to be added before template, parent template plugin add and core views.
     * Example : question template
     * @param string|null $dirName directory name to be added as extra directory inside template view
     */
    private function addRecursiveTemplatesPath($oTemplate, $extraPaths = array(), $dirName = null)
    {
        $oRTemplate   = $oTemplate;
        $loader       = $this->_twig->getLoader();
        /* Always reset (needed for Question template / $extraPaths, maybe in some other situation) */
        $loader->setPaths(array());
        /* Event to add or replace twig views */

        if (! App()->getConfig('force_xmlsettings_for_survey_rendering')) {
            $oEvent = new PluginEvent('getPluginTwigPath');
            App()->getPluginManager()->dispatchEvent($oEvent);
            $configTwigExtendsAdd = (array) $oEvent->get("add");
            $configTwigExtendsReplace = (array) $oEvent->get("replace");

            /* Forced twig by plugins (used to replace vanilla or core template …
            don't like to force on user template, but else can extend current core twig) */
            foreach ($configTwigExtendsReplace as $configTwigExtendReplace) {
                if (is_string($configTwigExtendReplace)) { // Need more control ?
                    $loader->addPath($configTwigExtendReplace);
                }
            }
        }

        if (!empty($dirName)) {
            /* This template for dirName template*/
            if (is_dir($oRTemplate->viewPath . $dirName)) {
                $loader->addPath($oRTemplate->viewPath . $dirName . DIRECTORY_SEPARATOR);
            }
            /* Parent template (for question)*/
            while ($oRTemplate->oMotherTemplate instanceof TemplateConfiguration) {
                $oRTemplate = $oRTemplate->oMotherTemplate;
                if (is_dir($oRTemplate->viewPath . $dirName)) {
                    $loader->addPath($oRTemplate->viewPath . $dirName . DIRECTORY_SEPARATOR);
                }
            }
        }
        /* Extra path (Question template Path for example)*/
        if (!empty($extraPaths)) {
            foreach ($extraPaths as $extraPath) {
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
        if (isset($configTwigExtendsAdd)) {
            foreach ($configTwigExtendsAdd as $configTwigExtendAdd) {
                if (is_string($configTwigExtendAdd)) {
                    $loader->addPath($configTwigExtendAdd);
                }
            }
        }

        $loader->addPath(App()->getBasePath() . '/views/'); // Core views path
    }

    /**
     * Plugin event, should be replaced by Question Template
     * @param string $sString
     * @param array $aData
     * @return array
     */
    private function getPluginsData($sString, $aData)
    {
        $event = new PluginEvent('beforeTwigRenderTemplate');

        if (!empty($aData['aSurveyInfo']['sid'])) {
            $surveyid = $aData['aSurveyInfo']['sid'];
            $event->set('surveyId', $aData['aSurveyInfo']['sid']);

            // show "Exit and clear survey" button whenever there is 'srid' key set,
            // button won't be rendered on welcome and final page because 'srid' key doesn't exist on those pages
            // additionally checks for submit page to compensate when srid is needed to render other views
            if (
                isset($_SESSION['responses_' . $surveyid]['srid'])
                && isset($aData['aSurveyInfo']['active']) && $aData['aSurveyInfo']['active'] == 'Y'
                && isset($aData['aSurveyInfo']['include_content']) && $aData['aSurveyInfo']['include_content'] !== 'submit'
                && isset($aData['aSurveyInfo']['include_content']) && $aData['aSurveyInfo']['include_content'] !== 'submit_preview'
            ) {
                $aData['aSurveyInfo']['bShowClearAll'] = true;
            }
        }

        if (!App()->getConfig('force_xmlsettings_for_survey_rendering')) {
            App()->getPluginManager()->dispatchEvent($event);
            $aPluginContent = $event->getAllContent();
            if (!empty($aPluginContent['sTwigBlocks'])) {
                $sString = $sString . $aPluginContent['sTwigBlocks'];
            }
        }

        return array($sString, $aData);
    }

    /**
     * In the LS2.x code, some of the render logic was duplicated on different files
     * (surveyRuntime, frontend_helper, etc)
     * In LS3, we did a first cycle of refactorisation. Some logic common to the different
     * files are for now here, in this function.
     *
     * @todo move all the display logic to surveyRuntime so we don't need this function here
     * @param TemplateConfiguration $oTemplate
     * @return array
     */
    private function getAdditionalInfos($aData, $oTemplate)
    {
        /* get minimal surveyInfo if we can have a sid, used in ExpressionManager for example */
        if (empty($aData["aSurveyInfo"])) {
            $aData["aSurveyInfo"] = array();
            if (!empty($aData["sid"]) || LimeExpressionManager::getLEMsurveyId()) {
                $sid = empty($aData["sid"]) ? LimeExpressionManager::getLEMsurveyId() : $aData["sid"];
                $language = empty($aData["language"]) ? App()->getLanguage() : $aData["language"];
                $aData["aSurveyInfo"] = getSurveyInfo($sid, $language);
            }
        }
        // We retrieve the definition of the core class and attributes
        // (in the future, should be template dependant done via XML file)
        $aData["aSurveyInfo"] = array_merge($aData["aSurveyInfo"], $oTemplate->getClassAndAttributes());

        $languagecode = App()->getLanguage();
        if (!empty($aData['aSurveyInfo']['sid']) && Survey::model()->findByPk($aData['aSurveyInfo']['sid'])) {
            if (!in_array($languagecode, Survey::model()->findByPk($aData['aSurveyInfo']['sid'])->getAllLanguages())) {
                $languagecode = Survey::model()->findByPk($aData['aSurveyInfo']['sid'])->language;
            }
        }

        $aData["aSurveyInfo"]['languagecode']     = $languagecode;
        $aData["aSurveyInfo"]['dir']              = (getLanguageRTL($languagecode)) ? "rtl" : "ltr";

        if (!empty($aData['aSurveyInfo']['sid'])) {
            $showxquestions                            = App()->getConfig('showxquestions');
            $aData["aSurveyInfo"]['bShowxquestions']  = ($showxquestions == 'show' ||
                ($showxquestions == 'choose' && !isset($aData['aSurveyInfo']['showxquestions'])) ||
                ($showxquestions == 'choose' && $aData['aSurveyInfo']['showxquestions'] == 'Y'));


            // NB: Session is flushed at submit, so sid is not defined here.
            if (
                isset($_SESSION['responses_' . $aData['aSurveyInfo']['sid']]) &&
                isset($_SESSION['responses_' . $aData['aSurveyInfo']['sid']]['totalquestions'])
            ) {
                $aData["aSurveyInfo"]['iTotalquestions'] = $_SESSION['responses_' .
                $aData['aSurveyInfo']['sid']]['totalVisibleQuestions'];
            }

            // Add the survey theme options
            if ($oTemplate->oOptions) {
                foreach ($oTemplate->oOptions as $key => $value) {
                    // TODO: Same issue as commit 2972aea41c51c74db95bfe40c337ae839471152c
                    // Options are not loaded the same way in all places.
                    if (!is_string($value)) {
                        $value = 'N/A';
                    }
                    $aData["aSurveyInfo"]["options"][$key] = $value;
                }
            }
        } else {
            // Add the global theme options
            $oTemplateConfigurationCurrent = Template::getInstance($oTemplate->sTemplateName);
            $aData["aSurveyInfo"]["options"] = isJson($oTemplateConfigurationCurrent['options'])
                ? json_decode((string) $oTemplateConfigurationCurrent['options'], true)
                : $oTemplateConfigurationCurrent['options'];
        }

        $aData = $this->fixDataCoherence($aData);

        return $aData;
    }


    /**
     * It can happen that user set incoherent values for options (like background is on, but no image file is selected)
     * With some server configuration, it can lead to critical errors : empty values in image src or url()
     * can block submition
     * This function will check thoses cases. It can be used in the future for further checks
     * @param array $aData
     * @return array
     *
     */
    private function fixDataCoherence($aData)
    {
        // Clean option with files
        $aFilesOptions = array( 'brandlogo' => 'brandlogofile'  , 'backgroundimage' => 'backgroundimagefile' );

        foreach ($aFilesOptions as $sOption => $sFileOption) {
            if (is_array($aData["aSurveyInfo"]["options"])) {
                if (array_key_exists($sFileOption, $aData["aSurveyInfo"]["options"])) {
                    if (empty($aData["aSurveyInfo"]["options"][$sFileOption])) {
                        $aData["aSurveyInfo"]["options"][$sOption] = "false";
                    }
                }
            }
        }

        return $aData;
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
            if ($extName == "\Twig\Extension\SandboxExtension") {
                // Process to load the sandBox
                $tags       = $this->sandboxConfig['tags'] ?? array();
                $filters    = $this->sandboxConfig['filters'] ?? array();
                $methods    = $this->sandboxConfig['methods'] ?? array();
                $properties = $this->sandboxConfig['properties'] ?? array();
                $functions  = $this->sandboxConfig['functions'] ?? array();
                $policy     = new \Twig\Sandbox\SecurityPolicy($tags, $filters, $methods, $properties, $functions);
                $sandbox    = new \Twig\Extension\SandboxExtension($policy, true);

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
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Syntax
     */
    public function renderPartial($twigView, $aData)
    {
        $oTemplate = Template::getLastInstance();
        $aData = $this->getAdditionalInfos($aData, $oTemplate);
        $this->addRecursiveTemplatesPath($oTemplate);
        return $this->_twig->render($twigView, $aData);
    }

    /**
     * Get the final source file for current context
     * Currently used in theme editor
     * @param string $twigView twigfile to be used (with twig extension)
     * @param TemplateConfiguration $oTemplate
     * @param string[] $extraPath path to be added before plugins add and core views
     * @param string|null $dirName directory name to be added as extra directory inside template view
     * @return string complete filename to be used
     * @throws Exception
     */
    public function getPathOfFile($twigView, $oTemplate = null, $extraPath = array(), $dirName = null)
    {
        if (!$oTemplate) {
            $oTemplate = Template::getLastInstance();
        }
        $this->addRecursiveTemplatesPath($oTemplate, $extraPath, $dirName);
        if (!$this->_twig->getLoader()->exists($twigView)) {
            return null;
        }
        return $this->_twig->getLoader()->getSourceContext($twigView)->getPath();
    }

    /**
     * This is used to add paths in controller views.
     *
     * @return FileLoader
     */
    public function getLoader()
    {
        return $this->_twig->getLoader();
    }

    /**
     * Adds custom user extensions
     * @param array $extensions @see self::$user_extensions
     */
    public function addUserExtensions($extensions)
    {
        foreach ($extensions as $extName) {
            Yii::setPathOfAlias('extName_' . $extName, Yii::app()->getConfig('usertwigextensionrootdir') . '/' . $extName . '/');
            Yii::import("extName_" . $extName . ".*");
            $this->_twig->addExtension(new $extName());
        }
    }
}
