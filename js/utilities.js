// vim: set tabstop=4 shiftwidth=4 autoindent expandtab:
// usually for looks.  MORE important in Python, keeps the indents sane...
//---------------------------------------------------------
// CAPTAIN  SLOG
//---------------------------------------------------------
//
//  FILE:       utilities.js 
//  SYSTEM:     Best Practice New System 
//  AUTHOR:     Mark Addinall
//  DATE:       23/01/2016
//  SYNOPSIS:   2016 redesign of the Best Practice system.
//              this is a proof of concept using
//                  - HTML5 
//                  - CSS3
//                  - Bootstrap 
//                  - Backbone
//                  - jQuery
//                  - Underscore
//              for the fron end, and
//                  - PHP/REST
//              for the server backend
//                  - mySQL
//              as the database
//
//              Bits and bobs of Javascript that dont fit in our modules.
            

//------------------
jQuery(function($) {

	//Ajax contact
	var form = $('.contact-form');
		form.submit(function () {
			$this = $(this);
			$.post($(this).attr('action'), function(data) {
			$this.prev().text(data.message).fadeIn().delay(3000).fadeOut();
		},'json');
		return false;
	});

	//Goto Top
	$('.gototop').click(function(event) {
		 event.preventDefault();
		 $('html, body').animate({
			 scrollTop: $("body").offset().top
		 }, 500);
	});	
	//End goto top		

});


