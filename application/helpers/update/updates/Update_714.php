<?php

namespace LimeSurvey\Helpers\Update;

/**
 * Deduplicate rows in the permissions table and (re)create a unique index on
 * (entity_id, entity, permission, uid).
 *
 * Bug #20076: copying a survey re-inserts every permissions row found for the
 * source survey (Permission::copySurveyPermissions()), which throws (silently
 * caught) a duplicate-key DB error whenever the source survey already carries
 * more than one row for the same (entity_id, entity, permission, uid) tuple.
 * Such duplicates should be impossible under the unique index normally
 * created for this table, but on databases upgraded from older LimeSurvey
 * versions that index was only ever added by Update_166 inside a try/catch
 * that silently gave up if duplicates already existed at the time - leaving
 * some installations with duplicate rows and no unique index to this day.
 *
 * Since Permission::hasPermission() reads a single row via findByAttributes()
 * with no ORDER BY, which of several duplicate rows is "in effect" for a
 * given user is otherwise undefined. Merging the CRUD flags (instead of
 * picking one row and discarding the rest) avoids silently revoking any
 * permission a user already had from either duplicate.
 */
class Update_714 extends DatabaseUpdateBase
{
    private const CRUD_COLUMNS = ['create_p', 'read_p', 'update_p', 'delete_p', 'import_p', 'export_p'];

    /** @var string[] Names this index has been created under across LimeSurvey versions and DB engines. */
    private const KNOWN_INDEX_NAMES = ['idx1_permissions', 'idxPermissions', 'permissions_idx2'];

    /**
     * Cleans up duplicate permissions rows and re-establishes the unique
     * index on (entity_id, entity, permission, uid).
     *
     * @inheritDoc
     * @return void
     */
    #[\Override]
    public function up()
    {
        $this->mergeDuplicatePermissions();
        $this->dropExistingPermissionsIndexes();
        $this->createUniquePermissionsIndex();
    }

    /**
     * Find every (entity_id, entity, permission, uid) tuple with more than one row,
     * merge their CRUD flags into the oldest row, and delete the rest.
     *
     * @return void
     */
    private function mergeDuplicatePermissions()
    {
        $duplicateGroups = $this->db->createCommand()
            ->select('entity_id, entity, permission, uid')
            ->from('{{permissions}}')
            ->group('entity_id, entity, permission, uid')
            ->having('COUNT(*) > 1')
            ->queryAll();

        foreach ($duplicateGroups as $group) {
            $rows = $this->db->createCommand()
                ->select('*')
                ->from('{{permissions}}')
                ->where('entity_id = :entity_id AND entity = :entity AND permission = :permission AND uid = :uid', [
                    ':entity_id'  => $group['entity_id'],
                    ':entity'     => $group['entity'],
                    ':permission' => $group['permission'],
                    ':uid'        => $group['uid'],
                ])
                ->order('id ASC')
                ->queryAll();

            $keeper = array_shift($rows);
            $merged = [];
            foreach (self::CRUD_COLUMNS as $column) {
                $merged[$column] = (int) $keeper[$column];
                foreach ($rows as $row) {
                    $merged[$column] = max($merged[$column], (int) $row[$column]);
                }
            }

            $this->db->createCommand()->update(
                '{{permissions}}',
                $merged,
                'id = :id',
                [':id' => $keeper['id']]
            );

            $duplicateIds = array_map('intval', array_column($rows, 'id'));
            if (!empty($duplicateIds)) {
                $this->db->createCommand()->delete(
                    '{{permissions}}',
                    'id IN (' . implode(',', $duplicateIds) . ')'
                );
            }
        }
    }

    /**
     * Drop whichever of the historically-used index names exist on this
     * installation, ignoring the ones that don't (name and existence vary
     * by DB engine and by how old the installation is).
     *
     * @return void
     */
    private function dropExistingPermissionsIndexes()
    {
        foreach (self::KNOWN_INDEX_NAMES as $indexName) {
            setTransactionBookmark();
            try {
                $this->db->createCommand()->dropIndex('{{' . $indexName . '}}', '{{permissions}}');
            } catch (\Exception $e) {
                rollBackToTransactionBookmark();
            }
        }
    }

    /**
     * (Re)create the unique index on (entity_id, entity, permission, uid),
     * now that duplicate rows have been merged away.
     *
     * @return void
     */
    private function createUniquePermissionsIndex()
    {
        $this->db->createCommand()->createIndex(
            '{{idx1_permissions}}',
            '{{permissions}}',
            ['entity_id', 'entity', 'permission', 'uid'],
            true
        );
    }
}
