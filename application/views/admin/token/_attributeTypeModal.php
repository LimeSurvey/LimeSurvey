<?php

/**
 * @var array $attributeTypeDropdownArray;
 */
?>
<div class="modal fade" id="attributeTypeModal" tabindex="-1" aria-labelledby="attributeTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attribute_type_modal">
                    <?php eT('Attribute type'); ?>
                </h5>
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

                <div id="dropdownOptionsContainer" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">
                            <?php eT('Dropdown fields'); ?>
                        </label>
                        <div id="dropdownOptionsList">
                            <!-- Options will be added here dynamically -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="addDropdownOption">
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
    $(document).ready(function() {
        var currentTokenField = '';
        var attributeTypeLabels = <?php echo json_encode($attributeTypeDropdownArray); ?>;
        var optionCounter = 0;

        // When modal opens, store which token field is being edited
        $('#attributeTypeModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            currentTokenField = button.data('token-field');

            // Load existing value if present, default to 'TB'
            var existingValue = $('#type_' + currentTokenField).val() || 'TB';
            $('#attributeTypeSelect').val(existingValue);

            // Show/hide dropdown options based on type
            toggleDropdownOptions(existingValue);

            // Load existing dropdown options if any
            loadExistingOptions();
        });

        // Handle attribute type change
        $('#attributeTypeSelect').on('change', function() {
            var selectedType = $(this).val();
            toggleDropdownOptions(selectedType);
        });

        // Toggle dropdown options visibility
        function toggleDropdownOptions(type) {
            if (type === 'DD') {
                $('#dropdownOptionsContainer').show();
            } else {
                $('#dropdownOptionsContainer').hide();
                $('#dropdownOptionsList').empty();
                optionCounter = 0;
            }
        }

        // Load existing dropdown options
        function loadExistingOptions() {
            var existingOptions = $('#type_options_' + currentTokenField).val();
            if (existingOptions) {
                try {
                    var options = JSON.parse(existingOptions);
                    $('#dropdownOptionsList').empty();
                    optionCounter = 0;
                    options.forEach(function(option) {
                        addDropdownOptionField(option);
                    });
                } catch (e) {
                    console.error('Error parsing existing options:', e);
                }
            } else {
                $('#dropdownOptionsList').empty();
                optionCounter = 0;
            }
        }

        // Add dropdown option field
        $('#addDropdownOption').on('click', function() {
            addDropdownOptionField('');
        });

        function addDropdownOptionField(value) {
            var optionId = 'option_' + optionCounter++;
            var optionHtml = `
                <div class="input-group mb-2" id="${optionId}">
                    <input type="text" class="form-control dropdown-option-input" value="${value}" placeholder="<?php eT('Enter option value'); ?>">
                    <span class="input-group-text remove-option" type="button" data-option-id="${optionId}">
                        <i class="ri-delete-bin-fill"></i>
                    </button>
                </div>
            `;
            $('#dropdownOptionsList').append(optionHtml);
        }

        // Remove dropdown option
        $(document).on('click', '.remove-option', function() {
            var optionId = $(this).data('option-id');
            $('#' + optionId).remove();
        });

        // Save button click handler
        $('#saveAttributeType').on('click', function() {
            var selectedType = $('#attributeTypeSelect').val();

            // Update the hidden input for this token field
            $('#type_' + currentTokenField).val(selectedType);

            // If dropdown type, save the options
            if (selectedType === 'DD') {
                var options = [];
                $('.dropdown-option-input').each(function() {
                    var value = $(this).val().trim();
                    if (value) {
                        options.push(value);
                    }
                });

                // Store options in a hidden field (create if doesn't exist)
                if ($('#type_options_' + currentTokenField).length === 0) {
                    $('<input>').attr({
                        type: 'hidden',
                        id: 'type_options_' + currentTokenField,
                        name: 'type_options_' + currentTokenField
                    }).insertAfter('#type_' + currentTokenField);
                }
                $('#type_options_' + currentTokenField).val(JSON.stringify(options));
            } else {
                // Remove options field if not dropdown
                $('#type_options_' + currentTokenField).remove();
            }

            // Update the display text using the label from the dropdown array
            var displayText = attributeTypeLabels[selectedType] || attributeTypeLabels['TB'];
            $('#type_display_' + currentTokenField).text(displayText);

            // Close the modal
            $('#attributeTypeModal').modal('hide');
        });
    });
</script>