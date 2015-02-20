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
        public $method = 'post';
        public $prefix;
        public $settings = array();
        /**
         * Set to true to render elements in a lit (ul/li)
         * @var boolean
         */
        public $inlist=false;
        public $title;


        public function beginForm()
        {
            if ($this->form)
            {
                echo CHtml::beginForm($this->action, $this->method, $this->formHtmlOptions);
            }
            else
            {
                echo CHtml::openTag('div', array('class' => $this->formHtmlOptions['class'], 'id' => $this->getId()));
            }
            if (isset($this->title))
            {
                echo CHtml::tag('legend', array(), $this->title);
            }
        }

        public function endForm()
        {
            if ($this->form)
            {
                echo CHtml::endForm();
            }
            else
            {
                echo CHtml::closeTag('div');
            }
        }
        public function init() {
            parent::init();

            // Register assets.
            Yii::app()->getClientScript()->registerPackage('jquery');
            Yii::app()->getClientScript()->registerCssFile(App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/settingswidget.css'));
            Yii::app()->getClientScript()->registerScriptFile(App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/settingswidget.js'));

            // Add default form class.
            $this->formHtmlOptions['class'] = isset($this->formHtmlOptions['class']) ? $this->formHtmlOptions['class'] . "form-horizontal settingswidget" : 'form-horizontal settingswidget';

            // Start form
            $this->beginForm();
        }

        protected function renderButton($label, $metaData)
        {
            $htmlOptions = $this->htmlOptions($metaData, null);
            
            switch($metaData['type']) {
                case 'link':
                    $result = TbHtml::linkButton($label, array_merge($htmlOptions, ['url' => $metaData['href']]));
                    break;
                case 'submit':
                    $result = TbHtml::submitButton($label, $htmlOptions);
                    break;
                default:
                    $result = TbHtml::htmlButton($label, $htmlOptions);
            }
            return $result;
        }

        protected function renderButtons()
        {
            
            if(!empty($this->buttons)) {
                
//                echo CHtml::openTag('div', ['class' => 'btn-group pull-right']);
                $buttons = [];
                foreach ($this->buttons as $label => $htmlOptions) {
                    if (is_numeric($label)) {
                        $label = $htmlOptions;
                        $htmlOptions = [];
                    }
                    $buttons[] = $this->renderButton($label, $htmlOptions);
                }
                echo TbHtml::formActions($buttons);
//                echo CHtml::closeTag('div');
            }
        }

        protected function renderSetting($name, $metaData, $form = null, $return = false,$wrapper='div')
        {
            // No type : invalid setting
            if (is_string($metaData)) {
                $metaData = [
                    'label' => $name,
                    'type' => 'info',
                    'content' => $metaData
                ];
            }
            if(!isset($metaData['type']))
                return "";
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

            // The input
            $input = $this->$function($name, $metaData, $form);
            // The help
            $content = $this->renderHelp($name, $metaData);
            $content = TbHtml::customControlGroup($input, $name, [
                'help' => isset($metaData['errors']) ? implode(', ', $metaData['errors']) : '',
                'label' => isset($metaData['label']) ? $metaData['label'] : $name,
                'formLayout' => TbHtml::FORM_LAYOUT_HORIZONTAL,
                'controlWidthClass' => 'col-sm-12 col-md-7',
                'labelWidthClass' => 'col-sm-12 col-md-5',
                'groupOptions' => [
                    'class' => "setting setting-{$metaData['type']} "
                    . ((isset($metaData['errors']) && !empty($metaData['errors'])) ? TbHtml::$errorCss : ''),
                    'data-name' => $name
                            
                ]
                 
            ]);

            $result = $metaData['type'] != 'hidden' ? $content : $input;

            if($return)
                return $result;
            else
                echo $result;
        }

        protected function renderSettings()
        {
            if($this->inlist)
            {
                echo CHtml::openTag('ul');
            }
            foreach($this->settings as $name => $metaData)
            {
                if($this->inlist)
                {
                    $this->renderSetting($name, $metaData, null, false,'li');
                }
                else
                {
                    $this->renderSetting($name, $metaData);
                }
            }
            if($this->inlist)
            {
                echo CHtml::closeTag('ul');
            }
        }



        public function run() {
            parent::run();
            
            // Render settings
            $this->renderSettings();
            // Render buttons
            $this->renderButtons();
            // End form
            $this->endForm();
        }


        /**
        * fix metaData for a setting : set default etc.
        *
        */
        public function fixMetaData($name,$metaData){

            $defaults = array(
                'class' => [],
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
            $htmlOptions = $this->htmlOptions($metaData,$form,array('container'=> false, 'separator' => ''));
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            return CHtml::radioButtonList($name, $value, array(
                0 => 'False',
                1 => 'True'
            ), $htmlOptions);
        }

        public function renderCheckbox($name, array $metaData, $form = null)
        {

            $htmlOptions = $this->htmlOptions($metaData,$form);
            $value = isset($metaData['current']) ? (bool) $metaData['current'] : false;
            return CHtml::checkBox($name, $value,$htmlOptions);
        }

        public function renderFloat($name, array $metaData, $form = null)
        {
            $htmlOptions = $this->htmlOptions($metaData,$form,array('step'=>'any'));// step can be replaced by plugin developer
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            return CHtml::numberField($name, $value, $htmlOptions);
        }

        public function renderHtml($name, array $metaData, $form = null)
        {
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $metaData['class'][] = 'htmleditor';
            $htmlOptions = $this->htmlOptions($metaData,$form);
            $editorOptions = array_merge(array(
                'html' => true,
            ), isset($metaData['editorOptions']) ? $metaData['editorOptions'] : array());
            return Chtml::tag('div', $htmlOptions,
                $this->widget('bootstrap.widgets.TbHtml5Editor', array(
                    'name' => $name,
                    'value' => $value,
                    'width' => '100%',
                    'editorOptions' =>  $editorOptions,
                ), true)
            );
        }
        
        public function renderInfo($name, array $metaData, $form = null)
        {
            $value = isset($metaData['content']) ? $metaData['content'] : '';
            if (is_array($value)) { throw new CException('wrong type' . $name); }
            $htmlOptions = $this->htmlOptions($metaData);
            return Chtml::tag('div',$htmlOptions,$value);
        }

        public function renderInt($name, array $metaData, $form = null)
        {
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            if (is_array($value)) { throw new CException('wrong type' . $name); }
            $htmlOptions = $this->htmlOptions($metaData,$form,array('step'=> 1,'pattern' => '\d+'));
            return TbHtml::numberField($name, $value, $htmlOptions);
        }

        public function renderJson($name, array $metaData, $form = null)
        {
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
            $htmlOptions = $this->htmlOptions($metaData,$form);
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            return CHtml::textArea($name, $value, $htmlOptions);
        }

        public function renderSelect($name, array $metaData, $form = null)
        {
            $value = isset($metaData['current']) ? $metaData['current'] : (isset($metaData['default']) ? $metaData['default'] : null);
            $htmlOptions = $this->htmlOptions($metaData,$form);
            $select2Options=array_merge(
                array(
                    'minimumResultsForSearch' => 50,
                    'dropdownAutoWidth'=> true,
//                    'width' => 'resolve',
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
            $result = App()->getController()->widget('WhSelect2', $properties, true);
            return $result;
        }

        public function renderString($name, array $metaData, $form = null)
        {
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $htmlOptions = $this->htmlOptions($metaData, $form);
            return TbHtml::textField($name, $value, $htmlOptions);
        }
        public function renderHidden($name, array $metaData, $form = null)
        {
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $htmlOptions = $this->htmlOptions($metaData,$form);
            return CHtml::hiddenField($name, $value, $htmlOptions);
        }

        public function renderEmail($name, array $metaData, $form = null)
        {
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $htmlOptions = $this->htmlOptions($metaData,$form);
            return TbHtml::emailField($name, $value, $htmlOptions);
        }

        public function renderText($name, array $metaData, $form = null)
        {
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $htmlOptions = $this->htmlOptions($metaData,$form);
            return TbHtml::textArea($name, $value, $htmlOptions);
        }
        
        

        public function renderPassword($name, array $metaData, $form = null)
        {
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $htmlOptions = $this->htmlOptions($metaData,$form,array('autocomplete'=>'off'));
            return TbHtml::passwordField($name,$value,$htmlOptions);
        }

        public function renderLink($name, array $metaData, $form = null)
        {
            $metaData['class'][] = 'btn btn-link';
            $metaData['text']=isset($metaData['text'])?$metaData['text']:$metaData['label'];
            $htmlOptions = $this->htmlOptions($metaData,$form,array('id' => $name));
            return TbHtml::link($metaData['text'], $metaData['link'], $htmlOptions);
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

        /* Return htmlOptions for an input or seting
        *
        * @param array metaData : completMetaData of setting
        * @param string form form to be used
        * @param array aDefault default htmlOptions to use
        * @param array aForced forced htmlOptions to use
        */
        public function htmlOptions(array $metaData, $form = null,array $aDefault = array(),array $aForced = array())
        {

            if(isset($metaData['htmlOptions']) && is_array($metaData['htmlOptions'])) {
                $htmlOptions = $metaData['htmlOptions'];
            } else {
                $htmlOptions = [];
            }
            // If metadata have a class, replace actual class
            if(!empty($metaData['class']) && is_array($metaData['class']))
                $htmlOptions['class']=implode(' ',$metaData['class']);
            // If metadata have style, replace actual style
            if(!empty($metaData['style']) && is_string($metaData['style']))
                $htmlOptions['style']=$metaData['style'];
            if (isset($metaData['readOnly']))
                $htmlOptions["readonly"]= $metaData['readOnly'];
            if (isset($metaData['color']))
                $htmlOptions["color"]= $metaData['color'];
            if (isset($metaData['name']))
                $htmlOptions["name"]= $metaData['name'];
            return array_merge(array('form'=>$form),$aDefault,$htmlOptions,$aForced);
        }
    }

?>
