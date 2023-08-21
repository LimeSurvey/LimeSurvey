<?php
echo App()->twigRenderer->renderQuestion('/survey/questions/answer/longfreetext/answer', array(
        'bIsThemeEditor'         => true,
        'extraclass'             => 'col-12',
        'coreClass'              =>"ls-answers answer-item text-item",
        'withColumn'             =>true,
        'kpclass'                => '',
        'name'                   => '1234X56X78',
        'basename'               => '1234X56X78',
        'drows'                  => 5,
        'dispVal'                => gT('Some text in this answer'),
        'tiwidth'                => 40,
        'maxlength'              => '',
        'inputsize'              => null,
        'placeholder'            => ''
    ));
?>
