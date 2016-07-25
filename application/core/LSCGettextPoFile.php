<?php

class LSCGettextPoFile extends CGettextPoFile
{
	/**
	 * Loads messages from a PO file.
	 * @param string $file file path
	 * @param string $context message context
	 * @return array message translations (source message => translated message)
	 */
	public function load($file,$context)
	{
		$pattern='/(msgctxt\s+"(.*?(?<!\\\\))")?\s+'
			.'msgid\s+((?:".*(?<!\\\\)"\s*)+)\s+'
			.'msgstr\s+((?:".*(?<!\\\\)"\s*)+)/';
		$matches=array();
		$n=preg_match_all($pattern,file_get_contents($file),$matches);

		$messages=array();
		for($i=0; $i<$n; $i++)
		{
			//if($matches[2][$i]===$context)
			//{
				$id=$this->decode($matches[3][$i]);
				$message=$this->decode($matches[4][$i]);
				$messages[$id]=$message;
			//}
		}
		return $messages;
	}

}
