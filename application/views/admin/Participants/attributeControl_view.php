<html>
    <head>
        <script src="<?php echo $this->config->item('generalscripts')."jquery/jquery.js" ?>" type="text/javascript"></script>
        <script src="<?php echo $this->config->item('generalscripts')."jquery/jquery-ui.js" ?>" type="text/javascript"></script>
         <script src="<?php echo $this->config->item('adminscripts')."attributeControl.js" ?>" type="text/javascript"></script>
        <link rel="stylesheet" type="text/css" href="<?php echo $this->config->item('styleurl')."admin/default/participants.css" ?>" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
         <div class='header ui-widget-header'>
        <strong><?php echo $clang->gT("Attribute Control"); ?> </strong></div>
        <?php
        $attribute = array('class' => 'form44');
        echo form_open('/admin/participants/storeAttributes',$attribute);?>
        <br></br>
        <ul><li><table id='atttable'class='hovertable'>
            <tr>
                <th>Attribute Name</th>
                <th>Attribute Type</th>
                <th>Visible in participant panel</th>
                <th>Actions </th>
            </tr>
                    <?php

        foreach($result as $row=>$value)
        {
            echo "<tr>";
            echo "<td>";
            echo $value['attribute_name'];
            echo "</td>";
            echo "<td>";
            if($value['attribute_type']=='DD')
                {
                    echo $clang->gT("Drop Down");
                }
            elseif($value['attribute_type']=='DP')
                {
                    echo $clang->gT("Date");
                }
            else
                {
                    echo $clang->gT("Text Box");
                }
            echo "</td><td>";
            if($value['visible']=="TRUE")
            {
            $data = array(
                'name'        => 'visible',
                'id'          => 'visible',
                'checked'     => TRUE,
                'disabled'    => 'disabled'
                );
            }
            else
            {$data = array(
                'name'        => 'visible',
                'id'          => 'visible',
                'checked'     => FALSE,
                'disabled'    => 'disabled'
                );

            }
            echo form_checkbox($data);
            echo "</td><td>";
          $edit = array(
          'src' => 'images/token_edit.png',
          'alt' => 'Edit',
          'width' => '15',
          'height' => '15',
          'title' => 'Edit Atribute',
          );
          echo anchor('admin/participants/viewAttribute/'.$value['attribute_id'],img($edit));
          $del = array(
          'src' => 'images/error_notice.png',
          'alt' => 'Delete',
          'width' => '15',
          'height' => '15',
          'title' => 'Delete Atribute',
          );
          echo anchor('admin/participants/delAttribute/'.$value['attribute_id'],img($del));
          echo "</td></tr>";
        }
        ?>
        <div id="attid" id="attid">
        </div>
        </table></li>
        <br>
        <li><a href="#" class="add"><img src = "<?php echo base_url().'images/plus.png' ?>" alt="Add Attribute" width="25" height="25" title="Add Attribute" id="add" name="add"></a></li></ul>

        <br>
        <p><input type="submit" name="Save" value="Save"></p>

    </body>
</html>
