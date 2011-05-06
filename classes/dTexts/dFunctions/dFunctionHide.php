<?php

class dFunctionHide implements dFunctionInterface
{
	public function __construct()
	{
	}
	
	public function run($args)
	{
		
		$funcName=array_shift($args);
		try
		{
			$func = dTexts::loadFunction($funcName);
			$newStr = $func->run($args);
			if(strtolower($newStr)=='true'){
				$id=time().rand(0,100);
				$hideJS=<<<EOF
		<div id="hide_$id" style="display:none;"/>
		<script	type="text/javascript">
		 	var elem = $('#hide_$id').parent();
		 	if(elem.is("li")){
				elem.css('display','none');
			}else{
				elem = elem.parent();
				if(elem.is("li")){
					elem.css('display','none');
				}else{
					elem = elem.parent();
					if(elem.is("li")){
						elem.css('display','none');
					}
				}			
			}
			
			
		</script>
EOF;
			return $hideJS;			
			}
		}
		catch(Exception $e)
		{
			throw $e;
		}
		return '';
		
	}
}
