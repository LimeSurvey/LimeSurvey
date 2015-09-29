<?php

/**
 * Class ClassFactory
 * Constructs classes dynamically to support multiple tables per model in Yii AR
 */
class ClassFactory {

	protected static $map = [];
	protected static $registered = false;

	public static function autoload($className)
	{
		foreach(self::$map as $prefix => $baseClass)
		{
			if (strpos($className, $prefix) === 0)
			{
				self::createClass($baseClass, $className);
				return true;
			}
		}
		return false;
	}

	public static function createClass($baseClass, $className)
	{
        if (false !== $pos = strrpos($className, '\\')) {
            $namespace = substr($className, 0, $pos);
            $className = substr($className, $pos + 1);
        } else {
            $namespace = '';
        }

		$code = "namespace {$namespace} { class $className extends \\$baseClass {} }";
		return eval($code);
	}

	public static function registerClass($prefix, $baseClass)
	{
		self::$map[$prefix] = $baseClass;
		if (!self::$registered)
		{
			self::$registered = spl_autoload_register(array(get_class(), 'autoload'), true, true);
		}
	}
}
