<?php
namespace LimeSurvey\PluginManager;
class PluginEventContent
{
    
    const APPEND = 'append';
    const PREPEND = 'prepend';
    
    protected $_content = null;
    protected $_cssClass = array('pluginblock'=>'pluginblock');
    protected $_cssId = '';
    
    /**
     * @param string $content
     * @param string $cssClass
     * @param string $id
     */
    public function __construct($content = null, $cssClass = null, $id = null)
    {
        $this->setContent($content);
        $this->setCssClass($cssClass);
        $this->setCssId($id);
    }
    
    /**
     * Add a css class to use for this content
     * 
     * @param string $cssClass
     * @return PluginEventContent
     */
    public function addCssClass($cssClass)
    {
        if (!empty($cssClass)) {
            $this->_cssClass[$cssClass] = array($cssClass);
        }
        
        return $this;
    }
    
    /**
     * Add to existing content, by default append but optionally prepend it
     * 
     * @param string $content
     * @param string $placement append or prepend
     */
    public function addContent($content = '', $placement = self::APPEND)
    {
        if (strtolower($placement) === self::APPEND) {
            $this->_content .= $content;
        } else {
            $this->_content = $content.$this->_content;
        }
        
        return $this;
    }
    
    /**
     * Clears exisiting content
     * 
     * @return PluginEventContent
     */
    public function cleanContent()
    {
        $this->_content = null;
        
        return $this;
    }
    
    public function getContent()
    {
        return $this->_content;
    }
    
    public function getCssClass()
    {
        return implode(' ', $this->_cssClass);
    }
    
    public function getCssId()
    {
        return $this->_cssId;
    }
    
    public function hasContent()
    {
        return !is_null($this->getContent());
    }
    
    /**
     * Replace existing content
     * 
     * @param string $content
     * @return PluginEventContent
     */
    public function setContent($content = '')
    {
        $this->_content = $content;
        
        return $this;
    }
    
    /**
     * Set the css class to use for this content
     * 
     * @param string $cssClass
     * @return PluginEventContent
     */
    public function setCssClass($cssClass)
    {
        if (!empty($cssClass)) {
            $this->_cssClass = array($cssClass => $cssClass);
        } else {
            $this->_cssClass = array();
        }
        
        return $this;        
    }
    
    /**
     * Set the css id to use for this content
     * 
     * @param string $id
     * @return PluginEventContent
     */
    public function setCssId($id)
    {
        $this->_cssId = $id;
    }
}