<?php
/**
 * TbEditableColumn.php
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiBooster bootstrap.widgets
 */
Yii::import('bootstrap.widgets.TbDataColumn');

class TbJEditableColumn extends TbDataColumn
{
	/**
	 * @var string $saveURL the route to make AJAX calls to
	 */
	public $saveURL;

	/**
	 * @var array $jEditableOptions the jEditable jquery plugin options
	 */
	public $jEditableOptions = array();

	/**
	 * @var string $cssClass the class that
	 */
	public $cssClass = 'tbjeditable-column';

	/**
	 * @var $event the event jEditable plugin should be displayed (ie dbclick, click...)
	 */
	protected $event;

	/**
	 * @var array the jEditable jquery plugin default options
	 * @see http://www.appelsiini.net/projects/jeditable
	 */
	protected $defaultJEditableOptions = array(
		'method' => 'POST', // method to use to send edited content (POST or PUT)
		'callback' => null, // Function to run after submitting edited content
		'name' => 'value', // POST parameter name of edited content,
		'id' => null, // POST parameter name of edited div id (if null will be filled with htmlOptions['id'] or $this->id)
		'submitdata' => null, // Extra parameters to send when submitting edited content
		'type' => 'text', // text, textarea or select (or any 3rd party input type)
		'rows' => null, // number of rows if using textarea
		'cols' => null, // number of cols if using textarea
		'height' => 'auto', // 'auto', 'none' or height in pixels,
		'width' => 'auto', // 'auto', 'none' or width in pixels
		'loadurl' => null, // URL to fetch input content before editing
		'loadtype' => 'GET', // Request type for load url. Should be GET or POST.
		'loadtext' => null, //  Text to display while loading external content.
		'loaddata' => null, // Extra parameters to pass when fetching content before editing.
		'data' => null, // Or content given as paramameter. String or function.
		'indicator' => null, // indicator html to show when saving (will default to assets/img/loading.gif if null)
		'tooltip' => null, // optional tooltip text via title attribute
		'event' => 'click', // jQuery event such as 'click' of 'dblclick'
		'submit' => null, // submit button value, empty means no button
		'cancel' => null, // cancel button value, empty means no button
		'cssclass' => null, // CSS class to apply to input form. 'inherit' to copy from parent.
		'style' => null, //  Style to apply to input form 'inherit' to copy from parent.
		'select' => false, // true or false, when true text is highlighted
		'placeholder' => null, // Placeholder text or html to insert when element is empty.
		'onblur' => null, // 'cancel', 'submit', 'ignore' or function
		'onsubmit' => null, // function(settings, original) { ... } called before submit
		'onreset' => null, // function(settings, original) { ... } called before reset
		'onerror' => null, // function(settings, original, xhr) { ... } called on error
		'ajaxoptions' => null, // jQuery Ajax options. See docs.jquery.com.
		'cancelAttrs' => array('class' => 'btn'), /* custom property */
		'submitAttrs' => array('class' => 'btn'), /* custom property */
		//'mask' => '99/99/9999', /* configuration setting for masked plugin */
		//'dateformat' => 'yyyy/mm/dd', /* you can use this configuration when using the date plugin */
		//'colorformat' => 'rgb' /*  rgb | hex | rgba you can use this parameter when using color picker plugin @see www.eyecon.ro/bootstrap-colorpicker/ */

	);

	/**
	 * Initializes the column.
	 */
	public function init()
	{

		parent::init();

		$this->jEditableOptions = CMap::mergeArray($this->defaultJEditableOptions, $this->jEditableOptions);

		if (!isset($this->jEditableOptions['type']))
		{
			$this->jEditableOptions['type'] = 'text';
		}
		if ($this->jEditableOptions['type'] == 'select' && (!isset($this->jEditableOptions['loadurl']) && !isset($this->jEditableOptions['data'])))
		{
			throw new CException('zii', 'When jeditable type is "select", "loadurl" or "data" must be configured properly. The data loaded must be in "json" format.');
		}
		if (!isset($this->jEditableOptions['id']))
		{
			$this->jEditableOptions['id'] = @$this->htmlOptions['id'] ? $this->htmlOptions['id'] : $this->id;
		}

		$this->event = (isset($this->jEditableOptions['event'])) ? $this->jEditableOptions['event'] : 'click';

		$this->jEditableOptions['event'] = null;

		if (!$this->saveURL)
		{
			$this->saveURL = Yii::app()->getRequest()->requestUri;
		}
		$this->cssClass .= '-' . $this->id;

		$this->registerClientScript();


	}

