<?php
/**
 * EditableField class file.
 * 
 * This widget makes editable single attribute of model
 * 
 * @author Vitaliy Potapov <noginsk@rambler.ru>
 * @link https://github.com/vitalets/yii-bootstrap-editable
 * @copyright Copyright &copy; Vitaliy Potapov 2012
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version 1.0.0
 * @since 10/2/12 12:24 AM  renamed class for YiiBooster integration antonio ramirez <antonio@clevertech.ibz>
 */
 
class TbEditableField extends CWidget
{
    //for all types
    public $model = null;
    public $attribute = null;
    public $type = null;
    public $url = null;
    public $title = null;
    public $emptytext = null;
    public $text = null; //will be used as content
    public $value = null;
    public $placement = null;
    public $inputclass = null;
    public $autotext = null;

    //for text & textarea
    public $placeholder = null;
    
    //for select
    public $source = array();
    public $prepend = null;

    //for date
    public $format = null;
    public $viewformat = null;
    public $language = null;
    public $weekStart = null;
    public $startView = null;

    //methods
    public $validate = null;
    public $success = null;
    public $error = null;
    
    //events
    public $onInit = null;
    public $onUpdate = null;
    public $onRender = null;
    public $onShown = null;
    public $onHidden = null;

    //js options
    public $options = array();
    
    //html options
    public $htmlOptions = array();

    //weather to encode text on output
    public $encode = true;

    //if false text will not be editable, but will be rendered
    public $enabled = null;

    public function init()
    {   
        if (!$this->model) {
            throw new CException(Yii::t('zii', 'Parameter "model" should be provided for Editable'));
        }
        if (!$this->attribute) {
            throw new CException(Yii::t('zii', 'Parameter "attribute" should be provided for Editable'));
        }
        if (!$this->model->hasAttribute($this->attribute)) {
            throw new CException(Yii::t('zii', 'Model "{model}" does not have attribute "{attribute}"',
	                array('{model}'=>get_class($this->model), '{attribute}'=>$this->attribute)));
        }
 
        parent::init();
                
        if ($this->type === null) {
            $this->type = 'text';
            //try detect type from metadata.
            if (array_key_exists($this->attribute, $this->model->tableSchema->columns)) {
                $dbType = $this->model->tableSchema->columns[$this->attribute]->dbType;
                if($dbType == 'date' || $dbType == 'datetime') $this->type = 'date';
                if(stripos($dbType, 'text') !== false) $this->type = 'textarea';
            }
        }

        /*
        * unfortunatly datepicker's format does not match Yii locale dateFormat
        * and we cannot take format from application locale
        * 
        * see http://www.unicode.org/reports/tr35/#Date_Format_Patterns
        * 
        if($this->type == 'date' && $this->format === null) {
            $this->format = Yii::app()->locale->getDateFormat();
        }
        */
        
        /* generate text from model attribute (for all types except 'select'. 
        *  For select/date autotext will be applied)
        */ 
        if (!strlen($this->text) && $this->type != 'select' && $this->type != 'date') {
            $this->text = $this->model->getAttribute($this->attribute);
        }

        //if enabled not defined directly, set it to true only for safe attributes
        if($this->enabled === null) {
            $this->enabled = $this->model->isAttributeSafe($this->attribute);
        }
        
        //if not enabled --> just print text        
        if (!$this->enabled) {
            return;
        }

        //language: use config's value if not defined directly
        if ($this->language === null && yii::app()->language) {
            $this->language = yii::app()->language;
        }

        //normalize url from array if needed
        $this->url = CHtml::normalizeUrl($this->url);

        //generate title from attribute label
        if ($this->title === null) {
            //todo: i18n here. Add messages folder to extension
            $this->title = (($this->type == 'select' || $this->type == 'date') ? Yii::t('zii', 'Select') : Yii::t('zii', 'Enter')) . ' ' . $this->model->getAttributeLabel($this->attribute);
        }

        $this->buildHtmlOptions();
        $this->buildJsOptions();
        $this->registerAssets();
    }

    public function buildHtmlOptions()
    {
        //html options
        $htmlOptions = array(
            'href'      => '#',
            'rel'       => $this->getSelector(),
            'data-pk'   => $this->model->primaryKey,
        );

        //for select we need to define value directly
        if ($this->type == 'select') {
            $this->value = $this->model->getAttribute($this->attribute);
            $this->htmlOptions['data-value'] = $this->value;
        }
        
        //for date we use 'format' to put it into value (if text not defined)
        if ($this->type == 'date' && !strlen($this->text)) {
            $this->value = $this->model->getAttribute($this->attribute);
            
            //if date comes as object, format it to string
            if($this->value instanceOf DateTime) {
                /* 
                * unfortunatly datepicker's format does not match Yii locale dateFormat,
                * we need replacements below to convert date correctly
                */
                $count = 0;
                $format = str_replace('MM', 'MMMM', $this->format, $count);
                if(!$count) $format = str_replace('M', 'MMM', $format, $count);
                if(!$count) $format = str_replace('m', 'M', $format);
                
                $this->value = Yii::app()->dateFormatter->format($format, $this->value->getTimestamp()); 
            }            

            $this->htmlOptions['data-value'] = $this->value;
        }        

        //merging options
        $this->htmlOptions = CMap::mergeArray($this->htmlOptions, $htmlOptions);
    }

