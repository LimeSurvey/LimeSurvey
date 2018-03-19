<?php
/*
 * LimeSurvey (tm)
 * Copyright (C) 2011 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 */
class CompileAssetsCommand extends CConsoleCommand
{

    /**
     * @param array $aArguments
     * @return void
     */
    public function run($aArguments)
    {
        if (isset($aArguments) && count($aArguments) < 2) {
            echo "=========================================================================\n";
            echo "=== Please provide method and path to compile package assets          ===\n";
            echo "=== usage example:                                                    ===\n";
            echo "=== php application/commands/console.php [method] [package] [?silent] ===\n";
            echo "=========================================================================\n";
            return;
        }

        $method  = $aArguments[0];
        $package = $aArguments[1];
        $silent  = isset($aArguments[2]) ? $aArguments[2] : false;

        if (!in_array($method, ['gulp', 'bash', 'npm', 'uglify'])) {
            echo "=========================================================================\n";
            echo "=== ERROR! Please provide a registered method for compiling           ===\n";
            echo "=== Possible methods are:                                             ===\n";
            echo "=== 'gulp', 'bash', 'npm'                                             ===\n";
            echo "=========================================================================\n";
            return;
        }
        echo "=========================================================================\n";
        echo "=== Compiling package ".$package." with ".$method." \n";
        echo "=========================================================================\n";

        $sCurrentDir = dirname(__FILE__);
        $assetsFolder = realpath($sCurrentDir.'/../../assets/');
        $packageFolder = $assetsFolder.'/packages/'.$package;

        if (!file_exists($packageFolder)) {
            echo "=========================================================================\n";
            echo "=== ERROR! Package does not exist! Exiting.                           ===\n";
            echo "=== Checked path:                                                     ===\n";
            echo "=== ".$packageFolder."\n";
            echo "=========================================================================\n";
            return;
        }

        $logfile = false;

        if ($silent == true && $silent !== "1") {
            $logfile = $silent;
        } else if ($silent == true && $silent === "1") {
            $logfile = " /dev/null";
        }

        switch ($method) {
            case "gulp" :
                $this->liveExecuteCommand("(cd {$packageFolder} && {$method})", $logfile);
                break;
            case "npm" :
                $this->liveExecuteCommand("(cd {$packageFolder} && {$method} run compile)", $logfile);
                break;
            case "bash" :
                $this->liveExecuteCommand("(cd {$packageFolder} && {$method} compile.sh)", $logfile);
                break;
        }
    }

    private function liveExecuteCommand($cmd, $logfile = false)
    {
    
        while (@ ob_end_flush()); // end all output buffers if any
    
        if ($logfile !== false) {
            $proc = popen("$cmd >{$logfile} 2>&1; echo Exit status : $?", 'r');
        } else {
            $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');
        }
    
        $live_output     = "";
        $complete_output = "";
    
        while (!feof($proc)) {
            $live_output     = fread($proc, 4096);
            $complete_output = $complete_output.$live_output;

            echo "$live_output";
            @ flush();
        }
    
        pclose($proc);
    
        // get exit status
        preg_match('/[0-9]+$/', $complete_output, $matches);
    
        // return exit status and intended output
        return array(
                        'exit_status'  => intval($matches[0]),
                        'output'       => str_replace("Exit status : ".$matches[0], '', $complete_output)
                        );
    }
}
