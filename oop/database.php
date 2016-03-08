<?php
// vim: set tabstop=4 shiftwidth=4 autoindent expandtab:
//---------------------------------------------------------
// CAPTAIN  SLOG
//---------------------------------------------------------
//
//  FILE:       database.php    
//  SYSTEM:     New Tools 2016
//  AUTHOR:     Mark Addinall
//  DATE:       16/04/2013 
//  SYNOPSIS:   This file contains the object that will 
//              encapsulate database methods and
//              properties.
//
//              This file has seen some major re-use over
//              the years.  Apart from the quicktools suite,
//              this file has been part of the Chameleon CMS
//              and several specific applications, ACCLOUD
//              accounting, What's Mine (Mining industry ERP
//              and assett management) and BetMe, a horce racing
//              statistical data gathering and reporting application
//              to name a few.
//
//              This incarnation has made it into a set of Web 2.0 tools that
//              make up a FRAMEWORK for developing RESPONSIVE web pages over
//              a number of different devices.  This file/object has become more
//              generic and will support the Web 2.0 applications and will also
//              continue to support the CMS, Chameleon.
//
//              It has ALWAYS been the philosophy of this system that it be
//                  -   operating system agnostic, and,
//                  -   DBMS agnostic
//
//              That is, the code lying closer to the application layer and
//              IN the application layer NEVER changes regardless of OS or
//              type of DBMS.  Choosing is as simple as changing a line in the
//              one and only little configuration file.
//
//              However, this is a computer program, not magic.  If you want to run this
//              on a DB2 database, you need to build PHP and DB2 with the correct extentions,
//              ditto postgreSQL and ORACLE.  mySQL is built into the PHP distributions
//              as that has been the usual suspect over the history of WAMP-LAMP
//              development.
//
//              Just a note on noSQL and 'current' technologies.
//              We really have travelled the full circle.
//              "Hey instead of distributed processing, we will just build a BIG fucking
//              room, stuff it full of mainframes, run up a VMS - TSO set of control,
//              and SELL CPU cycles, Storage and network bandwidth as required!  What
//              a NEAT NEW IDEA!  VERY SIMILAR to the 1960s-70s model.  In fact, identical.
//              Let's think up some new trendy name for a VERY OLD computational model.
//                  SaaS
//                  PaaS
//                  'The CLOUD'
//              That sounds stupid enough to suck in the script kiddies, the HR department,
//              low life twonks, anything in the guvmint, and assorted other
//              wannabees... 
//
//              "I had another good idea.  Let's RE-INVENT ISAM databases, speed them up
//              using tried and tested B+ Tree traversal routines through multiple indices
//              (well documented in 'The Art of Computer Programming.  Vols 1,2 & 3.
//              Knuth - 1966.
//              Volume 1 – Fundamental Algorithms
//                  Chapter 2 – Information Structures
//                      2.3. Trees
//                          2.3.1. Traversing Binary Trees
//                          2.3.2. Binary Tree Representation of Trees
//                          2.3.3. Other Representations of Trees
//                          2.3.4. Basic Mathematical Properties of Trees
//                              2.3.4.1. Free trees
//                              2.3.4.2. Oriented trees
//                              2.3.4.3. The "infinity lemma"
//                              2.3.4.4. Enumeration of trees
//                              2.3.4.5. Path length
//                              2.3.4.6. History and bibliography
//                          2.3.5. Lists and Garbage Collection
//                      2.4. Multilinked Structures
//                      2.5. Dynamic Storage Allocation
//
//                      Then we can write the API in some obscure implementation of a mixture
//                      between B and LISP and call the thing
//
//                      'noSQL' - 'State of the Art' Database technology!!
//
//                      What a HOOT!
//
//              Anyway.  It is implemented.  I am sticking an SQL parser into the API.
//
//-----------------------------------------------------------------------------
//  Copyright (c) 2006..2013,2014 Mark Addinall - That's IT - QLD
//  Copyright (c) 2016 Best Practice Australia 
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
// -------------------------------------------------------
//
//------------+-------------------------------+------------
// DATE       |    CHANGE                     |    WHO
//------------+-------------------------------+------------
// 02/10/2005 | Initial creation Toolset V1.0 |  MA
// 29/04/2007 | Adept to Telstra NOC          |  MA
// 12/08/2009 | Complete re-write v2.x own use|  MA
// 18/02/2010 | Re-write CITEC (unfinished)   |  MA
// 12/02/2012 | Re-write v3.x new object model|  MA
// 16/04/2013 | Re-write v4 new object model  |  MA
// 02/05/2013 | Added support for Mongo noSQL |  MA
// 08/05/2013 | Added an SQL Parser for Mongo |  MA
// 11/04/2014 | Back on the job.              |  MA
// 11/06/2014 | Back in. Distracted by work.  |  MA
// 29/07/2014 | Added Redis support, cache    |  MA
// 22/02/2016 | Adapt to new SPA systems      |  MA
// 26/02/2016 | FINALLY implement PDO         |  MA
//------------+-------------------------------+------------



