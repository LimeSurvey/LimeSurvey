<?php
class FlashMessage extends CWidget {
    public function run() {
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('third_party') . 'jqueryui/js/jquery-ui-1.10.0.custom.js');
        App()->getClientScript()->registerScriptFile(Yii::app()->getConfig('generalscripts') . 'jquery/jquery.notify.js');
        if (!empty(App()->session['flashmessage']) && Yii::app()->session['flashmessage'] != '')
        {
            $message = App()->session['flashmessage'];
            $key = 'session';
            unset(App()->session['flashmessage']);
            $this->render('message', compact('key', 'message'));
        }
        foreach(Yii::app()->user->getFlashes() as $key => $message) 
        {
            $this->render('message', compact('key', 'message'));
        }
    }
}
?>