<?php

/**
 * Creates a button based on given options to fit the admin theme design.
 */
class ButtonWidget extends CInputWidget
{
    /** @var string the text displayed in the button */
    public $text = '';

    /** @var string name of the icon class. e.g.: fa fa-paint-brush */
    public $icon = '';

    /** @var string Position of the icon either left or right */
    public $iconPosition = 'left';

    /** @var bool if button should behave as dropdown true or false */
    public $isDropDown = false;

    /** @var bool if the 'divider plus another icon' is displayed.
     * true or false (true, if not set and 'isDropDown' is true)
     */
    public $displayMenuIcon;

    /** @var string the icon displayed besides the divider, default is  */
    public $dropDownIcon = 'fa fa-ellipsis-h'; //@TODO switch to new icon when icons task is done

    /** @var string link where the button points to, if link is empty
     * an <button> element is created else an <a> element
     */
    public $link = '';

    /** @var string string that should contain valid html list for bootstrap dropdown button.
     * Only used when not empty and isDropDown is true
     */
    public $menuContent = ''; //todo rename this one to $dropDownContent

    /** @var array html options */
    public $htmlOptions = [];

    /** Initializes the widget */
    public function init()
    {
        $this->registerClientScript();
        $this->setDefaultOptions();
    }

    /** Executes the widget */
    public function run()
    {
        $this->renderButton();
    }

    /** Renders the button */
    public function renderButton()
    {
        list($name, $id) = $this->resolveNameID();
        $this->render('button', [
            'name' => $name,
            'id' => $id,
            'text' => $this->text,
            'icon' => $this->icon,
            'iconPosition' => $this->iconPosition,
            'menu' => $this->isDropDown,
            'displayMenuIcon' => $this->displayMenuIcon,
            'menuIcon' => $this->dropDownIcon,
            'menuContent' => $this->menuContent,
            'link' => $this->link,
            'htmlOptions' => $this->htmlOptions
        ]);
    }


    /** Registers required script files */
    public function registerClientScript()
    {
    }

    /**
     * Analyzes given parameters and htmlOptions and sets default values when options are not given
     */
    private function setDefaultOptions()
    {
        if ($this->displayMenuIcon === null && $this->isDropDown === true) {
            $this->displayMenuIcon = true;
        } elseif ($this->displayMenuIcon !== true) {
            $this->displayMenuIcon = false;
        }
        if (!array_key_exists('class', $this->htmlOptions)) {
            $this->htmlOptions['class'] = 'btn btn-primary';
        }
        if ($this->isDropDown) {
            $this->htmlOptions['data-bs-toggle'] = 'dropdown';
            $this->htmlOptions['aria-haspopup'] = 'true';
            $this->htmlOptions['aria-expanded'] = 'false';
            if (!$this->displayMenuIcon) {
                $this->htmlOptions['class'] .= ' dropdown-toggle';
            }
        }
        if (!array_key_exists('name', $this->htmlOptions)) {
            $this->htmlOptions['name'] = $this->name;
        }
        if (!array_key_exists('id', $this->htmlOptions)) {
            $this->htmlOptions['id'] = $this->id;
        }
    }
}

