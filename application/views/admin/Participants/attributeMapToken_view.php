<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="<?php echo site_url("styles/admin/default/attributeMapToken.css")?>" />
        <script src="<?php echo site_url("scripts/jquery/jquery.js")?>" type="text/javascript"></script>
        <script src="<?php echo site_url("scripts/jquery/jquery-ui.js")?>" type="text/javascript"></script>
        <script src="<?php echo site_url("scripts/jquery/jquery.ui.sortable.js")?>" type="text/javascript"></script>
        <script src="<?php echo site_url("scripts/admin/attributeMapToken.js")?>" type="text/javascript"></script>
        <script type="text/javascript">
            var redUrl = "<?php echo site_url("admin/participants/displayParticipants");?>";
            var copyUrl = "<?php echo site_url("admin/participants/addToCentral");?>";
            var surveyId = "<?php echo $this->uri->segment(4); ?>";
        </script>
  </head>
<body>
<?php if(!empty($tokenattribute)) { ?>
<div id="tokenattribute">
    <div class="heading">Token Attributes</div>
        <ul id="tokenatt">
            <?php 
            
        foreach($tokenattribute as $key=>$value)
        { 
            echo "<li id='t_".$value."' name=\"$key\">".$value."</li>"; //Passing attribute description as name of the attribute
        }?>
      </ul>
    <ul class="notsortable">
            <?php
             foreach($alreadymappedattributename as $key=>$value)
            {
                echo "<li title='This attribute is already mapped' id='' name='' >".$value."</li>";
           }
       ?>
    </ul>
</div>
<?php } ?>    
<?php if(!empty($tokenattribute)) { ?>
<div id="newcreated"><div class="heading">Attribute's to be created</div> 
     <ul class="newcreate" id="sortable" style ="height: 40px">
    </ul>
</div>
<?php } ?>    
 <?php if(!empty($tokenattribute)) { ?>
<div id="centralattribute"><div class="heading">Central Attribute</div>
     <ul class="centralatt">
        <?php
             if(!empty($attribute))
                {
                   foreach($attribute as $key=>$value)
                       {
                          echo "<li id='c_".$key."' name='c_".$key."' >".$value."</li>";
                       }
               }
         ?>
     </ul>
</div>
<?php } ?>    
    <p> <input type="button" name="attmap" id="attmap" value="Continue" /></p>
    <?php $ajaxloader = array(
          'src' => 'images/ajax-loader.gif',
          'alt' => 'Ajax Loader',
          'title' => 'Ajax Loader'
          );?>
    <div id="processing" title="<?php echo $clang->gT("Processing .....") ?>" style="display:none">
    <?php echo img($ajaxloader); ?>
    </div>
</body>
</html>







