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
            $data['csrfTokenName']              = Yii::app()->request->csrfTokenName;
            $data['csrfToken']                  = Yii::app()->request->csrfToken;
            $data['csrfTokenData']              = array(Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken);
            $data['language']                   = Yii::app()->language;
            $data['replacementFields']['path']  = App()->createUrl("limereplacementfields/index");
            $json = json_encode($data, JSON_FORCE_OBJECT);
            $script = "LS.data = $json;\n
                    // @see https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html#jquery
                    function csrfSafeMethod(method) {
                        // these HTTP methods do not require CSRF protection
                        return (/^(GET|HEAD|OPTIONS)$/.test(method));
                    }
                    $.ajaxSetup({
                        beforeSend: function(jqXHR, settings) {
                            if(!csrfSafeMethod(settings.type)) {
                                // NB: This sometimes includes the CSRF token twice, when already added to data.
                                settings.data +=  '&" . Yii::app()->request->csrfTokenName . "=" . Yii::app()->request->csrfToken ."';
                            }
                        }
                    });";
            App()->getClientScript()->registerScript('LimeScript', $script, CClientScript::POS_HEAD);
        }
    }

?>