require_once('parser.php');         // SQL front end for noSQL
                                    // I know, sounds dumb.  I do
                                    // have a reason.  It makes the calls
                                    // to some pre-defined procedures consistant
                                    // in the upper reaches of the API
//
//-----------------
class ResultArray {                 // little object looks kinda weird, it
                                    // serving as a container for an array
                                    // of SQL results.
//----------------------
private $table = array();           // table of SQL results
                       
    //----------------------
    function __construct() {
        // nothing to do
    }
    //----------------------------
    public function push($value) {
        $this->table[] = $value;    // PUSH onto stack.  PHP looks after
    }                               // the memory management

    //---------------------------
    public function get_stack() {   // return it to functions that can not digest objects
        return $this->table;        // like json_encode.  Dumb arse coders
    }

} // end ResultArray



//----------
class DBMS {
// This object opens, closes, manages, manipulates
// and reports on our database.  This is pointed
// only at mySQL at the moment.  
// This level of abstraction will allow us to use 
// ORACLE, DB2, mySQL and Postgress drivers easily
// by the application programmers.
//
// Going to add a noSQL database in a later version
// and also add support for MS-SQL.
//
// I had a couple of requests from people wanting
// to build 'cloudy' applications so I decided
// to implement support for Mongo noSQL in this
// version.  Might as well since it is a re-development
// pretty much from concepts.  For the sake of this
// initial development, all DB instances for both development
// and testing will be v2.4.3.
//
// This now begs the question "do we translate the JSON that
// comes back from Mongo into a traditional ROWTYPE% OBJECT
// or do we translate the results from the traditional engines
// (SQL) into JSON?  It has to be one or the other.
//
// Investigating persistant LOCAL databases on
// mobile devices.  The implementation of this
// technology is a few minor versions in the future.
// Possibly December 2013. -  Done.
//
private     $alive;             // is the database up?
private     $result;            // the result of an SQL query
private     $user;              // these are all of the DB connect
private     $database;          // variables
private     $db_type;           // the needs of different databases
private     $password;          // change so on occasion some of
private     $stream;            // this will remain empty for the
private     $hostname;          // duration of the application
private     $mongo_fp;          // no SQL has TWO streams associated with IO
private     $parser;            // and an SQL parser fron end
private     $db_cache;          // Redis can eithe be implemented
                                // stand alone (not very interesting)
                                // or as a high speed chache in between
                                // the application API and the RDBMS of
                                // choice.  At the moment, this varliable
                                // is either 'Redis' or 'DONE'.
public      $log_config;        // our log file configuration

