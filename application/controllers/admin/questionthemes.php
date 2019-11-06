<?php

class questionthemes extends Survey_Common_Action
{
    /**
     * @param string  $id
     *
     * @param boolean $visible
     */
    public function toggleVisibility($id)
    {
        if (!Permission::model()->hasGlobalPermission('templates', 'update')) {
            return;
        }
        $aQuestionThemes = QuestionTheme::model()->findAllByAttributes([], 'id = :id', ['id' => $id]);

        /** @var QuestionTheme $oQuestionTheme */
        foreach ($aQuestionThemes as $oQuestionTheme) {
            if ($oQuestionTheme->visible == 'Y'){
                $oQuestionTheme->setAttribute('visible', 'N');
            } else {
                $oQuestionTheme->setAttribute('visible', 'Y');
            }
            $oQuestionTheme->save();
        }
    }
}
