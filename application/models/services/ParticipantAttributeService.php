<?php

namespace LimeSurvey\Models\Services;

use CHtml;
use CHttpException;
use ParticipantAttributeName;
use ParticipantAttributeNameLang;
use ParticipantAttributeValue;

/**
 * Service class for managing participant attributes.
 * All dependencies are injected to enable mocking.
 *
 * There is still plenty of attribute related functions scattered in the codebase
 * (see ParticipantsAction.php and Participant.php)
 * which should be moved in here.
 * 
 * This service class also deals with attributes of survey participants (tokens)
 * which are related to CPDB attributes but still stored differently.
 */
class ParticipantAttributeService
{
    private ParticipantAttributeName $modelParticipantAttributeName;
    private ParticipantAttributeNameLang $modelParticipantAttributeLang;
    private ParticipantAttributeValue $modelParticipantAttributeValue;

    private $attributeTypes = null;

    public function __construct(
        ParticipantAttributeName $modelParticipantAttributeName,
        ParticipantAttributeNameLang $modelParticipantAttributeLang,
        ParticipantAttributeValue $modelParticipantAttributeValue
    ) {
        $this->modelParticipantAttributeName = $modelParticipantAttributeName;
        $this->modelParticipantAttributeLang = $modelParticipantAttributeLang;
        $this->modelParticipantAttributeValue = $modelParticipantAttributeValue;
    }

    /**
     * Saves a complete participant attribute with its name, localization, and dropdown options.
     *
     * This method orchestrates the creation of a new participant attribute by:
     * 1. Creating the base attribute record with type and encryption settings
     * 2. Adding a localized name for the attribute in the current admin language
     * 3. If the attribute is a dropdown type, saving all available options
     *
     * @param array $attributeData Array containing attribute configuration with keys:
     *                            - 'type': The attribute type (e.g., 'DD' for dropdown, 'TB' for textbox)
     *                            - 'encrypted': Whether the attribute should be encrypted ('Y' or 'N')
     *                            - 'type_options': JSON-encoded string of dropdown options (if type is 'DD')
     * @param string $attributeName The name/identifier for the attribute (e.g., 'attribute_1')
     * @return int The primary key (attribute_id) of the newly created attribute
     * @throws CHttpException If any save operation fails, throws a 500 error with validation messages
     */
    public function saveParticipantAttribute(
        array $attributeData,
        string $attributeName
    ): int {
        $attributeData = $this->normalizeAttributeDataset($attributeData);
        $attributeId = $this->saveParticipantAttributeName(
            $attributeData,
            $attributeName
        );
        $this->saveParticipantAttributeL10n($attributeName, $attributeId);
        $this->saveDropdownAttributeOptions($attributeId, $attributeData);

        return $attributeId;
    }

    /**
     * Saves a new participant attribute to the database.
     *
     * Creates a new central participant attribute with the specified type, encryption setting,
     * and default name. The attribute is set as visible by default.
     *
     * @param array $attributeData Array containing attribute configuration with keys:
     *                            - 'type': The attribute type (e.g., 'DD' for dropdown, 'TB' for textbox)
     *                            - 'encrypted': Whether the attribute should be encrypted ('Y' or 'N')
     * @param string $attributeName The default name/identifier for the attribute (e.g., 'attribute_1')
     * @return int The primary key (attribute_id) of the newly created attribute
     * @throws CHttpException If the attribute fails to save, throws a 500 error with validation messages
     */
    public function saveParticipantAttributeName(
        array $attributeData,
        string $attributeName
    ): int {
        $insertData = [
            'attribute_type' => $attributeData['type'],
            'visible' => 'Y',
            'encrypted' => $attributeData['encrypted'],
            'defaultname' => $attributeName
        ];
        $model = clone $this->modelParticipantAttributeName;
        $model->setAttributes($insertData, false);
        if (!$model->save()) {
            throw new CHttpException(500, CHtml::errorSummary($model));
        }
        return (int)$model->getPrimaryKey();
    }

    /**
     * Saves a localized name for a participant attribute.
     *
     * Creates a new ParticipantAttributeNameLang record to store the translated/localized
     * name of a participant attribute in the current admin language. This allows attributes
     * to have different display names in different languages.
     *
     * @param string $attributeName The localized/translated name for the attribute
     * @param int $attributeId The ID of the participant attribute from ParticipantAttributeName
     * @return void
     * @throws CHttpException If the localized name fails to save, throws a 500 error with validation messages
     */
    public function saveParticipantAttributeL10n(
        string $attributeName,
        int $attributeId
    ): void {
        $adminLang = App()->session['adminlang'] ?? App()->getConfig('defaultlang');
        $insertData = [
            'attribute_id' => $attributeId,
            'attribute_name' => $attributeName,
            'lang' => $adminLang
        ];
        $model = clone $this->modelParticipantAttributeLang;
        $model->setAttributes($insertData, false);
        if (!$model->save()) {
            throw new CHttpException(500, CHtml::errorSummary($model));
        }
    }

