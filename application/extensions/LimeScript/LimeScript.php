<?php 

    /**
     * 
     */
    class LimeScript extends CWidget
    {
        public function run()
        {
            $data = array();
            $data['baseUrl']                    = Yii::app()->getBaseUrl(true);
            $data['showScriptName']             = Yii::app()->urlManager->showScriptName;
            $data['urlFormat']                  = Yii::app()->urlManager->urlFormat;
            $data['layoutPath']                 = Yii::app()->getLayoutPath();
            $data['adminImageUrl']              = Yii::app()->getConfig('adminimageurl');
            $data['replacementFields']['path']  = App()->createUrl("admin/limereplacementfields/sa/index/");
            $this->render('script', compact('data'));
        }
    }

?>