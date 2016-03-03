<?php
// Trigger autoloading of YiiBase. This is a class file with side effects so that's why we need it.
class_exists(\Yii::class);
// Unregister yiis autoloader.
spl_autoload_unregister(['YiiBase','autoload']);
