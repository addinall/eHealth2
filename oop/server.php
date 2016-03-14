<?php
// vim: set tabstop=4 shiftwidth=4 autoindent expandtab:
//---------------------------------------------------------
// CAPTAIN  SLOG
//---------------------------------------------------------
//
//  FILE:       server.php
//  SYSTEM:     2016 full stack tool set 
//  AUTHOR:     Mark Addinall
//  DATE:       22/02/2016
//  SYNOPSIS:   This program will form the RESTful server
//              handleing data to and from the client.
//
//              Instead of _GET and _POST we will use the
//              super GLOBAL _REQUEST to accept data into this
//              API.  This allows us to consume data with
//              loose security as well as high security packets.
//             
//              Unlike the one line servers popular in REST for DUMMIES
//              books, this one takes into account different levels
//              of security requirements, different data format
//              requirements of the consumer of web services and
//              database persistance.  Hence it looks rather busy.
//
//              Now, another thing not mentioned inn the Dummies literature
//              is that of complex queries.  If all we ever do is
//                  SELECT * from CONTACTS
//              then all would be fine in RESTful paradise.
//              The wurld is a little more complex than that, so we
//              need a strategy to enable complex RDBMS queries.
//              Four spring to mind:
//
//              POST to a filtered endpoint /database_object/filter
//              along with the filter
//              { patientNo:1993, inState:QLD, isAlive:1 }
//
//
//              Build up the GET query
//              GET /contacts/?survey_id=1993&state=QLD&is_alive=1
//
//              that is better.  How we handle JOINs, UNIQUE, LIMIT, ORDER BY
//              is not all together clear in a RESTy wourld.
//
//              have an endpoint that executes SQL
//              This is by far he easiest and most flexible.  Tradiationalists,
//              script kiddies and Scala coders will gnash teeth and moan
//              at how un-RESTful we are.  There is also some security
//              issues with this approach.
//
//              Have some STORED PROCEDURES and FUNCTIONS described in the
//              database itself and CALL these procedures with a PREPARED
//              SQL ARGUMENT GROUP.
//
//              CREATE PROCEDURE get_contacts(IN VARCHAR who, ..., IN VARCHAR CONSTRAINTS)
//                  BEGIN
//                      QUERY = PREPARE(....
//                      EXEC(QUERY)
//                  END
//
//              It will work.  A little hard for those who are not DBAs to understand.
//              Not a common skill set, and of course, the code is hidden in the data
//              so greeping files for possible bugs will lead to no-where.
//
//              I am open to suggestions people.
//
//              I spent Sunday researching this.  It is also part of my current post-grad study
//              "Big Data and Relational Database Systems" so I killed two chooks with one stone.
//
//              As I suspected, REST and CRUD haven't progressed very far in the last two years.
//              As the ORBs in CORBA and CORBA like APIs became too complex for human beans to
//              manage, REST/CRUD is turning out to be too simplistic to make it in the (TM) "Real World".
//          
//              I kept getting led to the ODATA definitions and API, and it looks like they have
//              come to the same conclusion as me.  viz:
//
//              Step 6: Invoking a function
//
//              In RESTful APIs, there can be some custom operations that contain complicated logic and can be 
//              frequently used. For that purpose, OData supports defining functions and actions to represent such 
//              operations. They are also resources themselves and can be bound to existing resources. 
//              After having explored the TripPin OData service, Russell finds out that it has a function called 
//              GetInvolvedPeople from which he can find out the involved people of a specific trip. 
//              He invokes the function to find out who else other than him and Lewis goes to that trip in the U.S.
//
//              OK.  This is defining complex procedures that are going to be required and making them visible
//              or semi-visible to consumers of the web service and consumers of the data.
//
//              This is not REST/CRUD.  It is good ol' fashioned RPC.  Something I am familiar with.
//              Which is just as well, as this code (some of it) usedta belong to my RPC server
//              class!
//
//              I have decided that we shall accept packets via POST only for now.  We will
//              still implement the super GLOBAL just in casr I change my mind at a later stage.



            
require_once('config.php');                                    // this has some very basic primitives
                                                               // described.  Name of the database,
                                                               // base URL, type of database and other
                                                               // runtime options.  the file is simple,
                                                               // and this is the ONLY configuration
                                                               // file we use in this framework.
                                                               // ALL of the information is encapsulated
                                                               // into the methods and properties of ONE
                                                               // Configuration object.  We don't have many
                                                               // configuration options.  KISS.
require_once('error_logger.php');                              // error logging object
require_once('database.php');                                  // database connectivity object




