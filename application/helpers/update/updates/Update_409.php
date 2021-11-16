
            $sEncrypted = 'N';
            $oDB->createCommand()->update(
                '{{participant_attribute_names}}',
                array('encrypted' => $sEncrypted),
                "core_attribute='Y'"
            );
