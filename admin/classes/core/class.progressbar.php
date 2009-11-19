<?php
/*
*	Class ProgressBar
*
*	Author:		Gerd Weitenberg (hahnebuechen@web.de)
*	Date:		2005.03.09
*
*/

class ProgressBar
{
	// private vars
	
	var $code;	// unique code
	var $status = 'new';	// current status (new,show,hide)
	var $step = 0;	// current step
	var $position = array(); // current bar position
	
	// public vars
	
	var $min = 0;	// minimal steps
	var $max = 100;	// maximal steps
	
	var $left = 10;	// bar position from left
	var $top = 25;	// bar position from top
	var $width = 300;	// bar width
	var $height = 25;	// bar height
	var $pedding = 0;	// bar pedding
	var $color = '#0033ff';	// bar color
	var $bgr_color = '#c0c0c0';	// bar background color
	var $border = 1;			// bar border width
	var $brd_color = '#000000';	// bar border color
	
	var $direction = 'right';	// direction of motion (right,left,up,down)
	
	var $frame = array('show' => false);	// ProgressBar Frame
	/*	'show' => false,	# frame show (true/false)
		'left' => 200,	# frame position from left
		'top' => 100,	# frame position from top
		'width' => 300,	# frame width
		'height' => 75,	# frame height
		'color' => '#c0c0c0',	# frame color
		'border' => 2,		# frame border
		'brd_color' => '#dfdfdf #404040 #404040 #dfdfdf'	# frame border color
	*/
	
	var $label = array();	// ProgressBar Labels
	/*	'name' => array(	# label name
			'type' => 'text',	# label type (text,button,step,percent,crossbar)
			'value' => 'Please wait ...',	# label value
			'left' => 10,	# label position from left
			'top' => 20,	# label position from top
			'width' => 0,	# label width
			'height' => 0,	# label height
			'align' => 'left',	# label align
			'font-size' => 11,	# label font size
			'font-family' => 'Verdana, Tahoma, Arial',	# label font family
			'font-weight' => '',	#	label font weight
			'color' => '#000000',	#	label font color
			'bgr_color' => ''	# label background color
		)
	*/
	
	// constructor
	
	function ProgressBar($width=0,$height=0)
	{
		$this->code = substr(md5(microtime()), 0, 6);
		if ($width>0) {$this->width = $width;}
		if ($height>0) {$this->height = $height;}
	}
	
	// private functions
	
	function _calculatePercent($step)
	{
		$percent = round(($step - $this->min) / ($this->max - $this->min) * 100);
		if ($percent > 100) {$percent = 100;}
		return $percent;
	}
	
	function _calculatePosition($step)
	{
		switch ($this->direction) {
		case 'right':
		case 'left':
			$bar = $this->width;
			break;
		case 'down':
		case 'up':
			$bar = $this->height;
			break;
		}
    
		$pixel = round(($step - $this->min) * ($bar - ($this->pedding * 2)) / ($this->max - $this->min));
		if ($step <= $this->min) {$pixel = 0;}
		if ($step >= $this->max) {$pixel = $bar - ($this->pedding * 2);}
		
		switch ($this->direction) {
		case 'right':
			$position['left'] = $this->pedding;
			$position['top'] = $this->pedding;
			$position['width'] = $pixel;
			$position['height'] = $this->height - ($this->pedding * 2);
			break;
		case 'left':
			$position['left'] = $this->width - $this->pedding - $pixel;
			$position['top'] = $this->pedding;
			$position['width'] = $pixel;
			$position['height'] = $this->height - ($this->pedding * 2);
			break;
		case 'down':
			$position['left'] = $this->pedding;
			$position['top'] = $this->pedding;
			$position['width'] = $this->width - ($this->pedding * 2);
			$position['height'] = $pixel;
			break;
		case 'up':
			$position['left'] = $this->pedding;
			$position['top'] = $this->height - $this->pedding - $pixel;
			$position['width'] = $this->width - ($this->pedding * 2);
			$position['height'] = $pixel;
			break;
		}
		return $position;
	}
	
	function _setStep($step)
	{
		if ($step > $this->max) {$step = $this->max;}
		if ($step < $this->min) {$step = $this->min;}
		$this->step = $step;
	}
	
