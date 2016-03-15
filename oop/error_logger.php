<?php
// vim: set expandtab tabstop=4 shiftwidth=4 autoindent smartindent:
//---------------------------------------------------------
// CAPTAIN  SLOG
//---------------------------------------------------------
//
//	FILE:       error.php 
//	SYSTEM:     2016 New Tools/Boilerplate 
//	AUTHOR:     Mark Addinall
//	DATE:       22/03/2016
//	SYNOPSIS:   This is going to be a RESPONSIVE, lightweight
//              site based on CSS3 and HTML5.
//
//              This file is part of the new set of objects
//              I created for this, and future sites.  They
//              are not completely new as some of the objects
//              in use started out in life during 2002 and have
//              been carried across my code for years.
//
//              As mentioned, the new stuff is to be RESPONSIVE.
//              When I first started coding PHP way back when,
//              tablets and smartphones didn't exist.
//
//              This object is the error logger and general
//              internal reporting dogsbody.
//-----------------------------------------------------------------------------
//	Copyright (c) 2013,2016 Mark Addinall - That's IT - QLD
//	All rights reserved.
//
//	Redistribution and use in source and binary forms, with or without
//	modification, are permitted provided that the following conditions are met:
//	    * Redistributions of source code must retain the above copyright
//	      notice, this list of conditions and the following disclaimer.
//	    * Redistributions in binary form must reproduce the above copyright
//	      notice, this list of conditions and the following disclaimer in the
//	      documentation and/or other materials provided with the distribution.
//	    * Neither the name of That's IT, Mark Addinall, nor the
//	      names of its contributors may be used to endorse or promote products
//	      derived from this software without specific prior written permission.
//
//	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
//	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
//	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
//	DISCLAIMED. IN NO EVENT SHALL Mark Addinall BE LIABLE FOR ANY
//	DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
//	(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
//	LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
//	ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
//	(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
//	SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
//
//
//
//------------+-------------------------------+------------
// DATE       |    CHANGE                     |    WHO
//------------+-------------------------------+------------
// 22/03/2013 | Initial creation              |  MA
// 22/02/2016 | Adapt to new OOD model        |  MA
//------------+-------------------------------+------------
//
//
//-----------------
class ErrorLogger {
    
// Not only an error logger, but also a trace facility that
// is used during development, debugging of problems in a production
// environment, regression testing as a part of the suite of
// functions duringt the move from UAE (User Acceptance Environment)
// and production.
//
// The type of trace, and the verbosity to use is driven by a variable 
// in the Configuration Object.
//
// We use flat files for this purpose.  No sense in trying to put
// DEBUG statements in a DBMS that isn't working (I have seen that
// several time...).  The locations for these files are also
// controlled by properties in the Configuration object.
//
// The error directory and files should be CHMOD 640.
// 
// We keep the error files open during the run of the application.
// This is just to reduce FILE IO overhead.

private $configuration;         // Configuration passed in by REFERENCE
private $error_fd;              // error file handle
private $trace_fd;              // trace file handle
private $error_location;        // where are the log files
private $error_file;            // the error file
private $trace_file;            // the system message file


    //------------------------------------
    function __construct(Config $config) {
        $this->configuration = $config;                             // our PROTECTED copy of configuration
        
        // first dope the configuration file for nasty
        // re-direction strings.  If we find any, we 
        // just die with a message why.  This stops
        // FILE IO injection attacks.

        $error_location = $this->configuration->get_rootdir() .     // dope the configuration data
                          $this->configuration->get_errlog() ;      // and build the location string
        if ((strpos($error_location,"http"))    ||                  // no re-direction allowed, error logs
           (strpos($error_location, "https"))   ||                  // NEED to be on local host, and
           (strpos($error_location, "ftp"))     ||                  // in the root directory structure
           (strpos($error_location, "stdin"))   ||                  // and no redirection to SYSTEM
           (strpos($error_location, "stdout"))  ||                  // IO streams
           (strpos($error_location, ">"))       ||                  // no sneaky redirects
           (strpos($error_location, "<"))       ||
           (strpos($error_location, "|"))       ||                  // no pipes
           (strpos($error_location, "&"))) {                        // and nothing in the background theng you!
            die("Your configuration file example is illegal.  In your config.php file your error and " .
                "log locations parse to " . $error_location  .  "  You can not continue until this is " .
                "attended to.  Please see your System Administrator. \n\n ");
        }                                                           // something important here, first mention,
                                                                    // but we mention the subject in various locations.
                                                                    // a younger protoge asked "YUK!  Why didn't
                                                                    // you use a REGEX here".  Goog question.  Firstly,
                                                                    // REGEX LOOKS like it should be faster, but in
                                                                    // CPU cycles, it is a fair bit slower than
                                                                    // strpos.  Secondly, and more importantly,
                                                                    // since this application aims to be (and is)
                                                                    // machine agnostic, OS agnostic and DBMS
                                                                    // agnostic, we MUST bear in mind throughout the
                                                                    // development that the IBM mid range and the brand
                                                                    // spanking new BIG Iron still run the EBCDIC
                                                                    // character set.  The ordinal values are VERY
                                                                    // different.  Also, people tend to forget in
                                                                    // our modern world that the alphabets in the 
                                                                    // EBCDIC charater set are not contiguous.  So,
                                                                    // [a..j][p..x] ain't going to work.  Stuff like
                                                                    // this also fails.
                                                                    // $char = 'i'; $char++; echo $char;
                                                                    // It isn't going to be what you expect.
                                                                    // So no.  I am not scared of REGEX or tricky
                                                                    // Perl looking control loops.  This is the
                                                                    // MAIN reason moder teams have such a hard time
                                                                    // getting applications to work on big blue.
                                                                    // sort() does weird things as does date().
                                                                    // End of warning.
        $this->error_file = $error_location . "error_tit.log";      // OK, we hard code the ACTUAL names
        $this->trace_file = $error_location . "trace_tit.log";      // but leave the location up to the user
                                                                    // of this framework
        
        if (! ($this->error_fd = fopen($this->error_file, "a")))  {  // try and open the error file to append text 
            die("Could not open error file for writing. Blah blah.  Check permissions.<br> \n\n" .
                    $this->error_file);
        }                                                           // or die a horrible death.
                                                                    // Some would say a system should not die,
                                                                    // but attempt to continue in some way.
                                                                    // I think this is nonsense.  If the very first
                                                                    // FILE IO fails, something is VERY WRONG
                                                                    // up there^.  Fix it first.
        if  (! ($this->trace_fd = fopen($this->trace_file, "a"))) {  // try and open the trace file to append text 
            die("Could not open error file for writing.  Check permissions.");
        }                                                           // or die a horrible death.
        $this->trace("Session started normally. \n\n");             // say hello to the message file 
    }


