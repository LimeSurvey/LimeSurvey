<i>
    <?php if ($xz != 0) { ?>
        <span class='successtitle'><?php eT("Success"); ?></span>
    <?php } else { ?>
        <font color='red'><?php eT("Failed"); ?></font>
    <?php } ?>

    <br /><br />
    <?php echo $resultnum; ?>
    <?php eT("Results from LDAP Query."); ?><br />
    <?php printf(gT("%s records met minimum requirements"),$xv); ?><br />
    <?php echo $xz; ?> <?php eT("Records imported"); ?>.<br />
    <?php echo $xy; ?> <?php eT("Duplicate records removed"); ?>
    [<a href='#' onclick='$("#duplicateslist").toggle();'><?php eT("List"); ?></a>]
    <div class='badtokenlist' id='duplicateslist' style='display: none;'>
    <?php foreach ($duplicatelist as $aData) { ?>
        <li><?php echo $aData; ?></li>
    <?php } ?>
    </div>
    <br />
    <?php printf(gT("%s records with invalid email address removed"), $invalidemailcount); ?>
     [<a href='#' onclick='$("#invalidemaillist").toggle();'><?php eT("List"); ?></a>]
    <div class='badtokenlist' id='invalidemaillist' style='display: none;'>
    <?php foreach ($invalidemaillist as $aData) { ?>
        <li><?php echo $aData; ?></li>
    <?php } ?>
    </div>
    <br />
</i>
<br />
