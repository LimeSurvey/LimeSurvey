<?php
	/**
	 * @author Sam Mousa <sam@befound.nl>
	 */

	class JsonEditor extends CInputWidget
	{
        protected $baseUrl;
        public $editorOptions = array(
            'mode' => 'form',
            'modes' => array('form', 'code', 'tree', 'text')
        );

        public $htmlOptions = array(
            'class' => 'jsoneditor-wrapper'
        );

        protected $libraryDir = 'jsoneditor-2.3.6';
		
		public function init()
		{
			$this->baseUrl = Yii::app()->assetManager->publish(__DIR__ . "/" . $this->libraryDir) . "/";
            $this->registerClientScript();
            
		}

		protected function registerCssFile($fileName)
		{
			App()->clientScript->registerCssFile($this->baseUrl . $fileName);
		}
		
		protected function registerScriptFile($fileName)
		{
			App()->clientScript->registerScriptFile($this->baseUrl . $fileName);
		}
		
		protected function registerClientScript()
		{
			$this->registerCssFile('jsoneditor-min.css');
			$this->registerScriptFile('jsoneditor-min.js');
			$this->registerScriptFile('lib/ace/ace.js');
			App()->clientScript->registerScriptFile(App()->assetManager->publish(__DIR__ . '/widget.js'));
		}


		public function run()
		{
			$htmlOptions = $this->htmlOptions;
            list($name, $id) = $this->resolveNameID();
			$value = $this->value;
			// not a json, encoding
			if (!isJson($this->value)) {
				$value = json_encode($this->value);
			}

			echo CHtml::tag('div', $htmlOptions, CHtml::textArea($name, $value, array(
				'id' => $id,
                'encode' => false,
			)));
			$config = json_encode($this->editorOptions);
            App()->getClientScript()->registerScript("initJsonEditor" . $id, "$('#{$id}').jsonEditor($config);", CClientScript::POS_READY);
		}
	}
?>
