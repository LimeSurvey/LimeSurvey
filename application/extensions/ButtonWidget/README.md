# Button Widget
To handle the needs of the new admin theme, we decided to introduce a widget for buttons.

With this widget you can create either normal buttons or a Bootstrap 5 dropdown-button while
being able to control icons and their position, and most important the look of the new dropdown-buttons with a divider and an additional icon.

### Options

- **text**: *string*
    
    the text displayed in the button

    ***default***: empty string

- **icon**: *string*

    name of the icon class. e.g.: fa fa-paint-brush

    ***default***: empty string

- **iconPosition**: *string*

    Position of the icon either left or right

    ***default***: 'left'

- **menu**: *bool*

    if button should behave as dropdown true or false

    ***default***: false

- **displayMenuIcon**: *bool*

    if the 'divider plus another icon' is displayed. true or false (true, if not set and 'menu' is true)

- **menuIcon**: *string*

    the icon displayed besides the divider

    ***default***: 'fa fa-ellipsis-h' //@TODO switch to new icon when icons task is done

- **link**: *string*

    link where the button points to, if link is empty "button" element is created else an "a" element

    ***default***: empty string

- **menuContent**: *string* 

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
        'icon' => 'fa fa-pencil',
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
        'icon' => 'fa fa-eye',
        'isDropDown' => true,
        'menuIcon' => 'fa fa-caret-down'
        'menuContent' => '<ul class="dropdown-menu" aria-labelledby="example2">
            <li><a class="dropdown-item" href="#">Action</a></li>
            <li><a class="dropdown-item" href="#">Another action</a></li>
            <li><a class="dropdown-item" href="#">Something else here</a></li>
        </ul>',
        'htmlOptions' => [
            'class' => 'btn btn-secondary',
        ],
    ]);
```
