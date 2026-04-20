<?php

/**
 * Controller for rendering AlertWidget via Ajax call
 */
class AjaxAlertController extends LSBaseController
{
    /**
     * Spits out html from AlertWidget
     *
     * @return string
     */
    public function actionGetAlertWidget()
    {
        $widgetOptions = $this->translateOptionsForWidget();

        return json_encode(App()->getController()->widget('ext.AlertWidget.AlertWidget', $widgetOptions));
    }

    /**
     * Translates given json options to php array, but only the known options for the widget
     * @return array
     */
    private function translateOptionsForWidget()
    {
        $request = Yii::app()->request;
        $customOptions = $request->getPost('customOptions', []);
        $translatedOptions = [];
        if (empty($customOptions['useHtml'])) {
            $translatedOptions['text'] = CHtml::encode($request->getPost('message', 'message'));
        } else {
            $translatedOptions['text'] = viewHelper::purified($request->getPost('message', 'message'));
        }
        $translatedOptions['type'] = sanitize_alphanumeric($request->getPost('alertType', 'success'));
        $knownOptions = ['tag', 'isFilled', 'showIcon', 'showCloseButton', 'timeout'];
        foreach ($knownOptions as $knownOption) {
            if (array_key_exists($knownOption, $customOptions)) {
                if ($knownOption == 'tag') {
                    $translatedOptions[$knownOption] = sanitize_alphanumeric($customOptions[$knownOption]);
                } elseif ($knownOption == 'timeout') {
                    $translatedOptions[$knownOption] = intval($customOptions[$knownOption]);
                } else {
                    $translatedOptions[$knownOption] = $customOptions[$knownOption] !== 'false';
                }
            }
        }
        if (array_key_exists('htmlOptions', $customOptions)) {
            // htmlOptions is encoded by view
            $translatedOptions['htmlOptions'] = json_decode_ls($customOptions['htmlOptions']);
        }

        return $translatedOptions;
    }
}