    /**
     * Saves dropdown attribute options to the participant_attribute_values table.
     *
     * This method processes dropdown (DD) type attributes by decoding their JSON-encoded
     * options and creating individual ParticipantAttributeValue records for each option.
     * If the attribute type is not 'DD', no action is taken.
     *
     * @param int $attributeId The ID of the participant attribute from ParticipantAttributeName
     * @param array $attributeData Array containing attribute data, must include 'type' and 'type_options' keys
     * @return void
     */
    public function saveDropdownAttributeOptions(
        int $attributeId,
        array $attributeData
    ): void {
        if ($attributeData['type'] == 'DD') {
            $typeOptions = array_key_exists(
                'type_options',
                $attributeData
            ) ? $attributeData['type_options'] : '[]';
            $decodedOptions = $this->decodeJsonEncodedTypeOptions(
                $typeOptions
            );
            foreach ($decodedOptions as $option) {
                $model = clone $this->modelParticipantAttributeValue;
                $model->attribute_id = $attributeId;
                $model->value = $option;
                if (!$model->save()) {
                    throw new CHttpException(500, CHtml::errorSummary($model));
                }
            }
        }
    }

    /**
     * Decodes JSON encoded options string and returns an array.
     *
     * @param string $jsonEncodedOptions JSON string to decode
     * @return array Decoded array or empty array if invalid JSON or not an array
     */
    public function decodeJsonEncodedTypeOptions(string $jsonEncodedOptions): array
    {
        $decodedOptions = [];

        if (!empty($jsonEncodedOptions)) {
            $decoded = json_decode($jsonEncodedOptions, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Convert numeric array to associative array with values as both keys and values
                foreach ($decoded as $option) {
                    $decodedOptions[$option] = $option;
                }
            }
        }

        return $decodedOptions;
    }

    /**
     * Normalizes attribute data by ensuring all required keys exist with default values.
     *
     * This method validates and fills in missing attribute data keys with sensible defaults
     * to ensure consistent data structure when creating or updating participant attributes.
     *
     * @param array $attributeData The attribute data to normalize
     * @return array The normalized attribute data with all required keys
     */
    public function normalizeAttributeDataset(array $attributeData): array
    {
        $defaults = [
            'description' => '',
            'mandatory' => 'N',
            'encrypted' => 'N',
            'show_register' => 'Y',
            'type' => 'TB',
            'type_options' => '[]',
            'cpdbmap' => ''
        ];

        // Merge with defaults, keeping existing values
        $normalized = array_merge($defaults, $attributeData);

        // Ensure boolean-like values are normalized to Y/N
        $normalized['mandatory'] = in_array($normalized['mandatory'], ['Y', 'y', '1', 1, true], true) ? 'Y' : 'N';
        $normalized['encrypted'] = in_array($normalized['encrypted'], ['Y', 'y', '1', 1, true], true) ? 'Y' : 'N';
        $normalized['show_register'] = in_array($normalized['show_register'], ['Y', 'y', '1', 1, true], true) ? 'Y' : 'N';

        // Ensure type_options is a valid JSON string
        if (!is_string($normalized['type_options'])) {
            $normalized['type_options'] = json_encode($normalized['type_options']);
        }

        // Validate type_options is valid JSON
        json_decode($normalized['type_options']);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $normalized['type_options'] = '[]';
        }

