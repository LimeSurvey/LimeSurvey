<?php
/**
 * @var array $results
 */
?>

<div class="modal-header">
    <h5 class="modal-title"><?= gT('Saved successfully') ?></h5>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-12 text-center">
            <div class="check_mark">
                <div class="sa-icon sa-success animate">
                    <span class="sa-line sa-tip animateSuccessTip"></span>
                    <span class="sa-line sa-long animateSuccessLong"></span>
                    <div class="sa-placeholder"></div>
                    <div class="sa-fix"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <ul class="list-group">
                <?php foreach ($results as $type => $result) { ?>
                    <?php /*<?='<pre>'.print_r([$type,$result],true).'</pre>';?> */ ?>
                    <li class="list-group-item">
                        <?php if (is_array($result)) { ?>
                            <?= ($result['descriptionData']['title'] ?? $type) ?> :
                            <?php
                            if (isset($result['success'])) {
                                echo($result['success'] ? 'OK' : 'error');
                            } else {
                                echo 'error';
                            }
                            ?>
                        <?php } else { ?>
                            <?= $type ?> :
                            <?= ($result ? 'OK' : 'error') ?>
                        <?php } ?>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</div>
<div class="modal-footer">
    <?php if (!isset($noButton)): ?>
        <button id="exitForm" class="btn btn-cancel" data-bs-dismiss="modal">
            <?= gT('Close') ?>
        </button>
    <?php endif; ?>
</div>
