<?php 

    /**
     * 
     */
    class LimeScript extends CWidget
    {
        public function run()
        {
            App()->getClientScript()->registerScriptFile(App()->getAssetManager()->publish(Yii::getPathOfAlias('ext.LimeScript.assets'). '/script.js'));
            
            $data = array();
            $data['baseUrl']                    = Yii::app()->getBaseUrl(true);
            $data['showScriptName']             = Yii::app()->urlManager->showScriptName;
            $data['urlFormat']                  = Yii::app()->urlManager->urlFormat;
            $data['adminImageUrl']              = Yii::app()->getConfig('adminimageurl');
            $data['replacementFields']['path']  = App()->createUrl("admin/limereplacementfields/sa/index/");
            $json = json_encode($data, JSON_FORCE_OBJECT);
            $script = "LS.data = $json;";
            App()->getClientScript()->registerScript('LimeScript', $script, CClientScript::POS_HEAD);
        }
    }

?>