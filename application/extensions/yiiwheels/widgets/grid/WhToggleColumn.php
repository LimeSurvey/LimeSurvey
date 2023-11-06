<?php
/**
 * WhToggleColumn widget class
 * Renders a button to toggle values of a column
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.toggle
 * @uses YiiStrap.helpers.TbHtml
 * @uses YiiStrap.widgets.TbDataColumn
 */
Yii::import('bootstrap.helpers.TbHtml');
Yii::import('bootstrap.widgets.TbDataColumn');

class WhToggleColumn extends TbDataColumn
{
    /**
     * @var string the attribute name of the data model. Used for column sorting, filtering and to render the corresponding
     * attribute value in each data cell. If {@link value} is specified it will be used to rendered the data cell instead of the attribute value.
     * @see value
     * @see sortable
     */
    public $name;

    /**
     * @var array the HTML options for the data cell tags.
     */
    public $htmlOptions = array('class' => 'toggle-column');

    /**
     * @var array the HTML options for the header cell tag.
     */
    public $headerHtmlOptions = array('class' => 'toggle-column');

    /**
     * @var array the HTML options for the footer cell tag.
     */
    public $footerHtmlOptions = array('class' => 'toggle-column');

    /**
     * @var string the label for the toggle button. Defaults to "Check".
     * Note that the label will not be HTML-encoded when rendering.
     */
    public $checkedButtonLabel;

    /**
     * @var string the label for the toggle button. Defaults to "Uncheck".
     * Note that the label will not be HTML-encoded when rendering.
     */
    public $uncheckedButtonLabel;

    /**
     * @var string the label for the NULL value toggle button. Defaults to "Not Set".
     * Note that the label will not be HTML-encoded when rendering.
     */
    public $emptyButtonLabel;

    /**
     * @var string the glyph icon toggle button "checked" state.
     * You may set this property to be false to render a text link instead.
     */
    public $checkedIcon = 'fa-ok-circle';

    /**
     * @var string the glyph icon toggle button "unchecked" state.
     * You may set this property to be false to render a text link instead.
     */
    public $uncheckedIcon = 'fa-remove-sign';

    /**
     * @var string the glyph icon toggle button "empty" state (example for null value)
     */
    public $emptyIcon = 'fa-question-sign';

    /**
     * @var boolean display button with text or only icon with label tooltip
     */
    public $displayText = false;

    /**
     * @var boolean whether the column is sortable. If so, the header cell will contain a link that may trigger the sorting.
     * Defaults to true. Note that if {@link name} is not set, or if {@link name} is not allowed by {@link CSort},
     * this property will be treated as false.
     * @see name
     */
    public $sortable = true;

    /**
     * @var mixed the HTML code representing a filter input (eg a text field, a dropdown list)
     * that is used for this data column. This property is effective only when
     * {@link CGridView::filter} is set.
     * If this property is not set, a text field will be generated as the filter input;
     * If this property is an array, a dropdown list will be generated that uses this property value as
     * the list options.
     * If you don't want a filter for this data column, set this value to false.
     * @since 1.1.1
     */
    public $filter;

    /**
     * @var string Name of the action to call and toggle values
     * @see bootstrap.action.TbToggleAction for an easy way to use with your controller
     */
    public $toggleAction = 'toggle';

    /**
     * @var string a javascript function that will be invoked after the toggle ajax call.
     *
     * The function signature is <code>function(data)</code>
     * <ul>
     * <li><code>success</code> status of the ajax call, true if the ajax call was successful, false if the ajax call failed.
     * <li><code>data</code> the data returned by the server in case of a successful call or XHR object in case of error.
     * </ul>
     * Note that if success is true it does not mean that the delete was successful, it only means that the ajax call was successful.
     *
     * Example:
     * <pre>
     *  array(
     *     class'=>'TbToggleColumn',
     *     'afterToggle'=>'function(success,data){ if (success) alert("Toggled successfuly"); }',
     *  ),
     * </pre>
     */
    public $afterToggle;

    /**
     * @var string suffix substituted to a name class of the tag <a>
     */
    public $uniqueClassSuffix = '';

    /**
     * @var array the configuration for toggle button.
     */
    protected $toggleOptions = array();

