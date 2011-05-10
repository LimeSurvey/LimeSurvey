<?php

require_once('tcpdf.php');

class MyPDF extends TCPDF
{
    function MyPDF($orientation='P', $unit='mm', $format='A4')
    {
        parent::__construct($orientation,$unit,$format);
        $this->SetAutoPageBreak(true,10);
        $this->AliasNbPages();
    }
    /**
     *
     * obsolete
     * @param $text
     * @param $format
     * @return unknown_type
     */
    function intopdf($text,$format='')
    {
        $text = $this->delete_html($text);
        $oldformat = $this->FontStyle;
        $this->SetFont('',$format,$this->FontSizePt);
        $this->Write(5,$text);
        $this->ln(5);
        $this->SetFont('',$oldformat,$this->FontSizePt);
    }
    /**
     *
     * obsolete
     * @param $text
     * @return unknown_type
     */
    function helptextintopdf($text)
    {
        $oldsize = $this->FontSizePt;
        $this->SetFontSize($oldsize-2);
        $this->Write(5,$this->delete_html($text));
        $this->ln(5);
        $this->SetFontSize($oldsize);
    }
    /**
     *
     * writes a big title in the page + description
     * @param $title
     * @param $description
     * @return unknown_type
     */
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
    /**
     *
     * Creates a Table with equal cell width and Bold text. Used as Head for equalTable()
     * @param $array(0=>)
     * @return unknown_type
     */
    function tablehead($array)
    {
        //$maxwidth = array();
        $maxwidth = $this->getEqualWidth($array);
        $oldStyle = $this->FontStyle;
        $this->SetFont($this->FontFamily, 'B', $this->FontSizePt);
        for($a=0;$a<sizeof($array);$a++)
        {
            for($b=0;$b<sizeof($array[$a]);$b++)
            {
                $this->Cell($maxwidth,4,$this->delete_html($array[$a][$b]),0,0,'L');
            }
            $this->ln();
        }
        $this->ln(5);
        $this->SetFont($this->FontFamily, $oldStyle, $this->FontSizePt);
    }
    /**
     *
     * Creates a Table with equal cell width.
     * @param $array - table array( 0=> array("td", "td", "td"),
     * 								1=> array("td", "td", "td"))
     * @param $modulo - fills each second row with a light-grey for better visibility. Default is on turn off with 0
     * @return unknown_type
     */
    function equalTable($array, $modulo=1)
    {
        //$maxwidth = array();
        $maxwidth = $this->getEqualWidth($array);
        $this->SetFillColor(220, 220, 220);
        for($a=0;$a<sizeof($array);$a++)
        {
            if($modulo){
                if($a%2 === 0){$fill=0;}
                else{$fill=1;}
            }
            else{$fill=0;}
            for($b=0;$b<sizeof($array[$a]);$b++)
            {
                 
                $this->Cell($maxwidth,4,$this->delete_html($array[$a][$b]),0,0,'L',$fill);

            }
            $this->ln();
        }
        $this->ln(5);
    }
    /**
     *
     * creates a table using the full width of page
     * @param $array - table array( 0=> array("td", "td", "td"),
     * 								1=> array("td", "td", "td"))
     * @param $modulo - fills each second row with a light-grey for better visibility. Default is off, turn on with 1
     * @return unknown_type
     */
    function tableintopdf($array, $modulo=1 )
    {
        $maxwidth = array();
        $maxwidth = $this->getFullWidth($array);

        $this->SetFillColor(220, 220, 220);
        for($a=0;$a<sizeof($array);$a++)
        {
            if($modulo){
                if($a%2 === 0){$fill=0;}
                else{$fill=1;}
            }
            else{$fill=0;}
            for($b=0;$b<sizeof($array[$a]);$b++)
            {
                //echo $maxwidth[$b]." max $b.Spalte<br/>";
                $this->Cell($maxwidth[$b],4,$this->delete_html($array[$a][$b]),0,0,'L',$fill);
            }
            $this->ln();
        }
        $this->ln(5);
    }
    /**
     *
     * creates a table with a bold head using the full width of page
     * @param $head - head array( 0=> array("th", "th", "th"))
     * @param $table - table array( 0=> array("td", "td", "td"),
     * 								1=> array("td", "td", "td"))
     * @param $modulo - fills each second row with a light-grey for better visibility. Default is on, turn off with 0
     * @return unknown_type
     */
    function headTable($head, $table, $modulo=1 )
    {
        $array = array_merge_recursive($head, $table);
        //print_r($array);
        $maxwidth = array();
        $maxwidth = $this->getFullWidth($array);

        $this->SetFillColor(220, 220, 220);
        for($a=0;$a<sizeof($array);$a++)
        {
            if($modulo){
                if($a%2 === 0){$fill=1;}
                else{$fill=0;}
            }
            else{$fill=0;}
            for($b=0;$b<sizeof($array[$a]);$b++)
            {
                if($a==0)
                {
                    $oldStyle = $this->FontStyle;
                    $this->SetFont($this->FontFamily, 'B', $this->FontSizePt);
                    
                    if ($maxwidth[$b] > 140) $maxwidth[$b]=130;
                    if ($maxwidth[$b] < 20) $maxwidth[$b]=20;
                    $this->MultiCell($maxwidth[$b],6,$this->delete_html($array[$a][$b]),0,'L',1,0);
                    
                    $this->SetFont($this->FontFamily, $oldStyle, $this->FontSizePt);
                }
                else
                {
                    if ($a==1)
                    {
                        $this->SetFillColor(250, 250, 250);
                    }
                    //echo $maxwidth[$b]." max $b.Spalte<br/>";

                    if ($maxwidth[$b] > 140) $maxwidth[$b]=130;
                    if ($b==0)
                    {
                        $iLines=$this->MultiCell($maxwidth[$b],6,$this->delete_html($array[$a][$b]),0,'L',$fill,0); 
                }
                    else
                    {
                       $this->MultiCell($maxwidth[$b],$iLines,$this->delete_html($array[$a][$b]),0,'L',$fill,0);   
            }

                }
            }
            $this->ln();
        }
        $this->ln(5);
    }
    function getminwidth($array)
    {
        $width = array();
        for($i=0;$i<sizeof($array);$i++)
        {
            for($j=0;$j<sizeof($array[$i]);$j++)
            {
                $stringWidth=0;
                $chars = str_split($this->delete_html($array[$i][$j]),1);
                foreach($chars as $char)
                {
                    $stringWidth = $stringWidth+$this->GetCharWidth($char);

                    //echo $stringWidth.": ".$char."<br/>";
                }
                if($stringWidth!=0 && $stringWidth<8)
                $stringWidth = $stringWidth*3;
                if(!isset($width[$j])|| $stringWidth>$width[$j])
                {
                    $width[$j] = $stringWidth;
                }
            }
        }
        return $width;
    }
    function getmaxwidth($array)
    {
        for($i=0;$i<sizeof($array);$i++)
        {
            for($j=0;$j<sizeof($array[$i]);$j++)
            {
                if(($i-1)>=0)
                {
                    if(strlen($this->delete_html($array[($i-1)][$j])) < strlen($this->delete_html($array[$i][$j])))
                    {
                        $width[$j] = strlen($this->delete_html($array[$i][$j]));
                    }
                }
                else
                {
                    $width[$j]=strlen($this->delete_html($array[$i][$j]));
                }
            }
        }
        return ($width);
    }
    /**
     *
     * Gets the width for columns in a table based on their Stringlength and the width of the page...
     * @param $array
     * @return array with column width
     */
    function getFullWidth($array)
    {
        $maxlength = array();
        $width = array();
        $width = $this->getminwidth($array);
         
        $margins = $this->getMargins();
        $deadSpace = $margins['left']+$margins['right'];
        $fullWidth = ($this->GetLineWidth()*1000)-$deadSpace;
        $faktor = $fullWidth/array_sum($width);

        for($i=0;$i<sizeof($width);$i++)
        {
            $maxlength[$i]=$faktor*$width[$i];
        }
        return $maxlength;
    }
    /**
     *
     * gets the width for each column in tables, based on pagewidth and count of columns.
     * Good for static tables with equal value String-length
     * @param $array
     * @return unknown_type
     */
    function getEqualWidth($array)
    {
        $margins = $this->getMargins();
        $deadSpace = $margins['left']+$margins['right'];

        $width = ($this->GetLineWidth()*1000)-$deadSpace;
        $count = 0;
        for($i=0;$i<sizeof($array);$i++)
        {
            for($j=0;$j<sizeof($array[$i]);$j++)
            {
                if(sizeof($array[$i])>$count)
                {
                    $count = sizeof($array[$i]);
                }
            }
        }

        if($count!=0)
        return ($width/$count);
        else
        return FALSE;
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
