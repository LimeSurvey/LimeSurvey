            $oTransaction = $oDB->beginTransaction();
            switch (Yii::app()->db->driverName) {
                case 'sqlsrv':
                case 'dblib':
                case 'mssql':
                    $oDB->createCommand("UPDATE {{boxes}} SET ico = 'icon-' + ico")->execute();
                    break;
                default:
                    $oDB->createCommand("UPDATE {{boxes}} SET ico = CONCAT('icon-', ico)")->execute();
                    break;
            }
            // Only change label box if it's there.
            $labelBox = $oDB->createCommand(
                "SELECT * FROM {{boxes}} WHERE id = 5 AND position = 5 AND title = 'Label sets'"
            )->queryRow();
            if ($labelBox) {
                $oDB
                    ->createCommand()
                    ->update(
                        '{{boxes}}',
                        [
                            'title' => 'LimeStore',
                            'ico' => 'fa fa-cart-plus',
                            'desc' => 'LimeSurvey extension marketplace',
                            'url' => 'https://account.limesurvey.org/limestore'
                        ],
                        'id = 5'
                    );
            }
            $oDB->createCommand()->update('{{settings_global}}', ['stg_value' => 356], "stg_name='DBVersion'");
            $oTransaction->commit();
