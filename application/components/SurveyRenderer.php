<?php


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
                return (new GroupRenderer($this->session))->render();
                break;
            default:
                throw new \Exception("Format {$this->session->format} not supported");
        }

    }

    protected function renderLayout($content)
    {

    }
}
