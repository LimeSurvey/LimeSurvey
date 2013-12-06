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
        public $formHtmlOptions = array();
        public $method = 'post';
        public $settings = array();



        public function beginForm()
        {
            echo CHtml::beginForm($this->action, $this->method, $this->formHtmlOptions);
        }

        public function endForm()
        {
            echo CHtml::endForm();
        }
        public function init() {
            parent::init();

            // Register assets.
            Yii::app()->getClientScript()->registerPackage('jquery');
            Yii::app()->getClientScript()->registerCssFile(App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/settingswidget.css'));
            Yii::app()->getClientScript()->registerScriptFile(App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/settingswidget.js'));

            // Add default form class.
            $this->formHtmlOptions['class'] = isset($this->formHtmlOptions['class']) ? $this->formHtmlOptions['class'] . " settingswidget" : 'settingswidget';


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
			if (isset($htmlOptions['type']) && $htmlOptions['type'] == 'link')
			{
				$htmlOptions['class'] = 'limebutton';
				echo CHtml::linkButton($label, $htmlOptions);
			}
			else
			{
				echo CHtml::submitButton($label, $htmlOptions);
			}
        }

        protected function renderButtons()
        {
            foreach ($this->buttons as $label => $htmlOptions)
            {
                $this->renderButton($label, $htmlOptions);
            }
        }

        protected function renderSetting($name, $metaData, $form = null, $return = false)
        {
            $defaults = array(
                'class' => array(),
                'type' => 'string',
                'labelOptions' => array(
                    'class' => 'control-label'
                )
            );
            $metaData = array_merge($defaults, $metaData);

            if (is_string($metaData['class']))
            {
                $metaData['class'] = array($metaData['class']);
            }
            if (isset($metaData['type']))
            {
                $function = "render{$metaData['type']}";

                // Handle localization.
                if (isset($metaData['localized']) && $metaData['localized'] == true)
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

                
                $result = $this->$function($name, $metaData, $form);
                
                if ($return)
                {
                    return $result;
                }
                else
                {
                    echo $result;
                }
            }
        }

        protected function renderSettings()
        {
            //echo '<pre>'; var_dump($this->settings); echo ('</pre>'); return;
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



        
        /***********************************************************************
         * Settings renderers.
         **********************************************************************/



        public function renderBoolean($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            if (isset($metaData['label']))
            {
                $out .= CHtml::label($metaData['label'], $id);
            }
            $out .= CHtml::radioButtonList($id, $value, array(
                0 => 'False',
                1 => 'True'
            ), array('id' => $id, 'form' => $form, 'container'=>'div', 'separator' => ''));


            return $out;
        }
        
        public function renderCheckbox($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? (bool) $metaData['current'] : false;
            if (isset($metaData['label']))
            {
                $out .= CHtml::label($metaData['label'], $id);
            }
            $out .= CHtml::checkBox($id, $value, array('id' => $id, 'form' => $form, 'container'=>'div', 'separator' => ''));
            
            return $out;
        }

        public function renderFloat($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            if (isset($metaData['label']))
            {
                $out .= CHtml::label($metaData['label'], $id, $metaData['labelOptions']);
            }
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
            if (isset($metaData['label']))
            {
                $out .= CHtml::label($metaData['label'], $id, $metaData['labelOptions']);
            }
            $out .= Chtml::tag('div', array('class' => implode(' ', $metaData['class'])), CHtml::textArea($id, $value, array('id' => $id, 'form' => $form, 'readonly' => $readOnly)));
            return $out;
        }

        public function renderInt($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            if (isset($metaData['label']))
            {
                $out .= CHtml::label($metaData['label'], $id, $metaData['labelOptions']);
            }
            $out .= CHtml::textField($id, $value, array(
                'id' => $id,
                'form' => $form,
                'data-type' => 'int',
                'pattern' => '\d+'
            ));

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


            if (isset($metaData['label']))
            {
                $out .= CHtml::label($metaData['label'], $id, $metaData['labelOptions']);
            }
            $value = isset($metaData['current']) ? $metaData['current'] : '';

            $out .= CHtml::textArea($name, $value, array('id' => $id, 'form' => $form, 'class' => implode(' ', $metaData['class'])));

            return $out;
        }

        public function renderSelect($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : (isset($metaData['default']) ? $metaData['default'] : null);
            if (isset($metaData['label']))
            {
                $out .= CHtml::label($metaData['label'], $id, $metaData['labelOptions']);
            }
            $out .= CHtml::dropDownList($name, $value, $metaData['options'], array('form' => $form));

            return $out;
        }

        public function renderString($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $readOnly = isset($metaData['readOnly']) ? $metaData['readOnly'] : false;
            if (isset($metaData['label']))
            {
                $out .= CHtml::label($metaData['label'], $id, $metaData['labelOptions']);
            }
            $out .= CHtml::textField($id, $value, array('id' => $id, 'form' => $form, 'class' => implode(' ', $metaData['class']), 'readonly' => $readOnly));

            return $out;
        }

        public function renderText($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $readOnly = isset($metaData['readOnly']) ? $metaData['readOnly'] : false;
            if (isset($metaData['label']))
            {
                $out .= CHtml::label($metaData['label'], $id);
            }
            $out .= CHtml::textArea($id, $value, array('id' => $id, 'form' => $form, 'readonly' => $readOnly));
            return $out;
        }

        public function renderPassword($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            if (isset($metaData['label']))
            {
                $out .= CHtml::label($metaData['label'], $id, $metaData['labelOptions']);
            }
            $out .= CHtml::passwordField($id, $value, array('id' => $id, 'form' => $form));

            return $out;
        }

        public function renderList($name, array $metaData, $form = null)
        {
            $id = $name;
            if (isset($metaData['label']))
            {
                $label = CHtml::label($metaData['label'], $id, $metaData['labelOptions']);
            }
            else
            {
                $label = '';
            }

            $headers = '';
            $cells = '';
            foreach ($metaData['items'] as $itemName => $itemMetaData)
            {
                $headers .= CHtml::tag('th', array(), $itemMetaData['label']);
                unset($itemMetaData['label']);
                $cells .= CHtml::tag('td', array(), $this->renderSetting($itemName . '[]', $itemMetaData, $form, true));
            }
            $headers .= CHtml::tag('th');
            $cells .= CHtml::tag('td', array(), $this->widget('bootstrap.widgets.TbButtonGroup', array(
                'type' => 'link',
                'buttons' => array(
                    array('icon' => 'icon-minus', 'htmlOptions' => array('class' => 'remove')),
                    array('icon' => 'icon-plus', 'htmlOptions' => array('class' => 'add')),
                )
                
            ), true));
            echo CHtml::openTag('div', array('class' => 'settingslist'));
                echo CHtml::openTag('table');
                    // Create header row.
                    echo CHtml::openTag('thead');
                        echo $headers;
                    echo CHtml::closeTag('thead');

                    // Create cells.
                    echo CHtml::openTag('tbody');
                        echo CHtml::openTag('tr');
                        echo $cells;
                        echo CHtml::closeTag('tr');
                    echo CHtml::closeTag('tbody');
                echo CHtml::closeTag('table');
            echo CHtml::closeTag('div');
        }
    }

?>