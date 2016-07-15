# Massive Action Widget

 This widget is designed to be used with grid views. For now, in LimeSurvey core, it is used in the footer of the grids.
 It generates a dropup button with massive actions, and the modal associated to each action (if needed).

 When using it, you should defined each element of the list, and the modal associated to it (if needed).

## Usage Example:

### Widget:
```php
    $this->widget('ext.admin.grid.MassiveActionsWidget.MassiveActionsWidget', array(
        'pk'          => 'id',                                                  // The primary key identifier in the grid (for checkboxes)
        'gridid'      => 'question-grid',                                       // The grid id
        'dropupId'    => 'muhListActions',                                      // The dropup button id (optional)
        'dropUpText'  => gT('Selected item(s)...'),                         // The dropup text button

        // The array containing the of actions and modals definition
        'aActions'    => array(                                                   
            ...
        ),
```

This will generate a dropup button of this kind :
```html
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
      Selected items(s)...    <span class="caret"></span>
    </button>
    <ul class="dropdown-menu listActions" aria-labelledby="muhListActions">
        ...
    </ul>    
```

### Array of actions:
The array of actions and modals can accept different types of items :

- **separator**: generates a separator in the dropup list
- **dropdown-header**: generates a header un the dropup list
- **action**: generates an action link, and the modal associated to it (if needed).

```php
    'aActions'    => array(
        // Separator
        array(
            'type'  => 'separator',
        ),

        // Header
        array(
            'type' => 'dropdown-header',
            'text' => "Muh Header",
        ),

        array(
            'type' => 'action',
            ...
        ),        
    ),
```

This will generate a dropup button of this kind :
```html
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
      Selected items(s)...    <span class="caret"></span>
    </button>
    <ul class="dropdown-menu listActions" aria-labelledby="muhListActions">
        <!-- Separator -->
        <li role="separator" class="divider"></li>        

        <!-- Header -->
        <li class="dropdown-header">Muh Header</li>
        <!-- Action -->
        ....
    </ul>    
```

### Actions:
The action definition consists in two parts: defining the link in the dropup list and the modal (if needed).
The link, to be defined, need:

