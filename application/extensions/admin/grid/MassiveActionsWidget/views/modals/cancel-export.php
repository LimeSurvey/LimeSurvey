<?php

if (isset($aAction['showSelected'])) {
    $showSelected = $aAction['showSelected'];
} else {
    $showSelected = 'no';
}

if (isset($aAction['selectedUrl'])) {
    $selectedUrl = $aAction['selectedUrl'];
} else {
    $selectedUrl = '#';
}

if (isset($aAction['largeModalView']) && $aAction['largeModalView']) {
    $largeModalView = 'modal-lg';
}else{
    $largeModalView = '';
}
?>

<!-- Modal confirmation for <?php
echo $aAction['action']; ?> -->

<div id="massive-actions-modal-<?php
echo $this->gridid; ?>-<?php
echo $aAction['action']; ?>-<?php
echo $key; ?>"
     class="modal fade"
     role="dialog"
     data-keepopen="<?php echo $aAction['keepopen']; ?>"
     data-show-selected="<?php echo $showSelected; ?>"
     data-selected-url="<?php echo $selectedUrl ?>"
>
    <div class="modal-dialog <?php echo $largeModalView?>">
        <!-- Modal content-->
        <div class="modal-content" style="text-align:left; color:#000">
            <?php
            Yii::app()->getController()->renderPartial(
                '/layouts/partial_modals/modal_header',
                ['modalTitle' => $aAction['sModalTitle']]
            );
            ?>
            <div class="modal-body">
                <div class='modal-body-text'><?php
                    echo $aAction['htmlModalBody']; ?></div>

                <!-- shows list of selected items in the modal-->
                <div class="selected-items-list"></div>

                <?php
                if (isset($aAction['aCustomDatas'])) { ?>
                    <!--
                        Custom datas needed for action defined directly in the widget call.
                        Always hidden in Yes/No case.
                        For specific input (like text, selector, etc) that should be filled by user
                        parse a form to htmlModalBody and attribute to the wanted input the class "custom-data"
                    -->
                    <div class="custom-modal-datas d-none">
                        <?php
                        foreach ($aAction['aCustomDatas'] as $aCustomData) { ?>
                            <input
                                    class="custom-data"
                                    type="hidden"
                                    name="<?php
                                    echo $aCustomData['name']; ?>"
                                    value="<?php
                                    echo $aCustomData['value']; ?>"/>
                        <?php
                        } ?>
                    </div>
                <?php
                } ?>
            </div>
            <?php
            Yii::app()->getController()->renderPartial('/layouts/partial_modals/modal_footer_cancelexport');
            ?>
        </div>
    </div>
</div>
