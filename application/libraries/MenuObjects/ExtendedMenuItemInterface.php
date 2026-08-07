<?php

namespace LimeSurvey\Libraries\MenuObjects;

use LimeSurvey\Menu\MenuItemInterface;

/**
 * Interface descriptions here: https://www.limesurvey.org/manual/Extra_menus_event
 */
interface ExtendedMenuItemInterface extends MenuItemInterface
{
    public function isModal();
    public function getModalId();
    public function getItemClass();
}
