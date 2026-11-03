<?php

class NewQuestionAttributesPlugin extends PluginBase
{
    protected static $description = 'Dummy plugin for testing newQuestionAttributes event';
    protected static $name = 'NewQuestionAttributesPlugin';

    /** @var int Number of times the newQuestionAttributes event was dispatched to this plugin */
    public $dispatchCount = 0;

    public function init()
    {
        $this->subscribe('newQuestionAttributes');
    }

    public function newQuestionAttributes()
    {
        $this->dispatchCount++;
        $event = $this->getEvent();
        $questionAttributes = [
            'testAttribute' => [
                'types'     => 'S',
                'category'  => 'Test',
                'sortorder' => 1,
                'inputtype' => 'text',
                'default'   => '',
                'caption'   => 'Test Attribute',
                'help'      => 'This is a dummy attribute for testing purposes.',
                'expression'=> 1,
            ],
            'testAttributeForArray' => [
                'types'     => 'F',
                'category'  => gT('Test'),
                'sortorder' => 1,
                'inputtype' => 'text',
                'default'   => '',
                'caption'   => 'Test Attribute for Array type',
                'help'      => 'This is a dummy attribute for testing purposes.',
                'expression'=> 1,
            ],
            'nonFilteredAttribute' => [
                'types'     => 'S',
                'category'  => 'Test',
                'sortorder' => 1,
                'inputtype' => 'text',
                'default'   => '',
                'caption'   => 'Non Filtered Test Attribute',
                'help'      => 'This is a dummy attribute for testing purposes. It\'s not filtered for XSS',
                'xssfilter' => false,
                'expression'=> 1,
            ],
        ];
        $event->append('questionAttributes', $questionAttributes);
    }
}