<div class="pagetitle h3"><?php eT("Check data integrity");?></div>

<div class="row" style="margin-bottom: 100px">
    <div class="col-12">
        <div class="jumbotron message-box">
                <h2><?php eT("Data consistency check"); ?></h2>
                <p class="lead"><?php eT("If errors are showing up you might have to execute this script repeatedly."); ?></p>
                <?php if (!empty($errors)) { ?>
                    <?php
                    $errorList = '<ul>';
                    foreach ($errors as $error) {
                        $errorList .= '<li>' . $error . '</li>';
                    }
                    $errorList .= '</ul>';
                    $this->widget('ext.AlertWidget.AlertWidget', [
                        'header' => gT('Error'),
                        'text' => $errorList,
                        'type' => 'danger',
                    ]);
                    ?>
                <?php } ?>
                <?php if (!empty($warnings)) { ?>
                    <?php
                    $warningList = '<ul>';
                    foreach ($warnings as $warning) {
                        $warningList .= '<li>' . $warning . '</li>';
                    }
                    $warningList .= '</ul>';
                    $this->widget('ext.AlertWidget.AlertWidget', [
                        'header' => gT('Warning'),
                        'text' => $warningList,
                        'type' => 'warning',
                    ]);
                    ?>    
                <?php } ?>
                <?php if (!empty($messages)) { ?>
                    <?php
                    $infoList = '<ul>';
                    foreach ($messages as $info) {
                        $infoList .= '<li>' . $info . '</li>';
                    }
                    $infoList .= '</ul>';
                    $this->widget('ext.AlertWidget.AlertWidget', [
                        'header' => gT('info'),
                        'text' => $infoList,
                        'type' => 'info',
                    ]);
                    ?>
                <?php } ?>
                <p>
                    <a class="btn btn-lg btn-primary" href='<?php echo $this->createUrl('admin/checkintegrity');?>'><?php eT("Check again"); ?></a>
                </p>
        </div>
        
    </div>
</div>  
  
