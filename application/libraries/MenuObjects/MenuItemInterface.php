<?php

namespace LimeSurvey\Menu;

/**
 * Interface descriptions here: https://www.gitit-tech.com/manual/Extra_menus_event
 */
interface MenuItemInterface
{
    public function getHref();
    public function getLabel();
    public function getIconClass();
    public function isDivider();
    public function isSmallText();
}
