<?php
class TextDisplaySwitch extends CWidget {
    
    public $widgetsJsName = "";
    public $textToDisplay = "";
    public $abbreviationSize = 120;
    public $abbreviationSign = '...';

    public function run() {
        //clean up the widgets name to be js friendly
        $this->widgetsJsName = preg_replace('/[^a-zA-Z0-9_-]/','',$this->widgetsJsName);
        
        $this->registerScripts();
        $outView = (strlen($this->textToDisplay) > $this->abbreviationSize) ? 'default' : 'short';
        $this->render($outView);

    }

    private function registerScripts(){
        App()->getClientScript()->registerScriptFile(App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/textDisplaySwitch.js'), CClientScript::POS_END);
    }
}
