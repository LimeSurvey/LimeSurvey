<?php

namespace LimeSurvey\Models\Services;

/**
 * Typed access to the runtime state of one survey in the participant session.
 *
 * While a participant takes a survey, its state lives in
 * $_SESSION['responses_<surveyid>']: current step, response ID, token,
 * language, field map, group list and the answers given so far. This class
 * wraps that array so callers do not need to know its key names and value
 * types.
 *
 * The object holds no copy of the data. Every call reads or writes the
 * session directly, so it stays in sync with code that still uses
 * $_SESSION['responses_<surveyid>'] itself, and with killSurveySession().
 */
class SurveySessionState
{
    /** @var string Prefix of the session key holding a survey's state */
    public const SESSION_KEY_PREFIX = 'responses_';

    /** @var string Prefix of the key holding a question timer, followed by the question ID */
    public const QUESTION_TIMER_KEY_PREFIX = 'timer_question_';

    /** @var self[] Instances by survey ID, see forSurvey() */
    private static array $instances = [];

    /** @var int */
    private int $surveyId;

    /** @var string */
    private string $sessionKey;

    /**
     * @param int $surveyId
     */
    public function __construct(int $surveyId)
    {
        $this->surveyId = $surveyId;
        $this->sessionKey = self::SESSION_KEY_PREFIX . $surveyId;
    }

    /**
     * Returns a shared instance for a survey. The object holds no data, so
     * sharing it is safe; use this in hot code paths and in classes that are
     * serialized (like LimeExpressionManager) instead of keeping a property.
     *
     * @param int $surveyId
     * @return self
     */
    public static function forSurvey(int $surveyId): self
    {
        if (!isset(self::$instances[$surveyId])) {
            self::$instances[$surveyId] = new self($surveyId);
        }
        return self::$instances[$surveyId];
    }

    /**
     * @return int
     */
    public function getSurveyId(): int
    {
        return $this->surveyId;
    }

    /**
     * Returns the key of this survey's state inside $_SESSION.
     *
     * @return string
     */
    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    /**
     * Whether the session holds any state for this survey.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return isset($_SESSION[$this->sessionKey]);
    }

    /**
     * Returns the whole state array, or an empty array if there is none.
     * For code that needs a snapshot (e.g. to pass to a cache check); prefer
     * the specific accessors otherwise.
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = $_SESSION[$this->sessionKey] ?? [];
        return is_array($data) ? $data : [];
    }

    /**
     * Removes all state of this survey from the session.
     *
     * Unlike killSurveySession(), this does not reset the ExpressionManager.
     *
     * @return void
     */
    public function clear(): void
    {
        unset($_SESSION[$this->sessionKey]);
    }

    /**
     * Whether a key is set and not null, with the same semantics as isset().
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$this->sessionKey][$key]);
    }

    /**
     * Returns the raw value of a key, or $default if it is not set or null.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$this->sessionKey][$key] ?? $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $_SESSION[$this->sessionKey][$key] = $value;
    }

    /**
     * @param string $key
     * @return void
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$this->sessionKey][$key]);
    }

    /**
     * Returns the current step: 0 is the welcome page, 1 the first page.
     *
     * @return int|null Null before the survey session is initialized
     */
    public function getStep(): ?int
    {
        return $this->getInt('step');
    }

    /**
     * @param int $step
     * @return void
     */
    public function setStep(int $step): void
    {
        $this->set('step', $step);
    }

    /**
     * Returns the step shown before the current request. Holds the move name
     * instead for moves that stay on the page ("clearall", "changelang",
     * "saveall", "reload").
     *
     * @return int|string|null
     */
    public function getPrevStep()
    {
        return $this->get('prevstep');
    }

    /**
     * @param int|string $prevStep A step number or a move name
     * @return void
     */
    public function setPrevStep($prevStep): void
    {
        $this->set('prevstep', $prevStep);
    }

    /**
     * Returns the highest step the participant has reached.
     *
     * @return int|null
     */
    public function getMaxStep(): ?int
    {
        return $this->getInt('maxstep');
    }

    /**
     * @param int $maxStep
     * @return void
     */
    public function setMaxStep(int $maxStep): void
    {
        $this->set('maxstep', $maxStep);
    }

