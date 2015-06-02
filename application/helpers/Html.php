<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 6/2/15
 * Time: 11:28 AM
 */

class Html extends TbHtml {

    public static function buttonRow(array $buttons) {
        $buttonHtml =[];
        foreach($buttons as $label => $button) {
            $buttonHtml[] = self::btn(TbArray::popValue('type', $button, self::BUTTON_TYPE_SUBMIT), $label, $button);

        }
        return implode(' ', $buttonHtml);
    }

}