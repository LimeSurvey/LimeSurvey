<?php
/**
 * Twig view renderer, LimeSurvey overload
 *
 * Allow to run sandbox Configuration
 * Overload renderFile method to check if template's views should be use.
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
                $tags       = isset($this->sandboxConfig['tags'])?$this->sandboxConfig['tags']:array();
                $filters    = isset($this->sandboxConfig['filters'])?$this->sandboxConfig['filters']:array();
                $methods    = isset($this->sandboxConfig['methods'])?$this->sandboxConfig['methods']:array();
                $properties = isset($this->sandboxConfig['properties'])?$this->sandboxConfig['properties']:array();
                $functions  = isset($this->sandboxConfig['functions'])?$this->sandboxConfig['functions']:array();
                $policy     = new Twig_Sandbox_SecurityPolicy($tags, $filters, $methods, $properties, $functions);
                $sandbox    = new Twig_Extension_Sandbox($policy, true);

                $this->_twig->addExtension($sandbox);
            }else{
                $this->_twig->addExtension(new $extName());
            }
        }
    }

    /**
     * Renders a view file.
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

        // Check if template provides its own twig view
        if(file_exists($oTemplate->viewPath.ltrim($sView, '/').'.twig')){
            $loader->setPaths($oTemplate->viewPath);                            // Template views path
            $sView        = str_replace('/views/', '', $sView );
            $requiredView = $oTemplate->viewPath.ltrim($sView, '/');
        }

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
     * Only use for renderTemplateFromString for now, to force the path of included twig files
     */
    public function setForcedPath($sPath)
    {
        $this->forcedPath=$sPath;
    }

    public function renderQuestion( $sView, $aData)
    {
        $this->_twig  = parent::getTwig();                                      // Twig object
        $loader       = $this->_twig->getLoader();                              // Twig Template loader
        $requiredView = Yii::getPathOfAlias('application.views').$sView;        // By default, the required view is the core view
        $loader->setPaths(App()->getBasePath().'/views/');                      // Core views path

        $oQuestionTemplate = QuestionTemplate::getInstance();                   // Question template instance has been created at top of qanda_helper::retrieveAnswers()


        /*
        $aQuestionAttributes = QuestionAttribute::model()->getQuestionAttributes($oQuestion->qid);
        $sTemplateFolderName = $aQuestionAttributes['question_template'];
        $sTemplateFolderName = QuestionTemplate::getQuestionTemplate($oQuestion);
        */

        $sTemplateFolderName = $oQuestionTemplate->getQuestionTemplateFolderName();

        // Check if question use a custom teplate provides its own twig view
        if ($sTemplateFolderName){
            $bTemplateHasThisView = $oQuestionTemplate->checkIfTemplateHasView($sView);
            if ($bTemplateHasThisView){
                $sQTemplatePath  = $oQuestionTemplate->getTemplatePath();   // Question template views path
                $loader->setPaths($sQTemplatePath);
                $requiredView = $sQTemplatePath.ltrim($sView, '/');
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
     *
     */
    public function renderTemplateFromString( $line, $redata, $bReturn)
    {
        if (is_array($redata)){
            $this->_twig      = $twig = parent::getTwig();

            if (!is_null($this->forcedPath)){
                $loader       = $this->_twig->getLoader();                              // Twig Template loader
                $loader->setPaths($this->forcedPath);
            }

            $oTwigTemplate    = $twig->createTemplate($line);
            $nvLine = $oTwigTemplate->render($redata, false);
        }else{
            $nvLine = $line;
        }
        return $nvLine;

    }
}
