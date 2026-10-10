<?php

namespace LimeSurvey\Models\Services;

/**
 * Typed access to the ExpressionManager state kept in the PHP session.
 *
 * Unlike SurveySessionState, this state is not per survey: there is one
 * ExpressionManager per PHP session. It holds the survey and language the
 * ExpressionManager was last used for, its serialized instance and the flags
 * that force it to be rebuilt.
 *
 * The object holds no copy of the data; every call reads or writes $_SESSION
 * directly. Don't keep it in a property of LimeExpressionManager, since that
 * object is serialized into the session.
 */
class ExpressionManagerSessionState
{
    /** @var self|null Shared instance, see current() */
    private static ?self $instance = null;

    /**
     * Returns a shared instance. The object holds no data, so sharing it is safe.
     *
     * @return self
     */
    public static function current(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Whether a survey ID is recorded.
     *
     * @return bool
     */
    public function hasSurveyId(): bool
    {
        return isset($_SESSION['LEMsid']);
    }

    /**
     * Returns the ID of the survey the ExpressionManager was last used for.
     *
     * @return int|null
     */
    public function getSurveyId(): ?int
    {
        return isset($_SESSION['LEMsid']) ? (int) $_SESSION['LEMsid'] : null;
    }

    /**
     * @param int $surveyId
     * @return void
     */
    public function setSurveyId(int $surveyId): void
    {
        $_SESSION['LEMsid'] = $surveyId;
    }

    /**
     * Whether a language is recorded.
     *
     * @return bool
     */
    public function hasLanguage(): bool
    {
        return isset($_SESSION['LEMlang']);
    }

    /**
     * Returns the language the ExpressionManager works in.
     *
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        return isset($_SESSION['LEMlang']) ? (string) $_SESSION['LEMlang'] : null;
    }

    /**
     * @param string $language
     * @return void
     */
    public function setLanguage(string $language): void
    {
        $_SESSION['LEMlang'] = $language;
    }

    /**
     * Returns the serialized LimeExpressionManager instance, if one is stored.
     *
     * @return string|null
     */
    public function getSerializedInstance(): ?string
    {
        return isset($_SESSION['LEMsingleton']) ? (string) $_SESSION['LEMsingleton'] : null;
    }

    /**
     * @param string $serialized
     * @return void
     */
    public function setSerializedInstance(string $serialized): void
    {
        $_SESSION['LEMsingleton'] = $serialized;
    }

    /**
     * Removes the stored instance, so the next request starts with a new one.
     *
     * @return void
     */
    public function clearSerializedInstance(): void
    {
        unset($_SESSION['LEMsingleton']);
    }

    /**
     * Whether the stored instance must be discarded on the next use (the
     * survey structure or settings changed).
     *
     * @return bool
     */
    public function isDirty(): bool
    {
        return isset($_SESSION['LEMdirtyFlag']);
    }

    /**
     * @return void
     */
    public function markDirty(): void
    {
        $_SESSION['LEMdirtyFlag'] = true;
    }

    /**
     * @return void
     */
    public function clearDirty(): void
    {
        unset($_SESSION['LEMdirtyFlag']);
    }

    /**
     * Whether the field map must be rebuilt instead of taken from the cache
     * the next time the ExpressionManager loads a survey.
     *
     * @return bool
     */
    public function isForceRefreshRequested(): bool
    {
        return isset($_SESSION['LEMforceRefresh']);
    }

    /**
     * @return void
     */
    public function requestForceRefresh(): void
    {
        $_SESSION['LEMforceRefresh'] = true;
    }

    /**
     * @return void
     */
    public function clearForceRefresh(): void
    {
        unset($_SESSION['LEMforceRefresh']);
    }
}
