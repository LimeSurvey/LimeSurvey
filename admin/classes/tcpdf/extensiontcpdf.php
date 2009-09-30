<?php

require_once('tcpdf.php');      

class PDF extends TCPDF
{    
    function PDF($orientation='L', $unit='mm', $format='A4')
    {
        parent::__construct($orientation,$unit,$format);
        $this->SetAutoPageBreak(true,10); 
        $this->AliasNbPages();
        
    }    
        
    function intopdf($text,$format='')
    {
        $text = $this->delete_html($text);
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
        $this->Write(5,$this->delete_html($text));
        $this->ln(5);
        $this->SetFontSize($oldsize);
    }   
    function titleintopdf($title,$description='')
    {
        if(!empty($title))
        {
            $title = $this->delete_html($title);
            $oldsize = $this->FontSizePt;
            $this->SetFontSize($oldsize+4);
            $this->Line(5,$this->y,($this->w-5),$this->y);
            $this->ln(3);
            $this->MultiCell('','',$title,'','C',0);
            if(!empty($description) && isset($description))
            {
                $description = $this->delete_html($description);
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
                $this->Cell($maxwidth[$b]*($this->FontSize),4,$this->delete_html($array[$a][$b]),0,0,'C');
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
    
    function delete_html($text)
    {
       $text = html_entity_decode($text);
       return strip_tags($text);
    }
}    
?>
