<?php
/**
 * TbCrumb class file.
 * @author Christoffer Niska <ChristofferNiska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2011-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.widgets
 */

Yii::import('zii.widgets.CBreadcrumbs');

/**
 * Bootstrap breadcrumb widget.
 * @see http://twitter.github.com/bootstrap/components.html#breadcrumbs
 */
class TbBreadcrumbs extends CBreadcrumbs
{
	/**
	 * @var string the separator between links in the breadcrumbs. Defaults to '/'.
	 */
	public $separator = '/';

	/**
	 * Initializes the widget.
	 */
	public function init()
	{
		if (isset($this->htmlOptions['class']))
			$this->htmlOptions['class'] .= ' breadcrumb';
		else
			$this->htmlOptions['class'] = 'breadcrumb';

		// apply bootstrap style
		$this->separator = '<span class="divider">' . $this->separator . '</span>';
	}

	/**
	 * Renders the content of the widget.
	 * @throws CException
	 */
	public function run()
	{
		// Hide empty breadcrumbs.
		if (empty($this->links))
			return;

		$links = array();

		if (!isset($this->homeLink))
		{
			$content = CHtml::link(Yii::t('zii', 'Home'), Yii::app()->homeUrl);
			$links[] = $this->renderItem($content);
		} else if ($this->homeLink !== false)
			$links[] = $this->renderItem($this->homeLink);

		$count = count($this->links);
		$counter = 0;
		foreach ($this->links as $label => $url)
		{
			++$counter; // latest is the active one
			if (is_string($label) || is_array($url))
			{
				$content = CHtml::link($this->encodeLabel ? CHtml::encode($label) : $label, $url);
				$links[] = $this->renderItem($content);
			} else
				$links[] = $this->renderItem($this->encodeLabel ? CHtml::encode($url) : $url, ($counter === $count));
		}

		echo CHtml::tag('ul', $this->htmlOptions, implode($this->separator, $links));
	}

	/**
	 * Renders a single breadcrumb item.
	 * @param string $content the content.
	 * @param boolean $active whether the item is active.
	 * @return string the markup.
	 */
	protected function renderItem($content, $active = false)
	{
		ob_start();
		echo CHtml::openTag('li', $active ? array('class' => 'active') : array());
		echo $content;
		echo '</li>';
		return ob_get_clean();
	}
}
