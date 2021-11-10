<?php

namespace LimeSurvey\Menu;

/**
 * Interface descriptions here: https://manual.limesurvey.org/Extra_menus_event
 */
interface MenuInterface
{
    public function isDropDown();
    public function getLabel();
    public function getHref();
    public function getMenuItems();
}
