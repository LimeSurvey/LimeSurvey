<?php

/**
 * LSMessageSource class file.
 *
 * @author Denis Chenu
 * @link http://www.yiiframework.com/
 * @copyright 2021 LimeSurvey
 * License: GNU/GPL License v3 or later, see LICENSE.php
 */

class LSMessageSource extends CMessageSource
{
    public const CACHE_KEY_PREFIX = 'Yii.LSMessageSource.';
    public const MO_FILE_EXT = '.mo';
    public const PO_FILE_EXT = '.po';

    /**
     * @var integer the time in seconds that the messages can remain valid in cache.
     * @see CDbMessageSource::cachingDuration
     * @see CGettextMessageSource::cachingDuration
     */
    public $cachingDuration = 0;
    /**
     * @var string the ID of the cache application component that is used to cache the messages.
     * Defaults to 'cache' which refers to the primary cache application component.
     * @see CDbMessageSource::cacheID
     * @see CGettextMessageSource::cacheID
     */
    public $cacheID = 'cache';
    /**
     * @var string the base path for all translated messages. Defaults to null, meaning
     * @see CGettextMessageSource::basePath
     */
    public $basePath;
    /**
     * @var boolean whether to load messages from MO files. Defaults to true.
     * @see CGettextMessageSource::useMoFile
     */
    public $useMoFile = true;
    /**
     * @var boolean whether to use Big Endian to read MO files.
     * Defaults to false. This property is only used when {@link useMoFile} is true.
     * see CGettextMessageSource::useBigEndian
     */
    public $useBigEndian = false;

    /**
     * Loads the message translation for the specified language and category.
     * @see CGettextMessageSource::loadMessages
     * @see CDbMessageSource::loadMessages
     * @param string $category the message category, unused in LimeSurvey core (always '')
     * @param string $language the target language
     * @return array the loaded messages
     */
    protected function loadMessages($category, $language)
    {
        $messageFile = $this->basePath . DIRECTORY_SEPARATOR . $language . DIRECTORY_SEPARATOR . $language;
        if ($this->useMoFile) {
            $messageFile .= self::MO_FILE_EXT;
        } else {
            $messageFile .= self::PO_FILE_EXT;
        }

        if (
            $this->cachingDuration > 0
            && $this->cacheID !== false
            && ($cache = Yii::app()->getComponent($this->cacheID)) !== null
        ) {
            $key = self::CACHE_KEY_PREFIX . $messageFile . $category . '.' . $language;
            if (($data = $cache->get($key)) !== false) {
                return unserialize($data);
            }
        }

        /* Messages by getext */
        if (is_file($messageFile)) {
            if ($this->useMoFile) {
                $file = new LSGettextMoFile($this->useBigEndian);
            } else {
                $file = new CGettextPoFile();
            }
            $messagesGettext = $file->load($messageFile, $category);
        } else {
            $messagesGettext = array();
        }

        /* Messages by DB */
        if (App()->getConfig('DBVersion') >= 480) {
            $messagesDb = $this->loadMessagesFromDb($category, $language);
        } else {
            $messagesDb = array();
        }

        $messages = array_merge(
            $messagesGettext,
            $messagesDb
        );

        if (isset($cache)) {
            /* $key is set if $cache is set */
            $cache->set($key, serialize($messages), $this->cachingDuration);
        }

        return $messages;
    }

    /**
     * @see CDbMessageSource::getDbConnection
     * @throws CException if {@link connectionID} application component is invalid
     * @return CDbConnection the DB connection used for the message source.
     */
    public function getDbConnection()
    {
        return App()->getDb();
    }

    /**
     * @see CDbMessageSource::loadMessagesFromDb
     * @param string $category the message category, unused in LimeSurvey core (always '')
     * @param string $language the target language
     * @return array the messages loaded from database
     */
    protected function loadMessagesFromDb($category, $language)
    {
        $command = $this->getDbConnection()->createCommand()
            ->select("t1.message AS message, t2.translation AS translation")
            ->from(array(
                "{{source_message}} t1",
                "{{message}} t2"
            ))
            ->where(
                't1.id=t2.id AND t1.category=:category AND t2.language=:language',
                array(':category' => $category, ':language' => $language)
            );
        $messages = array();
        foreach ($command->queryAll() as $row) {
            $messages[$row['message']] = $row['translation'];
        }

        return $messages;
    }
}