    //------------------------------------------
    function __construct(ErrorLogger $logger) {
        $this->log_config = $logger;                            // get our own copy of the data logger.
                                                                // this is passed as a REFERENCE variable,
                                                                // and it contains the Configuration Object
                                                                //
                                                                // In this the latest re-write, the Configuration
                                                                // Object replaces the tried and tested array
                                                                // that has been flung around these systems
                                                                // for so many years!  In the v4.x, I have adopted
                                                                // a fully OOD/OOP paradigm.  The confuguration
                                                                // methods and properties are percolated up
                                                                // through several object APIs, each addressing
                                                                // application security at the appropriate level.
                                                                //
                                                                // At this stage, all of the  MUTATORS
                                                                // have been closed off with the exception of
                                                                // set_stream.
                                                                //
                                                                // The commonly used database variables will be moved
                                                                // into Private properties of this object so that
                                                                // subsequent DBMS access atoms will not require
                                                                // a call to a method in a related object.  Since
                                                                // after a successful CONNECT is established,
                                                                // 90% of the  properties are no
                                                                // longer of interest to the application.
        $this->user     = $this->log_config->get_user();        // make the copies into private static
        $this->database = $this->log_config->get_database();    // properties
        $this->db_type  = $this->log_config->get_dbtype();
        $this->password = $this->log_config->get_password();
        $this->hostname = $this->log_config->get_hostname();

        if ($this->db_type == 'mySQL') {
            $this->hostname = 'p:' . $this->hostname;           // establish a POOL of persistant connections
                                                                // in server user space saving the overhead of
                                                                // new connections each AJAX call. In this model
                                                                // if a SPA using deep binding and AJaX this CODE
                                                                // is not persistant as are the newer playtime
                                                                // Ruby and Python offerings, this code is invoked
                                                                // under the multi-threaded architecture of Apache2
                                                                // when required, and persists for one atomic
                                                                // transaction.  Keeping a pool of open STREAMS to
                                                                // the RDBMS greatly reduces the startup overhead
                                                                // per process.
        }

        $this->stream   = 0;                                    // stream will come back from the DBMS
        $this->result   = 0;                                    // result returned from stream
                                                                // both database objects
        //
                                                                // if a connection is made, we hope!
        if ($this->db_type == 'DB2') {                          // are we on a big blue macine?
            require_once('../lib/prepare.inc');                 // this is required by the IBM DB2 API
        }                                                       // it does not do anything if we are not
                                                                // a DB2 implementation.  Sets two constants
       if ($this->db_type == 'Mongo') {                         // this will apply to other flavours of
           require_once('parser.php');                          // noSQL.  As the syntax to generate queries
           $this->parser = new Parser();                        // on a noSQL database is VERY different to
       }                                                        // that of our other DBMS systems, I decided
                                                                // to write a small SQL subset compiler/
                                                                // interpreter that will parse SQL queries
                                                                // into Mongo system calls via an iCode
                                                                // stack.  This seems to be the best way
                                                                // of allowing an application coder the
                                                                // facility to use "Cloud" based (local or
                                                                // otherwise) tools such as Mongo noSQL without
                                                                // going to the trouble of learning the somewhat
                                                                // tortured syntax.  After all, one of the
                                                                // aims of a toolset/Framework is to provide
                                                                // abstraction from the DBMS.  We do it between
                                                                // RDBMS systems, so we just extend this
        $this->connect() ;                                      // try to connect to the database
                                                                // this level of abstraction will allow for
                                                                // mySQL, ORACLE, PostgreSQL or DB2 databases
                                                                // without changing the application code
    } // constructor

