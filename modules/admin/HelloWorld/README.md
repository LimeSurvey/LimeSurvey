# HelloWorld admin module for LimeSurvey


## Quick introduction
Here a very small exemple of an admion module for  LimeSurvey.

## Notice

This module is very basic. It will just show you an admin page saying Hello World.
It's not a survey admin module. It can't be shown inside the survey edition.
It does not generate the menu in configuration menu. That will come later with the manifest and the module uploader

It does not requier any kind of configuration.
So if you want to create your own module, just copy/paste the HelloWorld folder, rename the folder, the controller file and the controller class to something else, and it will work out immediately.

You can use any limesurvey / Yii models, helpers, functions, in it.


## Reach the module


### Default action

You can reach the default action via the url:
```
index.php?r=admin/HelloWorld/
```

It will shows the result of HelloWorld::index()


### Other actions

You can reach the action HelloWorldController::sayHello() via the url:

```
index.php?r=admin/HelloWorld/sa/sayHello/
```

It will tell you "Hello World!"

```
index.php?r=admin/HelloWorld/sa/sayHello/sWho/lime
```

It will tell you "Hello Lime!"


## Comming soon

Installer to read a manifest, to create menus, to create / update database tables, to add custom translation files (that will be added to limesurvey core translation files)