	// public functions
	
	function setFrame($width=0,$height=0)
	{
		$this->frame = array(
			'show' => true,
			'left' => 20,
			'top' => 35,
			'width' => 320,
			'height' => 90,
			'color' => '#c0c0c0',
			'border' => 2,
			'brd_color' => '#dfdfdf #404040 #404040 #dfdfdf'
		);
		
		if ($width>0) {$this->frame['width'] = $width;}
		if ($height>0) {$this->frame['height'] = $height;}
	}
	
	function addLabel($type,$name,$value='&nbsp;')
	{
		switch($type) {
		case 'text':
			$this->label[$name] = array(
				'type' => 'text',
				'value' => $value,
				'left' => $this->left,
				'top' => $this->top - 16,
				'width' => 0,
				'height' => 0,
				'align' => 'left',
				'font-size' => 11,
				'font-family' => 'Verdana, Tahoma, Arial',
				'font-weight' => 'normal',
				'color' => '#000000',
				'bgr_color' => ''
			);
			break;
		case 'button':
			$this->label[$name] = array(
				'type' => 'button',
				'value' => $value,
				'action' => '',
				'target' => 'self',
				'left' => $this->left,
				'top' => $this->top + $this->height + 10,
				'width' => 0,
				'height' => 0,
				'align' => 'center',
				'font-size' => 11,
				'font-family' => 'Verdana, Tahoma, Arial',
				'font-weight' => 'normal',
				'color' => '#000000',
				'bgr_color' => ''
			);
			break;
		case 'step':
			$this->label[$name] = array(
				'type' => 'step',
				'value' => $value,
				'left' => $this->left + 5,
				'top' => $this->top + 5,
				'width' => 10,
				'height' => 0,
				'align' => 'right',
				'font-size' => 11,
				'font-family' => 'Verdana, Tahoma, Arial',
				'font-weight' => 'normal',
				'color' => '#000000',
				'bgr_color' => ''
			);
			break;
		case 'percent':
			$this->label[$name] = array(
				'type' => 'percent',
				'value' => $value,
				'left' => $this->left + $this->width - 50,
				'top' => $this->top - 16,
				'width' => 50,
				'height' => 0,
				'align' => 'right',
				'font-size' => 11,
				'font-family' => 'Verdana, Tahoma, Arial',
				'font-weight' => 'normal',
				'color' => '#000000',
				'bgr_color' => ''
			);
			break;
		case 'crossbar':
			$this->label[$name] = array(
				'type' => 'crossbar',
				'value' => $value,
				'left' => $this->left + ($this->width / 2),
				'top' => $this->top - 16,
				'width' => 10,
				'height' => 0,
				'align' => 'center',
				'font-size' => 11,
				'font-family' => 'Verdana, Tahoma, Arial',
				'font-weight' => 'normal',
				'color' => '#000000',
				'bgr_color' => ''
			);
			break;
		}
	}
	
	function addButton($name,$value,$action,$target='self')
	{
		$this->addLabel('button',$name,$value);
		$this->label[$name]['action'] = $action;
		$this->label[$name]['target'] = $target;
	}
	
	function setLabelPosition($name,$left,$top,$width,$height,$align='')
	{
		$this->label[$name]['top'] = intval($top);
		$this->label[$name]['left'] = intval($left);
		$this->label[$name]['width'] = intval($width);
		$this->label[$name]['height'] = intval($height);
		if ($align!='') {$this->label[$name]['align'] = $align;}
		
		if ($this->status!='new') {
			echo '<script type="text/JavaScript">
            /* <![CDATA[ */
            document.getElementById("plbl'.$name.$this->code.'").style.top="'.$this->label[$name]['top'].'px";
            document.getElementById("plbl'.$name.$this->code.'").style.left="'.$this->label[$name]['left'].'px";
            document.getElementById("plbl'.$name.$this->code.'").style.width="'.$this->label[$name]['width'].'px";
            document.getElementById("plbl'.$name.$this->code.'").style.height="'.$this->label[$name]['height'].'px";
            document.getElementById("plbl'.$name.$this->code.'").style.align="'.$this->label[$name]['align'].'";
            /* ]]> */
            </script>'."\n";
			flush();
		}
	}
	
