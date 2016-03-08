<?php
// vim: set tabstop=4 shiftwidth=4 autoindent expandtab:
//---------------------------------------------------------
// CAPTAIN  SLOG
//---------------------------------------------------------
//
//  FILE:       index.html 
//  SYSTEM:     2016 new tools/boilerplate 
//  AUTHOR:     Mark Addinall
//  DATE:       26/01/2016
//  SYNOPSIS:   2016 redesign of the OOP system(s) 
//              this is a proof of concept using
//                  - HTML5 
//                  - CSS3
//                  - Bootstrap 
//                  - Angular
//                  - jQuery
//                  - Underscore
//              for the fron end, and
//                  - PHP/REST
//              for the server backend
//                  - mySQL
//              as the database
//
//            Little mailer tied to the contact-us form


	header('Content-type: application/json');
	$status = array(
		'type'=>'success',
		'message'=>'Email sent!'
	);

    $name = @trim(stripslashes($_POST['name'])); 
    $email = @trim(stripslashes($_POST['email'])); 
    $subject = @trim(stripslashes($_POST['subject'])); 
    $message = @trim(stripslashes($_POST['message'])); 

    $email_from = $email;
    $email_to = 'addinall@addinall.net.au';

    $body = 'Name: ' . $name . "\n\n" . 'Email: ' . $email . "\n\n" . 'Subject: ' . $subject . "\n\n" . 'Message: ' . $message;

    $success = @mail($email_to, $subject, $body, 'From: <'.$email_from.'>');

    echo json_encode($status . $success);
    die;
