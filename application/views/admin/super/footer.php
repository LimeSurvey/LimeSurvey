</div>
<div class='footer'>
    <div style='float:left;width:110px;text-align:left;'><a href='http://docs.limesurvey.org'><img alt='LimeSurvey - <?php echo $clang->gT("Online Manual");?>' title='LimeSurvey - <?php echo $clang->gT("Online Manual");?>' src='<?php echo $imageurl;?>/docs.png' width="25" height="25"/></a></div>
    <div style='float:right;'><a href='http://donate.limesurvey.org'><img alt='<?php echo $clang->gT("Support this project - Donate to "); ?>LimeSurvey' title='<?php echo $clang->gT("Support this project - Donate to "); ?>LimeSurvey!' src='<?php echo $imageurl;?>/donate.png' width="107" height="25"/></a></div>
    <div class='subtitle'><a class='subtitle' title='<?php echo $clang->gT("Visit our website!"); ?>' href='http://www.limesurvey.org' target='_blank'>LimeSurvey</a><br /><?php echo $versiontitle." ".$versionnumber." ".$buildtext;?></div>
</div>
<?php
    if(isset($js_admin_includes))
    {
        foreach ($js_admin_includes as $jsinclude)
        {
        ?>
        <script type="text/javascript" src="<?php echo $jsinclude;?>"></script>
        <?php
        }
    }
if(isset($css_admin_includes)) {
foreach ($css_admin_includes as $cssinclude)
{?>
            <link rel="stylesheet" type="text/css" media="all" href="<?php echo $cssinclude;?>" />
            <?php }
}
?>
</body>
</html>