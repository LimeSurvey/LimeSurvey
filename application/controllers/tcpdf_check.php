<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Tcpdf_check extends CAction {

    public function run()
	{
        $this->index();
    }
    
    function index()
    {
        require_once(APPPATH.'/third_party/tcpdf/tcpdf.php');
        Yii::app()->getConfig("tcpdf");
        $pdf = new TCPDF();
        $pdf->SetHeaderData(
			Yii::app()->getConfig('header_logo'),
			Yii::app()->getConfig('header_logo_width'),
			Yii::app()->getConfig('header_title'),
			Yii::app()->getConfig('header_string')
		);

        // set document information
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set font
        $pdf->SetFont('times', 'BI', 16);

        // add a page
        $pdf->AddPage();

        // print a line using Cell()
        $pdf->Cell(0, 12, 'Example 001 - Watsup dude', 1, 1, 'C');

        //Close and output PDF document
        $pdf->Output('example_001.pdf', 'I');
    }
}