	/**
	 * Renders a data cell.
	 * @param integer $row the row number (zero-based)
	 */
	public function renderDataCell($row)
	{
		$data = $this->grid->dataProvider->data[$row];
		$options = $this->htmlOptions;
		if ($this->cssClassExpression !== null)
		{
			$class = $this->evaluateExpression($this->cssClassExpression, array('row' => $row, 'data' => $data));
			if (isset($options['class']))
				$options['class'] .= ' ' . $class;
			else
				$options['class'] = $class;
		}
		echo CHtml::openTag('td', $options);
		echo CHtml::openTag('span', array('class' => $this->cssClass, 'data-rowid' => $this->getPrimaryKey($data)));
		$this->renderDataCellContent($row, $data);
		echo '</span>';
		echo '</td>';
	}

	/**
	 * Helper function to return the primary key of the $data
	 * IMPORTANT: composite keys on CActiveDataProviders will return the keys joined by comma
	 *
	 * @param $data
	 * @return null|string
	 */
	protected function getPrimaryKey($data)
	{
		if($this->grid->dataProvider instanceof CActiveDataProvider)
		{
			$key=$this->grid->dataProvider->keyAttribute===null ? $data->getPrimaryKey() : $data->{$this->keyAttribute};
			return is_array($key) ? implode(',',$key) : $key;
		}
		if($this->grid->dataProvider instanceof CArrayDataProvider)
		{
			return is_object($data) ? $data->{$this->grid->dataProvider->keyField} : $data[$this->grid->dataProvider->keyField];
		}
		return null;
	}

	/**
	 * Registers client javascript
	 * @throws CException
	 */
	public function registerClientScript()
	{
		$cs = Yii::app()->getClientScript();
		$assetsUrl = Yii::app()->bootstrap->getAssetsUrl();
		$cs->registerScriptFile($assetsUrl . '/js/jquery.jeditable.js', CClientScript::POS_END);

		$cs->registerCss('TbJEditableColumnTimepickerCSS#' . $this->id, "
				.{$this->cssClass} select { width: 50px; height:25px; margin: 1px; }
				.{$this->cssClass} button { margin: 1px; font-size: 10px; }
			");
		if (!isset($this->jEditableOptions['indicator']))
		{
			$this->jEditableOptions['indicator'] = CHtml::image($assetsUrl . '/img/loading.gif');
		}
		switch ($this->jEditableOptions['type'])
		{
			case 'time':
				$cs->registerScriptFile($assetsUrl . '/js/jquery.jeditable.time.js', CClientScript::POS_END);
				if (!isset($this->jEditableOptions['submit']))
				{
					$this->jEditableOptions['submit'] = Yii::t('zii', 'Ok');
					$this->jEditableOptions['cancel'] = Yii::t('zii', 'Cancel');
				}
				break;
			case 'masked':

				if (!isset($this->jEditableOptions['mask']))
					throw new CException('zii', '"mask" setting is required to use the masked plugin');

				$cs->registerScriptFile($assetsUrl . '/js/jquery.maskedInput.js', CClientScript::POS_END)
					->registerScriptFile($assetsUrl . '/js/jquery.jeditable.masked.js', CClientScript::POS_END);

				break;
			case 'bdatepicker':

				$cs->registerCssFile($assetsUrl . '/css/bootstrap-datepicker.css')
					->registerScriptFile($assetsUrl . '/js/bootstrap.datepicker.js', CClientScript::POS_END)
					->registerScriptFile($assetsUrl . '/js/jquery.jeditable.bdatepicker.js', CClientScript::POS_END);
				if (!isset($this->jEditableOptions['submit']))
				{
					$this->jEditableOptions['submit'] = Yii::t('zii', 'Ok');
				}
				break;
			case 'bcolorpicker':

				$cs->registerCssFile($assetsUrl . '/css/bootstrap-colorpicker.css')
					->registerScriptFile($assetsUrl . '/js/bootstrap.colorpicker.js', CClientScript::POS_END)
					->registerScriptFile($assetsUrl . '/js/jquery.jeditable.bcolorpicker.js', CClientScript::POS_END);
				break;
		}

		$options = CJavaScript::encode(array_filter($this->jEditableOptions));
		$cs->registerScript('TbJEditableColumn#' . $this->id, "
			jQuery(document).on('{$this->event}','.{$this->cssClass}', function(){
				var id = jQuery(this).attr('data-rowid');
				var options = jQuery.extend(true, {$options}, {'submitdata':{id:id,editable:'{$this->grid->id}'}});
				jQuery(this).editable('{$this->saveURL}', options);
			});
		");
	}
}