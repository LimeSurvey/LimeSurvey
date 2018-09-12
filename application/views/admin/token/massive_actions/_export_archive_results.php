<?php
/**
 * This view display the result of delete multiple action. It's rendered via ajax for the confirmation modal in survey list
 *
 * @var $aResults   The array containing the result of each survey deletion
 * @var $aZIPFileName
 */
?>

<table class="table table-striped">
    <thead>
        <th><?php eT('Survey ID');?></th>
        <th><?php eT('Survey title');?></th>
        <th><?php eT('Status');?></th>
    </thead>
    <tbody>
        <?php foreach($aResults as $iSid => $result):?>
            <tr>
                <td>
                    <?php echo $iSid;?>
                </td>
                <td>
                    <?php echo $result['title'];?>
                </td>
                <?php if ($result['result']):?>
                    <td class="text-success">
                        <?php echo 'Exported' ; ?>
                    </td>
                <?php else: ?>
                    <td class="text-warning">
                        <?php echo $result['error'] ; ?>
                    </td>
                <?php endif;?>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>

<?php if(!$bArchiveIsEmpty):?>
    <a class='btn btn-primary' href="<?php echo App()->createUrl('/admin/export/sa/downloadZip/sZip/'.$sZip);?>">
        <span class="fa fa-download"></span>
        <?php eT('Download archive');?>
    </a>
<?php endif; ?>