//---------------
class restServer {

    private $database;

    private $HTTPS_required             = FALSE;                // Define whether an HTTPS connection is required

                                                                    
    private $authentication_required    = FALSE;                // Define whether user authentication is required;

                                                                // Define API response codes and their related HTTP response

    private $format;                                            // JSON, XML or HTTP encoded payload

    private $api_response_code = array(
            0 => array('HTTP Response' => 400, 'Message' => 'Unknown Error'),
            1 => array('HTTP Response' => 200, 'Message' => 'Success'),
            2 => array('HTTP Response' => 403, 'Message' => 'HTTPS Required'),
            3 => array('HTTP Response' => 401, 'Message' => 'Authentication Required'),
            4 => array('HTTP Response' => 401, 'Message' => 'Authentication Failed'),
            5 => array('HTTP Response' => 404, 'Message' => 'Invalid Request'),
            6 => array('HTTP Response' => 400, 'Message' => 'Invalid Response Format')
            );


    private $http_response_code = array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found'
            );
   
    private $response = array();
 

    //------------------------------
    function __construct(DBMS $db) {

        $this->database         = $db;                                                  // pull in our database.  It is alive
                                                                                        // at this point
        if (isset($_REQUEST['format'])) {
            $this->format                     = $_REQUEST['format'];
        } else {
            $this->format = 'JSON';                                                     // default to JSON
        }

        $this->secure_wire();                                                           // check HTTPS status and availability
                                                                                        // bail if not secure

        $this->authenticate();                                                          // check role security required
                                                                                        // authenticate against a shared secret
                                                                                        // bail if not secure

        $this->response['code']   = 0;                                                  // Set default HTTP response of 'ok'
        $this->response['status'] = 200;
        $this->response['data']   = NULL;


        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->crud   = $_SERVER['REQUEST_METHOD'];                                 // since we are implementing CRUD as REST, we should
        }                                                                               // follow the convention of using the HTTP REQUEST
                                                                                        // types as indicative of the database function to be
                                                                                        // carried out.  For the life of me I know not why.
                                                                                        // I am sure that was not in the origional thinking
                                                                                        // when designing REST transactions.
        // start with our basic CRUD/REST response functions.  As I have pointed
        // out, in a review of recent published literature regarding the implementation
        // of CRUD/REST, the simple functions sre not going to cover a LOT of
        // what we want to do in out database.  Certainly not with any
        // advanced on-screen reporting.  As we have normalised our data
        // schema, and included foreign keys into child relationships
        // as attributes, then reporting is going to use some amount of set
        // mathematics to run.
        //
        // This will be covered either by using our existing
        //      database->execute 'SELECT name from participants
        //                                  WHERE alive = 1
        //                                      LEFT JOIN
        //                                          (SELECT address, town, state, country from addresses 
        //                                              WHERE
        //                                                  addresses.alive = 1)
        //
        // or stuff like that -
        // OR
        // We can code up a series of CURSORS that we KNOW will be used for a standard SERIES
        // or SET of reports.
        //

        if (isset($_REQUEST['exec'])) {                 // before we drop into CRUDDiness, handle
            $SQL = $_REQUEST['exec'];                   // the special cases of EXECUTE IMMEDIATE
            if ($this->safe_parse($SQL)) {              // and RPC requests, so here, if EXEC is
                                                        // set, and we deem it to be non-malicious
                                                        // via lexical analysis, then go and do
                                                        // the thing .....
                $this->database->execute($SQL);         // well?  This eithe dies or comes back
                                                        // it CAN come back with an empty SET
                                                        // that is an SEP.

            }
        }



        switch($this->crud) {
            case 'GET':
                //  retrieval of tuple
                break;

            case 'LIST':
                //  retrieval of tuple(s)
                //  $this->database->log_config->trace("GOING TO SELECT .......");
                //  $this->database->execute('SELECT * from em_patients LIMIT 10;');
                //  $this->database->log_config->trace("AFTER SELECT, GOING TO FETCH.......");
                //  $db_result = $this->database->fetch_all();
                //  $this->response['data'] = $db_result->get_stack();
                //  $this->database->log_config->trace("FETCHED ........"  . json_encode( $this->response['data']));
                  break;

            case 'CREATE':
                // create new tuple(s)
                break;

            case 'UPDATE':
                // update tuple
                break;
    
            case 'DELETE':
                // delete tuple()
                break;

            default:
                $this->database->log_config->error("Something has gone badly wrong in server.php", TRUE);
                // TRUE is a FATAL ERROR.  ABEND with errno, errmsg
        }

        $this->deliver_response();                                                      // Return Response to browser. This will exit the script.
    }   // end of contructor, and essentially this instance


    //----------------------
    function secure_wire() {


        if (isset($_REQUEST['https']) && $_REQUEST['https']) {                          // if it has been set to TRUE ...
            $this->HTTPS_required = TRUE;
        }

        if( $this->HTTPS_required && $_SERVER['HTTPS'] != 'on' ) {                      // Optionally require connections to be made via HTTPS
            $this->response['code']   = 2;
            $this->response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
            $this->response['data']   = $api_response_code[ $response['code'] ]['Message'];
    
            $this->deliver_response();                                                  // Return Response to browser. This will exit the script.
        }

    }


    //---------------------
    function safe_parse() {
        return TRUE;
    }


    //-----------------------
    function authenticate() {

        
        if (isset($_REQUEST['authenticate']) && $_REQUEST['authenticate']) {            // if it has been set to TRUE ...
            $this->authentication_required = TRUE;                                      // needs a password from somewhere
        }


        if( $this->authentication_required ) {                                          // Optionally require user authentication
            if( empty($_REQUEST['username']) || empty($_REQUEST['password']) ){
                $this->response['code'] = 3;
                $this->response['status'] = $api_response_code[ $response['code'] ]['HTTP Response'];
                $this->response['data'] = $api_response_code[ $response['code'] ]['Message'];

                // Return Response to browser
                $this->deliver_response();

            } elseif( $_REQUEST['username'] != 'foo' && 
                      $_REQUEST['password'] != 'bar' ){                                 // Return an error response if user fails authentication. 
                $response['code'] = 4;                                                  // needs a database call here
 
                $response['status'] = $api_response_code[ 
                                $response['code'] ]['HTTP Response'];                   // which woll be a LOT more complex in production 
                $response['data'] = $api_response_code[ 
                                    $response['code'] ]['Message'];

       
                deliver_response($_REQUEST['format'], $response);                       // Return Response to browser
            }

        }

    }

    //-------------------------------------------------------
    function to_xml(SimpleXMLElement $object, array $data) {  

    // take an array of data and transform it into XML format
    // for the users of the web service
 
        foreach ($data as $key => $value) {                                             // iterate over array 
            if (is_array($value)) {                                                     // multi level array?
                $new_object = $object->addChild($key);                                  // yes it is
                to_xml($new_object, $value);                                            // recurse as deep as you like
            }   
            else {   
                $object->addChild($key, $value);                                        // otherwise, build the branch
            }   
        }   
    }   


    //--------------------------
    function deliver_response(){

    // --- Step 1: Initialize variables and functions
    //
    //  Deliver HTTP Response
    //  The desired HTTP response content type: [json, html, xml]
    //  The desired HTTP response data


     //   header('Content-Type: text/html; charset=utf-8');                               // Set HTTP Response Content Type 


        if (strcasecmp($this->format,'JSON') == 0 ) {                                   // Process different content types
                                                                                        // json first we will be using
                                                                                        // this mostly I think` 
            $json_response = json_encode($this->response['data']);                      // Format data into a JSON response
            header('HTTP/1.1 '.$this->response['status'] .                                  // Set HTTP Response
                ' '.$this->http_response_code[$this->response['status'] ]);
            header('Content-Type: application/json; charset=utf-8');                    // Set HTTP Response Content Type

            echo $json_response;                                                        // Deliver formatted data
        } else if (strcasecmp($this->format,'xml') == 0) {                              // Process different content types
                                                                                        // xml for older CORBA feeders
                                                                                        // of API Web services
            $xml = new SimpleXMLElement('<HTTP-Root/>');
            to_xml($xml, $this->response['data']);
            echo $xml->asXML();
        } else {
            echo http_build_query($this->response['data'],'','<br>');                   // Deliver HTMLified formatted data
        }
    }

}  // class restServer






//------------- main() --------------------

$logger         = new ErrorLogger($configuration);              // turn on the error system first
$database       = new DBMS($logger);                            // fire up the database with details
                                                                // collected from config.php
                                                                // depending on the flavour of database,
                                                                // we try to make these persistant connections,
                                                                // that is, the connections are kept in a 
                                                                // pool of connections in USER space for a kernel specefied
                                                                // amount of time.  The database SHOULD just be handed
                                                                // an existing stream without the overhead of
                                                                // a brand new mysqli connect.  With luck.
$server = new restServer($database);                            // fire up a server and send in a database object



?>
