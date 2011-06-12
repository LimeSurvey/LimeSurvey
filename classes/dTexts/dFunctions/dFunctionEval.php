<?php

include_once('classes/eval/ExpressionManager.php');

class dFunctionEval implements dFunctionInterface
{
    private $knownVars;
    private $knownReservedWords;
    private $em;

	public function __construct()
	{
	}
	
	public function run($args)
	{
		global $connect;
		$expr = htmlspecialchars_decode($args[0]);
		if (isset($_SESSION['srid'])) $srid = $_SESSION['srid'];    // what is this for?
        $em = $this->getExpressionManager();
        $status = $em->Evaluate($expr);
        $errs = $em->GetReadableErrors();
        $result = $em->GetResult();
		return $result;
	}

    private function getReservedWordArray()
    {
        if (isset($this->knownReservedWords)) {
            return $this->knownReservedWords;
        }
        $this->createVarArrays();
        return $this->knownReservedWords();
    }
    
    /**
     * Return full list of variable names and values.
     * TODO:  Is there an existing function that does this?
     * TODO:  Want to only call this once per page refresh
     * @return array
     */

    private function getVarArray()
    {
		if (isset($this->knownVars)) {
            return $this->knownVars;
        }
        $this->createVarArrays();
        return $this->knownVars;
    }

    private function createVarArrays()
    {
        $sid = returnglobal('sid');
        $fieldmap=createFieldMap($sid,$style='full');
        $knownVars = array();   // mapping of VarName to Value
        $knownSGQAs = array();  // mapping of SGQA to Value
        if (isset($fieldmap))
        {
            foreach($fieldmap as $fielddata)
            {
                $value = retrieve_Answer($fielddata['fieldname'], $_SESSION['dateformats']['phpdate']);
                $knownVars[$fielddata['title']] = $value;
                $knownVars[$fielddata['fieldname']] = $value;
                $knownSGQAs['INSERTANS:' . $fielddata['fieldname']] = $value;
            }
        }
        $this->knownVars = $knownVars;
        $this->knownReservedWords= $knownSGQAs;
    }

    /**
     * Goal is to create Expression Manager once per page refresh.
     * @return <type>
     */

    private function getExpressionManager()
    {
        if (isset($this->em))
        {
            return $this->em;
        }

        $em = new ExpressionManager();
        $varArray = $this->getVarArray();
        $em->RegisterVarnames($varArray);
        $em->RegisterReservedWords($this->getReservedWordArray());
        $this->em  = $em;
        return $this->em;
    }
}
