<?php
/**
 * This extension is needed to add complex functions to twig, needing specific process (like accessing config datas).
 * Most of the calls to internal functions don't need to be set here, but can be directly added to the internal config file.
 * For example, the calls to encode, gT and eT don't need any extra parameters or process, so they are added as filter in the congif/internal.php:
 *
 * 'filters' => array(
 *     'jencode' => 'CJSON::encode',
 *     't'     => 'eT',
 *     'gT'    => 'gT',
 * ),
 *
 * So you only add functions here when they need a specific process while called via Twig. 
 * To add an advanced function to twig.
 *
 * 1. Add it here as a static public function
 *      eg:
 *          static public function foo($bar)
 *          {
 *              return procces($bar);
 *          }
 *
 * 2. Add it in config/internal.php as a function, and as an allowed function in the sandbox
 *      eg:
 *          twigRenderer' => array(
 *              ...
 *              'functions' => array(
 *                  ...
 *                  'foo' => 'LS_Twig_Extension::foo',
 *                ...),
 *              ...
 *              'sandboxConfig' => array(
 *              ...
 *                  'functions' => array('include', ..., 'foo')
 *                 ),
 *
 * Now you access this function in any twig file via: {{ foo($bar) }}, it will show the result of process($bar).
 * If LS_Twig_Extension::foo() returns some HTML, by default the HTML will be escaped and shows as text.
 * To get the pure HTML, just do: {{ foo($bar) | raw }}
 */


class LS_Twig_Extension extends Twig_Extension
{
    static public function registerPublicCssFile($value)
    {
        App()->getClientScript()->registerCssFile(App()->getConfig('publicstyleurl') . $value);
    }
}
