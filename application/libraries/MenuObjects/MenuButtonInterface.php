<?php

namespace LimeSurvey\Menu;

/**
 * Interface descriptions here: https://www.gitit-tech.com/manual/Extra_menus_event
 */
interface MenuButtonInterface extends MenuInterface
{
    public function getButtonId();
    public function getButtonClass();
    public function getOpenInNewTab();
}
