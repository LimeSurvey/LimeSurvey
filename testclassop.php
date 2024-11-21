<?php

return new CustomOp(
    "test-class",
    function($that, $sexpr) {
        $classname = $this->eval($sexpr->shift());
        if (!class_exists($classname)) {
            throw new \Exception('Found no class ' . $classname);
        }
        $constr = $this->findFirst($sexpr, 'constructor');
        if ($constr) {
            $args = $this->eval($constr->pop());
        }
        $tests = $this->findAll($sexpr, 'test-method');

        $refl = new ReflectionClass($classname);
        $classUnderTest = $refl->newInstanceArgs([$args]);
        foreach ($tests as $test) {
            $_ = $test->shift();
            $methodName = $this->eval($test->shift());
            $method = $refl->getMethod($methodName);
            $method->setAccessible(true); // Make the protected method accessible
            $arguments = $this->findFirst($test, 'arguments');
            if ($arguments) {
                $_ = $arguments->shift();
                $evalArgs = [];
                foreach ($arguments as $arg) {
                    $evalArgs[] = $this->eval($arg);
                }
            } else {
                $evalArgs = [];
            }
            $result = $method->invoke($classUnderTest, ...$evalArgs);
            $expectedResult = $this->findFirst($test, 'result');
            $expectedResult = $this->eval($expectedResult->pop());
            if ($expectedResult === $result) {
                // All good
                echo "Success\n";
            } else {
                throw new \Exception('Wrong result');
            }
        }
        return;
    }
);
