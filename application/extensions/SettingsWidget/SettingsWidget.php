<?php

class SettingsWidget extends CWidget
{
    protected static $counter = 0;

    public $action;
    /**
     *
     * @var array Buttons for the form.
     */
    public $buttons = array();

    /**
     * Set to false to render elements in an existing form.
     * @var boolean
     */
    public $form = true;
    public $formHtmlOptions = array();
    public $fieldHtmlOptions = array();

    public $method = 'post';
    public $prefix;
    public $settings = array();

    public $title;
    public $labelWidth=6;
    public $controlWidth=6;
    /** @var string - Raw HTML to output last */
    public $additionalHtml = "";

    public function beginForm()
    {
        if ($this->form)
        {
            echo CHtml::beginForm($this->action, $this->method,$this->formHtmlOptions);
        }
        else
        {
            $this->fieldHtmlOptions=array_replace($this->formHtmlOptions,$this->fieldHtmlOptions);
        }
        echo CHtml::openTag('fieldset', array_replace($this->fieldHtmlOptions, array('id' => $this->getId())));
        if (isset($this->title))
        {
            echo CHtml::tag('legend', array(), $this->title);
        }
    }

    public function endForm()
    {
        echo CHtml::closeTag('fieldset');
        if ($this->form)
        {
            echo CHtml::endForm();
        }
    }
    public function init() {
        parent::init();

        // Register assets.
        Yii::app()->getClientScript()->registerPackage('jquery');
        if (getLanguageRTL(App()->language))
        {
            Yii::app()->getClientScript()->registerCssFile(App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/settingswidget-rtl.css'));
        }
        else
        {
            Yii::app()->getClientScript()->registerCssFile(App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/settingswidget.css'));
        }

        Yii::app()->getClientScript()->registerScriptFile(App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/settingswidget.js'));

        // Add default form class.
        $this->formHtmlOptions['class'] = isset($this->formHtmlOptions['class']) ? $this->formHtmlOptions['class'] . " settingswidget form-horizontal" : 'settingswidget form-horizontal';


        // Start form
        $this->beginForm();
    }

    /**
     * Render a button
     *
     * @param string $label
     * @param string|array $metaData
     * @return string
     */
    protected function renderButton($label, $metaData)
    {
        //Button can come from 2 system, by pluginSettings>settings>button ot by by pluginSettings>buttons
        if (is_string($metaData))
        {
            $label = $metaData;
            $metaData = array(
                'htmlOptions'=>array(),
            );
        }


        $metaData['class'][]='btn';
        $htmlOptions = $this->htmlOptions($metaData);

        if (isset($metaData['type']) && $metaData['type'] == 'link')
        {
            return CHtml::link($label,$metaData['href'],$htmlOptions); // This allow cancel without js
        }
        elseif(isset($metaData['type']))
        {
            $htmlOptions['type']=$metaData['type'];
            if(!empty($metaData['name']) && is_string($metaData['name']))
                $htmlOptions['name']=$metaData['name'];
            return CHtml::htmlButton($label, $htmlOptions);
        }
        elseif(isset($htmlOptions['type'])) // Allow type button or cancel in pluginSettings>settings>button
        {
            return CHtml::htmlButton($label, $htmlOptions);
        }
        else
        {
            return CHtml::submitButton($label, $htmlOptions);
        }
    }

    protected function renderButtons()
    {
        if(!empty($this->buttons))
        {
            $aHtmlButtons=array();
            foreach ($this->buttons as $label => $htmlOptions)
            {
                if (is_string($htmlOptions))
                {
                    $label = $htmlOptions;
                    $htmlOptions=array(
                        'htmlOptions'=>array()
                    );
                }
                $aHtmlButtons[]= $this->renderButton($label, $htmlOptions);
            }
            echo CHtml::tag('div', array('class' => "clearfix col-md-offset-{$this->labelWidth}"),implode(" ",$aHtmlButtons));
        }
    }

    protected function renderSetting($name, $metaData, $form = null, $return = false,$wrapper='div')
    {
        // TODO: Weird hack that fixes some rendering issues after moving to Bootstrap2
        echo "&nbsp;";

        // No type : invalid setting
        if(!isset($metaData['type']))
        {
            // TODO: assert or throw exception
            return "";
        }

        // Fix $metaData
        $metaData=$this->fixMetaData($name, $metaData);
        // Fix $name
        if (isset($this->prefix))
        {
            $name = "{$this->prefix}[$name]";
        }
        if ($metaData['localized'])
        {
            $name = "{$name}[{$metaData['language']}]";
        }
        // Find function
        $function = "render{$metaData['type']}";

        // Construct the content
        // The labels
        $content  = $this->renderLabel($name, $metaData);
        // The control
        $content .= CHtml::openTag('div',$metaData['controlOptions']);
        // The input
        $content .= $this->$function($name, $metaData, $form);
        // The help
        $content .= $this->renderHelp($name, $metaData);
        $content .= CHtml::closeTag('div');

        $result=CHtml::tag($wrapper,array('class'=>"form-group setting setting-{$metaData['type']}", 'data-name' => $name),$content);

        if($return)
        {
            return $result;
        }
        else
        {
            echo $result;
        }
    }

    protected function renderSettings()
    {
        foreach($this->settings as $name => $metaData)
        {
            $this->renderSetting($name, $metaData, null, false, 'div');
        }
    }

    public function run() {
        parent::run();

        // Render settings
        $this->renderSettings();
        // Render buttons
        $this->renderButtons();
        // Render additional HTML
        $this->renderAdditionalHtml();
        // End form
        $this->endForm();
    }

    /**
     * Echo additional HTML, without any magic
     *
     * @since 2015-12-16
     * @author Olle Haerstedt
     */
    protected function renderAdditionalHtml()
    {
        echo $this->additionalHtml;
    }


    /**
     * fix metaData for a setting : set default etc.
     *
     */
    public function fixMetaData($name,$metaData){

        $defaults = array(
            'class' => array(),
            'htmlOptions'=>array(),
            'type' => 'string',
            'htmlOptions' => array(),
            'labelOptions' => array( // html option for the control-label part (not the label, but the wrapper)
                'class' => "default"
            ),
            'help'=> null,
            'controlOptions'=> array(// html option for the control-option part (wrapper of input(s))
                'class' => "default"
            ),
            'localized'=>false,
        );
        $metaData = array_merge($defaults, $metaData);

        // col-sm-6/col-sm-6 used in survey settings, sm-4/sm-6 in global : use sm-4/sm-6 for plugins ?
        $metaData['labelOptions']['class'].=" control-label col-sm-{$this->labelWidth}";
        // Set the witdth of control-option according to existence of label
        if(!isset($metaData['label'])){
            $metaData['controlOptions']['class'].=" col-sm-12";
        }
        else{
            $metaData['controlOptions']['class'].=" col-sm-{$this->controlWidth}";
        }
        $metaData['controlOptions']['class'].=" controls";

        if (is_string($metaData['class']))
        {
            $metaData['class'] = array($metaData['class']);
        }
        // Handle localization.
        if ($metaData['localized'])
        {
            $name = "{$name}[{$metaData['language']}]";
            if (isset($metaData['current']) && is_array($metaData['current']) && isset($metaData['current'][$metaData['language']]))
            {
                $metaData['current'] = $metaData['current'][$metaData['language']];
            }
            else
            {
                unset($metaData['current']);
            }
        }

        // Handle styles
        if (isset($metaData['style']) && is_array($metaData['style']))
        {
            $style = '';
            foreach($metaData['style'] as $key => $value)
            {
                $style .= "$key : $value;";
            }
            $metaData['style'] = $style;
        }
        return $metaData;
    }
    /**
     * render label according to type and $metaData['label']
     */
    public function renderLabel($name, $metaData){
        if(!isset($metaData['label']))
        {
            return "";
        }
        else if(!in_array($metaData['type'], array('list','logo','link','info')))
        {
            return CHtml::label($metaData['label'], CHtml::getIdByName($name), $metaData['labelOptions']);
        }
        else
        {
            return CHtml::tag('div',$metaData['labelOptions'], $metaData['label']);
        }
    }
    /**
     * render help/desscription according to type and $metaData['help']
     *
     */
    public function renderHelp($name,$metaData){
        if(!is_string($metaData['help']))
            return "";
        return CHtml::tag('div', array('class' => 'help-block'),$metaData['help']);// p is more clean but have class in adminstyle
    }

    /***********************************************************************
     * Settings renderers.
     **********************************************************************/

    public function renderBoolean($name, array $metaData, $form = null)
    {
        $htmlOptions = $this->htmlOptions($metaData,$form);
        $value = isset($metaData['current']) ? $metaData['current'] : '';
        //~ return CHtml::radioButtonList($name, $value, array(
            //~ 0 => 'False',
            //~ 1 => 'True'
        //~ ), $htmlOptions);
        return CHtml::tag('div', $htmlOptions,
            $this->widget('yiiwheels.widgets.switch.WhSwitch', array(
                'name' => $name,
                'value' => $value,
                'onLabel'=>gT('On'),
                'offLabel' => gT('Off'),
                'htmlOptions' => $htmlOptions,
            ), true)
        );
    }

    public function renderCheckbox($name, array $metaData, $form = null)
    {
        $htmlOptions = $this->htmlOptions($metaData,$form,array('uncheckValue'=>false));
        $value = isset($metaData['current']) ? (bool) $metaData['current'] : false;
        return CHtml::checkBox($name, $value,$htmlOptions);
    }

    public function renderFloat($name, array $metaData, $form = null)
    {
        $metaData['class'][] = 'form-control';
        $htmlOptions = $this->htmlOptions($metaData,$form,array('step'=>'any'));// step can be replaced by plugin developer
        $value = isset($metaData['current']) ? $metaData['current'] : '';
        return CHtml::numberField($name, $value, $htmlOptions);
    }

    public function renderHtml($name, array $metaData, $form = null)
    {
        $metaData['class'][] = 'form-control';
        $value = isset($metaData['current']) ? $metaData['current'] : '';
        $metaData['class'][] = 'htmleditor';
        $htmlOptions = $this->htmlOptions($metaData,$form);
        $editorOptions = array_merge(array(
            'html' => true,
        ), isset($metaData['editorOptions']) ? $metaData['editorOptions'] : array());
        return CHtml::tag('div', array("style"=>'height:auto;width:100%','class'=>'well'),
            $this->widget('yiiwheels.widgets.html5editor.WhHtml5Editor', array(
                'name' => $name,
                'value' => $value,
                'width' => isset($metaData['width']) ? $metaData['width'] : '100%',
                'height' => isset($metaData['height']) ? $metaData['height'] : '400px',
                'pluginOptions' =>  $editorOptions,
                'htmlOptions' => $htmlOptions,
            ), true)
        );
    }

    public function renderInfo($name, array $metaData, $form = null)
    {
        $value = isset($metaData['content']) ? $metaData['content'] : '';
        if (is_array($value)) { throw new CException('wrong type' . $name); }
        $htmlOptions = $this->htmlOptions($metaData);
        return CHtml::tag('div',$htmlOptions,$value);
    }

    public function renderInt($name, array $metaData, $form = null)
    {
        $metaData['class'][] = 'form-control';
        $value = isset($metaData['current']) ? $metaData['current'] : '';
        if (is_array($value)) { throw new CException('wrong type' . $name); }
        $htmlOptions = $this->htmlOptions($metaData,$form,array('step'=> 1,'pattern' => '\d+'));
        return CHtml::numberField($name, $value, $htmlOptions);
    }

    public function renderJson($name, array $metaData, $form = null)
    {
        $metaData['class'][] = 'form-control'; // Needed ?
        $value = isset($metaData['current']) ? $metaData['current'] : '';
        $metaData['class'][] = 'jsoneditor-wrapper';
        $htmlOptions = array_merge($metaData['htmlOptions'],array('class'=>implode(' ',$metaData['class'])));
        $htmlOptions = $this->htmlOptions($metaData,$form);
        $editorOptions = array_merge(array(
            'mode' => 'form',
            'modes' => array('form', 'code', 'tree', 'text')
        ), isset($metaData['editorOptions']) ? $metaData['editorOptions'] : array());
        return $this->widget('ext.yii-jsoneditor.JsonEditor', array(
            'name' => $name,
            'value' => $value,
            'editorOptions' => $editorOptions
        ), true);
    }

    public function renderLogo($name, array $metaData, $form = null)
    {
        $alt=isset($metaData['alt']) ? $metaData['alt'] : '';
        $htmlOptions = $this->htmlOptions($metaData);
        return CHtml::image($metaData['path'],$alt,$htmlOptions);
    }

    public function renderRadio($name, array $metaData, $form = null)
    {
        $value = isset($metaData['current']) ? $metaData['current'] : (isset($metaData['default']) ? $metaData['default'] : null);
        $htmlOptions = $this->htmlOptions($metaData,$form);
        return CHtml::radioButtonList($name, $value, $metaData['options'],$htmlOptions);
    }

    public function renderRelevance($name, array $metaData, $form = null)
    {
        $metaData['class'][] = 'relevance';
        $metaData['class'][] = 'form-control';
        $htmlOptions = $this->htmlOptions($metaData,$form);
        $value = isset($metaData['current']) ? $metaData['current'] : '';
        return CHtml::textArea($name, $value, $htmlOptions);
    }

    public function renderSelect($name, array $metaData, $form = null)
    {
        $metaData['class'][] = 'form-control';
        $value = isset($metaData['current']) ? $metaData['current'] : (isset($metaData['default']) ? $metaData['default'] : null);
        $htmlOptions = $this->htmlOptions($metaData,$form);
        $select2Options=array_merge(
            array(
                'minimumResultsForSearch' => 8,
                'dropdownAutoWidth'=> true,
                'width' => "js: function(){ return Math.max.apply(null, $(this.element).find('option').map(function() { return $(this).text().length; }))+'em' }",
            ),(isset($metaData['selectOptions']) ? $metaData['selectOptions'] : array())
        );
        $properties = array(
            'data' => $metaData['options'],
            'name' => $name,
            'value' => $value,
            'pluginOptions' => $select2Options,
            'htmlOptions'=>$htmlOptions,
        );
        $properties['events']=isset($metaData['events']) ? $metaData['events'] : array();
        // allow to submit the form when this element changes
        if (isset($metaData['submitonchange']) && $metaData['submitonchange']) {
            $properties['events']['change']='js: function(e) { this.form.submit();}';
        }

        // Remove class 'form-control' because of double styling
        // TODO: Where is this class added in the first place??
        $html = App()->getController()->widget('yiiwheels.widgets.select2.WhSelect2', $properties, true);
        $html = str_replace('form-control', '', $html);
        return $html;

    }

    public function renderString($name, array $metaData, $form = null)
    {
        $metaData['class'][] = 'form-control';
        $value = isset($metaData['current']) ? $metaData['current'] : '';
        $htmlOptions = $this->htmlOptions($metaData,$form,array('size'=>50));
        return CHtml::textField($name, $value, $htmlOptions);
    }

    public function renderEmail($name, array $metaData, $form = null)
    {
        $metaData['class'][] = 'form-control';
        $value = isset($metaData['current']) ? $metaData['current'] : '';
        $htmlOptions = $this->htmlOptions($metaData,$form,array('size'=>50));
        return CHtml::emailField($name, $value, $htmlOptions);
    }

    public function renderText($name, array $metaData, $form = null)
    {
        $metaData['class'][] = 'form-control';
        $value = isset($metaData['current']) ? $metaData['current'] : '';
        $htmlOptions = $this->htmlOptions($metaData,$form);
        return CHtml::textArea($name, $value, $htmlOptions);
    }

    public function renderPassword($name, array $metaData, $form = null)
    {
        $metaData['class'][] = 'form-control';
        $value = isset($metaData['current']) ? $metaData['current'] : '';
        $htmlOptions = $this->htmlOptions($metaData,$form,array('autocomplete'=>'off','size'=>50));
        return CHtml::passwordField($name,$value,$htmlOptions);
    }

    public function renderLink($name, array $metaData, $form = null)
    {
        $metaData['class'][] = 'btn btn-link';
        $metaData['text']=isset($metaData['text'])?$metaData['text']:$metaData['label'];
        $htmlOptions = $this->htmlOptions($metaData,$form,array('id' => $name));
        return CHtml::link($metaData['text'], $metaData['link'], $htmlOptions);
    }

    public function renderList($name, array $metaData, $form = null)
    {
        $out = '';
        $id = $name;
        $headers = '';
        $cells = '';
        foreach ($metaData['items'] as $itemName => $itemMetaData)
        {
            $headers .= CHtml::tag('th', array(), $itemMetaData['label']);
            //$itemMetaData['title']=$itemMetaData['label'];
            unset($itemMetaData['label']);
            $itemMetaData['controlOptions']['class']=(isset($itemMetaData['controlOptions']['class']))?$itemMetaData['controlOptions']['class']:'default';
            //$cells .= CHtml::tag('td', array(), $this->renderSetting($itemName . '[]', $itemMetaData, $form, true,false));
            // TODO $itemMetaData['htmlOtions']['id']=$itemName.$key or something like this
            $cells .= $this->renderSetting($itemName . '[]', $itemMetaData, $form, true,'td');
        }
        $headers .= CHtml::tag('th');
        $cells .= CHtml::tag('td', array(), $this->widget('bootstrap.widgets.TbButtonGroup', array(
            'type' => 'link',
            'buttons' => array(
                array('icon' => 'icon-minus', 'htmlOptions' => array('class' => 'remove')),
                array('icon' => 'icon-plus', 'htmlOptions' => array('class' => 'add')),
            )

        ), true));
        $out .= CHtml::openTag('table',array('class'=>'settings activecell'));
        // Create header row.
        $out .= CHtml::openTag('thead');
        $out .= CHtml::openTag('tr');
        $out .= $headers;
        $out .= CHtml::closeTag('tr');
        $out .= CHtml::closeTag('thead');
        // Create cells.
        $out .= CHtml::openTag('tbody');
        $out .= CHtml::openTag('tr');
        $out .= $cells;
        $out .= CHtml::closeTag('tr');
        $out .= CHtml::closeTag('tbody');
        $out .= CHtml::closeTag('table');

        return $out;
    }

    /**
     * Date type
     */
    public function renderDate($name, array $metaData, $form = null)
    {
        $dateformatdetails = getDateFormatData(Yii::app()->session['dateformat']);
        $value = isset($metaData['current']) ? $metaData['current'] : '';
        $html = Yii::app()->getController()->widget('yiiwheels.widgets.datetimepicker.WhDateTimePicker', array(
                'name' => $name,
                'id' => $name,
                'value' => $value,
                'pluginOptions' => array(
                    'format' => $dateformatdetails['jsdate'] . " HH:mm",
                    'allowInputToggle' =>true,
                    'showClear' => true,
                    'tooltips' => array(
                        'clear'=> gT('Clear selection'),
                        'prevMonth'=> gT('Previous month'),
                        'nextMonth'=> gT('Next month'),
                        'selectYear'=> gT('Select year'),
                        'prevYear'=> gT('Previous year'),
                        'nextYear'=> gT('Next year'),
                        'selectDecade'=> gT('Select decade'),
                        'prevDecade'=> gT('Previous decade'),
                        'nextDecade'=> gT('Next decade'),
                        'prevCentury'=> gT('Previous century'),
                        'nextCentury'=> gT('Next century'),
                        'selectTime'=> gT('Select time')
                    ),
                    'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                )
            ), true
        );
        return $html;
    }

    /* Return htmlOptions for an input od seting
     *
     * @param array metaData : completMetaData of setting
     * @param string form form to be used
     * @param array aDefault default htmlOptions to use
     * @param array aForced forced htmlOptions to use
     */
    public function htmlOptions(array $metaData, $form = null,array $aDefault = array(),array $aForced = array())
    {

        if(isset($metaData['htmlOptions']) && is_array($metaData['htmlOptions']))
        {
            $htmlOptions=$metaData['htmlOptions'];
        }
        else
        {
            $htmlOptions=array();
        }
        // If metadata have a class, replace (?) to actual class
        if(!empty($metaData['class']) && is_array($metaData['class'])){
            $htmlOptions['class']=implode(' ',$metaData['class']);
        }
        // If metadata have style, replace actual style
        if(!empty($metaData['style']) && is_string($metaData['style'])){
            $htmlOptions['style']=$metaData['style'];
        }
        if (isset($metaData['readOnly'])){
            $metaData['htmlOptions']["readonly"]= $metaData['readOnly'];
        }

        return array_merge(array('form'=>$form),$aDefault,$htmlOptions,$aForced);
    }
}
