<?php

/** Call a native PHP function */
return new CustomOp(
    "debug",
    function($that, $sexpr) {
        print_r($sexpr);
    }
);
