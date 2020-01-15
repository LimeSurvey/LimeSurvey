# Yii modules for LimeSurvey


## Quick introduction
In this folder you can add Yii modules for LimeSurvey.
see: https://www.yiiframework.com/doc/guide/1.1/en/basics.module

Modules must be loaded first in internal.php. In the future, if we want to allow user to upload Yii Modules, we'll provide a way to do it by DB.

In the subfolder HelloWorld, you'll fin a minimal exemple of a Yii Module.

## Note about yii modules and admin interface
Since LimeSurvey still doesn't use a real layout system, but rather the Survey_Common_Action strategy, those modules can't be shown inside the admin interface. They can still be usefull, for echoing pure json for exemple.

In future version of limesurve, we'll use the yii layout system so we'll can use real modules for the admin interface. For now, if you want to create modules for the admin interface, see modules/admin/.
