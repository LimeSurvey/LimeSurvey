<?php

namespace LimeSurvey\Model\Service\SurveyPatch;


/**
 * Survey Patch Path
 *
 * Represents an updatable survey patch path.
 */
class Path
{
    protected $pathPattern = '';
    protected $modelClass = null;
    protected $isCollection = false;

    /**
     * Survey Path Constructor
     *
     * @param string $pathPattern
     * @param string $modelClass
     * @param boolean $isCollection
     */
    public function __construct($pathPattern, $modelClass = null, $isCollection = false)
    {
        $this->pathPattern = rtrim($pathPattern, '/');
        $this->modelClass = $modelClass;
        $this->isCollection = $isCollection;
    }

    /**
     * Get path pattern
     *
     * @return string
     */
    public function getPathPattern()
    {
        return $this->pathPattern;
    }

    /**
     * Get model class
     *
     * @return string
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * Is collection
     *
     * @return boolean
     */
    public function isCollection()
    {
        return $this->isCollection;
    }

    /**
     * Match path
     *
     * @param string $path
     * @return boolean|Meta
     */
    public function match($realPath)
    {
        $patternParts = explode('/', $this->pathPattern);
        $parts = explode('/',  rtrim($realPath, '/'));

        $result = null;
        $variables = [];
        if (count($patternParts) == count($parts)) {
            foreach ($patternParts as $x => $patternPart) {
                $isVariable = is_string($patternPart) && !empty($patternPart) ? $patternPart[0] == '$' : false;
                if ($isVariable) {
                    $propName = substr($patternPart, 1);
                    $variables[$propName] = $parts[$x];
                } elseif ($parts[$x] !== $patternPart) {
                    $result = false;
                    break;
                }
            }
            if ($result === null) {
                $result = new Meta($realPath, $variables);
            }
        }

        return $result;
    }

    /**
     * Get defaults
     *
     * @return array
     */
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
