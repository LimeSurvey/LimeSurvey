<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
	#############################################################
	# > Author:  Jason Cleeland									#
	# > E-mail:  jason@cleeland.org								#
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,			#
	# >          CARLTON SOUTH 3053, AUSTRALIA					#
	# > Date: 	 20 February 2003								#
	#															#
	# This set of scripts allows you to develop, publish and	#
	# perform data-entry on surveys.							#
	#############################################################
	#															#
	#	Copyright (C) 2003  Jason Cleeland						#
	#															#
	# This program is free software; you can redistribute 		#
	# it and/or modify it under the terms of the GNU General 	#
	# Public License as published by the Free Software 			#
	# Foundation; either version 2 of the License, or (at your 	#
	# option) any later version.								#
	#															#
	# This program is distributed in the hope that it will be 	#
	# useful, but WITHOUT ANY WARRANTY; without even the 		#
	# implied warranty of MERCHANTABILITY or FITNESS FOR A 		#
	# PARTICULAR PURPOSE.  See the GNU General Public License 	#
	# for more details.											#
	#															#
	# You should have received a copy of the GNU General 		#
	# Public License along with this program; if not, write to 	#
	# the Free Software Foundation, Inc., 59 Temple Place - 	#
	# Suite 330, Boston, MA  02111-1307, USA.					#
	#############################################################
	#															#
	# This language file kindly provided by Luis M. Martinez	#
	#															#
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
define("_OTHER", "Otro");
define("_PLEASECHOOSE", "Favor de elegir");
define("_ERROR", "Error");
define("_COMPLETE", "completo");
define("_INCREASE", "Aumento"); //NEW WITH 0.98 BABELFISH TRANSLATION
define("_SAME", "Iguales"); //NEW WITH 0.98 BABELFISH TRANSLATION
define("_DECREASE", "Disminución"); //NEW WITH 0.98 BABELFISH TRANSLATION
//from questions.php
define("_CONFIRMATION", "Confirmación");
define("_TOKEN", "Token");
define("_CONTINUE", "Continuar");

//BUTTONS
define("_ACCEPT", "Aceptar");
define("_PREV", "anterior");
define("_NEXT", "sig");
define("_LAST", "último");
define("_SUBMIT", "enviar");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Favor de elijir uno de los siguientes");
define("_ENTERCOMMENT", "Favor de teclear su comentario aquí");
define("_NUMERICAL", "Sólo se aceptan números en este campo");
define("_CLEARALL", "Salir y Limpiar Encuesta");
define("_MANDATORY", "Esta pregunta es requerida");
define("_MANDATORY_PARTS", "Favor de completar todas las partes");
define("_MANDATORY_CHECK", "Favor de seleccionar al menos un elemento");
define("_MANDATORY_RANK", "Favor de clasificar todos los elementos");
define("_MANDATORY_POPUP", "Unas o más preguntas obligatorias no se han contestado. Usted no puede proceder hasta que se han terminado éstos"); //NEW in 0.98rc4
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
define("_CLOSEWIN", "Cerrar esta Ventana");
define("_CONFIRMCLEAR", "¿Está seguro de eliminar todas sus respuestas?");
define("_EXITCLEAR", "Salir y Limpiar la Encuesta");
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

define("_SURVEYCOMPLETE", "Usted ha terminado ya este examen.");

define("_INSTRUCTION_LIST", "Choose only one of the following"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Check any that apply"); //NEW for 098rc3

?>
