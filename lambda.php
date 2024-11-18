<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

/**
 * defmacro
 * quote, backquote, antiquote
 * defmacro accepts forms
 *
 * ELISP:
 * (symbol-function 'foo)
 * cl-loop
 * (defmacro when (condition body) `(if ,condition ,body))
 *
 * 14:47 < olle> Why are quote etc needed for macro programming if macro is "just" a search-and-replace in the syntax tree?
 * 14:59 < uhuh> As far as I understand the expected output of a macro is some code in list form to be evaluated, but you can do all sorts of things within the 
 macro to generate this output. So doing a search-and-replace is only one of many possibilities.
 *               15:00 < uhuh> You could (possibly?) call an LLM to generate code and return that from the macro for example
 *               15:01 < uhuh> So since you can do computation within the macro body (which gets executed at compile-time) you don't want everything to be quoted
 *
 *
 * https://www.gnu.org/software/emacs/manual/html_node/elisp/Expansion.html
 */
abstract class SexprBase
{
    /**
     * @return SplStack<string>
     */
    public function parse(string $sc)
    {
        // Remove comments
        $sc = preg_replace('/;.*$/m', '', $sc);
        // Normalize string
        $sc = trim((string) preg_replace('/[\t\n\r\s]+/', ' ', $sc));
        $current = new SplStack();
        $base = $current;
        $prev = null;
        $history = new SplStack();
        $buffer = '';
        $inside_string = 0;
        $inside_symbol = 0;
        // Build tree structure
        for ($i = 0; $i < strlen($sc); $i++) {
            $char = $sc[$i];
            if ($char === "'") {
                $inside_symbol = 1;
            } 
            if ($char === '(') {
                $inside_symbol = 0;
                $prev = $current;
                $history->push($current);
                $current = new SplStack();
                $prev->push($current);
            } elseif ($char === ')') {
                $inside_symbol = 0;
                if ($buffer) {
                    $current->push($buffer);
                    $buffer = '';
                }
                $current = $history->pop();
            } elseif ($char === '"') {
                $inside_symbol = 0;
                $inside_string = 1 - $inside_string;
                if (!$inside_string) {
                    $current->push(new Str($buffer));
                    $buffer = '';
                }
            } elseif ($char === ' ' && $inside_symbol) {
                $current->push(new Sym($buffer));
                $buffer = '';
                $inside_symbol = 0;
            } elseif ($char === ' ' && !$inside_string) {
                if ($buffer !== '') {
                    $current->push($buffer);
                    $buffer = '';
                }
            } else {
                $buffer .= $char;
            }
        } 
        return $base;
    }
}

class Sexpr extends SexprBase
{
    public $env = [];

