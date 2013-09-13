<?php

	class ClassFactory {

		protected static $map = array();
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
			$code = "class $className extends $baseClass {}";
			return eval($code);
		}

		public static function registerClass($prefix, $baseClass)
		{
			self::$map[$prefix] = $baseClass;
			// Keep the array "reverse"-sorted by keys.
			uksort(self::$map, function($a, $b) { return strcmp($a, $b) * -1;} );

			if (!self::$registered)
			{
				self::$registered = spl_autoload_register(array(get_class(), 'autoload'), true, true);
			}
		}
	}
?>