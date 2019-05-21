<?php
/**
 * Importing class to get users from a CSV file
 * 
 * @author Markus Flür <markus.fluer@limesurvey.org>
 * @license GPL3.0
 */
class UserParser  
{
    /**
     * reads an uploaded csv file
     *
     * @param array $FILES PHP Global $_FILES
     * @return array Liste der hinzuzufügenden Nutzer
     */
    public static function getDataFromCSV($FILES)
    {
        $sRandomFileName = randomChars(20);
        $sFilePath = Yii::app()->getConfig('tempdir').DIRECTORY_SEPARATOR.$sRandomFileName;
        $aPathinfo = pathinfo($FILES['the_file']['name']);
        $sExtension = $aPathinfo['extension'];
        $bMoveFileResult = false;
 
        if ($_FILES['the_file']['error'] == 1 || $_FILES['the_file']['error'] == 2) {
            Yii::app()->setFlashMessage(sprintf(gT("Sorry, this file is too large. Only files up to %01.2f MB are allowed."), getMaximumFileUploadSize() / 1024 / 1024), 'error');
            Yii::app()->getController()->redirect(array('/admin/usermanagement'));
            Yii::app()->end();
        } elseif (strtolower($sExtension) == 'csv' ||1==1) {
            $bMoveFileResult = @move_uploaded_file($_FILES['the_file']['tmp_name'], $sFilePath);
        } else {
            Yii::app()->setFlashMessage(gT("This is not a .csv file."). 'It is a '.$sExtension, 'error');
            Yii::app()->getController()->redirect(array('/admin/usermanagement'));
            Yii::app()->end();
        }

        if ($bMoveFileResult === false) {
            Yii::app()->setFlashMessage(gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."), 'error');
            Yii::app()->getController()->redirect(array('/admin/usermanagement'));
            Yii::app()->end();
            return;
        }

        $oCSVFile = fopen($sFilePath, 'r');
        if ($oCSVFile === false) {
            safeDie('File not found.');
        }
        $aFirstLine = fgetcsv($oCSVFile, 0,';', '"');

        $sSeparator = Yii::app()->request->getPost('separatorused');
        if ($sSeparator == 'auto') {
            $aCount = array();
            $aCount[','] = substr_count($aFirstLine, ',');
            $aCount[';'] = substr_count($aFirstLine, ';');
            $aCount['|'] = substr_count($aFirstLine, '|');
            $aResult = array_keys($aCount, max($aCount));
            $sSeparator = $aResult[0];
        }
        $iHeaderCount = count($aFirstLine);
        $aToBeAddedUsers = [];
        while (($row = fgetcsv($oCSVFile, 0,';', '"')) !== false) {
            $rowarray = array();
            for ($i = 0; $i < $iHeaderCount; ++$i) {
                $val = (isset($row[$i]) ? $row[$i] : '');
                // if Excel was used, it surrounds strings with quotes and doubles internal double quotes.  Fix that.
                if (preg_match('/^".*"$/', $val)) {
                    $val = str_replace('""', '"', substr($val, 1, -1));
                }
                $rowarray[$aFirstLine[$i]] = $val;
            }
            $aToBeAddedUsers[] = $rowarray;
        }
        fclose($oCSVFile);
        
        return $aToBeAddedUsers;
    }
}
