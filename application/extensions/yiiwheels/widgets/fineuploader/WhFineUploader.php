<?php
/**
 * WhFineUploader widget class
 * Inspired by https://github.com/anggiaj/EFineUploader
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.fileuploader
 * @uses YiiStrap.helpers.TbArray
 */
Yii::import('bootstrap.helpers.TbArray');

class WhFineUploader extends CInputWidget
{
    /**
     * @var string upload action url
     */
    public $uploadAction;

    /**
     * @var string the HTML tag to render the uploader to
     */
    public $tagName = 'div';

    /**
     * @var string text to display if javascript is disabled
     */
    public $noScriptText;

    /**
     * @var array the plugin options
     */
    public $pluginOptions = array();

    /**
     * @var array the events
     */
    public $events = array();

    /**
     * @var string which scenario we get the validation from
     */
    public $scenario;

    /**
     * @var array d
     */
    protected $defaultOptions = array();

    /**
     * @throws CException
     */
    public function init()
    {
        if ($this->uploadAction === null) {
            throw new CException(Yii::t('zii', '"uploadAction" attribute cannot be blank'));
        }
        if ($this->noScriptText === null) {
            $this->noScriptText = Yii::t('zii', "Please enable JavaScript to use file uploader.");
        }

        $this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));

        $this->initDefaultOptions();
    }

    /**
     * Widget's run method
     */
    public function run()
    {
        $this->renderTag();
        $this->registerClientScript();
    }

    /**
     * Renders the tag where the button is going to be rendered
     */
    public function renderTag()
    {
        echo CHtml::tag($this->tagName, $this->htmlOptions, '<noscript>' . $this->noScriptText . '</noscript>', true);
    }

    /**
     * Registers required client script for finuploader
     */
    public function registerClientScript()
    {
        /* publish assets dir */
        $path      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        $assetsUrl = $this->getAssetsUrl($path);

        /* @var $cs CClientScript */
        $cs = Yii::app()->getClientScript();

        $script = YII_DEBUG ? 'jquery.fineuploader-3.2.js' : 'jquery.fineuploader-3.2.min.js';

        $cs->registerCssFile($assetsUrl . '/css/fineuploader.css');
        $cs->registerScriptFile($assetsUrl . '/js/' . $script);

        /* initialize plugin */
        $selector = '#' . TbArray::getValue('id', $this->htmlOptions, $this->getId());

        $this->getApi()->registerPlugin(
            'fineUploader',
            $selector,
            CMap::mergeArray($this->defaultOptions, $this->pluginOptions)
        );
        $this->getApi()->registerEvents($selector, $this->events);
    }

    /**
     * Sets up default options for the plugin
     * - thanks https://github.com/anggiaj
     */
    protected function initDefaultOptions()
    {
        list($name, $id) = $this->resolveNameID();

        TbArray::defaultValue('id', $id, $this->htmlOptions);
        TbArray::defaultValue('name', $name, $this->htmlOptions);


        $this->defaultOptions = array(
            'request'    => array(
                'endpoint'  => $this->uploadAction,
                'inputName' => $name,
            ),
            'validation' => $this->getValidator(),
            'messages'   => array(
                'typeError'    => Yii::t('zii', '{file} has an invalid extension. Valid extension(s): {extensions}.'),
                'sizeError'    => Yii::t('zii', '{file} is too large, maximum file size is {sizeLimit}.'),
                'minSizeError' => Yii::t('zii', '{file} is too small, minimum file size is {minSizeLimit}.'),
                'emptyError:'  => Yii::t('zii', '{file} is empty, please select files again without it.'),
                'noFilesError' => Yii::t('zii', 'No files to upload.'),
                'onLeave'      => Yii::t(
                    'zii',
                    'The files are being uploaded, if you leave now the upload will be cancelled.'
                )
            ),
        );
    }

    /**
     * @return array
     */
    protected function getValidator()
    {
        $ret = array();
        if ($this->hasModel()) {
            if ($this->scenario !== null) {
                $originalScenario = $this->model->getScenario();
                $this->model->setScenario($this->scenario);
                $validators = $this->model->getValidators($this->attribute);
                $this->model->setScenario($originalScenario);

            } else {
                $validators = $this->model->getValidators($this->attribute);
            }

            // we are just looking for the first founded CFileValidator
            foreach ($validators as $validator) {
                if (is_a($validator, 'CFileValidator')) {
                    $ret = array(
                        'allowedExtensions' => explode(',', str_replace(' ', '', $validator->types)),
                        'sizeLimit'         => $validator->maxSize,
                        'minSizeLimit'      => $validator->minSize,
                    );
                    break;
                }
            }
        }
        return $ret;
    }
}