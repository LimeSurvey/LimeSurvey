<?php
namespace ls\cli;
use CConsoleCommand;
class TestCommand extends CConsoleCommand 
{
    public function actionIndex() {
        echo "Open your browser and go to http://localhost:8080.\n";
        shell_exec(PHP_BINARY . " -S localhost:8080");
        echo "Closed.";
    }
}