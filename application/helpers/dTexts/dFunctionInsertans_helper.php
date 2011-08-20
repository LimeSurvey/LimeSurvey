<?php

class dFunctionInsertAns
{
    public function __construct()
    {
    }

    public function run($args)
    {
        //global $connect;
        $field = $args[0];
        if ($this->session->userdata('srid')) $srid = $this->session->userdata('srid');
        $sid = returnglobal('sid');
        $dateformats = $this->session->userdata('dateformats');
        return retrieve_Answer($field, $dateformats['phpdate']);
    }
}