        return $normalized;
    }

    /**
     * Used by registration form
     * we get array of attribute names and their values
     * we figure out the type and convert the date if type is DP (date picker)
     * we replace the converted date in the array and return it back
     * @param array $attributes
     * @return array
     */
    public function prepareAttributesForSave(
        array $attributes,
        string $fromFormat,
        array $registerAttributesInfo
    ) {
        foreach ($attributes as $attrKey => $attrValue) {
            if (array_key_exists($attrKey, $registerAttributesInfo)) {
                $attrInfo = $registerAttributesInfo[$attrKey];
                if (
                    array_key_exists('type', $attrInfo)
                    && $attrInfo['type'] === 'DP'
                ) {
                    // convert date if attribute type is DP
                    $convertedDate = $this->convertDateToStoreFormat($attrValue, $fromFormat);
                    if ($convertedDate) {
                        $attributes[$attrKey] = $convertedDate;
                    }
                }
            }
        }
        return $attributes;
    }


    /**
     * Converts a date attribute value to standard storage format if it's a date picker type.
     *
     * This method checks if the attribute is a date picker (DP) type and converts its value
     * from the user's session date format to the standard database storage format (Y-m-d).
     * For non-date attributes, the original value is returned unchanged. If the date conversion
     * fails, an empty string is returned.
     *
     * @param array $attributeData Array containing attribute metadata with keys:
     *                            - 'type': The attribute type (e.g., 'DP' for date picker)
     * @param mixed $attributeValue The attribute value to be converted. Expected to be a date string
     *                             if the attribute type is 'DP', otherwise can be any value.
     * @return string The converted date string in 'Y-m-d' format if type is 'DP' and conversion succeeds,
     *                an empty string if type is 'DP' but conversion fails, or the original value cast
     *                to string for non-date types.
     */
    public function convertDateAttribute(array $attributeData, $attributeValue): string
    {
        $dateFormat = getDateFormatData(App()->session['dateformat']);
        if (array_key_exists('type', $attributeData) && $attributeData['type'] == 'DP') {
            $date = $this->convertDateToStoreFormat($attributeValue, $dateFormat['phpdate']);
            $attributeValue = $date === false ? '' : $date;
        }

        return $attributeValue;
    }

    /**
     * Converts a date string from a specified format to the standard storage format.
     *
     * This method takes a date string in a given format and converts it to the standard
     * datetime format used for database storage (Y-m-d). If the conversion fails
     * due to an invalid date string or format mismatch, false is returned.
     *
     * @param string $dateString The date string to be converted
     * @param string $fromFormat The PHP date format string that describes the input date format
     * @return string|false The converted datetime string in 'Y-m-d' format, or false if conversion fails
     */
    private function convertDateToStoreFormat($dateString, $fromFormat)
    {
        $convertedDateObj = \DateTime::createFromFormat(
            $fromFormat,
            $dateString
        );
        return $convertedDateObj ? $convertedDateObj->format(
            'Y-m-d'
        ) : false;
    }

    /**
     * Converts a CPDB (Central Participant Database) date attribute to standard storage format.
     *
     * This method retrieves the attribute type by ID and converts date picker (DP) values
     * from the user's session date format to the standard database storage format (Y-m-d).
     * For non-date attributes, the original value is returned unchanged.
     *
     * @param int $attributeId The ID of the participant attribute
     * @param mixed $attributeValue The attribute value to be converted
     * @return string The converted date string in 'Y-m-d' format if type is 'DP' and conversion succeeds,
     *                an empty string if type is 'DP' but conversion fails, or the original value for non-date types.
     */
    public function convertCPDBDateToStoreFormat($attributeId, $attributeValue)
    {
        $attributeType = $this->getParticipantAttributeNameType($attributeId);
        $attributeData = ['type' => $attributeType];
        return $this->convertDateAttribute($attributeData, $attributeValue);
    }

    /**
     * Converts a date attribute from storage format to display format.
     *
     * This method retrieves the attribute type by ID and converts date picker (DP) values
     * from the standard database storage format (Y-m-d) to the user's session date format.
     * For non-date attributes, the original value is returned unchanged.
     *
     * @param int $attributeId The ID of the participant attribute
     * @param mixed $value The attribute value to be converted
     * @param bool $checkForType Whether to check the attribute type and convert only if it's a date picker (DP) type. Default is true.
     * @return mixed The converted date string in the user's preferred format if type is 'DP' and value is in Y-m-d format,
     *                or the original value for non-date types or invalid dates.
     */
    public function convertDateAttributeToDisplayFormat(int $attributeId, $value, $checkForType = true)
    {
        if ($checkForType) {
            $attributeType = $this->getParticipantAttributeNameType($attributeId);
            if ($attributeType !== 'DP' || empty($value)) {
                return $value;
            }
        }

        // Check if the value is in Y-m-d format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }
        $dateObj = \DateTime::createFromFormat('Y-m-d', $value);
        if ($dateObj === false || $dateObj->format('Y-m-d') !== $value) {
            return $value;
        }

        // Convert to user's preferred date format
        $dateFormat = getDateFormatData(App()->session['dateformat']);
        return $dateObj->format($dateFormat['phpdate']);
    }

    /**
     * Retrieves the attribute type for a given participant attribute ID.
     *
     * This method uses lazy loading to cache all attribute types on first call,
     * then returns the type for the requested attribute ID from the cache.
     * The cache persists for the lifetime of the service instance.
     *
     * @param int $attributeId The ID of the participant attribute
     * @return string|null The attribute type (e.g., 'DD', 'TB', 'DP') if found, null if the attribute ID doesn't exist
     */
    public function getParticipantAttributeNameType($attributeId)
    {
        if ($this->attributeTypes === null) {
            $allAttributes = $this->modelParticipantAttributeName->findAll();
            $this->attributeTypes = [];
            foreach ($allAttributes as $attribute) {
                $this->attributeTypes[$attribute->attribute_id] = $attribute->attribute_type;
            }
        }
        
        return $this->attributeTypes[$attributeId] ?? null;
    }
}