	function setLabelColor($name,$color)
	{
		$this->label[$name]['color'] = $color;
		if ($this->status!='new') {
			echo '<script type="text/JavaScript">
            /* <![CDATA[ */
            document.getElementById("plbl'.$name.$this->code.'").style.color="'.$color.'";
            /* ]]> */
            </script>'."\n";
			flush();
		}
	}
	
	function setLabelBackground($name,$color)
	{
		$this->label[$name]['bgr_color'] = $color;
		if ($this->status!='new') {
			echo '<script type="text/JavaScript">
            /* <![CDATA[ */
            document.getElementById("plbl'.$name.$this->code.'").style.background="'.$color.'";
            /* ]]> */
            </script>'."\n";
			flush();
		}
	}
	
	function setLabelFont($name,$size,$family='',$weight='')
	{
		$this->label[$name]['font-size'] = intval($size);
		if ($family!='') {$this->label[$name]['font-family'] = $family;}
		if ($weight!='') {$this->label[$name]['font-weight'] = $weight;}
		
		if ($this->status!='new') {
			echo '<script type="text/JavaScript">
            /* <![CDATA[ */                  
            document.getElementById("plbl'.$name.$this->code.'").style.font-size="'.$this->label[$name]['font-size'].'px";
            document.getElementById("plbl'.$name.$this->code.'").style.font-family="'.$this->label[$name]['font-family'].'";
            document.getElementById("plbl'.$name.$this->code.'").style.font-weight="'.$this->label[$name]['font-weight'].'";
            /* ]]> */             
            </script>'."\n";
			flush();
		}
	}
	
	function setLabelValue($name,$value)
	{
		$this->label[$name]['value'] = $value;
		if ($this->status!='new') {
			echo '<script type="text/JavaScript">
            /* <![CDATA[ */              
            PBlabelText'.$this->code.'("'.$name.'","'.$this->label[$name]['value'].'");
            /* ]]> */              
            </script>'."\n";
			flush();
		}
	}
	
	function setBarColor($color)
	{
		$this->color = $color;
		if ($this->status!='new') {
			echo '<script type="text/JavaScript">
            /* <![CDATA[ */                
            document.getElementById("pbar'.$this->code.'").style.background="'.$color.'";
            /* ]]> */            
            </script>'."\n";
			flush();
		}
	}
	
	function setBarBackground($color)
	{
		$this->bgr_color = $color;
		if ($this->status!='new') {
			echo '<script type="text/JavaScript">
            /* <![CDATA[ */            
            document.getElementById("pbrd'.$this->code.'").style.background="'.$color.'";
            /* ]]> */              
            </script>'."\n";
			flush();
		}
	}
	
	function setBarDirection($direction)
	{
		$this->direction = $direction;
		
		if ($this->status!='new') {
			$this->position = $this->_calculatePosition($this->step);
			
			echo '<script type="text/JavaScript">';
            echo '/* <![CDATA[ */';            
			echo 'PBposition'.$this->code.'("left",'.$this->position['left'].');';
			echo 'PBposition'.$this->code.'("top",'.$this->position['top'].');';
			echo 'PBposition'.$this->code.'("width",'.$this->position['width'].');';
			echo 'PBposition'.$this->code.'("height",'.$this->position['height'].');';
            echo '/* ]]> */';            
			echo '</script>'."\n";
			flush();
		}
	}
	
