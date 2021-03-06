<?php
// CAPTAIN SLOG
// vim: set expandtab tabstop=4 shiftwidth=4 autoindent smartindent:
// File         : custom_exception.php
// System       : new toolset/boilerplate
// Date         : May 19 2015
// Author       : Mark Addinall
// Synopsis     : Allows the coder to implement custom exceptions in the
//                PHP code not only for error trapping but to enable
//                some pseudo RTOS exception driven scheduling system
//                to be used with incoming AJaX alarms, semaphores
//                and monitors.
//
//                 Adapted to the USM system 17 April 2016.
//



// -------------------------------
interface IException
{
    /* Protected methods inherited from Exception class */
    public function getMessage();                 // Exception message
    public function getCode();                    // User-defined Exception code
    public function getFile();                    // Source filename
    public function getLine();                    // Source line
    public function getTrace();                   // An array of the backtrace()
    public function getTraceAsString();           // Formated string of trace
   
    /* Overrideable methods inherited from Exception class */
    public function __toString();                 // formatted string for display
    public function __construct($message = null, $code = 0);
}


//--------------------------------------------------------------------
abstract class custom_exception extends Exception implements IException
{
    protected $message = 'Unknown exception';     // Exception message
    private   $string;                            // Unknown
    protected $code    = 0;                       // User-defined exception code
    protected $file;                              // Source filename of exception
    protected $line;                              // Source line of exception
    private   $trace;                             // Unknown

    //----------------------------------------------------
    public function __construct($message = null, $code = 0)
    {
        if (!$message) {
            throw new $this('Unknown '. get_class($this));
        }
        parent::__construct($message, $code);
    }

    //--------------------------
    public function __toString()
    {
        return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n"
                                . "{$this->getTraceAsString()}";
    }
}
?>
