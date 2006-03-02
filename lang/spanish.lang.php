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
    #                                                           #
    #############################################################
*/
//SINGLE WORDS
define("_YES", "Sí");
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
define("_DECREASE", "Disminución"); //NEW WITH 0.98 BABELFISH TRANSLATION
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Confirmación");
define("_TOKEN_PS", "Token");
define("_CONTINUE_PS", "Continuar");

//BUTTONS
define("_ACCEPT", "Aceptar");
define("_PREV", "anterior");
define("_NEXT", "sig");
define("_LAST", "Último");
define("_SUBMIT", "enviar");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Favor de elijir uno de los siguientes");
define("_ENTERCOMMENT", "Favor de teclear su comentario aquí");
define("_NUMERICAL_PS", "Sólo se aceptan números en este campo");
define("_CLEARALL", "Salir y Aclarar la Encuesta"); //Better word choice from John Krikorian
define("_MANDATORY", "Esta pregunta es requerida");
define("_MANDATORY_PARTS", "Favor de completar todas las partes");
define("_MANDATORY_CHECK", "Favor de seleccionar al menos un elemento");
define("_MANDATORY_RANK", "Favor de clasificar todos los elementos");
define("_MANDATORY_POPUP", "Una o más preguntas obligatorias no se han contestado. Usted no puede proceder hasta que se hayan terminado Éstos"); //NEW in 0.98rc4 - Mod by John Krikorian
define("_VALIDATION", "Esta pregunta debe ser contestada correctamente"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "Una o más preguntas no fueron contestadas de forma correcta. Usted no puede proceder hasta que dichas respuestas sean válidas."); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Formato: AAAA-MM-DD");
define("_DATEFORMATEG", "(pej: 2003-12-25 para Navidad)");
define("_REMOVEITEM", "Eliminar este elemento");
define("_RANK_1", "Haga click en un elemento de la lista de la izquierda, empezando por el");
define("_RANK_2", "elemento con más alta clasificación hasta llegar al elemento con más baja clasificación.");
define("_YOURCHOICES", "Sus Opciones");
define("_YOURRANKING", "Su Clasificación");
define("_RANK_3", "Haga click en las tijeras de la derecha de cada elemento");
define("_RANK_4", "para eliminar la última captura de su lista clasificada");
//From INDEX.PHP
define("_NOSID", "No ha proporcionado un número identificador de encuesta");
define("_CONTACT1", "Favor de contactar a");
define("_CONTACT2", "para más asistencia");
define("_ANSCLEAR", "Respuestas quitadas");
define("_RESTART", "Reiniciar la Encuesta");
define("_CLOSEWIN_PS", "Cerrar esta Ventana");
define("_CONFIRMCLEAR", "¿Está seguro de eliminar todas sus respuestas?");
define("_CONFIRMSAVE", "¿Está seguro que desea salvar sus respuestas?");
define("_EXITCLEAR", "Salir y Borrar la Encuesta"); //Mod by John Krikorian
//From QUESTION.PHP
define("_BADSUBMIT1", "No se pueden enviar los resultados - no hay resultados por enviar.");
define("_BADSUBMIT2", "Este error puede ocurrir si envió sus respuestas y presionó 'renovar' en su navegador. En este caso, sus respuestas ya fueron guardadas.");
define("_NOTACTIVE1", "Sus respuestas no han sido guardadas porque la Encuesta no ha sido activada aún.");
define("_CLEARRESP", "Inicializar Respuestas");
define("_THANKS", "Gracias");
define("_SURVEYREC", "Sus respuestas han sido guardadas.");
define("_SURVEYCPL", "Encuesta Completada");
define("_DIDNOTSAVE", "No se guardó");
define("_DIDNOTSAVE2", "Ha ocurrido un error inesperado y sus respuestas no han podido ser guardadas.");
define("_DIDNOTSAVE3", "Sus respuestas no se han perdido y han sido enviadas por correo electrónico al administrador de la encuesta para ser capturadas en nuestra base de datos posteriormente.");
define("_DNSAVEEMAIL1", "Ha sucedido un error al guardar una respuesta de la encuesta identificada con");
define("_DNSAVEEMAIL2", "DATOS PARA SER CAPTURADOS");
define("_DNSAVEEMAIL3", "EL CODIGO SQL HA FALLADO");
define("_DNSAVEEMAIL4", "MENSAJE DE ERROR");
define("_DNSAVEEMAIL5", "ERROR GUARDANDO");
define("_SUBMITAGAIN", "Reintente enviar otra vez");
define("_SURVEYNOEXIST", "Lo sentimos. No hay encuestas que coincidan.");
define("_NOTOKEN1", "Esta encuesta tiene control de acceso. Necesita un token válido para participar.");
define("_NOTOKEN2", "Si se le ha proporcionado un token, favor de teclearlo en la caja de abajo y hacer click en continuar.");
define("_NOTOKEN3", "El token que se le ha proporcionado no es válido o ya fue usado.");
define("_NOQUESTIONS", "Esta encuesta todavía no tiene preguntas y no puede ser probada ni completada.");
define("_FURTHERINFO", "Para más información contactar a");
define("_NOTACTIVE", "Esta encuesta no está activa. No podrá guardar sus respuestas.");
define("_SURVEYEXPIRED", "Esta encuesta no está disponible."); //NEW for 098rc5
define("_SURVEYCOMPLETE", "Usted ha terminado ya este examen.");
define("_INSTRUCTION_LIST", "Elija solamente uno del siguiente"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Compruebe cualquiera que se aplica"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Examen Sometido"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Una nueva respuesta fue incorporada para su examen"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Haga click en el siguiente link para ver la respuesta individual:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Vea las estadísticas haciendo click aquí:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Haga click en el siguiente link para editar las respuestas individuales:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>Una nota sobre privacidad.</i></strong><br />"
        ."Este examen es anónimo.<br />"
        ."No se guardará ninguna información sobre usted."); //New for 0.98rc9 - Translation by John Krikorian

