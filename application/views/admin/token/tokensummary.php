<div class='header ui-widget-header'>
    <?php $clang->eT("Token summary"); ?>
</div>
<br />
<table class='statisticssummary'>
    <tr>
        <th>
            <?php $clang->eT("Total records in this token table"); ?>
        </th>
        <td>
            <?php echo $queries['tkcount']; ?>
        </td>
    </tr>
    <tr>
        <th>
            <?php $clang->eT("Total with no unique Token"); ?>
        </th>
        <td>
            <?php echo $queries['tkinvalid']; ?>
        </td>
    </tr>
    <tr>
        <th>
            <?php $clang->eT("Total invitations sent"); ?>
        </th>
        <td>
            <?php echo $queries['tksent']; ?>
        </td>
    </tr>
    <tr>
        <th>
            <?php $clang->eT("Total opted out"); ?>
        </th>
        <td>
            <?php echo $queries['tkoptout']; ?>
        </td>
    </tr>
    <tr>
        <th>
            <?php $clang->eT("Total surveys completed"); ?>
        </th>
        <td>
            <?php echo $queries['tkcompleted']; ?>
        </td>
    </tr>
</table>
<br />
<script type='text/javascript'>
    surveyid = '<?php echo $surveyid; ?>'
</script>
<?php /* if (hasSurveyPermission($surveyid, 'tokens', 'update') || hasSurveyPermission($surveyid, 'tokens', 'delete'))
{ ?>
    <div class='header ui-widget-header'><?php $clang->eT("Token database administration options"); ?></div>
    <div style='width:30%; margin:0 auto;'>
        <ul>
                    <?php if (hasSurveyPermission($surveyid, 'tokens', 'update'))
                    { ?>
                <li><a href='#' onclick="if( confirm('<?php $clang->eT("Are you really sure you want to reset all invitation records to NO?", "js"); ?>')) { <?php echo convertGETtoPOST(Yii::app()->baseUrl . "?action=tokens&amp;sid=$surveyid&amp;subaction=clearinvites"); ?>}">
                        <?php $clang->eT("Set all entries to 'No invitation sent'."); ?></a></li>
                <li><a href='#' onclick="if ( confirm('<?php $clang->eT("Are you sure you want to delete all unique token strings?", "js"); ?>')) { <?php echo convertGETtoPOST(Yii::app()->baseUrl . "?action=tokens&amp;sid=$surveyid&amp;subaction=cleartokens"); ?>}">
                <?php $clang->eT("Delete all unique token strings"); ?></a></li>
                    <?php }
                    if (hasSurveyPermission($surveyid, 'tokens', 'delete'))
                    { ?>
                <li><a href='#' onclick=" if (confirm('<?php $clang->eT("Are you really sure you want to delete ALL token entries?", "js"); ?>')) { <?php echo convertGETtoPOST(Yii::app()->baseUrl . "?action=tokens&amp;sid=$surveyid&amp;subaction=deleteall"); ?>}">
        <?php $clang->eT("Delete all token entries"); ?></a></li>
    <?php } ?>
        </ul>
    </div>
<?php } */ ?>
