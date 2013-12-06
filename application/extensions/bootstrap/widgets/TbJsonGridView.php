<?php
/**
 * TbJsonGridView class file
 *
 * Converts TbGridView into a Json Javascript grid when using AJAX updates calls. This grid makes use of localStorage or
 * a custom in memory plugin to avoid repetitive ajax requests/responses and speed up data visualization.
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiBooster bootstrap.widgets
 */
Yii::import('bootstrap.widgets.TbGridView');
Yii::import('bootstrap.widgets.TbJsonDataColumn');

class TbJsonGridView extends TbGridView
{
	/**
	 * @var boolean $json true when there is an AJAX request. TbJsonGridView expect a JSON response.
	 */
	public $json;

	/**
	 * @var string $template display pager on top by default, override to place two pagers as we expect lots of records
	 */
	public $template = "{pager}\n{items}\n{summary}\n{pager}";

	/**
	 * @var int $cacheTTL how long we keep the responses on cache? It will depend on cacheTTLType (seconds, minutes, hours)
	 */
	public $cacheTTL = 1;

	/**
	 * @var string the type of cache duration
	 *  s: seconds
	 *  m: minutes
	 *  h: hours
	 */
	public $cacheTTLType = 's';

	/**
	 * @var bool $localCache whether we use client ajax cache or not. True by default.
	 */
	public $localCache = true;

	/**
	 * @var array the configuration for the pager.
	 * Defaults to <code>array('class'=>'ext.bootstrap.widgets.TbPager')</code>.
	 */
	public $pager = array('class' => 'bootstrap.widgets.TbJsonPager');

	/**
	 * Initializes $json property to find out whether ajax r
	 */
	public function init()
	{
		// parse request to find out whether is an ajax request or not, if so, then return $dataProvider JSON formatted
		$this->json = Yii::app()->getRequest()->getIsAjaxRequest();
		if ($this->json)
		{
			$this->template = '{items}'; // going to render only items!
		}
		parent::init();
	}

	/**
	 * Renders the view.
	 * This is the main entry of the whole view rendering.
	 * Child classes should mainly override {@link renderContent} method.
	 */
	public function run()
	{
		if (!$this->json)
			parent::run();
		else
		{
			$this->registerClientScript();
			$this->renderContent();
		}
	}

	/**
	 * Renders the pager.
	 */
	public function renderPager()
	{
		if (!$this->json)
		{
			parent::renderPager();
			return;
		}

		$pager = array();
		if (is_string($this->pager))
			$class = $this->pager;
		else if (is_array($this->pager))
		{
			$pager = $this->pager;
			if (isset($pager['class']))
			{
				$class = $pager['class'];
				unset($pager['class']);
			}
		}
		$pager['pages'] = $this->dataProvider->getPagination();

		if ($pager['pages']->getPageCount() > 1)
		{
			$pager['json'] = $this->json;
			$widget = $this->createWidget($class, $pager);

			return $widget->run();
		} else
			return array();
	}

	/**
	 * Creates column objects and initializes them.
	 */
	protected function initColumns()
	{
		foreach ($this->columns as $i => $column)
		{
			if (is_array($column) && !isset($column['class']))
				$this->columns[$i]['class'] = 'bootstrap.widgets.TbJsonDataColumn';
		}

		parent::initColumns();
	}

	/**
	 * Renders the data items for the grid view.
	 */
	public function renderItems()
	{
		if ($this->json)
		{
			echo function_exists('json_encode') ? json_encode($this->renderTableBody()) : CJSON::encode($this->renderTableBody());

		} elseif ($this->dataProvider->getItemCount() > 0 || $this->showTableOnEmpty)
		{
			echo "<table class=\"{$this->itemsCssClass}\">\n";
			$this->renderTableHeader();
			ob_start();
			$this->renderTableBody();
			$body = ob_get_clean();
			$this->renderTableFooter();
			echo $body; // TFOOT must appear before TBODY according to the standard.
			echo "</table>";
			$this->renderTemplates();

		} else
			$this->renderEmptyText();
	}

