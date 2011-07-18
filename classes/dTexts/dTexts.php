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
        return LimeExpressionManager::ProcessString($text,NULL,true);
	}
}
