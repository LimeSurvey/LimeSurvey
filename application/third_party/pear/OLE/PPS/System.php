<?php 
class System {
    public function tmpdir() { 
        if (!empty($_ENV['TMP'])) { return realpath($_ENV['TMP']); } 
        if (!empty($_ENV['TMPDIR'])) { return realpath( $_ENV['TMPDIR']); } 
        if (!empty($_ENV['TEMP'])) { return realpath( $_ENV['TEMP']); } 
        return sys_get_temp_dir(); 
    }  
} 