<?php
/**
 * Massive actions Widget, selector view
 * Render:
 *  - a dropup selector that can be injected in the footer of a grid, to display the multiple actions for the items of the grid
 *  - the modal associated associated with each action
 */
$listOfActions = $this->render('listOfActions', get_defined_vars(), true);
?>

<!-- Massive actions widget : selector view -->
<div class="float-start dropup listActions" data-pk="<?php echo $this->pk;?>" data-grid-id="<?php echo $this->gridid;?>" id="<?php echo $this->dropupId;?>">
    <!-- Drop Up button selector -->
    <?php
    $this->widget('ext.ButtonWidget.ButtonWidget', [
    'name' => '',
    'id' => '',
    'text' => $this->dropUpText,
    'icon' => '',
    'isDropDown' => true,
    'dropDownIcon' => 'ri-arrow-down-s-fill',
    'dropDownContent' => $listOfActions,
    'htmlOptions' => [
    'class' => 'btn btn-outline-secondary btntooltip disabled massiveAction',
    ],
    ]); ?>


</div>

<?php App()->getClientScript()->registerScript("ListQuestions-run-pagination", "
    var gridId = '".$this->gridid."';
", LSYii_ClientScript::POS_BEGIN); ?>


<!-- End of Massive actions widget : selector view -->
