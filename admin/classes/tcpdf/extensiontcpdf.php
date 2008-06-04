<?php

require_once('checkphpversion.php');

class PDF extends TCPDF
{    
    function PDF($orientation='L', $unit='mm', $format='A4')
    {
        if (version_compare(PHP_VERSION, '5.0.0', '<') && version_compare(PHP_VERSION, '4.0.0', '>'))
        {
//          require_once('tcpdf_php4.php');
          parent::TCPDF($orientation,$unit,$format);
        }
        else if(version_compare(PHP_VERSION, '5.0.0', '>'))
        {
//          require_once('tcpdf.php');
          parent::__construct($orientation,$unit,$format);
        }
        $this->SetAutoPageBreak(true,10); 
        $this->AliasNbPages();
        
    }    
        
    function intopdf($text,$format='')
    {
        $oldformat = $this->FontStyle;
        $this->SetFont('',$format,$this->FontSizePt);
        $this->Write(5,$text);
        $this->ln(5);
        $this->SetFont('',$oldformat,$this->FontSizePt);
    }
    function helptextintopdf($text)
    {
        $oldsize = $this->FontSizePt;
        $this->SetFontSize($oldsize-2);
        $this->Write(5,$text);
        $this->ln(5);
        $this->SetFontSize($oldsize);
    }   
    function titleintopdf($title,$description='')
    {
        if(!empty($title))
        {
            $oldsize = $this->FontSizePt;
            $this->SetFontSize($oldsize+4);
            $this->Line(5,$this->y,($this->w-5),$this->y);
            $this->ln(3);
            $this->MultiCell('','',$title,'','C',0);
            if(!empty($description) && isset($description))
            {
                $this->ln(7);
                $this->SetFontSize($oldsize+2);
                $this->MultiCell('','',$description,'','C',0);
                $this->ln(2);
            }
            else
            {
                $this->ln(4);
            }
            $this->Line(5,$this->y,($this->w-5),$this->y);
            $this->ln(5);
            $this->SetFontSize($oldsize);
        }
    }
    function tableintopdf($array)
    {      
        $maxwidth = array();
        $maxwidth = $this->getmaxwidth($array);        
        for($a=0;$a<sizeof($array);$a++)
        {
            for($b=0;$b<sizeof($array[$a]);$b++)
            {
                $this->Cell($maxwidth[$b]*($this->FontSize),4,$array[$a][$b],0,0,'C');
            }
            $this->ln();
        }
        $this->ln(5);
    }
    function getmaxwidth($array)
    {  
        for($i=0;$i<sizeof($array);$i++)
        {
            for($j=0;$j<sizeof($array[$i]);$j++)
            {
                if(($i-1)>=0)
                {
                     if(strlen($array[($i-1)][$j]) < strlen($array[$i][$j]))
                     {
                        $width[$j] = strlen($array[$i][$j]);
                     }
                }
                else
                {
                    $width[$j]=strlen($array[$i][$j]);
                }
            }
        }
        return ($width);
    }
    function write_out($name)
    {      
        $this->Output($name,"D");
    }
}    
?>