    //------------------------
    private function connect() {
    
            // connect to the RDMS first 
            $this->alive = FALSE ;                              // start out dead
            //------------------------------------------------------------------------------------------

            if ($this->db_type == 'mySQL') {                    // cater for several types of database
                $this->stream =                                 // the stream used to be a socket
                new mysqli( $this->hostname,                    // since the re-write it is now an      
                            $this->user,                        // mysqli database object.  Perhaps on
                            $this->password,                    // the TO DO list is including mySQL
                            $this->database);                   // PDO sopprt.  Perhaps....
                if (!$this->stream) {
                    $this->log_config->error('Database not started : '
                        . $stream->connect_error, TRUE);        // fail? quit with the error
                }                                               // I like the msqli OOP implementation so
                                                                // we are using that API
            }                                                   // end of mySQL
                                                                // I implemented PDO in Feb 2016.  I decided
                                                                // to keep using mysqli as 70%+ of the web apps
                                                                // use mysql as the RDMS and mysqli is very
                                                                // well supported.
            //------------------------------------------------------------------------------------------
            if ($this->db_type == 'MSSQL') {                    // have to include Microsoft 
                $this->stream =                                 // the stream used to be a socket
                new mssql_connect($this->hostname,              // since the re-write it is now an      
                            $this->user,                        // mysqli database object.  Perhaps on
                            $this->password);                   // the TO DO list is including mySQL
                if (!$this->stream) {
                    $this->log_config->error('Database not started : '
                        . $stream->connect_error, TRUE);        // fail? quit with the error
                }                                               // I like the msqli OOP implementation so
                                                                // we are using that API
            }                                                   // end of mySQL
            //------------------------------------------------------------------------------------------
            if ($this->db_type == 'sqlite') {                   // some people want to use it.
                $this->stream =                                 // God only knows why.  Ex Ruby and
                new sql3pdo_connect($this->hostname,            // Pythonese people who know no better
                            $this->user,                        // I assume.
                            $this->password);                   //
                if (!$this->stream) {
                    $this->log_config->error('Database not started : '
                        . $stream->connect_error, TRUE);        //
                }                                               //
                                                                //
            }                                                   //
            //------------------------------------------------------------------------------------------
            //------------------------------------------------------------------------------------------
            if ($this->db_type == 'Mongo') {                    // cater for trendy new database
                $this->mongo_stream =                           // looks a lot like old CISAM to me...
                    new mongo ( $this->hostname);               // wrapped up in an object.  I seem 
                if (!$this->mongo_fp) {
                    $this->log_config->error('Database not started : New mongo failed', TRUE);
                }                                              
                                                                // Mongo is a two part connect
                                                                // like the others USED to be.
                                                                // BAH!  Just as it all got simpler!
                $this->stream = $this->mongo_fp->{$this->database};
                if (!$this->stream) {
                    $this->log_config->error('Database not started : Mongo DBNAME failed', TRUE);
                }                                              
            }                                                   // end of  Mongo
            //------------------------------------------------------------------------------------------
            if (($this->db_type == 'Redis') || ($this->db_cache == 'Redis')){ 
               require "predis/autoload.php";
                // I added Redis July 2013 after seeing 
                // a PHP5 app on a Redis data structure
                // churning at 200,000 TPS and scaling
                // up to 300,000,000 TPD!  So I decided
                // that Redis is my choise for BIG DATA.
                // Redis is an in-memory key=>value
                // DB that usually acts as a cache between
                // PHP and one of the other RDBMs' here,
                // although like Berkely DB, it CAN be 
                // used stand alone.
       
                PredisAutoloader::register();
 
                // since we connect to default setting localhost
                // and 6379 port there is no need for extra
                // configuration. If not then you can specify the
                // scheme, host and port to connect as an array
                // to the constructor.
                try {
                    $redis = new PredisClient();
                }
                catch (Exception $e) {
                        $this->log_config->error('Database not started : REDIS failed   :' . $e->getMessage(), TRUE);
                }                                              
                $this->stream = $redis;
                if ($this->db_cache == "Redis") { 
                    $this->db_cache = "DONE";
                    $this->connect() ;                          // recurse and start the RDBMS now
                }
            }
            //------------------------------------------------------------------------------------------
            else if ($this->db_type == 'postgreSQL') {          // horrid old fashioned wreck of a database
                $this->stream = 
                    pg_connect("host=$this->hostname dbname=$this->database user=$this->user password=$this->password");
                if (!$this->stream) {
                    $this->log_config->error('Could not connect: ' . 
                                                    pg_last_error(), TRUE);
                }
            }                                                   // end of postgreSQL
            //-------------------------------------------------------------------------------------------
            else if ($this->db_type == 'ORACLE') {              // database for grown-ups ;-) 
            
                $this->stream =                         
                    oci_connect($this->user,            
                                $this->password,    
                                $this->hostname); 
                if (!$this->stream) {
                    $message = oci_error();
                    $this->log_config->error('Database not started :    '. 
                                                $message['message'], TRUE );
                }
            }                                                   // end of ORACLE
            //--------------------------------------------------------------------------------------------
            else if ($this->db_type == 'DB2') {                 // The IBM offering.  This is important
                                                                // as mainframes haven't gone away.
                                                                // IBM has Linux and DB2 running native
                                                                // in the zEnterprize models and implementing
                                                                // dozens to tens of thousands virtual
                                                                // hosts on the mainframe.
                                                                // Important to note for we coders.  A number
                                                                // of centuries ago, an organisation hired me
                                                                // as a Java Guru.  When I asked WHAT it was they
                                                                // wanted to do, the answer was "develop a web
                                                                // interface to the existing payroll.  They spent
                                                                // half a million bucks and 3/4 of a year trying
                                                                // to do it in Java, J2EE, Netbeans, Weblogic,
                                                                // etc., etc., and still couldn't get it.
                                                                // I asked them if I could show them a prototype
                                                                // in PHP (a language they hadn't really heard of).
                                                                // I stuck a Zend PHP server up into the iSeries
                                                                // and had prototype web pages chewing DB2 data
                                                                // in four days.  From scratch.  They were a little
                                                                // amazed to say the least.  So remember, the BIG
                                                                // COMPLEX asks, may be simpler than people expect.
                $this->stream =                     
                    db2_connect($this->database,                // DB2 used to be a little little weird, better now
                                $this->user,                    // user in catalog 
                                $this->password);               // password in plain 
                if (! $this->stream) {
                    $this->log_config->error('Database not started :    ' .
                                                db2_con_errormsg(), TRUE);  
                }
            }                                                   // end of DB2
            //----------------------------------------------------------------------------------------------
            $this->alive = TRUE;                                // if we got here one of the databases is UP!
    } // end of connect() 


