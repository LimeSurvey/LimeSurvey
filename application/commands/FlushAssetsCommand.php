<?php

/*
 * LimeSurvey (tm)
 * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */
class FlushAssetsCommand extends CConsoleCommand
{

    /**
     * @param array $args
     * @return void
     */
    public function run($args)
    {
        $sCurrentDir = dirname(__FILE__);
        $tmpFolder = realpath($sCurrentDir . '/../../tmp/');
        if ($tmpFolder === false) {
            echo 'Tmp folder  ' . $sCurrentDir . '/../../tmp/ not found';
            return;
        }
        echo "Flushing assets in " . $tmpFolder;
        echo "\n";

        $this->_sureRemoveFiles($tmpFolder . '/assets/', false, ['index.html']);
        $this->_sureRemoveFiles($tmpFolder . '/runtime/cache/', false, ['index.html']);
    }
    private function _sureRemoveFiles($dir, $DeleteMe, $exclude = array())
    {
        if (!$dh = @opendir($dir)) {
            return;
        }
        while (false !== ($obj = readdir($dh))) {
            if ($obj == '.' || $obj == '..' || in_array($obj, $exclude)) {
                continue;
            }
            if (!@unlink($dir . '/' . $obj)) {
                $this->_sureRemoveFiles($dir . '/' . $obj, true);
            }
        }
        closedir($dh);
        if ($DeleteMe) {
            if (!@rmdir($dir)) {
                echo "Error: could not delete " . $dir;
            }
        }
    }
}
