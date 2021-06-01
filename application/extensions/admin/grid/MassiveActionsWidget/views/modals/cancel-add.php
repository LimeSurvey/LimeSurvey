<!-- Modal confirmation for <?php echo $aAction['action'];?> -->
<div id="massive-actions-modal-<?php echo $this->gridid;?>-<?php echo $aAction['action'];?>-<?php echo $key; ?>" class="modal fade" role="dialog" data-keepopen="<?php echo $aAction['keepopen'];?>" data-show-selected="<?php if(isset($aAction['showSelected'])){echo $aAction['showSelected'];}else{ echo 'no'; }?>" data-selected-url="<?php if(isset($aAction['selectedUrl'])){ echo $aAction['selectedUrl'];}else{ echo '#'; }?>">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <div class="modal-title h4"><?php echo $aAction['sModalTitle']; ?></div>
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
                    <div class="custom-modal-datas hidden">
                        <?php foreach($aAction['aCustomDatas'] as $aCustomData):?>
                            <input class="custom-data" type="hidden" name="<?php echo $aCustomData['name'];?>" value="<?php echo $aCustomData['value'];?>" />
                        <?php endforeach;?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer modal-footer-buttons">
                <button type="button" class="btn btn-cancel" data-dismiss="modal"><span class='fa fa-ban'></span>
                    &nbsp;
                    <?php eT("Cancel"); ?>

                </button>
                <a class="btn btn-primary btn-ok"><span class='fa fa-plus'></span>
                    &nbsp;
                        <?php eT("Add"); ?>
                </a>
            </div>

        </div>
    </div>
</div>
