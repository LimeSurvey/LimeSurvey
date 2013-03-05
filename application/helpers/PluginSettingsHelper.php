<?php

    class PluginSettingsHelper 
    {
        
        public function renderSetting($name, array $metaData, $form = null, $return = false)
        {
            $defaults = array(
                'class' => array(),
                'type' => 'string'
            );
            $metaData = array_merge($defaults, $metaData);
            if (is_string($metaData['class']))
            {
                $metaData['class'] = array($metaData['class']);
            }
            if (isset($metaData['type']))
            {
                $function = "render{$metaData['type']}";
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
        
        public function renderFloat($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            if (isset($metaData['label']))
            {
                $out .= CHtml::label($metaData['label'], $id);
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
            // Register CKEditor library for inclusion.
            App()->getClientScript()->registerCoreScript('ckeditor');
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            $metaData['class'][] = 'htmleditor';
            $readOnly = isset($metaData['readOnly']) ? $metaData['readOnly'] : false;
            if (isset($metaData['label']))
            {
                $out .= CHtml::label($metaData['label'], $id);
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
                $out .= CHtml::label($metaData['label'], $id);
            }
            $out .= CHtml::textField($id, $value, array(
                'id' => $id, 
                'form' => $form,
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
                $out .= CHtml::label($metaData['label'], $id);
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
                $out .= CHtml::label($metaData['label'], $id);
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
                $out .= CHtml::label($metaData['label'], $id);
            }
            $out .= CHtml::textField($id, $value, array('id' => $id, 'form' => $form, 'class' => implode(' ', $metaData['class']), 'readonly' => $readOnly));
            
            return $out;
        }
        
        public function renderPassword($name, array $metaData, $form = null)
        {
            $out = '';
            $id = $name;
            $value = isset($metaData['current']) ? $metaData['current'] : '';
            if (isset($metaData['label']))
            {
                $out .= CHtml::label($metaData['label'], $id);
            }
            $out .= CHtml::passwordField($id, $value, array('id' => $id, 'form' => $form));
            
            return $out;
        }
        
                
    }
    
    

?>
