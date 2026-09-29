<?php
/**
 * Twig view renderer
 *
 * @author Leonid Svyatov <leonid@svyatov.ru>
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @author Nikolas Evers <hello@nikol.as>
 * @link https://github.com/vintagesucks/twig-renderer
 * @link https://twig.symfony.com/
 *
 * @version 3.0.0
 */
class ETwigViewRenderer extends CApplicationComponent implements IViewRenderer
{
    /**
     * @var string Path alias to Twig
     */
    public $twigPathAlias = 'application.vendor.Twig';
    /**
     * @var string Twig template files extension
     */
    public $fileExtension = '.twig';
    /**
      * @var array Twig environment options
      * @see https://twig.symfony.com/doc/3.x/api.html#environment-options
      */
    public $options = array();
    /**
     * @var array Objects or static classes
     * Keys of array are names to call in template, values - objects or names of static class as string
     * Example: array('html'=>'CHtml', 'clientScript'=>Yii::app()->clientScript)
     * Than in template: {{ html.link('Login', 'site/login') }} or {{ clientScript.registerCssFile(...) }}
     */
    public $globals = array();
    /**
     * @var array Custom functions
     * Keys of array are names to call in template, values - names of functions or static methods of some class
     * Example: array('rot13'=>'str_rot13', 'link'=>'CHtml::link')
     * Than in template: {{ rot13('test') }} or {{ link('Login', 'site/login') }}
     */
    public $functions = array();
    /**
     * @var array Custom filters
     * Keys of array are names to call in template, values - names of functions or static methods of some class
     * Example: array('rot13'=>'str_rot13', 'jsonEncode'=>'CJSON::encode')
     * Then in template: {{ 'test'|rot13 }} or {{ model|jsonEncode }}
     */
    public $filters = array();
    /**
     * @var array Custom extensions
     * Example: array('Twig_Extension_Sandbox', 'Twig_Extension_Text')
     */
    public $extensions = array();
    /**
     * @var array Twig lexer options
     * @see https://twig.symfony.com/doc/3.x/recipes.html#customizing-the-syntax
     * Example: Smarty-like syntax
     * array(
     *     'tag_comment'  => array('{*', '*}'),
     *     'tag_block'    => array('{', '}'),
     *     'tag_variable' => array('{$', '}')
     * )
     */
    public $lexerOptions = array();

    private $_twig;
    private $_paths;

    function init()
    {
        $app = Yii::app();

        /** @var $theme CTheme */
        $theme = method_exists($app, 'getTheme') ? $app->getTheme() : null;

        $this->_paths = array();

        if ($theme !== null) {
            $this->_paths[] = $theme->getBasePath();
        }

        $this->_paths[] = $app->getBasePath();

        $loader = new \Twig\Loader\FilesystemLoader($this->_paths);

        $defaultOptions = array(
            'autoescape' => false, // false because other way Twig escapes all HTML in templates
            'auto_reload' => true,
            'cache' => $app->getRuntimePath() . '/twig_cache/',
            'charset' => $app->charset,
        );
        $this->_twig = new \Twig\Environment($loader, array_merge($defaultOptions, $this->options));

        // Adding Yii::app() object to globals
        $this->_twig->addGlobal('App', $app);

        // Adding Yii's core static classes proxy as 'C' shortcut (usage: {{C.Html.tag(...)}})
        $this->_twig->addGlobal('C', new ETwigViewRendererYiiCoreStaticClassesProxy());

        // Adding global 'void' function (usage: {{void(App.clientScript.registerScriptFile(...))}})
        // (@see ETwigViewRendererVoidFunction below for details)
        $this->_twig->addFunction(new \Twig\TwigFunction('void', 'ETwigViewRendererVoidFunction'));

        // Adding custom globals (objects or static classes)
        if (!empty($this->globals)) {
            $this->addGlobals($this->globals);
        }
        // Adding custom functions
        if (!empty($this->functions)) {
            $this->addFunctions($this->functions);
        }
        // Adding custom filters
        if (!empty($this->filters)) {
            $this->addFilters($this->filters);
        }
        // Adding custom extensions
        if (!empty($this->extensions)) {
            $this->addExtensions($this->extensions);
        }
        // Change lexer syntax
        if (!empty($this->lexerOptions)) {
            $this->setLexerOptions($this->lexerOptions);
        }

        return parent::init();
    }