define("_THEREAREXQUESTIONS", "Hay {NUMBEROFQUESTIONS} preguntas en este examen."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count. - Translation by John Krikorian
define("_THEREAREXQUESTIONS_SINGLE", "Hay una pregunta en este examen."); //New for 0.98rc9 - singular version of above - Translation by John Krikorian

define ("_RG_REGISTER1", "Debe estar registrado para completar esta encuesta"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Puede registrarse para esta encuesta si desea participar.<br />\n"
                        ."Ingrese sus datos abajo, y le enviaremos inmediatamente un correo electrónico conteniendo "
                        ."el link para participar en esta encuesta."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Correo Electónico"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Nombre"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Apellido"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "La dirección de correo electrónico no es válido. Por favor, vuelva a intentar.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "La dirección de correo electrónico que usó ya fue registrada previamente.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Confirmación de Registro");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Gracias por registrarse para participar en esta encuesta.<br /><br />\n"
                                   ."Un correo electrónico fue enviado a la dirección provista por usted con detalles de acceso "
                                   ."para esta encuesta. Por favor, siga el link en dicho correo para continuar.<br /><br />\n"
                                   ."Administrador de la Encuesta {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Gracias<br /><br />"
                       ."Usted respondió todas las preguntas en esta encuesta.</strong><br /><br />"
                       ."Haga click en ["._SUBMIT."] para completar el proceso y salvar sus respuestas."); //New for 0.98finalRC1
define("_SM_REVIEW", "Si quiere revisar alguna de sus respuestas, y/o cambiarlas, "
                    ."puede hacerlo ahora, haciendo un click en el botón [<< "._PREV."] y navegando "
                    ."a través de sus respuestas."); //New for 0.98finalRC1

