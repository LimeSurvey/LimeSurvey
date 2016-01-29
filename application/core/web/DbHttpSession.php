<?php

class DbHttpSession extends \CDbHttpSession
{

	/**
	 * Session write handler.
	 * Do not call this method directly.
	 * @param string $id session ID
	 * @param string $data session data
	 * @return boolean whether session write is successful
	 */
	public function writeSession($id,$data)
	{
		// exception must be caught in session write handler
		// http://us.php.net/manual/en/function.session-set-save-handler.php
		try
		{
			$expire=time()+$this->getTimeout();
			$db=$this->getDbConnection();
			if($db->getDriverName()=='pgsql' )
				$data=new CDbExpression($db->quoteValueExtended($data, PDO::PARAM_LOB)."::bytea");
			if($db->getDriverName()=='sqlsrv' || $db->getDriverName()=='mssql' || $db->getDriverName()=='dblib')
				$data=new CDbExpression('CONVERT(VARBINARY(MAX), '.$db->quoteValue($data).')');
			if($db->createCommand()->select('id')->from($this->sessionTableName)->where('id=:id',array(':id'=>$id))->queryScalar()===false)
				$db->createCommand()->insert($this->sessionTableName,array(
					'id'=>$id,
					'data'=>$data,
					'expire'=>$expire,
				));
			else
				$db->createCommand()->update($this->sessionTableName,array(
					'data'=>$data,
					'expire'=>$expire
				),'id=:id',array(':id'=>$id));
		}
		catch(Exception $e)
		{
			if(YII_DEBUG)
				echo $e->getMessage();
			// it is too late to log an error message here
			return false;
		}
		return true;
	}
}
