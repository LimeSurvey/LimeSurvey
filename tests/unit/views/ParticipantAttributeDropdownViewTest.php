<?php

namespace ls\tests;

/**
 * The drop-down list of a custom attribute in the edit participant modal must keep
 * the value stored for the participant, even if it is no longer one of the options.
 */
class ParticipantAttributeDropdownViewTest extends TestBaseClass
{
    /**
     * A stored value that is one of the options is selected, and no extra option is added.
     * @return void
     */
    public function testStoredValueFromTheListIsSelected()
    {
        $options = $this->renderOptions('Lincoln Elementary', ['Washington High', 'Lincoln Elementary']);

        $this->assertSame(['', 'Washington High', 'Lincoln Elementary'], array_column($options, 'value'));
        $this->assertSame(['Lincoln Elementary'], $this->getSelectedValues($options));
    }

    /**
     * A stored value whose option was renamed or deleted is offered as an extra, selected option,
     * so saving the participant sends it again instead of an empty value.
     * @return void
     */
    public function testStoredValueMissingFromTheListIsKept()
    {
        $options = $this->renderOptions('Roosevelt High', ['Washington High', 'Lincoln Elementary']);

        $this->assertSame(
            ['', 'Washington High', 'Lincoln Elementary', 'Roosevelt High'],
            array_column($options, 'value')
        );
        $this->assertSame(['Roosevelt High'], $this->getSelectedValues($options));
        $this->assertSame(sprintf(gT('%s (no longer in the list)'), 'Roosevelt High'), $options[3]['label']);
    }

    /**
     * A participant without a value gets no extra option, and nothing is selected.
     * @return void
     */
    public function testEmptyValueAddsNoOption()
    {
        $options = $this->renderOptions('', ['Washington High', 'Lincoln Elementary']);

        $this->assertSame(['', 'Washington High', 'Lincoln Elementary'], array_column($options, 'value'));
        $this->assertSame([], $this->getSelectedValues($options));
    }

    /**
     * Values are compared as text: A stored "01" is not the option "1".
     * @return void
     */
    public function testNumericLookingValuesAreComparedAsText()
    {
        $options = $this->renderOptions('01', ['1', '2']);

        $this->assertSame(['', '1', '2', '01'], array_column($options, 'value'));
        $this->assertSame(['01'], $this->getSelectedValues($options));
    }

    /**
     * A stored value with HTML-special characters is output encoded, in the value and in the label.
     * @return void
     */
    public function testMissingValueIsEncoded()
    {
        $storedValue = "O'Neil & <Sons> \"High\"";
        $html = $this->renderDropdown($storedValue, ['Washington High']);
        $options = $this->parseOptions($html);

        $this->assertStringNotContainsString($storedValue, $html);
        $this->assertStringNotContainsString('<Sons>', $html);
        $this->assertSame([$storedValue], $this->getSelectedValues($options));
        $this->assertSame(sprintf(gT('%s (no longer in the list)'), $storedValue), $options[2]['label']);
    }

    /**
     * Renders the drop-down view and returns its options.
     *
     * @param string $storedValue Value stored for the participant
     * @param string[] $optionValues Drop-down options of the attribute
     * @return array<int, array{value: string, label: string, selected: bool}>
     */
    private function renderOptions($storedValue, array $optionValues)
    {
        return $this->parseOptions($this->renderDropdown($storedValue, $optionValues));
    }

    /**
     * Renders the drop-down view.
     *
     * @param string $storedValue Value stored for the participant
     * @param string[] $optionValues Drop-down options of the attribute
     * @return string HTML
     */
    private function renderDropdown($storedValue, array $optionValues)
    {
        $options = [];
        foreach ($optionValues as $index => $optionValue) {
            $options[] = ['value_id' => $index + 1, 'attribute_id' => 12, 'value' => $optionValue];
        }
        $controller = new \CController('test');
        return $controller->renderFile(
            APPPATH . 'views/admin/participants/modal_subviews/attributes/dropdown.php',
            [
                'name' => 'ea_12',
                'localizedname' => 'School',
                'value' => $storedValue,
                'options' => $options,
            ],
            true
        );
    }

    /**
     * Reads the options of the rendered drop-down list.
     *
     * @param string $html
     * @return array<int, array{value: string, label: string, selected: bool}>
     */
    private function parseOptions($html)
    {
        $document = new \DOMDocument();
        $this->assertTrue($document->loadHTML('<?xml encoding="UTF-8"><div>' . $html . '</div>'), 'The view is parsable HTML.');
        $options = [];
        foreach ($document->getElementsByTagName('option') as $option) {
            $options[] = [
                'value' => $option->getAttribute('value'),
                'label' => trim($option->textContent),
                'selected' => $option->hasAttribute('selected'),
            ];
        }
        return $options;
    }

    /**
     * Returns the values of the selected options.
     *
     * @param array<int, array{value: string, label: string, selected: bool}> $options
     * @return string[]
     */
    private function getSelectedValues(array $options)
    {
        $selectedValues = [];
        foreach ($options as $option) {
            if ($option['selected']) {
                $selectedValues[] = $option['value'];
            }
        }
        return $selectedValues;
    }
}
