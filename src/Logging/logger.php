<?php

namespace Logging;

defined('DS') || define('DS', DIRECTORY_SEPARATOR);

/*
 * General Logging class
 */
class Logger {

    public $log_file = '';
    public $log_path = '';
    public $log_level = 0;
    public $level = '';
    private $path_check = false;
    private $log_entry = '';
    const DEBUG = 4;
    const INFO = 3;
    const WARN = 2;
    const ERROR = 1;

    public function __construct($path = '', $file = '', $log_level = 'ERROR'){

        date_default_timezone_set('America/New_York'); //Set datetime zone

        $this->updateLogFilePath($path, $file);
        $this->setLogLevel($log_level);
    }

    /**
     * Perform a check to determine whether specified log file exists. If not, attempt to create it.
     */
    private function pathCheck(){

        $this->path_check = false;

        //Test whether log file exists. If not, attempt to create it.
        if (is_dir($this->log_path) && !file_exists($this->log_path . DS . $this->log_file)){

            if (is_writable($this->log_path)){
                $log = "\r\n [" . date("m-d-y h:i:s A") . "] Initializing... ";
                file_put_contents($this->log_path . DS . $this->log_file, $log);
            }
        }

        //Test log file and path and set the path_check to true on success.
        if (is_dir($this->log_path) && is_writable($this->log_path) && file_exists($this->log_path . DS . $this->log_file)){
            $this->path_check = true;
        }

        if (!$this->path_check){
            error_log("Path set in Logger not found: " . $this->log_path . DS . $this->log_file);
        }
    }

    /**
     * setLogFile
     * @param string $file
     */
    private function setLogFile($file = ""){
        $this->log_file = $file;
    }

    /**
     * setLogPath
     * @param string $path
     */
    private function setLogPath($path = ""){
        $this->log_path = $path;
    }

    /**
     * updateLogFilePath
     * @param string $path
     * @param string $file
     */
    public function updateLogFilePath($path = "", $file = ""){
        $this->setLogPath($path);
        $this->setLogFile($file);
        $this->pathCheck();
    }

    /**
     * Set initial log level
     * @param string $log_level
     */
    public function setLogLevel($log_level = 'ERROR'){

        switch(strtoupper($log_level)){
            case 'DEBUG':
                $this->log_level = self::DEBUG; //4
                break;
            case 'NOTICE':
            case 'INFO':
                $this->log_level = self::INFO;  //3
                break;
            case 'WARN':
                $this->log_level = self::WARN;  //2
                break;
            case 'ERROR':
            default:
                $this->log_level = self::ERROR; //1
                break;
        }
    }

    /**
     * Set log level for current entry
     * @param string $level
     */
    private function setLogEntryLevel($level = 'ERROR'){
        $this->level = strtoupper($level);
    }

    /**
     * Set current log entry
     * @param string $entry
     */
    private function setLogEntry($entry = ''){
        $this->log_entry = $entry;
    }

    /**
     * DEBUG level message
     * @param string $func
     * @param string $entry
     */
    public function debug($func = '', $entry = ''){

        if ($this->level !== 'DEBUG'){
            $this->setLogEntryLevel('DEBUG');
        }

        if ($this->log_level >= self::DEBUG){
            $this->setLogEntry($func . " : " . $entry);
            $this->write();
        }
    }

    /**
     * INFO level message
     * @param string $func
     * @param string $entry
     */
    public function info($func = '', $entry = ''){

        if ($this->level !== 'INFO'){
            $this->setLogEntryLevel('INFO');
        }

        if ($this->log_level >= self::INFO){
            $this->setLogEntry($func . " : " . $entry);
            $this->write();
        }
    }

    /**
     * WARN level message
     * @param string $func
     * @param string $entry
     */
    public function warn($func = '', $entry = ''){

        if ($this->level !== 'WARN'){
            $this->setLogEntryLevel('WARN');
        }

        if ($this->log_level >= self::WARN){
            $this->setLogEntry($func . " : " . $entry);
            $this->write();
        }
    }

    /**
     * ERROR level message
     * @param string $func
     * @param string $entry
     */
    public function error($func = '', $entry = ''){

        if ($this->level !== 'ERROR'){
            $this->setLogEntryLevel('ERROR');
        }

        if ($this->log_level >= self::ERROR){
            $this->setLogEntry($func . " : " . $entry);
            $this->write();
        }
    }

    /**
     * Log messages to a specified log file
     */
    private function write(){

        if (!$this->log_file || !$this->path_check){
            return false;
        }

        $log = "\r\n [" . date("m-d-y h:i:s A") . "] [" . $this->level . "] - ". $this->log_entry;
        file_put_contents($this->log_path . DS . $this->log_file, $log, FILE_APPEND);
    }
}
