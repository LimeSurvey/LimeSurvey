<?php
/**
 * TbRelationalColumn class
 *
 * Displays a clickable column that will make an ajax request and display its resulting data
 * into a new row.
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * Date: 9/25/12
 * Time: 10:05 PM
 */
class TbRelationalColumn extends TbDataColumn
{
	/**
	 * @var string $url the route to call via AJAX to get the data from
	 */
	public $url;

	/**
	 * @var string $cssClass the class name that will wrap up the cell content.
	 * Important Note: this class will be used as the trigger for the AJAX call, so make sure is unique for the
	 * column.
	 */
	public $cssClass = 'tbrelational-column';

	/**
	 * @var bool $cacheData if set to true, there won't be more than one AJAX request. If set to false, the widget will
	 * continuously make AJAX requests. This is useful if the data could vary. If the data doesn't change then is better
	 * to set it to true. Defaults to true.
	 */
	public $cacheData = true;

	/**
	 * @var string a javascript function that will be invoked if an AJAX call occurs.
	 *
	 * The function signature is <code>function(tr, rowid, data)</code>
	 * <ul>
	 * <li><code>tr</code> is the newly created TR HTML object that will display the returned server data.</li>
	 * <li><code>rowid</code> the model id of the row.</li>
	 * <li><code>data</code> is the data returned by the server that is already displayed on the row.</li>
	 * </ul>
	 * Note: This handler is not called for JSONP requests.
	 *
	 * Example (add in a call to TbRelationalColumn):
	 * <pre>
	 *  ...
	 *  'afterAjaxUpdate'=>'js:function(tr,rowid, data){ console.log(rowid); }',
	 *  ...
	 * </pre>
	 */
	public $afterAjaxUpdate;

	/**
	 * @var string $ajaxErrorMessage the message that is displayed on the newly created row in case there is an AJAX
	 * error.
	 */
	public $ajaxErrorMessage = 'Error';

	/**
	 * widget initialization
	 */
	public function init()
	{
		parent::init();

		if(empty($this->url))
		{
			$this->url = Yii::app()->getRequest()->requestUri;
		}

		$this->registerClientScript();
	}

	/**
	 * Overrides CDataColumn renderDataCell in order to wrap up its content with the object that will be used as a
	 * trigger.
	 * Important: Making use of links as a content for this of column is an error.
	 * @param int $row
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
	 *  * IMPORTANT: composite keys on CActiveDataProviders will return the keys joined by comma
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
	 * Register script that will handle its behavior
	 */
	public function registerClientScript()
	{
		Yii::app()->bootstrap->registerAssetCss('bootstrap-relational.css');

		$cs = Yii::app()->getClientScript();

		if($this->afterAjaxUpdate!==null)
		{
			if((!$this->afterAjaxUpdate instanceof CJavaScriptExpression) && strpos($this->afterAjaxUpdate,'js:')!==0)
			{
				$this->afterAjaxUpdate=new CJavaScriptExpression($this->afterAjaxUpdate);
			}
			else
			{
				$this->afterAjaxUpdate=$this->afterAjaxUpdate;
			}
		}
		else
			$this->afterAjaxUpdate = 'js:$.noop';

		$this->ajaxErrorMessage = CHtml::encode($this->ajaxErrorMessage);
		$afterAjaxUpdate = CJavaScript::encode($this->afterAjaxUpdate);
		$span = count($this->grid->columns);
		$loadingPic = CHtml::image(Yii::app()->bootstrap->getAssetsUrl().'/img/loading.gif');
		$cache = $this->cacheData? 'true':'false';
		$data = !empty($this->submitData) && is_array($this->submitData)? $this->submitData : 'js:{}';
		$data = CJavascript::encode($data);

		$js =<<<EOD
$(document).on('click','.{$this->cssClass}', function(){
	var span = $span;
	var that = $(this);
	var status = that.data('status');
	var rowid = that.data('rowid');
	var tr = $('#relatedinfo'+rowid);
	var parent = that.parents('tr');
	var afterAjaxUpdate = {$afterAjaxUpdate};

	if(status && status=='on'){return}
	that.data('status','on');

	if(tr.length && !tr.is(':visible') && {$cache})
	{
		tr.slideDown();
		that.data('status','off');
		return;
	}else if(tr.length && tr.is(':visible'))
	{
		tr.slideUp();
		that.data('status','off');
		return;
	}
	if(tr.length)
	{
		tr.find('td').html('{$loadingPic}');
		if(!tr.is(':visible')){
			tr.slideDown();
		}
	}
	else
	{
		var td = $('<td/>').html('{$loadingPic}').attr({'colspan':$span});
		tr = $('<tr/>').prop({'id':'relatedinfo'+rowid}).append(td);
		/* we need to maintain zebra styles :) */
		var fake = $('<tr class="hide"/>').append($('<td/>').attr({'colspan':$span}));
		parent.after(tr);
		tr.after(fake);
	}
	var data = $.extend({$data}, {id:rowid});
	$.ajax({
		url: '{$this->url}',
		data: data,
		success: function(data){
		    tr.find('td').html(data);
		    that.data('status','off');
		    if($.isFunction(afterAjaxUpdate))
		    {
		        afterAjaxUpdate(tr,rowid,data);
		    }
		},
		error: function()
		{
			tr.find('td').html('{$this->ajaxErrorMessage}');
			that.data('status','off');
		}
	});
});
EOD;
		$cs->registerScript(__CLASS__.'#'.$this->id, $js);
	}
}