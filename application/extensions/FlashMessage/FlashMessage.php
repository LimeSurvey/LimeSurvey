<?php
class FlashMessage extends CWidget {
    public function run() {
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