<?php
// vim: set tabstop=4 shiftwidth=4 autoindent smartindent expandtab:
//---------------------------------------------------------
// CAPTAIN  SLOG
//---------------------------------------------------------
//
//  FILE:       cms.php    
//  SYSTEM:     New Tools/Boilerplate 2016
//  AUTHOR:     Mark Addinall
//  DATE:       15/03/2016 
//  SYNOPSIS:   This file contains the object that will 
//              encapsulate CMS methods and
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
//
//-----------------------------------------------------------------------------
//  Copyright (c) 2013..2016, Mark Addinall - That's IT - QLD
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
// 02/10/2005 | Initial creation Toolset V1.0 |  MA
// 29/04/2007 | Adept to Telstra NOC          |  MA
// 12/08/2009 | Complete re-write v2.x own use|  MA
// 18/02/2010 | Re-write CITEC (unfinished)   |  MA
// 12/02/2012 | Re-write v3.x new object model|  MA
// 17/04/2013 | Re-write v4 new object model  |  MA
// 14/05/2013 | Split into three objects      |  MA
// 15/03/2016 | Bring into AJAX OOP/OOD       |  MA
//------------+-------------------------------+------------



require_once('cms_light.php');          // the primitive objects
                                        // that go to build these CMS
                                        // classes.
// ALL of the objects in this collection ARE instantiated within the
// application.  See CMS_BASE and CMS_light for the abstract classes


//----------
class CMS EXTENDS CMS_light  {
    // 2011 - This object has been used in several versions of
    // chameleon.  Now being used in eHealth.  I left the
    // above commenents in for MY historical purpose.
    //
    // The database strategy now is half traditional (on application
    // start, loading major stacks of objects) and AJAXy database
    // updates which obviously do not require a document re-fetch.
    //
    // Mid 2011, this is now being used in Family Law Settlement
    // centres web application.  Modified of course.
    //
    // April 2013  v4.0 Tools, v5.0 of Chameleon
    // Complete re-write this time to extend the toolset
    // and the CMS to cater for RESPONSIVE Web applications.
    // That is, to cater for HTML5, CSS3 and various differing
    // devices such as iPods, Android Smartphones, Fondleslabs
    // etc.  As when these tools were first written, such beasts
    // did not exist, this version got a MAJOR re-write!


    //---------------
    function __construct() {

    // this object can be created in two different ways.
    // To create and define a brand new object to go into the
    // database, or be created to retrieve an existing object
    // from the database.  As such, the constructor has nothing
    // to do once the memory is allocated.  In the latter
    // case, objects can be PUSHED onto a stack.  And generally are.

    
    }
}




?>

