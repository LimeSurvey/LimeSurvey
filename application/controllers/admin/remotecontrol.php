<?php
class remotecontrol extends Survey_Common_Controller {

    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        //        debugbreak();
        $this->load->library('xmlrpc');
        $this->load->library('xmlrpcs');

     //   $config['functions']['get_session_key'] = array('function' => 'remotecontrol.getSessionKey');
     //   $config['functions']['release_session_key'] = array('function' => 'remotecontrol.releaseSessionKey');
        $config['functions']['delete_survey'] = array('function' => 'admin/Survey._xmlrpc_deleteSurvey');
        $config['object'] = $this;

        $this->xmlrpcs->initialize($config);
        $this->xmlrpcs->serve();
    }

    function getSessionKey($sUsername,$sPassword)
    {

    }



    function test()
    {
        $this->load->library('xmlrpc');
        //$this->xmlrpc->set_debug(TRUE);
        $this->xmlrpc->server('http://localhost/limesurvey_ci/index.php/admin/remotecontrol', 80);
        $this->xmlrpc->method('delete_survey');

        $request = array(49832);
        $this->xmlrpc->request($request);

        if ( ! $this->xmlrpc->send_request())
        {
            echo $this->xmlrpc->display_error();
        }
        else
            print_r($this->xmlrpc->display_response());
    }
}