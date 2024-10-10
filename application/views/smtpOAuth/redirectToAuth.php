<div class="card">
    <div class="card-body">
        <h5 class="card-title"><?= sprintf(gT("Authenticate with %s"), $providerName) ?></h5>
        <p class="card-text">
            <?php if (!empty($description)): ?>
                <?= $description ?>
            <?php else: ?>
                <?= gT("Please click the button below to authenticate with your account.") ?>
            <?php endif; ?>
        </p>
        <button type="button" id="get-token" class="btn btn-primary">Get token</button>
    </div>
</div>
<script>
    $(document).on('click', '#get-token', () => {
        const width = <?= $width ?? 600 ?>;
        const height = <?= $height ?? 700 ?>;
        const left = (window.screen.width - width) / 2 + window.screen.availLeft;
        const top = (window.screen.height - height) / 2 + window.screen.availTop;

        window.open('<?= $providerUrl ?>', 'lime_oauth', 'width=' + width + ',height=' + height + ',left=' + left + ',top=' + top + 'location=0,menubar=0,status=0,toolbar=0');
    });

    window.addEventListener('message', function(event) {
        if (event.data == 'REFRESH') {
            window.location.replace('<?= $redirectUrl ?>');
        }
    });
</script>