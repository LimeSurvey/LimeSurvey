<script type="text/javascript">
    var redUrl = "<?php echo Yii::app()->baseUrl."/index.php/admin/participants/sa/displayParticipants";?>";
    var surveyId = "<?php echo $survey_id ?>";
    var redirect = "<?php echo $redirect ?>";
    if(redirect=='TRUE')
    {
     redUrl = "<?php echo Yii::app()->baseUrl."/index.php/admin/tokens/sa/browse/surveyid".'/'.$survey_id;?>";
    }
    var copyUrl = "<?php echo Yii::app()->baseUrl."/index.php/admin/participants/sa/addToTokenattmap";?>";
    var participant_id = "<?php echo $participant_id; ?>";   
</script>
<div class='header ui-widget-header'>
    <strong>
      <?php echo $count ?>
    </strong>
</div>
<div class="main">
 <?php
 if(!empty($selectedcentralattribute)) 
 { 
 ?>
 <div id="centralattribute">
   <div class="heading"><?php echo $clang->gT("Already Mapped"); ?></div>
    <ul id="cpdbatt">
    <?php
     foreach($selectedcentralattribute as $key=>$value)
     {
    ?>
       <li id='c_<?php echo $key; ?>'><?php echo $value; ?></li>
    <?php
     }
    ?>
    </ul>
    <ul class="notsortable">
     <?php
      foreach($alreadymappedattributename as $key=>$value)
      {
     ?>
      <li title='This attribute is already mapped' id=''><?php echo $value; ?></li>
     <?php
      }
     ?>
    </ul>
 </div>
 <?php
 if(!empty($selectedcentralattribute))
  { 
 ?>
  <div id="newcreated">
      <div class="heading"><?php echo $clang->gT("Attributes to be created"); ?></div>
      <ul class="newcreate" id="sortable" style ="height:40px">
      </ul>
  </div>
  <?php 
  } 
 }
 if(!empty($selectedtokenattribute))
 {
  ?>
    <div id="tokenattribute">
        <div class="heading">
         <?php echo $clang->gT("Token Table Attribute");?>
        </div>
        <ul class="tokenatt">
        <?php
        foreach($selectedtokenattribute as $key=>$value)
        {
             echo "<li id='t_".$value."'>".$value."</li>";
        }
        ?>
     </ul>
    </div>
<?php  }
?>

    <p> <input type="button" name="attmap" id="attmap" value="Continue" /></p>
    <?php $ajaxloader = array(
          'src' => Yii::app()->baseUrl.'/images/ajax-loader.gif',
          'alt' => 'Ajax Loader',
          'title' => 'Ajax Loader'
          );?>
    <div id="processing" title="<?php echo $clang->gT("Processing .....") ?>" style="display:none">
    <?php echo CHtml::image($ajaxloader['src'],$ajaxloader['alt']); ?>
    </div>
 </div>
