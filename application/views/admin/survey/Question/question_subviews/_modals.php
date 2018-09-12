<?php
/**
 * Render the modals for subquestions/answers label sets, quick add, save as label set
 */
?>

<!-- quickaddModal -->
<div class="modal fade labelsets-update" id="quickaddModal" tabindex="-1" role="dialog" aria-labelledby="quickaddModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span >&times;</span></button>
                <h4 class="modal-title" id="quickaddModal"><?php eT('Enter your answers:'); ?></h4>
            </div>

            <div class="modal-body">
                <p>
                    <?php eT('Enter one answer per line. You can provide a code by separating code and answer text with a semikolon or tab. For multilingual surveys you add the translation(s) on the same line separated with a semikolon or tab.'); ?>
                </p>
                <textarea id='quickaddarea' class='tipme' title='' cols='100' rows='10' style='width:570px;'></textarea>
            </div>
            <div class="modal-footer button-list">
                <button class='btn btn-default' id='btnqareplace' type='button'><?php eT('Replace'); ?></button>
                <button class='btn btn-default' id='btnqainsert' type='button'><?php eT('Add'); ?></button>
                <button class='btn btn-warning' id='btnqacancel' type='button'  data-dismiss="modal"><?php eT('Cancel'); ?></button>
            </div>
        </div>
    </div>
</div>

<!--labelset browser Modal -->
<div class="modal fade labelsets-update" id="labelsetbrowserModal" tabindex="-1" role="dialog" aria-labelledby="labelsetbrowserModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span >&times;</span></button>
                <h4 class="modal-title" id="labelsetbrowserModal"><?php eT('Available label sets:'); ?></h4>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-5">
                        <select id='labelsets' size='10' style='width:250px;'>
                            <option>&nbsp;</option>
                        </select>
                    </div>
                    <div class="col-sm-7">
                        <div id='labelsetpreview'>

                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer button-list">
                <button id='btnlsreplace' type='button' class='btn btn-default'><?php eT('Replace'); ?></button>
                <button id='btnlsinsert' type='button' class='btn btn-default'><?php eT('Add'); ?></button>
                <button class='btn btn-warning' id='btnqacancel' type='button'  data-dismiss="modal"><?php eT('Cancel'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Save as labelset Modal -->
<div class="modal fade" id="saveaslabelModal" tabindex="-1" role="dialog" aria-labelledby="saveaslabelModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span >&times;</span></button>
                <h4 class="modal-title">
                    <?php eT('Save as label set:'); ?>
                </h4>

            </div>

            <div class="modal-body">
                <p>
                    <input type="radio" name="savelabeloption" id="newlabel">
                    <label for="newlabel"><?php eT('New label set'); ?></label>
                </p>
                <p>
                    <input type="radio" name="savelabeloption" id="replacelabel">
                    <label for="replacelabel"><?php eT('Replace existing label set'); ?>
                </p>
            </div>

            <div class="modal-footer button-list">
                <button id='btnsave' class='btn btn-default' type='button'><?php eT('Save'); ?></button>
                <button class='btn btn-warning' id='btnlacancel' type='button'  data-dismiss="modal"><?php eT('Cancel'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm labelset replacement -->
<div class="modal fade" id="dialog-confirm-replaceModal" tabindex="-1" role="dialog" aria-labelledby="dialog-confirm-replaceModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span >&times;</span></button>
                <h4 class="modal-title">
                    <?php eT('Replace label set?'); ?>
                </h4>
            </div>

            <div class="modal-body">
                <p>
                    <span id='strReplaceMessage'></span>
                </p>
            </div>

            <div class="modal-footer button-list">
                <button class='btn btn-default' id='btnlconfirmreplace' type='button' data-dismiss="modal"  ><?php eT('OK'); ?></button>
                <button class='btn btn-warning' id='btnlacancel' type='button'  data-dismiss="modal"><?php eT('Cancel'); ?></button>
            </div>
        </div>
    </div>
</div>
