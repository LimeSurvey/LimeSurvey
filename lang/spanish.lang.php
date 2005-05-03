<?php
/*
    #############################################################
    # >>> PHP Surveyor                                          #
    #############################################################
    # > Author:  Jason Cleeland                                 #
    # > E-mail:  jason@cleeland.org                             #
    # > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
    # >          CARLTON SOUTH 3053, AUSTRALIA                  #
    # > Date:    20 February 2003                               #
    #                                                           #
    # This set of scripts allows you to develop, publish and    #
    # perform data-entry on surveys.                            #
    #############################################################
    #                                                           #
    #   Copyright (C) 2003  Jason Cleeland                      #
    #                                                           #
    # This program is free software; you can redistribute       #
    # it and/or modify it under the terms of the GNU General    #
    # Public License as published by the Free Software          #
    # Foundation; either version 2 of the License, or (at your  #
    # option) any later version.                                #
    #                                                           #
    # This program is distributed in the hope that it will be   #
    # useful, but WITHOUT ANY WARRANTY; without even the        #
    # implied warranty of MERCHANTABILITY or FITNESS FOR A      #
    # PARTICULAR PURPOSE.  See the GNU General Public License   #
    # for more details.                                         #
    #                                                           #
    # You should have received a copy of the GNU General        #
    # Public License along with this program; if not, write to  #
    # the Free Software Foundation, Inc., 59 Temple Place -     #
    # Suite 330, Boston, MA  02111-1307, USA.                   #
    #############################################################
    #                                                           #
    # This language file kindly provided by Luis M. Martinez    #
    #                                                           #
    #############################################################
*/
//SINGLE WORDS
define("_YES", "S&iacute;");
define("_NO", "No");
define("_UNCERTAIN", "Dudoso");
define("_ADMIN", "Admin");
define("_TOKENS", "Tokens");
define("_FEMALE", "Femenino");
define("_MALE", "Masculino");
define("_NOANSWER", "Sin respuesta");
define("_NOTAPPLICABLE", "N/A"); //New for 0.98rc5
define("_OTHER", "Otro");
define("_PLEASECHOOSE", "Favor de elegir");
define("_ERROR_PS", "Error");
define("_COMPLETE", "completo");
define("_INCREASE", "Aumento"); //NEW WITH 0.98 BABELFISH TRANSLATION
define("_SAME", "Iguales"); //NEW WITH 0.98 BABELFISH TRANSLATION
define("_DECREASE", "Disminuci&oacute;n"); //NEW WITH 0.98 BABELFISH TRANSLATION
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Confirmaci&oacute;n");
define("_TOKEN_PS", "Token");
define("_CONTINUE_PS", "Continuar");

