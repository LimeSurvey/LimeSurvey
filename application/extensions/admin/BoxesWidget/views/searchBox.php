<?php
/*
* LimeSurvey
* Copyright (C) 2007-2016 The LimeSurvey Project Team / Carsten Schmitz
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/**
 * @var $this ListSurveysWidget
 */
?>

<!-- Search Box -->

<!-- Begin Form -->
<div class="container">
    <div class="menu col-12">
        <?php $form = $this->beginWidget('CActiveForm', [ 'method' => 'get', 'htmlOptions' => ['id' => 'survey-search'],]); ?>
        <?php $state = App()->request->getQuery('state'); ?>
        <div class="row">

            <!-- select state -->
            <div class="col-4">
                <h2><?php eT('All surveys'); ?></h2>
            </div>
            <div class="col-7">
                <div class="dropdown pull-right">
                    <select name="active" id='survey_active' class="form-select">
                        <option value="" >
                            <?= gT('Status') ?>
                        </option>
                        <option value="Y" <?php echo $state == 'Y' ? 'selected' : ''?>>
                            <?= gT('Active') ?>
                        </option>
                        <option value="N" <?php echo $state == 'N' ? 'selected' : ''?>>
                            <?= gT('Inactive') ?>
                        </option>
                    </select>
                </div>
            </div>
            <div class="col-1">
                <div class="pull-right menu-icon">
                    <i class="ri-grid-fill purple"></i>
                    <i class="ri-menu-line"></i>
                </div>
            </div>

        </div>
        <?php $this->endWidget(); ?>
    </div>
</div>
<style>
    .menu {
        --main-color: #8046F5;
        --main-border-color: #6D748C;
        padding: 16px 12px;
    }
    .dropdown, .search-bar{
        display: inline-block;
    }
    .dropdown select, .search-bar input, .menu-button {
        color: var(--main-border-color);
        margin-left: 6px;
        border: 2px solid var(--main-border-color) !important;
        border-radius: 5px;
    }
    .menu-button:hover {
        box-shadow: none;
    }
    .menu-icon i {
        display: inline-block;
        vertical-align: middle ;
        padding-left: 10px;
    }
    .menu-icon i:before {
        font-size: 1.4em;
        opacity: 0.8;

    }
    h2, .menu-icon {
        height: 100%;
        display: flex;
        align-items: center;
        padding: 0 8px;
    }
    .search-bar {
        position: relative;
    }
    .search-bar i {
        position: absolute;
        top: 8px;
        right: 5px;
        font-size: 1.2em;
        color: var(--main-border-color);
    }
    .b-none {
        border: 0 !important;
        box-shadow: none;
    }
    .purple {
        color: var(--main-color);
    }
    .purple-bg {
        border-color: var(--main-color) !important;
    }
    .search-bar i, .menu-icon {
        cursor: pointer;
    }

</style>
