<?php
?>
<!-- List of actions -->
<ul class="dropdown-menu listActions" aria-labelledby="<?php echo $this->dropupId; ?>">

    <?php foreach($this->aActions as $key => $aAction):?>
        <?php
        switch ($aAction['type']):
            case 'separator':
                ?>

                <!-- Separator -->
                <li role="separator" class="dropdown-divider"></li>
                <?php break;?>

            <?php case 'dropdown-header': ?>

            <!-- Header -->
            <li class="dropdown-header"> <?php echo $aAction['text'];?></li>
            <?php break;?>

        <?php case 'action': ?>

            <!-- Action -->
            <li
                <?php if(!empty($aAction['disabled'])) : ?>
                    class='disabled'
                <?php endif;?>
            >
                <a href="#"
                   class="dropdown-item"
                    <?php if(!empty($aAction['disabled'])) : ?>
                        data-disabled='1'
                    <?php endif;?>
                   data-url="<?php echo $aAction['url'];?>"
                    <?php if (isset($aAction['on-success'])): ?>
                        data-on-success="<?php echo $aAction['on-success'];?>"
                    <?php endif; ?>
                    <?php if (isset($aAction['custom-js'])): ?>
                        data-custom-js="<?php echo $aAction['custom-js'];?>"
                    <?php endif; ?>
                   data-action="<?php echo $aAction['action'];?>"
                   data-type="<?php echo $aAction['actionType']; //Direct action, or modal ?>"
                   data-grid-reload="<?php if(isset($aAction['grid-reload'])){echo $aAction['grid-reload'];}else{echo "no";}?>"
                    <?php
                    /**
                     * It the action type is 'modal', a modal will be generated for this action, with the id: massive-actions-modal- $this->gridid - $aAction['action'] -  $key
                     * It will be shown by a javascript call in listAction.js
                     */
                    ?>
                    <?php if ($aAction['actionType']=="modal"):?>
                        data-modal-id="massive-actions-modal-<?php echo $this->gridid;?>-<?php echo $aAction['action'];?>-<?php echo $key; ?>"
                    <?php endif;?>

                   data-action-type='<?php echo $aAction['actionType'];?>'

                    <?php
                    // Specific datas needed for the js
                    // See token grid emails for an example
                    ?>
                    <?php if (isset($aAction['aLinkSpecificDatas'])):?>
                        <?php foreach($aAction['aLinkSpecificDatas'] as $sDataName => $sDataValue ):?>
                            data-<?php echo $sDataName; ?> = "<?php echo $sDataValue;?>"
                        <?php endforeach;?>
                    <?php endif;?>
                >
                    <span class="<?php echo $aAction['iconClasses'];?>"></span>
                    <?php echo $aAction['text'];?>
                </a>
            </li>
            <?php break;?>

        <?php endswitch;?>
    <?php endforeach;?>
</ul>
