<?php

// Build the expanded list of languages
class CustomFormatter extends CFormatter {
	public function formatLanguageList($value) {

		$langArr = explode(' ', trim($value));
		$expandedArr = array();

		foreach($langArr as $lang) {
			array_push($expandedArr, getLanguageNameFromCode($lang, false));
		}

		sort($expandedArr);

		return implode(', ', $expandedArr);
	}
}

?>