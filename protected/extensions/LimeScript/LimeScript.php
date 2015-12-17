<?php 

    /**
     * 
     */
    class LimeScript extends CWidget
    {
        public function run()
        {
            $cs = App()->clientScript;
            $cs->registerScriptFile(App()->getAssetManager()->publish(
                __DIR__ . '/assets/script.js')
            );
            
            $data = array();
            $data['baseUrl']                    = Yii::app()->getBaseUrl(true);
            $data['public']                     = App()->getPublicUrl();
            $data['showScriptName']             = Yii::app()->urlManager->showScriptName;
            $data['urlFormat']                  = Yii::app()->urlManager->urlFormat;
            $data['adminImageUrl']              = Yii::app()->getConfig('adminimageurl');
            $data['csrfToken']                  = Yii::app()->request->csrfToken;
            $data['language']                   = Yii::app()->language;
            $data['replacementFields']['path']  = App()->createUrl("admin/limereplacementfields/sa/index/");
            $json = json_encode($data, JSON_FORCE_OBJECT);
            $script = "LS = new LimeSurvey($json);\n"
                    . "$.ajaxSetup({data: {YII_CSRF_TOKEN: LS.getToken() }});";

            $cs->registerScript('LimeScript', $script, CClientScript::POS_HEAD);
        }
    }


