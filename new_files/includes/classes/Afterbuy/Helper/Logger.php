<?php

class Logger {
    
    /*
     * Log filename
     */
    protected $filename;

    public function __construct($filename) {
        $this->filename = DIR_FS_LOG . $filename;
    }
    
    public function writeLog($text) {
        if (!is_writable($this->filename)) {
            chmod($this->filename, 0755);
        }
        $log_file = fopen($this->filename, "a");
        $text = "[".date('d/m/Y H:i:s')."] " .$text."\n";
        fwrite($log_file, $text);
    }
    
}

?>