<?php
class remotecontrol extends Survey_Common_Controller {

    function __construct()
    {
        parent::__construct();
    }

    /**
    * This is the XML-RPC server routine
    *
    */
    function index()
    {
        $this->load->library('xmlrpc');
        $this->load->library('xmlrpcs');
        $this->load->model('sessions_model');


        $config['functions']['get_session_key'] = array('function' => 'remotecontrol.getSessionKey');
        $config['functions']['release_session_key'] = array('function' => 'remotecontrol.releaseSessionKey');
        $config['functions']['delete_survey'] = array('function' => 'remotecontrol.deleteSurvey');
        $config['functions']['create_survey'] = array('function' => 'remotecontrol.createSurvey');

        $this->xmlrpcs->initialize($config);
        $this->xmlrpcs->serve();
    }


    /**
    * XML-RPC routine to create a session key
    *
    * @param array $request Array containing username and password
    */
    function getSessionKey($request)
    {
        if (!is_object($request)) die();
        $parameters = $request->output_parameters();
        $sUserName=$parameters['0'];
        $sPassword=$parameters['1'];
        if ($this->_doLogin($sUserName,$sPassword))
        {
            $this->_jumpStartSession($sUserName);
            $sSessionKey=sRandomChars(64);
            $this->sessions_model->cleanSessions();
            $this->sessions_model->insertRecords(array('sesskey'=>$sSessionKey,
            'expiry'=>date_shift(date( 'Y-m-d H:i:s'), "Y-m-d H:i:s", '+'.$this->config->item('sess_expiration').' seconds'),
            'created'=>date( 'Y-m-d H:i:s'),
            'modified'=>date( 'Y-m-d H:i:s'),
            'sessdata'=>$sUserName));
            $response = array(array('sessionkey'  => $sSessionKey),'struct');
        }
        return $this->xmlrpc->send_response($response);
    }


    /**
    * Closes the RPC session
    *
    * @param mixed $request Array containing the session key as only element
    */
    function releaseSessionKey($request)
    {
        if (!is_object($request)) die();
        $parameters = $request->output_parameters();
        $sSessionKey=$parameters['0'];
        $this->db->delete('sessions', array('sesskey' => $sSessionKey));
        $this->sessions_model->cleanSessions();
        $response = array(array('status'  => 'OK'),'struct');
        return $this->xmlrpc->send_response($response);
    }


    /**
    * XML-RPC routine to delete a survey
    *
    * @param array $request Array containing username and password
    */
    function deleteSurvey($request)
    {
        if (!is_object($request)) die();
        $aParameters = $request->output_parameters();
        $sSessionKey=$aParameters['0'];
        if($this->_checkSessionKey($sSessionKey))
        {
            $iSurveyID=(int)$aParameters['1'];
            if(bHasSurveyPermission($surveyid,'survey','delete'))
            {
                $this->load->model('surveys_model');
                $this->surveys_model->deleteSurvey($iSurveyID);
                rmdirr($this->config->item("uploaddir").'/surveys/'.$iSurveyID);
                $response = array(array('status'  => 'OK'),'struct');
            }
            else
                $response = array(array('status'  => 'Failed'),'struct');
            return $this->xmlrpc->send_response($response);
        }
    }

    /**
    * Tries to login with username and password
    *
    * @param string $sUsername
    * @param mixed $sPassword
    */
    function _doLogin($sUsername, $sPassword)
    {
        $sUsername = sanitize_user($sUsername);
        $this->load->library('admin/sha256','sha256');
        $post_hash = $this->sha256->hashing($sPassword);

        $this->load->model("Users_model");

        $query = $this->Users_model->getAllRecords(array("users_name"=>$sUsername, 'password'=>$post_hash));

        if ($query->num_rows()==0)
        {
            $this->load->model("failed_login_attempts_model");
            $query = $this->failed_login_attempts_model->addAttempt($this->input->ip_address());
            return false;
        }
        else
        {
            return true;
        }

    }

    /**
    * Fills the session with necessary user info on the fly
    *
    * @param mixed $sUsername
    */
    function _jumpStartSession($sUsername)
    {

        $this->load->model("Users_model");
        $oQuery = $this->Users_model->getAllRecords(array("users_name"=>$sUsername));
        $aUserData = $oQuery->row_array();

        $session_data = array(
        'loginID' => intval($aUserData['uid']),
        'user' => $aUserData['users_name'],
        'full_name' => $aUserData['full_name'],
        'htmleditormode' => $aUserData['htmleditormode'],
        'templateeditormode' => $aUserData['templateeditormode'],
        'questionselectormode' => $aUserData['questionselectormode'],
        'dateformat' => $aUserData['dateformat'],
        'adminlang' => 'en'
        );
        $this->session->set_userdata($session_data);
        $this->_GetSessionUserRights($aUserData['uid']);
        return true;
    }


    /**
    * This function checks if the XML-RPC session key is valid. If yes returns true, otherwise false and sends a 'FAILED' responses
    *
    * @param mixed $sSessionKey
    */
    function _checkSessionKey($sSessionKey)
    {
        $this->sessions_model->cleanSessions();
        $oResult=$this->sessions_model->getAllRecords(array('sesskey'=>$sSessionKey));
        if($oResult->num_rows()==0)
        {
            $response = array(array('status'  => 'Failed'),'struct');
            $this->xmlrpc->send_response($response);
            return false;
        }
        else
        {
            $aRow=$oResult->row_array();
            $this->_jumpStartSession($aRow['sessdata']);
            return true;
        }
    }

    /**
    * Use this routine to test stuff
    *
    */
    function test()
    {
        $this->load->library('xmlrpc');
        // $this->xmlrpc->set_debug(TRUE);
        $this->xmlrpc->server(site_url('admin/remotecontrol'), 80);
        $this->xmlrpc->method('release_session_key');

        $request = array('zgn4phgzybs7j92rn89ayhvwrxu6pxp72acjgc9e8xe92fb95pvy72khfaffmtvv','12345');
        $this->xmlrpc->request($request);

        if ( ! $this->xmlrpc->send_request())
        {
            echo $this->xmlrpc->display_error();
        }
        else
            print_r($this->xmlrpc->display_response());
    }

}

