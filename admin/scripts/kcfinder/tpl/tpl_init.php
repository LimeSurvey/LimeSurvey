<root>
<tree>
<?php echo $tree ?>
</tree>
<files dirWritable="<?php echo $dirWritable ? "yes" : "no" ?>">
<?php FOREACH ($files as $file): ?>
<file size="<?php echo $file['size'] ?>" mtime="<?php echo $file['mtime'] ?>" date="<?php echo $file['date'] ?>" readable="<?php echo $file['readable'] ? "yes" : "no" ?>" writable="<?php echo $file['writable'] ? "yes" : "no" ?>" bigIcon="<?php echo $file['bigIcon'] ? "yes" : "no" ?>" smallIcon="<?php echo $file['smallIcon'] ? "yes" : "no" ?>" thumb="<?php echo $file['thumb'] ? "yes" : "no" ?>" smallThumb="<?php echo $file['smallThumb'] ? "yes" : "no" ?>">
<name><?php echo text::xmlData($file['name']) ?></name>
</file>
<?php ENDFOREACH ?>
</files>
</root>
