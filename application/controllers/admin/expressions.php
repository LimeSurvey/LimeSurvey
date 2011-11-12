<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Expressions extends Admin_Controller {
	function __construct()
	{
		parent::__construct();
	}

    function index()
	{
		$this->load->view('admin/expressions/test');
	}

    function test($which)
    {
        $this->load->view('admin/expressions/test/'.$which);
    }
}

/* End of file expressions.php */
/* Location: ./application/controllers/admin/expressions.php */