<?php
use ls\models\Survey;

class HtmlWriter extends Writer
    {



        /**
         * Manages stack of open HTML tags that need closing.
         */
        protected $stack = array();

        public function beforeRenderRecords($headers, Survey $survey)
        {
            return implode("\n", [
                '<!DOCTYPE html>',
                \Html::openTag('html'),
                \Html::openTag('head'),
                \Html::tag('meta', ['charset' => 'utf-8']),
                \Html::tag('style', [], 'td { border: 1px solid black }'),
                \Html::closeTag('head'),
                \Html::openTag('body'),
                \Html::tag('h1', ['data-sid' => $survey->sid], gT("ls\models\Survey name (ID)")
                    . ": {$survey->localizedTitle} ({$survey->sid})")
            ]);



            
            
        }

        public function afterRenderRecords() {
            return implode("\n", [
                \Html::closeTag('body'),
                \Html::closeTag('html')
            ]);
        }

        protected function renderRecord($headers, $values)
        {
            $record = \Html::tag('h1', [], sprintf(gT("ls\models\Survey response")));
            $record .= \Html::openTag('div', [
                'class' => 'response',
                'data-srid' => $values[0]
            ]);
            foreach ($this->groupMap as $gid => $questions)
            {
                if ($gid != 0)
                {
                    $record .= \Html::tag('h2', [],  gT("Group") . ": " . $questions[0]['group_name']);
                }
                $record .= \Html::openTag('table', array(
                    'class' => 'group',
                    'data-gid' => $questions[0]['gid']
                ));
                foreach ($questions as $question)
                {
                    if (isset($values[$question['index']]) && isset($headers[$question['index']]))
                    {
                        $record .= $this->renderQuestion($question, CHtml::encode($values[$question['index']]), $headers[$question['index']]);
                    }
                }
                $record .= \Html::closeTag('table');
            }
            $record .= \Html::closeTag('div');
            return $record;
        }

        /**
         * Renders a question and recurses into subquestions.
         * @param type $question
         * @return string
         */
        protected function renderQuestion($question, $value, $header)
        {
            $result = '';
            if (isset($value) && strlen($value) > 0)
            {
                $result = \Html::tag('tr', ['data-qid'  => $question['qid'], 'class' => 'question'],
                    \Html::tag('td', [], $header) . \Html::tag('td', [], $value)
                );
            }

            return $result;
        }




        public function getMimeType() {
            return 'text/html';
        }
    }