//BUTTONS
define("_ACCEPT", "Aceptar");
define("_PREV", "anterior");
define("_NEXT", "sig");
define("_LAST", "último");
define("_SUBMIT", "enviar");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Favor de elijir uno de los siguientes");
define("_ENTERCOMMENT", "Favor de teclear su comentario aqu&iacute;");
define("_NUMERICAL_PS", "S&oacute;lo se aceptan n&uacute;meros en este campo");
define("_CLEARALL", "Salir y Aclarar la Encuesta"); //Better word choice from John Krikorian
define("_MANDATORY", "Esta pregunta es requerida");
define("_MANDATORY_PARTS", "Favor de completar todas las partes");
define("_MANDATORY_CHECK", "Favor de seleccionar al menos un elemento");
define("_MANDATORY_RANK", "Favor de clasificar todos los elementos");
define("_MANDATORY_POPUP", "Unas o m&aacute;s preguntas obligatorias no se han contestado. Usted no puede proceder hasta que se hayan terminado éstos"); //NEW in 0.98rc4 - Mod by John Krikorian
define("_VALIDATION", "This question must be answered correctly"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Formato: AAAA-MM-DD");
define("_DATEFORMATEG", "(pej: 2003-12-25 para Navidad)");
define("_REMOVEITEM", "Eliminar este elemento");
define("_RANK_1", "Haga click en un elemento de la lista de la izquierda, empezando por el");
define("_RANK_2", "elemento con m&aacute;s alta clasificaci&oacute;n hasta llegar al elemento con m&aacute;s baja clasificaci&oacute;n.");
define("_YOURCHOICES", "Sus Opciones");
define("_YOURRANKING", "Su Clasificaci&oacute;n");
define("_RANK_3", "Haga click en las tijeras de la derecha de cada elemento");
define("_RANK_4", "para eliminar la &uacute;ltima captura de su lista clasificada");
//From INDEX.PHP
define("_NOSID", "No ha proporcionado un n&uacute;mero identificador de encuesta");
define("_CONTACT1", "Favor de contactar a");
define("_CONTACT2", "para m&aacute;s asistencia");
define("_ANSCLEAR", "Respuestas quitadas");
define("_RESTART", "Reiniciar la Encuesta");
define("_CLOSEWIN_PS", "Cerrar esta Ventana");
define("_CONFIRMCLEAR", "&iquest;Est&aacute; seguro de eliminar todas sus respuestas?");
define("_CONFIRMSAVE", "Are you sure you want to save your responses?");
define("_EXITCLEAR", "Salir y Aclarar la Encuesta"); //Mod by John Krikorian
//From QUESTION.PHP
define("_BADSUBMIT1", "No se pueden enviar los resultados - no hay resultados por enviar.");
define("_BADSUBMIT2", "Este error puede ocurrir si envi&oacute; sus respuestas y presion&oacute; 'renovar' en su navegador. En este caso, sus respuestas ya fueron guardadas.");
define("_NOTACTIVE1", "Sus respuestas no han sido guardadas porque la Encuesta no ha sido activada a&uacute;n.");
define("_CLEARRESP", "Inicializar Respuestas");
define("_THANKS", "Gracias");
define("_SURVEYREC", "Sus respuestas han sido guardadas.");
define("_SURVEYCPL", "Encuesta Completada");
define("_DIDNOTSAVE", "No se guard&oacute;");
define("_DIDNOTSAVE2", "Ha ocurrido un error inesperado y sus respuestas no han podido ser guardadas.");
define("_DIDNOTSAVE3", "Sus respuestas no se han perdido y han sido enviadas por correo electr&oacute;nico al administrador de la encuesta para ser capturadas en nuestra base de datos posteriormente.");
define("_DNSAVEEMAIL1", "Ha sucedido un error al guardar una respuesta de la encuesta identificada con");
define("_DNSAVEEMAIL2", "DATOS PARA SER CAPTURADOS");
define("_DNSAVEEMAIL3", "EL CODIGO SQL HA FALLADO");
define("_DNSAVEEMAIL4", "MENSAJE DE ERROR");
define("_DNSAVEEMAIL5", "ERROR GUARDANDO");
define("_SUBMITAGAIN", "Reintente enviar otra vez");
define("_SURVEYNOEXIST", "Lo sentimos. No hay encuestas que coincidan.");
define("_NOTOKEN1", "Esta encuesta tiene control de acceso. Necesita un token v&aacute;lido para participar.");
define("_NOTOKEN2", "Si se le ha proporcionado un token, favor de teclearlo en la caja de abajo y hacer click en continuar.");
define("_NOTOKEN3", "El token que se le ha proporcionado no es v&aacute;lido o ya fue usado.");
define("_NOQUESTIONS", "Esta encuesta todav&iacute;a no tiene preguntas y no puede ser probada ni completada.");
define("_FURTHERINFO", "Para m&aacute;s informaci&oacute;n contactar a");
define("_NOTACTIVE", "Esta encuesta no est&aacute; activa. No podr&aacute; guardar sus respuestas.");
define("_SURVEYEXPIRED", "This survey is no longer available."); //NEW for 098rc5
define("_SURVEYCOMPLETE", "Usted ha terminado ya este examen.");
define("_INSTRUCTION_LIST", "Elija solamente uno del siguiente"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Compruebe cualquiera que se aplica"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Examen Sometido"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Una nueva respuesta fue incorporada para su examen"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Chasque el acoplamiento siguiente para ver la respuesta individual:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Estad&iacute;stica de la visi&oacute;n chascando aqu&iacute;:"); //NEW for 098rc5

define("_PRIVACY_MESSAGE", "<b><i>Una nota sobre privacidad.</i></b><br />"
        ."Este examen es an&oacute;nimo.<br />"
        ."No se guardar&aacute; ninguna informaci&oacute;n sobre usted."); //New for 0.98rc9 - Translation by John Krikorian

define("_THEREAREXQUESTIONS", "Hay {NUMBEROFQUESTIONS} preguntas en este examen."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count. - Translation by John Krikorian
define("_THEREAREXQUESTIONS_SINGLE", "Hay una pregunta en este examen."); //New for 0.98rc9 - singular version of above - Translation by John Krikorian

define ("_RG_REGISTER1", "You must be registered to complete this survey"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "You may register for this survey if you wish to take part.<br />\n"
                        ."Enter your details below, and an email containing the link to "
                        ."participate in this survey will be sent immediately."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Email Address"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "First Name"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Last Name"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "The email you used is not valid. Please try again.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "The email you used has already been registered.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Registration Confirmation");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Thank you for registering to participate in this survey.<br /><br />\n"
                                   ."An email has been sent to the address you provided with access details "
                                   ."for this survey. Please follow the link in that email to proceed.<br /><br />\n"
                                   ."Survey Administrator {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<b>Thank You<br /><br />"
                       ."You have completed answering the questions in this survey.</b><br /><br />"
                       ."Click on ["._SUBMIT."] now to complete the process and save your answers."); //New for 0.98finalRC1
define("_SM_REVIEW", "If you want to check any of the answers you have made, and/or change them, "
                    ."you can do that now by clicking on the [<< "._PREV."] button and browsing "
                    ."through your responses."); //New for 0.98finalRC1

//For the "printable" survey
define("_PS_CHOOSEONE", "Please choose <b>only one</b> of the following:"); //New for 0.98finalRC1
define("_PS_WRITE", "Please write your answer here:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Please choose <b>all</b> that apply:"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Please choose all that apply and provide a comment:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Please choose the appropriate response for each item:"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Please write your answer(s) here:"); //New for 0.98finalRC1
define("_PS_DATE", "Please enter a date:"); //New for 0.98finalRC1
define("_PS_COMMENT", "Make a comment on your choice here:"); //New for 0.98finalRC1
define("_PS_RANKING", "Please number each box in order of preference from 1 to"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Submit Your Survey."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Thank you for completing this survey."); //New for 0.98finalRC1
define("_PS_FAXTO", "Please fax your completed survey to:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Only answer this question"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "if you answered"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "and"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "to question"); //New for 0.98finalRC1
define("_PS_CON_OR", "or"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Save your responses so far");
define("_SAVEHEADING", "Save Your Unfinished Survey");
define("_RETURNTOSURVEY", "Return To Survey");
define("_SAVENAME", "Name");
define("_SAVEPASSWORD", "Password");
define("_SAVEPASSWORDRPT", "Repeat Password");
define("_SAVE_EMAIL", "Your Email");
define("_SAVEEXPLANATION", "Enter a name and password for this survey and click save below.<br />\n"
                  ."Your survey will be saved using that name and password, and can be "
                  ."completed later by logging in with the same name and password.<br /><br />\n"
                  ."If you give an email address, an email containing the details will be sent "
                  ."to you.");
define("_SAVESUBMIT", "Save Now");
define("_SAVENONAME", "You must supply a name for this saved session.");
define("_SAVENOPASS", "You must supply a password for this saved session.");
define("_SAVENOMATCH", "Your passwords do not match.");
define("_SAVEDUPLICATE", "This name has already been used for this survey. You must use a unique save name.");
define("_SAVETRYAGAIN", "Please try again.");
define("_SAVE_EMAILSUBJECT", "Saved Survey Details");
define("_SAVE_EMAILTEXT", "You, or someone using your email address, have saved "
                         ."a survey in progress. The following details can be used "
                         ."to return to this survey and continue where you left "
                         ."off.");
define("_SAVE_EMAILURL", "Reload your survey by clicking on the following URL:");
define("_SAVE_SUCCEEDED", "Your survey responses have been saved succesfully");
define("_SAVE_FAILED", "An error occurred and your survey responses were not saved.");
define("_SAVE_EMAILSENT", "An email has been sent with details about your saved survey.");

//Load Messages
define("_LOAD_SAVED", "Load unfinished survey");
define("_LOADHEADING", "Load A Previously Saved Survey");
define("_LOADEXPLANATION", "You can load a survey that you have previously saved from this screen.<br />\n"
              ."Type in the 'name' you used to save the survey, and the password.<br /><br />\n");
define("_LOADNAME", "Saved name");
define("_LOADPASSWORD", "Password");
define("_LOADSUBMIT", "Load Now");
define("_LOADNONAME", "You did not provide a name");
define("_LOADNOPASS", "You did not provide a password");
define("_LOADNOMATCH", "There is no matching saved survey");

define("_ASSESSMENT_HEADING", "Your Assessment");
?>
