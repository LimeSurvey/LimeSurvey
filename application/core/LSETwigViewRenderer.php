<?php
/**
 * Twig view renderer, LimeSurvey overload
 *
 * Allow to run sandbox Configuration
 * Provide different render methods for different context:
 *
 * - render()                   : for general use
 * - renderQuestion()           : to render a question. It checks if a question template view should be use, else core's view (used from qanda helper).
 * - renderTemplateFromString() : to render a string without any file (used from replacement helper)
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
     private $forcedPath = null;

    /**
     * Adds custom extensions
     * @param array $extensions @see self::$extensions
     */
    public function addExtensions($extensions)
    {
        $this->_twig = parent::getTwig();
        foreach ($extensions as $extName) {
            if ($extName=="Twig_Extension_Sandbox"){
                // Process to load the sandBox
                $tags       = isset($this->sandboxConfig['tags'])?$this->sandboxConfig['tags']:array();
                $filters    = isset($this->sandboxConfig['filters'])?$this->sandboxConfig['filters']:array();
                $methods    = isset($this->sandboxConfig['methods'])?$this->sandboxConfig['methods']:array();
                $properties = isset($this->sandboxConfig['properties'])?$this->sandboxConfig['properties']:array();
                $functions  = isset($this->sandboxConfig['functions'])?$this->sandboxConfig['functions']:array();
                $policy     = new Twig_Sandbox_SecurityPolicy($tags, $filters, $methods, $properties, $functions);
                $sandbox    = new Twig_Extension_Sandbox($policy, true);

                $this->_twig->addExtension($sandbox);
            }
            else{
                $this->_twig->addExtension(new $extName());
            }
        }
    }

    /**
     * Renders a general view file.
     *
     * @param string $sourceFile the view file path
     * @param mixed $data the data to be passed to the view
     * @param boolean $return whether the rendering result should be returned
     * @return mixed the rendering result, or null if the rendering result is not needed.
     */
    public function render( $sView, $aData, $bReturn=true)
    {
        global $thissurvey;

        $this->_twig = parent::getTwig();                                       // Twig object
        $loader      = $this->_twig->getLoader();                               // Twig Template loader
        $oTemplate   = Template::model()->getInstance($thissurvey['template']); // Template configuration

        $requiredView = Yii::getPathOfAlias('application.views').$sView;        // By default, the required view is the core view
        $loader->setPaths(App()->getBasePath().'/views/');                      // Core views path

        // We check if the file is a twig file or a php file
        // This allow us to twig the view one by one, from PHP to twig.
        // The check will be removed when 100% of the views will have been twig
        if( file_exists($requiredView.'.twig') ){

            // We're not using the Yii Theming system, so we don't use parent::renderFile
            // current controller properties will be accessible as {{ this.property }}
            $data['this'] = Yii::app()->getController();
            $template = $this->_twig->loadTemplate($sView.'.twig')->render($data);

            if ($bReturn) {
                return $template;
            }else{
                echo $template;
            }
        }else{
            return Yii::app()->getController()->renderPartial($sView, $aData, $bReturn);
        }
    }

    /**
     * This method is called from qanda helper to render a question view file.
     * It first checks if the question use a template (set in display attributes)
     * If it is the case, it will use the views of that template, else, it will render the core view.
     *
     * @param string $sView     Name of the view to render
     * @param array  $aData     Datas for the view
     */
    public function renderQuestion( $sView, $aData)
    {
        $this->_twig  = parent::getTwig();                                                      // Twig object
        $loader       = $this->_twig->getLoader();                                              // Twig Template loader
        $requiredView = Yii::getPathOfAlias('application.views').$sView;                        // By default, the required view is the core view
        $loader->setPaths(App()->getBasePath().'/views/');                                      // Core views path

        $oQuestionTemplate   = QuestionTemplate::getInstance();                                 // Question template instance has been created at top of qanda_helper::retrieveAnswers()
        $sTemplateFolderName = $oQuestionTemplate->getQuestionTemplateFolderName();             // Get the name of the folder for that question type.

        // Check if question use a custom template and that it provides its own twig view
        if ($sTemplateFolderName){
            $bTemplateHasThisView = $oQuestionTemplate->checkIfTemplateHasView($sView);         // A template can change only one of the view of the question type. So other views should be rendered by core.

            if ($bTemplateHasThisView){
                $sQTemplatePath  = $oQuestionTemplate->getTemplatePath();                       // Question template views path
                $loader->setPaths($sQTemplatePath);                                             // Loader path
                $requiredView = $sQTemplatePath.ltrim($sView, '/');                             // Complete path of the view
            }
        }

        // We check if the file is a twig file or a php file
        // This allow us to twig the view one by one, from PHP to twig.
        // The check will be removed when 100% of the views will have been twig
        if( file_exists($requiredView.'.twig') ){
            // We're not using the Yii Theming system, so we don't use parent::renderFile
            // current controller properties will be accessible as {{ this.property }}
            $aData['this'] = Yii::app()->getController();
            $aData['question_template_attribute'] = $oQuestionTemplate->getCustomAttributes();
            $template = $this->_twig->loadTemplate($sView.'.twig')->render($aData);
            return $template;
        }else{
            return Yii::app()->getController()->renderPartial($sView, $aData, true);
        }
    }

    /**
     * Only use for renderTemplateFromString for now, to force the path of included twig files (in renderTemplateFromString: the template files)
     * It's necessary for the twig include statments: by default, those views would be looked into application/views instead of the template's views directory.
     * @param string $sPath  the path that will be used to render the views.
     */
    public function setForcedPath($sPath)
    {
        $this->forcedPath=$sPath;
    }


    /**
     * Render a string, not a file. It's used from template replace function.
     *
     * @param string  $line     The line of HTML/Twig to render
     * @param array   $aDatas   Array containing the datas needed to render the view ($thissurvey)
     * @param boolean $bReturn  Should the function echo the result, or just returns it?
     */
    public function renderTemplateFromString( $line, $aDatas, $bReturn)
    {
        // If no redata, there is no need to use twig, so we just return the line.
        // This happen when calling templatereplace() from admin, to replace some keywords.
        // NOTE: this check is already done in templatereplace().
        if (is_array($aDatas)){
            $this->_twig      = $twig = parent::getTwig();

            // At this point, forced path should not be nulled.
            // It contains the path to the template's view directory for twig include statements
            if (!is_null($this->forcedPath)){
                $loader       = $this->_twig->getLoader();
                $loader->setPaths($this->forcedPath);
            }

            // Plugin for blocks replacement
            // TODO: add blocks to template....
            $event = new PluginEvent('beforeTwigRenderTemplate');

            if (!empty($aDatas['aSurveyInfo']['sid'])){
                $surveyid = $aDatas['aSurveyInfo']['sid'];
                $event->set('surveyId', $aDatas['aSurveyInfo']['sid']);

                if (!empty($_SESSION['survey_'.$surveyid]['srid'])){
                    $aDatas['aSurveyInfo']['bShowClearAll'] = ! SurveyDynamic::model($surveyid)->isCompleted($_SESSION['survey_'.$surveyid]['srid']);
                }

            }

            App()->getPluginManager()->dispatchEvent($event);
            $aPluginContent = $event->getAllContent();
            if (!empty($aPluginContent['sTwigBlocks'])){
                $line = $line.$aPluginContent['sTwigBlocks'];
            }

            // Twig rendering
            $oTwigTemplate = $twig->createTemplate($line);
            $nvLine        = $oTwigTemplate->render($aDatas, false);
        }else{
            $nvLine = $line;
        }
        return $nvLine;
    }
}
