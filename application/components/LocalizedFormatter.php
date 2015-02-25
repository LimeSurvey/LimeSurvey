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
    
}