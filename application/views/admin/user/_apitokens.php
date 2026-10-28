<?php

/**
 * API tokens tab of the personal settings
 *
 * The revoke buttons submit the form #apitoken-revoke-form, which is outside of the settings form.
 *
 * @var UserApiToken[] $apiTokens
 * @var string|null    $newApiToken The API token created just before, shown once
 */
?>
<h2 class="pagetitle h3"><?php eT("API tokens"); ?></h2>
<p>
    <?php eT("Scripts and integrations can use an API token instead of your password to log in to RemoteControl or the REST API. Two-factor authentication is not requested for API tokens."); ?>
    <?php eT("Use your user name and the token when you request a session key."); ?>
</p>

<?php if ($newApiToken) : ?>
    <div class="alert alert-success" role="alert">
        <h3 class="h5"><?php eT("Your new API token"); ?></h3>
        <p><?php eT("Copy the token now. You won't be able to see it again."); ?></p>
        <div class="input-group">
            <label class="visually-hidden" for="apitoken-new"><?php eT("Your new API token"); ?></label>
            <input type="text" class="form-control font-monospace" id="apitoken-new" readonly value="<?php echo CHtml::encode($newApiToken); ?>" />
            <button type="button" class="btn btn-outline-secondary" id="apitoken-copy" data-copied-text="<?php echo CHtml::encode(gT("The API token was copied to the clipboard.", 'unescaped')); ?>">
                <i class="ri-file-copy-line" aria-hidden="true"></i>
                <?php eT("Copy"); ?>
            </button>
        </div>
        <div id="apitoken-copy-status" class="form-text" aria-live="polite"></div>
    </div>
<?php endif; ?>

<p>
    <a class="btn btn-primary" href="<?php echo App()->createUrl('admin/user/sa/apitoken'); ?>">
        <i class="ri-add-line" aria-hidden="true"></i>
        <?php eT("Create API token"); ?>
    </a>
</p>

<?php if (empty($apiTokens)) : ?>
    <p><?php eT("You don't have any API tokens."); ?></p>
<?php else : ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <caption class="visually-hidden"><?php eT("Your API tokens"); ?></caption>
            <thead>
                <tr>
                    <th scope="col"><?php eT("Name"); ?></th>
                    <th scope="col"><?php eT("Created"); ?></th>
                    <th scope="col"><?php eT("Last used"); ?></th>
                    <th scope="col"><?php eT("Expires"); ?></th>
                    <th scope="col"><?php eT("Action"); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($apiTokens as $apiToken) : ?>
                    <tr>
                        <th scope="row"><?php echo CHtml::encode($apiToken->name); ?></th>
                        <td><?php echo CHtml::encode($apiToken->getFormattedDate('created')); ?></td>
                        <td><?php echo $apiToken->last_used ? CHtml::encode($apiToken->getFormattedDate('last_used')) : gT("Never"); ?></td>
                        <td>
                            <?php if (!$apiToken->expires) : ?>
                                <?php eT("No expiration"); ?>
                            <?php elseif ($apiToken->isExpired()) : ?>
                                <span class="badge bg-danger"><?php eT("Expired"); ?></span>
                                <?php echo CHtml::encode($apiToken->getFormattedDate('expires')); ?>
                            <?php else : ?>
                                <?php echo CHtml::encode($apiToken->getFormattedDate('expires')); ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-nowrap">
                            <a
                                class="btn btn-outline-secondary btn-sm"
                                href="<?php echo App()->createUrl('admin/user/sa/apitoken', ['tokenid' => $apiToken->id]); ?>"
                                aria-label="<?php echo CHtml::encode(sprintf(gT("Edit API token %s", 'unescaped'), $apiToken->name)); ?>"
                            >
                                <i class="ri-pencil-fill" aria-hidden="true"></i>
                                <?php eT("Edit"); ?>
                            </a>
                            <button
                                type="submit"
                                class="btn btn-outline-danger btn-sm apitoken-revoke"
                                form="apitoken-revoke-form"
                                name="tokenid"
                                value="<?php echo (int) $apiToken->id; ?>"
                                aria-label="<?php echo CHtml::encode(sprintf(gT("Revoke API token %s", 'unescaped'), $apiToken->name)); ?>"
                                data-confirm-text="<?php echo CHtml::encode(sprintf(gT("Revoke the API token %s? Scripts and integrations using it will lose access immediately.", 'unescaped'), $apiToken->name)); ?>"
                            >
                                <i class="ri-delete-bin-fill" aria-hidden="true"></i>
                                <?php eT("Revoke"); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
