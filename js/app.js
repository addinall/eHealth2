// vim: set tabstop=4 shiftwidth=4 autoindent expandtab:
//---------------------------------------------------------
// CAPTAIN  SLOG
//---------------------------------------------------------
//
//  FILE:       app.js
//  SYSTEM:     2016 toolset/boilerplate
//  AUTHOR:     Mark Addinall
//  DATE:       22/02/2016
//  SYNOPSIS:   I called it app.js as every other framework
//              on heaven and Earth seems to have one.  It
//              will give the coders who modify this a clue
//              where to look.
//             
//              This module contains various functions that
//              are required by the Best Practice outward facing
//              pages as well as the portal and application functions.
//             

    // The Module Pattern is what is called a “design pattern”, and it’s extremely useful 
    // for a number of reasons. The attraction of the Module Pattern (and it’s variant, 
    // the Revealing Module Pattern, the one we use) are that it makes scoping simple, 
    // provides a clean way to implement a namespace, and doesn’t overcomplicate JavaScript design.
    //
    // It also keeps things very simple and easy to read and use, uses Objects in a very 
    // nice way, and doesn’t bloat your code with repetitive this and prototype declarations.

    //------------------------------
    var thatsIT = (function() {

        // if you look down the bottom of this design pattern you will see the functions
        // we are making available to the outside world by declaring them in our
        // return clause.  Everything else is PRIVATE and will trigger a runtime
        // exception if an application coder tries to access and METHODS or PROPERTIES
        // not defined as PUBLIC.
        //
        //      thatsIT.function_name(parameter_list, ...);
        //
        // this is about as secure as I can make the Javascript object model.
        //
        // We will use a little lightweight Angular in here, just for some
        // deep two way binding.  Not the full Model.

        var session = {};
        session.role            = "UNDEFINED";                      // what is the role of the current user
        session.logged_in       = false;                            // always assume no login
        session.current_task    = "UNDEFINED";                      // what task are we performing RIGHT NOW
        session.user_profile    = {};                               // who, what, where and when???
        
        packet                  = {};                               // AJaX(J) packet TO API
        packet.meta             = {};                               // accompanying meta data

        shared                  = {};                               // a shared memory block
                                                                    


        //-----------------------------------------                 
        var __private_add_admin_menu = function() {                 

            // if a user logs in, depending on the ROLE of the
            // registered user, give them more and/or different
            // menu options

            $("#admin-dropdown").toggleClass("hidden", false);

        }

        //-----------------------------------------
        var __private_remove_admin_menu = function() {

            // this happens when a staffie or SUPER-YOOSER
            // logs out.  Remove dangerous stuff from the menu
            // system.

            $("#admin-dropdown").toggleClass("hidden", true);

        }


        //----------------------------------------
        var __private_toggle_logins = function() {

            // if user is logged in, show LOGOUT on the menu bar
            // if user is NOT logged in, show LOGIN on the menu bar
            
            $("login").toggleClass("hidden");
            $("logout").toggleClass("hidden");

        }

        
        //------------------------------
        var __private_api = function() {

            //console.log(packet);

            jqxhr = $.ajax({
                url:            "oop/server.php",                               // API code
                type:           "POST",                                         // ALL API calls are POST
                dataType:       "json",                                         // JSON back at us please
                data:           JSON.stringify(packet),                         // form data + security packet
                processData:    false,                                          // probably redundant. do not URLencode
                contentType:    "application/json"                              // HEADER.  IMPORTANT!

            }).fail(function(msg) {                                             // error is depreciated.  WHY? Dunno...
                alert("Database Communication failure");                        // this is a HARD failure sent to
                console.log(JSON.stringify(msg));                               // us by the comms stack, authentication, or OS
                })
              .done(function(data) {                                            // AJaX worked, deal with the resultant packet
                __private_callback(data);                                       // in our custom callback. success() has been
                });                                                             // depreciated, replaced by bone().  Why???
        }




        //----------------------------------------
        var __private_callback = function(data) {

            // this is the private callback function that will consume the JASONP
            // back from the API. It can be as simple as an ACK/NACK or as complex
            // as changing the CSS for the whole site.


            console.log(data);
            // first test for soft errors from the API

            if (data.hasOwnProperty('error')) {                 // API sent us back a soft error, so
                alert(JSON.stringify(data.error));              // tell  the user and
                return false;                                   // leave the modal dialogue active
            }                                                   // so it can be fixed


            shared = data;                                      // make the returned JSON object
                                                                // available to other functions.
                                                                // as a request to LIST or READ
                                                                // will return a payload in this object
            console.log(shared);
            if (session.current_task == 'login') {

                alert("Success!  You are logged in.");
                $("#login-overlay").hide();                     // there is a bug in the bootstrap js that
                $('body').removeClass('modal-open');            // does not clean up the modal backdrop on
                $('modal-backdrop').remove();                   // exit and leaves the screen locked.

                role = "admin";

                if (role == "admin") {                          // if the user that just logged in is a super
                    __private_add_admin_menu();                 // user, give him/her some extra menu
                }   else {                                      // stuff to play with
                    __private_remove_admin_menu();
                }
            }
            else if (session.current_task == 'register') {
                alert("Success!  You are now registered.");
                $("#register").hide();                          // there is a bug in the bootstrap js that
                $('body').removeClass('modal-open');            // does not clean up the modal backdrop on
                $('modal-backdrop').remove();                   // exit and leaves the screen locked.
            }
        }


        //----------------------------------
        var __private_getinfo = function() {

            // function private to this module that will
            // call an independant IP and then we can do
            // one of a number of things.  We can look at it here
            // and bounce "iffy" requests, or we parcel up the
            // information in our API request, and let the
            // server side deal with the rules and regulations.
            // slightly less network traffic doing it here,
            // greater ELINT logging and tracing capabilities
            // doing it server side.
            //
            // if asked to choose, I would do it server side
            //
            // this will parcel the client's
            //  ip address
            //  hostname
            //  organisation
            //  country
            //  city
            //  zip
            // to send in the packet.  This will come in handy
            // against IP spoofing and man in the middle attacks. 



            $.get("http://geoip.nekudo.com/api", 
                function(data) {                                // this data is the result of the get
                    packet.meta = data;                         // form comes in as an argument, packet
                                                                // is global to this MODULE namespace
            }, "json");
        }


        //---------------------------
        var callAPI = function(form) {

            // before we get carried away calling our API,
            // first do a client side validation of the form.
            // we want to cycle through whatever form we are being
            // fed and do some simple validation.  ie. if the
            // input item is REQUIRED and it has been submitted
            // empty, then BEEP and bugger orf.
            //
            // we can build in validation sophistication here if and when
            // we need/want to.  'is the password strong enough',
            // does the email conform to RFCxxxx?' cetra.
            //
            // Now, in our newer browsers we add the 'required''
            // attribute to the form item object in the HTML5
            // and NULLS are trapped on submit before they get
            // this far.  This doesn't work on anything earlier
            // than late pick IE9.  To cater for older and 'difficult'
            // browsers, we simply use the CSS1-3 convenience of
            // adding a CLASS of required to the appropriate
            // for items.

            $(form).each(function(){
                var field = $(this).find(':input');
                if field.hasClass('required') {
                    console.log(field);
                }
            });


            // we then gather a little info about the client first of all.
            // a clever hacker can spoof an IP, but not all the location
            // and server information that we can gather.

            var data = $(form).serializeJSON({checkboxUncheckedValue: "false"});

            packet.method      = data.method;                               // move up a level for JSON object
            packet.type        = data.type;                                 // transport standard
            delete data['method'];
            delete data['type'];                                            // clean up the object

            if (packet.method == 'login') {                                 // prime our session variables
                session.current_task = 'login';
            } else if (packet.method == 'create' &&
                        packet.type == "user") {
                session.current_task = 'register';                          // user wants to join
                if (data.password != data.conform_password) {               // check the form passwords
                    alert("Passwords do not match.");                       // if they are not the same, bug out,
                    return false;                                           // but keep the MODAL up!
                }
            } else if (packet.method == 'logout') {
                session.role            = "UNDEFINED";                      // what is the role of the current user
                session.logged_in       = false;                            // always assume no login
                session.current_task    = "UNDEFINED";                      // what task are we performing RIGHT NOW
                session.user_profile    = {};                               // who, what, where and when???
                return true;                                                // make MODAL go away
            }

            packet.attributes  = data;                                      

            __private_getinfo();                                            // get client geo-location data 

            __private_api();                                                // make an ajax request to the API
        }           



        //-------------------------------------
        var __private_list_users = function() {

            $("#user_table").empty();                                       // get rid of the last table
            $("#user_table").append('<table class="table table_striped table-bordered"></table>');                       
            var workspace = $("#user_table").children();                    // grab the DOM body of the table

            // build the header

            console.log(shared);
            workspace.append("<thead> <tr> <th>First Name</th><th>Last name</th><th>Email</th><th>Edit</th><th>DELETE</th> </tr> </thead>");
            $.each(shared, function() {
                // loop throught the responses and build the table
             
                workspace.append('<tr><td>');
                workspace.append(this.first_name + '</td>');
                workspace.append('<tr><td>');
                workspace.append(this.last_name + '</td>');
                workspace.append('<tr><td>');
                workspace.append(this.email  + '</td></tr>');
            });
        }


        //-----------------------------------------------
        var fetch_users = function(alpha, filter, draw) {
           

            packet.method          = "list";                                // GET multiple tuples
            packet.type            = "user";                                // of type user
            packet.search          = "last_name";                           // object of simple SELECT criteria
            packet.card            = "^";                                   // BEGINS with
            packet.term            = alpha;                                 // the TERM IN - API
            packet.filter          = filter;                                // TERMS OUT - API eg. ["name_f","name_l","email"];

            __private_api();                                                // make an ajax request to the API
            if (draw) {
                __private_list_users();                                     // send payload to screen
            }
        }

        //------
        return {                                                            // ehatever me make visible here is the
            callAPI: callAPI,                                               // extent of the PUBLIC API.
            fetch_users: fetch_users
        }

    })();                                                                   // MODULE thatsIT namespace