    /**
     * Returns the number of steps (groups or questions, depending on the
     * survey format).
     *
     * @return int|null
     */
    public function getTotalSteps(): ?int
    {
        return $this->getInt('totalsteps');
    }

    /**
     * @param int $totalSteps
     * @return void
     */
    public function setTotalSteps(int $totalSteps): void
    {
        $this->set('totalsteps', $totalSteps);
    }

    /**
     * Returns the number of steps that are not always hidden.
     *
     * @return int|null
     */
    public function getTotalVisibleSteps(): ?int
    {
        return $this->getInt('totalVisibleSteps');
    }

    /**
     * Returns the number of steps before the current one that are irrelevant.
     *
     * @return int
     */
    public function getNotRelevantSteps(): int
    {
        return $this->getInt('notRelevantSteps') ?? 0;
    }

    /**
     * @param int $notRelevantSteps
     * @return void
     */
    public function setNotRelevantSteps(int $notRelevantSteps): void
    {
        $this->set('notRelevantSteps', $notRelevantSteps);
    }

    /**
     * Returns the number of steps before the current one that are hidden.
     *
     * @return int
     */
    public function getHiddenSteps(): int
    {
        return $this->getInt('hiddenSteps') ?? 0;
    }

    /**
     * @param int $hiddenSteps
     * @return void
     */
    public function setHiddenSteps(int $hiddenSteps): void
    {
        $this->set('hiddenSteps', $hiddenSteps);
    }

    /**
     * Returns the ID of the response row, once one has been created.
     *
     * @return int|null
     */
    public function getResponseId(): ?int
    {
        return $this->getInt('srid');
    }

    /**
     * @param int $responseId
     * @return void
     */
    public function setResponseId(int $responseId): void
    {
        $this->set('srid', $responseId);
    }

    /**
     * Returns the participant's access code.
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->getString('token');
    }

    /**
     * @param string $token
     * @return void
     */
    public function setToken(string $token): void
    {
        $this->set('token', $token);
    }

    /**
     * Records the access code that was written to the response row.
     *
     * @param string $token
     * @return void
     */
    public function setTokenUsed(string $token): void
    {
        $this->set('tokenused', $token);
    }

    /**
     * Returns the access code entered on the "fill token" form that still has
     * to be written to the response row.
     *
     * @return string|null
     */
    public function getFillToken(): ?string
    {
        return $this->getString('filltoken');
    }

    /**
     * @param string $token
     * @return void
     */
    public function setFillToken(string $token): void
    {
        $this->set('filltoken', $token);
    }

    /**
     * @return void
     */
    public function removeFillToken(): void
    {
        $this->remove('filltoken');
    }

    /**
     * Returns the language the participant is taking the survey in.
     *
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        return $this->getString('s_lang');
    }

    /**
     * Returns the referring URL recorded when the survey was started.
     *
     * @return string|null
     */
    public function getRefUrl(): ?string
    {
        return $this->getString('refurl');
    }

    /**
     * Whether the group list has been built.
     *
     * @return bool
     */
    public function hasGroupList(): bool
    {
        return $this->has('grouplist');
    }

    /**
     * Returns the groups in display order (after group randomization).
     *
     * @return array[]
     */
    public function getGroupList(): array
    {
        return $this->getArray('grouplist');
    }

    /**
     * Returns the per-question data built by initFieldArray().
     *
     * @return array[]
     */
    public function getFieldArray(): array
    {
        return $this->getArray('fieldarray');
    }

    /**
     * Whether question or group randomization produced a randomized field map.
     *
     * @return bool
     */
    public function hasRandomizedFieldMap(): bool
    {
        return $this->has('fieldmap-' . $this->surveyId . '-randMaster');
    }

    /**
     * Returns the names of the response columns to write on save.
     *
     * @return string[]
     */
    public function getInsertArray(): array
    {
        return $this->getArray('insertarray');
    }

    /**
     * Adds a response column to the ones written on save, unless it is
     * already there.
     *
     * @param string $fieldName
     * @return void
     */
    public function addToInsertArray(string $fieldName): void
    {
        $insertArray = $this->getInsertArray();
        if (!in_array($fieldName, $insertArray)) {
            $insertArray[] = $fieldName;
            $this->set('insertarray', $insertArray);
        }
    }