    //--------------------------------------
    public function execute($sql) {

    // just execute SQL Statement
    // given the nature of web applications and the
    // data structure in content management systems
    // (well mine anyway) the SQL statements tend to be
    // simplistic in nature.  As a result, placeholders,
    // least cost maps etc. are dutifully ignored.
    //
    // When this model was first written in the dim dark
    // ages, the syntax for connections and executions
    // between the different DBMS engines was VERY VERY
    // different.  Swathes of different looking coding
    // to get the basics established.  Now in 2013, they
    // are all looking very similar (with the exceptions
    // of the noSQL crowd, they have taken us back to
    // the 1980s CISAM model).  So similar it is tempting
    // to write a function aliasing API.  But, we might look
    // at that in the future.  Right now, the code size
    // has dropped significantly, and the code is easy to
    // read.
       

        if ($this->db_type == 'mySQL') {                                // mySQL sorta works as one would
            $this->result = $this->stream->query($sql);                 // expect from a modern database.
            $this->log_config->trace('SQL == ' . $sql  . ' ==  ' );
            if (!$this->result) {                                       // it gets a STREAM from a CONNECT
                $this->log_config->error('Query failed: ' . $sql . ' ' .// function (now it is an object that
                    $this->stream->errno . "-" . 
                        $this->stream->error . ' ',FALSE);              // encapsulates the STREAM, and queries
            }                                                           // to that handle or STREAM, therebye a
        }                                                               // script can open more than one database.
        //---------------------------------------------------------
        if ($this->db_type == 'MSSQL') {                                // the Microsoft DBMS has got better 
            $result = $this->stream->mssql_query($sql);                 // over the years.
            if (!$result) {                                             // it gets a STREAM from a CONNECT
                $this->log_config->error('Query failed: ' . $sql . ' ' .// function (now it is an object that
                    $mssql->errno . "-" . $mssql->error . ' ',TRUE);    // encapsulates the STREAM, and queries
            }                                                           // to that handle or STREAM, therebye a
        }                                                               // script can open more than one database.
        //---------------------------------------------------------
        else if ($this->db_type == 'postgreSQL') {                      // postgreSQL is similar, although it
            $result = pg_query($sql);                                   // ignores the STREAM received from
            if (!$result) {                                             // CONNECT meaning that any one script
                $this->log_config->error('Query failed: ' . $sql .      // can only have one database open at
                                        ' : ' . pg_last_error(), TRUE); // one time.  Usually not a big deal
            }                                                           // for web sites.
        }
        //----------------------------------------------------------
        else if ($this->db_type == 'Mongo') {                           // Mongo LOOKS similar on the outside.
            $result = 'To be announced';                                // I need to test the overall structure first
            if (!$result) {                                             // Mongo is flavour of the month so
                $this->log_config->error('Query failed: ' . $sql .      // we are here a little sooner than I
                                        ' : ' . pg_last_error(), TRUE); // expected.
            }                                                         
        }
        //----------------------------------------------------------
        else if ($this->db_type == 'Redis') {                           // I added Redis July 2013 after seeing 
            $result = 'To be announced';                                // a PHP5 app on a Redis data structure
                                                                        // churning at 200,000 TPS and scaling
                                                                        // up to 300,000,000 TPD!  So I decided
            if (!$result) {                                             // that Redis is my choise for BIG DATA.
                $this->log_config->error('Query failed: ' . $sql .      // 
                                        ' : ' . pg_last_error(), TRUE); // Redis is an in-memory key=>value
                                                                        // DB that usually acts as a cache between
                                                                        // PHP and one of the other RDBMs' here,
                                                                        // although like Berkely DB, it CAN be 
                                                                        // used stand alone.
            }                                                           
        }
        //----------------------------------------------------------
        else if ($this->db_type == 'ORACLE') {              
            $plan = oci_parse($this->stream, $sql);                     // OK, ORACLE is a little different            
            if (!$plan) {                                               // to mySQL and postgreSQL again. It has
                $error = oci_error($this->stream);                      // a two part execution process that
                $this->log_config->error('Query not PARSED: ' .         // produces iCode known in ORACLE
                                            $error['message'], TRUE);   // speak as a PLAN. What is does in the
            }                                                           // PHP environment is to set up the
            $result = oci_execute($plan);                               // statement for BIND variables.
            if (!$result) {                                             // It DOES NOT VALIDATE SQL!!  It
                $error = oci_error($plan);                              // SHOULD!  But this is an OLD OLD
                $this->log_config->error('Query not EXECUTED: ' .       // leftover from CIA Ada and the cost
                                            $error['message'], TRUE);   // of CPU cycles!
            }
        }
        //-------------------------------------------------------------
        else if ($this->db_type == 'DB2') {                             // DB2 operation is very similar to
            $result = db2_exec($this->stream, $sql,                     // that of mySQL.  It uses the CONNECT
                                    array('cursor' => DB2_SCROLLABLE)); // STREAM and multiple database can be
            if (!$result) {                                             // open at the same time.  For QUERY
                $this->log_config->error('Query failed: ' . $sql . ' ' .// statements, the DB2_SCROLLABLE is important,
                                            db2_stmt_errormsg(), TRUE); // otherwise the return set will be empty
            }                                                           // Trap for young player!  ;-)
        }
    } // execute 



