<?php
/**
 * This class is responsible for translating tags (like {INSERTANS..., {IF
 * @author lime-nk Michal Kurzeja
 */
class Dtexts
{
	/**
	 * This metod translates the given text and returns it
	 * @param $text
	 * @return String
	 */
	public static function run($text,$questionNum=NULL)
	{
        return LimeExpressionManager::ProcessString($text,$questionNum,NULL,true);
	}
}
