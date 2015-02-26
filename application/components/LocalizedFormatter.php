<?php
class LocalizedFormatter extends CLocalizedFormatter {
    
    public function formatSurveyStatus($value) {
        switch ($value) {
            case 'active':
                $icon = TbHtml::ICON_PLAY;
                break;
            case 'inactive': 
                $icon = TbHtml::ICON_STOP;
                break;
            case 'expired':
                $icon = TbHtml::ICON_PAUSE;
                break;
                
        }
        return TbHtml::icon($icon);
    }
    
    public function formatPercentage($factor) {
        return number_format($factor * 100, 1) . '%';
    }
    
    public function formatEmail($email) {
        $encoded = '';
        foreach (str_split($email, 1) as $character) {
            $ord = ord($character);
            $encoded .= '&#';
            $encoded .= rand(0, 1) === 0 ? 'x' . dechex($ord) : $ord;
            $encoded .= ';';
        }
        return $encoded;
    }
    
}