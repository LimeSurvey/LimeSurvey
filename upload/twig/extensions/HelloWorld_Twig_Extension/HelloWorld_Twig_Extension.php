<?php
/**
 * This is an example to show you how to add custom twig extensions
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
 * 3. Now you access this function in any twig file via: {{ foo($bar) }}, it will show the result of process($bar).
 * If foo() returns some HTML, by default the HTML will be escaped and shows as text.
 * To get the pure HTML, just do: {{ foo($bar) | raw }}
 *
 * NOTE 1: To see much more complex examples of LS Twig functions you can have a look to:  application/core/LS_Twig_Extension.php
 * NOTE 2: A twig extension is PHP code, so it can do anything on the server. So user should trust you to upload your twig extension.
 *         So if your goal is to sell your Survey / Question Theme on LimeStore, you'd rather use twig code as much as you can.
 * NOTE 3: Remember you can ask us to add functions in LS_Twig_Extension by PR on our Git Repo.
 *
 */

use Twig\Extension\AbstractExtension;

class HelloWorld_Twig_Extension extends AbstractExtension
{
  /**
   * Return the string "Hello World"
   */
  public static function helloWorld()
  {
    return "Hello World!";
  }
}
