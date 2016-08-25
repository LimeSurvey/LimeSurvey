# List Survey Widget

 This widget display the survey list, its search box and its footer.
 It has various parameters:

 - $model            : the survey search model
 - $bRenderFooter    : set if the footer should the footer be rendered
 - $bRenderSearchBox : set if the search boxes should be rendered
 - $formUrl          : url of the action for the search action (default: admin/survey/sa/listsurveys/)

## Usage Example:

```php
$this->widget('ext.admin.survey.ListSurveysWidget.ListSurveysWidget', array(                        
            'model'            => new Survey('search'),
            'bRenderFooter'    => false,
            'bRenderSearchBox' => false,
        ));
```
