<?php

/** This file is part of KCFinder project
  *
  *      @desc MIME type detection class
  *   @package KCFinder
  *   @version 2.51
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

class type_mime {

    public function checkFile($file, array $config) {
        if (!class_exists("finfo"))
            return "Fileinfo PECL extension is missing.";

        if (!isset($config['params']))
            return "Undefined MIME types.";

        $finfo = strlen($config['mime_magic'])
            ? new finfo(FILEINFO_MIME, $config['mime_magic'])
            : new finfo(FILEINFO_MIME);
        if (!$finfo)
            return "Opening fileinfo database failed.";

        $type = $finfo->file($file);
        $type = substr($type, 0, strrpos($type, ";"));

        $mimes = $config['params'];
        if (substr($mimes, 0, 1) == "!") {
            $mimes = trim(substr($mimes, 1));
            return in_array($type , explode(" ", $mimes))
                ? "You can't upload such files."
                : true;
        }

        return !in_array($type , explode(" ", $mimes))
            ? "You can't upload such files."
            : true;
    }
}

?>