<?php

class LSCGettextMoFile extends CGettextMoFile
{
	/**
	 * Loads messages from an MO file.
	 * @param string $file file path
	 * @param string $context message context
	 * @return array message translations (source message => translated message)
	 */
	public function load($file,$context)
	{
		if(!($fr=@fopen($file,'rb')))
			throw new CException(Yii::t('yii','Unable to read file "{file}".',
				array('{file}'=>$file)));

		if(!@flock($fr,LOCK_SH))
			throw new CException(Yii::t('yii','Unable to lock file "{file}" for reading.',
				array('{file}'=>$file)));

		$array=unpack('c',$this->readByte($fr,4));
		$magic=current($array);
		if($magic==-34)
			$this->useBigEndian=false;
		elseif($magic==-107)
			$this->useBigEndian=true;
		else
			throw new CException(Yii::t('yii','Invalid MO file: {file} (magic: {magic}).',
				array('{file}'=>$file,'{magic}'=>$magic)));

		if(($revision=$this->readInteger($fr))!=0)
			throw new CException(Yii::t('yii','Invalid MO file revision: {revision}.',
				array('{revision}'=>$revision)));

		$count=$this->readInteger($fr);
		$sourceOffset=$this->readInteger($fr);
		$targetOffset=$this->readInteger($fr);

		$sourceLengths=array();
		$sourceOffsets=array();
		fseek($fr,$sourceOffset);
		for($i=0;$i<$count;++$i)
		{
			$sourceLengths[]=$this->readInteger($fr);
			$sourceOffsets[]=$this->readInteger($fr);
		}

		$targetLengths=array();
		$targetOffsets=array();
		fseek($fr,$targetOffset);
		for($i=0;$i<$count;++$i)
		{
			$targetLengths[]=$this->readInteger($fr);
			$targetOffsets[]=$this->readInteger($fr);
		}

		$messages=array();
		for($i=0;$i<$count;++$i)
		{
			$id=$this->readString($fr,$sourceLengths[$i],$sourceOffsets[$i]);
			$pos = strpos($id,chr(4));

			//if(($context && $pos!==false && substr($id,0,$pos)===$context) || (!$context && $pos===false))
			//{
				if($pos !== false)
					$id=substr($id,$pos+1);

				$message=$this->readString($fr,$targetLengths[$i],$targetOffsets[$i]);
				$messages[$id]=$message;
			//}
		}

		@flock($fr,LOCK_UN);
		@fclose($fr);

		return $messages;
	}
}
