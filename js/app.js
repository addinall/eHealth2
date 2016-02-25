// vim: set tabstop=4 shiftwidth=4 autoindent expandtab:
//---------------------------------------------------------
// CAPTAIN  SLOG
//---------------------------------------------------------
//
//  FILE:       app.js
//  SYSTEM:   
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


        var session = {};
        session.role            = "UNDEFINED";                      // what is the role of the current user
        session.logged_in       = false;                            // always assume no login
        session.current_task    = "UNDEFINED";                      // what task are we performing RIGHT NOW
        session.user_profile    = {};                               // who, what, where and when???
        
        packet                  = {};                               // AJaX(J) packet TO API
        packet.meta             = {};                               // accompanying meta data

        shared                  = {};                               // a shared memory block
                                                                    
                                                                    // social media authentication
        lock                    = new Auth0Lock('TgZy1CGIjbIBPs746UXEjYXJpGeWfx9L', 
                                                    'addinall.au.auth0.com');


        //-----------------------------------------
        var __private_add_admin_menu = function() {

            $("#admin-dropdown").toggleClass("hidden", false);

        }

        //-----------------------------------------
        var __private_remove_admin_menu = function() {

            $("#admin-dropdown").toggleClass("hidden", true);

        }


        //----------------------------------------
        var __private_toggle_logins = function() {

            $("login").toggleClass("hidden");
            $("logout").toggleClass("hidden");

        }

        
        //------------------------------
        var __private_api = function() {

            //console.log(packet);

            jqxhr = $.ajax({
                url:            "localhost/server.php",                         // API code
                type:           "POST",                                         // ALL API calls are POST
                dataType:       "json",                                         // JSON back at us please
                data:           JSON.stringify(packet),                         // form data + security packet
                processData:    false,                                          // probably redundant. do not URLencode
                contentType:    "application/json"                              // HEADER.  IMPORTANT!

            }).fail(function(msg) {                                             // error is depreciated.  WHY? Dunno...
                alert("Database Communication failure");                        // this is a HARD failure sent to
                console.log(JSON.stringify(msg));                               // us by the comms stack, authentication, or OS
                })
              .success(function(data) {                                         // AJaX worked, deal with the resultant packet
                __private_callback(data);                                       // in our custom callback.
                });
        }




        //----------------------------------------
        var __private_callback = function(data) {

            // this is the private callback function that will consume the JASONP
            // back from the API. It can be as simple as an ACK/NACK or as complex
            // as changing the CSS for the whole site.


            console.log(data);
            // first test for soft errors from the API

            if (data.hasOwnProperty('error')) {                  // API sent us back a soft error, so
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

            $(form).each(function(){
                //console.log(this);
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
                lock.show(function(err, profile, token) {
                    if (err) {
                                                                            // Error callback
                        alert('There was an error');
                    } else {
                                                                            // Success callback

                                                                            // Save the JWT token.
                        localStorage.setItem('userToken', token);

                                                                            // Save the profile
                        session.user_profile = profile;
                    }
                });
            } else if (packet.method == 'create' &&
                        packet.type == "user") {
                session.current_task = 'register';
            }

            packet.attributes  = data;                                      
            __private_getinfo();                                            // get client geo-location data 
                                                                            // and merge it with form data

            __private_api();                                                // make an ajax request to the API
        }           



        //-------------------------------------
        var __private_list_users = function() {

            $("#user_table").empty();                                       // get rid of the last table
            $("#user_table").append('<table class="table table_striped table-bordered"></table>');                       
            var workspace = $("#user_table").children();

            // build the header

            console.log(shared);
            workspace.append("<thead> <tr> <th>First Name</th><th>Last name</th><th>Email</th><th>Edit</th><th>DELETE</th> </tr> </thead>");
            $.each(shared, function() {
                // loop throught the responses and build the table
             
                workspace.append('<tr><td>');
                workspace.append(this.name_f + '</td>');
                workspace.append('<tr><td>');
                workspace.append(this.name_l + '</td>');
                workspace.append('<tr><td>');
                workspace.append(this.email  + '</td></tr>');
            });
        }


        //--------------------------------------
        var fetch_users = function(alpha,draw) {
           

            packet.method          = "list";
            packet.type            = "user";
            packet.search          = "name_l";
            packet.card            = "^";
            packet.term            = alpha;
            packet.filter           = ["name_f","name_l","email"];

            __private_api();                                                // make an ajax request to the API
            if (draw) {
                __private_list_users();                                     // send payload to screen
            }
        }

        //------
        return {
            callAPI: callAPI,
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
    // As Alex and I define the contract between the API consumer and
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

        var index = $(this).parent("li").index();           // which li was clicked?
        thatsIT.fetch_users(ALPHABET[index], true);         // fetch those users and draw to screen
   });

   
});                                                         // document.ready
// ------   EOF -------------


