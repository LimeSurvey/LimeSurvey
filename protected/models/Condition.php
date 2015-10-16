<?php
namespace ls\models;

/**
 * Class Condition
 */
class Condition extends ActiveRecord
{


    /**
     * Returns the setting's table name to be used by the model
     *
     * @access public
     * @return string
     */
    public function tableName()
    {
        return '{{conditions}}';
    }

    /**
     * Defines the relations for this model
     *
     * @access public
     * @return array
     */
    public function relations()
    {
        return [
            'question' => [self::BELONGS_TO, Question::class, 'qid'],
            // The question the condition is targeting.
            'targetQuestion' => [self::BELONGS_TO, Question::class, 'cqid'],
        ];
    }

    /**
     * Updates the group ID for all conditions
     *
     * @param integer $iSurveyID
     * @param integer $iQuestionID
     * @param integer $iOldGroupID
     * @param integer $iNewGroupID
     */
    public function updateCFieldName($iSurveyID, $iQuestionID, $iOldGroupID, $iNewGroupID)
    {
        $oResults = $this->findAllByAttributes(array('cqid' => $iQuestionID));
        foreach ($oResults as $oRow) {

            $cfnregs = '';
            if (preg_match('/' . $surveyid . "X" . $iOldGroupID . "X" . $iQuestionID . "(.*)/", $oRow->cfieldname,
                    $cfnregs) > 0
            ) {
                $newcfn = $surveyid . "X" . $iNewGroupID . "X" . $iQuestionID . $cfnregs[1];
                $c2query = "UPDATE " . db_table_name('conditions')
                    . " SET cfieldname='{$newcfn}' WHERE cid={$oRow->cid}";

                Yii::app()->db->createCommand($c2query)->query();
            }
        }
    }



}
