<?php
    $attrfieldnames=getTokenFieldsAndNames($surveyid,true);
?>

<div class="panel panel-primary" id="panel-7">
  <div class="panel-heading">
    <div class="panel-title h4">
      <?php eT("Token control");?>
    </div>
  </div>
  <div class="panel-body">
    <div class="alert alert-info alert-dismissible" role="alert">
      <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span>&times;</span></button>
      <?php eT('Your survey can export associated token data with each response. Select any additional fields you would like to export.'); ?>
    </div>

    <label for='attribute_select' class="col-sm-4 control-label">
      <?php eT("Choose token fields:");?>
    </label>
    <div class="col-sm-8">
      <select name='attribute_select[]' multiple size='20' class="form-control" id="attribute_select">
        <option value='first_name' id='first_name'>
          <?php eT("First name");?>
        </option>
        <option value='last_name' id='last_name'>
          <?php eT("Last name");?>
        </option>
        <option value='email_address' id='email_address'>
          <?php eT("Email address");?>
        </option>

        <?php 
            foreach ($attrfieldnames as $attr_name=>$attr_desc)
            {
                echo "<option value='$attr_name' id='$attr_name' />".$attr_desc['description']."</option>\n";
            } 
        ?>
      </select>
    </div>
  </div>
</div>