- a text for the Actions (eg: *Set muh value*)
- a classes for the icon in front of the text (eg: *fa fa-muh-icon*)
- the url of the action to apply (for redirection, or ajax request. eg: */admin/muhController/sa/setMultipleMuhValue/*)
- the action type (redirect, fill sessions and redirect, or modal).

There is currently 3 action types (they are the result of the refactorisation of the old jQgrid massive actions) :

- **redirect** : when clicking on the action link, user will be redirected to the wanted url in a blank windows. The list of the checked items will be posted in a string separated by |. This is used only for tokens right now (send email...).
- **fill-session-and-redirect** : basically the same than redirect, but calling first an action on a controller to fill the session with the checked items before redirecting. This is used only for tokens "add participant to CPDB" for now.
- **modal** : This is the most used case. It raises a modal to first confirm the action, then submit an ajax request to the defined url, and closes it OR shows an array of results.

```php
// Exemple of action
array(
    // li element
    'type'        => 'action',                                                        
    'action'      => 'set-muhvalue',
    'url'         => App()->createUrl('/admin/muhController/sa/setMultipleMuhValue/'),     // The url to reach the action method
    'iconClasses' => 'fa fa-muh-icon',                                                  // The class to define the icon that will be show next to the action link in the dropUp button
    'text'        => gT('Set muh value'),                                               // The text of the action link in the dropUp button

    // modal
    'actionType'    => 'modal',                                                         // the action type
    ...
),
```


This will generate a dropup button of this kind :
```html
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
      Selected items(s)...    <span class="caret"></span>
    </button>
    <ul class="dropdown-menu listActions" aria-labelledby="muhListActions">
        <!-- Action -->
        <li>
            <a href="#"
                data-url="/index.php//admin/muhController/sa/setMultipleMuhValue/"
                data-action="set-muhvalue"
                data-type="modal"
                ...
            >
                <span class="ui-icon fa fa-muh-icon"></span>
                Set muh value
            </a>
        </li>
        ...
    </ul>    
```

You can also add some data attributes in the link, if you need it for custom JavaScript. Just add an array 'aLinkSpecificDatas' to your action. In LS, it's used only for the token grid, for the redirect action type.

```php
// Exemple of action with some custom data attribute in the link
array(
    // li element
    'type'        => 'action',                                                        
    ...

    'aLinkSpecificDatas'  => array(
        'specific-data'         => 'specific-data-value',
        'another-specific-data' => 'another-specific-data-value',
    ),

),
```

This will generate a dropup button of this kind :
```html
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
      Selected items(s)...    <span class="caret"></span>
    </button>
    <ul class="dropdown-menu listActions" aria-labelledby="muhListActions">
        <!-- Action -->
        <li>
            <a href="#"
                data-url="/index.php//admin/muhController/sa/setMultipleMuhValue/"
                data-action="set-muhvalue"
                data-type="modal"
                data-specific-data = "specific-data-value"
                data-another-specific-data = "another-specific-data-value"
            >
                <span class="ui-icon fa fa-muh-icon"></span>
                Set muh value
            </a>
        </li>
    </ul>    
```

#### Action type "modal":
The modal action is complex, and accept various parameters.

First, the modal title and its html body should be specified. Then  a modal type should be defined. It correponds to a view in the widget modals/ directory. For now, only one type is available : yes-no. The yes-no modal accepts parameters to change the text for "yes" and "no" (e.g: to show "apply" and "cancel".)

```php
// modal
'actionType'    => 'modal',         
'sModalTitle'   => 'Muh Title',     // The title of the modal
'htmlModalBody' => 'Are you sure?', // The modal text
'modalType'     => 'yes-no',        // The type of the modal (the view to use)
'yes'           => gT('apply'),     // Text replacement for yes
'no'            => gT('cancel'),    // Text replacement fo no
'keepopen'      => 'no',            // Should the modal stayed open after the ajax request?
'grid-reload'   => 'yes',           // Should the grid be reloaded after the ajax request?
```

The modal accepts a parameter **grid-reload** to define if the grid should be reloaded after the ajax request (eg: if you deleted some items, the grid will be reloaded so the deleted items will not be shown anymore).

The modals accepts a parameter **keepopen**. If it's set to true, the modal will remain opened after the ajax request, and its content will be updated to show the HTML returned by the controller. For now, in LS, this behavior is used only for survey list (export) and for question deletion (a question deletion can failed if conditions depends on it, so a result table must be shown). Of course, this should be used for all the actions, because users love feedbacks, and that's a TODO task for LS.

### Form in modal:
Of course, the main interest of using a modal after clicking an action is to show a form so the user can set some values. This form, with its values, will be parsed to 'htmlModalBody'. For readability, in LS, this is done using a renderPartial :

```php
// modal
'actionType'    => 'modal',         
...
'htmlModalBody' =>  $this->renderPartial('my_view.php', array(...), true),
```

The form will not be posted to the url directly by the ajax request. Indeed, the listActions.js script will build its own post by aggregating the checked items and the data from inputs in the modals having the class "custom-data".

*my_view.php:*
```html
<!-- The form itself is optional-->
<form class="custom-modal-datas">                                               
    <div class="form-group">
        <label class="col-sm-4 control-label"><?php eT("Muh Value:"); ?></label>
        <div class="col-sm-8">
            <!-- Thoses input have the class "custom-data", they will be posted by the ajax request -->
            <input type="text" class="form-control custom-data" id="muhvalue" name="muhvalue" value="">         
            <input type="hidden" name="sid" value="<?php echo $_GET['surveyid']; ?>" class="custom-data"/>
        </div>

        <!-- This input doesn't have the class "custom-data", it will NOT be posted by the ajax request -->
        <input type="useless" name="useless" id="useless" value="useless" />
    </div>
</form>
```

## Special cases with special classes:
To fit LimeSurvey specifities, two special cases has been added to make the code dryer:
- A special case for defining question attributes (adding a class "attributes-to-update" to a custom-data)
- A special case for BootstrapSwitches ( to manage its value and reload behaviour)

### Question attributes
Instead of using a custom method for each set of question attributes to update (like setMultipleStatisticsOptions to set public_statistics, statistics_showgraph, statistics_graphtype), all question attribute editing can call the same method : question::setMultipleAttributes()

Then, in the modal form, the list of a attributes to set should not only have the class "custom-data" but also the class "attributes-to-update".
See Questions massive actions for detailed example.

### BootstrapSwitches
Bootstrap switches are often used in forms. They needed to be reloaded when the grid is updated. This has been automatized in the widget.
Also, Bootstrap switches always provides a boolean value {true, false}, whereas sometimes an integer {1,0} or a string {Y,N} can be necessary. Usually, the value is converted on the action side. But to preserver the unity and simplicity of question::setMultipleAttributes, this can be done by the listActions.js script.
To reload automatically the bootrstrap switches on grid reload, and/or convert its values, add to the switch one of the classes bootstrap-switch-boolean or bootstrap-switch-integer.
See Questions massive actions for detailed example.


## More...
The code of the widget itself, and of its scripts, is widely commented and can complete this short documentation.
