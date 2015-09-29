<?php
namespace ls\models;

class Session extends CActiveRecord
{
    /**
     * Returns the static model of ls\models\Session table
     *
     * @static
     * @access public
     * @param string $class
     * @return CActiveRecord
     */
    public static function model($class = __CLASS__)
    {
        return parent::model($class);
    }

    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{sessions}}';
    }

    /**
     * Returns the primary key of this table
     *
     * @access public
     * @return string
     */
    public function primaryKey()
    {
        return 'id';
    }

    public function afterFind()
    {
        $sDatabasetype = Yii::app()->db->getDriverName();
        // MSSQL delivers hex data (except for dblib driver)
        if ($sDatabasetype == 'sqlsrv' || $sDatabasetype == 'mssql') {
            $this->data = $this->hexToStr($this->data);
        }
        // Postgres delivers a stream pointer
        if (gettype($this->data) == 'resource') {
            $this->data = stream_get_contents($this->data, -1, 0);
        }

        return parent::afterFind();
    }

    private function hexToStr($hex)
    {
        $string = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }

        return $string;
    }

}

