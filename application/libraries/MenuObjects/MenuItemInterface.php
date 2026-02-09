<?php

namespace LimeSurvey\Menu;

/**
 * Interface descriptions here: https://www.limesurvey.org/manual/Extra_menus_event
 */
interface MenuItemInterface
{
    public function getHref();
    public function getLabel();
    public function getIconClass();
    public function isDivider();
    public function isSmallText();
    public function isModal();
    public function getModalId();
    public function getItemClass();
}
