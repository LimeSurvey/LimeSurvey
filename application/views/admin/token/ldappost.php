<?php
/**
 * Result of ldap upload
 */
?>

<div class='side-body <?php echo getSideBodyClass(false); ?>'>
    <div class="row">
        <div class="col-lg-12 content-right">
            
            <!-- Success -->
            <?php if ($xz != 0): ?>
                <div class="jumbotron message-box">
                    <h2 class="text-success"><?php eT("Success"); ?></h2>
                    <p><?php echo $resultnum; ?></p>
                    <p>
                        <?php eT("Results from LDAP Query."); ?><br />
                        <?php printf(gT("%s records met minimum requirements"),$xv); ?><br />
                        <?php echo $xz; ?> <?php eT("Records imported"); ?>.<br />
                        <?php echo $xy; ?> <?php eT("Duplicate records removed"); ?>                        
                    </p>
                    <p>
                        [<a href='#' onclick='$("#duplicateslist").toggle();'><?php eT("List"); ?></a>]
                    </p>
                    <p class='badtokenlist' id='invalidemaillist' style='display: none;'>
                        <ul class="list-unstyled">
                            <?php foreach ($invalidemaillist as $aData) { ?>
                                <li><?php echo $aData; ?></li>
                            <?php } ?>                
                        </ul>        
                    </p>
                </div>
                
            <!-- Error -->
            <?php else: ?>
                <div class="jumbotron message-box message-box-error">
                    <h2 class="text-danger"><?php eT("Error"); ?></h2>
                    <p><?php echo $resultnum; ?></p>
                    <p>
                        <?php eT("Results from LDAP Query."); ?><br />
                        <?php printf(gT("%s records met minimum requirements"),$xv); ?><br />
                        <?php echo $xz; ?> <?php eT("Records imported"); ?>.<br />
                        <?php echo $xy; ?> <?php eT("Duplicate records removed"); ?>                        
                    </p>
                    <p>
                        [<a href='#' onclick='$("#duplicateslist").toggle();'><?php eT("List"); ?></a>]
                    </p>
                    <p class='badtokenlist' id='invalidemaillist' style='display: none;'>
                        <ul class="list-unstyled">
                            <?php foreach ($invalidemaillist as $aData) { ?>
                                <li><?php echo $aData; ?></li>
                            <?php } ?>                
                        </ul>        
                    </p>

                </div>                
            <?php endif;?>
        </div>
    </div>
</div>
<br />
