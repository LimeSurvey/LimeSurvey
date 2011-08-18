<?php
$data['clang'] = $clang;
$data['action'] = $action;
$this->load->view('admin/Survey/subview/tab_view',$data);
$data['esrow'] = $esrow;
$data['surveyid'] = $surveyid;
$this->load->view('admin/Survey/subview/tabGeneralEditSurvey_view',$data);