	function getHtml()
	{
		$html = '';
		$js = '';
		
		$this->_setStep($this->step);
		$this->position = $this->_calculatePosition($this->step);
		
		$style_brd = 'position:absolute;top:'.$this->top.'px;left:'.$this->left.'px;width:'.$this->width.'px;height:'.$this->height.'px;background:'.$this->bgr_color.';';
		if ($this->border>0) {$style_brd .= 'border:'.$this->border.'px solid;border-color:'.$this->brd_color.';';}
		
		$style_bar = 'position:absolute;top:'.$this->position['top'].'px;left:'.$this->position['left'].'px;'.'width:'.$this->position['width'].'px;height:'.$this->position['height'].'px;background:'.$this->color.';';
		
		if ($this->frame['show']==true) {
			if ($this->frame['border']>0) {$border = 'border:'.$this->frame['border'].'px solid;border-color:'.$this->frame['brd_color'].';';}
			$html = '<div id="pfrm'.$this->code.'" style="position:absolute;top:'.$this->frame['top'].'px;left:'.$this->frame['left'].'px;width:'.$this->frame['width'].'px;height:'.$this->frame['height'].'px;'.$border.'background:'.$this->frame['color'].';">'."\n";
		}
		
		$html .= '<div id="pbrd'.$this->code.'" style="'.$style_brd.'">'."\n";
		$html .= '<div id="pbar'.$this->code.'" style="'.$style_bar.'"></div></div>'."\n";
		
		$js .= 'function PBposition'.$this->code.'(item,pixel) {'."\n";
		$js .= ' pixel = parseInt(pixel);'."\n";
		$js .= ' switch(item) {'."\n";
		$js .= '  case "left": document.getElementById("pbar'.$this->code.'").style.left=(pixel) + \'px\'; break;'."\n";
		$js .= '  case "top": document.getElementById("pbar'.$this->code.'").style.top=(pixel) + \'px\'; break;'."\n";
		$js .= '  case "width": document.getElementById("pbar'.$this->code.'").style.width=(pixel) + \'px\'; break;'."\n";
		$js .= '  case "height": document.getElementById("pbar'.$this->code.'").style.height=(pixel) + \'px\'; break;'."\n";
		$js .= ' }'."\n";
		$js .= '}'."\n";
		
		foreach($this->label as $name => $data) {
			$style_lbl = 'position:absolute;top:'.$data['top'].'px;left:'.$data['left'].'px;text-align:'.$data['align'].';';
			if ($data['width']>0) {$style_lbl .= 'width:'.$data['width'].'px;';}
			if ($data['height']>0) {$style_lbl .= 'height:'.$data['height'].'px;';}
			
			if (array_key_exists('font-size', $data))	{$style_lbl .= 'font-size:'.$data['font-size'].'px;';}
			if (array_key_exists('font-family', $data)) {$style_lbl .= 'font-family:'.$data['font-family'].';';}
			if (array_key_exists('font-weight', $data)) {$style_lbl .= 'font-weight:'.$data['font-weight'].';';}
			if (array_key_exists('bgr_color', $data) && ($data['bgr_color']!='')) {$style_lbl .= 'background:'.$data['bgr_color'].';';}
			
			if (array_key_exists('type', $data)) {
				switch ($data['type']) {
				case 'text':
					$html .= '<div id="plbl'.$name.$this->code.'" style="'.$style_lbl.'">'.$data['value'].'</div>'."\n";
					break;
				case 'button':
					$html .= '<div><input id="plbl'.$name.$this->code.'" type="button" value="'.$data['value'].'" style="'.$style_lbl.'" onclick="'.$data['target'].'.location.href=\''.$data['action'].'\'" /></div>'."\n";
					break;
				case 'step':
					$html .= '<div id="plbl'.$name.$this->code.'" style="'.$style_lbl.'">'.$this->step.'</div>'."\n";
					break;
				case 'percent':
					$html .= '<div id="plbl'.$name.$this->code.'" style="'.$style_lbl.'">'.$this->_calculatePercent($this->step).'%</div>'."\n";
					break;
				case 'crossbar':
					$html .= '<div id="plbl'.$name.$this->code.'" style="'.$style_lbl.'">'.$data['value'].'</div>'."\n";
					
					$js .= 'function PBrotaryCross'.$name.$this->code.'() {'."\n";
					$js .= ' cross = document.getElementById("plbl'.$name.$this->code.'").firstChild.nodeValue;'."\n";
					$js .= ' switch(cross) {'."\n";
					$js .= '  case "--": cross = "\\\\"; break;'."\n";
					$js .= '  case "\\\\": cross = "|"; break;'."\n";
					$js .= '  case "|": cross = "/"; break;'."\n";
					$js .= '  default: cross = "--"; break;'."\n";
					$js .= ' }'."\n";
					$js .= ' document.getElementById("plbl'.$name.$this->code.'").firstChild.nodeValue = cross;'."\n";
					$js .= '}'."\n";
					break;
				}
			}
		}
		
		if(count($this->label)>0) {
			
			$js .= 'function PBlabelText'.$this->code.'(name,text) {'."\n";
			$js .= ' name = "plbl" + name + "'.$this->code.'";'."\n";
			$js .= ' document.getElementById(name).firstChild.nodeValue=text;'."\n";
			$js .= '}'."\n";
		}
		
		if ($this->frame['show']==true) {
			$html .= '</div>'."\n";
		}
		
		$html .= '<script type="text/JavaScript">'."\n";
        $html .= '/* <![CDATA[ */';         
		$html .= $js;
        $html .= '/* ]]> */';          
		$html .= '</script>'."\n";
		
		return $html;
	}
	
