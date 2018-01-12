<?php

require(__DIR__ . '/../../helpers/TbHtml.php');

class Dummy extends CModel
{
    public $text = 'text';
    public $password = 'secret';
    public $url = 'http://www.getyiistrap.com';
    public $email = 'christoffer.niska@gmail.com';
    public $number = 42;
    public $range = 3.33;
    public $date = '2013-08-28';
    public $file = '';
    public $radio = true;
    public $checkbox = false;
    public $uneditable = 'Uneditable text';
    public $search = 'Search query';
    public $textarea = 'Textarea text';
    public $dropdown = '1';
    public $listbox = '1';
    public $radioList = '0';
    public $checkboxList = array('0', '2');

    public function attributeNames()
    {
        return array();
    }
}

class TbHtmlTest extends TbTestCase
{
    /**
     * @var \CodeGuy
     */
    protected $codeGuy;

    protected function _before()
    {
        $this->mockApplication();
    }

    public function testLead()
    {
        $I = $this->codeGuy;
        $html = TbHtml::lead('Lead text');
        $p = $I->createNode($html, 'p.lead');
        $I->seeNodeText($p, 'Lead text');
    }

    public function testSmall()
    {
        $I = $this->codeGuy;
        $html = TbHtml::small('Small text');
        $small = $I->createNode($html, 'small');
        $I->seeNodeText($small, 'Small text');
    }

    public function testBold()
    {
        $I = $this->codeGuy;
        $html = TbHtml::b('Bold text');
        $strong = $I->createNode($html, 'strong');
        $I->seeNodeText($strong, 'Bold text');
    }

    public function testItalic()
    {
        $I = $this->codeGuy;
        $html = TbHtml::i('Italic text');
        $em = $I->createNode($html, 'em');
        $I->seeNodeText($em, 'Italic text');
    }

    public function testEmphasize()
    {
        $I = $this->codeGuy;

        $html = TbHtml::em(
            'Warning text',
            array(
                'color' => TbHtml::TEXT_COLOR_WARNING,
            )
        );
        $span = $I->createNode($html, 'p.text-warning');
        $I->seeNodeText($span, 'Warning text');

        $html = TbHtml::em(
            'Success text',
            array(
                'color' => TbHtml::TEXT_COLOR_SUCCESS,
            ),
            'span'
        );
        $span = $I->createNode($html, 'span.text-success');
        $I->seeNodeText($span, 'Success text');
    }

    public function testMuted()
    {
        $I = $this->codeGuy;
        $html = TbHtml::muted('Muted text');
        $p = $I->createNode($html, 'p.muted');
        $I->seeNodeText($p, 'Muted text');
    }

    public function testMutedSpan()
    {
        $I = $this->codeGuy;
        $html = TbHtml::mutedSpan('Muted text');
        $span = $I->createNode($html, 'span.muted');
        $I->seeNodeText($span, 'Muted text');
    }

    public function testAbbreviation()
    {
        $I = $this->codeGuy;
        $html = TbHtml::abbr('Abbreviation', 'Word');
        $abbr = $I->createNode($html, 'abbr');
        $I->seeNodeAttribute($abbr, 'title', 'Word');
        $I->seeNodeText($abbr, 'Abbreviation');
    }

    public function testSmallAbbreviation()
    {
        $I = $this->codeGuy;
        $html = TbHtml::smallAbbr('Abbreviation', 'Word');
        $abbr = $I->createNode($html, 'abbr');
        $I->seeNodeAttribute($abbr, 'title', 'Word');
        $I->seeNodeCssClass($abbr, 'initialism');
        $I->seeNodeText($abbr, 'Abbreviation');
    }

    public function testAddress()
    {
        $I = $this->codeGuy;
        $html = TbHtml::address('Address text');
        $addr = $I->createNode($html, 'address');
        $I->seeNodeText($addr, 'Address text');
    }

    public function testQuote()
    {
        $I = $this->codeGuy;
        $html = TbHtml::quote(
            'Quote text',
            array(
                'paragraphOptions' => array('class' => 'paragraph'),
                'source' => 'Source text',
                'sourceOptions' => array('class' => 'source'),
                'cite' => 'Cited text',
                'citeOptions' => array('class' => 'cite'),
            )
        );
        $blockquote = $I->createNode($html, 'blockquote');
        $I->seeNodeChildren($blockquote, array('p', 'small'));
        $p = $blockquote->filter('p');
        $I->seeNodeCssClass($p, 'paragraph');
        $I->seeNodeText($p, 'Quote text');
        $small = $blockquote->filter('blockquote > small');
        $I->seeNodeCssClass($small, 'source');
        $I->seeNodeText($small, 'Source text');
        $cite = $small->filter('small > cite');
        $I->seeNodeCssClass($cite, 'cite');
        $I->seeNodeText($cite, 'Cited text');
    }

    public function testQuotePullRight()
    {
        $I = $this->codeGuy;
        $html = TbHtml::quote(
            'Quote text',
            array(
                'paragraphOptions' => array('class' => 'paragraph'),
                'source' => 'Source text',
                'sourceOptions' => array('class' => 'source'),
                'cite' => 'Cited text',
                'citeOptions' => array('class' => 'cite'),
                'class' => 'pull-right',
            )
        );
        $blockquote = $I->createNode($html, 'blockquote');
        $I->seeNodeChildren($blockquote, array('p', 'small'));
        $I->seeNodeCssClass($blockquote, 'pull-right');
        $p = $blockquote->filter('p');
        $I->seeNodeCssClass($p, 'paragraph');
        $I->seeNodeText($p, 'Quote text');
        $small = $blockquote->filter('blockquote > small');
        $I->seeNodeCssClass($small, 'source');
        $I->seeNodeText($small, 'Source text');
        $cite = $small->filter('small > cite');
        $I->seeNodeCssClass($cite, 'cite');
        $I->seeNodeText($cite, 'Cited text');
    }

    public function testHelp()
    {
        $I = $this->codeGuy;
        $html = TbHtml::help('Help text');
        $p = $I->createNode($html, 'p.help-block');
        $I->seeNodeText($p, 'Help text');
    }

    public function testCode()
    {
        $I = $this->codeGuy;
        $html = TbHtml::code('Source code');
        $code = $I->createNode($html, 'code');
        $I->seeNodeText($code, 'Source code');
    }

    public function testCodeBlock()
    {
        $I = $this->codeGuy;
        $html = TbHtml::codeBlock('Source code');
        $pre = $I->createNode($html, 'pre');
        $I->seeNodeText($pre, 'Source code');
    }

    public function testTag()
    {
        $I = $this->codeGuy;
        $html = TbHtml::tag(
            'div',
            array(
                'textAlign' => TbHtml::TEXT_ALIGN_RIGHT,
                'pull' => TbHtml::PULL_RIGHT,
                'span' => 3,
            ),
            'Content'
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'pull-right col-md-3 text-right');
    }

    public function testOpenTag()
    {
        $I = $this->codeGuy;
        $html = TbHtml::openTag(
            'p',
            array(
                'class' => 'tag',
            )
        );
        $p = $I->createNode($html, 'p');
        $I->seeNodeCssClass($p, 'tag');
    }

    public function testXsCol()
    {
        $I = $this->codeGuy;
        $html = TbHtml::tag(
            'div',
            array(
                'xs' => 3,
            ),
            'Content'
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'col-xs-3');
    }

    public function testSmCol()
    {
        $I = $this->codeGuy;
        $html = TbHtml::tag(
            'div',
            array(
                'sm' => 3,
            ),
            'Content'
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'col-sm-3');
    }

    public function testMdCol()
    {
        $I = $this->codeGuy;
        $html = TbHtml::tag(
            'div',
            array(
                'md' => 3,
            ),
            'Content'
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'col-md-3');
    }

    public function testLgCol()
    {
        $I = $this->codeGuy;
        $html = TbHtml::tag(
            'div',
            array(
                'lg' => 3,
            ),
            'Content'
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'col-lg-3');
    }

    public function testHybridCol()
    {
        $I = $this->codeGuy;
        $html = TbHtml::tag(
            'div',
            array(
                'xs' => 12,
                'md' => 8,
            ),
            'Content'
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, array('col-xs-12', 'col-md-8'));
    }

    public function testForm()
    {
        $I = $this->codeGuy;
        $html = TbHtml::formTb(
            TbHtml::FORM_LAYOUT_VERTICAL,
            '#',
            'post',
            array(
                'class' => 'form',
            )
        );
        $form = $I->createNode($html, 'form.form-vertical');
        $I->seeNodeCssClass($form, 'form');
        $I->seeNodeAttributes(
            $form,
            array(
                'action' => '#',
                'method' => 'post'
            )
        );
    }

    public function testBeginForm()
    {
        $I = $this->codeGuy;
        $html = TbHtml::beginFormTb(TbHtml::FORM_LAYOUT_VERTICAL, '#');
        $form = $I->createNode($html, 'form');
        $I->seeNodeCssClass($form, 'form-vertical');
    }

    public function testStatefulForm()
    {
        $I = $this->codeGuy;
        $html = TbHtml::statefulFormTb(TbHtml::FORM_LAYOUT_VERTICAL, '#');
        $body = $I->createNode($html);
        $form = $body->filter('form');
        $I->seeNodeCssClass($form, 'form-vertical');
        $div = $body->filter('div');
        $I->seeNodeCssStyle($div, 'display: none');
        $input = $div->filter('input[type=hidden]');
        $I->seeNodeAttributes(
            $input,
            array(
                'name' => 'YII_PAGE_STATE',
                'value' => '',
            )
        );
    }

    public function testTextField()
    {
        $I = $this->codeGuy;

        $html = TbHtml::textField(
            'text',
            'text',
            array(
                'class' => 'input',
            )
        );
        $input = $I->createNode($html, 'input[type=text].form-control');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input form-control',
                'id' => 'text',
                'name' => 'text',
                'value' => 'text',
            )
        );

        $html = TbHtml::textField(
            'text',
            'text',
            array(
                'prepend' => 'Prepend text',
            )
        );
        $div = $I->createNode($html, 'div.input-group');
        $I->seeNodeCssClass($div, 'input-group');
        $I->seeNodeChildren($div, array('span', 'input'));
        $span = $div->filter('span.input-group-addon');
        $I->seeNodeText($span, 'Prepend text');

        $html = TbHtml::textField(
            'text',
            'text',
            array(
                'append' => 'Append text',
            )
        );
        $div = $I->createNode($html, 'div.input-group');
        $I->seeNodeCssClass($div, 'input-group');
        $I->seeNodeChildren($div, array('input', 'span'));
        $span = $div->filter('span.input-group-addon');
        $I->seeNodeText($span, 'Append text');

        $html = TbHtml::textField(
            'text',
            'text',
            array(
                'prepend' => 'Prepend text',
                'append' => 'Append text',
            )
        );
        $div = $I->createNode($html, 'div.input-group');
        $I->seeNodeCssClass($div, 'input-group');
        $I->seeNodeChildren($div, array('span', 'input', 'span'));
