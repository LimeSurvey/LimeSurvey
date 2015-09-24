<?php


namespace ls\components;

use GroupRenderer;
use QuestionRenderer;
use ls\components\SurveySession;

class SurveyRenderer extends Renderer
{

    public function render()
    {
        $body = $this->renderBody();

        return $this->renderLayout($body);
    }

    public function renderBody()
    {
        switch ($this->session->format) {
            case SurveySession::FORMAT_GROUP:
                // This will render the current group.
                $result = (new GroupRenderer($this->session))->render();
                break;
            case SurveySession::FORMAT_QUESTION:
                // This will render the current question.
                $result = (new QuestionRenderer($this->session))->render();
                break;
            default:
                throw new \Exception("Format {$this->session->format} not supported");
        }

        return $result;
    }

    protected function renderLayout($content)
    {
        return $content;
    }
}
