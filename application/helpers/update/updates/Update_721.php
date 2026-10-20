<?php

namespace LimeSurvey\Helpers\Update;

class Update_721 extends DatabaseUpdateBase
{
    /**
     * Create the tables holding the personal API tokens of the users and their permissions,
     * and link API sessions to the token they were opened with.
     * API tokens can be used instead of the password to log in to RemoteControl or the REST API.
     *
     * @return void
     */
    #[\Override]
    public function up()
    {
        if (!$this->db->schema->getTable('{{user_api_tokens}}', true)) {
            $this->createApiTokenTable();
        }
        if (!$this->db->schema->getTable('{{user_api_token_permissions}}', true)) {
            $this->createApiTokenPermissionTable();
        }
        if (!isset($this->db->schema->getTable('{{sessions}}', true)->columns['api_token_id'])) {
            $this->db->createCommand()->addColumn('{{sessions}}', 'api_token_id', 'integer NULL');
        }
    }

    /**
     * Create the table holding the API tokens.
     *
     * @return void
     */
    private function createApiTokenTable()
    {
        $this->db->createCommand()->createTable(
            '{{user_api_tokens}}',
            [
                'id' => "pk",
                'uid' => "integer NOT NULL",
                'name' => "string(150) NOT NULL",
                'token_hash' => "string(64) NOT NULL",
                'created' => "datetime NOT NULL",
                'last_used' => "datetime NULL",
                'expires' => "datetime NULL",
            ],
            $this->options
        );
        $this->db->createCommand()->createIndex('{{idx1_user_api_tokens}}', '{{user_api_tokens}}', 'token_hash', true);
        $this->db->createCommand()->createIndex('{{idx2_user_api_tokens}}', '{{user_api_tokens}}', 'uid', false);
    }

    /**
     * Create the table holding the permissions of the API tokens.
     * It has the same structure as {{permissions}}, with the token instead of the user.
     *
     * @return void
     */
    private function createApiTokenPermissionTable()
    {
        $this->db->createCommand()->createTable(
            '{{user_api_token_permissions}}',
            [
                'id' => "pk",
                'token_id' => "integer NOT NULL",
                'entity' => "string(50) NOT NULL",
                'entity_id' => "integer NOT NULL",
                'permission' => "string(100) NOT NULL",
                'create_p' => "integer NOT NULL DEFAULT 0",
                'read_p' => "integer NOT NULL DEFAULT 0",
                'update_p' => "integer NOT NULL DEFAULT 0",
                'delete_p' => "integer NOT NULL DEFAULT 0",
                'import_p' => "integer NOT NULL DEFAULT 0",
                'export_p' => "integer NOT NULL DEFAULT 0",
            ],
            $this->options
        );
        $this->db->createCommand()->createIndex(
            '{{idx1_user_api_token_permissions}}',
            '{{user_api_token_permissions}}',
            ['token_id', 'entity', 'entity_id', 'permission'],
            true
        );
    }
}
