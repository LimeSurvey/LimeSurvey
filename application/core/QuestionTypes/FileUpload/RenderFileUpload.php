<?php

/**
 * RenderClass for File Upload Question
 *  * The ia Array contains the following
 *  0 => string qid
 *  1 => string sgqa
 *  2 => string questioncode
 *  3 => string question
 *  4 => string type
 *  5 => string gid
 *  6 => string mandatory,
 *  7 => string conditionsexist,
 *  8 => string usedinconditions
 *  0 => string used in group.php for question count
 * 10 => string new group id for question in randomization group (GroupbyGroup Mode)
 *
 */
class RenderFileUpload extends QuestionBaseRenderer
{
    protected $aPackages = ['question-file-upload'];

    /**
     * Returns the twig view used to render the answer part of the question.
     *
     * @return string
     */
    public function getMainView()
    {
        return '/survey/questions/answer/file_upload/answer';
    }

    /**
     * File upload questions have no rows.
     *
     * @return void
     */
    public function getRows()
    {
        return;
    }

    /**
     * Renders the file upload question (answer area and upload modal trigger).
     *
     * @param string $sCoreClasses Unused, kept for signature compatibility
     * @return array{0: string, 1: string[]} Rendered answer HTML and the list of input names
     */
    public function render($sCoreClasses = '')
    {
        $iSurveyId = $this->oQuestion->sid;
        $sAction = Yii::app()->request->getParam('action');
        // Preview is launched from question or group level, or the survey is not active
        $bPreview = $sAction == "previewgroup" || $sAction == "previewquestion" || $this->oQuestion->survey->active != "Y";
        $_SESSION['responses_' . $iSurveyId]['fieldname'] = $this->sSGQA;
        $_SESSION['responses_' . $iSurveyId]['preview'] = (int) $bPreview;

        $uploadurl = Yii::app()->getController()->createUrl(
            'uploader/index',
            [
                "sid" => $iSurveyId,
                "fieldname" => $this->sSGQA,
                "qid" => $this->oQuestion->qid,
                "preview" => (int) $bPreview,
                "show_title" => $this->getQuestionAttribute('show_title'),
                "show_comment" => $this->getQuestionAttribute('show_comment'),
                "minfiles" => $this->getQuestionAttribute('min_num_of_files'), // TODO: Regression here? Should use LEMval(minfiles)
                "maxfiles" => $this->getQuestionAttribute('max_num_of_files'), // Same here.
            ]
        );

        $filecountvalue = $this->getFromSurveySession($this->sSGQA . "_Cfilecount", '0');
        if (!is_numeric($filecountvalue)) {
            $filecountvalue = '0';
        }

        $answer = Yii::app()->twigRenderer->renderQuestion($this->getMainView(), array(
            'fileid' => $this->sSGQA,
            'value' => $this->getFromSurveySession($this->sSGQA),
            'filecountvalue' => $filecountvalue,
            'coreClass' => "ls-answers upload-item",
            'maxFiles' => $this->getQuestionAttribute('max_num_of_files'),
            'basename' => $this->sSGQA,
            'uploadurl' => $uploadurl,
            'scriptloc' => Yii::app()->getController()->createUrl('/uploader/index/mode/upload/'),
            'showTitle' => $this->getQuestionAttribute('show_title'),
            'showComment' => $this->getQuestionAttribute('show_comment'),
            'uploadButtonLabel' => ngT("Upload file|Upload files", $this->getQuestionAttribute('max_num_of_files'))
        ));

        $this->registerAssets();
        $inputnames = [$this->sSGQA, $this->sSGQA . "_Cfilecount"];
        return array($answer, $inputnames);
    }
}
