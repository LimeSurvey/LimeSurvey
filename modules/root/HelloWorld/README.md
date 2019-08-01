# HelloWorld Yii modules for LimeSurvey


## Quick introduction
Here a very small exemple of a Yii Module working in LimeSurvey.
If you want to learn more about Yii Modules, see:
see: https://www.yiiframework.com/doc/guide/1.1/en/basics.module

## Notice

The HelloWorld Modules has been be loaded first in internal.php. In the future, if we want to allow user to upload Yii Modules, we'll provide a way to do it by DB.

## Reach the module

### Default action

You can reach the default action via the url:
```
index.php?r=HelloWorld
```

It will shows the result of HelloWorldController::actionIndex()


### Other actions

You can reach the action HelloWorldController::actionHelloAdmin() via the url:

```
index.php?r=HelloWorld/HelloWorld/HelloAdmin
```
Notice it will tell you "Hello Super Admin" only and only if your logged in.