    /**
     * Initializes the column.
     * This method registers necessary client script for the button column.
     */
    public function init()
    {
        if ($this->name === null) {
            throw new CException(Yii::t(
                'zii',
                '"{attribute}" attribute cannot be empty.',
                array('{attribute}' => "name")
            ));
        }

        $this->initButton();
        $this->registerClientScript();
    }

    /**
     * Initializes the default buttons (toggle).
     */
    protected function initButton()
    {
        if ($this->checkedButtonLabel === null) {
            $this->checkedButtonLabel = Yii::t('zii', 'Uncheck');
        }
        if ($this->uncheckedButtonLabel === null) {
            $this->uncheckedButtonLabel = Yii::t('zii', 'Check');
        }
        if ($this->emptyButtonLabel === null) {
            $this->emptyButtonLabel = Yii::t('zii', 'Not set');
        }

        $this->toggleOptions = array(
            'url'         => 'Yii::app()->controller->createUrl("' . $this->toggleAction . '",array("id"=>$data->primaryKey,"attribute"=>"' . $this->name . '"))',
            'htmlOptions' => array('class' => $this->name . '_toggle' . $this->uniqueClassSuffix),
        );

        if (Yii::app()->request->enableCsrfValidation) {
            $csrfTokenName = Yii::app()->request->csrfTokenName;
            $csrfToken     = Yii::app()->request->csrfToken;
            $csrf          = "\n\t\tdata:{ '$csrfTokenName':'$csrfToken' },";
        } else {
            $csrf = '';
        }

        if ($this->afterToggle === null) {
            $this->afterToggle = 'function(){}';
        }

        $this->toggleOptions['click'] = "js:
function() {
	var th=this;
	var afterToggle={$this->afterToggle};
	$.fn.yiiGridView.update('{$this->grid->id}', {
		type:'POST',
		url:$(this).attr('href'),{$csrf}
		success:function(data) {
			$.fn.yiiGridView.update('{$this->grid->id}');
			afterToggle(true, data);
		},
		error:function(XHR){
			afterToggle(false,XHR);
		}
	});
	return false;
}";
    }

    /**
     * Renders the data cell content.
     * This method renders the view, update and toggle buttons in the data cell.
     * @param integer $row the row number (zero-based)
     * @param mixed $data the data associated with the row
     */
    protected function renderDataCellContent($row, $data)
    {
        $checked               = CHtml::value($data, $this->name);
        $toggleOptions         = $this->toggleOptions;
        $toggleOptions['icon'] = $checked === null
            ? $this->emptyIcon
            : ($checked
                ? $this->checkedIcon
                : $this->uncheckedIcon);

        $toggleOptions['url'] = isset($toggleOptions['url'])
            ? $this->evaluateExpression($toggleOptions['url'], array('data' => $data, 'row' => $row))
            : '#';

        if (!$this->displayText) {
            $htmlOptions          = TbArray::getValue('htmlOptions', $this->toggleOptions, array());
            $htmlOptions['title'] = $this->getButtonLabel($checked);
            $htmlOptions['rel']   = 'tooltip';
            echo CHtml::link(TbHtml::icon($toggleOptions['icon']), $toggleOptions['url'], $htmlOptions);
        } else {
            echo TbHtml::button($this->getButtonLabel($checked), $toggleOptions);
        }
    }

    /**
     * Registers the client scripts for the button column.
     */
    protected function registerClientScript()
    {
        $js = array();

        $function = CJavaScript::encode(TbArray::popValue('click', $this->toggleOptions, ''));

        $class = preg_replace('/\s+/', '.', $this->toggleOptions['htmlOptions']['class']);
        $js[]  = "$(document).on('click','#{$this->grid->id} a.{$class}',$function);";

        Yii::app()->getClientScript()->registerScript( $this->name. '#ReadyJS', implode("\n", $js));
    }

    /**
     * Returns the button label
     * @param $value
     * @return string
     */
    private function getButtonLabel($value)
    {
        return $value === null ? $this->emptyButtonLabel : ($value ? $this->checkedButtonLabel : $this->uncheckedButtonLabel);
    }
}