    public function eval($sexpr)
    {
        if (!is_object($sexpr)) {
            if ((string) intval($sexpr) === $sexpr) {
                return intval($sexpr);
            }
            if (isset($this->env[$sexpr])) {
                $thing = $this->env[$sexpr];
                if ($thing instanceof Fun) {
                    // Function
                    return $this->eval($thing->body);
                } else {
                    // Variable
                    return $thing;
                }
            }
        }
        if ($sexpr instanceof Str) {
            return $sexpr->s;
        }
        if ($sexpr instanceof Sym) {
            return $sexpr->s;
        }
        if (is_string($sexpr)) {
            throw new Exception($sexpr);
        }
        $result = 0;
        if (count($sexpr) === 0) {
            return null;
        }
        $op = $sexpr->shift();
        if ($op instanceof SplStack) {
            return $this->eval($op);
        }
        switch ($op) {
            case "php":
                $fn = $sexpr->bottom();
                if ($fn instanceof Sym) {
                    $fn = $this->eval($sexpr->shift());
                    $arg = $this->eval($sexpr->shift());
                    call_user_func($fn, $arg);
                } elseif ($fn instanceof SplStack) {
                    $list = $this->eval($sexpr->shift());
                    foreach ($list as $node) {
                        $fn = $this->eval($node->shift());
                        $arg = $this->eval($node->shift());
                        call_user_func($fn, $arg);
                    }
                } else {
                    // what
                }
                break;
            case "=":
                $branch2 = $sexpr->pop();
                $branch1 = $sexpr->pop();
                return ($this->eval($branch1) === $this->eval($branch2));
            case "!=":
                $branch2 = $sexpr->pop();
                $branch1 = $sexpr->pop();
                return ($this->eval($branch1) !== $this->eval($branch2));
            case "and":
                $branch2 = $sexpr->pop();
                $branch1 = $sexpr->pop();
                return ($this->eval($branch1) && $this->eval($branch2));
            case "or":
                $branch2 = $sexpr->pop();
                $branch1 = $sexpr->pop();
                return ($this->eval($branch1) || $this->eval($branch2));
            case "true":
                return 1;
            case "false":
                return 0;
            case "nil":
                return null;
            case "if":
                $cond = $sexpr->shift();
                $branch1 = $sexpr->shift();
                $branch2 = $sexpr->shift();
                if ($this->eval($cond)) {
                    return $this->eval($branch1);
                } else {
                    return $this->eval($branch2);
                }
            case "concat":
                $arg1 = $sexpr->shift();
                $arg2 = $sexpr->shift();
                return $this->eval($arg1) . $this->eval($arg2);
                break;
            case "+":
                $arg1 = $sexpr->shift();
                $arg2 = $sexpr->shift();
                return $this->eval($arg1) + $this->eval($arg2);
            case "*":
                $arg1 = $sexpr->shift();
                $arg2 = $sexpr->shift();
                return $this->eval($arg1) * $this->eval($arg2);
            case "list":
                $l = new SplStack();
                while (count($sexpr) > 0 && $el = $sexpr->shift()) {
                    $l->push($el);
                }
                return $l;
            case "defun":
                $name = $sexpr->shift();
                $args = $sexpr->shift();
                $body = $sexpr->shift();
                $this->env[$name] = new Fun($name, $args, $body);
                break;
            case "defmacro":
                $name = $sexpr->shift();
                $args = $sexpr->shift();
                $body = $sexpr->shift();
                $this->env[$name] = new Macro($name, $args, $body);
                break;
            case "setq":
                $value = $sexpr->pop();
                $name  = $sexpr->pop();
                $this->env[$name] = $this->eval($value);
                break;
            case "map":
                $fn   = $this->eval($sexpr->shift());
                $list = $this->eval($sexpr->shift());
                foreach ($list->body as $elem) {
                    // TODO
                }
                break;
            case "progn":
                $result = null;
                foreach ($sexpr as $s) {
                    $result = $this->eval($s);
                }
                return $result;
                break;
            case "new":
                $classname = $this->eval($sexpr->shift());
                $arg = $this->eval($sexpr->shift());
                error_log('here');
                error_log($arg);
                error_log($classname);
                $class = new $classname($arg);
                error_log('there');
                return $class;
            case "test-class":
                $classname = $this->eval($sexpr->shift());
                $rest = $sexpr->shift();
                $args = null;
                if ($rest[1] === 'constructor') {
                    //print_r($rest[0]);
                    $args = $this->eval($rest[0]);
                }
                die('here');
                while ($methodToTest = $sexpr->pop()) {
                    foreach ($methodToTest as $thing) {
                        //print_r($thing);
                    }
                }
                //print_r($sexpr->pop());
                //print_r($sexpr->pop());
                //die;
                $classUnderTest = new $classname($args);
                return;
            default:
                if (isset($this->env[$op])) {
                    $thing = $this->env[$op];
                    if ($thing instanceof Fun) {
                        foreach ($thing->args as $arg) {
                            $this->replaceArg($arg, $sexpr->shift(), $thing->body);
                        }
                        return $this->eval($thing->body);
                    } elseif ($thing instanceof Macro) {
                        $newBody = $this->clone($thing->body);
                        for ($i = count($thing->args) - 1; $i >= 0; $i--) {
                            $arg = $thing->args->offsetGet($i);
                            $repl = $sexpr->shift();
                            $this->replaceArg($arg, $repl, $newBody);
                        }
                        $newBody = $thing->macroExpand($newBody);
                        return $this->eval($newBody);
                    } else {
                        throw new RuntimeException('Unknown entity in env: ' . $op);
                    }
                } else {
                    throw new RuntimeException('Unsupported operation: ' . $op);
                }
                break;
        }
    }

    /**
     * Recursively replace $arg inside $body with $replaceWith
     *
     * @return void
     */
    public function replaceArg($arg, $replaceWith, SplStack $body)
    {
        foreach ($body as $key => $node) {
            if ($node === $arg) {
                $body->offsetSet(count($body) - $key - 1, $replaceWith);
            }
            if ($node instanceof SplStack) {
                $this->replaceArg($arg, $replaceWith, $node);
            }
        }
    }

    public function clone($obj)
    {
        return unserialize(serialize($obj));
        /*
        if ($s instanceof SplStack) {
            $new = new SplStack();
            for ($i = 0; $i < count($s); $i++) {
                $n = $s->offsetGet(count($s) - $i - 1);
                $new->push($this->clone($n));
            }
            return $new;
        } elseif (is_object($s)) {
            return clone $s;
        } else {
            return $s;
        }
         */
    }
}

class Str
{
    public $s;
    public function __construct($s)
    {
        $this->s = $s;
    }
}

class Sym
{
    public $s;
    public function __construct($s)
    {
        $this->s = ltrim($s, "'");
    }
}

class Fun
{
    public $name;
    public $args;
    public $body;
    public function __construct($n, $args, $b)
    {
        $this->name = $n;
        $this->args = $args;
        $this->body = $b;
    }
}

class Macro
{
    public $name;
    public $args;
    public $body;
    public function __construct($n, $args, $b)
    {
        $this->name = $n;
        $this->args = $args;
        $this->body = $b;
    }

    /**
     * Example body: (list (quote setq) var (list (quote (+ 1 var))))
     */
    public function macroExpand($body)
    {
        if (is_string($body)) {
            return $body;
        } elseif ($body instanceof SplStack) {
            $op = $body->bottom();
            switch ($op) {
                case 'list':
                    $b = new SplStack();
                    $body->shift();    // Discard bottom op
                    while (count($body) > 0 && $n = $body->shift()) {
                        $b->push($this->macroExpand($n));
                    }
                    return $b;
                    break;
                case 'quote':
                    return $body->pop();
                    break;
                default:
                    return unserialize(serialize($body));
            }
        } elseif ($body === 'quote') {
            throw new Exception($body);
        }
        return null;
    }
}

/** Since require_once is not a proper PHP function */
function req($f)
{
    require_once($f);
}

$s = new Sexpr();
$source = file_get_contents($argv[1]);
$sexp = $s->parse($source);
while ($sex = $sexp->shift()) {
    $result = $s->eval($sex);
    if (count($sexp) === 0) {
        break;
    }
}
