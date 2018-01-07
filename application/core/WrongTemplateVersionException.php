<?php

/**
 * Thrown when template/theme can't be loaded and
 * there's a mismatch between template version in 
 * db and in config.xml
 * @todo Put in another folder?
 */
class WrongTemplateVersionException extends CException
{
}
