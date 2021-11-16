            $oDB->createCommand()->update(
                '{{surveymenu_entries}}',
                [
                    'permission' => 'tokens',
                ],
                'name=\'participants\''
            );
