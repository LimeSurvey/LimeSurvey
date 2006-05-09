<?php
/*
    #############################################################
    # >>> PHPSurveyor                                          #
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
    # Reviewed by Juan Rafael Fernández, May 2006.              #
    #                                                           #
    #############################################################
*/
//SINGLE WORDS
define("_YES", "Sí");
define("_NO", "No");
define("_UNCERTAIN", "Dudoso");
define("_ADMIN", "Admin.");
define("_TOKENS", "Tokens");
define("_FEMALE", "Femenino");
define("_MALE", "Masculino");
define("_NOANSWER", "Sin respuesta");
define("_NOTAPPLICABLE", "No disponible"); //New for 0.98rc5
define("_OTHER", "Otro");
define("_PLEASECHOOSE", "Elija, por favor");
define("_ERROR_PS", "Error");
define("_COMPLETE", "completo");
define("_INCREASE", "Aumento"); //NEW WITH 0.98 BABELFISH TRANSLATION
define("_SAME", "Iguales"); //NEW WITH 0.98 BABELFISH TRANSLATION
define("_DECREASE", "Disminución"); //NEW WITH 0.98 BABELFISH TRANSLATION
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Confirmación");
define("_TOKEN_PS", "Token");
define("_CONTINUE_PS", "Continuar");

