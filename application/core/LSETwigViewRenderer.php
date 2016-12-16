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
     public $sandboxConfig = array();
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
     * This method is required by {@link IViewRenderer}.
     * @param CBaseController $context the controller or widget who is rendering the view file.
     * @param string $sourceFile the view file path
     * @param mixed $data the data to be passed to the view
     * @param boolean $return whether the rendering result should be returned
     * @return mixed the rendering result, or null if the rendering result is not needed.
     */
    public function renderFile($context, $sView, $aData, $bReturn=true)
    {
        global $thissurvey;
        $requiredView = Yii::getPathOfAlias('application.views').$sView;
        if(isset($thissurvey['template']))
        {
            $sTemplate = $thissurvey['template'];
            $oTemplate = Template::model()->getInstance($sTemplate);                // we get the template configuration
            if($oTemplate->overwrite_question_views===true && Yii::app()->getConfig('allow_templates_to_overwrite_views'))                         // If it's configured to overwrite the views
            {
                if( file_exists($requiredView.'.php') || file_exists($requiredView.'.twig') )                             // If it the case, the function will render this view
                {
                    Yii::setPathOfAlias('survey.template.view', $requiredView);     // to render a view from an absolute path outside of application/, path alias must be used.
                    $sView = 'survey.template.view';                                // See : http://www.yiiframework.com/doc/api/1.1/CController#getViewFile-detail
                    $requiredView = $oTemplate->viewPath.ltrim($sView, '/');        // Then we check if it has its own version of the required view
                }
            }
        }

        // Twig or not twig?
        if( file_exists($requiredView.'.twig') ){
            return parent::renderFile( $context, $requiredView.'.twig', $aData, $bReturn);
        }else{
            return Yii::app()->getController()->renderPartial($sView, $aData, $bReturn);
        }
    }


    /**
     *
     */
    public function renderTemplateFromString( $line, $redata, $bReturn)
    {
        $this->_twig      = $twig = parent::getTwig();
        $oTwigTemplate    = $twig->createTemplate($line);
        $nvLine = $oTwigTemplate->render($redata, false);
        return $nvLine;
    }
}
