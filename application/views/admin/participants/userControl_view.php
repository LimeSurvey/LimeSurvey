<script src="<?php echo $this->config->item('adminscripts')."userControl.js" ?>" type="text/javascript"></script>
 <div class='header ui-widget-header'>
   <strong>
     <?php
      echo $clang->gT("Global Participant Settings"); 
     ?>
   </strong>
 </div>
 <div id='tabs'>
   <ul>
    <li>
      <a href='#usercontrol'>User Control</a>
    </li>
   </ul>
  <div id='usercontrol-1'>
   <?php
    if($this->session->userdata('USER_RIGHT_SUPERADMIN'))
    {
       $attribute = array('class' => 'form44');
       echo form_open('admin/participants/storeUserControlValues',$attribute);
       $options = array('Y'=>'Yes','N'=> 'No');
   ?>
  <ul>
   <li>
    <label for='userideditable' id='userideditable'>
     <?php echo $clang->gT('User ID editable : '); ?>
    </label>
     <?php echo form_dropdown('userideditable', $options,$userideditable); ?>
   </li>
  </ul>
  <p>
     <?php 
       echo form_submit('submit', 'Submit'); 
     ?>
  </p>
   <?php
      echo form_close();
     }
     else
     {  
        echo "<div class='messagebox ui-corner-all'>".$clang->gT("You don't have sufficient permissions")."</div>";
     }
   ?>
  </div>
 </div>