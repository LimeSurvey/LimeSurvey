<?php

/**
 * Extension of CGettextMessageSource to allow plugins to have
 * their own localization files
 *
 * @since 2016-07-25
 * @author Olle Haerstedt
 */
class LSCGettextMessageSource extends CGettextMessageSource
{
    const CACHE_KEY_PREFIX = 'Yii.LSCGettextMessageSource.';

    /**
     * Loads the message translation for the specified language and category.
     * @param string $category the message category
     * @param string $language the target language
     * @return array the loaded messages
     */
    public function loadMessages($category, $language)
    {
        // Default catalog to langauge (e.g. de)
        // TODO: Where is catalog set (except default value)?
        $this->catalog = $language;

        $messageFile = $this->basePath.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$this->catalog;
        if ($this->useMoFile) {
                    $messageFile .= self::MO_FILE_EXT;
        } else {
                    $messageFile .= self::PO_FILE_EXT;
        }

        if ($this->cachingDuration > 0 && $this->cacheID !== false && ($cache = Yii::app()->getComponent($this->cacheID)) !== null) {
            $key = self::CACHE_KEY_PREFIX.$messageFile.".".$category;
            if (($data = $cache->get($key)) !== false) {
                            return unserialize($data);
            }
        }

        if (is_file($messageFile)) {
            if ($this->useMoFile) {
                            $file = new CGettextMoFile($this->useBigEndian);
            } else {
                            $file = new CGettextPoFile();
            }
            $messages = $file->load($messageFile, $category);
            if (isset($cache) && isset($key)) {
                $dependency = new CFileCacheDependency($messageFile);
                $cache->set($key, serialize($messages), $this->cachingDuration, $dependency);
            }
            return $messages;
        } else {
                    return array();
        }
    }
}
