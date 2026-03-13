<?php

namespace LimeSurvey\Helpers\Update;

use CException;
use PDO;

class Update_643 extends DatabaseUpdateBase
{
    /**
     * @inheritDoc
     * @throws CException
     */
    public function up()
    {
        $desc = $this->db->quoteColumnName('desc');
        $this->db->createCommand("UPDATE {{boxes}} SET title = :title, buttontext = :buttontext, {$desc} = :desc WHERE id = :id")
            ->execute([
                ':title' => 'Workspace',
                ':buttontext' => 'View workspace',
                ':desc' => 'View workspace',
                ':id' => 1,
            ]);
    }
}