    //-----------------------
    public function fetch() {
    // fetch the TOP row array from the returned SELECT
    // call.
    // this is normally called when only SELECTing one
    // element from the database ie: one particular 
    // UNIQUE row in a table.
        if ($this->db_type == 'mySQL') {
            return($this->result->fetch_assoc());
        }
        if ($this->db_type == 'MSSQL') {
        }
        else if ($this->db_type == 'Mongo') {
        }
        else if ($this->db_type == 'Redis`') {
        }
        else if ($this->db_type == 'postgreSQL') {
        }
        else if ($this->db_type == 'ORACLE') {
        }
        else if ($this->db_type == 'DB2') {
        }
    } //  fetch



    //---------------------------
    public function fetch_all() {
    // fetch the entire array from the returned SELECT call.
    // this is normally called when SELECTing a list of 
    // elements from the database and you want the results
    // back quick smart.
    //
    // 'SELECT account_number,name,postcode from CLIENT'
    //
    // $table_list = $database->fetch_all();
        $stack = new ResultArray();                            // container to send upstairs

        if ($this->db_type == 'mySQL') {
            for ($i=0; $i < $this->result->num_rows; $i++) {
                $data = $this->fetch();
                $stack->push($data);
            }
        return($stack);
        }
        else if ($this->db_type == 'MSSQL') {
        }
        else if ($this->db_type == 'Mongo') {
        }
        else if ($this->db_type == 'Redis`') {
        }
        else if ($this->db_type == 'postgreSQL') {
        }
        else if ($this->db_type == 'ORACLE') {
        }
        else if ($this->db_type == 'DB2') {
        }
        return $result;
    } //  fetch
    //--------------------------------------
    public function close() {

        // some databases need to be explicitly closed to
        // release several thousand lock files cluttering
        // up the OS and file systems in general.
        // ORACLE and DB2 frinstance.  If you don't clean up,
        // the never work again!

        if ($this->db_type == 'mySQL') {
        }
        if ($this->db_type == 'MSSQL') {
        }
        else if ($this->db_type == 'Mongo') {
        }
        else if ($this->db_type == 'Redis`') {
        }
        else if ($this->db_type == 'postgreSQL') {
        }
        else if ($this->db_type == 'ORACLE') {
        }
        else if ($this->db_type == 'DB2') {
        }
    } // close  
    
    //--------------------------    
    public function is_alive() {
    // NUMBER FIVE IS ALIVE!!!
    
        return $this->alive ;
    }
} // class DBMS 
?>
