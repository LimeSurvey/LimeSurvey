<?php
/**
 * List DropDown Option group header Html
 * @var $sOptGroupOptions   the current options for this group, generated with the view rows/options.php
 *
 * @var $categoryname
 */
?>

<!-- optgroup -->
<optgroup class="dropdowncategory" label="<?php echo $categoryname;?>">
    <?php
        // rows/option.php
        echo LimeExpressionManager::ProcessStepString($sOptGroupOptions,array(),3,true);
    ?>
</optgroup>
<!-- end of optgroup -->
