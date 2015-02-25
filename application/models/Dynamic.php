<?php
	/**
	 * This class implements the basis for dynamic models.
	 * In this implementation class definitions are generated dynamically.
	 * This class and its descendants should be declared abstract!
	 */
	abstract class Dynamic extends LSActiveRecord
	{
		/**
         * Prefixed with _ to not collide with column names.
		 * @var int The dynamic part of the class name.
         *
		 */
		protected $dynamicId;

		public function __construct($scenario = 'insert') {
            list(,$this->dynamicId)=explode('_', get_class($this));
            parent::__construct($scenario);
		}

		/**
		 *
		 * @param type $className
		 * @return Dynamic
		 */

		public static function model($className = null) {
			if (!isset($className)) {
				$className =  get_called_class();
			} elseif (is_numeric($className)) {
				$className = get_called_class() . '_' . $className;
			}
            if ($className == 'Response') {
                throw new Exception('noo');
            }
			return parent::model($className);
		}

        public static function create($id, $scenario = 'insert')
		{
			$className = get_called_class() . '_' . $id;
			return new $className($scenario);
		}
        
        /**
         * This function checks if a table with the specified $id can be opened.
         * @param int $id
         * @return boolean Returns true if the table is found.
         */
        public static function valid($id) 
        {
            $result = false;
            if (is_numeric($id)) {
                try {
                    static::model($id);
                } catch (\CDbException $e) {
                    $result = false;
                }
            }
            return $result;
        }

	}

?>
