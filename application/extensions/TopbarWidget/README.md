We have a class for the topbar: TopbarConfiguration.php (/application/core/...). Inside this class you can set the main views:

* the main topbar view
* the data to be passed to the topbar view
* the right side view
* the left side view

There is a function in LayoutHelper.php which uses this class: renderTopbar($aData)

There is a topbar widget implemented in TopbarWidget.php (/application/extensions/TopbarWidget) which renders the topbar.

The views are stored in /application/extensions/TopbarWidget/views. Here you can find the most important view "baseTopbar_view.php".

Example (actionListQuestions() in QuestionAdministrationController):

    $aData['topBar']['name'] = 'baseTopbar_view';
    $aData['topBar']['leftSideView'] = 'listquestionsTopbarLeft_view';

This is passed in layout_questioneditor.php to the rendering function of the LayoutHelper:

    echo LayoutHelper::renderTopbar($aData);

Inside this function the topbar configuration is set and the widget is used:

```php
$oTopbarConfig = TopbarConfiguration::createFromViewData($aData);
return Yii::app()->getController()->widget(
   'ext.TopbarWidget.TopbarWidget', 
   array(
       'config' => $oTopbarConfig,
       'aData' => $aData,
   ),
   true
);
```
