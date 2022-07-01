<?php

/**
 * @inheritdoc
 * replace to not use $_SESSION in console
 */
class ConsoleHttpSession extends CHttpSession
{
    /* @inheritdoc
     * Disable all action
     */
    public function setCookieParams($value)
    {
        // nothing to do
    }

    /* @inheritdoc
     * Disable all action
     */
    public function setCookieMode($value)
    {
        // nothing to do
    }

    /* @inheritdoc
     * Disable all action
     */
    public function setSessionName($value)
    {
        // nothing to do
    }

    /* @inheritdoc
     * Return always default
     */
    public function get($key, $defaultValue = null)
    {
        return $defaultValue;
    }
}
