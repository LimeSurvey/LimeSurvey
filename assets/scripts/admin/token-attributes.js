'use strict';

/**
 * Initializes the token attributes management functionality when the document is ready.
 * Sets up event handlers for managing attribute types and dropdown options, including:
 * - Modal interactions for editing token field attribute types
 * - Adding and removing dropdown options
 * - Saving attribute type selections and their associated options
 * - Updating the UI to reflect changes in attribute types
 *
 * This function establishes the complete workflow for token attribute configuration,
 * including type selection, dropdown option management, and form submission.
 *
 * @returns {void}
 */
$(document).ready(function () {
    let currentTokenField = '';
    let attributeTypeLabels = window.attributeTypeLabels || {};
    let optionCounter = 0;

    // Toggle dropdown options visibility
    function toggleDropdownOptions(type) {
        if (type === 'DD') {
            $('#dropdownOptionsContainer').removeClass('d-none');
        } else {
            $('#dropdownOptionsContainer').addClass('d-none');
            $('#dropdownOptionsList').empty();
            optionCounter = 0;
        }
    }

    /**
     * Adds a new dropdown option input field to the dropdown options list.
     * Creates an input group with a text input and a delete button, then appends it to the dropdown options container.
     * Each option is assigned a unique ID based on the current option counter.
     *
     * @param {string} value - The initial value to populate in the dropdown option input field. Can be an empty string for new options.
     * @returns {void}
     */
    function addDropdownOptionField(value) {
        let optionId = 'option_' + optionCounter++;
        let optionHtml = `
                <div class="input-group mb-2" id="${optionId}">
                    <input type="text" class="form-control dropdown-option-input" value="${value}">
                    <span class="input-group-text remove-option" type="button" data-option-id="${optionId}">
                        <i class="ri-delete-bin-fill"></i>
                    </button>
                </div>
            `;
        $('#dropdownOptionsList').append(optionHtml);

        // Focus the newly added input field
        $('#' + optionId + ' .dropdown-option-input').focus();
    }

    /**
     * Loads and displays existing dropdown options for the current token field.
     * Retrieves the options from a hidden input field, parses them from JSON format,
     * clears the current dropdown options list, and repopulates it with the existing options.
     * If no options exist or parsing fails, the dropdown options list is cleared.
     *
     * @returns {void}
     */
    function loadExistingOptions() {
        let existingOptions = $('#type_options_' + currentTokenField).val();
        if (existingOptions) {
            try {
                let options = JSON.parse(existingOptions);
                $('#dropdownOptionsList').empty();
                optionCounter = 0;
                options.forEach(function (option) {
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
    $('#addDropdownOption').on('click', function () {
        addDropdownOptionField('');
    });

    // When modal opens, store which token field is being edited
    $('#attributeTypeModal').on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);
        currentTokenField = button.data('token-field');

        // Load existing value if present, default to 'TB'
        let existingValue = $('#type_' + currentTokenField).val() || 'TB';
        $('#attributeTypeSelect').val(existingValue);

        // Show/hide dropdown options based on type
        toggleDropdownOptions(existingValue);

        // Load existing dropdown options if any
        loadExistingOptions();
    });

    // Handle attribute type change
    $('#attributeTypeSelect').on('change', function () {
        let selectedType = $(this).val();
        toggleDropdownOptions(selectedType);
    });

    // Remove dropdown option
    $(document).on('click', '.remove-option', function () {
        let optionId = $(this).data('option-id');
        $('#' + optionId).remove();
    });

    // Save button click handler
    $('#saveAttributeType').on('click', function () {
        let selectedType = $('#attributeTypeSelect').val();

        // Update the hidden input for this token field
        $('#type_' + currentTokenField).val(selectedType);

        // If dropdown type, save the options
        if (selectedType === 'DD') {
            let options = [];
            $('.dropdown-option-input').each(function () {
                let value = $(this).val().trim();
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
        let displayText = attributeTypeLabels[selectedType] || attributeTypeLabels['TB'];
        $('.type_display_' + currentTokenField).text(displayText);

        // Close the modal
        $('#attributeTypeModal').modal('hide');

        // Trigger the form submission
        $('#manage_token_attributes_form').submit();
    });
});
