<?php 
// vim: set tabstop=4 shiftwidth=4 autoindent expandtab:
//---------------------------------------------------------
// CAPTAIN  SLOG
//---------------------------------------------------------
//
//  FILE:       session.php
//  SYSTEM:     new toolkit/boilerplate
//  AUTHOR:     Mark Addinall
//  DATE:       23/02/2016
//  SYNOPSIS:   OOD/OOP paradigm approach to PHP SESSION 
//              management.  It is not that dificult to
//              make PHP an elegant language to implement
//              server side session management.  It would
//              be better done client side in Javascript,
//              alas, not in USM 2.2.6.  This is a bit o a hybrid
//              as I have dropped server side session management
//              from my 2016 code base.
//              Adapted into USM system 17 April 2016
//             
            

include_once ('custom_exception.php');
 
if ( !class_exists('custom_exception') ) {
    class custom_exception extends Exception {}
}


class session_handler_exception         extends custom_exception {}
class session_disabled_exception        extends session_handler_exception {}
class invalid_argument_type_exception   extends session_handler_exception {}
class expired_session_exception         extends session_handler_exception {}
 
 
//-----------
class Session
{
    // The directory name for the session 
    private  $SESSION_DIR = 'd79252d7dea8e2812b4ebf29ffc603ed/';
    
    // The name used for the session
    private  $SESSION_NAME = 'f7eac143c2e6c95e84a3e128e9ddcee6';
    
     // Session Age.
     // 
     // The number of seconds of inactivity before a session expires.
     // 
     // @var integer
    
    
    protected  $SESSION_AGE = 1800;


    //---------------------------------------
    public  function write($key, $value) {

    // Writes a value to the current session data.
    // 
    // @param string $key String identifier.
    // @param mixed $value Single value or array of values to be written.
    // @return mixed Value or array of values written.
    // @throws InvalidArgumentTypeException Session key is not a string value.

        if ( !is_string($key) )
            throw new InvalidArgumentTypeException('Session key must be string value');
        $this->_init();
        $_SESSION[$key] = $value;
        $this->_age();
        return $value;
    }


    //-----------------------------------------------
    public  function read($key, $child = false) {
     // Reads a specific value from the current session data.
     // 
     // @param string $key String identifier.
     // @param boolean $child Optional child identifier for accessing array elements.
     // @return mixed Returns a string value upon success.  Returns false upon failure.
     // @throws InvalidArgumentTypeException Session key is not a string value.
    
    
        if ( !is_string($key) ) {
            return false;
        }
        if (isset($_SESSION[$key])) {
            if($this->_age() === false) {
                if (false == $child) {
                    return $_SESSION[$key];
                } else {
                    if (isset($_SESSION[$key][$child])) {
                        return $_SESSION[$key][$child];
                    }
                }
            }
        }
        return false;   
    }


    //-----------------------------------
    public  function delete($key) {
    // Deletes a value from the current session data.
    // 
    // @param string $key String identifying the array key to delete.
    // @return void
    // @throws InvalidArgumentTypeException Session key is not a string value.
        if ( !is_string($key) )
            throw new InvalidArgumentTypeException('Session key must be string value');
        $this->_init();
        unset($_SESSION[$key]);
        $this->_age();
    }
    
    /////
     // Echos current session data.
     // 
     // @return void
     ///
    public  function dump()
    {
        $this->_init();
        echo nl2br(print_r($_SESSION));
    }
 
    /////
     // Starts or resumes a session by calling {@link Session::_init()}.
     // 
     // @see Session::_init()
     // @return boolean Returns true upon success and false upon failure.
     // @throws SessionDisabledException Sessions are disabled.
     ///
    public  function start($regenerate_session_id = true, $limit = 0, $path = '/', $domain = null, $secure_cookies_only = null)
    {
        // this function is extraneous
        return $this->_init($regenerate_session_id, $limit, $path, $domain, $secure_cookies_only);
    }
    
    /////
     // Expires a session if it has been inactive for a specified amount of time.
     // 
     // @return void
     // @throws ExpiredSessionException() Throws exception when read or write is attempted on an expired session.
     ///
    private  function _age()
    {
        $last = isset($_SESSION['LAST_ACTIVE']) ? $_SESSION['LAST_ACTIVE'] : false ;
        
        if (false !== $last && (time() - $last > $this->$SESSION_AGE)) {
            return $this->destroy();
        }

        $_SESSION['LAST_ACTIVE'] = time();
        return false;
    }
    
    public  function regenerate_session_id() {
      
        $session = array();
        
        foreach ($_SESSION as $k => $v) {
        
            $session[$k] = $v;
            
        }
        
        session_destroy();
        
        session_id(bin2hex(openssl_random_pseudo_bytes(16)));
        
        session_start();
        
        foreach ($session as $k => $v) {
        
            $_SESSION[$k] = $v;
            
        }
      
    }
    
    /////
     // Returns current session cookie parameters or an empty array.
     // 
     // @return array Associative array of session cookie parameters.
     ///
    public  function params()
    {
        $r = array();
        if ( '' !== session_id() )
        {
            $r = session_get_cookie_params();
        }
        return $r;
    }
    
    /////
     // Closes the current session and releases session file lock.
     // 
     // @return boolean Returns true upon success and false upon failure.
     ///
    public  function close()
    {
        if ( '' !== session_id() )
        {
            return session_write_close();
        }
        return true;
    }
    
    /////
     // Alias for {@link Session::close()}.
     // 
     // @see Session::close()
     // @return boolean Returns true upon success and false upon failure.
     ///
    public  function commit()
    {
        return $this->close();
    }
    
    /////
     // Removes session data and destroys the current session.
     // 
     // @return void
     ///
    public  function destroy()
    {
        if ( '' !== session_id() )
        {
            $_SESSION = array();
 
            // If it's desired to kill the session, also delete the session cookie.
            // Note: This will destroy the session, and not just the session data!
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
 
            session_destroy();
        }
    }
    
    /////
     // Initializes a new session or resumes an existing session.
     // 
     // @return boolean Returns true upon success and false upon failure.
     // @throws SessionDisabledException Sessions are disabled.
     ///
    private  function _init($regenerate_session_id = false, $limit = 0, $path = '/', $domain = null, $secure_cookies_only = null)
    {
        if (function_exists('session_status'))
        {
            // PHP 5.4.0+
            if (session_status() == PHP_SESSION_DISABLED)
                throw new SessionDisabledException();
        }
        
        if ( '' === session_id() )
        {
            $site_root = BASE_URI;
            $session_save_path = $site_root . $this->$SESSION_DIR;
            session_save_path($session_save_path);
            
            session_name($this->$SESSION_NAME);
            
            $domain = isset($domain) ? $domain : $_SERVER['SERVER_NAME'];
            
            session_set_cookie_params($limit, $path, $domain, $secure_cookies_only, true);
            
            session_start();
            
            if ($regenerate_session_id) {
                $this->regenerate_session_id();
            }
            
            return true;
        
        }
        
        $this->_age();

        if ($regenerate_session_id && rand(1, 100) <= 5) {
            $this->regenerate_session_id();
            $_SESSION['regenerated_id'] = session_id();
        }
        
        return true;
        
    }
}
