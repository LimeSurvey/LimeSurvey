<script type="text/javascript">
  var url = "<?php echo site_url("admin/participants/getAttributeBox");?>";
  var attname = "<?php echo $clang->gT("Attribute Name:"); ?>";
  removeitem = new Array(); // Array to hold values that are to be removed from langauges option
</script>
<script src="<?php echo $this->config->item('adminscripts')."admin_core.js" ?>" type="text/javascript"></script>
<script src="<?php echo $this->config->item('generalscripts')."jquery/jquery.js" ?>" type="text/javascript"></script>
<script src="<?php echo $this->config->item('generalscripts')."jquery/jquery-ui.js" ?>" type="text/javascript"></script>
<script src="<?php echo $this->config->item('adminscripts')."viewAttribute.js" ?>" type="text/javascript"></script>
<div class='header ui-widget-header'><strong><?php echo $clang->gT("Attribute Settings"); ?></strong></div><br/>
<?php
  $hidden = array();
  echo form_open('/admin/participants/saveAttribute/'.$this->uri->segment(4),"",$hidden);
  $plus = array('src'    => base_url()."images/plus.png",
                'alt'    => 'Add Language',
                'title'  => 'Add Language',
                'id'     => 'add',
                'hspace' => 2,
                'vspace' => -6);
?>
<div id="addlang"> 
  <?php echo $clang->gT('Add a Language:')?>
  <?php 
  $options = array();
  $options[''] = $clang->gT('<---Select One--->');
  foreach (getLanguageData () as $langkey2 => $langname)
  {
    $options[$langkey2] = $langname['description'];
  }
   echo form_dropdown('langdata', $options, '','id="langdata"');
   echo img($plus);
  ?>
 </div>
 <br/><br/>
 <div id='tabs'>
  <ul>
  <?php
  foreach($attributenames as $key=>$value)  
  {
  ?>
    <li>
      <a href="#<?php echo $value['lang']; ?>">
        <?php echo $options[$value['lang']] ?>
      </a>
    </li>
    <script type='text/javascript'>
      removeitem.push('<?php echo $value['lang']?>'); 
    </script>
  <?php
  }
  ?>
  </ul>
  <?php
  foreach($attributenames as $key=>$value)  
  {
  ?>
  <div id="<?php echo $value['lang']?>">
   <p>
        <label for='attname' id='attname'>
            <?php echo $clang->gT('Attribute Name:'); ?>
        </label>
        <?php echo form_input($value['lang'],$value['attribute_name']); ?>
   </p>
  </div>
  <?php
  }
  ?>
 <br/>
 </div>
 <div class='header ui-widget-header'>
    <strong>
        <?php echo $clang->gT("Common Settings"); ?>
    </strong>
 </div>
 <br/>
 <div id="comsettingdrop">
    <label for='atttype' id='atttype'>
      <?php echo $clang->gT('Attribute Type:'); ?>
    </label>
    <?php 
      $options = array('DD' => 'Drop Down',
                       'DP' => 'Date',
                       'TB' => 'Text Box');
      echo form_dropdown('attribute_type', $options,$attributes->attribute_type,'id="attribute_type"');
    ?>
    <br/><br/>
 </div>
 <div id="comsettingcheck">
 <label for='attvisible' id='attvisible'>
  <?php echo $clang->gT('Attribute Visible:') ?>
 </label>
  <?php
    if($attributes->visible =="TRUE")
    {
      echo form_checkbox('visible','TRUE',TRUE);
    }
    else
    {
      echo form_checkbox('visible','TRUE',FALSE);
    }
    $hidden = array('visible' => 'FALSE');
  ?>
 </div>
 <br/>
 <br/>
  <div id='dd'>
   <table id='ddtable' class='hovertable'>
     <tr>
       <th><?php echo $clang->gT('Value Name'); ?></th>
     </tr>
     <?php
     foreach($attributevalues as $row=>$value)
     {
     ?>
    <tr>
      <td>
        <div class=editable id="<?php echo $value['value_id'];?> ">
          <?php echo $value['value']; ?>
        </div>
      </td>
      <td>
      <?php
        $del = array( 'src'    => 'images/error_notice.png',
                      'alt'    => 'Delete',
                      'width'  => '15',
                      'height' => '15',
                      'title'  => 'Delete Atribute Value' );
        $edit = array('src' => 'images/token_edit.png',
                      'alt' => 'Edit',
                      'width' => '15',
                      'id' => 'edit',
                      'name' => $value['value_id'],
                      'height' => '15',
                      'title' => 'Edit Atribute');
        echo img($edit);
        echo anchor('admin/participants/delAttributeValues/'.$attributes->attribute_id.'/'.$value['value_id'],img($del));
      ?>
      </td>
    </tr>
    <?php  
    }
    ?>
   </table>
   <div id="plus">
   <a href='#' class='add'>
       <img src = "<?php echo base_url()?>images/plus.png" alt='Add Attribute' width='25' height='25' title='Add Attribute' id='addsign' name='addsign'>
   </a>
   </div>
   </div>
 <br/>
 <center>
   <?php
    echo form_submit('submit', 'Save');
    echo form_close()
   ?>
 </center>
 