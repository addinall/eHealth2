<?php
// vim: set expandtab tabstop=4 shiftwidth=4 autoindent smartindent:
//---------------------------------------------------------
// CAPTAIN  SLOG
//---------------------------------------------------------
//
//  FILE:       config.php 
//  SYSTEM:     New Tools
//  AUTHOR:     Mark Addinall
//  DATE:       22/03/2013
//  SYNOPSIS:   "I put up a HTML website for myself in
//              2002.  I put about six hours worth of
//              work into it and never touched it again.
//              It looks and feels like crap, but I never
//              seemed to have the time nor inclination
//              to do anything about it!  So, 11 years on,
//              let's address this."
//
//              That was the first run of this file when I wrote
//              it in 2005!  This is the 2016 version.  Not changed
//              a great deal.
//
//              This is going to be a RESPONSIVE, lightweight
//              site based on CSS3 and HTML5.  I have been 
//              playing with this technology in the past,
//              so it is time to implement it in a BRAND
//              SPANKING NEW website of my very own!
//
//              This is one of the oldest files in my systems.
//              First made a debut in 1996 as a Perl file.
//
//-----------------------------------------------------------------------------
//  Copyright (c) 2013, Mark Addinall - That's IT - QLD
//  All rights reserved.
//
//  Redistribution and use in source and binary forms, with or without
//  modification, are permitted provided that the following conditions are met:
//      * Redistributions of source code must retain the above copyright
//        notice, this list of conditions and the following disclaimer.
//      * Redistributions in binary form must reproduce the above copyright
//        notice, this list of conditions and the following disclaimer in the
//        documentation and/or other materials provided with the distribution.
//      * Neither the name of That's IT, Mark Addinall, nor the
//        names of its contributors may be used to endorse or promote products
//        derived from this software without specific prior written permission.
//
//  THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
//  ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
//  WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
//  DISCLAIMED. IN NO EVENT SHALL Mark Addinall BE LIABLE FOR ANY
//  DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
//  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
//  LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
//  ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
//  (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
//  SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
//
//
//              
//------------+-------------------------------+------------
// DATE       |    CHANGE                     |    WHO
//------------+-------------------------------+------------
// 04/07/2005 | Initial creation              |  MA
// -----------+-------------------------------+------------
// 22/03/2013 | Creation again                |  MA
//------------+-------------------------------+------------
// 30/07/2014 | Added Content type static     |  MA



//------------
class Config {
// this started out in life as an array,
// VERY briefly, an XML document for a
// specific customer, then morphed into
// a JSON collation, which didn't really
// offer much over the origional array.
// So in line with the rest of the PURE
// OOD 2013 re-write, it is now a classic
// object.  All of the new objects in this 
// have accessors and mutators to re-write
// process properties.  I got a bit slack
// over the last few decades and relaxed this
// a little too much.
//
// If the security visibility looks a little 
// odd, this is the very first object that is
// created in the system.  The object method
// I use in this version is one of object
// chaining by REFERENCE rather than
// polymorphism by inheritance. That is, in
// these SYSTEM level of object where ONLY ONE
// instance is EVER going to be allowed
// during the course of the application run.
//
// So, the next object to be created after this
// will be the error logger which will accept this object
// by REFERENCE into its constructor.  So, sort of
// inheritance.
//
//
//      *****  THIS FILE SHOULD BE chmod() 400   *******
//
    
    
    
    
private $user;              // who am I?
private $password;          // database password for the CMS
private $database;          // database name, qualified
private $hostname;          // hostname, qualified
private $db_type;           // mySQL, MS-SQL, ORACLE, DB2, PostgreSQL so far (added Mongo) (added redis)
private $stream;            // this is a socket() pointer returned by the DBMS
private $root_dir;          // execution root directory
private $theme;             // CSS3 Skin to use.  This can change on the fly
private $error_log;         // where to stick the error logs
private $log_level;         // level of verbosity
private $google;            // google analystics code reference
private $os_type;           // which operating system for some low level functions

private $shared_secret;     // there is NO MUTATOR function for this string
                            // there is no ACCESSOR function for this string
                            // the only way to get access to it is by require(ing) it in your
                            // source code.

// password security for this system.
// ----------------------------------
// This config file is to be published chmod 400 so this initial shared secret should
// remain 'fairly' safe.  If it is hacked, then the hacker is a lot closer to your
// system than you want them to be already!
//
// I am going to assume that this system is going to be used by sensible people, and
// that HTTPS/TTL/SSL is going to be used over the wire therefore removing the concern
// with sending passwords in the 'plain' over the wire.  However, for reasons unknown,
// and for political purposes with the HR idiots, that may not be enuff.
//
    //---------------------------------------------------------------------------------------------------------
    function __construct($usr, $pass, $db, $host, $dbtype, $strm, $rdir, $css, $errl, $errlev, $google, $os) {
        
        // This shared secret is used on the client side to encrypt the password (and confirmation
        // oasswords) before launching text onto the bit of string.  This is a small part of our security
        // system, never the less, this shared secret should be changed on a regular basis, and the
        // ownership/priviliges of this file closely monitered.


$shared_secret = <<<EOQ
Now is the winter of our discontent
    Made glorious summer by this sun of York;
And all the clouds that lour'd upon our house
    In the deep bosom of the ocean buried.
    Now are our brows bound with victorious wreaths;
EOQ;


        $this->set_user($usr);
        $this->set_password($pass);
        $this->set_database($db);
        $this->set_hostname($host);
        $this->set_dbtype($dbtype);
        $this->set_stream($strm);
        $this->set_rootdir($rdir);
        $this->set_theme($css);
        $this->set_errlog($errl);
        $this->set_errlevel($errlev);
        $this->set_google($google);
        $this->set_os($os);
    }

