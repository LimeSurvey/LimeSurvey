# Button Widget
To handle the needs of the new admin theme, we decided to introduce a widget for buttons.

With this widget you can create either normal buttons or a Bootstrap 5 dropdown-button while
being able to control icons and their position, and most important the look of the new dropdown-buttons with a divider and an additional icon.

### Options

- **text**: *string*
    
    the text displayed in the button

    ***default***: empty string

- **icon**: *string*

    name of the icon class. e.g.: ri-eye-fill

    ***default***: empty string

- **iconPosition**: *string*

    Position of the icon either left or right

    ***default***: 'left'

- **isDropDown**: *bool*

    if button should behave as dropdown true or false

    ***default***: false

- **displayDropDownIcon**: *bool*

    if the 'divider plus another icon' is displayed. true or false (true, if not set and 'menu' is true)

- **dropDownIcon**: *string*

    the icon displayed besides the divider

    ***default***: 'ri-more-fill'

- **link**: *string*

    link where the button points to, if link is empty "button" element is created else an "a" element

    ***default***: empty string

- **dropDownContent**: *string* 

    string that should contain a valid html list for bootstrap dropdown button. Only used when not empty and $menu is true

    ***default***: empty string

- **htmlOptions**: *array*

    array of html options as used in other widgets and yii components

    ***default***: empty array

### Examples

```PHP
// Simple button with an icon positioned on the right side from the button text
$this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => '',
        'id' => 'example1',
        'text' => 'This is the button text',
        'icon' => 'ri-pencil-fill',
        'iconPosition' => 'right'
        'link' => 'https://www.limesurvey.org',
        'htmlOptions' => [
            'class' => 'btn btn-primary',
            'target' => '_blank',
        ],
    ]);

// Dropdown button with a left positioned icon and a divider followed by a caret
    $this->widget('ext.ButtonWidget.ButtonWidget', [
        'name' => 'dropdown-button-example',
        'id' => 'example2',
        'text' => 'Another dropdown button',
        'icon' => 'ri-eye-fill',
        'isDropDown' => true,
        'dropDownIcon' => 'ri-arrow-down-s-fill'
        'dropDownContent' => '<ul class="dropdown-menu" aria-labelledby="example2">
            <li><a class="dropdown-item" href="#">Action</a></li>
            <li><a class="dropdown-item" href="#">Another action</a></li>
            <li><a class="dropdown-item" href="#">Something else here</a></li>
        </ul>',
        'htmlOptions' => [
            'class' => 'btn btn-secondary',
        ],
    ]);
```