//        $span = $div->filter('span.input-group-addon');
//        $I->seeNodeText($span, array('Prepend text', 'Append text'));

        // @todo Deprecated in BS3?
        $html = TbHtml::textField(
            'text',
            'text',
            array(
                'block' => true,
            )
        );
        $input = $I->createNode($html, 'input');
        $I->seeNodeCssClass($input, 'input-block-level');

        $html = TbHtml::textField(
            'text',
            'text',
            array(
                'span' => 6,
            )
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'col-md-6');
        $I->seeNodeChildren($div, array('input'));

        $html = TbHtml::textField(
            'text',
            'text',
            array(
                'xs' => 6,
                'md' => 6,
            )
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'col-xs-6');
        $I->seeNodeCssClass($div, 'col-md-6');
        $I->seeNodeChildren($div, array('input'));

        $html = TbHtml::textField(
            'text',
            'text',
            array(
                'class' => 'col-md-4',
            )
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'col-md-4');
        $I->seeNodeChildren($div, array('input'));
    }

    public function testPasswordField()
    {
        $I = $this->codeGuy;
        $html = TbHtml::passwordField(
            'password',
            'secret',
            array(
                'class' => 'input',
            )
        );
        $input = $I->createNode($html, 'input[type=password].form-control');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input form-control',
                'id' => 'password',
                'name' => 'password',
                'value' => 'secret',
            )
        );
    }

    public function testUrlField()
    {
        $I = $this->codeGuy;
        $html = TbHtml::urlField(
            'url',
            'http://www.getyiistrap.com',
            array(
                'class' => 'input',
            )
        );
        $input = $I->createNode($html, 'input[type=url].form-control');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input form-control',
                'id' => 'url',
                'name' => 'url',
                'value' => 'http://www.getyiistrap.com',
            )
        );
    }

    public function testEmailField()
    {
        $I = $this->codeGuy;
        $html = TbHtml::emailField(
            'email',
            'christoffer.niska@gmail.com',
            array(
                'class' => 'input',
            )
        );
        $input = $I->createNode($html, 'input[type=email].form-control');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input form-control',
                'id' => 'email',
                'name' => 'email',
                'value' => 'christoffer.niska@gmail.com',
            )
        );
    }

    public function testNumberField()
    {
        $I = $this->codeGuy;
        $html = TbHtml::numberField(
            'number',
            42,
            array(
                'class' => 'input',
            )
        );
        $input = $I->createNode($html, 'input[type=number].form-control');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input form-control',
                'id' => 'number',
                'name' => 'number',
                'value' => '42',
            )
        );
    }

    public function testRangeField()
    {
        $I = $this->codeGuy;
        $html = TbHtml::rangeField(
            'range',
            3.33,
            array(
                'class' => 'input',
            )
        );
        $input = $I->createNode($html, 'input[type=range].form-control');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input form-control',
                'id' => 'range',
                'name' => 'range',
                'value' => '3.33',
            )
        );
    }

    public function testDateField()
    {
        $I = $this->codeGuy;
        $html = TbHtml::dateField(
            'date',
            '2013-08-28',
            array(
                'class' => 'input',
            )
        );
        $input = $I->createNode($html, 'input[type=date].form-control');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input form-control',
                'id' => 'date',
                'name' => 'date',
                'value' => '2013-08-28',
            )
        );
    }

    public function testFileField()
    {
        $I = $this->codeGuy;
        $html = TbHtml::fileField(
            'file',
            '',
            array(
                'class' => 'input',
            )
        );
        $input = $I->createNode($html, 'input[type=file]');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input',
                'id' => 'file',
                'name' => 'file',
                'value' => '',
            )
        );
    }

    public function testTextArea()
    {
        $I = $this->codeGuy;
        $html = TbHtml::textArea(
            'textarea',
            'Textarea text',
            array(
                'class' => 'textarea',
            )
        );
        $textarea = $I->createNode($html, 'textarea.form-control');
        $I->seeNodeAttributes(
            $textarea,
            array(
                'class' => 'textarea form-control',
                'id' => 'textarea',
                'name' => 'textarea',
            )
        );
        $I->seeNodeText($textarea, 'Textarea text');

        $html = TbHtml::textArea(
            'textarea',
            'Textarea text',
            array(
                'xs' => 6,
                'md' => 6,
                'class' => 'textarea',
            )
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'col-xs-6');
        $I->seeNodeCssClass($div, 'col-md-6');
        $textarea = $div->filter('textarea');
        $I->seeNodeAttributes(
            $textarea,
            array(
                'class' => 'textarea form-control',
                'id' => 'textarea',
                'name' => 'textarea',
            )
        );
        $I->seeNodeText($textarea, 'Textarea text');
    }

    public function testRadioButton()
    {
        $I = $this->codeGuy;

        $html = TbHtml::radioButton(
            'radio',
            false,
            array(
                'class' => 'input',
                'label' => 'Label text',
            )
        );
        $label = $I->createNode($html, 'label');
        $I->seeNodePattern($label, '/> Label text$/');
        $input = $label->filter('input[type=radio]');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input',
                'id' => 'radio',
                'name' => 'radio',
                'value' => '1',
            )
        );
        $I->dontSeeNodeAttribute($input, 'checked');

        $html = TbHtml::radioButton('radio', true);
        $input = $I->createNode($html, 'input[type=radio]');
        $I->seeNodeAttribute($input, 'checked', 'checked');
    }

    public function testCheckBox()
    {
        $I = $this->codeGuy;
        $html = TbHtml::checkBox(
            'checkbox',
            false,
            array(
                'class' => 'input',
                'label' => 'Label text',
            )
        );
        $label = $I->createNode($html, 'label');
        $I->seeNodePattern($label, '/> Label text$/');
        $input = $label->filter('input[type=checkbox]');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input',
                'id' => 'checkbox',
                'name' => 'checkbox',
                'value' => '1',
            )
        );
        $I->dontSeeNodeAttribute($input, 'checked');

        $html = TbHtml::checkBox('checkbox', true);
        $input = $I->createNode($html, 'input[type=checkbox]');
        $I->seeNodeAttribute($input, 'checked', 'checked');
    }

    public function testDropDownList()
    {
        $I = $this->codeGuy;
        $html = TbHtml::dropDownList(
            'dropdown',
            null,
            array('1', '2', '3', '4', '5'),
            array(
                'class' => 'list',
                'empty' => 'Empty text',
                'size' => TbHtml::INPUT_SIZE_LARGE,
                'textAlign' => TbHtml::TEXT_ALIGN_CENTER,
            )
        );
        $select = $I->createNode($html, 'select.form-control');
        $I->seeNodeCssClass($select, 'input-large text-center list');
        $I->dontSeeNodeAttribute($select, 'size');

        $I = $this->codeGuy;
        $html = TbHtml::dropDownList(
            'dropdown',
            null,
            array('1', '2', '3', '4', '5'),
            array(
                'xs' => 6,
                'md' => 6,
                'class' => 'list',
                'empty' => 'Empty text',
                'size' => TbHtml::INPUT_SIZE_LARGE,
                'textAlign' => TbHtml::TEXT_ALIGN_CENTER,
            )
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'col-xs-6');
        $I->seeNodeCssClass($div, 'col-md-6');
        $select = $div->filter('select.form-control');
        $I->seeNodeCssClass($select, 'input-large text-center list');
        $I->dontSeeNodeAttribute($select, 'size');
    }

    public function testListBox()
    {
        $I = $this->codeGuy;

        $html = TbHtml::listBox(
            'listbox',
            null,
            array('1', '2', '3', '4', '5'),
            array(
                'class' => 'list',
                'empty' => 'Empty text',
                'size' => TbHtml::INPUT_SIZE_LARGE,
                'textAlign' => TbHtml::TEXT_ALIGN_CENTER,
            )
        );
        $select = $I->createNode($html, 'select.form-control');
        $I->seeNodeCssClass($select, 'input-large text-center list');
        $I->seeNodeAttributes(
            $select,
            array(
                'name' => 'listbox',
                'id' => 'listbox',
                'size' => 4,
            )
        );

        $html = TbHtml::listBox(
            'listbox',
            null,
            array('1', '2', '3', '4', '5'),
            array(
                'multiple' => true,
            )
        );
        $select = $I->createNode($html, 'select');
        $I->seeNodeAttribute($select, 'name', 'listbox[]');
    }

    public function testRadioButtonList()
    {
        $I = $this->codeGuy;
        $html = TbHtml::radioButtonList(
            'radioList',
            null,
            array('Option 1', 'Option 2', 'Option 3'),
            array(
                'separator' => '<br>',
                'container' => 'div',
                'containerOptions' => array('class' => 'container'),
            )
        );
        $container = $I->createNode($html, 'div.container');
        $I->seeNodeChildren($container, array('label', 'br', 'label', 'br', 'label'));
        $label = $container->filter('label')->first();
        $I->seeNodePattern($label, '/> Option 1$/');
        $input = $label->filter('input[type=radio]');
        $I->seeNodeAttributes(
            $input,
            array(
                'id' => 'radioList_0',
                'name' => 'radioList',
                'value' => '0',
            )
        );
    }

    public function testInlineRadioButtonList()
    {
        $I = $this->codeGuy;
        $html = TbHtml::inlineRadioButtonList(
            'radioList',
            null,
            array('Option 1', 'Option 2', 'Option 3')
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeNumChildren($div, 3);
        $I->seeNodeChildren($div, array('label.radio-inline', 'label.radio-inline', 'label.radio-inline'));
    }

    public function testCheckboxList()
    {
        $I = $this->codeGuy;

        $html = TbHtml::checkBoxList(
            'checkboxList',
            null,
            array('Option 1', 'Option 2', 'Option 3'),
            array(
                'separator' => '<br>',
                'container' => 'div',
                'containerOptions' => array('class' => 'container'),
            )
        );
        $container = $I->createNode($html, 'div.container');
        $I->seeNodeChildren($container, array('div.checkbox', 'div.checkbox', 'div.checkbox'));
        $I->seeNodeChildren($container, array('label', 'br', 'label', 'br', 'label'));
        $label = $container->filter('label')->first();
        $I->seeNodePattern($label, '/> Option 1$/');
        $input = $label->filter('input[type=checkbox]');
        $I->seeNodeAttributes(
            $input,
            array(
                'id' => 'checkboxList_0',
                'name' => 'checkboxList[]',
                'value' => '0',
            )
        );

        $html = TbHtml::checkBoxList(
            'checkboxList',
            null,
            array('Option 1', 'Option 2', 'Option 3'),
            array(
                'checkAll' => true,
            )
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeChildren(
            $div,
            array('input[type=checkbox]', 'label', 'label', 'label', 'label')
        );
        $label = $div->filter('label')->first();
        $input = $label->filter('input');
        $I->seeNodeAttribute($input, 'name', 'checkboxList_all');

        $html = TbHtml::checkBoxList(
            'checkboxList',
            null,
            array('Option 1', 'Option 2', 'Option 3'),
            array(
                'checkAll' => true,
                'checkAllLast' => true,
            )
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeChildren(
            $div,
            array('label', 'label', 'label', 'label', 'input[type=checkbox]')
        );
        $label = $div->filter('label')->last();
        $input = $label->filter('input');
        $I->seeNodeAttribute($input, 'name', 'checkboxList_all');
    }

    public function testInlineCheckBoxList()
    {
        $I = $this->codeGuy;
        $html = TbHtml::inlineCheckBoxList(
            'checkboxList',
            null,
            array('Option 1', 'Option 2', 'Option 3')
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeNumChildren($div, 3);
        $I->seeNodeChildren(
            $div,
            array('label.checkbox-inline', 'label.checkbox-inline', 'label.checkbox-inline')
        );
    }

    public function testUneditableField()
    {
        $I = $this->codeGuy;
        $html = TbHtml::uneditableField(
            'Uneditable text',
            array(
                'class' => 'span',
            )
        );
        $span = $I->createNode($html, 'span.uneditable-input');
        $I->seeNodeCssClass($span, 'span');
        $I->seeNodeText($span, 'Uneditable text');
    }

    public function testSearchQueryField()
    {
        $I = $this->codeGuy;
        $html = TbHtml::searchQueryField(
            'search',
            'Search query',
            array(
                'class' => 'input',
            )
        );
        $input = $I->createNode($html, 'input[type=search]');
        $I->seeNodeCssClass($input, 'input');
        $I->seeNodeAttributes(
            $input,
            array(
                'id' => 'search',
                'name' => 'search',
                'value' => 'Search query',
            )
        );
    }

    public function testTextFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::textFieldControlGroup('text', 'text');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('input[type=text].form-control'));
    }

    public function testPasswordFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::passwordFieldControlGroup('password', 'secret');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('input[type=password].form-control'));
    }

    public function testUrlFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::urlFieldControlGroup('url', 'url');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('input[type=url].form-control'));
    }

    public function testEmailFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::emailFieldControlGroup('email', 'email');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('input[type=email].form-control'));
    }

    public function testNumberFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::numberFieldControlGroup('number', 'number');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('input[type=number].form-control'));
    }

    public function testRangeFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::rangeFieldControlGroup('range', 'range');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('input[type=range].form-control'));
    }

    public function testDateFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::dateFieldControlGroup('date', 'date');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('input[type=date].form-control'));
    }

    public function testFileFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::fileFieldControlGroup('file', 'file');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('input[type=file]'));
    }

    public function testTextAreaControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::textAreaControlGroup('textarea', 'Textarea text');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('textarea.form-control'));
    }

    public function testRadioButtonControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::radioButtonControlGroup(
            'radio',
            false,
            array(
                'label' => 'Label text',
            )
        );
        $group = $I->createNode($html, 'div.radio');
        $label = $group->filter('label');
        $I->seeNodeChildren($label, array('input[type=radio]'));
    }

    public function testCheckBoxControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::checkBoxControlGroup(
            'checkbox',
            false,
            array(
                'label' => 'Label text',
            )
        );
        $group = $I->createNode($html, 'div.checkbox');
        $label = $group->filter('label');
        $I->seeNodeChildren($label, array('input[type=checkbox]'));
    }

    public function testDropDownListControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::dropDownListControlGroup(
            'dropdown',
            '',
            array('1', '2', '3', '4', '5')
        );
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->seeNodeChildren($controls, array('select.form-control'));
    }

    public function testListBoxControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::listBoxControlGroup(
            'listbox',
            '',
            array('1', '2', '3', '4', '5')
        );
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->seeNodeChildren($controls, array('select.form-control'));
    }

    public function testRadioButtonListControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::radioButtonListControlGroup(
            'radioList',
            '1',
            array('Option 1', 'Option 2', 'Option 3')
        );
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->seeNodeChildren($controls, array('label', 'label', 'label'));
    }

    public function testInlineRadioButtonListControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::inlineRadioButtonListControlGroup(
            'radioList',
            '1',
            array('Option 1', 'Option 2', 'Option 3')
        );
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->seeNodeChildren($controls, array('label.radio-inline', 'label.radio-inline', 'label.radio-inline'));
    }

    public function testCheckBoxListControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::checkBoxListControlGroup(
            'checkboxList',
            array('0', '2'),
            array('Option 1', 'Option 2', 'Option 3')
        );
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->seeNodeChildren($controls, array('label', 'label', 'label'));
    }

    public function testInlineCheckBoxListControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::inlineCheckBoxListControlGroup(
            'checkboxList',
            array('0', '2'),
            array('Option 1', 'Option 2', 'Option 3')
        );
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->seeNodeChildren(
            $controls,
            array('label.checkbox-inline', 'label.checkbox-inline', 'label.checkbox-inline')
        );
    }

    public function testUneditableFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::uneditableFieldControlGroup('Uneditable text');
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->seeNodeChildren($controls, array('span.uneditable-input'));
    }

    public function testSearchQueryControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::searchQueryControlGroup('Search query');
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->dontSeeNodeCssClass($controls, 'controls');
        $I->seeNodeChildren($controls, array('input[type=search]'));
    }

    public function testControlGroup()
    {
        $I = $this->codeGuy;

        $html = TbHtml::controlGroup(
            TbHtml::INPUT_TYPE_TEXT,
            'text',
            '',
            array(
                'color' => TbHtml::INPUT_COLOR_SUCCESS,
                'groupOptions' => array('class' => 'group'),
                'label' => 'Label text',
                'labelOptions' => array('class' => 'label'),
                'help' => 'Help text',
                'helpOptions' => array('class' => 'help'),
            )
        );
        $group = $I->createNode($html, 'div.form-group');
        $I->seeNodeCssClass($group, 'has-success group');
        $I->seeNodeChildren($group, array('label.control-label', 'div'));
        $label = $group->filter('label.control-label');
        $I->seeNodeCssClass($label, 'label');
        $I->seeNodeAttribute($label, 'for', 'text');
        $I->seeNodeText($label, 'Label text');
        $controls = $group->filter('div');
        $I->seeNodeChildren($controls, array('input', 'span'));
        $input = $controls->filter('input[type=text].form-control');
        $I->seeNodeAttributes(
            $input,
            array(
                'id' => 'text',
                'name' => 'text',
                'value' => '',
            )
        );
        $help = $controls->filter('span.help-block');
        $I->seeNodeCssClass($help, 'help');
        $I->seeNodeText($help, 'Help text');

        $html = TbHtml::controlGroup(
            TbHtml::INPUT_TYPE_RADIOBUTTON,
            'radio',
            true,
            array(
                'label' => 'Label text',
            )
        );
        $group = $I->createNode($html, 'div.radio');
        $label = $group->filter('label');
        $I->seeNodePattern($label, '/> Label text$/');
        $radio = $label->filter('input[type=radio]');
        $I->seeNodeAttributes(
            $radio,
            array(
                'checked' => 'checked',
                'id' => 'radio',
                'name' => 'radio',
                'value' => '1',
            )
        );
    }

    public function testCustomControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::customControlGroup(
            '<div class="widget"></div>',
            'custom',
            array(
                'label' => false,
            )
        );
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->seeNodeChildren($controls, array('div.widget'));
    }

    public function testActiveTextField()
    {
        $I = $this->codeGuy;

        $html = TbHtml::activeTextField(
            new Dummy,
            'text',
            array(
                'class' => 'input'
            )
        );
        $input = $I->createNode($html, 'input[type=text].form-control');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input form-control',
                'id' => 'Dummy_text',
                'name' => 'Dummy[text]',
                'value' => 'text',
            )
        );

        $html = TbHtml::activeTextField(
            new Dummy,
            'text',
            array(
                'prepend' => 'Prepend text',
            )
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'input-group');
        $I->seeNodeChildren($div, array('span', 'input'));
        $span = $div->filter('span.input-group-addon');
        $I->seeNodeText($span, 'Prepend text');

        $html = TbHtml::activeTextField(
            new Dummy,
            'text',
            array(
                'append' => 'Append text',
            )
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'input-group');
        $I->seeNodeChildren($div, array('input', 'span'));
        $span = $div->filter('span.input-group-addon');
        $I->seeNodeText($span, 'Append text');

        $html = TbHtml::activeTextField(
            new Dummy,
            'text',
            array(
                'prepend' => 'Prepend text',
                'append' => 'Append text',
            )
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'input-group');
        $I->seeNodeChildren($div, array('span.input-group-addon', 'input', 'span.input-group-addon'));

        $html = TbHtml::activeTextField(
            new Dummy,
            'text',
            array(
                'span' => 6,
            )
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'col-md-6');
        $I->seeNodeChildren($div, array('input'));

        $html = TbHtml::activeTextField(
            new Dummy,
            'text',
            array(
                'xs' => 6,
                'md' => 6,
            )
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'col-xs-6');
        $I->seeNodeCssClass($div, 'col-md-6');
        $I->seeNodeChildren($div, array('input'));

        $html = TbHtml::activeTextField(
            new Dummy,
            'text',
            array(
                'class' => 'col-md-4',
            )
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'col-md-4');
        $I->seeNodeChildren($div, array('input'));
    }

    public function testActivePasswordField()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activePasswordField(
            new Dummy,
            'password',
            array(
                'class' => 'input'
            )
        );
        $input = $I->createNode($html, 'input[type=password].form-control');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input form-control',
                'id' => 'Dummy_password',
                'name' => 'Dummy[password]',
                'value' => 'secret',
            )
        );
    }

    public function testActiveUrlField()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeUrlField(
            new Dummy,
            'url',
            array(
                'class' => 'input'
            )
        );
        $input = $I->createNode($html, 'input[type=url].form-control');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input form-control',
                'id' => 'Dummy_url',
                'name' => 'Dummy[url]',
                'value' => 'http://www.getyiistrap.com',
            )
        );
    }

    public function testActiveEmailField()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeEmailField(
            new Dummy,
            'email',
            array(
                'class' => 'input'
            )
        );
        $input = $I->createNode($html, 'input[type=email].form-control');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input form-control',
                'id' => 'Dummy_email',
                'name' => 'Dummy[email]',
                'value' => 'christoffer.niska@gmail.com',
            )
        );
    }

    public function testActiveNumberField()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeNumberField(
            new Dummy,
            'number',
            array(
                'class' => 'input'
            )
        );
        $input = $I->createNode($html, 'input[type=number].form-control');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input form-control',
                'id' => 'Dummy_number',
                'name' => 'Dummy[number]',
                'value' => '42',
            )
        );
    }

    public function testActiveRangeField()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeRangeField(
            new Dummy,
            'range',
            array(
                'class' => 'input'
            )
        );
        $input = $I->createNode($html, 'input[type=range].form-control');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input form-control',
                'id' => 'Dummy_range',
                'name' => 'Dummy[range]',
                'value' => '3.33',
            )
        );
    }

    public function testActiveDateField()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeDateField(
            new Dummy,
            'date',
            array(
                'class' => 'input'
            )
        );
        $input = $I->createNode($html, 'input[type=date].form-control');
        $I->seeNodeAttributes(
            $input,
            array(
                'class' => 'input form-control',
                'id' => 'Dummy_date',
                'name' => 'Dummy[date]',
                'value' => '2013-08-28',
            )
        );
    }

    public function testActiveTextArea()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeTextArea(
            new Dummy,
            'textarea',
            array(
                'class' => 'textarea',
            )
        );
        $textarea = $I->createNode($html, 'textarea.form-control');
        $I->seeNodeAttributes(
            $textarea,
            array(
                'class' => 'textarea form-control',
                'id' => 'Dummy_textarea',
                'name' => 'Dummy[textarea]',
            )
        );
        $I->seeNodeText($textarea, 'Textarea text');

        $html = TbHtml::activeTextArea(
            new Dummy,
            'textarea',
            array(
                'xs' => 6,
                'md' => 6,
                'class' => 'textarea',
            )
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'col-xs-6');
        $I->seeNodeCssClass($div, 'col-md-6');
        $textarea = $div->filter('textarea');
        $I->seeNodeAttributes(
            $textarea,
            array(
                'class' => 'textarea form-control',
                'id' => 'Dummy_textarea',
                'name' => 'Dummy[textarea]',
            )
        );
        $I->seeNodeText($textarea, 'Textarea text');
    }

    public function testActiveRadioButton()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeRadioButton(
            new Dummy,
            'radio',
            array(
                'class' => 'input',
                'label' => 'Label text',
            )
        );
        $body = $I->createNode($html, 'body');
        $hidden = $body->filter('input[type=hidden]');
        $I->seeNodeAttributes(
            $hidden,
            array(
                'id' => 'ytDummy_radio',
                'name' => 'Dummy[radio]',
                'value' => '0',
            )
        );
        $label = $body->filter('label');
        $radio = $label->filter('input[type=radio]');
        $I->seeNodeAttributes(
            $radio,
            array(
                'class' => 'input',
                'checked' => 'checked',
                'id' => 'Dummy_radio',
                'name' => 'Dummy[radio]',
                'value' => '1',
            )
        );
        $I->seeNodePattern($label, '/> Label text$/');
    }

    public function testActiveCheckBox()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeCheckBox(
            new Dummy,
            'checkbox',
            array(
                'class' => 'input',
                'label' => 'Label text',
            )
        );
        $body = $I->createNode($html, 'body');
        $hidden = $body->filter('input[type=hidden]');
        $I->seeNodeAttributes(
            $hidden,
            array(
                'id' => 'ytDummy_checkbox',
                'name' => 'Dummy[checkbox]',
                'value' => '0',
            )
        );
        $label = $body->filter('label');
        $checkbox = $label->filter('input[type=checkbox]');
        $I->seeNodeAttributes(
            $checkbox,
            array(
                'class' => 'input',
                'id' => 'Dummy_checkbox',
                'name' => 'Dummy[checkbox]',
                'value' => '1',
            )
        );
        $I->seeNodePattern($label, '/> Label text$/');
    }

    public function testActiveDropDownList()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeDropDownList(
            new Dummy,
            'dropdown',
            array('1', '2', '3', '4', '5'),
            array(
                'class' => 'list',
                'empty' => 'Empty text',
                'size' => TbHtml::INPUT_SIZE_LARGE,
                'textAlign' => TbHtml::TEXT_ALIGN_CENTER,
            )
        );
        $select = $I->createNode($html, 'select');
        $I->seeNodeCssClass($select, 'input-large text-center list');
        $I->dontSeeNodeAttribute($select, 'size');

        $html = TbHtml::activeDropDownList(
            new Dummy,
            'dropdown',
            array('1', '2', '3', '4', '5'),
            array(
                'xs' => 6,
                'md' => 6,
                'class' => 'list',
                'empty' => 'Empty text',
                'size' => TbHtml::INPUT_SIZE_LARGE,
                'textAlign' => TbHtml::TEXT_ALIGN_CENTER,
            )
        );
        $div = $I->createNode($html, 'div');
        $I->seeNodeCssClass($div, 'col-xs-6');
        $I->seeNodeCssClass($div, 'col-md-6');
        $select = $div->filter('select');
        $I->seeNodeCssClass($select, 'input-large text-center list');
        $I->dontSeeNodeAttribute($select, 'size');
    }

    public function testActiveListBox()
    {
        $I = $this->codeGuy;

        $html = TbHtml::activeListBox(
            new Dummy,
            'listbox',
            array('1', '2', '3', '4', '5'),
            array(
                'class' => 'list',
                'empty' => 'Empty text',
                'size' => TbHtml::INPUT_SIZE_LARGE,
                'textAlign' => TbHtml::TEXT_ALIGN_CENTER,
            )
        );
        $select = $I->createNode($html, 'select.form-control');
        $I->seeNodeCssClass($select, 'input-large text-center list');
        $I->seeNodeAttributes(
            $select,
            array(
                'name' => 'Dummy[listbox]',
                'id' => 'Dummy_listbox',
                'size' => 4,
            )
        );

        $html = TbHtml::activeListBox(
            new Dummy,
            'listbox',
            array('1', '2', '3', '4', '5'),
            array(
                'multiple' => true,
            )
        );
        $select = $I->createNode($html, 'select.form-control');
        $I->seeNodeAttribute($select, 'name', 'Dummy[listbox][]');
    }

    public function testActiveRadioButtonList()
    {
        // todo: ensure that this test is actually correct.
        $I = $this->codeGuy;
        $html = TbHtml::activeRadioButtonList(
            new Dummy,
            'radioList',
            array('Option 1', 'Option 2', 'Option 3')
        );
        $body = $I->createNode($html);
        $I->seeNodeChildren(
            $body,
            array('input[type=hidden]', 'label.radio', 'label.radio', 'label.radio')
        );
        $label = $body->filter('label')->first();
        $I->seeNodePattern($label, '/> Option 1$/');
        $input = $label->filter('input[type=radio]');
        $I->seeNodeAttributes(
            $input,
            array(
                'id' => 'Dummy_radioList_0',
                'name' => 'Dummy[radioList]',
                'value' => '0',
            )
        );
    }

    public function testActiveInlineRadioButtonList()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeInlineRadioButtonList(
            new Dummy,
            'radioList',
            array('Option 1', 'Option 2', 'Option 3')
        );
        $container = $I->createNode($html);
        $I->seeNodeChildren($container, array('label.radio-inline', 'label.radio-inline', 'label.radio-inline'));
    }

    public function testActiveCheckBoxList()
    {
        // todo: ensure that this test is actually correct.
        $I = $this->codeGuy;
        $html = TbHtml::activeCheckBoxList(
            new Dummy,
            'checkboxList',
            array('Option 1', 'Option 2', 'Option 3')
        );
        $container = $I->createNode($html);
        $I->seeNodeChildren(
            $container,
            array('input[type=hidden]', 'label.checkbox', 'label.checkbox', 'label.checkbox')
        );
        $label = $container->filter('label')->first();
        $I->seeNodePattern($label, '/> Option 1$/');
        $input = $label->filter('input[type=checkbox]');
        $I->seeNodeAttributes(
            $input,
            array(
                'id' => 'Dummy_checkboxList_0',
                'name' => 'Dummy[checkboxList][]',
                'value' => '0',
            )
        );
    }

    public function testActiveInlineCheckBoxList()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeInlineCheckBoxList(
            new Dummy,
            'checkboxList',
            array('Option 1', 'Option 2', 'Option 3')
        );
        $container = $I->createNode($html);
        $I->seeNodeChildren(
            $container,
            array('label.checkbox-inline', 'label.checkbox-inline', 'label.checkbox-inline')
        );
    }

    public function testUncheckValueOptionForCheckboxesAndRadioInputs()
    {
        $I = $this->codeGuy;
        $items = array(0);
        $model = new Dummy();
        $outputsWithHidden = array(
            // hidden field not printed in these cases
            'checkbox' => TbHtml::checkBox('cb1', false, array('uncheckValue'=>1)),
            'checkboxList' => TbHtml::checkBoxList('cb2', 0, $items, array('uncheckValue'=>1)),
            'radio' => TbHtml::radioButton('rd1', false, array('uncheckValue'=>1)),
            'radioList' => TbHtml::radioButtonList('rd2', 0, $items, array('uncheckValue'=>1)),
            // but not printed by default in active* methods o_O
            'activeCheckbox' => TbHtml::activeCheckBox($model, 'checkboxList'),
            'activeCheckboxList' => TbHtml::activeCheckBoxList($model, 'checkboxList', $items),
            'activeRadio' => TbHtml::activeRadioButton($model, 'radioList'),
            'activeRadioList' => TbHtml::activeRadioButtonList($model, 'radioList', $items),
        );
        foreach ($outputsWithHidden as $output) {
            $I->seeNodeChildren($I->createNode($output), array('input[type=hidden]'));
        }

        // comparing against null 'uncheckValue' option
        $noHiddenOptions = array('uncheckValue'=>null);
        $outputsWithoutHidden = array(
            'checkbox' => TbHtml::checkBox('cb1'),
            'checkboxList' => TbHtml::checkBoxList('cb2', 0, $items),
            'radio' => TbHtml::radioButton('rd1'),
            'radioList' => TbHtml::radioButtonList('rd2', 0, $items),
            'activeCheckbox' => TbHtml::activeCheckBox($model, 'checkboxList', $noHiddenOptions),
            'activeCheckboxList' => TbHtml::activeCheckBoxList($model, 'checkboxList', $items, $noHiddenOptions),
            'activeRadio' => TbHtml::activeRadioButton($model, 'radioList', $noHiddenOptions),
            'activeRadioList' => TbHtml::activeRadioButtonList($model, 'radioList', $items, $noHiddenOptions),
        );
        foreach ($outputsWithoutHidden as $output) {
            $I->dontSeeNodeChildren($I->createNode($output), array('input[type=hidden]'));
        }
    }

    public function testActiveUneditableField()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeUneditableField(
            new Dummy,
            'uneditable',
            array(
                'class' => 'span'
            )
        );
        $span = $I->createNode($html, 'span.uneditable-input');
        $I->seeNodeCssClass($span, 'span');
        $I->seeNodeText($span, 'Uneditable text');
    }

    public function testActiveSearchQueryField()
    {
        $I = $this->codeGuy;
        $model = new Dummy;
        $html = TbHtml::activeSearchQueryField(
            $model,
            'search',
            array(
                'class' => 'input'
            )
        );
        $input = $I->createNode($html, 'input[type=search]');
        $I->dontSeeNodeCssClass($input, 'search-query');
        $I->seeNodeCssClass($input, 'input');
        $I->seeNodeAttributes(
            $input,
            array(
                'id' => 'Dummy_search',
                'name' => 'Dummy[search]',
                'value' => 'Search query',
            )
        );
    }

    public function testActiveTextFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeTextFieldControlGroup(new Dummy, 'text');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('input[type=text].form-control'));
    }

    public function testActivePasswordFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activePasswordFieldControlGroup(new Dummy, 'password');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('input[type=password].form-control'));
    }

    public function testActiveUrlFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeUrlFieldControlGroup(new Dummy, 'url');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('input[type=url].form-control'));
    }

    public function testActiveEmailFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeEmailFieldControlGroup(new Dummy, 'email');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('input[type=email].form-control'));
    }

    public function testActiveNumberFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeNumberFieldControlGroup(new Dummy, 'number');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('input[type=number].form-control'));
    }

    public function testActiveRangeFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeRangeFieldControlGroup(new Dummy, 'range');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('input[type=range].form-control'));
    }

    public function testActiveDateFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeDateFieldControlGroup(new Dummy, 'date');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('input[type=date].form-control'));
    }

    public function testActiveFileFieldControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeFileFieldControlGroup(new Dummy, 'file');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('input[type=file].form-control'));
    }

    public function testActiveTextAreaControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeTextAreaControlGroup(new Dummy, 'textarea');
        $group = $I->createNode($html, 'div.form-group');
        $label = $group->filter('label.control-label');
        $I->seeNodeChildren($label, array('textarea.form-control'));
    }

    public function testActiveRadioButtonControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeRadioButtonControlGroup(new Dummy, 'radio');
        $group = $I->createNode($html, 'div');
        $I->seeNodeChildren($group, array('div.radio', 'input[type=hidden]', 'label'));
        $label = $group->filter('label');
        $I->seeNodeChildren($label, array('input[type=radio]'));
    }

    public function testActiveCheckBoxControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeCheckBoxControlGroup(new Dummy, 'checkbox');
        $group = $I->createNode($html, 'div');
        $I->seeNodeChildren($group, array('input[type=hidden]', 'label'));
        $label = $group->filter('label');
        $I->seeNodeChildren($label, array('input[type=checkbox]'));
    }

    public function testActiveDropDownListControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeDropDownListControlGroup(
            new Dummy,
            'dropdown',
            array('1', '2', '3', '4', '5')
        );
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->dontSeeNodeCssClass($controls, 'controls');
        $I->seeNodeChildren($controls, array('select.form-control'));
    }

    public function testActiveListBoxControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeListBoxControlGroup(
            new Dummy,
            'listbox',
            array('1', '2', '3', '4', '5')
        );
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->dontSeeNodeCssClass($controls, 'controls');
        $I->seeNodeChildren($controls, array('select.form-control'));
    }

    public function testActiveRadioButtonListControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeRadioButtonListControlGroup(
            new Dummy,
            'radioList',
            array('Option 1', 'Option 2', 'Option 3')
        );
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->dontSeeNodeCssClass($controls, 'controls');
        $I->seeNodeChildren($controls, array('input[type=hidden]', 'label', 'label', 'label'));
    }

    public function testActiveInlineRadioButtonListControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeInlineRadioButtonListControlGroup(
            new Dummy,
            'radioList',
            array('Option 1', 'Option 2', 'Option 3')
        );
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->dontSeeNodeCssClass($controls, 'controls');
        $I->seeNodeChildren(
            $controls,
            array('input[type=hidden]', 'label.radio-inline', 'label.radio-inline', 'label.radio-inline')
        );
    }

    public function testActiveCheckBoxListControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeCheckBoxListControlGroup(
            new Dummy,
            'checkboxList',
            array('0', '2'),
            array('Option 1', 'Option 2', 'Option 3')
        );
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->dontSeeNodeCssClass($controls, 'controls');
        $I->seeNodeChildren(
            $controls,
            array('input[type=hidden]', 'label', 'label', 'label')
        );
    }

    public function testActiveInlineCheckBoxListControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeInlineCheckBoxListControlGroup(
            new Dummy,
            'checkboxList',
            array('0', '2'),
            array('Option 1', 'Option 2', 'Option 3')
        );
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->dontSeeNodeCssClass($controls, 'controls');
        $I->seeNodeChildren(
            $controls,
            array('input[type=hidden]', 'label.checkbox-inline', 'label.checkbox-inline', 'label.checkbox-inline')
        );
    }

    public function testActiveUneditableFieldControlGroup()
    {
        // @todo BS3 equivalent?
        $I = $this->codeGuy;
        $html = TbHtml::activeUneditableFieldControlGroup(new Dummy, 'uneditable');
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->dontSeeNodeCssClass($controls, 'controls');
        $I->seeNodeChildren($controls, array('span.uneditable-input'));
    }

    public function testActiveSearchQueryControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::activeSearchQueryControlGroup(new Dummy, 'search');
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->dontSeeNodeCssClass($controls, 'controls');
        $I->seeNodeChildren($controls, array('input[type=search]'));
    }

    public function testActiveControlGroup()
    {
        $I = $this->codeGuy;

        $html = TbHtml::activeControlGroup(
            TbHtml::INPUT_TYPE_TEXT,
            new Dummy,
            'text',
            array(
                'color' => TbHtml::INPUT_COLOR_ERROR,
                'groupOptions' => array('class' => 'group'),
                'labelOptions' => array('class' => 'label'),
                'help' => 'Help text',
                'helpOptions' => array('class' => 'help'),
            )
        );
        $group = $I->createNode($html, 'div.form-group');
        $I->seeNodeCssClass($group, 'has-error');
        $I->seeNodeChildren($group, array('label.control-label', 'div'));
        $label = $group->filter('label.control-label');
        $I->seeNodeCssClass($label, 'label');
        $I->seeNodeAttribute($label, 'for', 'Dummy_text');
        $I->seeNodeText($label, 'Text');
        $controls = $group->filter('div');
        $I->dontSeeNodeCssClass($controls, 'controls');
        $I->seeNodeChildren($controls, array('input', 'span'));
        $input = $controls->filter('input[type=text]');
        $I->seeNodeAttributes(
            $input,
            array(
                'id' => 'Dummy_text',
                'name' => 'Dummy[text]',
                'value' => 'text',
            )
        );
        $help = $controls->filter('span.help-block');
        $I->seeNodeCssClass($help, 'help');
        $I->seeNodeText($help, 'Help text');

        $html = TbHtml::activeControlGroup(
            TbHtml::INPUT_TYPE_RADIOBUTTON,
            new Dummy,
            'radio',
            array(
                'labelOptions' => array('class' => 'label'),
            )
        );
        $group = $I->createNode($html, 'div');
        $I->seeNodeChildren($group, array('div.radio'));
        $controls = $group->filter('div');
        $I->dontSeeNodeCssClass($controls, 'controls');
        $I->seeNodeChildren($controls, array('input[type=hidden]', 'label'));
        $hidden = $controls->filter('input[type=hidden]');
        $I->seeNodeAttributes(
            $hidden,
            array(
                'id' => 'ytDummy_radio',
                'name' => 'Dummy[radio]',
                'value' => '0',
            )
        );
        $label = $controls->filter('label');
        $I->seeNodePattern($label, '/> Radio$/');
        $radio = $label->filter('input[type=radio]');
        $I->seeNodeAttributes(
            $radio,
            array(
                'checked' => 'checked',
                'id' => 'Dummy_radio',
                'name' => 'Dummy[radio]',
                'value' => '1',
            )
        );
    }

    public function testActiveCustomControlGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::customActiveControlGroup(
            '<div class="widget"></div>',
            new Dummy,
            'text',
            array(
                'label' => false,
            )
        );
        $group = $I->createNode($html, 'div.form-group');
        $controls = $group->filter('div');
        $I->dontSeeNodeCssClass($controls, 'controls');
        $I->seeNodeChildren($controls, array('div.widget'));
    }

    public function testErrorSummary()
    {
        $I = $this->codeGuy;
        $model = new Dummy;
        $model->addError('text', 'Error text');
        $html = TbHtml::errorSummary(
            $model,
            'Header text',
            'Footer text',
            array(
                'class' => 'summary'
            )
        );
        $div = $I->createNode($html, 'div.alert');
        $I->seeNodeCssClass($div, 'alert-block alert-danger summary');
        $I->seeNodePattern($div, '/^Header text/');
        $I->seeNodePattern($div, '/Footer text$/');
        $li = $div->filter('ul > li')->first();
        $I->seeNodeText($li, 'Error text');
    }

    public function testError()
    {
        $I = $this->codeGuy;
        $model = new Dummy;
        $model->addError('text', 'Error text');
        $html = TbHtml::error(
            $model,
            'text',
            array(
                'class' => 'error',
            )
        );
        $span = $I->createNode($html, 'span.help-block');
        $I->seeNodeCssClass($span, 'error');
        $I->seeNodeText($span, 'Error text');
    }

    public function testControls()
    {
        $I = $this->codeGuy;
        $html = TbHtml::controls(
            '<div class="row"></div><div class="row"></div>',
            array(
                'before' => 'Before text',
                'after' => 'After text',
            )
        );
        $controls = $I->createNode($html, 'div');
        $I->seeNodeChildren($controls, array('div.row', 'div.row'));
        $I->seeNodePattern($controls, '/^Before text</');
        $I->seeNodePattern($controls, '/>After text$/');
    }

    public function testControlsRow()
    {
        $I = $this->codeGuy;
        $html = TbHtml::controlsRow(
            array(
                '<div class="row"></div>',
                '<div class="row"></div>',
            )
        );
        $controls = $I->createNode($html, 'div');
        $I->seeNodeCssClass($controls, 'row');
        $I->seeNodeChildren($controls, array('div.row', 'div.row'));
    }

    public function testFormActions()
    {
        $I = $this->codeGuy;

        $html = TbHtml::formActions('<div class="action"></div><div class="action"></div>');
        $this->assertEquals(
            '<div class="form-actions"><div class="action"></div><div class="action"></div></div>',
            $html
        );
        $actions = $I->createNode($html, 'div.form-actions');
        $I->seeNodeChildren($actions, array('div.action', 'div.action'));

        $html = TbHtml::formActions(
            array(
                '<div class="action"></div>',
                '<div class="action"></div>',
            )
        );
        $actions = $I->createNode($html, 'div.form-actions');
        $I->seeNodeChildren($actions, array('div.action', 'div.action'));
    }

    public function testSearchForm()
    {
        $I = $this->codeGuy;
        $html = TbHtml::searchForm(
            '#',
            'post',
            array(
                'class' => 'form',
            )
        );
        $form = $I->createNode($html, 'form.form-search');
        $I->seeNodeCssClass($form, 'form');
        $I->seeNodeAttributes(
            $form,
            array(
                'action' => '#',
                'method' => 'post'
            )
        );
        $input = $form->filter('input[type=search]');
        $I->dontSeeNodeCssClass($input, 'search-query');
    }

    public function testLink()
    {
        $I = $this->codeGuy;
        $html = TbHtml::link(
            'Link',
            '#',
            array(
                'class' => 'link'
            )
        );
        $a = $I->createNode($html, 'a.link');
        $I->seeNodeAttribute($a, 'href', '#');
        $I->seeNodeText($a, 'Link');
    }

    public function testButton()
    {
        $I = $this->codeGuy;
        $html = TbHtml::button(
            'Button',
            array(
                'class' => 'button',
                'name' => 'button',
                'color' => TbHtml::BUTTON_COLOR_PRIMARY,
                'size' => TbHtml::BUTTON_SIZE_LARGE,
                'block' => true,
                'disabled' => true,
                'loading' => 'Loading text',
                'toggle' => true,
                'icon' => TbHtml::ICON_CHECK
            )
        );
        $button = $I->createNode($html, 'button[type=button].btn');
        $I->seeNodeCssClass($button, 'btn-primary btn-lg btn-block disabled button');
        $I->seeNodeAttributes(
            $button,
            array(
                'name' => 'button',
                'data-loading-text' => 'Loading text',
                'data-toggle' => 'button',
                'disabled' => 'disabled',
            )
        );
        $I->seeNodeChildren($button, array('span.fa-check'));
        $I->seeNodePattern($button, '/> Button$/');
    }

    public function testHtmlButton()
    {
        $I = $this->codeGuy;
        $html = TbHtml::htmlButton(
            'Button',
            array(
                'class' => 'button',
                'name' => 'button',
            )
        );
        $button = $I->createNode($html, 'button[type=button].btn');
        $I->seeNodeCssClass($button, 'button');
        $I->seeNodeAttribute($button, 'name', 'button');
        $I->seeNodeText($button, 'Button');
    }

    public function testSubmitButton()
    {
        $I = $this->codeGuy;
        $html = TbHtml::submitButton(
            'Submit',
            array(
                'class' => 'button',
                'name' => 'button',
            )
        );
        $button = $I->createNode($html, 'button[type=submit].btn');
        $I->seeNodeCssClass($button, 'button');
        $I->seeNodeAttribute($button, 'name', 'button');
        $I->seeNodeText($button, 'Submit');
    }

    public function testResetButton()
    {
        $I = $this->codeGuy;
        $html = TbHtml::resetButton(
            'Reset',
            array(
                'class' => 'button',
                'name' => 'button',
            )
        );
        $button = $I->createNode($html, 'button[type=reset].btn');
        $I->seeNodeCssClass($button, 'button');
        $I->seeNodeAttribute($button, 'name', 'button');
        $I->seeNodeText($button, 'Reset');
    }

    public function testImageButton()
    {
        $I = $this->codeGuy;
        $html = TbHtml::imageButton(
            'image.png',
            array(
                'class' => 'button',
                'name' => 'button',
            )
        );
        $button = $I->createNode($html, 'input[type=image].btn');
        $I->seeNodeCssClass($button, 'button');
        $I->seeNodeAttributes(
            $button,
            array(
                'name' => 'button',
                'src' => 'image.png',
            )
        );
    }

    public function testLinkButton()
    {
        $I = $this->codeGuy;
        $html = TbHtml::linkButton(
            'Link',
            array(
                'class' => 'button',
            )
        );
        $a = $I->createNode($html, 'a.btn');
        $I->seeNodeCssClass($a, 'button');
        $I->seeNodeAttribute($a, 'href', '#');
        $I->seeNodeText($a, 'Link');
    }

    public function testAjaxLink()
    {
        $I = $this->codeGuy;
        $html = TbHtml::ajaxLink(
            'Link',
            '#',
            array(), // todo: figure out a way to test the ajax options as well.
            array(
                'id' => 'button',
                'class' => 'button',
            )
        );
        $a = $I->createNode($html, 'a');
        $I->seeNodeCssClass($a, 'button');
        $I->seeNodeAttributes(
            $a,
            array(
                'id' => 'button',
                'href' => '#',
            )
        );
        $I->seeNodeText($a, 'Link');
    }

    public function testAjaxButton()
    {
        $I = $this->codeGuy;
        $html = TbHtml::ajaxButton(
            'Button',
            '#',
            array(),
            array(
                'id' => 'button',
                'class' => 'button',
            )
        );
        $button = $I->createNode($html, 'button[type=button].btn');
        $I->seeNodeCssClass($button, 'button');
        $I->seeNodeAttribute($button, 'id', 'button');
        $I->seeNodeText($button, 'Button');
    }

    public function testAjaxSubmitButton()
    {
        $I = $this->codeGuy;
        $html = TbHtml::ajaxSubmitButton(
            'Submit',
            '#',
            array(),
            array(
                'class' => 'button',
                'id' => 'button',
                'name' => 'button'
            )
        );
        $button = $I->createNode($html, 'button[type=submit].btn');
        $I->seeNodeCssClass($button, 'button');
        $I->seeNodeAttributes(
            $button,
            array(
                'id' => 'button',
                'name' => 'button'
            )
        );
        $I->seeNodeText($button, 'Submit');
    }

    public function testImageRounded()
    {
        $I = $this->codeGuy;
        $html = TbHtml::imageRounded(
            'image.png',
            'Alternative text',
            array(
                'class' => 'image',
            )
        );
        $img = $I->createNode($html, 'img.img-rounded');
        $I->seeNodeCssClass($img, 'image');
        $I->seeNodeAttributes(
            $img,
            array(
                'src' => 'image.png',
                'alt' => 'Alternative text',
            )
        );
    }

    public function testImageCircle()
    {
        $I = $this->codeGuy;
        $html = TbHtml::imageCircle(
            'image.png',
            'Alternative text',
            array(
                'class' => 'image',
            )
        );
        $img = $I->createNode($html, 'img.img-circle');
        $I->seeNodeCssClass($img, 'image');
        $I->seeNodeAttributes(
            $img,
            array(
                'src' => 'image.png',
                'alt' => 'Alternative text',
            )
        );
    }

    public function testImagePolaroid()
    {
        $I = $this->codeGuy;
        $html = TbHtml::imagePolaroid(
            'image.png',
            'Alternative text',
            array(
                'class' => 'image',
            )
        );
        $img = $I->createNode($html, 'img.img-thumbnail');
        $I->seeNodeCssClass($img, 'image');
        $I->seeNodeAttributes(
            $img,
            array(
                'src' => 'image.png',
                'alt' => 'Alternative text',
            )
        );
    }

    public function testImageThumbnailResponsive()
    {
        $I = $this->codeGuy;
        $html = TbHtml::imagePolaroid(
            'image.png',
            'Alternative text',
            array(
                'class' => 'image',
                'responsive' => true,
            )
        );
        $img = $I->createNode($html, 'img.img-thumbnail');
        $I->seeNodeCssClass($img, 'image');
        $I->seeNodeCssClass($img, 'img-responsive');
        $I->seeNodeAttributes(
            $img,
            array(
                'src' => 'image.png',
                'alt' => 'Alternative text',
            )
        );
    }

    public function testIcon()
    {
        $I = $this->codeGuy;

        $html = TbHtml::icon(
            TbHtml::ICON_CHECK,
            array(
                'class' => 'icon',
            )
        );
        $span = $I->createNode($html, 'span.fa-check');
        $I->seeNodeEmpty($span);

        $html = TbHtml::icon(
            TbHtml::ICON_REMOVE,
            array(
                'color' => TbHtml::ICON_COLOR_WHITE,
            )
        );
        $span = $I->createNode($html, 'span.fa-remove');
        $I->seeNodeCssClass($span, 'fa-white');
        $I->seeNodeEmpty($span);

        $html = TbHtml::icon('pencil white');
        $span = $I->createNode($html, 'span.fa-pencil');
        $I->seeNodeCssClass($span, 'fa-white');
        $I->seeNodeEmpty($span);

        $html = TbHtml::icon(array());
        $this->assertEquals('', $html);
    }

    public function testDropdownToggleLink()
    {
        $I = $this->codeGuy;
        $html = TbHtml::dropdownToggleLink(
            'Link',
            array(
                'class' => 'link',
            )
        );
        $a = $I->createNode($html, 'a.btn.dropdown-toggle');
        $I->seeNodeCssClass($a, 'link');
        $I->seeNodeAttributes(
            $a,
            array(
                'href' => '#',
                'data-toggle' => 'dropdown',
            )
        );
        $I->seeNodePattern($a, '/^Link </');
        $I->seeNodeChildren($a, array('b.caret'));
    }

    public function testDropdownToggleButton()
    {
        $I = $this->codeGuy;
        $html = TbHtml::dropdownToggleButton(
            'Button',
            array(
                'class' => 'button',
                'name' => 'button',
            )
        );
        $button = $I->createNode($html, 'button[type=button].btn.dropdown-toggle');
        $I->seeNodeCssClass($button, 'button');
        $I->seeNodeAttributes(
            $button,
            array(
                'name' => 'button',
                'data-toggle' => 'dropdown',
            )
        );
        $I->seeNodePattern($button, '/^Button </');
        $I->seeNodeChildren($button, array('b.caret'));
    }

    public function testDropdownToggleMenuLink()
    {
        $I = $this->codeGuy;
        $html = TbHtml::dropdownToggleMenuLink(
            'Link',
            '#',
            array(
                'class' => 'link',
            )
        );
        $a = $I->createNode($html, 'a.dropdown-toggle');
        $I->seeNodeCssClass($a, 'link');
        $I->seeNodeAttributes(
            $a,
            array(
                'href' => '#',
                'data-toggle' => 'dropdown',
            )
        );
        $I->seeNodePattern($a, '/^Link </');
        $I->seeNodeChildren($a, array('b.caret'));
    }

    public function testButtonGroup()
    {
        $I = $this->codeGuy;

        $buttons = array(
            array('label' => 'Left'),
            array(
                'label' => 'Middle',
                'items' => array(
                    array('label' => 'Action', 'url' => '#'),
                ),
                'htmlOptions' => array('color' => TbHtml::BUTTON_COLOR_INVERSE),
            ),
            array('label' => 'Right', 'visible' => false),
        );

        $html = TbHtml::buttonGroup(
            $buttons,
            array(
                'class' => 'div',
                'color' => TbHtml::BUTTON_COLOR_PRIMARY,
                'toggle' => TbHtml::BUTTON_TOGGLE_CHECKBOX,
            )
        );
        $group = $I->createNode($html, 'div.btn-group');
        $I->seeNodeCssClass($group, 'div');
        $I->seeNodeAttribute($group, 'data-toggle', 'buttons');
        $I->seeNodeNumChildren($group, 2);
        foreach ($group->children() as $i => $btnElement) {
            $btn = $I->createNode($btnElement);
            if ($i === 1) {
                $I->seeNodeChildren($btn, array('button.dropdown-toggle', 'ul.dropdown-menu'));
                $a = $btn->filter('button.dropdown-toggle');
                $I->seeNodeCssClass($a, 'btn-inverse');
                $I->seeNodeText($a, 'Middle');
            } else {
                $I->seeNodeCssClass($btn, 'btn');
                $I->seeNodeCssClass($btn, 'btn-primary');
                $I->seeNodeText($btn, ' '.$buttons[$i]['label']);
                $input = $btn->filter('input[type="checkbox"]');
                $I->seeNodeAttribute($input, 'name', 'checkbox[]');
            }
        }

        $html = TbHtml::buttonGroup(array());
        $this->assertEquals('', $html);
    }

    public function testButtonGroupWithRadioToggle()
    {
        $I = $this->codeGuy;

        $buttons = array(
            array('label' => 'Left'),
            array(
                'label' => 'Middle',
                'items' => array(
                    array('label' => 'Action', 'url' => '#'),
                ),
                'htmlOptions' => array('color' => TbHtml::BUTTON_COLOR_INVERSE),
            ),
            array('label' => 'Right', 'visible' => false),
        );

        $html = TbHtml::buttonGroup(
            $buttons,
            array(
                'class' => 'div',
                'color' => TbHtml::BUTTON_COLOR_PRIMARY,
                'toggle' => TbHtml::BUTTON_TOGGLE_RADIO,
            )
        );
        $group = $I->createNode($html, 'div.btn-group');
        $I->seeNodeCssClass($group, 'div');
        $I->seeNodeAttribute($group, 'data-toggle', 'buttons');
        $I->seeNodeNumChildren($group, 2);
        foreach ($group->children() as $i => $btnElement) {
            $btn = $I->createNode($btnElement);
            if ($i === 1) {
                $I->seeNodeChildren($btn, array('button.dropdown-toggle', 'ul.dropdown-menu'));
                $a = $btn->filter('button.dropdown-toggle');
                $I->seeNodeCssClass($a, 'btn-inverse');
                $I->seeNodeText($a, 'Middle');
            } else {
                $I->seeNodeCssClass($btn, 'btn');
                $I->seeNodeCssClass($btn, 'btn-primary');
                $I->seeNodeText($btn, ' '.$buttons[$i]['label']);
                $input = $btn->filter('input[type="radio"]');
                $I->seeNodeAttribute($input, 'name', 'radio[]');
            }
        }

        $html = TbHtml::buttonGroup(array());
        $this->assertEquals('', $html);
    }

    public function testVerticalButtonGroup()
    {
        $I = $this->codeGuy;
        $html = TbHtml::verticalButtonGroup(
            array(
                array('icon' => TbHtml::ICON_ALIGN_LEFT),
                array('icon' => TbHtml::ICON_ALIGN_CENTER),
                array('icon' => TbHtml::ICON_ALIGN_RIGHT),
                array('icon' => TbHtml::ICON_ALIGN_JUSTIFY),
            )
        );
        $group = $I->createNode($html, 'div.btn-group');
        $I->seeNodeCssClass($group, 'btn-group-vertical');
    }

    public function testButtonToolbar()
    {
        $I = $this->codeGuy;

        $groups = array(
            array(
                'items' => array(
                    array('label' => '1', 'color' => TbHtml::BUTTON_COLOR_DANGER),
                    array('label' => '2'),
                    array('label' => '3'),
                    array('label' => '4'),
                ),
                'htmlOptions' => array(
                    'color' => TbHtml::BUTTON_COLOR_DEFAULT,
                ),
            ),
            array(
                'items' => array(
                    array('label' => '5'),
                    array('label' => '6'),
                    array('label' => '7'),
                )
            ),
            array(
                'visible' => false,
                'items' => array(
                    array('label' => '8'),
                )
            ),
            array(
                'items' => array()
            ),
        );

        $html = TbHtml::buttonToolbar(
            $groups,
            array(
                'class' => 'div',
                'color' => TbHtml::BUTTON_COLOR_PRIMARY,
            )
        );
        $toolbar = $I->createNode($html, 'div.btn-toolbar');
        $I->seeNodeCssClass($toolbar, 'div');
        foreach ($toolbar->children() as $i => $groupElement) {
            $group = $I->createNode($groupElement);
            $I->seeNodeCssClass($group, 'btn-group');
            foreach ($group->children() as $j => $btnElement) {
                $btn = $I->createNode($btnElement);
                $I->seeNodeCssClass($btn, 'btn');
                if ($i === 0) {
                    $I->seeNodeCssClass($btn, $j === 0 ? 'btn-danger' : 'btn-default');
                } else {
                    $I->seeNodeCssClass($btn, 'btn-primary');
                }
                $I->seeNodeText($btn, $groups[$i]['items'][$j]['label']);
            }
        }

        $html = TbHtml::buttonToolbar(array());
        $this->assertEquals('', $html);
    }

    public function testButtonDropdownHtmlButton()
    {
        $I = $this->codeGuy;

        $items = array(
            array(
                'label' => 'Action',
                'url' => '#',
                'class' => 'item',
                'linkOptions' => array('class' => 'link'),
            ),
            array('label' => 'Another action', 'url' => '#'),
            array('label' => 'Something else here', 'url' => '#'),
            TbHtml::menuDivider(),
            array('label' => 'Separate link', 'url' => '#'),
        );

        $html = TbHtml::buttonDropdown(
            'Action',
            $items,
            array(
                'class' => 'link',
                'dropup' => true,
                'groupOptions' => array('class' => 'group'),
                'menuOptions' => array('class' => 'menu'),
            )
        );
        $group = $I->createNode($html, 'div.btn-group');
        $I->seeNodeCssClass($group, 'dropup group');
        $I->seeNodeChildren($group, array('button.dropdown-toggle', 'ul.dropdown-menu'));
        $a = $group->filter('button.dropdown-toggle');
        $I->seeNodeCssClass($a, 'link');
        $I->seeNodeAttributes(
            $a,
            array(
                'data-toggle' => 'dropdown'
            )
        );
        $I->seeNodePattern($a, '/Action </');
        $b = $a->filter('b.caret');
        $I->seeNodeEmpty($b);
        $ul = $group->filter('ul.dropdown-menu');
        foreach ($ul->children() as $i => $liElement) {
            $li = $I->createNode($liElement);
            if ($i === 3) {
                $I->seeNodeCssClass($li, 'divider');
            } else {
                $a = $li->filter('a');
                if ($i === 0) {
                    $I->seeNodeCssClass($li, 'item');
                    $I->seeNodeCssClass($a, 'link');
                }
                $I->seeNodeAttributes(
                    $a,
                    array(
                        'href' => '#',
                        'tabindex' => '-1',
                    )
                );
                $I->seeNodeText($a, $items[$i]['label']);
            }
        }
    }

    public function testButtonDropdownLinkButton()
    {
        $I = $this->codeGuy;

        $items = array(
            array(
                'label' => 'Action',
                'url' => '#',
                'class' => 'item',
                'linkOptions' => array('class' => 'link'),
            ),
            array('label' => 'Another action', 'url' => '#'),
            array('label' => 'Something else here', 'url' => '#'),
            TbHtml::menuDivider(),
            array('label' => 'Separate link', 'url' => '#'),
        );

        $html = TbHtml::buttonDropdown(
            'Action',
            $items,
            array(
                'class' => 'link',
                'dropup' => true,
                'groupOptions' => array('class' => 'group'),
                'menuOptions' => array('class' => 'menu'),
                'type'=>TbHtml::BUTTON_TYPE_LINK
            )
        );
        $group = $I->createNode($html, 'div.btn-group');
        $I->seeNodeCssClass($group, 'dropup group');
        $I->seeNodeChildren($group, array('a.dropdown-toggle', 'ul.dropdown-menu'));
        $a = $group->filter('a.dropdown-toggle');
        $I->seeNodeCssClass($a, 'link');
        $I->seeNodeAttributes(
            $a,
            array(
                'data-toggle' => 'dropdown',
                'href' => '#',
            )
        );
        $I->seeNodePattern($a, '/Action </');
        $b = $a->filter('b.caret');
        $I->seeNodeEmpty($b);
        $ul = $group->filter('ul.dropdown-menu');
        foreach ($ul->children() as $i => $liElement) {
            $li = $I->createNode($liElement);
            if ($i === 3) {
                $I->seeNodeCssClass($li, 'divider');
            } else {
                $a = $li->filter('a');
                if ($i === 0) {
                    $I->seeNodeCssClass($li, 'item');
                    $I->seeNodeCssClass($a, 'link');
                }
                $I->seeNodeAttributes(
                    $a,
                    array(
                        'href' => '#',
                        'tabindex' => '-1',
                    )
                );
                $I->seeNodeText($a, $items[$i]['label']);
            }
        }
    }

    public function testSplitButtonDropdownHtmlButtons()
    {
        $I = $this->codeGuy;

        $items = array(
            array('label' => 'Action', 'url' => '#'),
            array('label' => 'Another action', 'url' => '#'),
            array('label' => 'Something else here', 'url' => '#'),
            TbHtml::menuDivider(),
            array('label' => 'Separate link', 'url' => '#'),
        );

        $html = TbHtml::splitButtonDropdown('Action',  $items);
        $group = $I->createNode($html, 'div.btn-group');
        $I->seeNodeChildren($group, array('button.btn', 'button.dropdown-toggle', 'ul.dropdown-menu'));
        CHtml::$count = 0;
    }

    public function testSplitButtonDropdownLinkButtons()
    {
        $I = $this->codeGuy;

        $items = array(
            array('label' => 'Action', 'url' => '#'),
            array('label' => 'Another action', 'url' => '#'),
            array('label' => 'Something else here', 'url' => '#'),
            TbHtml::menuDivider(),
            array('label' => 'Separate link', 'url' => '#'),
        );

        $html = TbHtml::splitButtonDropdown('Action',  $items, array(
                'type'=>array(TbHtml::BUTTON_TYPE_LINKBUTTON, TbHtml::BUTTON_TYPE_LINK)
            )
        );
        $group = $I->createNode($html, 'div.btn-group');
        $I->seeNodeChildren($group, array('a.btn', 'a.dropdown-toggle', 'ul.dropdown-menu'));
        $a = $group->filter('a.btn');
        $I->seeNodeAttributes(
            $a,
            array(
                'href' => '#'
            )
        );
        CHtml::$count = 0;
    }

    public function testTabs()
    {
        $I = $this->codeGuy;
        $html = TbHtml::tabs(
            array(
                array('label' => 'Link', 'url' => '#'),
            )
        );
        $nav = $I->createNode($html, 'ul.nav');
        $I->seeNodeCssClass($nav, 'nav-tabs');
    }

    public function testStackedTabs()
    {
        $I = $this->codeGuy;
        $html = TbHtml::stackedTabs(
            array(
                array('label' => 'Link', 'url' => '#'),
            )
        );
        $nav = $I->createNode($html, 'ul.nav');
        $I->seeNodeCssClass($nav, 'nav-tabs nav-stacked');
    }

    public function testPills()
    {
        $I = $this->codeGuy;
        $html = TbHtml::pills(
            array(
                array('label' => 'Link', 'url' => '#'),
            )
        );
        $nav = $I->createNode($html, 'ul.nav');
        $I->seeNodeCssClass($nav, 'nav-pills');
    }

    public function testStackedPills()
    {
        $I = $this->codeGuy;

        $html = TbHtml::stackedPills(
            array(
                array('label' => 'Link', 'url' => '#'),
            )
        );
        $nav = $I->createNode($html, 'ul.nav');
        $I->seeNodeCssClass($nav, 'nav-pills nav-stacked');
    }

    public function testNavList()
    {
        $I = $this->codeGuy;

        $items = array(
            array('label' => 'Header text'),
            array('label' => 'Link', 'url' => '#'),
            TbHtml::menuDivider(),
        );

        $html = TbHtml::navList(
            $items,
            array(
                'stacked' => true,
            )
        );
        $nav = $I->createNode($html, 'ul.nav');
        $I->seeNodeCssClass($nav, 'nav-list');
        $I->dontSeeNodeCssClass($nav, 'nav-stacked');
        foreach ($nav->children() as $i => $liElement) {
            $li = $I->createNode($liElement);
            if ($i === 0) {
                $I->seeNodeCssClass($li, 'dropdown-header');
                $I->seeNodeText($li, 'Header text');
            } else if ($i === 1) {
                $a = $li->filter('a');
                $I->seeNodeText($a, $items[$i]['label']);
            } else if ($i === 2) {
                $I->seeNodeCssClass($li, 'divider');
            }
        }
    }

    public function testNav()
    {
        $I = $this->codeGuy;
        $html = TbHtml::nav(
            TbHtml::NAV_TYPE_NONE,
            array(
                array('label' => 'Link', 'url' => '#'),
            ),
            array(
                'stacked' => true,
            )
        );
        $nav = $I->createNode($html, 'ul.nav');
        $I->seeNodeCssClass($nav, 'nav-stacked');
    }

    public function testMenu()
    {
        $I = $this->codeGuy;

        $items = array(
            array('icon' => TbHtml::ICON_HOME, 'label' => 'Home', 'url' => '#'),
            array('label' => 'Profile', 'url' => '#', 'htmlOptions' => array('disabled' => true)),
            array('label' => 'Dropdown', 'active' => true, 'items' => array(
                array('label' => 'Action', 'url' => '#'),
                array('label' => 'Another action', 'url' => '#'),
                array('label' => 'Dropdown', 'items' => array(
                    array('label' => 'Action', 'url' => '#'),
                )),
                TbHtml::menuDivider(),
                array('label' => 'Separate link', 'url' => '#'),
            )),
            array('label' => 'Hidden', 'url' => '#', 'visible' => false),
        );

        $html = TbHtml::menu(
            $items,
            array(
                'class' => 'ul',
            )
        );
        $nav = $I->createNode($html, 'ul');
        $I->seeNodeAttribute($nav, 'role', 'menu');
        $I->seeNodeNumChildren($nav, 3);
        foreach ($nav->children() as $i => $liElement) {
            $li = $I->createNode($liElement);
            if ($i === 2) {
                $I->seeNodeCssClass($li, 'dropdown active');
                $I->seeNodeChildren($li, array('a.dropdown-toggle', 'ul.dropdown-menu'));
                $ul = $li->filter('ul.dropdown-menu');
                $I->seeNodeNumChildren($ul, 5);
                foreach ($ul->children() as $j => $subLiElement) {
                    $subLi = $I->createNode($subLiElement);
                    if ($j === 2) {
                        $I->seeNodeCssClass($subLi, 'dropdown-submenu');
                        $I->seeNodeChildren($subLi, array('a.dropdown-toggle', 'ul.dropdown-menu'));
                        $subUl = $subLi->filter('ul.dropdown-menu');
                        $I->seeNodeNumChildren($subUl, 1);
                    } else {
                        if ($j === 3) {
                            $I->seeNodeCssClass($subLi, 'divider');
                        } else {
                            $subA = $subLi->filter('a');
                            $I->seeNodeText($subA, $items[$i]['items'][$j]['label']);
                        }
                    }
                }
            } else {
                if ($i === 0) {
                    $I->seeNodeChildren($li, array('span.fa-home', 'a'));
                }
                if ($i === 2) {
                    $I->seeNodeCssClass($li, 'disabled');
                }
                $a = $li->filter('a');
                $I->seeNodeAttributes(
                    $a,
                    array(
                        'href' => '#',
                        'tabindex' => '-1',
                    )
                );
                $I->seeNodeText($a, $items[$i]['label']);
            }
        }

        $html = TbHtml::menu(array());
        $this->assertEquals('', $html);
    }

    public function testMenuLink()
    {
        $I = $this->codeGuy;
        $html = TbHtml::menuLink(
            'Link',
            '#',
            array(
                'class' => 'item',
                'linkOptions' => array('class' => 'link'),
            )
        );
        $li = $I->createNode($html, 'li');
        $I->seeNodeCssClass($li, 'item');
        $a = $li->filter('a');
        $I->seeNodeCssClass($a, 'link');
        $I->seeNodeAttribute($a, 'href', '#');
        $I->seeNodeText($a, 'Link');
    }

    public function testMenuHeader()
    {
        $I = $this->codeGuy;
        $html = TbHtml::menuHeader(
            'Header text',
            array(
                'class' => 'item',
            )
        );
        $li = $I->createNode($html, 'li.dropdown-header');
        $I->seeNodeCssClass($li, 'item');
        $I->seeNodeText($li, 'Header text');
    }

    public function testMenuDivider()
    {
        $I = $this->codeGuy;
        $html = TbHtml::menuDivider(
            array(
                'class' => 'item',
            )
        );
        $li = $I->createNode($html, 'li.divider');
        $I->seeNodeCssClass($li, 'item');
        $I->seeNodeEmpty($li);
    }

    public function testTabbableTabs()
    {
        $I = $this->codeGuy;
        $html = TbHtml::tabbableTabs(
            array(
                array('label' => 'Link', 'content' => 'Tab content'),
            )
        );
        $tabbable = $I->createNode($html, 'div.tabbable');
        $ul = $tabbable->filter('ul.nav');
        $I->seeNodeCssClass($ul, 'nav-tabs');
    }

    public function testTabbablePills()
    {
        $I = $this->codeGuy;
        $html = TbHtml::tabbablePills(
            array(
                array('label' => 'Link', 'content' => 'Tab content'),
            )
        );
        $tabbable = $I->createNode($html, 'div.tabbable');
        $ul = $tabbable->filter('ul.nav');
        $I->seeNodeCssClass($ul, 'nav-pills');
    }

    public function testTabbable()
    {
        $I = $this->codeGuy;

        $tabs = array(
            array('label' => 'Home', 'content' => 'Tab content', 'active' => true),
            array('label' => 'Profile', 'content' => 'Tab content', 'id' => 'profile'),
            array(
                'label' => 'Messages',
                'items' => array(
                    array('label' => '@fat', 'content' => 'Tab content'),
                    array('label' => '@mdo', 'content' => 'Tab content'),
                )
            ),
        );

        $html = TbHtml::tabbable(
            TbHtml::NAV_TYPE_NONE,
            $tabs,
            array(
                'class' => 'div',
            )
        );
        $tabbable = $I->createNode($html, 'div.tabbable');
        $I->seeNodeCssClass($tabbable, 'div');
        $ul = $tabbable->filter('ul.nav');
        $I->seeNodeNumChildren($ul, 3);
        foreach ($ul->children() as $i => $liElement) {
            $li = $I->createNode($liElement);
            if ($i === 0) {
                $I->seeNodeCssClass($li, 'active');
            }
            if ($i === 2) {
                $I->seeNodeCssClass($li, 'dropdown');
                $a = $li->filter('a.dropdown-toggle');
                $I->seeNodeText($a, 'Messages');
                $subUl = $li->filter('ul.dropdown-menu');
                foreach ($subUl->children() as $j => $subLiElement) {
                    $subLi = $I->createNode($subLiElement);
                    $subA = $subLi->filter('a');
                    $I->seeNodeAttributes(
                        $subA,
                        array(
                            'data-toggle' => 'tab',
                            'tabindex' => '-1',
                            'href' => '#tab_' . ($i + $j + 1),
                        )
                    );
                    $I->seeNodeText($subA, $tabs[$i]['items'][$j]['label']);
                }
            } else {
                $a = $li->filter('a');
                $I->seeNodeAttributes(
                    $a,
                    array(
                        'data-toggle' => 'tab',
                        'tabindex' => '-1',
                        'href' => '#' . (isset($tabs[$i]['id']) ? $tabs[$i]['id'] : 'tab_' . ($i + 1)),
                    )
                );
                $I->seeNodeText($a, $tabs[$i]['label']);
            }
        }
        $content = $tabbable->filter('div.tab-content');
        $I->seeNodeNumChildren($content, 4);
        foreach ($content->children() as $i => $paneElement) {
            $pane = $I->createNode($paneElement);
            $I->seeNodeCssClass($pane, 'tab-pane fade');
            if ($i === 0) {
                $I->seeNodeCssClass($pane, 'active in');
            }
            if ($i > 1) {
                $I->seeNodeText($pane, $tabs[2]['items'][$i - 2]['content']);
            } else {
                $I->seeNodeText($pane, $tabs[$i]['content']);
            }
        }
    }

    public function testNavbar()
    {
        $I = $this->codeGuy;

        $html = TbHtml::navbar(
            'Navbar content',
            array(
                'class' => 'nav',
            )
        );
        $navbar = $I->createNode($html, 'nav.navbar');
        $I->seeNodeCssClass($navbar, 'nav');
        $I->seeNodeText($navbar, 'Navbar content');

        $html = TbHtml::navbar(
            '',
            array(
                'display' => TbHtml::NAVBAR_DISPLAY_STATICTOP,
            )
        );
        $navbar = $I->createNode($html, 'nav.navbar');
        $I->seeNodeCssClass($navbar, 'navbar-static-top');

        $html = TbHtml::navbar(
            '',
            array(
                'display' => TbHtml::NAVBAR_DISPLAY_FIXEDTOP,
            )
        );
        $navbar = $I->createNode($html, 'nav.navbar');
        $I->seeNodeCssClass($navbar, 'navbar-fixed-top');

        $html = TbHtml::navbar(
            '',
            array(
                'display' => TbHtml::NAVBAR_DISPLAY_FIXEDBOTTOM,
            )
        );
        $navbar = $I->createNode($html, 'nav.navbar');
        $I->seeNodeCssClass($navbar, 'navbar-fixed-bottom');

        $html = TbHtml::navbar(
            '',
            array(
                'color' => TbHtml::NAVBAR_COLOR_INVERSE,
            )
        );
        $navbar = $I->createNode($html, 'nav.navbar');
        $I->seeNodeCssClass($navbar, 'navbar-inverse');
    }

    public function testNavbarBrandLink()
    {
        $I = $this->codeGuy;
        $html = TbHtml::navbarBrandLink(
            'Brand text',
            '#',
            array(
                'class' => 'link',
            )
        );
        $a = $I->createNode($html, 'a.navbar-brand');
        $I->seeNodeCssClass($a, 'link');
        $I->seeNodeAttribute($a, 'href', '#');
        $I->seeNodeText($a, 'Brand text');
    }

    public function testNavbarText()
    {
        $I = $this->codeGuy;
        $html = TbHtml::navbarText(
            'Navbar text',
            array(
                'class' => 'text',
            )
        );
        $p = $I->createNode($html, 'p.navbar-text');
        $I->seeNodeCssClass($p, 'text');
        $I->seeNodeText($p, 'Navbar text');
    }

    public function testNavbarMenuDivider()
    {
        $I = $this->codeGuy;
        $html = TbHtml::navbarMenuDivider(
            array(
                'class' => 'item',
            )
        );
        $li = $I->createNode($html, 'li.divider-vertical');
        $I->seeNodeCssClass($li, 'item');
        $I->seeNodeEmpty($li);
    }

    public function testNavbarForm()
    {
        $I = $this->codeGuy;
        $html = TbHtml::navbarForm('#');
        $I->createNode($html, 'form.navbar-form');
    }

    public function testNavbarSearchForm()
    {
        $I = $this->codeGuy;
        $html = TbHtml::navbarSearchForm('#');
        $I->createNode($html, 'form.navbar-search');
    }

    public function testNavbarCollapseLink()
    {
        $I = $this->codeGuy;
        $html = TbHtml::navbarCollapseLink(
            '#',
            array(
                'class' => 'link',
            )
        );
        $a = $I->createNode($html, 'button.btn.btn-navbar');
        $I->seeNodeCssClass($a, 'link');
        $I->seeNodeAttributes(
            $a,
            array(
                'data-toggle' => 'collapse',
                'data-target' => '#',
            )
        );
        $I->seeNodeChildren($a, array('span.icon-bar', 'span.icon-bar', 'span.icon-bar'));
    }

    public function testBreadcrumbs()
    {
        $I = $this->codeGuy;

        $links = array(
            'Home' => '#',
            'Library' => '#',
            'Data',
        );

        $html = TbHtml::breadcrumbs(
            $links,
            array(
                'class' => 'ol',
            )
        );
        $ol = $I->createNode($html, 'ol.breadcrumb');
        $I->seeNodeCssClass($ol, 'ol');
        $I->seeNodeNumChildren($ol, 3);
        foreach ($ol->children() as $i => $liElement) {
            $li = $I->createNode($liElement);
            switch ($i) {
                case 0:
                    $a = $li->filter('a');
                    $I->seeNodeAttribute($a, 'href', '#');
                    $I->seeNodeText($a, 'Home');
                    break;
                case 1:
                    $a = $li->filter('a');
                    $I->seeNodeAttribute($a, 'href', '#');
                    $I->seeNodeText($a, 'Library');
                    break;
                case 2:
                    $I->seeNodeText($li, 'Data');
                    break;
            }
        }
    }

    public function testPagination()
    {
        $I = $this->codeGuy;

        $items = array(
            array('label' => 'Prev', 'url' => '#', 'disabled' => true),
            array(
                'label' => '1',
                'url' => '#',
                'active' => true,
                'htmlOptions' => array('class' => 'item'),
                'linkOptions' => array('class' => 'link'),
            ),
            array('label' => '2', 'url' => '#'),
            array('label' => '3', 'url' => '#'),
            array('label' => '4', 'url' => '#'),
            array('label' => '5', 'url' => '#'),
            array('label' => 'Next', 'url' => '#'),
        );

        $html = TbHtml::pagination(
            $items,
            array(
                'class' => 'ul',
            )
        );
        $ul = $I->createNode($html, 'ul.pagination');
        $I->seeNodeCssClass($ul, 'ul');
        $I->seeNodeNumChildren($ul, 7);
        foreach ($ul->children() as $i => $liElement) {
            $li = $I->createNode($liElement);
            $a = $li->filter('a');
            if ($i === 0) {
                $I->seeNodeCssClass($li, 'disabled');
            }
            if ($i === 1) {
                $I->seeNodeCssClass($li, 'item active');
                $I->seeNodeCssClass($a, 'link');
            }
            $I->seeNodeAttribute($a, 'href', '#');
            $I->seeNodeText($a, $items[$i]['label']);
        }

        $html = TbHtml::pagination(
            $items,
            array(
                'size' => TbHtml::PAGINATION_SIZE_LARGE,
            )
        );
        $ul = $I->createNode($html, 'ul.pagination');
        $I->seeNodeCssClass($ul, 'pagination-lg');

        $html = TbHtml::pagination(array());
        $this->assertEquals('', $html);
    }

    public function testPaginationLink()
    {
        $I = $this->codeGuy;
        $html = TbHtml::paginationLink(
            'Link',
            '#',
            array(
                'class' => 'item',
                'linkOptions' => array('class' => 'link'),
            )
        );
        $li = $I->createNode($html, 'li');
        $I->seeNodeCssClass($li, 'item');
        $a = $li->filter('a');
        $I->seeNodeCssClass($a, 'link');
        $I->seeNodeAttribute($a, 'href', '#');
    }

    public function testPager()
    {
        $I = $this->codeGuy;

        $items = array(
            array(
                'label' => 'Prev',
                'url' => '#',
                'previous' => true,
                'htmlOptions' => array('disabled' => true),
            ),
            array('label' => 'Next', 'url' => '#', 'next' => true),
        );

        $html = TbHtml::pager(
            $items,
            array(
                'class' => 'list',
            )
        );
        $ul = $I->createNode($html, 'ul.pager');
        $I->seeNodeCssClass($ul, 'list');
        $I->seeNodeNumChildren($ul, 2);
        $prev = $ul->filter('li')->first();
        $I->seeNodeCssClass($prev, 'previous disabled');
        $a = $prev->filter('a');
        $I->seeNodeAttribute($a, 'href', '#');
        $I->seeNodeText($a, 'Prev');
        $next = $ul->filter('li')->last();
        $I->seeNodeCssClass($next, 'next');
        $a = $next->filter('a');
        $I->seeNodeAttribute($a, 'href', '#');
        $I->seeNodeText($a, 'Next');

        $html = TbHtml::pager(array());
        $this->assertEquals('', $html);
    }

    public function testPagerLink()
    {
        $I = $this->codeGuy;

        $html = TbHtml::pagerLink(
            'Link',
            '#',
            array(
                'class' => 'item',
                'linkOptions' => array('class' => 'link'),
                'disabled' => true,
            )
        );
        $li = $I->createNode($html, 'li');
        $I->seeNodeCssClass($li, 'item disabled');
        $a = $li->filter('a');
        $I->seeNodeCssClass($a, 'link');
        $I->seeNodeAttribute($a, 'href', '#');
        $I->seeNodeText($a, 'Link');

        $html = TbHtml::pagerLink(
            'Previous',
            '#',
            array(
                'previous' => true,
            )
        );
        $li = $I->createNode($html, 'li.previous');
        $a = $li->filter('a');
        $I->seeNodeAttribute($a, 'href', '#');
        $I->seeNodeText($a, 'Previous');

        $html = TbHtml::pagerLink(
            'Next',
            '#',
            array(
                'next' => true,
            )
        );
        $li = $I->createNode($html, 'li.next');
        $a = $li->filter('a');
        $I->seeNodeAttribute($a, 'href', '#');
        $I->seeNodeText($a, 'Next');
    }

    public function testLabel()
    {
        $I = $this->codeGuy;
        $html = TbHtml::labelTb(
            'Label text',
            array(
                'color' => TbHtml::LABEL_COLOR_INFO,
                'class' => 'span',
            )
        );
        $span = $I->createNode($html, 'span.label');
        $I->seeNodeCssClass($span, 'label-info span');
        $I->seeNodeText($span, 'Label text');
    }

    public function testBadge()
    {
        $I = $this->codeGuy;
        $html = TbHtml::badge(
            'Badge text',
            array(
                'class' => 'span',
            )
        );
        $span = $I->createNode($html, 'span.badge');
        $I->seeNodeCssClass($span, 'span');
        $I->seeNodeText($span, 'Badge text');
    }

    public function testJumbotron()
    {
        $I = $this->codeGuy;
        $html = TbHtml::heroUnit(
            'Heading text',
            'Content text',
            array(
                'class' => 'div',
                'headingOptions' => array('class' => 'heading'),
            )
        );
        $hero = $I->createNode($html, 'div.jumbotron');
        $I->seeNodeCssClass($hero, 'div');
        $I->seeNodeText($hero, 'Content text');
        $h1 = $hero->filter('h1');
        $I->seeNodeCssClass($h1, 'heading');
        $I->seeNodeText($h1, 'Heading text');
    }

    public function testPageHeader()
    {
        $I = $this->codeGuy;
        $html = TbHtml::pageHeader(
            'Heading text',
            'Subtext',
            array(
                'class' => 'header',
                'headerOptions' => array('class' => 'heading'),
                'subtextOptions' => array('class' => 'subtext')
            )
        );
        $header = $I->createNode($html, 'div.page-header');
        $I->seeNodeCssClass($header, 'header');
        $h1 = $header->filter('h1');
        $I->seeNodeCssClass($h1, 'heading');
        $I->seeNodeText($h1, 'Heading text');
        $small = $h1->filter('small');
        $I->seeNodeCssClass($small, 'subtext');
        $I->seeNodeText($small, 'Subtext');
    }

    public function testThumbnails()
    {
        $I = $this->codeGuy;

        $items = array(
            array(
                'image' => 'image.png',
                'label' => 'Thumbnail label',
                'caption' => 'Caption text',
                'span' => 6,
                'imageOptions' => array('class' => 'image', 'alt' => 'Alternative text'),
                'captionOptions' => array('class' => 'div'),
                'labelOptions' => array('class' => 'heading'),
            ),
            array('image' => 'image.png', 'label' => 'Thumbnail label', 'caption' => 'Caption text'),
            array('image' => 'image.png', 'label' => 'Thumbnail label', 'caption' => 'Caption text'),
        );

        $html = TbHtml::thumbnails(
            $items,
            array(
                'span' => 3,
                'class' => 'list',
            )
        );
        $thumbnails = $I->createNode($html, 'ul.thumbnails');
        $I->seeNodeCssClass($thumbnails, 'list');
        $I->seeNodeNumChildren($thumbnails, 3);
        $I->seeNodeChildren($thumbnails, array('li.col-md-6', 'li.col-md-3', 'li.col-md-3'));
        foreach ($thumbnails->children() as $i => $liElement) {
            $li = $I->createNode($liElement);
            $thumbnail = $li->filter('div.thumbnail');
            $I->seeNodeChildren($thumbnail, array('img', 'div.caption'));
            $img = $thumbnail->filter('img');
            $I->seeNodeAttribute($img, 'src', 'image.png');
            $caption = $thumbnail->filter('div.caption');
            $h3 = $caption->filter('h3');
            $I->seeNodeText($caption, $items[$i]['caption']);
            $I->seeNodeText($h3, $items[$i]['label']);
            if ($i === 0) {
                $I->seeNodeCssClass($img, 'image');
                $I->seeNodeAttribute($img, 'alt', 'Alternative text');
                $I->seeNodeCssClass($caption, 'div');
                $I->seeNodeCssClass($h3, 'heading');
            }
        }

        $html = TbHtml::thumbnails(array());
        $this->assertEquals('', $html);
    }

    public function testThumbnail()
    {
        $I = $this->codeGuy;
        $html = TbHtml::thumbnail(
            'Thumbnail text',
            array(
                'class' => 'div',
                'itemOptions' => array('class' => 'item'),
            )
        );
        $li = $I->createNode($html, 'li');
        $I->seeNodeCssClass($li, 'item');
        $thumbnail = $li->filter('div.thumbnail');
        $I->seeNodeCssClass($thumbnail, 'div');
        $I->seeNodeText($thumbnail, 'Thumbnail text');
    }

    public function testThumbnailLink()
    {
        $I = $this->codeGuy;
        $html = TbHtml::thumbnailLink(
            'Thumbnail text',
            '#',
            array(
                'class' => 'link',
                'itemOptions' => array('class' => 'item'),
            )
        );
        $li = $I->createNode($html, 'li');
        $I->seeNodeCssClass($li, 'item');
        $thumbnail = $li->filter('a.thumbnail');
        $I->seeNodeCssClass($thumbnail, 'link');
        $I->seeNodeAttribute($thumbnail, 'href', '#');
        $I->seeNodeText($thumbnail, 'Thumbnail text');
    }

    public function testAlert()
    {
        $I = $this->codeGuy;

        $html = TbHtml::alert(
            TbHtml::ALERT_COLOR_SUCCESS,
            'Alert message',
            array(
                'class' => 'div',
                'closeText' => 'Close',
                'closeOptions' => array('class' => 'text'),
            )
        );
        $alert = $I->createNode($html, 'div.alert');
        $I->seeNodeCssClass($alert, 'alert-success in fade div');
        $I->seeNodeText($alert, 'Alert message');
        $close = $alert->filter('a[type=button].close');
        $I->seeNodeCssClass($close, 'text');
        $I->seeNodeAttributes(
            $close,
            array(
                'href' => '#',
                'data-dismiss' => 'alert',
            )
        );
        $I->seeNodeText($close, 'Close');

        $html = TbHtml::alert(
            TbHtml::ALERT_COLOR_INFO,
            'Alert message',
            array(
                'closeText' => false,
                'in' => false,
                'fade' => false,
            )
        );
        $alert = $I->createNode($html, 'div.alert');
        $I->seeNodeCssClass($alert, 'alert-info');
        $I->dontSeeNodeCssClass($alert, 'fade in');
        $I->dontSeeNodeChildren($alert, array('.close'));
        $I->seeNodeText($alert, 'Alert message');
    }

    public function testBlockAlert()
    {
        $I = $this->codeGuy;
        $html = TbHtml::blockAlert(TbHtml::ALERT_COLOR_WARNING, 'Alert message');
        $alert = $I->createNode($html, 'div.alert');
        $I->seeNodeCssClass($alert, 'alert-warning alert-block fade in');
        $I->seeNodeText($alert, 'Alert message');
        $I->seeNodeChildren($alert, array('div.alert > a[type=button].close'));
    }

    public function testProgressBar()
    {
        $I = $this->codeGuy;

        $html = TbHtml::progressBar(
            60,
            array(
                'class' => 'div',
                'color' => TbHtml::PROGRESS_COLOR_INFO,
                'content' => 'Bar text',
                'barOptions' => array('class' => 'another-div'),
            )
        );
        $progress = $I->createNode($html, 'div.progress');
        $bar = $progress->filter('div.progress-bar');
        $I->seeNodeCssClass($bar, 'progress-bar-info another-div');
        $I->seeNodeCssStyle($bar, 'width: 60%');
        $I->seeNodeText($bar, 'Bar text');

        $html = TbHtml::progressBar(
            35,
            array(
                'barOptions' => array('color' => TbHtml::PROGRESS_COLOR_SUCCESS),
            )
        );
        $progress = $I->createNode($html, 'div.progress');
        $bar = $progress->filter('div.progress-bar');
        $I->seeNodeCssClass($bar, 'progress-bar-success');
        $I->seeNodeCssStyle($bar, 'width: 35%');

        $html = TbHtml::progressBar(-1);
        $progress = $I->createNode($html, 'div.progress');
        $bar = $progress->filter('div.progress-bar');
        $I->seeNodeCssStyle($bar, 'width: 0');

        $html = TbHtml::progressBar(100.1);
        $progress = $I->createNode($html, 'div.progress');
        $bar = $progress->filter('div.progress-bar');
        $I->seeNodeCssStyle($bar, 'width: 100%');
    }

    public function testStripedProgressBar()
    {
        $I = $this->codeGuy;
        $html = TbHtml::stripedProgressBar(20);
        $progress = $I->createNode($html, 'div.progress');
        $I->seeNodeCssClass($progress, 'progress-striped');
        $bar = $progress->filter('div.progress-bar');
        $I->seeNodeCssStyle($bar, 'width: 20%');
    }

    public function testAnimatedProgressBar()
    {
        $I = $this->codeGuy;
        $html = TbHtml::animatedProgressBar(40);
        $progress = $I->createNode($html, 'div.progress');
        $I->seeNodeCssClass($progress, 'progress-striped active');
        $bar = $progress->filter('div.progress-bar');
        $I->seeNodeCssStyle($bar, 'width: 40%');
    }

    public function testStackedProgressBar()
    {
        $I = $this->codeGuy;

        $html = TbHtml::stackedProgressBar(
            array(
                array('color' => TbHtml::PROGRESS_COLOR_SUCCESS, 'width' => 35),
                array('color' => TbHtml::PROGRESS_COLOR_WARNING, 'width' => 20),
                array('color' => TbHtml::PROGRESS_COLOR_DANGER, 'width' => 10),
            )
        );
        $progress = $I->createNode($html, 'div.progress');
        $I->seeNodeChildren(
            $progress,
            array(
                'div.progress-bar-success',
                'div.progress-bar-warning',
                'div.progress-bar-danger',
            )
        );
        $success = $progress->filter('div.progress-bar-success');
        $I->seeNodeCssClass($success, 'progress-bar');
        $I->seeNodeCssStyle($success, 'width: 35%');
        $warning = $progress->filter('div.progress-bar-warning');
        $I->seeNodeCssClass($warning, 'progress-bar');
        $I->seeNodeCssStyle($warning, 'width: 20%');
        $danger = $progress->filter('div.progress-bar-danger');
        $I->seeNodeCssClass($danger, 'progress-bar');
        $I->seeNodeCssStyle($danger, 'width: 10%');

        $html = TbHtml::stackedProgressBar(
            array(
                array('width' => 35),
                array('width' => 20),
                array('width' => 100),
            )
        );
        $progress = $I->createNode($html, 'div.progress');
        $last = $progress->filter('div.progress-bar')->last();
        $I->seeNodeCssStyle($last, 'width: 45%');

        $html = TbHtml::stackedProgressBar(
            array(
                array('width' => 35),
                array('width' => 20),
                array('width' => 10, 'visible' => false),
            )
        );
        $progress = $I->createNode($html, 'div.progress');
        $last = $progress->filter('div.progress-bar')->last();
        $I->seeNodeCssStyle($last, 'width: 20%');

        $html = TbHtml::stackedProgressBar(array());
        $this->assertEquals('', $html);
    }

    public function testMediaList()
    {
        $I = $this->codeGuy;

        $items = array(
            array('image' => 'image.png', 'heading' => 'Media heading', 'content' => 'Content text'),
            array('heading' => 'Media heading', 'content' => 'Content text'),
        );

        $html = TbHtml::mediaList(
            $items,
            array(
                'class' => 'list',
            )
        );
        $ul = $I->createNode($html, 'ul.media-list');
        $I->seeNodeNumChildren($ul, 2);
        $I->seeNodeChildren($ul, array('li.media', 'li.media'));

        $html = TbHtml::mediaList(array());
        $this->assertEquals('', $html);
    }

    public function testMedias()
    {
        $I = $this->codeGuy;

        $items = array(
            array(
                'image' => 'image.png',
                'heading' => 'Media heading',
                'content' => 'Content text',
                'items' => array(
                    array(
                        'image' => '#',
                        'heading' => 'Media heading',
                        'content' => 'Content text',
                    ),
                    array(
                        'image' => '#',
                        'heading' => 'Media heading',
                        'content' => 'Content text',
                        'visible' => false,
                    ),
                )
            ),
            array('heading' => 'Media heading', 'content' => 'Content text'),
        );

        $html = TbHtml::medias($items);
        $body = $I->createNode($html, 'body');
        $medias = $body->filter('div.media');
        $first = $medias->first();
        $I->seeNodeChildren($first, array('a.pull-left', 'div.media-body'));
        $img = $first->filter('img.media-object');
        $I->seeNodeAttribute($img, 'src', 'image.png');
        $mediaBody = $first->filter('div.media-body');
        $I->seeNodeChildren($mediaBody, array('h4.media-heading', 'div.media'));
        $I->seeNodeText($mediaBody, 'Content text');
        $h4 = $body->filter('h4.media-heading');
        $I->seeNodeText($h4, 'Media heading');
        $I->seeNodeNumChildren($mediaBody, 1, 'div.media');
        $last = $medias->last();
        $I->seeNodeChildren($last, array('div.media-body'));

        $html = TbHtml::medias(array());
        $this->assertEquals('', $html);
    }

    public function testMedia()
    {
        $I = $this->codeGuy;

        $html = TbHtml::media(
            'image.png',
            'Heading text',
            'Content text',
            array(
                'class' => 'div',
                'linkOptions' => array('class' => 'link'),
                'imageOptions' => array('class' => 'image', 'alt' => 'Alternative text'),
                'contentOptions' => array('class' => 'content'),
                'headingOptions' => array('class' => 'heading'),
            )
        );
        $div = $I->createNode($html, 'div.media');
        $I->seeNodeCssClass($div, 'div');
        $I->seeNodeChildren($div, array('a.pull-left', 'div.media-body'));
        $a = $div->filter('a.pull-left');
        $I->seeNodeCssClass($a, 'link');
        $I->seeNodeAttribute($a, 'href', '#');
        $img = $a->filter('img.media-object');
        $I->seeNodeCssClass($img, 'image');
        $I->seeNodeAttributes(
            $img,
            array(
                'src' => 'image.png',
                'alt' => 'Alternative text',
            )
        );
        $content = $div->filter('div.media-body');
        $I->seeNodeCssClass($content, 'content');
        $I->seeNodeText($content, 'Content text');
        $h4 = $content->filter('h4.media-heading');
        $I->seeNodeCssClass($h4, 'heading');
        $I->seeNodeText($h4, 'Heading text');
    }

    public function testWell()
    {
        $I = $this->codeGuy;
        $html = TbHtml::well(
            'Well text',
            array(
                'class' => 'div',
                'size' => TbHtml::WELL_SIZE_LARGE,
            )
        );
        $well = $I->createNode($html, 'div.well');
        $I->seeNodeCssClass($well, 'well-large');
        $I->seeNodeText($well, 'Well text');
    }

    public function testCloseLink()
    {
        $I = $this->codeGuy;
        $html = TbHtml::closeLink(
            'Close',
            '#',
            array(
                'class' => 'link',
                'dismiss' => TbHtml::CLOSE_DISMISS_ALERT,
            )
        );
        $a = $I->createNode($html, 'a[type=button].close');
        $I->seeNodeCssClass($a, 'link');
        $I->seeNodeAttributes(
            $a,
            array(
                'href' => '#',
                'data-dismiss' => 'alert',
            )
        );
        $I->seeNodeText($a, 'Close');
    }

    public function testCloseButton()
    {
        $I = $this->codeGuy;
        $html = TbHtml::closeButton(
            'Close',
            array(
                'dismiss' => TbHtml::CLOSE_DISMISS_MODAL,
                'class' => 'button',
            )
        );
        $button = $I->createNode($html, 'button[type=button].close');
        $I->seeNodeCssClass($button, 'button');
        $I->seeNodeAttribute($button, 'data-dismiss', 'modal');
        $I->seeNodeText($button, 'Close');
    }


    public function testCollapseLink()
    {
        $I = $this->codeGuy;
        $html = TbHtml::collapseLink(
            'Link',
            '#',
            array(
                'class' => 'link',
            )
        );
        $a = $I->createNode($html, 'a[data-toggle=collapse]');
        $I->seeNodeCssClass($a, 'link');
        $I->seeNodeAttribute($a, 'href', '#');
        $I->seeNodeText($a, 'Link');
    }

    public function testTooltip()
    {
        $I = $this->codeGuy;

        $html = TbHtml::tooltip(
            'Link',
            '#',
            'Tooltip text',
            array(
                'class' => 'link',
                'animation' => true,
                'html' => true,
                'selector' => '.selector',
                'placement' => TbHtml::TOOLTIP_PLACEMENT_RIGHT,
                'trigger' => TbHtml::TOOLTIP_TRIGGER_CLICK,
                'delay' => 350,
            )
        );
        $a = $I->createNode($html, 'a[rel=tooltip]');
        $I->seeNodeCssClass($a, 'link');
        $I->seeNodeAttributes(
            $a,
            array(
                'title' => 'Tooltip text',
                'data-animation' => 'true',
                'data-html' => 'true',
                'data-selector' => '.selector',
                'data-placement' => 'right',
                'data-trigger' => 'click',
                'data-delay' => '350',
                'href' => '#'
            )
        );
        $I->seeNodeText($a, 'Link');
    }

    public function testPopover()
    {
        $I = $this->codeGuy;

        $html = TbHtml::popover(
            'Link',
            'Heading text',
            'Content text',
            array(
                'class' => 'link',
            )
        );
        $a = $I->createNode($html, 'a[rel=popover]');
        $I->seeNodeCssClass($a, 'link');
        $I->seeNodeAttributes(
            $a,
            array(
                'title' => 'Heading text',
                'data-content' => 'Content text',
                'data-toggle' => 'popover',
                'href' => '#'
            )
        );
        $I->seeNodeText($a, 'Link');
    }

    public function testCarousel()
    {
        $I = $this->codeGuy;

        $items = array(
            array(
                'image' => 'image.png',
                'label' => 'First Thumbnail label',
                'url' => '#',
                'caption' => 'Caption text',
            ),
            array('image' => 'image.png', 'label' => 'Second Thumbnail label'),
            array('image' => 'image.png', 'imageOptions' => array('class' => 'image', 'alt' => 'Alternative text')),
        );

        $html = TbHtml::carousel(
            $items,
            array(
                'id' => 'carousel',
                'class' => 'div',
            )
        );
        $carousel = $I->createNode($html, 'div.carousel');
        $I->seeNodeCssClass($carousel, 'div slide');
        $I->seeNodeChildren($carousel, array('ol.carousel-indicators', 'div.carousel-inner', 'a.carousel-control', 'a.carousel-control'));
        $inner = $carousel->filter('div.carousel-inner');
        foreach ($inner->children() as $i => $divElement) {
            $div = $I->createNode($divElement);
            $I->seeNodeCssClass($div, 'item');
            switch ($i) {
                case 0:
                    $I->seeNodeCssClass($div, 'active');
                    $I->seeNodeChildren($div, array('a', 'div.carousel-caption'));
                    $a = $div->filter('a');
                    $I->seeNodeAttribute($a, 'href', '#');
                    break;
                case 1:
                    $I->seeNodeChildren($div, array('img', 'div.carousel-caption'));
                    break;
                case 2:
                    $img = $div->filter('img.image');
                    $I->seeNodeAttributes(
                        $img,
                        array(
                            'src' => 'image.png',
                            'alt' => 'Alternative text',
                        )
                    );
                    break;
            }
        }
    }

    public function testCarouselItem()
    {
        $I = $this->codeGuy;
        $html = TbHtml::carouselItem(
            'Content text',
            'Label text',
            'Caption text',
            array(
                'class' => 'div',
                'overlayOptions' => array('class' => 'overlay'),
                'labelOptions' => array('class' => 'label'),
                'captionOptions' => array('class' => 'caption'),
            )
        );
        $div = $I->createNode($html, 'div.item');
        $I->seeNodeCssClass($div, 'div');
        $I->seeNodeText($div, 'Content text');
        $overlay = $div->filter('div.carousel-caption');
        $I->seeNodeCssClass($overlay, 'overlay');
        $I->seeNodeChildren($overlay, array('h4', 'p'));
        $h4 = $overlay->filter('h4');
        $I->seeNodeCssClass($h4, 'label');
        $caption = $overlay->filter('p');
        $I->seeNodeCssClass($caption, 'caption');
        $I->seeNodeText($caption, 'Caption text');
    }

    public function testCarouselPrevLink()
    {
        $I = $this->codeGuy;
        $html = TbHtml::carouselPrevLink(
            'Previous',
            '#',
            array(
                'class' => 'link',
            )
        );
        $a = $I->createNode($html, 'a.carousel-control.left');
        $I->seeNodeCssClass($a, 'link');
        $I->seeNodeAttributes(
            $a,
            array(
                'href' => '#',
                'data-slide' => 'prev',
            )
        );
        $I->seeNodeText($a, 'Previous');
    }

    public function testCarouselNextLink()
    {
        $I = $this->codeGuy;
        $html = TbHtml::carouselNextLink(
            'Next',
            '#',
            array(
                'class' => 'link',
            )
        );
        $a = $I->createNode($html, 'a.carousel-control.right');
        $I->seeNodeCssClass($a, 'link');
        $I->seeNodeAttributes(
            $a,
            array(
                'href' => '#',
                'data-slide' => 'next',
            )
        );
        $I->seeNodeText($a, 'Next');
    }

    public function testCarouselIndicators()
    {
        $I = $this->codeGuy;
        $html = TbHtml::carouselIndicators(
            '#',
            3,
            array(
                'class' => 'list',
            )
        );
        $ol = $I->createNode($html, 'ol.carousel-indicators');
        $I->seeNodeCssClass($ol, 'list');
        $I->seeNodeChildren($ol, array('li.active', 'li', 'li'));
        foreach ($ol->filter('li') as $i => $element) {
            $node = $I->createNode($element);
            $I->seeNodeAttributes(
                $node,
                array(
                    'data-target' => '#',
                    'data-slide-to' => $i,
                )
            );
            $I->seeNodeEmpty($node);
        }
    }

    public function testAddCssClass()
    {
        $htmlOptions = array('class' => 'my');
        TbHtml::addCssClass(array('class'), $htmlOptions);
        $this->assertEquals('my class', $htmlOptions['class']);
        TbHtml::addCssClass('more classes', $htmlOptions);
        $this->assertEquals('my class more classes', $htmlOptions['class']);
        TbHtml::addCssClass(array('my'), $htmlOptions);
        $this->assertEquals('my class more classes', $htmlOptions['class']);
        TbHtml::addCssClass('class more classes', $htmlOptions);
        $this->assertEquals('my class more classes', $htmlOptions['class']);
    }

    public function testAddCssStyle()
    {
        $htmlOptions = array('style' => 'display: none');
        TbHtml::addCssStyle('color: purple', $htmlOptions);
        TbHtml::addCssStyle('background: #fff;', $htmlOptions);
        TbHtml::addCssStyle(array('font-family: "Open sans"', 'font-weight: bold;'), $htmlOptions);
        $this->assertEquals(
            'display: none; color: purple; background: #fff; font-family: "Open sans"; font-weight: bold',
            $htmlOptions['style']
        );
    }
}