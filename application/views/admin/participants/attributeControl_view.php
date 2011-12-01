<script src="<?php echo Yii::app()->getConfig('generalscripts')."jquery/jquery.js" ?>" type="text/javascript"></script>
<script src="<?php echo Yii::app()->getConfig('generalscripts')."jquery/jquery-ui.js" ?>" type="text/javascript"></script>
<script src="<?php echo Yii::app()->getConfig('adminscripts')."attributeControl.js" ?>" type="text/javascript"></script>
<script type="text/javascript">
  var saveVisibleMsg = "<?php echo $clang->gT("Attribute Visiblity Changed") ?>";    
  var saveVisible = "<?php echo Yii::app()->baseUrl."/index.php/admin/participants/sa/saveVisible";?>";
</script>
<div class='header ui-widget-header'>
  <strong>
    <?php echo $clang->gT("Attribute Control"); ?>
  </strong>
</div>
<?php
  $attribute = array('class' => 'form44');
  echo CHtml::beginForm('storeAttributes','post',$attribute);
?>
<br></br>
<ul>
  <li>
    <table id='atttable'class='hovertable'>
    <tr>
      <th><?php echo $clang->gT("Attribute Name"); ?></th>
      <th><?php echo $clang->gT("Attribute Type"); ?></th>
      <th><?php echo $clang->gT("Visible in participant panel"); ?></th>
      <th><?php echo $clang->gT("Actions"); ?></th>
    </tr>
    <?php 
    foreach($result as $row=>$value)
    {
    ?>
    <tr>
      <td>
      <?php
        echo $value['attribute_name'];
      ?>
      </td>
      <td>
      <?php
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
      ?>
      </td>
      <td>
      <?php
      if($value['visible']=="TRUE")
      {
        $data = array('name'  => 'visible_'.$value['attribute_id'],
                      'id'    => 'visible_'.$value['attribute_id'],
                      'value' => 'TRUE',
                      'checked' => TRUE);
      }
      else
      {
        $data = array('name'    => 'visible_'.$value['attribute_id'],
                      'id'      => 'visible_'.$value['attribute_id'],
                      'value'   => 'TRUE',
                      'checked' => FALSE);
      }
        echo CHtml::checkbox($data['name'],$data['checked'],array('value' => $data['value']));
      ?>
      </td>
      <td>
      <?php
        $edit = array('src'    => Yii::app()->baseUrl.'/images/token_edit.png',
                      'alt'    => 'Edit',
                      'width'  => '15',
                      'height' => '15',
                      'title'  => 'Edit Atribute');
        echo CHtml::link(CHtml::image($edit['src'],$edit['alt'],array_slice($edit,2)),'viewAttribute/aid/'.$value['attribute_id']);
        $del = array('src' => Yii::app()->baseUrl.'/images/error_notice.png',
                     'alt' => 'Delete',
                     'width' => '15',
                     'height' => '15',
                     'title' => 'Delete Atribute');
        echo CHtml::link(CHtml::image($del['src'],$del['alt'],array_slice($del,2)),'delAttribute/aid/'.$value['attribute_id']);
      ?>
      </td>
    </tr>
    <?php
      }
    ?>
    </table>
  </li>
  <li>
    <a href="#" class="add"><img src = "<?php echo Yii::app()->baseUrl.'/images/plus.png' ?>" alt="Add Attribute" width="25" height="25" title="Add Attribute" id="add" name="add" /></a>
  </li>
</ul>
<br/>
<p><input type="submit" name="Save" value="Save" /></p>
<?php echo CHtml::endForm(); ?>
