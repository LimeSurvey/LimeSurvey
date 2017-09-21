<?php

$uploaddir = str_replace('instances','installations',dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))).'/'.$_SERVER['SERVER_NAME'].'/userdata/upload';

function humanFilesize($bytes, $decimals = 2) {
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3); 
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}

function folderSize($dir)
{
    $size = 0;
    foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
        if (is_file($each)) {
            //$stat = stat($each);
            //$tmpsize = $stat[11] * $stat[12] / 8;
            //$size += $tmpsize;
            $size += filesize($each);
        } else {
            $size += folderSize($each);
        }   
    }   
    return $size;
}

$totalStorage = humanFilesize(folderSize($uploaddir));

$templateSize = humanFilesize(folderSize($uploaddir . '/templates'));

$surveyFolders = array_filter(glob($uploaddir . '/surveys/*'), 'is_dir');

$surveys = array();
foreach ($surveyFolders as $folder) {
    $parts = explode('/', $folder);
    $surveyId = (int) end($parts);
    $surveyinfo = getSurveyInfo($surveyId);
    $size = folderSize($folder);
    $surveys[] = array(
        'sizeInBytes' => $size,
        'name'        => $surveyinfo['name'],
        'sid'         => $surveyId
    );  
}

?>

<label><?php eT('Overview'); ?></label>
<table class='table table-striped table-bordered'>
    <tr>
        <td><?php eT('Total storage'); ?>:</td>
        <td><?php echo $totalStorage; ?></td>
    </tr>
    <tr>
        <td><?php eT('Template storage'); ?>:</td>
        <td><?php echo $templateSize; ?></td>
    </tr>
</table>

<label><?php eT('Survey storage'); ?></label>
<table class='table table-striped table-bordered'>
    <?php foreach ($surveys as $survey): ?>
    <tr>
        <td><?php echo $survey['name']; ?> (<?php echo $survey['sid']; ?>)</td>
        <td><?php echo humanFilesize($survey['sizeInBytes']); ?></td>
    </tr>
    <?php endforeach; ?>
</table>
