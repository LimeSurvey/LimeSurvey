<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="<?php echo site_url("styles/admin/default/adminstyle.css")?>" />
        <link rel="stylesheet" type="text/css" href="<?php echo site_url("styles/admin/default/attributeMap.css")?>" />
        <script src="<?php echo site_url("scripts/jquery/jquery.js")?>" type="text/javascript"></script>
        <script src="<?php echo site_url("scripts/jquery/jquery-ui.js")?>" type="text/javascript"></script>
        <script src="<?php echo site_url("scripts/jquery/jquery.qtip.js")?>" type="text/javascript"></script>
        <script src="<?php echo site_url("scripts/jquery/jquery.ui.sortable.js")?>" type="text/javascript"></script>
        <script src="<?php echo site_url("scripts/admin/attributeMap.js")?>" type="text/javascript"></script>
        <script type="text/javascript">
            var redUrl = "<?php echo site_url("admin/participants/displayParticipants");?>";
            var surveyId = "<?php echo $this->uri->segment(4); ?>";
            var redirect = "<?php echo $this->uri->segment(5); ?>";
            if(redirect=='redirect')
                {
                    redUrl = "<?php echo site_url("admin/tokens/browse").'/'.$this->uri->segment(4);?>";
                }
            var copyUrl = "<?php echo site_url("admin/participants/addToTokenattmap");?>";
            
        </script>
  </head>
<body>
<div class='header ui-widget-header'><strong><?php echo sprintf($clang->gT("Select attributes to copy with your %s participant(s)"),count($this->session->userdata('participantid')));?></strong></div>
<div class="main">
<?php if(!empty($selectedcentralattribute)) { ?>
<div id="centralattribute">
    <div class="heading"><?php echo $clang->gT("Already Mapped"); ?></div>
        <ul id="cpdbatt">
        <?php
            foreach($selectedcentralattribute as $key=>$value)
            {
                echo "<li id='c_".$key."' name='c_".$key."' >".$value."</li>";
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
  <?php if(!empty($selectedcentralattribute)){ ?>
<div id="newcreated"><div class="heading">Attributes to be created</div>
     <ul class="newcreate" id="sortable" style ="height: 40px">
    </ul>
</div><?php } ?>

<?php }
if(!empty($selectedtokenattribute)){ ?>
      <div id="tokenattribute"><div class="heading"><?php echo $clang->gT("Token Table Attribute");?></div>
    <ul class="tokenatt">
        <?php
        foreach($selectedtokenattribute as $key=>$value)
        {
             echo "<li id='t_".$value."' name='t_".$value."' >".$value."</li>";
        }
        ?>
     </ul>
    </div>
<?php  }
?>

    <p> <input type="button" name="attmap" id="attmap" value="Continue" /></p>
    <?php $ajaxloader = array(
          'src' => 'images/ajax-loader.gif',
          'alt' => 'Ajax Loader',
          'title' => 'Ajax Loader'
          );?>
    <div id="processing" title="<?php echo $clang->gT("Processing .....") ?>" style="display:none">
    <?php echo img($ajaxloader); ?>
    </div>
 </div>
</body>
</html>







