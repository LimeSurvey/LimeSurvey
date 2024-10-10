<?php

class HtmlWriter extends Writer
{
    /**
     * The open filehandle
     */
    protected $handle = null;

    protected $first = true;

    protected $groupMap = array();
    /**
     *
     * @var FormattingOptions
     */
    protected $options;

    /**
     *
     * @var SurveyObj
     */
    protected $survey;
    /**
     * Manages stack of open HTML tags that need closing.
     */
    protected $stack = array();

    public function init(SurveyObj $survey, $sLanguageCode, FormattingOptions $oOptions)
    {
        parent::init($survey, $sLanguageCode, $oOptions);
        $this->survey = $survey;
        if ($oOptions->output == 'display') {
            //header("Content-Disposition: attachment; filename=results-survey".$survey->id.".html");
            header("Content-type: text/html; charset=UTF-8");
            $this->handle = fopen('php://output', 'w');
        } elseif ($oOptions->output == 'file') {
            $this->handle = fopen($this->filename, 'w');
        }
        $this->groupMap = $this->setGroupMap($survey, $oOptions);
    }

    protected function writeHeader()
    {
        $this->out('<!DOCTYPE html>');
        $this->openTag('html');
        $this->openTag('head');
        $this->tag('meta', array('charset' => 'utf-8'));
        $this->tag('style', 'td { border: 1px solid black }');
        $this->closeTag();
        $this->openTag('body');
        // Title of the survey.
        $this->tag('h1', array(
            'data-sid' => $this->survey->info['sid']
        ), gT("Survey name (ID)") . ": {$this->survey->info['surveyls_title']} ({$this->survey->info['sid']})");
    }

    protected function outputRecord($headers, $values, FormattingOptions $oOptions, $fieldNames = [])
    {
        if ($this->first) {
            $this->writeHeader();
            $this->first = false;
        }
        $this->tag('h1', sprintf(gT("Survey response")));
        $this->openTag('div', array(
            'class' => 'response',
            'data-srid' => $values[0]
        ));
        //echo '<pre>'; var_dump($this->groupMap); echo '</pre>';
        foreach ($this->groupMap as $gid => $questions) {
            if ($gid != 0) {
                $this->tag('h2', gT("Group") . ": " . $questions[0]['group_name']);
            }
            $this->openTag('table', array(
                'class' => 'group',
                'data-gid' => $questions[0]['gid']
            ));
            foreach ($questions as $question) {
                if (isset($values[$question['index']]) && isset($headers[$question['index']])) {
                            $this->renderQuestion($question, CHtml::encode($values[$question['index']]), $headers[$question['index']]);
                }
            }
            $this->closeTag();
        }
            $this->closeTag();
    }



    /**
     * @param string $content
     */
    protected function out($content)
    {
        fwrite($this->handle, str_pad('', count($this->stack) * 4) . $content . "\n");
    }

    /**
     * @param string $tag
     */
    protected function openTag($tag, $options = array())
    {
        $this->out(CHtml::openTag($tag, $options));
        $this->stack[] = $tag;
    }

    /**
     * Renders a question and recurses into subquestions.
     * @param Question $question
     * @param string $value
     */
    protected function renderQuestion($question, $value, $header)
    {
        if (isset($value) && strlen($value) > 0) {
            $this->openTag('tr', array(
                'data-qid'  => $question['qid'],
                'class' => 'question'
            ));

            $this->tag('td', $header);
            $this->tag('td', $value);
            $this->closeTag();
        }
    }

    /**
     * @param string $tag
     * @param string $content
     */
    protected function tag($tag, $options = array(), $content = null)
    {
        if (is_string($options) && !isset($content)) {
            $content = $options;
            $options = array();
        }
        $this->out(CHtml::tag($tag, $options, $content));
    }

    protected function closeTag()
    {
        if (!empty($this->stack)) {
            $this->out(CHtml::closeTag(array_pop($this->stack)));
            return true;
        } else {
            return false;
        }
    }

    protected function closeTags()
    {
        while ($this->closeTag()) {
        }
    }

    public function close()
    {
        $this->closeTags();
        fclose($this->handle);
    }
}
