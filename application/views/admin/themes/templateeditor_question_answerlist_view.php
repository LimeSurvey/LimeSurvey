<?php
/* Set some part */
$sRows="";
$sRows .= App()->twigRenderer->renderQuestion('/survey/questions/answer/listradio/columns/column_header', array(
    'bIsThemeEditor' => true,
    'iColumnWidth' => 12));
$sRows .= App()->twigRenderer->renderQuestion('/survey/questions/answer/listradio/rows/answer_row', array(
    'bIsThemeEditor' => true,
    'sDisplayStyle' => '',
    'name'          => '1234X56X79',
    'code'          => 'A1',
    'answer'        => gT('One'),
    'checkedState'  => '',
    'myfname'       => 'answer22549X14X68A1',
));
$sRows .= App()->twigRenderer->renderQuestion('/survey/questions/answer/listradio/rows/answer_row', array(
    'bIsThemeEditor' => true,
    'sDisplayStyle' => '',
    'name'          => '1234X56X79',
    'code'          => 'A2',
    'answer'        => gT('Two'),
    'checkedState'  => 'checked',
    'myfname'       => 'answer22549X14X68A2',
));
$sRows .= App()->twigRenderer->renderQuestion('/survey/questions/answer/listradio/rows/answer_row', array(
    'bIsThemeEditor' => true,
    'sDisplayStyle' => '',
    'name'          => '1234X56X79',
    'code'          => 'A3',
    'answer'        => gT('Three'),
    'checkedState'  => '',
    'myfname'       => 'answer22549X14X68A3',
));
$sRows .= App()->twigRenderer->renderQuestion('/survey/questions/answer/listradio/rows/answer_row', array(
    'bIsThemeEditor' => true,
    'sDisplayStyle' => '',
    'name'          => '1234X56X79',
    'code'          => 'A4',
    'answer'        => gT('Four'),
    'checkedState'  => '',
    'myfname'       => 'answer22549X14X68A4',
));
$sRows .= App()->twigRenderer->renderQuestion('/survey/questions/answer/listradio/columns/column_footer', array(
    'bIsThemeEditor' => true,
    'last'=>true));
/* rendering */
echo App()->twigRenderer->renderQuestion('/survey/questions/answer/listradio/answer', array(
    'bIsThemeEditor' => true,
    'sTimer'=>'',
    'sRows' => $sRows,
    'name'  => '1234X56X79',
    'value' => 'A2',
    'coreClass'=>"ls-answers answers-list radio-list",
));
