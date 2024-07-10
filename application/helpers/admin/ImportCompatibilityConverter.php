<?php

/**
 * This will convert LSv4+ survey XML element (with L10Ns) to ls v3 compatible format (without L10Ns) to be able
 * to import new version XML files to ls v3
 */
class ImportCompatibilityConverter
{
    private SimpleXMLElement $xml;
    private array $languages;
    private array $groups;
    private array $questions;
    private array $subQuestions;
    private array $answers;
    private array $questionL10ns;
    private array $groupL10ns;
    private array $answerL10ns;

    public function __construct($xml)
    {
        Yii::import('application.helpers.export_helper', true);

        $this->xml = $xml;
    }

    /**
     * @return SimpleXMLElement
     */
    public function convert()
    {


        $items = $this->convertGroups();
        if(!empty($items)) {
            unset($this->xml->groups->rows);
            $this->addRows('groups', $items);
            unset($this->xml->group_l10ns);
        }

        $items = $this->convertSubQuestions();
        if(!empty($items)) {
            unset($this->xml->subquestions->rows);
            $this->addRows('subquestions', $items);
        }

        $items = $this->convertQuestions();
        if(!empty($items)) {
            unset($this->xml->questions->rows);
            $this->addRows('questions', $items);
            unset($this->xml->question_l10ns);
        }

        $items = $this->convertSubQuestions();
        if(!empty($items)) {
            unset($this->xml->subquestions->rows);
            $this->addRows('subquestions', $items);
            unset($this->xml->question_l10ns);
        }

        $items = $this->convertAnswers();
        if(!empty($items)) {
            unset($this->xml->answers->rows);
            $this->addRows('answers', $items);
            unset($this->xml->answer_l10ns);

        }

        return $this->xml;

    }




    /**
     * @param string $xmlElementName
     * @param array $data
     * @return void
     */
    private function addRows($xmlElementName,$data)
    {
        $this->xml->{$xmlElementName}->addChild('rows');
        foreach ($data as $item) {
            $row = $this->xml->{$xmlElementName}->rows->addChild('row');
            foreach ($item as $name => $value) {
                $item = $row->addChild($name);
                $child_node = dom_import_simplexml($item);
                $child_owner = $child_node->ownerDocument;
                $child_node->appendChild($child_owner->createCDATASection(cleanXmlValueForCdata($value)));
            }
        }
    }


    private function convertGroups()
    {
        $groups = $this->parseGroups();
        $out = [];
        foreach ($groups as $group) {
            $newGroups = $this->convertGroup($group);
            $out = array_merge($out, $newGroups);
        }
        return $out;

    }

    /**
     * @param array $group
     * @return array
     */
    private function convertGroup($group)
    {
        $languages = $this->parseLanguages();
        $allL10Ns = $this->parseGroupL10ns();
        $l10ns = $allL10Ns[$group['gid']];
        $out = [];
        foreach ($languages as $language) {
            $newFormat = $group;
            $newFormat['group_name'] = $l10ns[$language]['group_name'];
            $newFormat['description'] = $l10ns[$language]['description'];
            $newFormat['language'] = $language;
            $out[] = $newFormat;
        }
        return $out;
    }

    private function convertAnswers()
    {
        $answers = $this->parseAnswers();

        $out = [];
        foreach ($answers as $answer) {
            $newAnswers = $this->convertAnswer($answer);
            $out = array_merge($out, $newAnswers);
        }
        return $out;

    }

    /**
     * @param array $answer
     * @return array
     */
    private function convertAnswer($answer)
    {
        $languages = $this->parseLanguages();
        $allL10Ns = $this->parseAnswerL10ns();
        $l10ns = $allL10Ns[$answer['aid']];

        $out = [];
        foreach ($languages as $language) {
            $newFormat = $answer;
            $newFormat['answer'] = $l10ns[$language]['answer'];
            $newFormat['language'] = $language;
            unset($newFormat['aid']);
            $out[] = $newFormat;
        }
        return $out;
    }

    private function convertSubQuestions()
    {
        $questions = $this->parseSubQuestions();
        $out = [];
        foreach ($questions as $question) {
            $newQuestions = $this->convertQuestion($question);
            $out = array_merge($out, $newQuestions);
        }
        return $out;
    }


    private function convertQuestions()
    {
        $questions = $this->parseQuestions();
        $out = [];
        foreach ($questions as $question) {
            $newQuestions = $this->convertQuestion($question);
            $out = array_merge($out, $newQuestions);
        }
        return $out;
    }

