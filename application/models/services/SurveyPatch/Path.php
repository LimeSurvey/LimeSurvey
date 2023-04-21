<?php

namespace LimeSurvey\Models\Services\JsonPatch;


/**
 * Survey Patch Path
 *
 * Represents an updatable survey patch path.
 */
class Path
{
    const PATH_TYPE_OBJECT = 'object';
    const PATH_TYPE_PROP = 'prop';
    const PATH_TYPE_COLLECTION = 'collection';

    protected $pathPattern = '';
    protected $modelClass = null;
    protected $type = null;

    /**
     * Survey Path Constructor
     *
     * @param string $pathPattern Path pattern (maybe include variable path elements my/path/$variable/$other)
     * @param string $modelClass Model class name
     * @param boolean $isProp Path points to an object property rather than a root
     * @param boolean $isCollection Path points to a collection of objects
     */
    public function __construct($pathPattern, $modelClass = null, $type = null)
    {
        $this->pathPattern = rtrim($pathPattern, '/');
        $this->modelClass = $modelClass;
        $this->type = $type ?: self::PATH_TYPE_OBJECT;
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
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Match path
     *
     * @param string $path
     * @return boolean|PathMatch
     */
    public function match($realPath)
    {
        $patternParts = explode('/', $this->pathPattern);
        $parts = explode('/',  rtrim($realPath, '/'));

        $result = null;
        $variables = [];
        if (count($patternParts) != count($parts)) {
            $result = false;
        } else {
            foreach ($patternParts as $x => $patternPart) {
                $isVariable = is_string($patternPart) && !empty($patternPart)
                    ? $patternPart[0] == '$'
                    : false;
                if ($isVariable) {
                    $propName = substr($patternPart, 1);
                    $variables[$propName] = $parts[$x];
                } elseif ($parts[$x] !== $patternPart) {
                    $result = false;
                    break;
                }
            }
            if ($result === null) {
                $result = new PathMatch(
                    $this->modelClass,
                    $variables,
                    $this->type
                );
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
            new Path('/languages/$x'),
            new Path('/languages'),
            new Path(
                '/languagesettings/$language/$prop',
                SurveyLanguageSetting::class,
                Path::PATH_TYPE_PROP
            ),
            new Path(
                '/languagesettings/$language',
                SurveyLanguageSetting::class,
            ),
            new Path(
                '/defaultlanguage/$prop',
                SurveyLanguageSetting::class,
                Path::PATH_TYPE_PROP
            ),
            new Path('/defaultlanguage'),
            new Path(
                '/questionGroups/$questionGroupX/l10ns/$language/$prop',
                QuestionGroupL10n::class,
                Path::PATH_TYPE_PROP
            ),
            new Path(
                '/questionGroups/$questionGroupX/l10ns/$language',
                QuestionGroupL10n::class
            ),
            new Path(
                '/questionGroups/$questionGroupX/questions/$questionX',
                Question::class
            ),
            new Path(
                '/questionGroups/$questionGroupX/questions/$questionX/$prop',
                Question::class,
                Path::PATH_TYPE_PROP
            ),
            new Path(
                '/questionGroups/$questionGroupX/questions/$questionX/l10ns/$language/$prop',
                QuestionL10n::class,
                Path::PATH_TYPE_PROP
            ),
            new Path(
                '/questionGroups/$questionGroupX/questions/$questionX/l10ns/$language',
                QuestionL10n::class
            ),
            new Path(
                '/questionGroups/$questionGroupX/questions/$questionX/attributes/$attributeCode',
                QuestionAttribute::class
            ),
            new Path(
                '/questionGroups/$questionGroupX/questions/$questionX/attributes/$attributeCode/$prop',
                QuestionAttribute::class,
                Path::PATH_TYPE_PROP
            ),
            new Path(
                '/questionGroups/$questionGroupX/questions/$questionX/attributes',
                QuestionAttribute::class,
                Path::PATH_TYPE_COLLECTION
            ),
            new Path(
                '/questionGroups/$questionGroupX/questions/$questionX/answers/$answerX',
                Answer::class
            ),
            new Path(
                '/questionGroups/$questionGroupX/questions/$questionX/answers/$answerX/$prop',
                Answer::class,
                Path::PATH_TYPE_PROP
            ),
            new Path(
                '/questionGroups/$questionGroupX/questions/$questionX/answers',
                Answer::class,
                Path::PATH_TYPE_COLLECTION
            ),
            new Path(
                '/questionGroups/$questionGroupX/questions',
                Question::class,
                Path::PATH_TYPE_COLLECTION
            ),
            new Path(
                '/questionGroups/$questionGroupX/$prop',
                QuestionGroup::class,
                Path::PATH_TYPE_PROP
            ),
            new Path(
                '/questionGroups',
                QuestionGroup::class,
                Path::PATH_TYPE_COLLECTION
            ),
            new Path(
                '/$prop',
                Survey::class,
                Path::PATH_TYPE_PROP
            ),
            new Path('/', Survey::class)
        ];
    }
}
