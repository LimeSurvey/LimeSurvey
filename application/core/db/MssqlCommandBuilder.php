<?php

/**
 * @inheritdoc
 * Replace rewriteLimitOffsetSql for SQL server up to 11 
 * @version 0.1.0
 */

class MssqlCommandBuilder extends CMssqlCommandBuilder
{

    /**
     * @inheritdoc
     * Fixed for new version of MSSQL, issue #18102
     * @see https://github.com/yiisoft/yii2/blob/364e907875fd57ee218085cca796ac5d1c3c8d51/framework/db/mssql/QueryBuilder.php#L73
     */
    protected function rewriteLimitOffsetSql($sql, $limit, $offset)
    {
        if (version_compare(App()->db->getServerVersion(), '11', '<')) {
            return parent::rewriteLimitOffsetSql($sql, $limit, $offset);
        }

        $ordering = $this->findOrdering($sql);
        $orderFix = "";
        if ($ordering === '') {
            // ORDER BY clause is required when FETCH and OFFSET are in the SQL
            $orderFix = 'ORDER BY (SELECT NULL)';
        }
        $sql .= " " . $orderFix;
        /* $limit and $offset must be integer > 0 (see CMssqlCommandBuilder->rewriteLimitOffsetSql)
         * FETCH need OFFSET >= 0 */
        $sql .= " " . "OFFSET $offset ROWS";
        $sql .= " " . "FETCH NEXT $limit ROWS ONLY";
        return $sql;
    }
}