    /**
     * @param array $question
     * @return array
     */
    private function convertQuestion($question)
    {
        $languages = $this->parseLanguages();
        $allL10Ns = $this->parseQuestionL10ns();
        $l10ns = $allL10Ns[$question['qid']];
        $out = [];
        foreach ($languages as $language) {
            $newFormat = $question;
            $newFormat['question'] = $l10ns[$language]['question'];
            $newFormat['question'] .= $l10ns[$language]['script'];
            $newFormat['help'] = $l10ns[$language]['help'];
            $newFormat['language'] = $language;
            $out[] = $newFormat;
        }
        return $out;
    }


    private function parseCurrentData()
    {
        $groups = $this->parseGroups();
        $questions = $this->parseQuestions();
        $answers = $this->parseAnswers();
        $groupL10ns = $this->parseGroupL10ns();
        $questionL10ns = $this->parseQuestionL10ns();
        $answerL10ns = $this->parseAnswerL10ns();

        var_dump($answers);die;

    }
    private function parseAnswerL10ns()
    {
        if(isset($this->answerL10ns)) {
            return $this->answerL10ns;
        }
        $out = [];
        foreach ($this->xml->answer_l10ns->rows as $row) {
            foreach ($row as $item) {
                $itemData = [];
                foreach ($item as $key => $value) {
                    $itemData[(string)$key] = (string)$value;
                }
                $out[$itemData['aid']][$itemData['language']] = $itemData;
            }

        }
        $this->answerL10ns = $out;
        return $out;

    }

    private function parseQuestionL10ns()
    {
        if(isset($this->questionL10ns)) {
            return $this->questionL10ns;
        }
        $out = [];
        foreach ($this->xml->question_l10ns->rows as $row) {
            foreach ($row as $item) {
                $itemData = [];
                foreach ($item as $key => $value) {
                    $itemData[(string)$key] = (string)$value;
                }
                $out[$itemData['qid']][$itemData['language']] = $itemData;
            }

        }
        $this->questionL10ns = $out;
        return $out;

    }

    private function parseGroupL10ns()
    {
        if(isset($this->groupL10ns)) {
            return $this->groupL10ns;
        }
        $out = [];
        foreach ($this->xml->group_l10ns->rows as $row) {
            foreach ($row as $item) {
                $itemData = [];
                foreach ($item as $key => $value) {
                    $itemData[(string)$key] = (string)$value;
                }
                $out[$itemData['gid']][$itemData['language']] = $itemData;
            }

        }
        $this->groupL10ns = $out;
        return $out;

    }
    private function parseSubQuestions()
    {
        if(isset($this->subQuestions)) {
            return $this->subQuestions;
        }
        $out = [];
        foreach ($this->xml->subquestions->rows as $row) {
            foreach ($row as $item) {
                $itemData = [];
                foreach ($item as $key => $value) {
                    $itemData[(string)$key] = (string)$value;
                }
                $out[] = $itemData;
            }

        }
        $this->subQuestions = $out;
        return $out;

    }


    private function parseQuestions()
    {
        if(isset($this->questions)) {
            return $this->questions;
        }
        $out = [];
        foreach ($this->xml->questions->rows as $row) {
            foreach ($row as $item) {
                $itemData = [];
                foreach ($item as $key => $value) {
                    $itemData[(string)$key] = (string)$value;
                }
                $out[$itemData['qid']] = $itemData;
            }

        }
        $this->questions = $out;
        return $out;

    }


    private function parseGroups()
    {
        if(isset($this->groups)) {
            return $this->groups;
        }
        $out = [];
        foreach ($this->xml->groups->rows as $row) {
            foreach ($row as $item) {
                $itemData = [];
                foreach ($item as $key => $value) {
                    $itemData[(string)$key] = (string)$value;
                }
                $out[$itemData['gid']] = $itemData;
            }

        }
        $this->groups = $out;
        return $out;

    }
    private function parseAnswers()
    {
        if(isset($this->answers)) {
            return $this->answers;
        }
        $out = [];
        if(empty($this->xml->answers->rows)) {
            return $out;
        }
        foreach ($this->xml->answers->rows as $row) {
            foreach ($row as $item) {
                $itemData = [];
                foreach ($item as $key => $value) {
                    $itemData[(string)$key] = (string)$value;
                }
                $out[] = $itemData;
            }

        }
        $this->answers = $out;
        return $out;

    }


    private function parseLanguages()
    {
        if(isset($this->languages)) {
            return $this->languages;
        }
        $importlanguages = [];
        foreach ($this->xml->languages->language as $language) {
            $importlanguages[] = (string) $language;
        }
        $this->languages = $importlanguages;
        return $importlanguages;

    }

}