//-----------------------------
$(document).ready(function() {


    var ALPHABET   = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";                          // SHOULD be a constant BUT this code has
                                                                            // to run in older browsers
    //--------------------------------------------------
    $(".tit-ajax-form").bind('submit', function(event) {

    // instead of the usual method (sic) of tying AJaX(J) calls to the
    // id of specfic forms, we will give all of our client side API
    // consumers a CLASS and treat each with this one routine.
    //
    // As we define the contract between the API consumer and
    // provider, we will negotiate the parameters that need to be
    // communicated between the two parts of the system.  From the
    // front side here, we will wrap up the parameters in hidden POST
    // variables.
    //
    // If all agree, we will ALWAYS use POST as the transfer type and
    // indicate in the argument package the type of CRUD transaction
    // we request.
    //

        event.preventDefault();                             // stop the normal submit actions

        thatsIT.callAPI(this);                              // that is all we have to do for
                                                            // ALL of our form/submit based
                                                            // API requests

    });                                                     // end of our AJaX(J) catch-all
                                                            // form class trap
    

    // the above code traps about everything the user wants to ADD or EDIT in the
    // system by blocking all the forms in the system.  Now we implement some
    // other more specialised routines that WILL rquire a call to our API server,
    // but do not involve a form submission.

    //-----------------------------------------------
    $("#users_tabs").on('click', 'a',function() {

    // now for some list routines.  The LIST pages of the application
    // are TABBED allowing the end user to select by alphabetical order.
    // This saves HUGE screens, which are pretty awful normally,
    // dreadfull on a smaller device like a fondleslab or telephone.

        var index = $(this).parent("li").index();               // which li was clicked?
        thatsIT.fetch_users(ALPHABET[index], ["first_name",
                                                "last_name",
                                                "email"] true); // fetch those users and draw to screen
   });

   
});                                                             // document.ready
// ------   EOF -------------


