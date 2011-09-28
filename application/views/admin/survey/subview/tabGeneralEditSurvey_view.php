<?php
$data['clang'] = $clang;
$data['action'] = $action;
$this->load->view('admin/survey/subview/tab_view',$data);
$data['esrow'] = $esrow;
$data['surveyid'] = $surveyid;
$this->load->view('admin/survey/editSurvey_view',$data);
?>