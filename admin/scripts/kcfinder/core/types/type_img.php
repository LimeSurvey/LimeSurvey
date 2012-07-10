<?php

/** This file is part of KCFinder project
  *
  *      @desc GD image detection class
  *   @package KCFinder
  *   @version 2.21
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

class type_img {

    public function checkFile($file, array $config) {
        $gd = new gd($file);
        if ($gd->init_error)
            return "Unknown image format/encoding.";
        return true;
    }
}

?>