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
            $this->formHtmlOptions['class'] = isset($this->formHtmlOptions['class']) ? $this->formHtmlOptions['class'] . " settingswidget form-horizontal" : 'settingswidget form-horizontal';


            // Start form
            $this->beginForm();
        }

        protected function renderButton($label, $htmlOptions)
        {
            if (is_string($htmlOptions))
            {
                $label = $htmlOptions;
                $htmlOptions = array();
            }
            $htmlOptions['class'] = isset($htmlOptions['class']) ? $htmlOptions['class'] . " btn" : 'btn';
            if (isset($htmlOptions['type']) && $htmlOptions['type'] == 'link')
            {
                $htmlOptions['class'] = isset($htmlOptions['class']) ? $htmlOptions['class'] . " btn-link button" : 'btn-link button';
                //echo CHtml::link($label,$htmlOptions['href'],$htmlOptions); // This allow cancel without js
                echo CHtml::linkButton($label,$htmlOptions);
            }
            elseif(isset($htmlOptions['type']))
            {
                echo CHtml::htmlButton($label, $htmlOptions);
            }
            else
            {
                echo CHtml::submitButton($label, $htmlOptions);
            }
        }

        protected function renderButtons()
        {
            echo CHtml::openTag('div', array('class' => 'buttons control-group'));
            foreach ($this->buttons as $label => $htmlOptions)
            {
                $htmlOptions['class'] = isset($htmlOptions['class']) ? $htmlOptions['class'] . " inline" : 'inline';
                $this->renderButton($label, $htmlOptions);
            }
            echo CHtml::closeTag('div');
        }

        protected function renderSetting($name, $metaData, $form = null, $return = false,$wrapper='div')
        {
            // No type : invalid setting
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

            $result=CHtml::tag($wrapper,array('class'=>"setting control-group setting-{$metaData['type']}", 'data-name' => $name),$content);

            if($return)
                return $result;
            else
                echo $result;
        }

        protected function renderSettings()
        {
            foreach($this->settings as $name => $metaData)
            {
                $this->renderSetting($name, $metaData);
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
                'class' => array(),
                'type' => 'string',
                'labelOptions' => array(
                    'class' => ''
                ),
                'help'=> null,
                'controlOptions'=> array(
                    'class' => 'default col-sm-7'
                ),
                'localized'=>false,
            );
            $metaData = array_merge($defaults, $metaData);

            // col-sm-X is here for bootsrap 3 when ready
            $metaData['labelOptions']['class'].=" control-label col-sm-5";
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
            else
            {
                $metaData['style'] = null;
            }
            return $metaData;
        }
        /**
        * render label according to type and $metaData['label']
        *
        */
        public function renderLabel($name,$metaData){
            if(!isset($metaData['label']))
                return "";
            if(!in_array($metaData['type'],array('list','boolean')))
                return CHtml::label($metaData['label'], $name, $metaData['labelOptions']);
            else
                return CHtml::tag('div',$metaData['labelOptions'], $metaData['label']);
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
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $out .= CHtml::radioButtonList($id, $value, array(
                0 => 'False',
                1 => 'True'
            ), array('id' => $id, 'form' => $form, 'container'=> false, 'separator' => ''));
            return $out;
        }
        
        public function renderCheckbox($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? (bool) $metaData['current'] : false;
            $out .= CHtml::checkBox($id, $value, array('id' => $id, 'form' => $form));

            return $out;
        }

        public function renderFloat($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $out .= CHtml::textField($id, $value, array(
                'id' => $id,
                'form' => $form,
                'pattern' => '\d+(\.\d+)?'
            ));
            return $out;
        }

        public function renderHtml($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $metaData['class'][] = 'htmleditor';
            $readOnly = isset($metaData['readOnly']) ? $metaData['readOnly'] : false;
            $editorOptions = array_merge(array(
                'html' => true,
            ), isset($metaData['editorOptions']) ? $metaData['editorOptions'] : array());
            $out .= Chtml::tag('div', array('class' => implode(' ', $metaData['class'])),
                $this->widget('bootstrap.widgets.TbHtml5Editor', array(
                    'name' => $id,
                    'value' => $value,
                    'width' => '100%',
                    'editorOptions' =>  $editorOptions,
                ), true)
            );
            return $out;
        }

        public function renderInt($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            if (is_array($value)) { throw new CException('wrong type' . $name); }
            $out .= CHtml::textField($id, $value, array(
                'id' => $id,
                'form' => $form,
                'data-type' => 'int',
                'pattern' => '\d+'
            ));
            return $out;
        }

        public function renderJson($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $readOnly = isset($metaData['readOnly']) ? $metaData['readOnly'] : false;
            $editorOptions = array_merge(array(
                'mode' => 'form',
                'modes' => array('form', 'code', 'tree', 'text')
            ), isset($metaData['editorOptions']) ? $metaData['editorOptions'] : array());
            $out .= $this->widget('ext.yii-jsoneditor.JsonEditor', array(
                'name' => $id,
                'value' => $value,
                'editorOptions' => $editorOptions
            ), true);
            return $out;
        }

        public function renderLogo($name, array $metaData)
        {
            return CHtml::image($metaData['path']);
        }
        public function renderRelevance($name, array $metaData, $form = null)
        {
            $out = '';
            $metaData['class'][] = 'relevance';
            $id = $name;

            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $out .= CHtml::textArea($name, $value, array('id' => $id, 'form' => $form, 'class' => implode(' ', $metaData['class'])));

            return $out;
        }

        public function renderSelect($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : (isset($metaData['default']) ? $metaData['default'] : null);
            $properties = array(
                'data' => $metaData['options'],
                'name' => $name,
                'value' => $value,
                'options' => array(
                    'minimumResultsForSearch' => 1000
                )
            );
            
            // allow to submit the form when this element changes
            if (isset($metaData['submitonchange']) && $metaData['submitonchange']) {
                $properties['events'] = array(
                    'change' => 'js: function(e) {
        this.form.submit();
}'
                );
            }
            $out .= App()->getController()->widget('ext.bootstrap.widgets.TbSelect2', $properties, true);
            return $out;
        }

        public function renderString($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $readOnly = isset($metaData['readOnly']) ? $metaData['readOnly'] : false;
            $out .= CHtml::textField($id, $value, array('id' => $id, 'form' => $form, 'class' => implode(' ', $metaData['class']), 'readonly' => $readOnly));
            return $out;
        }

        public function renderText($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $readOnly = isset($metaData['readOnly']) ? $metaData['readOnly'] : false;
            $out .= CHtml::textArea($id, $value, array('id' => $id, 'form' => $form, 'readonly' => $readOnly, 'style' => $metaData['style']));
            return $out;
        }

        public function renderPassword($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $out .= CHtml::passwordField($id, $value, array('id' => $id,'autocomplete'=>'off', 'form' => $form));
            return $out;
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
    }

?>