    /**
     * Returns the key of the last page sent to the participant, used to detect
     * resubmission of an outdated page (browser back button).
     *
     * @return int|string|null
     */
    public function getPostKey()
    {
        return $this->get('LEMpostKey');
    }

    /**
     * @param int|string $postKey
     * @return void
     */
    public function setPostKey($postKey): void
    {
        $this->set('LEMpostKey', $postKey);
    }

    /**
     * Returns the ExpressionManager debug level bitfield (LEM_DEBUG_* flags).
     *
     * @return int 0 if not set
     */
    public function getDebugLevel(): int
    {
        return $this->getInt('LEMdebugLevel') ?? 0;
    }

    /**
     * @param int $debugLevel Bitfield of LEM_DEBUG_* flags
     * @return void
     */
    public function setDebugLevel(int $debugLevel): void
    {
        $this->set('LEMdebugLevel', $debugLevel);
    }

    /**
     * Whether the participant resumed a response with an access code, so the
     * survey must be replayed up to the last step reached.
     *
     * @return bool
     */
    public function isTokenResume(): bool
    {
        return $this->has('LEMtokenResume');
    }

    /**
     * @return void
     */
    public function clearTokenResume(): void
    {
        $this->remove('LEMtokenResume');
    }

    /**
     * Whether the "use the survey navigation buttons" warning is turned off.
     *
     * @return bool
     */
    public function isBrowserNavigationWarningIgnored(): bool
    {
        return $this->has('ignorebrowsernavigationwarning');
    }

    /**
     * @return void
     */
    public function ignoreBrowserNavigationWarning(): void
    {
        $this->set('ignorebrowsernavigationwarning', 1);
    }

    /**
     * Whether the participant already solved the captcha of a screen.
     *
     * @param string $screen Screen name, e.g. "surveyaccessscreen"
     * @return bool
     */
    public function isCaptchaPassed(string $screen): bool
    {
        return $this->has('captcha_' . $screen);
    }

    /**
     * @param string $screen Screen name, e.g. "surveyaccessscreen"
     * @return void
     */
    public function setCaptchaPassed(string $screen): void
    {
        $this->set('captcha_' . $screen, true);
    }

    /**
     * Whether the participant loaded a saved response with a name and
     * password.
     *
     * @return bool
     */
    public function hasSavedControl(): bool
    {
        return $this->has('scid');
    }

    /**
     * Whether the participant has submitted the survey.
     *
     * @return bool
     */
    public function isFinished(): bool
    {
        return (bool) $this->get('finished', false);
    }

    /**
     * Marks the survey as submitted.
     *
     * @return void
     */
    public function markFinished(): void
    {
        $this->set('finished', true);
        $this->set('sid', $this->surveyId);
    }

    /**
     * Returns the survey ID recorded by markFinished().
     *
     * @return int|null Null if the survey was not submitted in this session
     */
    public function getFinishedSurveyId(): ?int
    {
        return $this->getInt('sid');
    }

    /**
     * Returns the answer stored for a response field (SGQA field name or
     * other response column), or $default if none is set.
     *
     * @param string $fieldName
     * @param mixed $default
     * @return mixed
     */
    public function getFieldValue(string $fieldName, $default = null)
    {
        return $this->get($fieldName, $default);
    }

    /**
     * Whether an answer is stored for a response field (isset() semantics, so
     * a null answer counts as not set).
     *
     * @param string $fieldName
     * @return bool
     */
    public function hasFieldValue(string $fieldName): bool
    {
        return $this->has($fieldName);
    }

    /**
     * @param string $fieldName
     * @param mixed $value
     * @return void
     */
    public function setFieldValue(string $fieldName, $value): void
    {
        $this->set($fieldName, $value);
    }

    /**
     * @param string $fieldName
     * @return void
     */
    public function removeFieldValue(string $fieldName): void
    {
        $this->remove($fieldName);
    }