//BUTTONS
define("_ACCEPT", "Aceptar");
define("_PREV", "anterior");
define("_NEXT", "sig.");
define("_LAST", "último");
define("_SUBMIT", "Enviar");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Por favor elija una de los siguientes");
define("_ENTERCOMMENT", "Por favor  teclee su comentario aquí");
define("_NUMERICAL_PS", "Sólo se aceptan números en este campo");
define("_CLEARALL", "Salir y reiniciar la encuesta"); //Better word choice from John Krikorian
define("_MANDATORY", "Esta pregunta es de respuesta obligatoria");
define("_MANDATORY_PARTS", "Por favor  complete todas las partes");
define("_MANDATORY_CHECK", "Por favor seleccione al menos un elemento");
define("_MANDATORY_RANK", "Por favor clasifique todos los elementos");
define("_MANDATORY_POPUP", "Una o más preguntas obligatorias no se han contestado. No podrá proseguir hasta que no haya completado estas"); //NEW in 0.98rc4 - Mod by John Krikorian
define("_VALIDATION", "Esta pregunta debe ser contestada correctamente"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "Una o más preguntas no fueron contestadas de forma correcta. Usted no puede proceder hasta que dichas respuestas sean válidas"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Formato: AAAA-MM-DD");
define("_DATEFORMATEG", "(pej: 2003-12-25 para Navidad)");
define("_REMOVEITEM", "Eliminar este elemento");
define("_RANK_1", "Haga click en un elemento de la lista de la izquierda, empezando por el");
define("_RANK_2", "elemento con más alta clasificación hasta llegar al elemento con más baja clasificación.");
define("_YOURCHOICES", "Sus Opciones");
define("_YOURRANKING", "Su Clasificación");
define("_RANK_3", "Haga click en las tijeras que hay a la derecha de cada elemento");
define("_RANK_4", "para eliminar la última captura de su lista clasificada");
//From INDEX.PHP
define("_NOSID", "No ha proporcionado un número identificador de encuesta");
define("_CONTACT1", "Contacte con");
define("_CONTACT2", "si desea más ayuda");
define("_ANSCLEAR", "Respuestas borradas");
define("_RESTART", "Reiniciar la Encuesta");
define("_CLOSEWIN_PS", "Cerrar esta Ventana");
define("_CONFIRMCLEAR", "¿Está seguro de eliminar todas sus respuestas?");
define("_CONFIRMSAVE", "¿Está seguro de que desea guardar sus respuestas?");
define("_EXITCLEAR", "Salir y reiniciar la encuesta"); //Mod by John Krikorian
//From QUESTION.PHP
define("_BADSUBMIT1", "No se pueden enviar los resultados - no hay resultados que enviar.");
define("_BADSUBMIT2", "Este error puede ocurrir si envió sus respuestas y presionó 'renovar' en su navegador. En este caso, sus respuestas ya fueron guardadas.");
define("_NOTACTIVE1", "Sus respuestas no han sido guardadas porque la encuesta no se ha activado aún.");
define("_CLEARRESP", "Borrar las respuestas");
define("_THANKS", "Gracias");
define("_SURVEYREC", "Sus respuestas han sido guardadas.");
define("_SURVEYCPL", "Encuesta Completada");
define("_DIDNOTSAVE", "No se guardó");
define("_DIDNOTSAVE2", "Ha ocurrido un error inesperado y sus respuestas no han podido ser guardadas.");
define("_DIDNOTSAVE3", "Sus respuestas no se han perdido, han sido enviadas por correo electrónico al administrador de la encuesta para su posterior incorporación a nuestra base de datos.");
define("_DNSAVEEMAIL1", "Ha sucedido un error al guardar una respuesta de la encuesta identificada con");
define("_DNSAVEEMAIL2", "DATOS QUE SERÁN GUARDADOS");
define("_DNSAVEEMAIL3", "EL CODIGO SQL QUE HA FALLADO");
define("_DNSAVEEMAIL4", "MENSAJE DE ERROR");
define("_DNSAVEEMAIL5", "Error al guardar los resultados de la encuesta en la base de datos");
define("_SUBMITAGAIN", "Intente enviarla otra vez");
define("_SURVEYNOEXIST", "Lo sentimos. No hay encuestas que coincidan.");
define("_NOTOKEN1", "Esta encuesta tiene control de acceso. Necesita un token válido para participar.");
define("_NOTOKEN2", "Si se le ha proporcionado un token, por favor de tecléelo en la caja de abajo y pulse en continuar.");
define("_NOTOKEN3", "El token que se le ha proporcionado no es válido o ya fue usado.");
define("_NOQUESTIONS", "Esta encuesta todavía no tiene preguntas y no puede ser probada ni completada.");
define("_FURTHERINFO", "Para más información contactar con");
define("_NOTACTIVE", "Esta encuesta no está activa. No podrá guardar sus respuestas.");
define("_SURVEYEXPIRED", "Esta encuesta ya no está disponible."); //NEW for 098rc5

define("_SURVEYCOMPLETE", "Ya ha completado esta encuesta.");

define("_INSTRUCTION_LIST", "Elija solamente una entrada de las siguientes"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Seleccione las entradas que correspondan"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Encuesta enviada"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Se añadió una nueva respuesta a su encuesta"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Pulse en el siguiente enlace para ver la respuesta individual:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Vea las estadísticas pulsando aquí:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Pulse en el siguiente enlace para editar la respuesta individual:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>Nota sobre privacidad.</i></strong><br />"
        ."Esta encuesta es anónima.<br />"
	."El registro de sus respuestas a la encuesta no contiene "
	."información identificativa sobre usted a menos que en alguna pregunta específica "
	."de la encuesta se haya preguntado. Si ha respondido a una "
	."encuesta que usaba un token de identificación para permitirle acceder a "
	."la encuesta, puede confiar en que el token identificatorio no "
	."se guarda con sus respuestas. Se gestionan en una base de datos "
	."separada, y únicamente se actualizará para indicar que "
	."ha completado (o no) la encuesta. No hay formar de relacionar "
	."tokens de identificación con las respuestas de esta encuesta."); //New for 0.98rc9 - Translation by John Krikorian

define("_THEREAREXQUESTIONS", "Hay {NUMBEROFQUESTIONS} preguntas en esta encuesta."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count. - Translation by John Krikorian
define("_THEREAREXQUESTIONS_SINGLE", "Hay una pregunta en este encuesta."); //New for 0.98rc9 - singular version of above - Translation by John Krikorian

define ("_RG_REGISTER1", "Debe haberse dado de alta para completar esta encuesta"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Puede darse de alta en esta encuesta si desea participar.<br />\n"
                        ."Introduzca sus datos abajo, y le enviaremos inmediatamente un correo electrónico con "
                        ."el enlace para participar en esta encuesta."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Correo Electrónico"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Nombre"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Apellido"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "La dirección de correo electrónico no es válida. Por favor, vuelva a intentarlo.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "La dirección de correo electrónico que usó ya fue registrada previamente.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Confirmación de Registro");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Gracias por registrarse para participar en esta encuesta.<br /><br />\n"
                                   ."Un correo electrónico fue enviado a la dirección provista por usted con detalles de acceso "
                                   ."para esta encuesta. Por favor, siga el enlace de dicho correo para continuar.<br /><br />\n"
                                   ."El administrador de la encuesta {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Gracias<br /><br />"
                       ."Usted respondió todas las preguntas de esta encuesta.</strong><br /><br />"
                       ."Pulse en ["._SUBMIT."] para completar el proceso y salvar sus respuestas."); //New for 0.98finalRC1
define("_SM_REVIEW", "Si quiere revisar alguna de sus respuestas, y/o cambiarlas, "
                    ."puede hacerlo ahora, haciendo un click en el botón [<< "._PREV."] y examinando "
                    ."sus respuestas."); //New for 0.98finalRC1

//For the "printable" survey
define("_PS_CHOOSEONE", "Por favor, elija <strong>sólo una</strong> de las siguientes entradas:"); //New for 0.98finalRC1
define("_PS_WRITE", "Por favor, escriba su respuesta aquí:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Por favor, marque <strong>todas</strong> las que correspondan:"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Por favor, elija todas las que correspondan y escriba un comentario:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Por favor, elija la respuesta apropiada para cada entrada:"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Por favor, escriba su(s) respuesta(s) aquí:"); //New for 0.98finalRC1
define("_PS_DATE", "Por favor, introduzca la fecha:"); //New for 0.98finalRC1
define("_PS_COMMENT", "Comente su opción aquí:"); //New for 0.98finalRC1
define("_PS_RANKING", "Por favor, enumere cada cuadrito en orden de preferencia desde 1 a"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Enviar su encuesta."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Gracias por completar esta encuesta."); //New for 0.98finalRC1
define("_PS_FAXTO", "Por favor, envíe un fax con su encuesta completada a:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Sólo responda esta pregunta"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "si usted respondió"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "y"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "a la pregunta"); //New for 0.98finalRC1
define("_PS_CON_OR", "o"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Guardar las respuestas realizadas hasta ahora");
define("_SAVEHEADING", "Guardar una encuesta no terminada");
define("_RETURNTOSURVEY", "Volver a la encuesta");
define("_SAVENAME", "Nombre");
define("_SAVEPASSWORD", "Contraseña");
define("_SAVEPASSWORDRPT", "Repita la contraseña");
define("_SAVE_EMAIL", "Su correo electrónico");
define("_SAVEEXPLANATION", "Introduzca un nombre y contraseña para esta encuesta y pulse en guardar abajo.<br />\n"
                  ."Su encuesta se guardará usando ese nombre y contraseña, y puede  "
                  ."ser completada mas tarde ingresando con dicho nombre y contraseña.<br /><br />\n"
                  ."Si ingresa una dirección de correo electrónico, le enviaremos un correo con los detalles.");
define("_SAVESUBMIT", "Guardar Ahora");
define("_SAVENONAME", "Usted debe proporcionar un nombre para esta sesión guardada.");
define("_SAVENOPASS", "Usted debe proporcionar una contraseña para esta sesión guardada.");
define("_SAVENOMATCH", "Sus contraseñas no concuerdan.");
define("_SAVEDUPLICATE", "Este nombre ya ha sido utilizado para esta encuesta. Debe utilizar un nombre único para guardar.");
define("_SAVETRYAGAIN", "Por favor, vuelva a intentarlo.");
define("_SAVE_EMAILSUBJECT", "Detalles de la encuesta guardada");
define("_SAVE_EMAILTEXT", "Usted, o alguien utilizando su dirección de correo electrónico, ha guardado "
                         ."una encuesta en proceso. Los siguientes detalles pueden ser usados "
                         ."para recuperar a dicha encuesta y continuarla.");
define("_SAVE_EMAILURL", "Recargue su encuesta haciendo click en la siguiente URL:");
define("_SAVE_SUCCEEDED", "Sus respuestas a esta encuesta fueron guardadas con éxito");
define("_SAVE_FAILED", "Se ha producido un error, y sus respuestas no fueron guardadas.");
define("_SAVE_EMAILSENT", "Un correo electrónico fue enviado con los detalles de su encuesta guardada.");

//Load Messages
define("_LOAD_SAVED", "Recuperar una encuesta no terminada");
define("_LOADHEADING", "Cargar una encuesta guardada previamente");
define("_LOADEXPLANATION", "Puede cargar una encuesta que usted ha guardado previamente desde esta pantalla.<br />\n"
              ."Escriba el 'Nombre' que usó para guardar la encuesta, y la contraseña.<br /><br />\n");
define("_LOADNAME", "Nombre guardado");
define("_LOADPASSWORD", "Contraseña");
define("_LOADSUBMIT", "Recuperar Ahora");
define("_LOADNONAME", "No ingresó el Nombre");
define("_LOADNOPASS", "No ingresó la contraseña");
define("_LOADNOMATCH", "No existen encuestas con dicho nombre");

define("_ASSESSMENT_HEADING", "Sus Evaluaciones");
?>
