<?php

namespace LimeSurvey;

/**
 * Extends ZipArchive class to add a check for Zip Bombing
 */
class Zip extends \ZipArchive
{
    protected $opened = false;

    /**
     * @inheritdoc
     * @param bool $checkZipBomb If true, check for Zip Bombing
     */
    public function open($filename, $flags = null, $checkZipBomb = true)
    {
        $result = parent::open($filename, $flags);
        $this->opened = ($result === true);
        if ($result === true && $checkZipBomb && $this->isZipBomb()) {
            /**
             * @todo: Throw an exception instead of die? Currently using die() to keep it consistent with the LimeSurvey's PclZip behaviour.
             */
            die('Abort ' . basename(__FILE__) . ' : Unzipped file is bigger than upload_max_filesize or post_max_size');
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        $result = parent::close();
        $this->opened = false;
        return $result;
    }

    /**
     * Check if the zip archive is a Zip Bomb
     * @return bool
     */
    public function isZipBomb()
    {
        if (!$this->opened) {
            return false;
        }
        $totalSize = 0;
        for ($i = 0; $i < $this->numFiles; $i++) {
            $fileStats = $this->statIndex($i);
            $totalSize += $fileStats['size'];
        }
        return ($totalSize > \Yii::app()->getConfig('maximum_unzipped_size'));
    }
}