	function show()
	{
		$this->status = 'show';
		echo $this->getHtml();
		flush();
	}
	
	function moveStep($step)
	{
		$last_step = $this->step;
		$this->_setStep($step);
		
		$js = '';
    
		$new_position = $this->_calculatePosition($this->step);
		if ($new_position['width']!=$this->position['width'] && ($this->direction=='right' || $this->direction=='left')) {
			if ($this->direction=='left') {
				$js .= 'PBposition'.$this->code.'("left",'.$new_position['left'].');';
			}
		  $js .= 'PBposition'.$this->code.'("width",'.$new_position['width'].');';
		}
		if ($new_position['height']!=$this->position['height'] && ($this->direction=='up' || $this->direction=='down')) {
			if ($this->direction=='up') {
				$js .= 'PBposition'.$this->code.'("top",'.$new_position['top'].');';
			}
			$js .= 'PBposition'.$this->code.'("height",'.$new_position['height'].');';
		}
		$this->position = $new_position;
    	
		foreach($this->label as $name => $data) {
			if (array_key_exists('type', $data)) {
				switch($data['type']) {
				case 'step':
					if ($this->step!=$last_step) {
						$js .= 'PBlabelText'.$this->code.'("'.$name.'","'.$this->step.'/'.$this->max.'");';
					}
					break;
				case 'percent':
					$percent = $this->_calculatePercent($this->step);
					if ($percent!=$this->_calculatePercent($last_step)) {
						$js .= 'PBlabelText'.$this->code.'("'.$name.'","'.$percent.'%");';
					}
					break;
				case 'crossbar':
					$js .= 'PBrotaryCross'.$name.$this->code.'();';
					break;
				}
			}
		}
		if ($js!='') {
            echo '<script type="text/JavaScript">'."\n"
            .'/* <![CDATA[ */'."\n"
            . $js."\n"
            . '/* ]]> */'."\n";       
			flush ();
		}
	}
	
	function moveNext()
	{
		$this->moveStep($this->step + 1);
	}
	
	function moveMin()
	{
		$this->moveStep($this->min);
	}
	
	function hide()
	{
		if ($this->status=='show') {
			$this->status = 'hide';
			
			echo '<script type="text/JavaScript">document.getElementById("pbrd'.$this->code.'").style.visibility="hidden";document.getElementById("pbar'.$this->code.'").style.visibility="hidden";';
			if ($this->frame['show']==true) {
				echo 'document.getElementById("pfrm'.$this->code.'").style.visibility="hidden";';
			}
			foreach($this->label as $name => $data) {
				echo 'document.getElementById("plbl'.$name.$this->code.'").style.visibility="hidden";';
			}
			echo '</script>'."\n";
			flush();
		}
	}
	
	function unhide()
	{
		if ($this->status=='hide') {
			$this->status = 'show';
			
			echo '<script type="text/JavaScript">document.getElementById("pbrd'.$this->code.'").style.visibility="visible";document.getElementById("pbar'.$this->code.'").style.visibility="visible";';
			if ($this->frame['show']==true) {
				echo 'document.getElementById("pfrm'.$this->code.'").style.visibility="visible";';
			}
			foreach($this->label as $name => $data) {
				echo 'document.getElementById("plbl'.$name.$this->code.'").style.visibility="visible";';
			}
			echo '</script>'."\n";
			flush();
		}
	}
	
}
?>
