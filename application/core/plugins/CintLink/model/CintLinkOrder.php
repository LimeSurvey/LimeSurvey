<?php

/**
 * Order model
 *
 * Pretty crude, just containing url, raw (xml) and status
 *
 * @since 2017-07-15
 * @author Olle Haerstedt
 */
class CintLinkOrder extends CActiveRecord
{
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
    }

    public function tableName()
    {
        return '{{plugin_cintlink_orders}}';
    }

    public function primaryKey()
    {
        return 'url';
    }
}
