<?php

/** Call a native PHP function */
return new CustomOp(
    "php",
    function($that, $sexpr) {
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
    }
);