    //---------------------------------------
    public function error($message, $fatal) {
 
        $bytes = fwrite($this->error_fd, $message . " - " . 
                    date(DATE_RFC822) . "\n\n");                    // attempt to write the trace
        if((!$bytes) || ($fatal))  {
            die("Fatal error encountered " .  $message);            // can not write an error file
        }                                                           // die a horrible death
                                                                    // write to console
        fflush($this->error_fd);                                    // do NOT cache write buffers
    }


    //-------------------------------
    public function trace($message) {
        if (! fwrite($this->trace_fd, $message . " - " . 
                    date(DATE_RFC822))) {                           // attempt to write the trace
            $this->error("Failed write to trace file " .
                                            $message, FALSE);       // can not write a trace
        }                                                           // flag this as a non FATAL error
        fflush($this->trace_fd);                                    // do NOT cache write buffers
    }
    // OK, so now we have finished our error/tracking routines,
    // we now pass on some functions onto the next object that
    // inherits us so that THAT object will have access to the
    // ACCESSOR methods of the Configuration Object, but we
    // now close off access to the MUTATOR methods of these low
    // level methods and properties.  As the application objects
    // get closer to the MMI part of the model, we will restrict
    // which ACCESSOR methods that will be available.  It is envisaged
    // that after the DBMS object, no ACCESSOR methods to
    // the low level configuration will be required.
    //
    // These are the methods we have access to that need to be bubbled up:
    //
    //  $this->get_user();
    //  $this->get_password();
    //  $this->get_database();
    //  $this->get_hostname();
    //  $this->get_dbtype();
    //  $this->get_stream();
    //  $this->get_rootdir();
    //  $this->get_theme();
    //  $this->get_errlog();
    //  $this->get_errlevel();
    //  $this->get_os();
    //
    // The DBMS object will require one access to the MUTATOR
    // set stream.  After a successful initialisation of the DBMS
    // (of any flavour), the connection routines return a STREAM
    // to the database for subsequent SQL DML
    //--------------------------
    public function get_user() {
    // bubble access to the user name upstream.
    // this is used for the DBMS logon
        return $this->configuration->get_user();
    }
    //--------------------------
    public function get_password() {
    // bubble access to the password upstream.
    // this is used for the DBMS logon
        return $this->configuration->get_password();
    }
    //--------------------------
    public function get_database() {
    // bubble access to the database name upstream.
    // i.e.  addinall_chameleon
    // this is used for the DBMS logon
        return $this->configuration->get_database();
    }
    //--------------------------
    public function get_hostname() {
    // bubble access to the hostname upstream.
    // i.e.  addinall_chameleon
    // this is used for the DBMS logon
        return $this->configuration->get_hostname();
    }
    //--------------------------
    public function get_dbtype() {
    // bubble access to the database TYPE upstream.
    // i.e. mySQL, ORACLE, PostgreSQL, DB2
    // this is used for the DBMS logon
        return $this->configuration->get_dbtype();
    }
    //--------------------------
    public function get_stream() {
    // bubble access to the database STREAM upstream.
    // this is empty at the moment, but this function
    // will be required in nearly all of the functions
    // in the DBMS
        return $this->configuration->stream;
    }
    //--------------------------
    public function set_stream($stream) {
    // the OPEN or CONNECT routines to the DBMS
    // will modify this property.  It is later used
    // for all DML reads and writes in the DBMS Object
        $this->configuration->set_stream($stream);
        $this->configuration->stream = $stream;
    }
    //--------------------------
    public function get_rootdir() {
    // bubble up the DOCUMENT ROOT for this implementation
    // of the application using this framework
        return $this->configuration->get_rootdir();
    }
    //--------------------------
    public function get_theme() {
    // bubble up the theme (CSS description)
    // to use.  this goes all the way up into
    // the HTML5 object to define the look and
    // feel of the MMI
        return $this->configuration->get_theme() ;
    }
    //--------------------------
    public function get_errlog() {
    // the location of the error file.  This is probably
    // not required up higher and will be removed during
    // unit testing I would think
        return $this->configuration->get_errlog() ;
    }
    //------------------------------
    public function get_errlevel() {
    // the error and trace reporting level.
    // this need to bubble all the way up
    // to the UI and associated methods.
        return $this->configuration->get_errlevel() ;
    }
    //-------------------------
    public function get_os() {
    // get the operating system
    // seldom used, but might have a use
    // in the DBMS.  The different DBMS
    // systems sometimes function differently
    // between operating systems.
    // Should they?  NO!  But they do indeed...
        return $this->configuration->get_os() ;
    }
}  // end of object
?>
