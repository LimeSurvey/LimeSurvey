<?php

    Yii::import('zii.widgets.CMenu');
    class BarMenu extends CMenu
    {
		public $iconUrl;
		protected function renderMenuItem($item) {
			if (isset($item['icon']))
			{
				$item['label'] = CHtml::image($this->iconUrl . $item['icon'] . '.png', $item['label']);
			}
			return parent::renderMenuItem($item);
		}
		
        public function run() {
            Yii::app()->getClientScript()->registerCssFile(App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/nav.css'));
            parent::run();
        }

    }




?>
