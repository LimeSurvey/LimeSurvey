<?php
class FlashMessage extends CWidget {
    public function run() {
        $aMessage=array();
        App()->getClientScript()->registerPackage('jquery-notify');
        $assetUrl = Yii::app()->assetManager->publish(dirname(__FILE__) . '/assets');
        Yii::app()->clientScript->registerCssFile($assetUrl . '/css/notify.css');
        Yii::app()->clientScript->registerScriptFile($assetUrl . '/js/notify.js',CClientScript::POS_END);
        if (!empty(App()->session['aFlashMessage']) && count(Yii::app()->session['aFlashMessage']))
        {
            $aMessage = App()->session['aFlashMessage'];
            unset(App()->session['aFlashMessage']);
        }

        if (!empty(App()->session['flashmessage']) && Yii::app()->session['flashmessage'] != '')
        {
            $message = App()->session['flashmessage'];
            unset(App()->session['flashmessage']);
            if($message)
                $aMessage[]=array('message'=>$message);
        }
         foreach(Yii::app()->user->getFlashes() as $key => $message) 
         {
            if(is_string($message))
                $aMessage[]=array('message'=>$message,'type'=>'default');
            elseif(is_array($message) && is_string($message['message']) && isset($message['type']))
                $aMessage[]=array('message'=>$message['message'],'type'=>$message['type']);
            elseif(is_array($message) && is_string($message['message']) )
                $aMessage[]=array('message'=>$message['message']);
        }
        Yii::app()->clientScript->registerScript('notify-messages',"LS.messages=".json_encode($aMessage).';',CClientScript::POS_HEAD);
        $this->render('message', array('aMessage'=>$aMessage));
    }
}
?>
