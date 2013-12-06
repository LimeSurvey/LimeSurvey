<?php
/**
 * TbMarkdownEditorJs class
 *
 * @author: antonio ramirez <antonio@clevertech.biz>
 * @copyright Copyright &copy; Clevertech 2012-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiBooster bootstrap.widgets
 */
class TbMarkdownEditorJS extends CInputWidget
{
	/**
	 * Editor width
	 */
	public $width = '100%';
	/**
	 * Editor height
	 */
	public $height = '400px';

	/**
	 * Display editor
	 */
	public function run()
	{

		list($name, $id) = $this->resolveNameID();

		$this->registerClientScript($id);

        // Markdown Editor looks for an id of wmd-input...
		$this->htmlOptions['id'] = $id;

        $this->htmlOptions['class'] = (isset($this->htmlOptions['class']))
            ? $this->htmlOptions['class'].' wmd-input'
            : 'wmd-input';

		if (!array_key_exists('style', $this->htmlOptions))
		{
			$this->htmlOptions['style'] = "width:{$this->width};height:{$this->height};";
		}
		// Do we have a model?
		if ($this->hasModel())
		{
			$html = CHtml::activeTextArea($this->model, $this->attribute, $this->htmlOptions);
		}
        else
		{
			$html = CHtml::textArea($name, $this->value, $this->htmlOptions);
		}
		echo $html;
	}

	/**
	 * Register required script files
	 * @param $id
	 */
	public function registerClientScript($id)
	{
		Yii::app()->bootstrap->registerAssetCss('markdown.editor.css');
		Yii::app()->bootstrap->registerAssetJs('markdown.converter.js', CClientScript::POS_HEAD);
        Yii::app()->bootstrap->registerAssetJs('markdown.sanitizer.js', CClientScript::POS_HEAD);
        Yii::app()->bootstrap->registerAssetJs('markdown.editor.js', CClientScript::POS_HEAD);
        Yii::app()->clientScript->registerScript(
            $id,
            "var converter = Markdown.getSanitizingConverter();
            var editor = new Markdown.Editor(converter, '".$id."');
            editor.run();",
            CClientScript::POS_END
        );
	}
}