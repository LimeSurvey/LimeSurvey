<?php

/**
 * Page shown instead of any administrative page while the maintenance mode "hard-cli"
 * locks the administration, see LSYii_Controller::endRequestIfAdministrationLocked()
 *
 * @var string $message
 */

$layoutHelper = new LayoutHelper();
$layoutHelper->getAdminHeader(false, false, ['pageTitle' => gT('Site under maintenance')]);
?>
<div class="container-fluid">
    <div class="jumbotron message-box">
        <h2><?php eT('Site under maintenance'); ?></h2>
        <p class="lead"><?php echo CHtml::encode($message); ?></p>
    </div>
</div>
</body>
</html>