    /**
     * Whether time remaining was posted for a timed question.
     *
     * @param int $questionId
     * @return bool
     */
    public function hasQuestionTimer(int $questionId): bool
    {
        return $this->has(self::QUESTION_TIMER_KEY_PREFIX . $questionId);
    }

    /**
     * Returns the seconds remaining on a timed question, as posted with the
     * page.
     *
     * @param int $questionId
     * @return float|null Null if not set or not a number
     */
    public function getQuestionTimer(int $questionId): ?float
    {
        $value = $this->get(self::QUESTION_TIMER_KEY_PREFIX . $questionId);
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Whether the relevance status list exists.
     *
     * @return bool
     */
    public function hasRelevanceStatus(): bool
    {
        return $this->has('relevanceStatus');
    }

    /**
     * Returns the relevance status of all groups ("G<gseq>"), questions (qid)
     * and subquestion rows (rowdivid) evaluated so far.
     *
     * @return array
     */
    public function getRelevanceStatus(): array
    {
        return $this->getArray('relevanceStatus');
    }

    /**
     * Returns the relevance of one group ("G<gseq>"), question (qid) or
     * subquestion row (rowdivid), or $default if it was not evaluated yet.
     *
     * @param int|string $key
     * @param mixed $default
     * @return mixed
     */
    public function getRelevance($key, $default = null)
    {
        return $_SESSION[$this->sessionKey]['relevanceStatus'][$key] ?? $default;
    }

    /**
     * Whether the relevance of a group, question or subquestion row was
     * evaluated (isset() semantics).
     *
     * @param int|string $key
     * @return bool
     */
    public function hasRelevance($key): bool
    {
        return isset($_SESSION[$this->sessionKey]['relevanceStatus'][$key]);
    }

    /**
     * @param int|string $key
     * @param mixed $relevance
     * @return void
     */
    public function setRelevance($key, $relevance): void
    {
        $_SESSION[$this->sessionKey]['relevanceStatus'][$key] = $relevance;
    }

    /**
     * Replaces the whole relevance status list.
     *
     * @param array $relevanceStatus
     * @return void
     */
    public function setRelevanceStatus(array $relevanceStatus): void
    {
        $this->set('relevanceStatus', $relevanceStatus);
    }

    /**
     * Whether starting values (prefilled answers from the URL or the response
     * row, plus the random seed) are set.
     *
     * @return bool
     */
    public function hasStartingValues(): bool
    {
        return $this->has('startingValues');
    }

    /**
     * @return array
     */
    public function getStartingValues(): array
    {
        return $this->getArray('startingValues');
    }

    /**
     * @param array $startingValues
     * @return void
     */
    public function setStartingValues(array $startingValues): void
    {
        $this->set('startingValues', $startingValues);
    }

    /**
     * @param string $key Field name, question code or "seed"
     * @return bool
     */
    public function hasStartingValue(string $key): bool
    {
        return isset($_SESSION[$this->sessionKey]['startingValues'][$key]);
    }

    /**
     * @param string $key Field name, question code or "seed"
     * @param mixed $default
     * @return mixed
     */
    public function getStartingValue(string $key, $default = null)
    {
        return $_SESSION[$this->sessionKey]['startingValues'][$key] ?? $default;
    }

    /**
     * @param string $key Field name, question code or "seed"
     * @param mixed $value
     * @return void
     */
    public function setStartingValue(string $key, $value): void
    {
        $_SESSION[$this->sessionKey]['startingValues'][$key] = $value;
    }

    /**
     * @param string $key Field name, question code or "seed"
     * @return void
     */
    public function removeStartingValue(string $key): void
    {
        unset($_SESSION[$this->sessionKey]['startingValues'][$key]);
    }

    /**
     * Whether the field map has been built.
     *
     * @return bool
     */
    public function hasFieldMap(): bool
    {
        return $this->has('fieldmap');
    }

    /**
     * Returns the field map built by createFieldMap() for the session language.
     *
     * @return array[]
     */
    public function getFieldMap(): array
    {
        return $this->getArray('fieldmap');
    }

    /**
     * @param array $fieldMap
     * @return void
     */
    public function setFieldMap(array $fieldMap): void
    {
        $this->set('fieldmap', $fieldMap);
    }

    /**
     * Stores the randomized field map for a language and marks it as the
     * master map used for all languages.
     *
     * @param string $language
     * @param array $fieldMap
     * @return void
     */
    public function setRandomizedFieldMap(string $language, array $fieldMap): void
    {
        $key = 'fieldmap-' . $this->surveyId . $language;
        $this->set($key, $fieldMap);
        $this->set('fieldmap-' . $this->surveyId . '-randMaster', $key);
    }

    /**
     * Returns the master randomized field map, or null if the survey is not
     * randomized.
     *
     * @return array|null
     */
    public function getRandomizedFieldMap(): ?array
    {
        $key = $this->get('fieldmap-' . $this->surveyId . '-randMaster');
        if ($key === null) {
            return null;
        }
        $fieldMap = $this->get((string) $key);
        return is_array($fieldMap) ? $fieldMap : null;
    }

    /**
     * Forgets which randomized field map is the master one. The map itself
     * stays in the session.
     *
     * @return void
     */
    public function clearRandomizedFieldMap(): void
    {
        $this->remove('fieldmap-' . $this->surveyId . '-randMaster');
    }

    /**
     * Whether question or group randomization is used in this survey.
     *
     * @return bool
     */
    public function isRandomized(): bool
    {
        return (bool) $this->get('randomized', false);
    }

    /**
     * @param bool $randomized
     * @return void
     */
    public function setRandomized(bool $randomized): void
    {
        $this->set('randomized', $randomized);
    }

    /**
     * @param array[] $groupList
     * @return void
     */
    public function setGroupList(array $groupList): void
    {
        $this->set('grouplist', $groupList);
    }

    /**
     * @return void
     */
    public function removeGroupList(): void
    {
        $this->remove('grouplist');
    }

    /**
     * Returns the map from original to randomized group IDs.
     *
     * @return array
     */
    public function getGroupReMap(): array
    {
        return $this->getArray('groupReMap');
    }

    /**
     * @param array $groupReMap
     * @return void
     */
    public function setGroupReMap(array $groupReMap): void
    {
        $this->set('groupReMap', $groupReMap);
    }

    /**
     * @return void
     */
    public function removeGroupReMap(): void
    {
        $this->remove('groupReMap');
    }

    /**
     * Whether the field array has been built.
     *
     * @return bool
     */
    public function hasFieldArray(): bool
    {
        return $this->has('fieldarray');
    }

    /**
     * @param array[] $fieldArray
     * @return void
     */
    public function setFieldArray(array $fieldArray): void
    {
        $this->set('fieldarray', $fieldArray);
    }

    /**
     * @return void
     */
    public function removeFieldArray(): void
    {
        $this->remove('fieldarray');
    }

    /**
     * @param string[] $insertArray
     * @return void
     */
    public function setInsertArray(array $insertArray): void
    {
        $this->set('insertarray', $insertArray);
    }

    /**
     * @return void
     */
    public function removeInsertArray(): void
    {
        $this->remove('insertarray');
    }

    /**
     * Returns the map from response field name to "Q<qid>".
     *
     * @return string[]
     */
    public function getFieldNamesInfo(): array
    {
        return $this->getArray('fieldnamesInfo');
    }

    /**
     * @param string[] $fieldNamesInfo
     * @return void
     */
    public function setFieldNamesInfo(array $fieldNamesInfo): void
    {
        $this->set('fieldnamesInfo', $fieldNamesInfo);
    }

    /**
     * @return void
     */
    public function removeFieldNamesInfo(): void
    {
        $this->remove('fieldnamesInfo');
    }

    /**
     * @param string $language
     * @return void
     */
    public function setLanguage(string $language): void
    {
        $this->set('s_lang', $language);
    }

    /**
     * @param string $refUrl
     * @return void
     */
    public function setRefUrl(string $refUrl): void
    {
        $this->set('refurl', $refUrl);
    }

    /**
     * @return void
     */
    public function removeRefUrl(): void
    {
        $this->remove('refurl');
    }

    /**
     * Returns the access code that was written to the response row.
     *
     * @return string|null
     */
    public function getTokenUsed(): ?string
    {
        return $this->getString('tokenused');
    }

    /**
     * @param int $totalVisibleSteps
     * @return void
     */
    public function setTotalVisibleSteps(int $totalVisibleSteps): void
    {
        $this->set('totalVisibleSteps', $totalVisibleSteps);
    }

    /**
     * @return int|null
     */
    public function getTotalQuestions(): ?int
    {
        return $this->getInt('totalquestions');
    }

    /**
     * @param int $totalQuestions
     * @param int $totalVisibleQuestions
     * @return void
     */
    public function setTotalQuestions(int $totalQuestions, int $totalVisibleQuestions): void
    {
        $this->set('totalquestions', $totalQuestions);
        $this->set('totalVisibleQuestions', $totalVisibleQuestions);
    }

    /**
     * Returns the number of questions that are not always hidden.
     *
     * @return int|null
     */
    public function getTotalVisibleQuestions(): ?int
    {
        return $this->getInt('totalVisibleQuestions');
    }

    /**
     * Returns the survey URL parameters captured when the survey was started.
     *
     * @return array
     */
    public function getUrlParams(): array
    {
        return $this->getArray('urlparams');
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function setUrlParam(string $name, $value): void
    {
        $_SESSION[$this->sessionKey]['urlparams'][$name] = $value;
    }

    /**
     * Returns the date stamp of the last save, in GMT.
     *
     * @return string|null
     */
    public function getDatestamp(): ?string
    {
        return $this->getString('datestamp');
    }

    /**
     * @param string $datestamp
     * @return void
     */
    public function setDatestamp(string $datestamp): void
    {
        $this->set('datestamp', $datestamp);
    }

    /**
     * Returns the date the participant started the survey, in GMT.
     *
     * @return string|null
     */
    public function getStartDate(): ?string
    {
        return $this->getString('startdate');
    }

    /**
     * @param string $startDate
     * @return void
     */
    public function setStartDate(string $startDate): void
    {
        $this->set('startdate', $startDate);
    }

    /**
     * Returns the ID of the saved_control row when the participant saved or
     * loaded the response with a name and password.
     *
     * @return int|null
     */
    public function getSavedControlId(): ?int
    {
        return $this->getInt('scid');
    }

    /**
     * @param int $savedControlId
     * @return void
     */
    public function setSavedControlId(int $savedControlId): void
    {
        $this->set('scid', $savedControlId);
    }

    /**
     * Returns the answer to the arithmetic security question of the
     * save/load forms.
     *
     * @return int|null
     */
    public function getSecurityAnswer(): ?int
    {
        return $this->getInt('secanswer');
    }

    /**
     * @param int $answer
     * @return void
     */
    public function setSecurityAnswer(int $answer): void
    {
        $this->set('secanswer', $answer);
    }

    /**
     * Marks the survey to be replayed up to the last step reached on the next
     * request (resume with access code, or survey structure changed).
     *
     * @return void
     */
    public function setTokenResume(): void
    {
        $this->set('LEMtokenResume', true);
    }

    /**
     * Returns the response list columns an administrator chose to show.
     *
     * @return string[]|null Null if no choice was made, so all columns are shown
     */
    public function getFilteredColumns(): ?array
    {
        $value = $this->get('filteredColumns');
        return is_array($value) ? $value : null;
    }

    /**
     * @param string[] $columns
     * @return void
     */
    public function setFilteredColumns(array $columns): void
    {
        $this->set('filteredColumns', $columns);
    }

    /**
     * Returns a value cast to int, or null if it is not set.
     *
     * @param string $key
     * @return int|null
     */
    private function getInt(string $key): ?int
    {
        $value = $this->get($key);
        return $value === null ? null : (int) $value;
    }

    /**
     * Returns a value cast to string, or null if it is not set.
     *
     * @param string $key
     * @return string|null
     */
    private function getString(string $key): ?string
    {
        $value = $this->get($key);
        return $value === null ? null : (string) $value;
    }

    /**
     * Returns an array value, or an empty array if it is not set.
     *
     * @param string $key
     * @return array
     */
    private function getArray(string $key): array
    {
        $value = $this->get($key);
        return is_array($value) ? $value : [];
    }
}
