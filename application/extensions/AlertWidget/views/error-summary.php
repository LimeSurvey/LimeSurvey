<?php
/* @var $errors */
?>
<?php if (!empty($errors)) : ?>
<ul class="d-flex flex-column">
    <?php foreach ($errors as $error) : ?>
        <li><?= $error ?></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>

