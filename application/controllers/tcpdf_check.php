<?php if ( ! defined('BASEPATH')) die('No direct script access allowed');

class Tcpdf_check extends CI_Controller {

    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        $this->load->library('admin/pdf');
        require ($this->config->item('homedir').'application/config/tcpdf_config_ci.php');
        $this->_config = $tcpdf;
        $this->pdf->SetHeaderData(
			$this->_config['header_logo'],
			$this->_config['header_logo_width'],
			$this->_config['header_title'],
			$this->_config['header_string']
		);

        // set document information
        $this->pdf->SetSubject('TCPDF Tutorial');
        $this->pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        // set font
        $this->pdf->SetFont('times', 'BI', 16);

        // add a page
        $this->pdf->AddPage();

        // print a line using Cell()
        $this->pdf->Cell(0, 12, 'Example 001 - Watsup dude', 1, 1, 'C');

        //Close and output PDF document
        $this->pdf->Output('example_001.pdf', 'I');
    }
}