<?php
/**
 * This class is responsible for translating tags (like {INSERTANS..., {IF
 * @author lime-nk Michal Kurzeja
 */
class dTexts
{
	/**
	 * This metod translates the given text and returns it
	 * @param $text
	 * @return String
	 */
	public static function run($text)
	{
        $text = insertansReplace($text);
        $text = tokenReplace($text);    // ISSUE - how should anonymized be passed to this function?
		return $text;
	}
}
