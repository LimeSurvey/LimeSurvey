<?php

class CustomFormatter extends CFormatter {

    public $longTextMaxLength = 50;

    // Build the expanded list of languages
	public function formatLanguageList($value) {

		$langArr = explode(' ', trim($value));
		$expandedArr = array();

		foreach($langArr as $lang) {
			array_push($expandedArr, getLanguageNameFromCode($lang, false));
		}

		sort($expandedArr);

		return implode(', ', $expandedArr);
	}

    /**
     * return a string limited by $this->maxLength
     * @param string|null $value
     * @return $string
     */
    public function formatLongText($value) {
        if (empty($value)) {
            return $value;
        }
        $originalvalue = $value = CHTML::encode($value);
        if (mb_strlen($value, 'UTF-8') > $this->longTextMaxLength) {
            $value = ellipsize($value, $this->longTextMaxLength);
        }
        $value = '<span class="longtext-content" data-toggle="tooltip" data-placement="left" title="' . $originalvalue . '">' . $value . '</span>';
        return $value;
    }

}