//For the "printable" survey
define("_PS_CHOOSEONE", "Por favor, elija <strong>solo una</strong> de las siguientes:"); //New for 0.98finalRC1
define("_PS_WRITE", "Por favor, escriba su respuesta aquí:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Por favor, elija <strong>todas</strong> las que se apliquen:"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Por favor, elija todas las que se apliquen y provea un comentario:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Por favor, elija la respuesta apropiada para cada item:"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Por favor, escriba su(s) respuesta(s) aquí:"); //New for 0.98finalRC1
define("_PS_DATE", "Por favor, ingrese la fecha:"); //New for 0.98finalRC1
define("_PS_COMMENT", "Comente sobre su opción aquí:"); //New for 0.98finalRC1
define("_PS_RANKING", "Por favor, enumere cada cuadrito en orden de preferencia desde 1 a"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Enviar su Encuesta."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Gracias por completar esta encuesta."); //New for 0.98finalRC1
define("_PS_FAXTO", "Por favor, envíe un fax con su encuesta completada a:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Sólo responda esta pregunta"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "si usted respondió"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "y"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "a la pregunta"); //New for 0.98finalRC1
define("_PS_CON_OR", "o"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Salvar sus respuestas hasta ahora");
define("_SAVEHEADING", "Salvar su Encuesta No Terminada");
define("_RETURNTOSURVEY", "Volver a la Encuesta");
define("_SAVENAME", "Nombre");
define("_SAVEPASSWORD", "Contraseña");
define("_SAVEPASSWORDRPT", "Repita la Contraseña");
define("_SAVE_EMAIL", "Su Correo Electrónico");
define("_SAVEEXPLANATION", "Ingrese un nombre y contraseña para esta encuesta y haga click en salvar abajo.<br />\n"
                  ."Su encuesta será salvada usando ese nombre y contraseña, y puede ser "
                  ."ser completada mas tarde ingresando con dicho nombre y contraseña.<br /><br />\n"
                  ."Si ingresa una dirección de correo electrónico, le enviaremos un correo conteniendo los detalles.");
define("_SAVESUBMIT", "Salvar Ahora");
define("_SAVENONAME", "Usted debe proporcionar un nombre para esta sesión salvada.");
define("_SAVENOPASS", "Usted debe proporcionar una contraseña para esta sesión salvada.");
define("_SAVENOMATCH", "Sus contraseñas no concuerdan.");
define("_SAVEDUPLICATE", "Este nombre ya ha sido utilizado para esta encuesta. Debe utilizar un nombre único para salvar.");
define("_SAVETRYAGAIN", "Por favor, vuelva a intentar.");
define("_SAVE_EMAILSUBJECT", "Detalles de Encuesta Salvada");
define("_SAVE_EMAILTEXT", "Usted, o alguien utilizando su direccó de correo electrónico, ha salvado "
                         ."una encuesta en proceso. Los siguientes detalles pueden ser usados "
                         ."para retornar a dicha encuesta y continuarla.");
define("_SAVE_EMAILURL", "Recargue su encuesta haciendo click en la siguiente URL:");
define("_SAVE_SUCCEEDED", "Sus respuestas a esta encuesta fueron salvadas con éxito");
define("_SAVE_FAILED", "Se ha producido un error, y sus respuestas no fueron salvadas.");
define("_SAVE_EMAILSENT", "Un correo electrónico fue enviado con los detalles de su encuesta salvada.");

//Load Messages
define("_LOAD_SAVED", "Cargar su encuesta no terminada");
define("_LOADHEADING", "Cargar una Encuesta Previamente Salvada");
define("_LOADEXPLANATION", "Usted puede cargar una encuesta que usted ha salvado previamente desde esta pantalla.<br />\n"
              ."Escriba el 'Nombre' que usó para salvar la encuesta, y la contraseña.<br /><br />\n");
define("_LOADNAME", "Nombre salvado");
define("_LOADPASSWORD", "Contraseña");
define("_LOADSUBMIT", "Cargar Ahora");
define("_LOADNONAME", "No ingresó el Nombre");
define("_LOADNOPASS", "No ingresó la contraseña");
define("_LOADNOMATCH", "No existen encuestas con dicho nombre");

define("_ASSESSMENT_HEADING", "Sus Evaluaciones");
?>
