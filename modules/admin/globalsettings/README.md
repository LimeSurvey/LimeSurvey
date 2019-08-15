# GlobalSettings admin module for LimeSurvey

## Quick introduction
Comming with LS4, it's now possible for 3rd party developpers to extends LimeSurvey controllers, and to override their views and subviews.
Here a very small example to show how to proceed.

## Directories, namespace and class name

### The core file name determines the directory name

You can extend any of the controller in the admin directory:
https://github.com/LimeSurvey/LimeSurvey/tree/85cc7c52479addbffb6a857ddc7aa10f52b0b02c/application/controllers/admin

For this example, we choose to extend GlobalSettings controller:
https://github.com/LimeSurvey/LimeSurvey/blob/85cc7c52479addbffb6a857ddc7aa10f52b0b02c/application/controllers/admin/globalsettings.php

Since the name of the file is **globalsettings.php**, we must create a directory **modules/admin/globalsettings/**. Respecting case of original file is important.
Note that LimeSurvey files are not normalized (sometime upper case, sometime lower case, sometime using dash, sometime camel cased, etc ). If we have time, we'll normalize the names before we release LS 4.0.0.

Then, inside **modules/admin/globalsettings/controller/** we create a file with the exact same name as the core file: **globalsettings.php**

### The directory path determines the name space

LimeSurvey is based on yii1, and yii1 doesn't really use name space, but rather aliases for path. This is for historical reasons: PHP prior to 5.3.0 does not support namespace intrinsically. So yii1 rather uses a prefix for all their core classes, and uses path aliases extensively. But, for those who want to use namespace, all the Yii import methods accepts path, alias, or namespace. So ideally, in yii1, it must be possible to easely translate your namespace to aliase so we can easely translate your namespace to aliases. To be clear:

So first, in **modules/admin/globalsettings/controller/globalsettings.php** we define a namespace:

``php namespace lsadminmodules\globalsettings\controller; ``


lsadminmodules is the Yii alias for the directory **modules/admin/**. Then **lsadminmodules\globalsettings\controller** is the namespace attached to the path **modules/admin/globalsettings/controller/**, which is indeed the path of our controller so everything is fine :)

To learn more about the relations between path, aliases and namespace in yii1:
https://www.yiiframework.com/doc/guide/1.1/en/basics.namespace

To see examples why we  need to convert your name space to aliases:
https://github.com/LimeSurvey/LimeSurvey/blob/85cc7c52479addbffb6a857ddc7aa10f52b0b02c/application/controllers/AdminController.php#L176-L179
https://github.com/LimeSurvey/LimeSurvey/blob/85cc7c52479addbffb6a857ddc7aa10f52b0b02c/application/controllers/AdminController.php#L208-L213



### The class name

We give to our controller the same class name as the core controller. It extends the core controller:
``php class GlobalSettings extends \GlobalSettings``

notice the backslash in front of the second GlobalSettings: it means that we extend the GlobalSetting class from the global namespace, so from core.

Indeed, Yii1 core classes, like LimeSurvey core classes, are under the global PHP name space: **/**. Since our module use a namespace. As a consequence, we'll have to use the global name space in front of all the calls to Yii or LS classes. Eg:
``php \Yii::getPathOfAlias();``
instead of:
``php Yii::getPathOfAlias();``


## Adding new method to the GlobalSettings controller

Now, any method you'll add to your module will be accessible as if it was part of the core controller.
We added a very simple HelloWorld method that will display the content of the HelloWorld view.
You can reach it via: index.php?r=admin/globalsettings/sa/HelloWorld/
