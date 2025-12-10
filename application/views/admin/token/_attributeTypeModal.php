<?php

/**
 * @var array $attributeTypeDropdownArray;
 */
?>
<div class="modal fade" id="attributeTypeModal" tabindex="-1" aria-labelledby="attributeTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="attributeTypeModalLabel">
                    <?php eT('Attribute type'); ?>
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="attributeTypeSelect" class="form-label"><?php eT('Select attribute type:'); ?></label>
                    <?php
                    echo CHtml::dropDownList(
                            'attributeTypeSelect',
                            'TB',
                            $attributeTypeDropdownArray,
                            array(
                                    'class' => 'form-select',
                                    'id' => 'attributeTypeSelect'
                            )
                    );
                    ?>
                </div>

                <div id="dropdownOptionsContainer" class="d-none">
                    <div class="mb-3">
                        <label class="form-label">
                            <?php eT('Dropdown fields'); ?>
                        </label>
                        <div id="dropdownOptionsList">
                            <!-- Options will be added here dynamically -->
                        </div>
                        <button type="button" class="btn btn-sm btn-link" id="addDropdownOption">
                            <i class="ri-add-line"></i> <?php eT('Add dropdown field'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php eT('Close'); ?></button>
                <button type="button" class="btn btn-primary" id="saveAttributeType"><?php eT('Save'); ?></button>
            </div>
        </div>
    </div>
</div>
<script>
    // Pass PHP variables to JavaScript
    window.attributeTypeLabels = <?php echo json_encode($attributeTypeDropdownArray); ?>;
</script>
<?php
App()->getClientScript()->registerScriptFile(App()->getConfig('adminscripts') . 'token-attributes.js', LSYii_ClientScript::POS_BEGIN);
?>