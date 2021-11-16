            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                [
                    'permission' => 'surveylocale',
                ],
                'name=\'emailtemplates\''
            );
