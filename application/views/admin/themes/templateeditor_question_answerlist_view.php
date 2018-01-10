<?php
/* Set some part */
$sRows="";
$sRows .= App()->getController()->renderPartial('/survey/questions/answer/listradio/columns/column_header', array('iColumnWidth' => 12), true);
$sRows .= App()->getController()->renderPartial('/survey/questions/answer/listradio/rows/answer_row', array(
    'sDisplayStyle' => '',
    'name'          => '1234X56X79',
    'code'          => 'A1',
    'answer'        => gT('One'),
    'checkedState'  => '',
    'myfname'       => 'answer22549X14X68A1',
), true);
$sRows .= App()->getController()->renderPartial('/survey/questions/answer/listradio/rows/answer_row', array(
    'sDisplayStyle' => '',
    'name'          => '1234X56X79',
    'code'          => 'A2',
    'answer'        => gT('Two'),
    'checkedState'  => 'checked',
    'myfname'       => 'answer22549X14X68A2',
), true);
$sRows .= App()->getController()->renderPartial('/survey/questions/answer/listradio/rows/answer_row', array(
    'sDisplayStyle' => '',
    'name'          => '1234X56X79',
    'code'          => 'A3',
    'answer'        => gT('Three'),
    'checkedState'  => '',
    'myfname'       => 'answer22549X14X68A3',
), true);
$sRows .= App()->getController()->renderPartial('/survey/questions/answer/listradio/rows/answer_row', array(
    'sDisplayStyle' => '',
    'name'          => '1234X56X79',
    'code'          => 'A4',
    'answer'        => gT('Four'),
    'checkedState'  => '',
    'myfname'       => 'answer22549X14X68A4',
), true);
$sRows .= App()->getController()->renderPartial('/survey/questions/answer/listradio/columns/column_footer', array('last'=>true), true);
/* rendering */
App()->getController()->renderPartial('/survey/questions/answer/listradio/answer', array(
        'sTimer'=>'',
        'sRows' => $sRows,
        'name'  => '1234X56X79',
        'value' => 'A2',
        'coreClass'=>"ls-answers answers-list radio-list",
));
