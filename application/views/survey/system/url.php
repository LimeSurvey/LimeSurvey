<?php
/**
 * @var string url : url for the link
 * @var string description : description/content of the link
 * @var coreClass core class of the url
 * @var type type of the url
 **/

$type=$type ?? '-default';
$coreClass=isset($coreClass) ? $type : '';
$description=$description ?? $url;
?>
<div class="url-wrapper url-wrapper-<?php echo $type; ?>">
    <a href="<?php echo $url; ?>" class="<?php echo $coreClass; ?>"><?php echo $description; ?></a>
</div>
