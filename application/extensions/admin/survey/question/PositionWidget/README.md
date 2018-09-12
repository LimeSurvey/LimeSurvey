# Question Position Widget

 This widget display a position selection for a question inside a group. It's used for now in "add new question".
 It has two modes :

 - static mode (display = form_group) : just render a select with "At end", "At Bengining", "After question..." for each question of the group
   this mode imply that the group can't be change in the same view that the widget

 - ajax mode (display = ajax_form_group): In ajax mode, the widget will first render an hidden input where is called.
    This hidden input contains the necessary datas to generate a static position selector.
    Then it will register some js, that will insert after this hidden input a static position selector based on the datas of the hidden input.
    If the question group selector change, it will update the datas of the hidden input, and regenerate a new position selector


## Usage Example:

```php
$this->widget('ext.admin.survey.question.PositionWidget.PositionWidget', array(
            'display'               => 'ajax_form_group',
            'oQuestionGroup'        => $oQuestionGroup,
            'reloadAction'          => 'admin/questions/sa/ajaxReloadPositionWidget',
            'dataGroupSelectorId'   => $gid,
    ));
```


## Paramaters

| Parameter  |  accepted value | default value | comment |
| ---------  | --------------- | ------------- | ------- |
| `display`  |  form_group/ajax_form_group | form_group | What kind of rendering to use. For now, only form_group, to display a static one inside right menu, or  to display a dynamic one |
| `oQuestionGroup` | Question Group Model instance | none |The question group the position is related to |
| `reloadAction` | string | admin/questions/sa/ajaxReloadPositionWidget |  In ajax mode, name of the controller/action to call to get the HTML of the static widget. Update this value if you want to use the widget outside of the Questions controller (that should never happen, and if it happens, then it would be better to update this widget to a Yii module) |
| `dataGroupSelectorId` | string | 'gid' |  The id of the question group selector to watch |
