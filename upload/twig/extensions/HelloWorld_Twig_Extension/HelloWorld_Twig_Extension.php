<?php
/**
 * This is an exemple to show you how to add custom twi extensions
 * To add an advanced function to twig:
 *
 * 1. Add it as a static public function
 *      eg:
 *          static public function foo($bar)
 *          {
 *              return procces($bar);
 *          }
 *
 * 2. Add it in helloworld.xml so it will be added to the sandbox
 *
 * Now you access this function in any twig file via: {{ foo($bar) }}, it will show the result of process($bar).
 * If LS_Twig_Extension::foo() returns some HTML, by default the HTML will be escaped and shows as text.
 * To get the pure HTML, just do: {{ foo($bar) | raw }}
 */


class HelloWorld_Twig_Extension extends Twig_Extension
{
  /**
   * Return the string "Hello World"
   */
  public static function helloWorld()
  {
    return "Hello World!";
  }
}