	/**
	 * Renders the required templates for the client engine (jqote2 used)
	 */
	protected function renderTemplates()
	{
		echo $this->renderTemplate($this->id . '-col-template', '<td <%=this.attrs%>><%=this.content%></td>');
		echo $this->renderTemplate($this->id . '-row-template', '<tr class="<%=this.class%>"><% var t = "#' . $this->id . '-col-template"; out += $.jqote(t, this.cols);%></tr>');
		echo $this->renderTemplate($this->id . '-keys-template', '<span><%=this%></span>');
		if ($this->enablePagination)
		{
			echo $this->renderTemplate($this->id . '-pager-template', '<li class="<%=this.class%>"><a href="<%=this.url%>"><%=this.text%></a></li>');
		}
	}

	/**
	 * Encloses the given JavaScript within a script tag.
	 * @param string $text the JavaScript to be enclosed
	 * @return string the enclosed JavaScript
	 */
	public function renderTemplate($id, $text)
	{
		return "<script type=\"text/x-jqote-template\" id=\"{$id}\">\n<![CDATA[\n{$text}\n]]>\n</script>";
	}

	/**
	 * Renders the table body.
	 */
	public function renderTableBody()
	{
		$data = $this->dataProvider->getData();
		$n = count($data);

		if ($this->json)
		{
			return $this->renderTableBodyJSON($n);
		}
		echo "<tbody>\n";

		if ($n > 0)
		{
			for ($row = 0; $row < $n; ++$row)
				$this->renderTableRow($row);
		} else
		{
			echo '<tr><td colspan="' . count($this->columns) . '" class="empty">';
			$this->renderEmptyText();
			echo "</td></tr>\n";
		}
		echo "</tbody>\n";

	}

	/**
	 * Renders the body table for JSON requests - assumed ajax is for JSON
	 * @param $rows
	 * @return array
	 */
	protected function renderTableBodyJSON($rows)
	{
		$tbody = array(
			'headers' => array(),
			'rows' => array(),
			'keys' => array()
		);
		foreach ($this->columns as $column)
		{
			if (property_exists($column, 'json'))
			{
				$column->json = $this->json;
				$tbody['headers'][] = $column->renderHeaderCell();
			}
		}

		if ($rows > 0)
		{
			for ($row = 0; $row < $rows; ++$row)
				$tbody['rows'][] = $this->renderTableRowJSON($row);

			foreach ($this->dataProvider->getKeys() as $key)
				$tbody['keys'][] = CHtml::encode($key);

		} else
		{
			ob_start();
			$this->renderEmptyText();
			$content = ob_get_contents();
			ob_end_clean();

			$tbody['rows'][0]['cols'][] = array('attrs' => "colspan=\"" . count($this->columns) . "\"", 'content' => $content);
			$tbody['rows'][0]['class'] = " ";
		}
		$tbody['pager'] = $this->renderPager();

		return $tbody;
	}

	/**
	 * Renders a table body row.
	 * @param integer $row the row number (zero-based).
	 */
	public function renderTableRow($row)
	{
		if ($this->rowCssClassExpression !== null)
		{
			$data = $this->dataProvider->data[$row];
			echo '<tr class="' . $this->evaluateExpression($this->rowCssClassExpression, array('row' => $row, 'data' => $data)) . '">';
		} else if (is_array($this->rowCssClass) && ($n = count($this->rowCssClass)) > 0)
			echo '<tr class="' . $this->rowCssClass[$row % $n] . '">';
		else
			echo '<tr>';
		foreach ($this->columns as $column)
			$column->renderDataCell($row);
		echo "</tr>\n";
	}

