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
        $config['functions']['add_participants'] = array('function' => 'remotecontrol.addParticipants');
        //        $config['functions']['create_survey'] = array('function' => 'remotecontrol.createSurvey');

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
            return $this->xmlrpc->send_response(array($sSessionKey,'string'));
        }
        else
        {
            return $this->xmlrpc->send_error_message('1', 'Login failed');
        }
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
        return $this->xmlrpc->send_response(array('OK','array'));
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
                return $this->xmlrpc->send_response($response);
            }
            else
                return $this->xmlrpc->send_error_message('2', 'No permission');
        }
    }

    /**
    * XML-RPC routine to add a participant to a token table
    * Returns the inserted data including additional new information like the Token entry ID and the token
    *
    * @param array $request Array containing the following elements (in that order):
    * - Session key (string)
    * - Survey ID (integer)
    * - ParticipantData (array)
    * - CreateToken (boolean)  Sets if a token should be created for each ParticipantData record
    *
    *
    */
    function addParticipants($request)
    {
        if (!is_object($request)) die();
        $aParameters = $request->output_parameters();

        if (!isset($aParameters['0'],$aParameters['1'],$aParameters['2'],$aParameters['3']))
        {
            return $this->xmlrpc->send_error_message('3', 'Missing parameters');
        }
        $sSessionKey=$aParameters['0'];
        $iSurveyID=(int)$aParameters['1'];
        $aParticipantData=$aParameters['2'];
        $bCreateTokenKey=$aParameters['3'];

        if($this->_checkSessionKey($sSessionKey))
        {
            if(bHasSurveyPermission($iSurveyID,'tokens','create'))
            {
                if (!$this->db->table_exists('tokens_'.$iSurveyID))
                {
                    return $this->xmlrpc->send_error_message('11', 'No token table');
                }
                $aFieldnames=$this->db->list_fields('tokens_'.$iSurveyID);
                $aFieldnames=array_flip($aFieldnames);
                $this->load->model("tokens_dynamic_model");
                foreach ($aParticipantData as &$aParticipant)
                {
                    Foreach ($aParticipant as $sFieldname=>$sValue)
                    {
                        if (!isset($aFieldnames[$sFieldname])) unset($aParticipant[$sFieldname]);
                    }
                    if ($this->tokens_dynamic_model->insertToken($iSurveyID,$aParticipant))
                    {
                        $iNewTokenEntryID=$this->db->insert_id();
                       $aParticipant=array_merge($aParticipant, array('tid'=>(string)$iNewTokenEntryID,
                                                                      'token'=>$this->tokens_dynamic_model->createToken($iSurveyID,$iNewTokenEntryID))
                       );
                    };
                }
                $iTokensInserted=$this->db->affected_rows();
                $aOutArray=array(array(array($this->array_to_xml_rpc_struct($aParticipantData),'struct')),'struct');
                return $this->xmlrpc->send_response($aOutArray);
            }
            else
                return $this->xmlrpc->send_error_message('2', 'No permission');
        }
    }

    /**
    * Converts a result_array() response set to a XLMRPC struct array
    *
    * @param mixed $array Array to convert
    */
    function array_to_xml_rpc_struct($array)
    {

        $xml_rpc_rows=array();
        for ($i=0;$i<count($array);++$i)
        {
           $xml_rpc_rows[$i]=array($array[$i],'struct');
        }
        return $xml_rpc_rows;
    }


    /**
    * Tries to login with username and password
    *
    * @param string $sUsername
    * @param mixed $sPassword
    */
    function _doLogin($sUsername, $sPassword)
    {
        $this->load->model("failed_login_attempts_model");
        if ($this->failed_login_attempts_model->isLockedOut($this->input->ip_address()))
        {
            return false;
        }

        $sUsername = sanitize_user($sUsername);
        $this->load->library('admin/sha256','sha256');
        $post_hash = $this->sha256->hashing($sPassword);

        $this->load->model("Users_model");
        $query = $this->Users_model->getAllRecords(array("users_name"=>$sUsername, 'password'=>$post_hash));

        if ($query->num_rows()==0)
        {
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
    * This function checks if the XML-RPC session key is valid. If yes returns true, otherwise false and sends an error message with error code 1
    *
    * @param mixed $sSessionKey
    */
    function _checkSessionKey($sSessionKey)
    {
        $this->sessions_model->cleanSessions();
        $oResult=$this->sessions_model->getAllRecords(array('sesskey'=>$sSessionKey));
        if($oResult->num_rows()==0)
        {
            $this->xmlrpc->send_error_message('1', 'Invalid session key');
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
        $iSurveyID=552489;
        $aParticipantsData=array(
        array(
        array(array('firstname'=>'firstname1','lastname'=>'lastname1','dummy'=>'lastname1'),'struct'),
        array(array('firstname'=>'firstname2','lastname'=>'lastname2'),'struct'),
        )
        ,'array');



        $this->load->library('xmlrpc');
       // $this->xmlrpc->set_debug(TRUE);
        $this->xmlrpc->server(site_url('admin/remotecontrol'), 80);


        $this->xmlrpc->method('get_session_key');
        $request = array('admin','password');
        $this->xmlrpc->request($request);

        if ( ! $this->xmlrpc->send_request())
        {
            echo $this->xmlrpc->display_error();
        }
        else
        {
            $sSessionKey=($this->xmlrpc->display_response());
        }

        $this->xmlrpc->method('add_participants');
        $request = array(array($sSessionKey,'string'),array($iSurveyID,'integer'),$aParticipantsData, array(true,'boolean'));
        $this->xmlrpc->request($request,'struct');

        if ( ! $this->xmlrpc->send_request())
        {
            echo $this->xmlrpc->display_error();
        }
        else
            var_dump($this->xmlrpc->display_response());

    }

}

