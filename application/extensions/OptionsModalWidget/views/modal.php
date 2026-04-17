<div class="modal fade" id="<?= $id ?>" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $modalTitle ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group">
                <?php foreach ($options as $optionData): ?>
                    <li class="list-group-item">
                        <?php $targetAttribute = isset($optionData['target']) ? 'target="' . $optionData['target'] . '"' : '' ?>
                        <a class="<?= $optionData['linkClass'] ?>" href="<?= $optionData['href'] ?>" <?= $targetAttribute ?>><?= $optionData['text'] ?></a>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= gT("Close"); ?>
                </button>
            </div>
        </div>
    </div>
</div>