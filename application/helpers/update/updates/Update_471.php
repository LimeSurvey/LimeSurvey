            $oTransaction = $oDB->beginTransaction();

            $fixedTitles = [
                '5pointchoice' => '5 point choice',
                'arrays/10point' => 'Array (10 point choice)',
                'arrays/5point' => 'Array (5 point choice)',
                'hugefreetext' => 'Huge free text',
                'multiplenumeric' => 'Multiple numerical input',
                'multipleshorttext' => 'Multiple short text',
                'numerical' => 'Numerical input',
                'shortfreetext' => 'Short free text',
                'image_select-listradio' => 'Image select list (Radio)',
                'image_select-multiplechoice' => 'Image select multiple choice',
                'ranking_advanced' => 'Ranking advanced'
            ];

            foreach ($fixedTitles as $themeName => $newTitle) {
                $oDB->createCommand()->update('{{question_themes}}', array('title' => $newTitle), "name='$themeName'");
            }

            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 471), "stg_name='DBVersion'");
            $oTransaction->commit();
