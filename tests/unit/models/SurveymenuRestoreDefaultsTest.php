<?php

namespace ls\tests;

/**
 * Tests for restoring the default survey menus.
 */
class SurveymenuRestoreDefaultsTest extends TestBaseClass
{
    /**
     * Restoring the default menus deletes the entries of custom menus,
     * but keeps custom entries of default menus.
     */
    public function testRestoreDefaultsRemovesEntriesOfDeletedMenus()
    {
        $db = \Yii::app()->db;
        $now = date('Y-m-d H:i:s');

        // Explicit id: The PostgreSQL sequence is not advanced past the default menus inserted with fixed ids
        $customMenuId = (int) $db->createCommand('SELECT MAX(id) FROM {{surveymenu}}')->queryScalar() + 1;
        switchMSSQLIdentityInsert('surveymenu', true);
        $db->createCommand()->insert('{{surveymenu}}', [
            'id' => $customMenuId,
            'name' => 'restoretestmenu',
            'title' => 'Restore test menu',
            'ordering' => 99,
            'level' => 0,
            'created_at' => $now,
        ]);
        switchMSSQLIdentityInsert('surveymenu', false);

        $entry = [
            'title' => 'Restore test entry',
            'menu_title' => 'Restore test entry',
            'menu_description' => 'Restore test entry',
            'ordering' => 99,
            'created_at' => $now,
        ];
        $db->createCommand()->insert('{{surveymenu_entries}}', array_merge($entry, ['menu_id' => $customMenuId, 'name' => 'restoretestcustom']));
        $db->createCommand()->insert('{{surveymenu_entries}}', array_merge($entry, ['menu_id' => 1, 'name' => 'restoretestdefault']));

        $this->assertTrue(\Surveymenu::model()->restoreDefaults());

        $this->assertNull(\Surveymenu::model()->findByAttributes(['name' => 'restoretestmenu']));
        $this->assertNull(\SurveymenuEntries::model()->findByAttributes(['name' => 'restoretestcustom']));
        $this->assertNotNull(\SurveymenuEntries::model()->findByAttributes(['name' => 'restoretestdefault']));

        $db->createCommand()->delete('{{surveymenu_entries}}', "name = 'restoretestdefault'");
    }

    /**
     * A new menu can be created after the default menus with fixed ids were restored.
     */
    public function testCreateMenuAfterRestoreDefaults()
    {
        $this->assertTrue(\Surveymenu::model()->restoreDefaults());

        $menu = new \Surveymenu();
        $menu->name = 'restoretestnewmenu';
        $menu->title = 'Restore test new menu';
        $menu->ordering = 99;
        $menu->changed_at = date('Y-m-d H:i:s');
        $this->assertTrue($menu->save(), print_r($menu->getErrors(), true));
        $menu->delete();
    }
}
