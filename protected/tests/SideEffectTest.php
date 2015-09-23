<?php

class SideEffectTest extends PHPUnit_Framework_TestCase {


    public function fileProvider() {
        $result = array_map(function($fileName) {
            return ['file' => $fileName];
        }, CFileHelper::findFiles(realpath(__DIR__ . '/..'), [
            'fileTypes' => ['php'],
            'exclude' => ['vendor', 'views', 'entry.php', 'tests']
        ]));
//        var_dump($result); die();
        return $result;
    }

    /**
     * @dataProvider fileProvider
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @param $directory
     */
    public function testSideEffects($file) {
        $this->expectOutputString($file);
        $handle = fopen($file, 'r+');
        $string = fread($handle, 200);
        fclose($handle);
        echo $file;
        if (strpos($string, 'exit') !== false) {
            echo "Exit in first 200 chars!";
        } else {
            include($file);
        }

    }


}