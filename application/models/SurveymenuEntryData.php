<?php

class SurveymenuEntryData extends CFormModel
{

    public $rawData = null;
    public $render = null;
    public $link = "admin/survey/sa/rendersidemenulink";
    public $linkData  = array();
    public $linkExternal = false;
    public $surveyid  = 0;
    public $menuEntry = null;
    public $placeholder = false;
    public $pjaxed = true;
    public $isActive  = null;

    /**
     * @param integer|null $surveyid
     */
    public function apply($menuEntry, $surveyid)
    {
        $this->surveyid = $surveyid;
        $this->menuEntry = $menuEntry;

        $oData = json_decode(stripcslashes($this->menuEntry->data));
        $jsonError = json_last_error();
        if ($jsonError) {
            $this->rawData = [];
        } else {
            $this->rawData = $oData;
            $this->_parseDataAttribute();
        }
        $this->_parseLink();
    }

    public function createOptionJson($addSurveyID = false, $addQuestionGroupId = false, $addQuestionId = false)
    {

        $dataArray = array();
        if ($addSurveyID) {
                    $dataArray['surveyid'] = ['survey', 'sid'];
        }
        if ($addQuestionGroupId) {
                    $dataArray['gid'] = ['questiongroup', 'gid'];
        }
        if ($addQuestionId) {
                    $dataArray['qid'] = ['question', 'qid'];
        }

        $dataArray = array_merge($dataArray, $this->linkData);

        $baseArray = array(
            'link' => array(
                'external' => $this->linkExternal,
                'pjaxed' => $this->pjaxed,
                'data' => $dataArray
            )
        );

        if ($this->isActive === true || $this->isActive === false) {
                    $baseArray['isActive'] = $this->isActive;
        }


        return json_encode(array('render' => $baseArray));
    }

    public function linkCreator()
    {
        if( $this->linkExternal ) {
            return  Yii::app()->getController()->createAbsoluteUrl($this->link, $this->linkData);
        }
        return  Yii::app()->getController()->createUrl($this->link, $this->linkData);
        
    }

    private function _parseDataAttribute()
    {

        $this->isActive = $this->_recursiveIssetWithDefault($this->rawData, array('render', 'isActive'), 0, $this->isActive);
        $this->linkExternal = $this->_recursiveIssetWithDefault($this->rawData, array('render', 'link', 'external'), 0, $this->linkExternal);
        $this->pjaxed = $this->_recursiveIssetWithDefault($this->rawData, array('render', 'link', 'pjaxed'), 0, $this->pjaxed);
        $alinkData = $this->_recursiveIssetWithDefault($this->rawData, array('render', 'link', 'data'), 0, $this->linkData);

        foreach ($alinkData as $key => $value) {
            if (is_array($value)) {
                $value = $this->_getValueForLinkData($value);
            }
            $this->linkData[$key] = $value;
        }
    }


    private function _parseLink()
    {

        if (empty($this->menuEntry->menu_link)) {
            $this->linkData['subaction'] = $this->menuEntry->name;
            $this->linkData['surveyid'] = $this->surveyid;
        } else {
            $this->link = $this->menuEntry->menu_link;
        }

    }

    /**
     * @param string[] $checkArray
     */
    private function _recursiveIssetWithDefault($variable, $checkArray, $i = 0, $fallback = null)
    {
        $default = null;
        if (is_array($variable) && array_key_exists($checkArray[$i], $variable)) {
                    $default = $variable[$checkArray[$i]];
        } else if (is_object($variable) && property_exists($variable, $checkArray[$i])) {
                    $default = $variable->{$checkArray[$i]};
        }
        if (!isset($default)) {
                    return $fallback;
        } else if (count($checkArray) > $i + 1) {
                    return $this->_recursiveIssetWithDefault($default, $checkArray, $i + 1, $fallback);
        } else {
                    return $default;
        }

    }

    private function _getValueForLinkData($getDataPair)
    {

        $oSurvey = Survey::model()->findByPk($this->surveyid);
        list($type, $attribute) = $getDataPair;
        $oTypeObject = null;
        switch ($type) {
            case 'survey':
                $oTypeObject = &$oSurvey;
                break;
            case 'template':
                $oTypeObject = Template::model()->findByPk($oSurvey->template);
                break;
            case 'questiongroup':
                if (App()->getRequest()->getParam('gid')) {
                    $oTypeObject = QuestionGroup::model()->findByPk(array('gid'=>App()->getRequest()->getParam('gid'),'language'=>App()->getLanguage()));
                }
                break;
            case 'question':
                if (App()->getRequest()->getParam('qid')) {
                    $oTypeObject = QuestionGroup::model()->findByPk(array('gid'=>App()->getRequest()->getParam('qid'),'language'=>App()->getLanguage()));
                }
                break;
            break;
        }

        $result = $oTypeObject != null ? $oTypeObject->{$attribute} : null;
        return $result;
    }

}
