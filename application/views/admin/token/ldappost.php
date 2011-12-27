<i>
    <?php if ($xz != 0) { ?>
        <span class='successtitle'><?php $clang->eT("Success"); ?></span>
    <?php } else { ?>
        <font color='red'><?php $clang->eT("Failed"); ?></font>
    <?php } ?>

    <br /><br />
    <?php echo $resultnum; ?>
    <?php $clang->eT("Results from LDAP Query."); ?><br />
    <?php echo $xv; ?> <?php $clang->eT("Records met minumum requirements"); ?>.<br />
    <?php echo $xz; ?> <?php $clang->eT("Records imported"); ?>.<br />
    <?php echo $xy; ?> <?php $clang->eT("Duplicate records removed"); ?>
    [<a href='#' onclick='$("#duplicateslist").toggle();'><?php $clang->eT("List"); ?></a>]
    <div class='badtokenlist' id='duplicateslist' style='display: none;'>
    <?php foreach ($duplicatelist as $aData) { ?>
        <li><?php echo $aData; ?></li>
    <?php } ?>
    </div>
    <br />
    <?php printf($clang->gT("%s records with invalid email address removed"), $invalidemailcount); ?>
     [<a href='#' onclick='$("#invalidemaillist").toggle();'><?php $clang->eT("List"); ?></a>]
    <div class='badtokenlist' id='invalidemaillist' style='display: none;'>
    <?php foreach ($invalidemaillist as $aData) { ?>
        <li>$aData</li>
    <?php } ?>
    </div>
    <br />
</i>
<br />
