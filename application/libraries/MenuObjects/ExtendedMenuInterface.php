<?php

namespace LimeSurvey\Menu;

interface ExtendedMenuInterface extends MenuMinimalInterface
{
    public function getId();
    public function isDropDownButton();
    public function getDropDownButtonClass();
    public function getIconClass();
    public function getOnClick();
    public function getTooltip();
    public function isInMiddleSection();
    public function isPrepended();
    public function isButton();
}