    // ACCESSORS and MUTATORS
    // nothing special to look at

    //----------------------------------
    public function set_user($usr) {
        $this->user = $usr;
    }
    //-----------------------------
    public function get_user() {
        return $this->user; 
    }
    //--------------------------------------
    public function set_password($pass) {
        $this->password = $pass;
    }
    //---------------------------------
    public function get_password() {
        return $this->password; 
    }
    //------------------------------------
    public function set_database($db) {
        $this->database = $db;
    }
    //---------------------------------
    public function get_database() {
        return $this->database; 
    }
    //------------------------------------
    public function set_hostname($host) {
        $this->hostname = $host;
    }
    //---------------------------------
    public function get_hostname() {
        return $this->hostname; 
    }
    //------------------------------------
    public function set_dbtype($type) {
        $this->db_type = $type;
    }
    //-------------------------------
    public function get_dbtype() {
        return $this->db_type; 
    }
    //------------------------------------
    public function set_stream($strm) {
        $this->stream = $strm;
    }
    //-------------------------------
    public function get_stream() {
        return $this->stream; 
    }
    //------------------------------------
    public function set_rootdir($dir) {
        $this->root_dir = $dir;
    }
    //--------------------------------
    public function get_rootdir() {
        return $this->root_dir; 
    }
    //----------------------------------
    public function set_theme($css) {
        $this->theme = $css;
    }
    //------------------------------
    public function get_theme() {
        return $this->theme; 
    }
    //-----------------------------------
    public function set_errlog($log) {
        $this->error_log = $log;
    }
    //-------------------------------
    public function get_errlog() {
        return $this->error_log; 
    }
    //-------------------------------------
    public function set_errlevel($err) {
        $this->log_level = $err;
    }
    //---------------------------------
    public function get_errlevel() {
        return $this->log_level; 
    }
    //-------------------------------------
    public function set_google($google) {
        $this->google = $google;
    }
    //---------------------------------
    public function get_google() {
        return $this->google; 
    }
    //-------------------------------------
    public function set_os($os) {
        $this->os_type = $os;
    }
    //---------------------------------
    public function get_os() {
        return $this->os_type; 
    }


} // end of Config Object
//---------------------------------------
//
// fill in the values that reflect
// your site.  This information in
// PLAIN looks a little old fashioned,
// but storing all of this stuff in a database
// makes for difficult recovery, and it adds
// a deal of overhead to an otherwise lightweight
// process.  So the stuff lives in here.
// this file MUST be CHMOD 640, the security
// manager built into the application will
// bitch and stop otherwise.
//
// DON'T leave any of these out, or get the order wrong!
// It'll fall in a heap!  I thought about putting this stuff
// back into an associative array INSIDE the object, but
// hey, you can do JUST a LEETLE work!
// so make a copy of this first, comment it out, then
// add your stuff.
//
// The two entries that ask for directories:
// 1.  If you are on a windoze box LEAVE THE SLASHES ALONE!
// 2.  They are not allowed to contain the strings:
// 2.1 http
// 2.2 https
// 2.3 ftp
// 2.4 stdio
// 2.5 stderr
// 2.6 stdin
// 2.7 <
// 2.8 >
// 2.9 |
// 2.a &
//
// The system will spit and STOP.
//
// function __construct($usr, $pass, $db, $host, $dbtype, $strm, $rdir, $css, $errl, $errlev, $google, $os)
$configuration = New Config('root',                     // database username
                            'S0laris7.1',               // database password for the CMS
                            'casemix',                  // database name, qualified
                            'localhost',                // local host does it for 90% of installs
                            'mySQL',                    // mySQL, MS-SQL, ORACLE, DB2, PostgreSQL so far. Mongo added
                                                        // to use Amazon Web Services (the CLOUD).  These 'new'
                                                        // DBMSs are actually really old CISAM stuff.  Orange being
                                                        // a tricked up Berkeley database.  None of this 'ground
                                                        // breaking' Cloud stuff is really new.  The concepts and
                                                        // the tools date back to the 70s.  Added NONE, bit of an
                                                        // oversight on my part.
                                                        // Just added redis.  This one rocks! I just had a look
                                                        // inside a site (no name unfortunately) that is running
                                                        // a PHP5 app with a redis data structure that is serving
                                                        // 300,000 TPS!!!  Errrrkkkk!  Scaling up to 200,000,000 TPD.
                                                        // I was so impressed that I had to include this as my
                                                        // BIG DATA option.  It makes even ORACLE look slow
                                                        // and clumsy.
                                                        // Also added 'STATIC' so that this tool suite can be
                                                        // used to build a web app with no database driven
                                                        // CMS.  Dunno why anyone would want to, but it was
                                                        // a request.....
                                                        // just shoved in sqlite.  again, requested
                            '',                         // this is a socket() pointer returned by the DBMS
                            '',                         // execution root directory, TRAILING SLASH IMPORTANT!
                            'light',                    // CSS3 Skin to use.  This can change on the fly
                            'logs/',                    // where to stick the error logs. NB trailing slash
                            'DEBUG',                    // level of verbosity, DEBUG, WORDY, SPARSE, SILENT
                            'UA-12345-XX',              // google analytics code
                            'ubuntu');                  // operating system, ubuntu, deadrat, debian, windoze, bsd, solaris, zos
?>
