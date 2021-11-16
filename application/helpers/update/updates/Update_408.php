            $oDB->createCommand()->update(
                '{{participant_attribute_names}}',
                array('encrypted' => 'Y'),
                "core_attribute='Y'"
            );
