            addColumn('{{participant_attribute_names}}', 'defaultname', "string(50) NOT NULL default ''");
            upgradeCPDBAttributeDefaultNames173();
