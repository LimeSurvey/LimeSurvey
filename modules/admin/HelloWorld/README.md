# HelloWorld admin module for LimeSurvey

## Quick introduction
Here a very small example of an admin MVC module for  LimeSurvey.

This module consists in two parts: Full page module and survey page module.
The code is commented so you can easily copy / paste those files and modify them to your need.
We'll add soon a model and a table creation process.


To create a module, you must first know a bit of the Yii 1 Framework.
see: https://www.yiiframework.com/doc/guide/1.1/en


##  Full page Hello World Module


###  Global presentation

The first part of the module HelloWorld is a "full page module".
Example of this kind of modules in LimeSurvey are the Global Settings page, or the label sets page, or the user account page. They are accessible via the navigation bar, or via the boxes on the home page. When we'll provide you a module uploader, you'll can set your menu / box via the module manifest. For now, you must access it via the URL or by creating a box on the home page (see: index.php?r=admin/homepagesettings ).

You can reach this full page helloWorld module via the URL:

**index.php?r=admin/HelloWorld/**

You will see:
![Full page HelloWorld Module](https://www.limesurvey.org/images/github/full-page-hello-world-module.png)

For now, it does not require any kind of configuration.
So if you want to create your own module, just copy/paste the HelloWorld folder, rename the folder, the controller file and the controller class to something else, and it will work out immediately.

You can use any limesurvey / Yii models, helpers, functions, in it.

Soon, we'll provide you a way to manage table creation / update in it.


### Default action

Modules use the LS routing system, so you can access them via the normal routes. It means that if you provide only the name of the controller, the default action will be reached: public function index()

You can reach the default action via the URL:

**index.php?r=admin/HelloWorld/**

It will show the result of HelloWorld::index()
https://github.com/LimeSurvey/LimeSurvey/blob/bdeeb8edc4eca6d15f219bb1642e6457c46d213b/modules/admin/HelloWorld/controller/HelloWorld.php#L24-L31


### Accessing a specific action with no parameters

You can reach the action HelloWorldController::sayHello() via the url:

**index.php?r=admin/HelloWorld/sa/sayHello/**

Since you don't provide any parameter, it will use the default value "world" . So it will tell you on screen "Hello World!"

https://github.com/LimeSurvey/LimeSurvey/blob/bdeeb8edc4eca6d15f219bb1642e6457c46d213b/modules/admin/HelloWorld/controller/HelloWorld.php#L34-L48


### Accessing a specific action with URL parameters

You can reach the action HelloWorldController::sayHello() and pass it the parameter "lime" via the url:

**index.php?r=admin/HelloWorld/sa/sayHello/sWho/lime**

It will tell you "Hello Lime!"

### Note about module's views

You'll notice that the sayHello() functions consists in nothing but just returning a view and passing it the sWho parameter:

```php
// Call to Survey_Common_Action::_renderWrappedTemplate that will generate the "Layout"
$this->_renderWrappedTemplate('HelloWorld', 'index', array(
    'sWho'=>$sWho,
  ));
```

It's pretty different from the normal Yii's renderPartial. That's because LimeSurvey (sadly) doesn't use the Yii Layout system, but rather a custom layout system that is defined in Survey_Common_Action. For you to know: this has been done by the Google Team during the Google Summer Code 2014, and we hope that for LS5 we'll totally get rid of it, and we'll use a real Yii Layout (and maybe even yii3 :) )

You don't need to understand the logic in Survey_Common_Action. The only thing you need to understand is the signature of the function:
The first parameter is the name of the directory inside your module where is the view, the second one in the name of the file of the view (without ".php"), the last one is the array of variables that will be available in the view ('foo'=> "bar" : means in your view you will have a variable called "$foo" that will contain the string "var")

So the function sayHello() call the view :**modules/admin/HelloWorld/views/index.php**

By the way, this why our module extends Survey_Common_Action.


### Can I call a view outside of the module directory?

If for any reason, you want to call a view outside of the module directory, you can do it by editing the function renderCentralContents() in your module. It's advanced feature, so if you want to do it you should be able understand how it works.


## Survey page Hello World Module


The second part of the module HelloWorld is a "survey page module".
Those kind of modules are the one shown in survey edition. For example: participant settings, or quotas, etc. They are accessible via the left bar menu, or via the boxes on survey overview. When we'll provide you a module uploader, you'll can set your menu / box via the module manifest. For now, you must access it via the URL or by creating manually a menu in the left bar (see: index.php?r=admin/menuentries/sa/view ).

You can reach this full page helloWorld module via the url:

**index.php?r=admin/HelloWorld/sa/HelloWorldSurvey&surveyid=XXXXX** ( XXXX being any valid survey id. )

You will see:

![Full page HelloWorld Module](https://www.limesurvey.org/images/github/survey-page-hello-world-module.png)

As you can see, this module offers its own top menu bar (the button "Hello User"), and use the breadcrumb system of survey edition (survey title (654523) => HelloWorld). Again, this breadcrumb system is a home made one, it's not using the Yii one. Again, we hope that in LS5 we can use the Yii Layout + breadcrumb system. In this module, you'll see how the top menu button call an action in the controller, render a specific view, and how you can use the breadcrumb to navigate back to the module landing page.

### Reach the module landing page

As we've just see it, the landing page is reachable via the url:

**index.php?r=admin/HelloWorld/sa/HelloWorldSurvey&surveyid=XXXXX** ( XXXX being any valid survey id. )

So, it call the method HelloWorld::HelloWorldSurvey() :

https://github.com/LimeSurvey/LimeSurvey/blob/bdeeb8edc4eca6d15f219bb1642e6457c46d213b/modules/admin/HelloWorld/controller/HelloWorld.php#L57-L89

The function first instantiate the Survey Model:

```php
// First, we get the survey model to get some information
$oSurvey = Survey::model()->findByPk($surveyid);
```

This will provide you a typical AR instance of row "surveyid" of the table Survey, plus all the methos in Survey Model. To know more about that, see:
https://www.yiiframework.com/doc/guide/1.1/en/database.ar

Then, we build the array of data that will be parsed to the view. In LS architecture, this array of data is first parsed by Survey Common Action. It will look into it for specific data to know what to show or not in the layout.

> Switch on survey edition layout

If the array of data passed to the view contains a field "surveyid", Survey Common Helper will show the survey edition layout:

```php
// By providing a surveyid, we launch the survey "layout".
// see: https://github.com/LimeSurvey/LimeSurvey/blob/ae760dd3274a390b790c494f50826cb3a56f37c3/application/core/Survey_Common_Action.php#L328-L338
$aData['surveyid'] = $surveyid;
```

> Show the green top bar

If the array of data passed to the view contains a field "title_bar", Survey Common Helper will show the survey green top bar:

```php
// By providing a "title_bar", we the green top bar with the breadcrumb.
// see: https://github.com/LimeSurvey/LimeSurvey/blob/ae760dd3274a390b790c494f50826cb3a56f37c3/application/core/Survey_Common_Action.php#L481-L486
$aData['title_bar']['title'] = $oSurvey->currentLanguageSettings->surveyls_title." (".gT("ID").":".$surveyid.")";
```

Notice 2 things:

1. We choose what is shown inside that bar. By convention, we always show the survey title followed by the survey id. But you can pass to it what ever you want.

2. The survey title is reached via "currentLanguageSettings->surveyls_title". It's calling a the related table "surveys_languagesettings" and retrieves it's field "surveyls_title". In a simple use case, we could use the AR pattern following rules inside defined in the model. But LimeSurvey being complex, here we're calling the function Survey::getCurrentLanguageSettings(). See:
https://github.com/LimeSurvey/LimeSurvey/blob/bdeeb8edc4eca6d15f219bb1642e6457c46d213b/application/models/Survey.php#L340-L353

So, if you want to see what you could do with the current edited survey, we suggest you to have a look to the methods of the survey / question / question group / token / etc models. Most of the time, your modules will consist in writting data in the survey related tables.  

> Show the bread crumb

If the array of data passed to the view contains a field "module_subaction" inside the field "title_bar", Survey Common Helper will show the bread crumb.

```php
// By providing a module subaction, we launch the breadcrumb
$aData['title_bar']['module_subaction'] = "HelloWorld";
$aData['title_bar']['module_subaction_url'] = App()->createUrl('admin/HelloWorld/sa/HelloWorldSurvey/', ['surveyid' => $oSurvey->sid]);
```

module_subaction define the string that will be shown in the breadcrumb, and  module_subaction_url will define what URL it will lead too.

Notice we create the URL using the yii method App()->createUrl. This is important. Remember that LimeSurvey can use get url or path url, that user can configure specific url routing in their config files. So juste use this method to generate your url, and it will always fit the specificites of the installation of the user. Remember to define the parameters in a separate array (like "['surveyid' => $oSurvey->sid]") for the same reasons.

To know more about the Yii createUrl method:
https://www.yiiframework.com/doc/api/1.1/CController#createUrl-detail

>  render the view

As you can see, the function render the view:**modules/admin/HelloWorld/views/HelloWorldSurvey.php**
We define ourself the top menu HTML in our view :

```php
<div class='menubar surveybar' id="helloworldbarid">
    <div class='row container-fluid'>
        <!-- left buttons -->
        <div class="col-md-10">
          <a class="btn btn-default pjax" href='<?php echo $this->createUrl('admin/HelloWorld/sa/sayHelloUser/', ['surveyid' => $oSurvey->sid, 'sWho'=> "foo"]); ?>' role="button">
              <span class="fa  fa-smile-o text-success"></span>
                Hello user
          </a>
        </div>
    </div>
</div>
```

So you can basically do what ever you want. A top menu is a just a button calling a URL, being a route to an action in your controller. Here, the menu will call the url:
**index.php?r=admin/HelloWorld/sa/sayHelloUser&surveyid=XXXXX&sWho=foo** ( XXXX being any valid survey id. )

Notice that we use font awesome to generate the icon. For now, we're still using Font Awesome 4.7, so you can use any icon you'll find here:
https://fontawesome.com/v4.7.0/


### Reach the action "Say Hello to user"

As we've just see it, the landing page is reachable via the url:

**index.php?r=admin/HelloWorld/sa/sayHelloUser&surveyid=XXXXX&sWho=foo** ( XXXX being any valid survey id. )

So, it call the method HelloWorld::sayHelloUser() and pass it the value "foo":

https://github.com/LimeSurvey/LimeSurvey/blob/bdeeb8edc4eca6d15f219bb1642e6457c46d213b/modules/admin/HelloWorld/controller/HelloWorld.php#L92-L127

The function is very similar to HelloWorld::HelloWorldSurvey(). So will just see the differences

> Add a subaction in the breadcrumb


If the array of data passed to the view contains a field "module_current_action" inside the field "title_bar", Survey Common Helper will show an additional acction in the breadcrumb.

```php
$aData['title_bar']['module_current_action'] = 'sayHelloUser';
```

This action has no url, since it will not be clickable. You can't provide a subaction to a subaction (a top menu inside an action reached by clicking on a top menu), it would be too much.

User can go back to the module landing page by clicking on the "HelloWorld" link in the bread crumb (just launch the module if you can't figure out by reading this, or have a look at the screenshot below)


> Get the parameters from url

Inside the button "Say Hello to user!" we defined a dumb parameter "sWho" that contains the string "foo"

```php
// Our own datas for our view
$aData['sWho'] = $sWho;
```

We get if from url since it's defined in the Action signature ( public function sayHelloUser($sWho="World", $surveyid) )

Notice here that we're not filtering it and this is wrong. In web development, you can't trust user input, never. Sinced it's just echoed inside the view, someone could use this to pass some javascript to our view and perform some XSS exploit. So you should filter it as HTML by using CHtml::encode.
See: https://www.yiiframework.com/doc/api/1.1/CHtml#encode-detail

> Get the name of the logged in user and pass it to the view

Then we get the name of the current logged in user.

```php
$aData['sUserName'] = Yii::app()->user->name;
```

This is a call to CUserdentity : https://www.yiiframework.com/doc/api/1.1/CUserIdentity

If you want to perform some permission checks, just use the permission moldel static methods.


>  render the view

As you can see, the function render the view:**modules/admin/HelloWorld/views/sayHelloUser.php**
https://github.com/LimeSurvey/LimeSurvey/blob/bdeeb8edc4eca6d15f219bb1642e6457c46d213b/modules/admin/HelloWorld/views/sayHelloUser.php

It says hello to the logged in user, shows unsafely the value of the URL variable "sWho" and invite the user to go back to the module landing page by clicking on the link of the breadcrumb

![Full page HelloWorld Module](https://www.limesurvey.org/images/github/say-hello-user-hello-world-module.png)


## That's all for now !

Hope that quick HelloWorld will help you to develop custom components for the LimeSurvey admin interface. For now it's very basic, but you can already do very advanced stuff. Most of the current LimeSurvey module could be moved here now. What's really missing is a way for you to create new tables and update them, a way to provide your own tranlsation files. Remember that you can already register javascript via the normal Yii methods.

## Comming soon


Installer to read a manifest, to create menus, to create / update database tables, to add custom translation files (that will be added to limesurvey core translation files).
