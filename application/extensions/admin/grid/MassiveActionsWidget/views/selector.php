<?php
/**
 * Massive actions Widget, selector view
 * Render:
 *  - a dropup selector that can be injected in the footer of a grid, to display the multiple actions for the items of the grid
 *  - the modal associated associated with each action
 */
?>

<!-- Massive actions widget : selector view -->
<div class="col-sm-4 pull-left dropup listActions" data-pk="<?php echo $this->pk;?>" data-grid-id="<?php echo $this->gridid;?>" id="<?php echo $this->dropupId;?>">
    <!-- Drop Up button selector -->
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
      <?php echo $this->dropUpText;?>
    <span class="caret"></span>
    </button>

    <!-- List of actions -->
    <ul class="dropdown-menu listActions" aria-labelledby="<?php echo $this->dropupId; ?>">

        <?php foreach($this->aActions as $key => $aAction):?>
            <?php
                switch ($aAction['type']):
                case 'separator':
            ?>

                <!-- Separator -->
                <li role="separator" class="divider"></li>
            <?php break;?>

            <?php case 'dropdown-header': ?>

                <!-- Header -->
                <li class="dropdown-header"> <?php echo $aAction['text'];?></li>
            <?php break;?>

            <?php case 'action': ?>

                <!-- Action -->
                <li>
                    <a href="#"
                        data-url="<?php $aAction['url'];?>"
                        data-action="<?php $aAction['action'];?>"
                        data-type="<?php echo $aAction['actionType']; //Direct action, or modal ?>"
                        <?php if ($this->type=="modal"):?>
                            data-modal-id="massive-actions-modal-<?php $aAction['action'];?>-<?php echo $key; ?>"
                        <?php endif;?>
                    >
                        <span class="<?php $aAction['iconClasses'];?>"></span>
                        <?php $aAction['text'];?>
                    </a>
                </li>
            <?php break;?>

            <?php endswitch;?>
        <?php endforeach;?>
    </ul>
</div>


<!-- End of Massive actions widget : selector view -->
