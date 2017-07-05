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
        $this->_twig = parent::getTwig();                                       // Twig object
        $loader      = $this->_twig->getLoader();                               // Twig Template loader

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

    public function renderTemplateForTemplateEditor($sView, $aDatas, $oEditedTemplate)
    {
        $oTemplate = $this->getTemplateForView($sView, $oEditedTemplate);
        $line      = file_get_contents($oTemplate->viewPath.$sView);
        $result = $this->renderTemplateFromString( $line, $aDatas, $oTemplate, true);
        return $result;
    }

    public function renderTemplateFromFile($sView, $aDatas, $bReturn)
    {
        $oRTemplate = Template::model()->getInstance();
        $oTemplate = $this->getTemplateForView($sView, $oRTemplate);
        $line      = file_get_contents($oTemplate->viewPath.$sView);
        $result = $this->renderTemplateFromString( $line, $aDatas, $oTemplate, $bReturn);
        if ($bReturn){
            return $result;
        }
    }

    private function getTemplateForView($sView, $oRTemplate)
    {
        while (!file_exists($oRTemplate->viewPath.$sView)){

            $oMotherTemplate = $oRTemplate->oMotherTemplate;
            if(!($oMotherTemplate instanceof TemplateConfiguration)){
                return false;
                break;
            }
            $oRTemplate = $oMotherTemplate;
        }

        return $oRTemplate;
    }

    /**
     * Render a string, not a file. It's used from template replace function.
     *
     * @param string  $line     The line of HTML/Twig to render
     * @param array   $aDatas   Array containing the datas needed to render the view ($thissurvey)
     * @param boolean $bReturn  Should the function echo the result, or just returns it?
     */
    public function renderTemplateFromString( $line, $aDatas, $oRTemplate, $bReturn=false)
    {
        $this->_twig  = $twig = parent::getTwig();
        $loader       = $this->_twig->getLoader();
        $loader->addPath($oRTemplate->viewPath);
        Yii::app()->clientScript->registerPackage( $oRTemplate->sPackageName );

        // Set Langage // TODO remove one of the Yii::app()->session see bug #5901
        if (!empty($aDatas['aSurveyInfo']['sid'])){
            if (Yii::app()->session['survey_'.$aDatas['aSurveyInfo']['sid']]['s_lang'] ){
                $languagecode =  Yii::app()->session['survey_'.$aDatas['aSurveyInfo']['sid']]['s_lang'];
            }elseif ($aDatas['aSurveyInfo']['sid']  && Survey::model()->findByPk($aDatas['aSurveyInfo']['sid'])){
                $languagecode = Survey::model()->findByPk($aDatas['aSurveyInfo']['sid'])->language;
            }else{
                $languagecode = Yii::app()->getConfig('defaultlang');
            }

            $aDatas["aSurveyInfo"]['languagecode'] = $languagecode;
            $aDatas["aSurveyInfo"]['dir']          = (getLanguageRTL($languagecode))?"rtl":"ltr";
        }

        // Add all mother templates path
        while($oRTemplate->oMotherTemplate instanceof TemplateConfiguration){
            $oRTemplate = $oRTemplate->oMotherTemplate;
            $loader->addPath($oRTemplate->viewPath);
        }

        // Add the template options
        foreach($oRTemplate->oOptions as $oOption){
            foreach($oOption as $key => $value){
                $aDatas["aSurveyInfo"]["options"][$key] = (string) $value;
            }
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

        if (!$bReturn){
            ob_start(function($buffer, $phase)
            {
                App()->getClientScript()->render($buffer);
                App()->getClientScript()->reset();
                return $buffer;
            });

            ob_implicit_flush(false);
            echo $nvLine;
            ob_flush();

            Yii::app()->end();
        }else{
            return $nvLine;
        }
    }
}
