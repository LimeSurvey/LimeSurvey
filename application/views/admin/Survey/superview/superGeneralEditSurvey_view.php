<?php 
$data['clang'] = $clang;
$this->load->view('admin/survey/subview/tab_view',$data); 
$data['esrow'] = $esrow;
$data['surveyid'] = $surveyid;
$this->load->view('admin/survey/subview/tabGeneralEditSurvey_view',$data); 
?>
