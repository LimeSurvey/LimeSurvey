<?php

namespace LimeSurvey\Menu;

/**
 * Interface descriptions here: https://www.limesurvey.org/manual/Extra_menus_event
 */
interface MenuMinimalInterface
{
    public function isDropDown();
    public function getLabel();
    public function getHref();
    public function getMenuItems();
}
