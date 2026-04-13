<?php
/*
* LimeSurvey
* Copyright (C) 2007-2023 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Extends CGettextMoFile to make it compatible with PHP 8.1
 */
class LSGettextMoFile extends CGettextMoFile
{
	/**
	 * @inheritdoc
	 */
	protected function readString($fr, $length, $offset = null)
	{
		if($offset !== null) {
			fseek($fr, $offset);
        }
		return (string) $this->readByte($fr, $length);
	}
}
