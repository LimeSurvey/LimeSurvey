<?php
namespace ls\import;

/**
 * Class BaseElementXmlImport
 * Base class for XML files that use elements only. (Current LSS format)
 * @package ls\import
 */
abstract class BaseElementXmlImport extends BaseXmlImport
{

    public $parsedDocument;
    public function setSource($file)
    {
        parent::setSource($file);
        $this->parsedDocument = $this->constructTree($this->recurse($this->document->firstChild));
    }

    /**
     * Constructs a tree from an array extracted from an LSS file.
     * @param $data
     */
    protected function constructTree($data) {
        $result = $data['surveys']['rows'][0];
        $result['languagesettings'] = $data['surveys_languagesettings']['rows'];
        $languages = isset($result['additional_languages']) && !empty($result['additional_languages']) ? array_merge([$result['language']], array_filter(explode(' ', $result['additional_languages']))) : [$result['language']];
        // Recursion create cleaner code at the cost of some speed (since the $data array is iterated a lot).
        foreach($data['groups']['rows'] as $group) {
            // Only handle the base language.
            if ($group['language'] == $result['language']) {
                $result['groups'][] = $this->constructGroup($group, $result['language'], $data);
            }
        }
        return $result;
    }

    protected function recurse(\DOMNode $node) {
        if ($node->hasChildNodes()) {
            $result = [];
            if ($node->childNodes->length == 1) {
                return $node->firstChild->data;
            }
            foreach ($node->childNodes as $childNode) {
                if ($childNode instanceof \DOMElement) {
                    $recurse = $this->recurse($childNode);
                    if (array_key_exists($childNode->tagName, ['row' => true, 'fieldname' => true])) {
                        $result[] = $recurse;
                    } elseif (!isset($result[$childNode->tagName])) {
                        $result[$childNode->tagName] = $recurse;
                    } elseif(is_array($result[$childNode->tagName]) && isset($result[$childNode->tagName][0])) {
                        $result[$childNode->tagName][] = $recurse;
                    } else {
                        $result[$childNode->tagName] = [$result[$childNode->tagName], $recurse];
                    }
                }
            }

            return $result;
        }
    }

    protected  function constructAnswer($answer, $language, $data) {
        // Add translations.
        foreach ($data['answers']['rows'] as $translatedAnswer) {
            if ($translatedAnswer['qid'] == $answer['qid']
                && $translatedAnswer['code'] == $answer['code']
                && $translatedAnswer['language'] != $language
            ) {
                $answer['translations'][] = $translatedAnswer;
            }
        }
        return $answer;
    }
    protected function constructQuestion($question, $language, $data)
    {

        // Add translations.
        $questions = isset($data['subquestions']) ? array_merge($data['subquestions']['rows'], $data['questions']['rows']) : $data['questions']['rows'];
        foreach ($questions as $translatedQuestion) {
            if ($translatedQuestion['qid'] == $question['qid']
                && $translatedQuestion['language'] != $language
            ) {
                $question['translations'][] = $translatedQuestion;
            }
        }

        // Add subquestions
        foreach (isset($data['subquestions']) ? $data['subquestions']['rows'] : [] as $subQuestion) {
            if ($subQuestion['parent_qid'] == $question['qid'] && $subQuestion['language'] == $language) {
                $question['subquestions'][] = $this->constructQuestion($subQuestion, $language, $data);
            }
        }

        // Add answers
        foreach ($data['answers']['rows'] as $answer) {
            if ($answer['qid'] == $question['qid']
                && $answer['language'] == $language) {
                $question['answers'][] = $this->constructAnswer($answer, $language, $data);
            }
        }

        // Add conditions
        foreach (isset($data['conditions']) ? $data['conditions']['rows'] : [] as $condition) {
            if ($condition['qid'] == $question['qid']) {
                $question['conditions'][] = $condition;
            }
        }

        // Add attributes
        foreach (isset($data['question_attributes']) ? $data['question_attributes']['rows'] : [] as $attribute) {
            if ($attribute['qid'] == $question['qid']) {
                $question[$attribute['attribute']] = $attribute['value'];
            }
        }

        return $question;
    }




    /**
     * Creates a tree structure for a specific group.
     * @param $group
     * @param $survey
     * @param $data
     */
    protected function constructGroup($group, $language, $data) {
        // Add translations.
        foreach($data['groups']['rows'] as $translatedGroup) {
            if ($translatedGroup['gid'] == $group['gid']
                && $translatedGroup['language'] != $language) {
                $group['translations'][] = $translatedGroup;
            }
        }


        // Add questions.
        foreach($data['questions']['rows'] as $question) {
            // Only handle the base language.
            if ($question['gid'] == $group['gid'] && $question['language'] == $language) {

                $group['questions'][] = $this->constructQuestion($question, $language, $data);
            }





        }
        return $group;

    }
}