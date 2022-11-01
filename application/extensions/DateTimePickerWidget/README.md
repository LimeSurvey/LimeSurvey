LS DateTimePicker Widget
==========

This widget is based on the [Tempus Dominus datepicker](https://getdatepicker.com/) which attempts to be the successor of the
eonasdan/bootstrap-datetimepicker.

As of now we use v6.0.0-beta7 as it is semi stable and supports Bootstrap 5.
Some buggy behaviors are fixed here, though.

## Use PHP widget
The widget works similar to the old datepicker widget.

```PHP
Yii::app()->getController()->widget('ext.DateTimePickerWidget.DateTimePicker', array(
                    'name' => 'expires',
                    'id' => 'expires',
                    'value' => $oUser->expires ? date(
                        $dateformatdetails['phpdate'] . " H:i",
                        strtotime($oUser->expires)
                    ) : '',
                    'pluginOptions' => [
                        'format' => $dateformatdetails['jsdate'] . " HH:mm",
                        'allowInputToggle' => true,
                        'showClear' => true,
                        'locale' => convertLStoDateTimePickerLocale(Yii::app()->session['adminlang'])
                    ]
                ));
```
Possible plugin options can be seen in docblock of
```PHP
public $pluginOptions = array();
```
in class DateTimePicker.
We just introduced those which are needed at the moment.

## Initialize datetimepicker via JS
### A) When widget was also used

In some cases, for example when the PHP code is updated with ajax, you need to reinitialize the picker on client side.
When the form was created using the widget, all the plugin options are in the datepicker input field as data attributes.
initDatePicker() analyzes those attributes and uses them for reinitialization.
You just have to pass the input element to the function:
```JS
initDatePicker(document.getElementById('expires'));
```

### B) When created from scratch
When the datepicker needs to be created from scratch, you should put plugin options you want to use in the input as data attributes: 
```HTML
<input
    class="YesNoDatePicker form-control"
    id="massedit_sent-date"
    type="text"
    value="<?php echo date($dateformatdetails['phpdate']); ?>"
    name="sent-date"
    data-locale="<?php echo $locale ?>"
    data-format="<?php echo $dateformatdetails['jsdate']; ?> HH:mm"
    data-showclear="1"
>
```
```JS
initDatePicker(document.getElementById('massedit_sent-date'));
```

Or you should at least pass locale and dateformat to the function.
The values in the example are also taken as default, if you don't pass them directly or as data attribute:
```JS
initDatePicker(inputElement, 'en', 'YYYY-MM-DD');
```
