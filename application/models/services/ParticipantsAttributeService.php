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
 */
class ParticipantsAttributeService
{
    private ParticipantAttributeName $modelParticipantAttributeName;
    private ParticipantAttributeNameLang $modelParticipantAttributeLang;
    private ParticipantAttributeValue $modelParticipantAttributeValue;

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
}