	/**
	 * Renders a table body row for JSON requests  - assumed ajax is for JSON
	 * @param $row
	 * @return array
	 */
	protected function renderTableRowJSON($row)
	{
		$json = array();
		if ($this->rowCssClassExpression !== null)
		{
			$data = $this->dataProvider->data[$row];
			$json['class'] = $this->evaluateExpression($this->rowCssClassExpression, array('row' => $row, 'data' => $data));
		} else if (is_array($this->rowCssClass) && ($n = count($this->rowCssClass)) > 0)
			$json['class'] = $this->rowCssClass[$row % $n];
		else
			echo '<tr>';
		foreach ($this->columns as $column)
		{
			$json['cols'][] = $column->renderDataCell($row);

		}

		return $json;
	}

	/**
	 * Creates a column based on a shortcut column specification string.
	 * @param mixed $text the column specification string
	 * @return \TbJSONDataColumn|\TbDataColumn|\CDataColumn the column instance
	 * @throws CException if the column format is incorrect
	 */
	protected function createDataColumn($text)
	{
		if (!preg_match('/^([\w\.]+)(:(\w*))?(:(.*))?$/', $text, $matches))
			throw new CException(Yii::t('zii', 'The column must be specified in the format of "Name:Type:Label", where "Type" and "Label" are optional.'));

		$column = new TbJsonDataColumn($this);
		$column->name = $matches[1];

		if (isset($matches[3]) && $matches[3] !== '')
			$column->type = $matches[3];

		if (isset($matches[5]))
			$column->header = $matches[5];

		return $column;
	}

	/**
	 * Registers necessary client scripts.
	 */
	public function registerClientScript()
	{
		$id = $this->getId();

		if ($this->ajaxUpdate === false)
			$ajaxUpdate = false;
		else
			$ajaxUpdate = array_unique(preg_split('/\s*,\s*/', $this->ajaxUpdate . ',' . $id, -1, PREG_SPLIT_NO_EMPTY));
		$options = array(
			'ajaxUpdate' => $ajaxUpdate,
			'ajaxVar' => $this->ajaxVar,
			'pagerClass' => $this->pagerCssClass,
			'loadingClass' => $this->loadingCssClass,
			'filterClass' => $this->filterCssClass,
			'tableClass' => $this->itemsCssClass,
			'selectableRows' => $this->selectableRows,
			'enableHistory' => $this->enableHistory,
			'updateSelector' => $this->updateSelector,
			'cacheTTL' => $this->cacheTTL,
			'cacheTTLType' => $this->cacheTTLType,
			'localCache' => $this->localCache
		);
		if ($this->ajaxUrl !== null)
			$options['url'] = CHtml::normalizeUrl($this->ajaxUrl);
		if ($this->enablePagination)
			$options['pageVar'] = $this->dataProvider->getPagination()->pageVar;

		foreach (array('beforeAjaxUpdate', 'afterAjaxUpdate', 'ajaxUpdateError', 'selectionChanged') as $prop)
		{
			if ($this->{$prop} !== null)
			{
				if ((!$this->{$prop} instanceof CJavaScriptExpression) && strpos($this->{$prop}, 'js:') !== 0)
				{
					$options[$prop] = new CJavaScriptExpression($this->{$prop});
				} else
				{
					$options[$prop] = $this->{$prop};
				}
			}
		}

		$options = CJavaScript::encode($options);
		$cs = Yii::app()->getClientScript();
		$cs->registerCoreScript('jquery');
		$cs->registerCoreScript('bbq');
		if ($this->enableHistory)
			$cs->registerCoreScript('history');
		$assetsUrl = Yii::app()->bootstrap->getAssetsUrl();
		// jqote2 template engine
		$cs->registerScriptFile($assetsUrl . '/js/jquery.jqote2.min.js', CClientScript::POS_END);
		// ajax cache
		$cs->registerScriptFile($assetsUrl . '/js/jquery.ajax.cache.js', CClientScript::POS_END);
		// custom yiiGridView
		$cs->registerScriptFile($assetsUrl . '/js/jquery.json.yiigridview.js', CClientScript::POS_END);
		$cs->registerScript(__CLASS__ . '#' . $id, "jQuery('#$id').yiiJsonGridView($options);");
	}
}