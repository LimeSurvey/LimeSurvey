<?php

/*
   * LimeSurvey
   * Copyright (C) 2013-2026 The LimeSurvey Project Team
   * All rights reserved.
   * License: GNU/GPL License v2 or later, see LICENSE.php
   * LimeSurvey is free software. This version may have been modified pursuant
   * to the GNU General Public License, and as distributed it includes or
   * is derivative of works licensed under the GNU General Public License or
   * other free or open source software licenses.
   * See COPYRIGHT.php for copyright notices and details.
   *
*/

/**
 * Class ExpressionError
 *
 * @property integer $id Primary key
 * @property string $errortime
 * @property integer $sid Survey ID
 * @property integer $gid Group ID
 * @property integer $qid Question ID
 * @property integer $gseq
 * @property integer $qseq
 * @property string $type
 * @property string $eqn
 * @property string $prettyprint
 *
 */
class ExpressionError extends LSActiveRecord
{
    /**
     * @inheritdoc
     * @return ExpressionError
     */
    public static function model($className = __CLASS__)
    {
        /** @var self $model */
        $model = parent::model($className);
        return $model;
    }

    /** @inheritdoc */
    public function tableName()
    {
        return '{{expression_errors}}';
    }

    /** @inheritdoc */
    public function primaryKey()
    {
        return 'scid';
    }


    /**
     * @param array $data
     * @return mixed
     * @deprecated at 2018-01-29 use $model->attributes = $data && $model->save()
     */
    public function insertRecords($data)
    {
        return $this->db->insert('expression_errors', $data);
    }
}
