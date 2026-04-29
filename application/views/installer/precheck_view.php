<?php
/** @var InstallerConfigForm $model */
/** @var string $title */
/** @var string $descp */
/** @var boolean $sessionWritable */

$iconOk = "<span class='ri-check-fill text-success'></span>";
$iconFail = "<span class='ri-error-warning-fill text-danger'></span>";

?>
<div class="row">
    <div class="col-lg-3">
        <?php $this->renderPartial('/installer/sidebar_view', compact('progressValue', 'classesForStep')); ?>
    </div>
    <div class="col-lg-9">
        <?php if (empty($next) && empty($cookiesAllowed)) : ?>
            <div class="alert alert-warning" role="alert">
                <?= gT("Cookies seem to be disabled. Please use the \"Check again\" button instead of refreshing the page."); ?>
            </div>
        <?php endif; ?>
        <h2><?php echo $title; ?></h2>
        <p><?php echo $descp; ?></p>
        <legend><?php eT("Minimum requirements"); ?></legend>

        <table class='table-striped table'>
            <thead>
                <tr>
                       <th>&nbsp;</th>
                       <th class='text-center'><?php eT("Required"); ?></th>
                       <th class='text-center'><?php eT("Current"); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                       <td><?php eT("PHP version"); ?></td>
                       <td><?= $model::MINIMUM_PHP_VERSION?></td>
                       <td>
                           <?php if ($model->isPhpVersionOK) :?>
                                <?= phpversion(); ?>
                           <?php else :?>
                               <span style='font-weight:bold; color: red'><?php eT("Outdated"); ?>: <?= phpversion(); ?></span>
                           <?php endif;?>
                        </td>
                </tr>
                <tr>
                       <td><?php eT("Minimum memory available"); ?></td>
                       <td><?=$model::MINIMUM_MEMORY_LIMIT?></td>
                       <td>
                           <?php if ($model->isMemoryLimitOK) :?>
                                <?= $model->memoryLimit == -1 ? gT("Unlimited") : $model->memoryLimit . "MB" ?>
                           <?php else :?>
                                <span style='font-weight:bold; color: red'><?= gT("Too low"); ?>: <?=$model->memoryLimit?>MB</span>
                           <?php endif;?>
                       </td>
                </tr>
                <tr>
                       <td><?php eT("PHP PDO driver library"); ?></td>
                       <td><?php eT("At least one installed"); ?></td>
                       <td>
                           <?php if (empty($model->supportedDbTypes)) :?>
                               <span style='font-weight:bold; color: red'><?php eT("None found"); ?></span>
                           <?php else :?>
                               <?= implode(', ', $model->supportedDbTypes); ?>
                           <?php endif;?>
                       </td>
                </tr>
                <tr>
                       <td><?php eT("PHP mbstring library"); ?></td>
                       <td><span class='ri-check-fill text-success'></span></td>
                       <td><?= $model->isPhpMbStringPresent ? $iconOk : $iconFail ?></td>
                </tr>
                <tr>
                       <td><?php eT("PHP fileinfo library"); ?></td>
                       <td><span class='ri-check-fill text-success'></span></td>
                       <td><?= $model->isPhpFileInfoPresent ? $iconOk : $iconFail ?></td>
                </tr>
                <tr>
                       <td><?php eT("PHP zlib library");?></td>
                       <td><span class='ri-check-fill text-success'></span></td>
                       <td><?= $model->isPhpZlibPresent ? $iconOk : $iconFail ?></td>
                </tr>
                <tr>
                    <td><?php eT("PHP zip library"); ?></td>
                    <td><span class='ri-check-fill text-success'></span></td>
                    <td><?= $model->isPhpZipPresent ? $iconOk : $iconFail ?></td>
                </tr>

                <tr>
                       <td><?php eT("PHP/PECL JSON library"); ?></td>
                       <td><span class='ri-check-fill text-success'></span></td>
                       <td><?= $model->isPhpJsonPresent ? $iconOk : $iconFail ?></td>
                </tr>
                <tr>
                        <td><?php eT("PHP GD library (with JPEG & Freetype support)"); ?></td>
                        <td><span class='ri-check-fill text-success'></span></td>
                        <td>
                        <?php if ($model->isPhpGdPresent) : ?>
                            <?= ($model->phpGdHasFreeTypeSupport && $model->phpGdHasJpegSupport) ? $iconOk : $iconFail . '<br/>' . gT("GD extension doesn't support JPEG and/or Freetype") ?>
                        <?php else : ?>
                            <?= $iconFail ?>
                        <?php endif; ?>
               </td>

                <tr>
                       <td>/application/config <?php eT("directory"); ?></td>
                       <td><?php eT("Found & writable"); ?></td>
                       <td><?= $model->isConfigDirWriteable ? $iconOk : $iconFail ?></td>
                </tr>
                <tr>
                       <td>/upload <?php eT("directory"); ?></td>
                       <td><?php eT("Found & writable"); ?></td>
                       <td><?= $model->isUploadDirWriteable ? $iconOk : $iconFail ?></td>
                </tr>
                <tr>
                       <td>/tmp <?php eT("directory"); ?></td>
                       <td><?php eT("Found & writable"); ?></td>
                       <td><?= $model->isTmpDirWriteable ? $iconOk : $iconFail ?></td>
                </tr>
                <tr>
                       <td><?php eT("Session writable"); ?></td>
                       <td><span class='ri-check-fill text-success'></span></td>
                       <td>
                           <?= $sessionWritable ? $iconOk : $iconFail . '<br/>session.save_path: ' . session_save_path(); ?>
                       </td>
                </tr>
            </tbody>
        </table>
        <br/>
        <legend><?php eT('Optional modules'); ?></legend>
        <table class='table-striped table'>
        <thead>
            <tr>
                   <th>&nbsp;</th>
                   <th><?php eT('Recommended'); ?></th>
                   <th><?php eT('Current'); ?></th>
            </tr>
        </thead>
        <tbody>
        <tr>
               <td><?php eT("PHP Intl library"); ?></td>
               <td><span class='ri-check-fill text-success' alt="Check"></span></td>
               <td><?= $model->isCollatorPresent ? $iconOk : $iconFail ?></td>
        </tr>
        <tr>
               <td><?php eT("PHP LDAP library"); ?></td>
               <td><span class='ri-check-fill text-success'></span></td>
               <td><?= $model->isPhpLdapPresent ? $iconOk : $iconFail ?></td>
        </tr>
        <tr>
               <td><?php eT("PHP imap library"); ?></td>
               <td><span class='ri-check-fill text-success'></span></td>
               <td><?= $model->isPhpImapPresent ? $iconOk : $iconFail ?></td>
        </tr>
        <tr>
                
               <td><?php eT("PHP Sodium library [data encryption]"); ?></td>
               <td><span class='ri-check-fill text-success' alt="Check"></span></td>
               <td><?= $model->isSodiumPresent ? $iconOk : $iconFail ?></td>
        </tr>
        </tbody>

        </table>
        <div class="row navigator">
            <div class="col-lg-4" >
                <input id="ls-previous" class="btn btn-outline-secondary" type="button" value="<?php eT('Previous'); ?>" onclick="window.open('<?php echo $this->createUrl("installer/license"); ?>', '_top')" />
            </div>
            <div class="col-lg-4">
                <input id="ls-check-again" class="btn btn-outline-secondary" type="button" value="<?php eT('Check again'); ?>" onclick="window.open('<?php echo $this->createUrl("installer/precheckprepare"); ?>', '_top')" />
            </div>
            <div class="col-lg-4">

                <?php if (isset($next) && $next == true) :?>
                    <input id="ls-next" class="btn btn-outline-secondary" type="button" value="<?php eT('Next'); ?>" onclick="window.open('<?php echo $this->createUrl("installer/database"); ?>', '_top')" />
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
