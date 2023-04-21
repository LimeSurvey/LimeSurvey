<?php
class TextDisplaySwitch extends CWidget {
    
    public $widgetsJsName = "";
    public $textToDisplay = "";
    public $abbreviationSize = 120;
    public $abbreviationSign = '...';
    public $returnHtml = false;

    public function run() {
        //clean up the widgets name to be js friendly
        $this->widgetsJsName = preg_replace('/[^a-zA-Z0-9_-]/','',(string) $this->widgetsJsName);
        
        $this->registerScripts();
        $outView = (strlen((string) $this->textToDisplay) > $this->abbreviationSize) ? 'default' : 'short';
        
        if( $this->returnHtml ){
            return $this->render($outView, null, true);
        }

        $this->render($outView);

    }

    private function registerScripts(){
        App()->getClientScript()->registerScriptFile(App()->getAssetManager()->publish(dirname(__FILE__) . '/assets/textDisplaySwitch.js'), CClientScript::POS_END);
    }
}
