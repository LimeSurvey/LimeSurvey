<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <script type="text/javascript">
        var url = "<?php echo site_url("admin/participants/getAttributeBox");?>";
        var attname = "<?php echo $clang->gT("Attribute Name:"); ?>";
        removeitem = new Array(); // Array to hold values that are to be removed from langauges option
         </script>
        <link rel="stylesheet" type="text/css" href="<?php echo site_url("styles/admin/default/adminstyle.css")?>" />
        <script src="<?php echo site_url("scripts/admin/admin_core.js")?>" type="text/javascript"></script>
        <script src="<?php echo site_url("scripts/jquery/jquery.js")?>" type="text/javascript"></script>
        <script src="<?php echo site_url("scripts/jquery/jquery-ui.js")?>" type="text/javascript"></script>
        <script src="<?php echo site_url("scripts/admin/viewAttribute.js")?>" type="text/javascript"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo site_url("styles/admin/default/participants.css")?>" />
    </head>
    <body>
        <div class='menubar-title ui-widget-header'><strong><center><?php echo $clang->gT("Attribute Settings"); ?></center> </strong></div><br><br>
        <?php
        $hidden = array();
         $attributeview = form_open('/admin/participants/saveAttribute/'.$this->uri->segment(4),"",$hidden);
        $plus = array('src' => 'images/plus.png',
                      'alt' => 'Add Language',
                      'title' => 'Add Language',
                      'id' => 'add',
                      'hspace' => 2,
                      'vspace' => -6   );
          $attributeview .= "<center><label for='langdata'>".$clang->gT('Add a Language:')."</label>";
          $options = array();
          $options[''] = "<---Select One---> ";
          foreach (getLanguageData () as $langkey2 => $langname)
          {
            $options[$langkey2] = $langname['description'];
          }
          $attributeview .= form_dropdown('langdata', $options, '','id="langdata"');
          $attributeview .= img($plus);
          $attributeview .= "</center><br></br><div id='tabs'><ul>";
          foreach($attributenames as $key=>$value)  {
          $attributeview .= "<li><a href=#".$value['lang'].">".$options[$value['lang']]."</a></li>";
          $attributeview .= "<script type='text/javascript'>removeitem.push('".$value['lang']."'); </script>";
          }
          $attributeview .= "</ul>";
          foreach($attributenames as $key=>$value)  {
          $attributeview .= "<div id=".$value['lang'].">";
          $attributeview .= "<p><center><label for='attname'>".$clang->gT('Attribute Name:')."</label>".form_input($value['lang'],$value['attribute_name'])."</p></div>";

          }
          $attributeview .= "</br></div></center>";
          $attributeview .= "<div class='menubar-title ui-widget-header'><strong><center>".$clang->gT("Common Settings")."</center> </strong></div>";
          $attributeview .= "<br><br><center><label for='atttype'>".$clang->gT('Attribute Type:')."</label>";
          $options = array('DD'  => 'Drop Down',
                           'DP' => 'Date',
                           'TB' => 'Text Box' );
          $attributeview .= form_dropdown('attribute_type', $options,$attributes->attribute_type,'id="attribute_type"');
          $attributeview .= "<br><br></center><center>";
          $attributeview .= "<label for='attvisible'>". $clang->gT('Attribute Visible:')."</label>";
          if($attributes->visible =="TRUE")
          {
            $attributeview .= form_checkbox('visible','TRUE',TRUE);
          }
          else
          {
            $attributeview .= form_checkbox('visible','TRUE',FALSE);
          }
          $hidden = array('visible' => 'FALSE');
          $attributeview .= "</center><br><center><br><div id='dd'><table id='ddtable' class='hovertable'>";
          $attributeview .= "<tr><th>Value Name</th></tr>";

          foreach($attributevalues as $row=>$value)
          {

            $attributeview .= "<tr><td><div class=editable id=".$value['value_id'].">";
            $attributeview .= $value['value']."</div></td><td>";
            $del = array( 'src' => 'images/error_notice.png',
                          'alt' => 'Delete',
                          'width' => '15',
                          'height' => '15',
                          'title' => 'Delete Atribute Value' );
            $edit = array(
            'src' => 'images/token_edit.png',
            'alt' => 'Edit',
            'width' => '15',
            'id' => 'edit',
            'name' => $value['value_id'],
            'height' => '15',
            'title' => 'Edit Atribute',
            );
            $attributeview .= img($edit);
            $attributeview .= anchor('admin/participants/delAttributeValues/'.$attributes->attribute_id.'/'.$value['value_id'],img($del));
            $attributeview .= "</td></tr>";
            }
            $attributeview .= "</table><a href='#' class='add'><img src = ".site_url('images/plus.png')." alt='Add Attribute' width='25' height='25' title='Add Attribute' id='add' name='add'></a>";
            $attributeview .= "</div><br><br>".form_submit('submit', 'Save');
            $attributeview .= form_close()."</center>";
            echo $attributeview;
            ?>
            </body>
</html>
