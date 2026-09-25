<?php

/**
 * RenderClass for Map Question
 *  * The ia Array contains the following
 *  0 => string qid
 *  1 => string sgqa
 *  2 => string questioncode
 *  3 => string question
 *  4 => string type
 *  5 => string gid
 *  6 => string mandatory,
 *  7 => string conditionsexist,
 *  8 => string usedinconditions
 *  0 => string used in group.php for question count
 * 10 => string new group id for question in randomization group (GroupbyGroup Mode)
 *
 */
class RenderMap extends QuestionBaseRenderer
{
    public function getMainView()
    {
        return '/survey/questions/answer/map/location_mapservice/item';
    }

    public function getRows()
    {
        return;
    }

    public function render($sCoreClasses = '')
    {
        $result = @do_map($this->aFieldArray);
        $this->registerAssets();
        return $result;
    }
}
