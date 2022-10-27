<?php

namespace LimeSurvey\Models\Services;

/**
 * Session interface defines session data management API.
 *
 * @see https://github.com/yiisoft/session
 * @todo Install via composer when LimeSurvey no longer support PHP 7.2
 */
interface SessionInterface
{
    /**
     * Read value from session.
     *
     * @param string $key Key to read value from.
     * @param mixed $default Default value in case there is no value with the key specified. Null by default.
     *
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Write value into session.
     *
     * @param string $key Key to write value to.
     * @param mixed $value Value to write.
     */
    public function set(string $key, $value): void;

    /**
     * Write session and close it.
     */
    public function close(): void;

    /**
     * Start session if it is not started yet.
     */
    public function open(): void;

    /**
     * @return bool If session is started.
     */
    public function isActive(): bool;

    /**
     * @return string|null Current session ID or null if there is no started session.
     */
    public function getId(): ?string;

    /**
     * @param string $sessionId Set session ID.
     */
    public function setId(string $sessionId): void;

    /**
     * Regenerate session ID keeping data.
     */
    public function regenerateId(): void;

    /**
     * Discard session changes and close session.
     */
    public function discard(): void;

    /**
     * @return string Session name.
     */
    public function getName(): string;

    /**
     * @return array All session data.
     */
    public function all(): array;

    /**
     * Remove value from session.
     *
     * @param string $key
     */
    public function remove(string $key): void;

    /**
     * Check if session has a value with a given key.
     *
     * @param string $key The key to check.
     *
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Read value and remove it afterwards.
     *
     * @param string $key The key to pull value from.
     * @param mixed $default Default value in case there is no value with the key specified. Null by default.
     *
     * @return mixed The value.
     */
    public function pull(string $key, $default = null);

    /**
     * Remove session data from runtime.
     */
    public function clear(): void;

    /**
     * Remove session data from runtime and session storage.
     */
    public function destroy(): void;

    /**
     * @return array Parameters for a session cookie.
     */
    public function getCookieParameters(): array;
}
