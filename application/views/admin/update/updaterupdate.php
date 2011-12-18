<?php

if (!is_writable($tempdir))
{
    echo  "<li class='errortitle'>".sprintf($clang->gT("Tempdir %s is not writable"),$tempdir)."<li>";
}
if (!is_writable(APPPATH.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'update.php'))
{
    echo  "<li class='errortitle'>".sprintf($clang->gT("Updater file is not writable (%s). Please set according file permissions."),DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'update.php')."</li>";
}

if ($httperror != '')
{
	print( $httperror );
}

if (!$updater_exists)
{
	echo $clang->gT('There was a problem downloading the updater file. Please try to restart the update process.').'<br />';
}

?>
