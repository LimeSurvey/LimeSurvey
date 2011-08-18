<?php
$data['clang'] = $clang;
$data['action'] = $action;
$this->load->view('admin/Survey/subview/tab_view',$data); ?>
<label for='language' title='<?php echo $clang->gT("This is the base language of your survey and it can't be changed later. You can add more languages after you have created the survey."); ?>'><span class='annotationasterisk'>*</span><?php echo $clang->gT("Base language:"); ?></label>
<select id='language' name='language'>
        <?php foreach (getLanguageData () as $langkey2 => $langname) { ?>
            <option value='<?php echo $langkey2; ?>'
            <?php if ($this->config->item('defaultlang') == $langkey2) { ?>
                 selected='selected'
            <?php } ?>
            ><?php echo $langname['description']; ?> </option>
        <?php } ?>


</select>

<?php $data['owner'] = $owner;
$this->load->view('admin/Survey/subview/tabGeneralNewSurvey_view',$data);
?>
