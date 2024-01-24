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
                        <?php eT('Exported'); ?>
                    </td>
                <?php else: ?>
                    <td class="text-danger">
                        <?php echo $result['error'] ; ?>
                    </td>
                <?php endif;?>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>

<?php if(!$bArchiveIsEmpty):?>
<div class="modal-footer modal-footer-buttons">
    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">
        &nbsp
        <?php
        eT("Cancel"); ?>
    </button>
    <a role="button" class='btn btn-primary' href="<?php echo App()->createUrl('/admin/export/sa/downloadZip/sZip/'.$sZip);?>">
        <span class="ri-download-fill"></span>
        <?php eT('Download archive');?>
    </a>
</div>
<?php endif; ?>
