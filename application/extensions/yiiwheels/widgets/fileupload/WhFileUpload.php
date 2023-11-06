<?php
/**
 * WhFileUpload widget class
 *
 * @author Antonio Ramirez <amigo.cobos@gmail.com>
 * @copyright Copyright &copy; 2amigos.us 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package YiiWheels.widgets.fileupload
 * @uses YiiStrap.helpers.TbArray
 */
Yii::import('bootstrap.helpers.TbArray');
Yii::import('zii.widgets.jui.CJuiInputWidget');

class WhFileUpload extends CJuiInputWidget
{
    /**
     * the url to the upload handler
     * @var string
     */
    public $url;

    /**
     * set to true to use multiple file upload
     * @var boolean
     */
    public $multiple = true;

    /**
     * The upload template id to display files available for upload
     * defaults to null, meaning using the built-in template
     */
    public $uploadTemplate;

    /**
     * The template id to display files available for download
     * defaults to null, meaning using the built-in template
     */
    public $downloadTemplate;

    /**
     * Wheter or not to preview image files before upload
     */
    public $previewImages = true;

    /**
     * Wheter or not to add the image processing pluging
     */
    public $imageProcessing = true;

    /**
     * @var string name of the form view to be rendered
     */
    public $formView = 'yiiwheels.widgets.fileupload.views.form';

    /**
     * @var string name of the upload view to be rendered
     */
    public $uploadView = 'yiiwheels.widgets.fileupload.views.upload';

    /**
     * @var string name of the download view to be rendered
     */
    public $downloadView = 'yiiwheels.widgets.fileupload.views.download';

    /**
     * @var string name of the view to display images at bootstrap-slideshow
     */
    public $previewImagesView = 'yiiwheels.widgets.fileupload.views.gallery';

    /**
     * Widget initialization
     */
    public function init()
    {
        $this->attachBehavior('ywplugin', array('class' => 'yiiwheels.behaviors.WhPlugin'));

        if ($this->uploadTemplate === null) {
            $this->uploadTemplate = "#template-upload";
        }

        if ($this->downloadTemplate === null) {
            $this->downloadTemplate = "#template-download";
        }

        if (!isset($this->htmlOptions['enctype'])) {
            $this->htmlOptions['enctype'] = 'multipart/form-data';
        }
        parent::init();
    }

    /**
     * Generates the required HTML and Javascript
     */
    public function run()
    {

        list($name, $id) = $this->resolveNameID();

        $this->htmlOptions['id'] = ($this->hasModel() ? get_class($this->model) : 'fileupload') . '-form';

        $this->options['url'] = $this->url;

        $htmlOptions = array();

        if ($this->multiple) {
            $htmlOptions["multiple"] = true;
        }

        $this->render($this->uploadView);
        $this->render($this->downloadView);
        $this->render($this->formView, array('model', $this->model, 'name' => $name, 'htmlOptions' => $htmlOptions));

        if ($this->previewImages || $this->imageProcessing) {
            $this->render($this->previewImagesView);
        }

        $this->registerClientScript();
    }

    /**
     * Registers and publishes required scripts
     */
    public function registerClientScript()
    {

        /* publish assets dir */
        $path      = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        $assetsUrl = $this->getAssetsUrl($path);

        /* @var $cs CClientScript */
        $cs = Yii::app()->getClientScript();

        $cs->registerCssFile($assetsUrl . '/css/jquery.fileupload-ui.css');

        // Upgrade widget factory
        // @todo remove when jquery.ui 1.9+ is fully integrated into stable Yii versions
        $cs->registerScriptFile($assetsUrl . '/js/vendor/jquery.ui.widget.js', CClientScript::POS_END);

        //The Templates plugin is included to render the upload/download listings
        $cs->registerScriptFile($assetsUrl . '/js/tmpl.min.js', CClientScript::POS_END);

        if ($this->previewImages || $this->imageProcessing) {
            $cs->registerScriptFile($assetsUrl . '/js/load-image.min.js', CClientScript::POS_END);
            $cs->registerScriptFile($assetsUrl . '/js/canvas-to-blob.min.js', CClientScript::POS_END);
            // gallery :)
            $this->getYiiWheels()->registerAssetCss("bootstrap-image-gallery.min.css");
            $this->getYiiWheels()->registerAssetJs("bootstrap-image-gallery.min.js", CClientScript::POS_END);
        }
        //The Iframe Transport is required for browsers without support for XHR file uploads
        $cs->registerScriptFile($assetsUrl . '/js/jquery.iframe-transport.js', CClientScript::POS_END);
        $cs->registerScriptFile($assetsUrl . '/js/jquery.fileupload.js', CClientScript::POS_END);

        // The File Upload image processing plugin
        if ($this->imageProcessing) {
            $cs->registerScriptFile($assetsUrl . '/js/jquery.fileupload-ip.js', CClientScript::POS_END);
        }
        // The File Upload file processing plugin
        if ($this->previewImages) {
            $cs->registerScriptFile($assetsUrl . '/js/jquery.fileupload-fp.js', CClientScript::POS_END);
        }
        // locale
        $cs->registerScriptFile($assetsUrl . '/js/jquery.fileupload-locale.js', CClientScript::POS_END);

        //The File Upload user interface plugin
        $cs->registerScriptFile($assetsUrl . '/js/jquery.fileupload-ui.js', CClientScript::POS_END);

        /* initialize plugin */
        $selector = '#' . TbArray::getValue('id', $this->htmlOptions, $this->getId());

        $this->getApi()->registerPlugin('fileupload', $selector, $this->options);
    }

}