    /**
     * Renders a view file.
     * This method is required by {@link IViewRenderer}.
     * @param CBaseController $context the controller or widget who is rendering the view file.
     * @param string $sourceFile the view file path
     * @param mixed $data the data to be passed to the view
     * @param boolean $return whether the rendering result should be returned
     * @return mixed the rendering result, or null if the rendering result is not needed.
     */
    public function renderFile($context, $sourceFile, $data, $return)
    {
        // current controller properties will be accessible as {{ this.property }}
        $data['this'] = $context;

        $sourceFile = realpath($sourceFile); // to prevent common problems with paths associated with symlinks

        foreach($this->_paths as $path) {
            if(strpos($sourceFile, $path) === 0) {
                $sourceFile = substr($sourceFile, strlen($path));
                break;
            }
        }
        $template = $this->_twig->render($sourceFile, $data);

        if ($return) {
            return $template;
        }

        echo $template;
    }

    /**
     * Adds global objects or static classes
     * @param array $globals @see self::$globals
     */
    public function addGlobals($globals)
    {
        foreach ($globals as $name => $value) {
            if (!is_object($value)) {
                $value = new ETwigViewRendererStaticClassProxy($value);
            }
            $this->_twig->addGlobal($name, $value);
        }
    }

    /**
     * Adds custom functions
     * @param array $functions @see self::$functions
     */
    public function addFunctions($functions)
    {
        $this->_addCustom('Function', $functions);
    }

    /**
     * Adds custom filters
     * @param array $filters @see self::$filters
     */
    public function addFilters($filters)
    {
        $this->_addCustom('Filter', $filters);
    }

    /**
     * Adds custom extensions
     * @param array $extensions @see self::$extensions
     */
    public function addExtensions($extensions)
    {
        foreach ($extensions as $extName) {
            $this->_twig->addExtension(new $extName());
        }
    }

    /**
     * Sets Twig lexer options to change templates syntax
     * @param array $options @see self::$lexerOptions
     */
    public function setLexerOptions($options)
    {
        $lexer = new Twig\Lexer($this->_twig, $options);
        $this->_twig->setLexer($lexer);
    }

    /**
     * Returns Twig object
     * @return Twig\Environment
     */
    public function getTwig()
    {
        return $this->_twig;
    }

    /**
     * Adds custom function or filter
     * @param string $classType 'Function' or 'Filter'
     * @param array $elements Parameters of elements to add
     * @throws CException
     */
    private function _addCustom($classType, $elements)
    {
        $classFunction = '\Twig\Twig' . $classType;

        foreach ($elements as $name => $func) {
            $twigElement = null;

            switch ($func) {
                // Just a name of function
                case is_string($func):
                    $twigElement = new $classFunction($name, $func);
                break;
                // Name of function + options array
                case is_array($func) && is_string($func[0]) && isset($func[1]) && is_array($func[1]):
                    $twigElement = new $classFunction($name, $func[0], $func[1]);
                break;
            }

            if ($twigElement !== null) {
                $this->_twig->{'add'.$classType}($twigElement);
            } else {
                throw new CException(Yii::t('yiiext',
                                             'Incorrect options for "{classType}" [{name}]',
                                             array('{classType}'=>$classType, '{name}'=>$name)));
            }
        }
    }
}

/**
 * Class-proxy for static classes
 * Needed because you can't pass static class to Twig other way
 *
 * @author Leonid Svyatov <leonid@svyatov.ru>
 * @version 1.0.0
 */
class ETwigViewRendererStaticClassProxy
{
    private $_staticClassName;

    public function __construct($staticClassName) {
        $this->_staticClassName = $staticClassName;
    }

    public function __get($property)
    {
        $class = new ReflectionClass($this->_staticClassName);
        return $class->getStaticPropertyValue($property);
    }

    public function __set($property, $value)
    {
        $class = new ReflectionClass($this->_staticClassName);
        $class->setStaticPropertyValue($property, $value);
        return $value;
    }

    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->_staticClassName, $method), $arguments);
    }
}

/**
 * Class-proxy for Yii core static classes
 *
 * @author Leonid Svyatov <leonid@svyatov.ru>
 * @version 1.0.0
 */
class ETwigViewRendererYiiCoreStaticClassesProxy
{
    private $_classes = array();

    function __isset($className)
    {
        return (isset($_classes[$className]) || class_exists('C'.$className));
    }

    function __get($className)
    {
        if (!isset($this->_classes[$className])) {
            $this->_classes[$className] = new ETwigViewRendererStaticClassProxy('C'.$className);
        }

        return $this->_classes[$className];
    }

}

/**
 * Function for adding global 'void' function in Twig
 * Needed to make possible to call functions and methods which return non-string result (object, resources and etc.)
 * For example: {{ void(App.clientScript.registerScriptFile(...)) }}
 *
 * @param mixed $argument
 * @return string
 */
function ETwigViewRendererVoidFunction($argument)
{
    return '';
}
