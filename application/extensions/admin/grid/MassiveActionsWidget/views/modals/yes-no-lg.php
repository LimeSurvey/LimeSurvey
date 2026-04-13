<?php

/**
 * TODO: this file seems to be unused
 */

?>
<!-- Modal confirmation for <?php echo $aAction['action'];?> -->
<div id="massive-actions-modal-<?php echo $this->gridid;?>-<?php echo $aAction['action'];?>-<?php echo $key; ?>" class="modal fade" role="dialog" data-keepopen="<?php echo $aAction['keepopen'];?>" data-show-selected="<?php if(isset($aAction['showSelected'])){echo $aAction['showSelected'];}else{ echo 'no'; }?>" data-selected-url="<?php if(isset($aAction['selectedUrl'])){ echo $aAction['selectedUrl'];}else{ echo '#'; }?>">
    <div class="modal-dialog modal-lg">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo $aAction['sModalTitle']; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class='modal-body-text'><?php echo $aAction['htmlModalBody']; ?></div>

                 <!-- shows list of selected items in the modal-->
                 <div class="selected-items-list"></div>

                <?php if (isset($aAction['aCustomDatas'])):?>
                    <!--
                        Custom datas needed for action defined directly in the widget call.
                        Always hidden in Yes/No case.
                        For specific input (like text, selector, etc) that should be filled by user
                        parse a form to htmlModalBody and attribute to the wanted input the class "custom-data"
                    -->
                    <div class="custom-modal-datas d-none">
                        <?php foreach($aAction['aCustomDatas'] as $aCustomData):?>
                            <input class="custom-data" type="hidden" name="<?php echo $aCustomData['name'];?>" value="<?php echo $aCustomData['value'];?>" />
                        <?php endforeach;?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer modal-footer-buttons">
                <a role="button" class="btn btn-primary btn-ok"><span class='ri-check-fill'></span>
                    &nbsp;
                    <?php if(isset($aAction['yes'])):?>
                        <?php echo $aAction['yes'];?>
                    <?php else:?>
                        <?php eT("Yes"); ?>
                    <?php endif;?>
                </a>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                    <span class='ri-forbid-2-line'></span>
                    &nbsp;
                    <?php if(isset($aAction['no'])):?>
                        <?php echo $aAction['no'];?>
                    <?php else:?>
                        <?php eT("No"); ?>
                    <?php endif;?>
                </button>
            </div>

            <?php if($aAction['keepopen']=="yes"):?>
                <div class="modal-footer modal-footer-close" style="display: none;">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                        <span class='ri-forbid-2-line'></span>
                        &nbsp;
                        <?php eT("Close"); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
