<?php

// Build the expanded list of languages
class CustomFormatter extends CFormatter {

	public $maxLength = 125;

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
	 * @param $value
	 * @deprecated in 3.6.2
	 * @return $string
	 */
	public function formatLongText($value) {
		$value = Chtml::encode($value);
		if(strlen($value) > $this->maxLength) {
			$truncated = substr($value, 0, $this->maxLength-3);
			return trim($truncated)."...";
		}
		return $value;
	}
}
