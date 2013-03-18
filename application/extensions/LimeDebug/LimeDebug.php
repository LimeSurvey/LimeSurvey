<?php 

    /**
     * 
     */
    class LimeDebug extends CWidget
    {
        public function run()
        {
            if (YII_DEBUG && in_array(App()->request->getUserHostAddress(), array("127.0.0.1","::1")))
            {
                //App()->getClientScript()->registerScriptFile(App()->getAssetManager()->publish(Yii::getPathOfAlias('ext.LimeScript.assets'). '/script.js'));
                $data = array(
                    'session' => $_SESSION,
                    'server' => $_SERVER
                );
                $json = json_encode($data, JSON_FORCE_OBJECT);
                $script = "LSdebug = $json";
                App()->getClientScript()->registerScript('LimeScript', $script, CClientScript::POS_HEAD);
            }
        }
    }

?>