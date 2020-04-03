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
            Yii::app()->getController()->redirect(array('/userManagement/index'));
            Yii::app()->end();
        } elseif (strtolower($sExtension) == 'csv' ) {
            $bMoveFileResult = @move_uploaded_file($_FILES['the_file']['tmp_name'], $sFilePath);
        } else {
            Yii::app()->setFlashMessage(gT("This is not a .csv file."). 'It is a '.$sExtension, 'error');
            Yii::app()->getController()->redirect(array('/userManagement/index'));
            Yii::app()->end();
        }

        if ($bMoveFileResult === false) {
            Yii::app()->setFlashMessage(gT("An error occurred uploading your file. This may be caused by incorrect permissions for the application /tmp folder."), 'error');
            Yii::app()->getController()->redirect(array('/userManagement/index'));
            Yii::app()->end();
            return;
        }

        $delimiter =  self::detectCsvDelimiter($sFilePath);
        $oCSVFile = fopen($sFilePath, 'r');
        if ($oCSVFile === false) {
            safeDie('File not found.');
        }

        $aFirstLine = fgetcsv($oCSVFile, 0,$delimiter, '"');

        $iHeaderCount = count($aFirstLine);
        $aToBeAddedUsers = [];
        while (($row = fgetcsv($oCSVFile, 0,$delimiter, '"')) !== false) {
            $rowarray = array();
            for ($i = 0; $i < $iHeaderCount; ++$i) {
                $val = (isset($row[$i]) ? $row[$i] : '');
                // if Excel was used, it surrounds strings with quotes and doubles internal double quotes.  Fix that.
                if (preg_match('/^".*"$/', $val)) {
                    $val = trim(str_replace('""', '"', substr($val, 1, -1)), "\xC2\xA0\n");
                }
                $rowarray[$aFirstLine[$i]] = $val;
            }
            $aToBeAddedUsers[] = $rowarray;
        }
        fclose($oCSVFile);
        
        return $aToBeAddedUsers;
    }


    /**
     * reads an uploaded json file
     *
     * @param array $FILES PHP Global $_FILES
     * @return array List of users to create
     */
    public static function getDataFromJSON($FILES)
    {
        $json = file_get_contents($FILES['the_file']['tmp_name']);
        $decoded = json_decode($json, true);

        foreach($decoded as $data){
            if(!isset($data["email"]) || !isset($data["users_name"]) || !isset($data["full_name"]) || !isset($data["lang"]) || !isset($data["password"])){
                Yii::app()->setFlashMessage(
                    sprintf(gT("Wrong definition! Please make sure that your JSON arrays contains the fields '%s', '%s', '%s', '%s', and '%s'"), '<b>users_name</b>','<b>full_name</b>','<b>email</b>','<b>lang</b>','<b>password</b>'),
                    'error'
                    );
                Yii::app()->getController()->redirect(array('/userManagement/index'));
                Yii::app()->end();
            }
        }

        return $decoded;
    }

    /** 
    *Function to get the delimiter of a Csv file
    * @param string $csvFile Path to the CSV file
    * @return string Delimiter
    */
    private static function detectCsvDelimiter($csvFile)
    {
        $delimiters = array(
            ';' => 0,
            ',' => 0,
            "\t" => 0,
            "|" => 0
        );

        $handle = fopen($csvFile, "r");
        $firstLine = fgets($handle);
        fclose($handle); 
        foreach ($delimiters as $delimiter => &$count) {
            $count = count(str_getcsv($firstLine, $delimiter));
        }

        return array_search(max($delimiters), $delimiters);
    }
}
