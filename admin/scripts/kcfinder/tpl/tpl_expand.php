<root>
<?php FOREACH ($dirs as $dir): ?>
<dir readable="<?php echo $dir['readable'] ? "yes" : "no" ?>" writable="<?php echo $dir['writable'] ? "yes" : "no" ?>" removable="<?php echo $dir['removable'] ? "yes" : "no" ?>" hasDirs="<?php echo $dir['hasDirs'] ? "yes" : "no" ?>">
<name><?php echo text::xmlData($dir['name']) ?></name>
</dir>
<?php ENDFOREACH ?>
</root>
