<script type="text/javascript">
  var url = "<?php echo Yii::app()->createUrl("admin/participants/sa/getAttributeBox");?>";
  var attname = "<?php echo $clang->gT("Attribute Name:"); ?>";
  removeitem = new Array(); // Array to hold values that are to be removed from langauges option
</script>
<script src="<?php echo Yii::app()->getConfig('adminscripts')."admin_core.js" ?>" type="text/javascript"></script>
<script src="<?php echo Yii::app()->getConfig('generalscripts')."jquery/jquery.js" ?>" type="text/javascript"></script>
<script src="<?php echo Yii::app()->getConfig('generalscripts')."jquery/jquery-ui.js" ?>" type="text/javascript"></script>
<script src="<?php echo Yii::app()->getConfig('adminscripts')."viewAttribute.js" ?>" type="text/javascript"></script>
<div class='header ui-widget-header'><strong><?php echo $clang->gT("Attribute Settings"); ?></strong></div><br/>
<?php
  echo CHtml::beginForm(Yii::app()->createUrl('admin/participants/sa/saveAttribute/aid/'.CHttpRequest::getQuery('aid')).'/',"post");
  $plus = array('src'    => Yii::app()->baseUrl."/images/plus.png",
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
  $options[''] = $clang->gT('Select One');
  foreach (getLanguageData () as $langkey2 => $langname)
  {
    $options[$langkey2] = $langname['description'];
  }
   echo CHtml::dropDownList('langdata','',$options);
   echo CHtml::image($plus['src'],$plus['alt'],array_slice($plus,2));
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
        <?php echo CHtml::textField($value['lang'],$value['attribute_name']); ?>
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
      echo CHtml::dropDownList('attribute_type',$attributes['attribute_type'],$options);
    ?>
    <br/><br/>
 </div>
 <div id="comsettingcheck">
 <label for='attvisible' id='attvisible'>
  <?php echo $clang->gT('Attribute Visible:') ?>
 </label>
  <?php
    if($attributes['visible'] =="TRUE")
    {
      echo CHtml::checkbox('visible',TRUE, array('value' => 'TRUE', 'uncheckValue' => 'FALSE'));
    }
    else
    {
      echo CHtml::checkbox('visible',FALSE, array('value' => 'TRUE', 'uncheckValue' => 'FALSE'));
    }
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
        <div class=editable id="<?php echo $value['value_id'];?>">
            <?php
                echo $value['value'];
            ?>
        </div>
      </td>
      <td>
      <?php
                      $edit = array('src' =>  Yii::app()->getConfig('imageurl').'/token_edit.png',
                      'alt' => 'Edit',
                      'width' => '15',
                      'class' => 'edit',
                      'name' => $value['value_id'],
                      'height' => '15',
                      'title' => 'Edit Atribute');
            echo CHtml::image($edit['src'],$edit['alt'],array_slice($edit,2));
        $del = array( 'src'    =>  Yii::app()->getConfig('imageurl').'/error_notice.png',
                      'alt'    => 'Delete',
                      'width'  => '15',
                      'height' => '15',
                      'title'  => 'Delete Atribute Value');
        echo CHtml::link(CHtml::image($del['src'],$del['alt'],array_slice($del,2)),$this->createURL('admin/participants/sa/delAttributeValues/aid/'.$attributes['attribute_id'].'/vid/'.$value['value_id']));
      ?>
      </td>
    </tr>
    <?php  
    }
    ?>
   </table>
   <div id="plus">
   <a href='#' class='add'>
       <img src = "<?php echo Yii::app()->getConfig('imageurl'); ?>/plus.png" alt='Add Attribute' width='25' height='25' title='Add Attribute' id='addsign' name='addsign'>
   </a>
   </div>
   </div>
 <br/>
 <p>
   <?php
    echo CHtml::submitButton('submit', array('value'=>'Save'));
    echo CHtml::endForm();
   ?>
 </p>
 