    public function buildJsOptions()
    {
        $options = array(
            'type'  => $this->type,
            'url'   => $this->url,
            'name'  => $this->attribute,
            'title' => CHtml::encode($this->title),
        );

        if ($this->emptytext) {
            $options['emptytext'] = $this->emptytext;
        }
        
        if ($this->placement) {
            $options['placement'] = $this->placement;
        }
        
        if ($this->inputclass) {
            $options['inputclass'] = $this->inputclass;
        }    
        
        if ($this->autotext) {
            $options['autotext'] = $this->autotext;
        }            

        switch ($this->type) {
            case 'text':
            case 'textarea':
                if ($this->placeholder) {
                    $options['placeholder'] = $this->placeholder;
                }
                break;
            case 'select':
                if ($this->source) {
                    $options['source'] = $this->source;
                }
                if ($this->prepend) {
                    $options['prepend'] = $this->prepend;
                }
                break;
            case 'date':
                if ($this->format) {
                    $options['format'] = $this->format;
                }
                if ($this->viewformat) {
                    $options['viewformat'] = $this->viewformat;
                }                
                if ($this->language && substr($this->language, 0, 2) != 'en') {
                    $options['datepicker']['language'] = $this->language;
                }
                if ($this->weekStart !== null) {
                    $options['weekStart'] = $this->weekStart;
                }
                if ($this->startView !== null) {
                    $options['startView'] = $this->startView;
                }
                break;
        }

        //methods
        foreach(array('validate', 'success', 'error') as $event) {
            if($this->$event!==null) {
                $options[$event]=(strpos($this->$event, 'js:') !== 0 ? 'js:' : '') . $this->$event;
            }
        }        

        //merging options
        $this->options = CMap::mergeArray($this->options, $options);
    }

    public function registerClientScript()
    {
        $script = "$('a[rel={$this->htmlOptions['rel']}]')";
          
        //attach events
        foreach(array('init', 'update', 'render', 'shown', 'hidden') as $event) {
            $property = 'on'.ucfirst($event); 
            if ($this->$property) {
                // CJavaScriptExpression appeared only in 1.1.11, will turn to it later
                //$event = ($this->onInit instanceof CJavaScriptExpression) ? $this->onInit : new CJavaScriptExpression($this->onInit);
                $eventJs = (strpos($this->$property, 'js:') !== 0 ? 'js:' : '') . $this->$property;
                $script .= "\n.on('".$event."', ".CJavaScript::encode($eventJs).")";
            }
        }

        //apply editable
        $options = CJavaScript::encode($this->options);        
        $script .= ".editable($options);";
        
        Yii::app()->getClientScript()->registerScript(__CLASS__ . '#' . $this->id, $script);
        
        return $script;
    }


	/**
	 * @since 10/2/12 12:32 AM  refactored to make use of Component's registerAssetCss|Js function.
	 * @author antonio ramirez <antonio@clevertech.biz>
	 */
	public function registerAssets()
    {
        //if bootstrap extension installed, but no js registered -> register it!
        if (($bootstrap = Yii::app()->getComponent('bootstrap')) && !$bootstrap->enableJS) {
            $bootstrap->registerCorePlugins(); //enable bootstrap js if needed
        }

	    $bootstrap->registerAssetCss('bootstrap-editable.css') ;
	    $bootstrap->registerAssetJs('bootstrap-editable' . (!YII_DEBUG ? '.min' : '') . '.js', CClientScript::POS_END);

        //include locale for datepicker
        if ($this->type == 'date' && $this->language && substr($this->language, 0, 2) != 'en') {

             $bootstrap->registerAssetJs('locales/bootstrap-datepicker.'. str_replace('_', '-', $this->language).'.js', CClientScript::POS_END);
        }
    }

    public function run()
    {
        if($this->enabled) {
            $this->registerClientScript();
            $this->renderLink();
        } else {
            $this->renderText();
        }
    }

    public function renderLink()
    {
        echo CHtml::openTag('a', $this->htmlOptions);
        $this->renderText();
        echo CHtml::closeTag('a');
    }

    public function renderText()
    {   
        $encodedText = $this->encode ? CHtml::encode($this->text) : $this->text;
        if($this->type == 'textarea') {
             $encodedText = preg_replace('/\r?\n/', '<br>', $encodedText);
        }
        echo $encodedText;
    }    
    
    public function getSelector()
    {
        return get_class($this->model) . '_' . $this->attribute . ($this->model->primaryKey ? '_' . $this->model->primaryKey : '_new');
    }
}
