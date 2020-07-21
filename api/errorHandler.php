<?php
  interface IException
  {
    /*Protected methods inherited from Exception Class*/
    public function getMessage();
    public function getCode();
    public function getFile();
    public function getLine();
    public function getTrace();
    public function getTraceAsString();
    public function _toString();
    public function __construct($message=null, $code=0);
  }
  
  abstract class CustomException extends Exception implements IException
  {
    protected $message = 'Unknown Exception';
    private $string;
    protected $code = 0;
    protected $file;
    protected $line;
    private $trace;
    
    public function __construct($message=null, $code=0) {
      if(!$message) {
	throw new $this('Unknown'.get_class($this));
      }
      parent::__construct($message, $code);
    }
    
    public function _toString() {
      return get_class($this)." '{$this->message}' in {$this->file} ({$this->line})\n"
      ."{$this->getTraceAsString()}";
    }
  }
?>
