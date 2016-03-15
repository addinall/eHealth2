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
//              This little file compramises the CLIENT side of the RESTful API.
//              It also contains a few ancilliary functions to manipulate various
//              well defined DOM elements, and to "catch" events on elements
//              with pre-defined functions through the application of discrete
//              classes.
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

        var session = {};                                           // current session, used to set COOKIES
        session.role            = "UNDEFINED";                      // what is the role of the current user
        session.logged_in       = false;                            // always assume no login
        session.current_task    = "UNDEFINED";                      // what task are we performing RIGHT NOW
        session.user_profile    = {};                               // who, what, where and when???
        
        packet                  = {};                               // AJaX(J) packet TO API

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

            // workhorse.  EVERY call to our server REST API comes through
            // this interface.  NO exceptions.  We will NOT have an interface per
            // form or per MODEL as is a common case.  Makes no sense having an API really.
            // Might as well just code willy7-nilly.

            var packed = JSON.stringify(packet);

            console.log(packed);

            jqxhr = $.ajax({
                method:         "POST",                                         // ALL API calls are POST
                url:            "oop/server.php/",                              // API code
                dataType:       "json",                                         // JSON back at us please
                //contentType:    "application/json",                           // HEADER.  IMPORTANT!
                data:           {"packed":packed}                               // form data + security packet

            }).fail(function(msg) {                                             // error is depreciated.  WHY? Dunno...
                alert("Database Communication failure");                        // this is a HARD failure sent to
                console.log(JSON.stringify(msg));                               // us by the comms stack, authentication, or OS
            }).done(function(data) {                                            // AJaX worked, deal with the resultant packet
                __private_callback(data);                                       // in our custom callback. success() has been
            });                                                                 // depreciated, replaced by bone().  Why???
        }




        //----------------------------------------
        var __private_callback = function(data) {

            // this is the private callback function that will consume the JASONP
            // back from the API. It can be as simple as an ACK/NACK or as complex
            // as changing the CSS for the whole site.


            // first test for soft errors from the API

            if (data.hasOwnProperty('error')) {                 // API sent us back a soft error, so
                alert(JSON.stringify(data.error));              // tell  the user and
                return false;                                   // leave the modal dialogue or the form active
            }                                                   // so it can be fixed


            shared = data;                                      // make the returned JSON object
                                                                // available to other functions.
                                                                // as a request to LIST or READ
                                                                // will return a payload in this object
            console.log(shared);
            if (session.current_task == 'LOGIN') {

                alert("Success!  You are logged in.");
                $("#login-overlay").hide();                     // there is a bug in the bootstrap js that
                $('body').removeClass('modal-open');            // does not clean up the modal backdrop on
                $('modal-backdrop').remove();                   // exit and leaves the screen locked.

                role = "ADMIN";

                if (role == "ADMIN") {                          // if the user that just logged in is a super
                    __private_add_admin_menu();                 // user, give him/her some extra menu
                }   else {                                      // stuff to play with
                    __private_remove_admin_menu();
                }
            }
            else if (session.current_task == 'REGISTER') {
                alert("Success!  You are now registered.");
                $("#register").hide();                          // there is a bug in the bootstrap js that
                $('body').removeClass('modal-open');            // does not clean up the modal backdrop on
                $('modal-backdrop').remove();                   // exit and leaves the screen locked.
            }
        }


        //---------------------------------------
        var __private_validate = function(form) {

            var is_valid = true;

            // form field validation
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
                if (field.hasClass('required')) {                                           // ok, first check for fields that are REQUIRED
                    //console.log(field);                                                   // have not been caught by a modern browser and are
                    fval = field.val();                                                     // empty.  HONK!
                    if ((fval == null) || fval == '') {
                        alert('All required fields must be filled out:  ' + field.name);
                        is_valid = false;                                                   // and keep the MODAL for active
                    }
                    if (field.hasClass('email')) {                                          // now check if the email entry follows some
                                                                                            // rudimentry rules as described in this REGEX
                        var regex = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/;
                        is_valid = regex.test(fval);
                        if (! is_valid) {
                            alert('The email address is invalid.  Please re-enter');
                        }
                    }
                    if (field.hasClass('url')) {                                            // do the same for a URL entry, parse it
                                                                                            // lightly for correctness.
                        var regex = /(http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-])?/; 
                        is_valid = regex.test(fval);
                        if (! is_valid) {
                            alert('The email address is invalid.  Please re-enter');
                        }
                    }
                }
            });
            return is_valid;
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

            if (! __private_validate(form)) {
                return false;
            }

            packet = $(form).serializeJSON({checkboxUncheckedValue: "false"});


            if (packet.method == 'LOGIN') {                                 // prime our session variables
                session.current_task = 'LOGIN';
            } else if (packet.method == 'CREATE' &&
                        packet.type == "USER") {
                session.current_task = 'REGISTER';                          // user wants to join
                if (data.password != data.conform_password) {               // check the form passwords
                    alert("Passwords do not match.");                       // if they are not the same, bug out,
                    return false;                                           // but keep the MODAL up!
                }
            } else if (packet.method == 'LOGOUT') {
                session.role            = "UNDEFINED";                      // what is the role of the current user
                session.logged_in       = false;                            // always assume no login
                session.current_task    = "UNDEFINED";                      // what task are we performing RIGHT NOW
                session.user_profile    = {};                               // who, what, where and when???
                return true;                                                // make MODAL go away
            }


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


        //---------------------------------
        var display_list = function(form) {

            // generic stick a list to the screen
            // this little lot probably COULD be replaced with some ANGULAR
            // or REACT code, but I am reicant to introduce complexity
            // until I am sure I need to do so.  After all, the ANGULAR and REACT
            // people are doing pretty much the same things I do in native
            // or jQuery.  I just am doing a LOT less of it to meet my system
            // requirements.


        }

        //-----------------------------------------------
        var fetch_list = function(alpha, form) {
          
            // a generic function we make available to the application that will
            // return multiple tuples of data in a list based on the operating
            // parameters enclosed in the controlling form.  This is generally
            // a multilpe TAB display where the operator chooses the A..Z TAB
            // and the relevant sorted database members of the correct type
            // are sourced from the REST server.
            //
            // What happens witht the data at that stage is up to the business
            // logic in the application.  The data is available AT THIS STAGE
            // in the SHARED object, shared.

            packet.method          = "LIST";                                // GET multiple tuples
            packet.type            = form.type;                             // of type SCHEME_MEMBER - patient, client, staff etc...
            packet.card            = form.term;                             // BEGINS with, ENDS with, CONTAINS, SOUNDS LIKE ...
            packet.term            = alpha;                                 // the TERM IN - API
            packet.filter          = filter;                                // TERMS OUT - API eg. ["name_f","name_l","email"];

            __private_api();                                                // make an ajax request to the API
        }


        //--------------------------------
        var fetch_tuple = function(form) {

            // return one tuple from the database.
            // this is essentially a GET /patients/666
            // but written with some brains for a change


        }



        //------
        return {                                                            // ehatever me make visible here is the
            callAPI: callAPI,                                               // extent of the PUBLIC API.
            fetch_list: fetch_list,                                         // fetch multiple tuples in a list
            fetch_tuple: fetch_tuple                                        // GET fetch one tuple    

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
    $(".tit-tabs").on('click', 'a',function() {

    // now for some list routines.  The LIST pages of the application
    // are TABBED allowing the end user to select by alphabetical order.
    // This saves HUGE screens, which are pretty awful normally,
    // dreadfull on a smaller device like a fondleslab or telephone.

        var index = $(this).parent("li").index();                   // which li was clicked?
        thatsIT.fetch_list(ALPHABET[index], this);                  // go and get multiple tuples from the database
   });

   
});                                                             // document.ready
// ------   EOF -------------


