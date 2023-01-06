<?php

namespace LimeSurvey\Model\Service\SurveyPatch;

class Path
{
    protected $pathPattern = '';
    protected $modelClass = null;
    protected $isCollection = false;

    /**
     * @param string $pathPattern
     * @param string $modelClass
     * @param boolean $isCollection
     */
    public function __construct($pathPattern, $modelClass, $isCollection = false)
    {
        $this->pathPattern = rtrim($pathPattern, '/');
        $this->modelClass = $modelClass;
        $this->isCollection = $isCollection;
    }

    public function getPathPattern()
    {
        return $this->pathPattern;
    }

    public function getModelClass()
    {
        return $this->modelClass;
    }

    public function isCollection()
    {
        return $this->isCollection;
    }

    public function match($path)
    {
        $patternParts = explode('/', $this->pathPattern);
        $parts = explode('/',  rtrim($path, '/'));

        $result = null;
        $variables = [];
        if (count($patternParts) == count($parts)) {
            foreach ($patternParts as $x => $patternPart) {
                $isVariable = is_string($patternPart) ? $patternPart[0] == '$' : false;
                if ($isVariable) {
                    $propName = substr($patternPart, 1);
                    $variables[$propName] = $parts[$x];
                } elseif ($parts[$x] !== $patternPart) {
                    $result = false;
                    break;
                }
            }
            if ($result === null) {
                $result = new Meta($path, $variables);
            }
        }

        return $result;
    }

    public static function getDefaults()
    {
        return [
            new Path('/defaultlanguage/$prop', SurveyLanguageSetting::class),
            new Path('/defaultlanguage', SurveyLanguageSetting::class),
            new Path('/languages/$x', null),
            new Path('/languages', null),
            new Path('/questionGroups/$questionGroupX/l10ns/$language/$prop', QuestionGroupL10n::class),
            new Path('/questionGroups/$questionGroupX/l10ns/$language', QuestionGroupL10n::class, true),
            new Path('/questionGroups/$questionGroupX/$prop', QuestionGroup::class),
            new Path('/questionGroups', QuestionGroup::class, true),
        ];
    }
}
