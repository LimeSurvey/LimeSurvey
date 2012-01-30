<?php
/*
    #################################################################
    # >>> PHPSurveyor                                               #
    #################################################################
    # > Author:  Jason Cleeland                                     #
    # > E-mail:  jason@cleeland.org                                 #
    # > Mail:    Box 99, Trades Hall, 54 Victoria St,               #
    # >          CARLTON SOUTH 3053, AUSTRALIA                      #
    # > Date:    20 February 2003                                   #
    #                                                               #
    # This set of scripts allows you to develop, publish and        #
    # perform data-entry on surveys.                                #
    #################################################################
    #   Copyright (C) 2003  Jason Cleeland                          #
    #                                                               #
    # This program is free software; you can redistribute           #
    # it and/or modify it under the terms of the GNU General        #
    # Public License as published by the Free Software              #
    # Foundation; either version 2 of the License, or (at your      #
    # option) any later version.                                    #
    #                                                               #
    # This program is distributed in the hope that it will be       #
    # useful, but WITHOUT ANY WARRANTY; without even the            #
    # implied warranty of MERCHANTABILITY or FITNESS FOR A          #
    # PARTICULAR PURPOSE.  See the GNU General Public License       #
    # for more details.                                             #
    #                                                               #
    # You should have received a copy of the GNU General            #
    # Public License along with this program; if not, write to      #
    # the Free Software Foundation, Inc., 59 Temple Place -         #
    # Suite 330, Boston, MA  02111-1307, USA.                       #
    #################################################################
    #  TRANSLATION PROVIDED BY JOSE LUIS RAMIREZ					#
    #   								 							#
    #  																#
    # Translation reviewed by Juan Rafael Fernández, may 2006			#
    #                                                               #
    #  Edit this file with an UTF-8 capable editor only!            #
    #                                                               #
    #################################################################
*/


//BUTTON BAR TITLES
define("_ADMINISTRATION", "Administración");
define("_SURVEY", "Encuesta");
define("_GROUP", "Grupo");
define("_QUESTION", "Pregunta");
define("_ANSWERS", "Respuestas");
define("_CONDITIONS", "Condiciones");
define("_HELP", "Ayuda");
define("_USERCONTROL", "Control de Usuarios");
define("_ACTIVATE", "Activar Encuesta");
define("_DEACTIVATE", "Desactivar Encuesta");
define("_CHECKFIELDS", "Revisar Campos de la Base de Datos");
define("_CREATEDB", "Crear Base de Datos");
define("_CREATESURVEY", "Crear Encuesta"); //New for 0.98rc4
define("_SETUP", "Configuración de PHPSurveyor");
define("_DELETESURVEY", "Eliminar Encuesta");
define("_EXPORTQUESTION", "Exportar Pregunta");
define("_EXPORTSURVEY", "Exportar Encuesta");
define("_EXPORTLABEL", "Exportar Etiquetas");
define("_IMPORTQUESTION", "Importar Pregunta");
define("_IMPORTGROUP", "Import Group"); //New for 0.98rc5
define("_IMPORTSURVEY", "Importar Encuesta");
define("_IMPORTLABEL", "Importar Etiquetas");
define("_EXPORTRESULTS", "Exportar Respuestas");
define("_BROWSERESPONSES", "Examinar las respuestas");
define("_BROWSESAVED", "Examinar las respuestas guardadas");
define("_STATISTICS", "Estadísticas Rápidas");
define("_VIEWRESPONSE", "Ver Respuesta");
define("_VIEWCONTROL", "Vista de Control de Datos");
define("_DATAENTRY", "Entrada de Datos");
define("_TOKENCONTROL", "Control de Tokens");
define("_TOKENDBADMIN", "Opciones de la Base de Datos de los Tokens");
define("_DROPTOKENS", "Eliminar Tabla de Tokens");
define("_EMAILINVITE", "Enviar Invitación (por email)");
define("_EMAILREMIND", "Enviar Recordatorio (por email)");
define("_TOKENIFY", "Crear Tokens");
define("_UPLOADCSV", "Subir Archivo CSV");
define("_LABELCONTROL", "Administración de Etiquetas"); //NEW with 0.98rc3
define("_LABELSET", "Conjunto de Etiquetas"); //NEW with 0.98rc3
define("_LABELANS", "Etiquetas"); //NEW with 0.98rc3
define("_OPTIONAL", "Opcional"); //NEW with 0.98finalRC1

//DROPDOWN HEADINGS
define("_SURVEYS", "Encuestas");
define("_GROUPS", "Grupos");
define("_QUESTIONS", "Preguntas");
define("_QBYQ", "Pregunta por Pregunta");
define("_GBYG", "Grupo por Grupo");
define("_SBYS", "Todos en Uno");
define("_LABELSETS", "Etiquetas"); //New with 0.98rc3

//BUTTON MOUSEOVERS
//administration bar
define("_A_HOME_BT", "Inicio de Página de Administración");
define("_A_SECURITY_BT", "Modificar Opciones de Seguridad");
define("_A_BADSECURITY_BT", "Activar Seguridad");
define("_A_CHECKDB_BT", "Revisar la Base de Datos");
define("_A_DELETE_BT", "Borrar la Encuesta");
define("_A_ADDSURVEY_BT", "Crear o Importar una Nueva Encuesta");
define("_A_HELP_BT", "Mostrar Ayuda");
define("_A_CHECKSETTINGS", "Revisar Opciones del Sistema");
define("_A_BACKUPDB_BT", "Hacer copia de seguridad de toda la base de datos"); //New for 0.98rc10
define("_A_TEMPLATES_BT", "Editor de plantillas"); //New for 0.98rc9
//Survey bar
define("_S_ACTIVE_BT", "Esta encuesta está actualmente activa");
define("_S_INACTIVE_BT", "Esta encuesta actualmente NO está activa");
define("_S_ACTIVATE_BT", "Activar esta Encuesta");
define("_S_DEACTIVATE_BT", "Desactivar esta Encuesta");
define("_S_CANNOTACTIVATE_BT", "No se puede activar esta Encuesta");
define("_S_DOSURVEY_BT", "Contestar la Encuesta");
define("_S_DATAENTRY_BT", "Pantalla de Entrada de Datos para la Encuesta");
define("_S_PRINTABLE_BT", "Versión Imprimible de la Encuesta");
define("_S_EDIT_BT", "Modificar Encuesta Seleccionada");
define("_S_DELETE_BT", "Borrar Encuesta Seleccionada");
define("_S_EXPORT_BT", "Exportar esta Encuesta");
define("_S_BROWSE_BT", "Examinar las Respuestas a esta encuesta");
define("_S_TOKENS_BT", "Activar/Modificar Tokens de esta Encuesta");
define("_S_ADDGROUP_BT", "Agregar Nuevo Grupo a la Encuesta");
define("_S_MINIMISE_BT", "Ocultar Detalles de esta Encuesta");
define("_S_MAXIMISE_BT", "Mostrar Detalles de esta Encuesta");
define("_S_CLOSE_BT", "Cerrar esta Encuesta");
define("_S_SAVED_BT", "Ver las respuestas guardadas pero no enviadas"); //New in 0.99dev01
define("_S_ASSESSMENT_BT", "Fijar reglas de evaluación"); //New in  0.99dev01
//Group bar
define("_G_EDIT_BT", "Modificar Grupo Seleccionado");
define("_G_EXPORT_BT", "Exportar el grupo actual"); //New in 0.98rc5
define("_G_DELETE_BT", "Borrar Grupo Seleccionado");
define("_G_ADDQUESTION_BT", "Agregar Nueva Pregunta al Grupo");
define("_G_MINIMISE_BT", "Ocultar Detalles de este Grupo");
define("_G_MAXIMISE_BT", "Mostrar Detalles de este Grupo");
define("_G_CLOSE_BT", "Cerrar este Grupo");
//Question bar
define("_Q_EDIT_BT", "Modificar Pregunta Seleccionada");
define("_Q_COPY_BT", "Copiar Pregunta Seleccionada"); //New in 0.98rc4
define("_Q_DELETE_BT", "Borrar Pregunta Seleccionada");
define("_Q_EXPORT_BT", "Exportar esta Pregunta");
define("_Q_CONDITIONS_BT", "Establecer Condiciones para esta Pregunta");
define("_Q_ANSWERS_BT", "Modificar/Agregar Respuestas para esta Pregunta");
define("_Q_LABELS_BT", "Modificar/Agregar Etiquetas");
define("_Q_MINIMISE_BT", "Ocultar Detalles para esta Pregunta");
define("_Q_MAXIMISE_BT", "Mostrar Detalles para esta Pregunta");
define("_Q_CLOSE_BT", "Cerrar esta Pregunta");
//Browse Button Bar
define("_B_ADMIN_BT", "Regresar a la Administración de Encuestas");
define("_B_SUMMARY_BT", "Mostrar información");
define("_B_ALL_BT", "Mostrar Todas las Contestaciones");
define("_B_LAST_BT", "Mostrar últimas 50 Contestaciones");
define("_B_STATISTICS_BT", "Generar estadísticas a partir de estas contestaciones");
define("_B_EXPORT_BT", "Exportar Resultados a alguna Aplicación");
define("_B_BACKUP_BT", "Respaldar la tabla de resultados como archivo SQL");
//Tokens Button Bar
define("_T_ALL_BT", "Mostrar Tokens");
define("_T_ADD_BT", "Agregar un nuevo Token");
define("_T_IMPORT_BT", "Importar Tokens desde Archivo CSV");
define("_T_EXPORT_BT", "Exportar Tokens a un fichero CSV"); //New for 0.98rc7
define("_T_INVITE_BT", "Enviar Invitación (por email)");
define("_T_REMIND_BT", "Enviar Recordatorio (por email)");
define("_T_TOKENIFY_BT", "Generar Tokens");
define("_T_KILL_BT", "Borrar Tabla de Tokens");
//Labels Button Bar
define("_L_ADDSET_BT", "Agregar Etiquetas");
define("_L_EDIT_BT", "Modificar Etiquetas");
define("_L_DEL_BT", "Borrar Etiquetas");
//Datacontrols
define("_D_BEGIN", "Mostrar inicio..");
define("_D_BACK", "Mostrar anterior..");
define("_D_FORWARD", "Mostrar siguiente..");
define("_D_END", "Mostrar última..");

//DATA LABELS
//surveys
define("_SL_TITLE", "Título:");
define("_SL_SURVEYURL", "URL de la Encuesta:"); //new in 0.98rc5
define("_SL_DESCRIPTION", "Descripción:");
define("_SL_WELCOME", "Bienvenida:");
define("_SL_ADMIN", "Administrador:");
define("_SL_EMAIL", "Correo electrónico del administrador:");
define("_SL_FAXTO", "Nº Fax:");
define("_SL_ANONYMOUS", "¿Anónimo?");
define("_SL_EXPIRYDATE", "Expiración:");
define("_SL_FORMAT", "Formato:");
define("_SL_DATESTAMP", "¿Marcar la fecha?");
define("_SL_IPADDRESS", "Dirección IP"); //New with 0.991
define("_SL_TEMPLATE", "Plantilla:");
define("_SL_LANGUAGE", "Idioma:");
define("_SL_LINK", "Enlace:");
define("_SL_URL", "URL de salida:");
define("_SL_URLDESCRIP", "Texto del URL:");
define("_SL_STATUS", "Estado:");
define("_SL_SELSQL", "Seleccionar Archivo SQL:");
define("_SL_USECOOKIES", "¿Utilizar Cookies?"); //NEW with 098rc3
define("_SL_NOTIFICATION", "Notificación:"); //New with 098rc5
define("_SL_ALLOWREGISTER", "¿Permitir inscripción pública?"); //New with 0.98rc9
define("_SL_ATTRIBUTENAMES", "Nombres de los atributos de los Tokens:"); //New with 0.98rc9
define("_SL_EMAILINVITE_SUBJ", "Asunto del correo de invitación:"); //New with 0.99dev01
define("_SL_EMAILINVITE", "Mensaje de correo de invitación:"); //New with 0.98rc9
define("_SL_EMAILREMIND_SUBJ", "Asunto del mensaje recordatorio:"); //New with 0.99dev01
define("_SL_EMAILREMIND", "Mensaje recordatorio:"); //New with 0.98rc9
define("_SL_EMAILREGISTER_SUBJ", "Asunto del correo de registro público:"); //New with 0.99dev01
define("_SL_EMAILREGISTER", "Mensaje de inscripción pública:"); //New with 0.98rc9
define("_SL_EMAILCONFIRM_SUBJ", "Asunto del mensaje de confirmación"); //New with 0.99dev01
define("_SL_EMAILCONFIRM", "Mensaje de confirmación"); //New with 0.98rc9
define("_SL_REPLACEOK", "Esto sustituirá al texto actual. ¿Proceder?"); //New with 0.98rc9
define("_SL_ALLOWSAVE", "¿Confirmar Guardar?"); //New with 0.99dev01
define("_SL_AUTONUMBER", "Comenzar los números de ID a partir de:"); //New with 0.99dev01
define("_SL_AUTORELOAD", "¿Cargar la URL automáticamente cuando se haya completado la encuesta?"); //New with 0.99dev01
define("_SL_ALLOWPREV", "Mostrar el botón [<< Anterior]"); //New with 0.99dev01
define("_SL_USE_DEFAULT","Usar los valores por defecto");
define("_SL_UPD_SURVEY","Actualizar la encuesta");

//groups
define("_GL_TITLE", "Título:");
define("_GL_DESCRIPTION", "Descripción:");
define("_GL_EDITGROUP", "Editar Grupo para la Encuesta con ");
define("_GL_UPDATEGROUP", "Actualizar Grupo");
//questions
define("_QL_EDITQUESTION", "Editar Pregunta");// New with 0.99dev02
define("_QL_UPDATEQUESTION", "Actualizar Pregunta");// New with 0.99dev02
define("_QL_CODE", "Código:");
define("_QL_QUESTION", "Pregunta:");
define("_QL_VALIDATION", "Validación:"); //New in VALIDATION VERSION
define("_QL_HELP", "Ayuda:");
define("_QL_TYPE", "Tipo:");
define("_QL_GROUP", "Grupo:");
define("_QL_MANDATORY", "Obligatoria:");
define("_QL_OTHER", "Otro:");
define("_QL_LABELSET", "Etiquetas:");
define("_QL_COPYANS", "¿Copiar Respuestas?"); //New in 0.98rc3
define("_QL_QUESTIONATTRIBUTES", "Atributos de la pregunta:"); //New in 0.99dev01
define("_QL_COPYATT", "¿Copiar los atributos?"); //New in 0.99dev01
//answers
define("_AL_CODE", "Código");
define("_AL_ANSWER", "Respuesta");
define("_AL_DEFAULT", "Por defecto");
define("_AL_MOVE", "Mover");
define("_AL_ACTION", "Acción");
define("_AL_UP", "Arriba");
define("_AL_DN", "Abajo");
define("_AL_SAVE", "Guardar");
define("_AL_DEL", "Borrar");
define("_AL_ADD", "Agregar");
define("_AL_FIXSORT", "Corregir Orden");
define("_AL_SORTALPHA", "Ordenar Alfabéticamente"); //New in 0.98rc8 - Sort Answers Alphabetically
//users
define("_UL_USER", "Usuario");
define("_UL_PASSWORD", "Contraseña");
define("_UL_SECURITY", "Seguridad");
define("_UL_ACTION", "Acción");
define("_UL_EDIT", "Modificar");
define("_UL_DEL", "Borrar");
define("_UL_ADD", "Agregar");
define("_UL_TURNOFF", "Desactivar Seguridad");
//tokens
define("_TL_FIRST", "Nombre");
define("_TL_LAST", "Apellidos");
define("_TL_EMAIL", "Email");
define("_TL_TOKEN", "Token");
define("_TL_INVITE", "¿Invitación enviada?");
define("_TL_DONE", "¿Completada?");
define("_TL_ACTION", "Acciones");
define("_TL_ATTR1", "Atributo_1"); //New for 0.98rc7
define("_TL_ATTR2", "Atributo_2"); //New for 0.98rc7
define("_TL_MPID", "MPID"); //New for 0.98rc7
//labels
define("_LL_NAME", "Nombre del Conjunto"); //NEW with 098rc3
define("_LL_CODE", "Código"); //NEW with 098rc3
define("_LL_ANSWER", "Título"); //NEW with 098rc3
define("_LL_SORTORDER", "Orden"); //NEW with 098rc3
define("_LL_ACTION", "Acción"); //New with 098rc3

//QUESTION TYPES
define("_5PT", "Elegir entre 5 Puntos");
define("_DATE", "Fecha");
define("_GENDER", "Género");
define("_LIST", "Lista (Radio)"); //Changed with 0.99dev01
define("_LIST_DROPDOWN", "Lista (Dropdown)"); //New with 0.99dev01
define("_LISTWC", "Lista con Comentarios");
define("_MULTO", "Opción Múltiple");
define("_MULTOC", "Opción Múltiple con Comentarios");
define("_MULTITEXT", "Múltiples Textos Cortos");
define("_NUMERICAL", "Entrada Numérica");
define("_RANK", "Ordenar/Fila");
define("_STEXT", "Texto corto");
define("_LTEXT", "Texto largo");
define("_HTEXT", "Texto libre enorme"); //New with 0.99dev01
define("_YESNO", "Sí/No");
define("_ARR5",  "Arreglo (Elegir entre 5 Puntos)");
define("_ARR10", "Arreglo (Elegir entre 10 Puntos)");
define("_ARRYN", "Arreglo (Sí/No/Incierto)");
define("_ARRMV", "Arreglo (Ampliar, Mantener, Reducir)");
define("_ARRFL", "Arreglo (Etiquetas Flexibles)"); //Release 0.98rc3
define("_ARRFLC", "Array (Etiquetas Flexibles) por Columna"); //Release 0.98rc8
define("_SINFL", "Sencillo (Etiquetas Flexibles)"); //(FOR LATER RELEASE)
define("_EMAIL", "Correo electrónico"); //FOR LATER RELEASE
define("_BOILERPLATE", "Pregunta estándar"); //New in 0.98rc6
define("_LISTFL_DROPDOWN", "Lista (Etiquetas Flexibles) (Lista desplegable)"); //New in 0.99dev01
define("_LISTFL_RADIO", "Lista (Etiquetas Flexibles) (Radio)"); //New in 0.99dev01
define("_SLIDER", "control deslizador"); //New for slider mod

//GENERAL WORDS AND PHRASES
define("_AD_YES", "Sí");
define("_AD_NO", "No");
define("_AD_CANCEL", "Cancelar");
define("_AD_CHOOSE", "Elegir..");
define("_AD_OR", "o"); //New in 0.98rc4
define("_ERROR", "Error");
define("_SUCCESS", "éxito");
define("_REQ", "*Obligatorio");
define("_ADDS", "Agregar Encuesta");
define("_ADDG", "Agregar Grupo");
define("_ADDQ", "Agregar Pregunta");
define("_ADDA", "Agregar Respuesta"); //New in 0.98rc4
define("_COPYQ", "Copiar Pregunta"); //New in 0.98rc4
define("_ADDU", "Agregar Usuario");
define("_SEARCH", "Buscar"); //New in 0.98rc4
define("_SAVE", "Guardar Cambios");
define("_NONE", "Ninguno"); //as in "Do not display anything", "or none chosen";
define("_GO_ADMIN", "Pantalla de Administración Principal"); //text to display to return/display main administration screen
define("_CONTINUE", "Continuar");
define("_WARNING", "Advertencia");
define("_USERNAME", "Nombre de Usuario");
define("_PASSWORD", "Contraseña");
define("_DELETE", "Borrar");
define("_CLOSEWIN", "Cerrar Ventana");
define("_TOKEN", "Token");
define("_DATESTAMP", "Fecha"); //Referring to the datestamp or time response submitted
define("_IPADDRESS", "Dirección IP"); //Referring to the ip address of the submitter - New with 0.991
define("_COMMENT", "Comentario");
define("_FROM", "De"); //For emails
define("_SUBJECT", "Asunto"); //For emails
define("_MESSAGE", "Mensaje"); //For emails
define("_RELOADING", "Recargando la pantalla. Por favor espere.");
define("_ADD", "Agregar");
define("_UPDATE", "Actualizar");
define("_BROWSE", "Navegar"); //New in 098rc5
define("_AND", "y"); //New with 0.98rc8
define("_SQL", "SQL"); //New with 0.98rc8
define("_PERCENTAGE", "Porcentaje"); //New with 0.98rc8
define("_COUNT", "Cuenta"); //New with 0.98rc8

//SURVEY STATUS MESSAGES (new in 0.98rc3)
define("_SS_NOGROUPS", "Número de grupos en la encuesta:"); //NEW for release 0.98rc3
define("_SS_NOQUESTS", "Número de preguntas en la encuesta:"); //NEW for release 0.98rc3
define("_SS_ANONYMOUS", "Esta encuesta es anónima."); //NEW for release 0.98rc3
define("_SS_TRACKED", "Esta encuesta NO es anónima."); //NEW for release 0.98rc3
define("_SS_DATESTAMPED", "Las respuestas tendrán estampado de fecha"); //NEW for release 0.98rc3
define("_SS_IPADDRESS", "La Dirección IP será guardada en un fichero de registro"); //New with 0.991
define("_SS_COOKIES", "Utiliza \"cookies\" para el control de acceso."); //NEW for release 0.98rc3
define("_SS_QBYQ", "Presentación pregunta por pregunta."); //NEW for release 0.98rc3
define("_SS_GBYG", "Presentación por grupos."); //NEW for release 0.98rc3
define("_SS_SBYS", "Presentación en una sola página."); //NEW for release 0.98rc3
define("_SS_ACTIVE", "La encuesta está activa."); //NEW for release 0.98rc3
define("_SS_NOTACTIVE", "La encuesta NO está activa."); //NEW for release 0.98rc3
define("_SS_SURVEYTABLE", "El nombre de la tabla de la encuesta es:"); //NEW for release 0.98rc3
define("_SS_CANNOTACTIVATE", "La encuesta no puede ser activada aún."); //NEW for release 0.98rc3
define("_SS_ADDGROUPS", "Debe agregar grupos"); //NEW for release 0.98rc3
define("_SS_ADDQUESTS", "Debe agregar preguntas"); //NEW for release 0.98rc3
define("_SS_ALLOWREGISTER", "Si se utilizan tokens, el público puede darse de alta en esta encuesta"); //NEW for release 0.98rc9
define("_SS_ALLOWSAVE", "Los participantes pueden guardar encuestas parcialmente terminadas"); //NEW for release 0.99dev01

//QUESTION STATUS MESSAGES (new in 0.98rc4)
define("_QS_MANDATORY", "Pregunta obligatoria"); //New for release 0.98rc4
define("_QS_OPTIONAL", "Pregunta opcional"); //New for release 0.98rc4
define("_QS_NOANSWERS", "Debe añadir respuestas a esta pregunta"); //New for release 0.98rc4
define("_QS_NOLID", "Debe seleccionar un conjunto de etiquetas para esta pregunta"); //New for release 0.98rc4
define("_QS_COPYINFO", "Nota: Debe introducir un código de pregunta nuevo"); //New for release 0.98rc4

//General Setup Messages
define("_ST_NODB1", "La base de datos del sistema no existe");
define("_ST_NODB2", "O la base de datos seleccionada no ha sido creada o hay problemas para acceder a ella.");
define("_ST_NODB3", "PHPSurveyor puede intentar crear la base de datos por usted.");
define("_ST_NODB4", "El nombre de la base de datos seleccionada es:");
define("_ST_CREATEDB", "Crear la base de datos");

//USER CONTROL MESSAGES
define("_UC_CREATE", "Creando archivo .htaccess por defecto");
define("_UC_NOCREATE", "No se pudo crear el archivo .htaccess. Revisar el valor de \$homedir en el archivo config.php, y que tenga permisos de escritura.");
define("_UC_SEC_DONE", "¡Se han establecido los niveles de seguridad!");
define("_UC_CREATE_DEFAULT", "Creando usuarios por defecto");
define("_UC_UPDATE_TABLE", "Actualizando tabla de usuarios");
define("_UC_HTPASSWD_ERROR", "Ocurrió un error al crear el archivo .htpasswd");
define("_UC_HTPASSWD_EXPLAIN", "Si está utilizando un servidor Windows es recomendable que copie el archivo htpasswd.exe de apache al directorio admin para que esta función funcione correctamente.");
define("_UC_SEC_REMOVE", "Quitando opciones de seguridad");
define("_UC_ALL_REMOVED", "Archivos de acceso, de contraseñas y base de datos de usuarios borrados");
define("_UC_ADD_USER", "Añadiendo Usuario");
define("_UC_ADD_MISSING", "No se pudo añadir el usuario. Faltan el Nombre de Usuario y/o la Contraseña");
define("_UC_DEL_USER", "Borrando el Usuario");
define("_UC_DEL_MISSING", "No se pudo borrar el usuario. Faltó el Nombre de Usuario.");
define("_UC_MOD_USER", "Modificando el Usuario");
define("_UC_MOD_MISSING", "No se pudo modificar el usuario. Faltaron Nombre de Usuario y/o Contraseña");
define("_UC_TURNON_MESSAGE1", "Aún no ha inicializado las opciones de seguridad para el sistema de encuestas y subsecuentemente no hay restricciones de acceso .</p>\nSi da clic en el botón de 'Inicializar Seguridad' de abajo, opciones estándar de seguridad de APACHE serán agregadas al directorio de administración del sistema. Y entonces deberá utilizar los valores por defecto de nombre de usuario y contraseña para accesar la pantalla de administración.");
define("_UC_TURNON_MESSAGE2", "Se recomienda que una vez que haya inicializado el sistema de seguridad cambie la contraseña por defecto.");
define("_UC_INITIALISE", "Inicializar seguridad");
define("_UC_NOUSERS", "No existen usuarios en su tabla. Se recomienda desactivar la seguridad para luego activarla nuevamente.");
define("_UC_TURNOFF", "Desactivar seguridad");

//Activate and deactivate messages
define("_AC_MULTI_NOANSWER", "Esta pregunta  es de múltiples respuestas pero aún no tiene respuestas asignadas a ella");
define("_AC_NOTYPE", "Esta pregunta aún no tiene asignado el 'tipo' de pregunta.");
define("_AC_NOLID", "Esta pregunta requiere de un conjunto de etiquetas, pero ninguno está listo."); //New for 0.98rc8
define("_AC_CON_OUTOFORDER", "Esta pregunta tiene asignada una condición, pero esta condición está basada en una pregunta que aparece después de ella.");
define("_AC_FAIL", "La encuesta no pasa la revisión de consistencia");
define("_AC_PROBS", "Se han encontrado los siguientes problemas:");
define("_AC_CANNOTACTIVATE", "La encuesta no podrá ser activada hasta que se hayan resuelto todos los problemas");
define("_AC_READCAREFULLY", "LEA CUIDADOSAMENTE ESTO ANTES DE CONTINUAR");
define("_AC_ACTIVATE_MESSAGE1", "Debe activar una encuesta sólo cuando esté absolutamente seguro(a) de que la configuración de la encuesta es la correcta y que no habrá más cambios.");
define("_AC_ACTIVATE_MESSAGE2", "Una vez que la encuesta es activada no podrá:<ul><li>Agregar o borrar Grupos</li><li>Agregar o borrar respuestas a preguntas de Opción Múltiple</li><li>Agregar o borrar preguntas</li></ul>");
define("_AC_ACTIVATE_MESSAGE3", "Aunque aún podrá:<ul><li>Modificar los códigos de las preguntas, texto o tipo</li><li>Modificar los nombres de los grupos</li><li>Agregar, borrar o modificar respuestas de respuestas predefinidas (exceptuando preguntas de múltiples respuestas)</li><li>Cambiar el nombre o la descripción de la encuesta</li></ul>");
define("_AC_ACTIVATE_MESSAGE4", "Si desea agregar o borra grupos o preguntas una vez que la encuesta ya haya sido contestada por lo menos una vez, deberá desactivar la encuesta, lo cual moverá toda la información ya existente a otra tabla para ser archivada.");
define("_AC_ACTIVATE", "Activar");
define("_AC_ACTIVATED", "La encuesta ha sido activada. La tabla de resultados ha sido creada con éxito.");
define("_AC_NOTACTIVATED", "La encuesta no pudo ser activada.");
define("_AC_NOTPRIVATE", "Esta no es una encuesta anónima. Se debe crear una tabla de tokens.");
define("_AC_REGISTRATION", "Esta encuesta admite registro público. Es necesario crear la tabla de tokens.");
define("_AC_CREATETOKENS", "Inicializar los Tokens");
define("_AC_SURVEYACTIVE", "La encuesta ha sido activada, y ahora las respuestas pueden ser almacenadas.");
define("_AC_DEACTIVATE_MESSAGE1", "En una encuesta activa, una tabla es creada para el almacenamiento de las contestaciones.");
define("_AC_DEACTIVATE_MESSAGE2", "Cuando desactiva una encuesta TODOS los datos que fueron introducidos en la tabla original serán movidos a otra tabla, y cuando se vuelva a activar nuevamente la encuesta, la tabla estará vacía. Ya no tendrá acceso a estos datos por medio de PHPSurveyor.");
define("_AC_DEACTIVATE_MESSAGE3", "Los datos de una encuesta que ha sido desactivada sólo pueden ser accesados por un usuario con acceso a la base de datos. Si su encuesta utiliza tokens, esta tabla también será renombrada y sólo será accesible a los administradores del sistema.");
define("_AC_DEACTIVATE_MESSAGE4", "La tabla de contestaciones será renombrada a:");
define("_AC_DEACTIVATE_MESSAGE5", "Se sugiere que exporte las contestaciones antes de desactivar la encuesta. Haga clic en \"Cancelar\" para regresar a la pantalla de administración sin desactivar la encuesta.");
define("_AC_DEACTIVATE", "Desactivar");
define("_AC_DEACTIVATED_MESSAGE1", "La tabla de respuestas ha sido renombrada a: ");
define("_AC_DEACTIVATED_MESSAGE2", "Las respuestas a esta encuesta ya no están disponibles para PHPSurveyor.");
define("_AC_DEACTIVATED_MESSAGE3", "Anote el nombre de esta tabla en caso que desee acceder a ella posteriormente.");
define("_AC_DEACTIVATED_MESSAGE4", "La tabla de tokens asociada a esta encuesta ha sido renombrada a: ");

//CHECKFIELDS
define("_CF_CHECKTABLES", "Verificando que todas las tablas existan");
define("_CF_CHECKFIELDS", "Verificando que todos los campos existan");
define("_CF_CHECKING", "Verificando");
define("_CF_TABLECREATED", "Tabla Creada");
define("_CF_FIELDCREATED", "Campo Creado");
define("_CF_OK", "OK");
define("_CFT_PROBLEM", "Parece como si faltaran algunas tablas o campos en la base de datos.");

//CREATE DATABASE (createdb.php)
define("_CD_DBCREATED", "La Base de Datos ha sido creada.");
define("_CD_POPULATE_MESSAGE", "Haga clic para rellenar la base de datos");
define("_CD_POPULATE", "Rellenar Base de Datos");
define("_CD_NOCREATE", "No se pudo crear la base de datos");
define("_CD_NODBNAME", "La información para acceder a la base de datos no se ha dado. Este guión debe ser ejecutado desde el admin.php solamente.");

//DATABASE MODIFICATION MESSAGES
define("_DB_FAIL_GROUPNAME", "No se pudo agregar el grupo. Falta el nombre del grupo.");
define("_DB_FAIL_GROUPUPDATE", "No se pudo actualizar el grupo.");
define("_DB_FAIL_GROUPDELETE", "No se pudo borrar el grupo.");
define("_DB_FAIL_NEWQUESTION", "No se pudo crear la pregunta.");
define("_DB_FAIL_QUESTIONTYPECONDITIONS", "No se pudo actualizar la pregunta. Hay condiciones en otras preguntas que dependen de las respuestas a esta pregunta y cambiando su tipo causarán problemas. Debe eliminar las condiciones antes de que pueda cambiar el tipo de esta pregunta.");
define("_DB_FAIL_QUESTIONUPDATE", "No se pudo actualizar la pregunta");
define("_DB_FAIL_QUESTIONDELCONDITIONS", "No se pudo borrar la pregunta. Hay condiciones en otras preguntas que dependen de esta pregunta. No podrá borrar esta pregunta hasta que se quiten estas condiciones.");
define("_DB_FAIL_QUESTIONDELETE", "No se pudo borrar la pregunta.");
define("_DB_FAIL_NEWANSWERMISSING", "No se pudo agregar la respuesta. Debe incluir el Código y una Respuesta.");
define("_DB_FAIL_NEWANSWERDUPLICATE", "No se pudo agregar la respuesta. Ya existe una respuesta con este código.");
define("_DB_FAIL_ANSWERUPDATEMISSING", "No se pudo actualizar la respuesta. Debe incluir el Código y una Respuesta.");
define("_DB_FAIL_ANSWERUPDATEDUPLICATE", "No se pudo actualizar la respuesta. Ya existe una respuesta con este código.");
define("_DB_FAIL_ANSWERUPDATECONDITIONS", "No se pudo agregar la respuesta. Ha cambiado su código, pero hay condiciones en otras preguntas que dependen del código anterior. Debe borrar las condiciones antes de poder cambiar el código a esta respuesta.");
define("_DB_FAIL_ANSWERDELCONDITIONS", "No se pudo borrar la respuesta. Hay condiciones en otras preguntas que dependen de esta respuesta. No puede borrar esta respuesta hasta que se quiten estas condiciones.");
define("_DB_FAIL_NEWSURVEY_TITLE", "No se pudo crear la encuesta porque no tenía el título corto.");
define("_DB_FAIL_NEWSURVEY", "No se pudo crear la encuesta.");
define("_DB_FAIL_SURVEYUPDATE", "No se pudo actualizar la encuesta.");
define("_DB_FAIL_SURVEYDELETE", "No se pudo borrar la encuesta.");

//DELETE SURVEY MESSAGES
define("_DS_NOSID", "No seleccionó qué encuesta borrar.");
define("_DS_DELMESSAGE1", "Está a punto de borrar esta encuesta.");
define("_DS_DELMESSAGE2", "Este proceso borrará esta encuesta, junto con todos los grupos, preguntas, respuestas y condiciones relacionadas.");
define("_DS_DELMESSAGE3", "Se recomienda que antes de borrar esta encuesta exporte la encuesta entera desde la página de administración.");
define("_DS_SURVEYACTIVE", "Esta encuesta está activa y existe una tabla de contestaciones. Si la borra, estas contestaciones serán borradas. Recomendamos que las exporte antes de borrar la encuesta.");
define("_DS_SURVEYTOKENS", "Esta encuesta está activa y existe una tabla de tokens. Si la borra, estos tokens serán borrados. Recomendamos que los exporte antes de borrar la encuesta.");
define("_DS_DELETED", "Se ha borrado la encuesta.");

//DELETE QUESTION AND GROUP MESSAGES
define("_DG_RUSURE", "Suprimir este grupo también suprimirá cualesquiera preguntas y respuestas que contenga. ¿Está usted seguro de que desea continuar?"); //New for 098rc5
define("_DQ_RUSURE", "Suprimir esta pregunta también suprimirá cualquier respuesta que incluya. ¿Está usted seguro de que desea continuar?"); //New for 098rc5

//EXPORT MESSAGES
define("_EQ_NOQID", "No se indicó un QID. No se pueden exportar las preguntas.");
define("_ES_NOSID", "No se indicó un SID. No se pudo exportar la encuesta");

//EXPORT RESULTS
define("_EX_FROMSTATS", "Filtrado del guión de estadísticas");
define("_EX_HEADINGS", "Preguntas");
define("_EX_ANSWERS", "Respuestas");
define("_EX_FORMAT", "Formato");
define("_EX_HEAD_ABBREV", "Cabeceras Abreviadas");
define("_EX_HEAD_FULL", "Cabeceras Completas");
define("_EX_ANS_ABBREV", "Códigos de las Respuestas");
define("_EX_ANS_FULL", "Respuestas Completas");
define("_EX_FORM_WORD", "Microsoft Word");
define("_EX_FORM_EXCEL", "Microsoft Excel");
define("_EX_FORM_CSV", "CSV Delimitado por Comas");
define("_EX_EXPORTDATA", "Exportar los Datos");
define("_EX_COLCONTROLS", "Control de Columnas"); //New for 0.98rc7
define("_EX_TOKENCONTROLS", "Control de Tokens"); //New for 0.98rc7
define("_EX_COLSELECT", "Elegir columnas"); //New for 0.98rc7
define("_EX_COLOK", "Elija las columnas que desea exportar. Deje todo sin seleccionar para exportar todas las columnas."); //New for 0.98rc7
define("_EX_COLNOTOK", "Su encuesta contiene más de 255 columnas de respuestas. Las aplicaciones de Hoja de cálculos como Excel están limitadas a una carga de no más de 255. Seleccione las columnas que desea exportar en la lista a continuación."); //New for 0.98rc7
define("_EX_TOKENMESSAGE", "Puede exportar datos de token asociados a cada pregunta. Seleccione los campos adicionales que desea exportar."); //New for 0.98rc7
define("_EX_TOKSELECT", "Elija los campos Token"); //New for 0.98rc7

//IMPORT SURVEY MESSAGES
define("_IS_FAILUPLOAD", "Ha ocurrido un error al subir el archivo. Puede ser causado porque el directorio de administración tenga permisos incorrectos.");
define("_IS_OKUPLOAD", "Se subió el archivo con éxito.");
define("_IS_READFILE", "Leyendo el archivo..");
define("_IS_WRONGFILE", "El formato del archivo no es de PHPSurveyor. La importación falló.");
define("_IS_IMPORTSUMMARY", "Resumen de la importación de la encuesta");
define("_IS_SUCCESS", "Se completó con éxito la importación de la encuesta.");
define("_IS_IMPFAILED", "La importación de la encuesta falló.");
define("_IS_FILEFAILS", "El archivo no contiene un formato PHPSurveyor válido.");

//IMPORT GROUP MESSAGES
define("_IG_IMPORTSUMMARY", "Resumen de la Importación del Grupo");
define("_IG_SUCCESS", "Se completó con éxito la importación del grupo.");
define("_IG_IMPFAILED", "La importación del grupo falló.");
define("_IG_WRONGFILE", "El archivo no contiene un formato PHPSurveyor válido.");

//IMPORT QUESTION MESSAGES
define("_IQ_NOSID", "No se indicó un SID (Encuesta). No se pudo importar la pregunta.");
define("_IQ_NOGID", "No se indicó un GID (Grupo). No se pudo importar la pregunta");
define("_IQ_WRONGFILE", "Este archivo no es un archivo de pregunta PHPSurveyor válido. Falló la importación.");
define("_IQ_IMPORTSUMMARY", "Resumen de la Importación de la Pregunta");
define("_IQ_SUCCESS", "Se completó con éxito la importación de la pregunta");

//IMPORT LABELSET MESSAGES
define("_IL_DUPLICATE", "Hubo un conjunto de etiquetas duplicado, por lo que este conjunto no fue importado. El duplicado será utilizado en su lugar.");

//BROWSE RESPONSES MESSAGES
define("_BR_NOSID", "No ha elegido qué encuesta va a examinar.");
define("_BR_NOTACTIVATED", "Esta encuesta no ha sido activada. No hay resultados que examinar.");
define("_BR_NOSURVEY", "No se encontró ninguna encuesta.");
define("_BR_EDITRESPONSE", "Editar esta entrada");
define("_BR_DELRESPONSE", "Eliminar esta entrada");
define("_BR_DISPLAYING", "Registros Desplegados:");
define("_BR_STARTING", "Empezando desde:");
define("_BR_SHOW", "Mostrar");
define("_DR_RUSURE", "¿Está seguro de que quiere borrar esta entrada?"); //New for 0.98rc6

//STATISTICS MESSAGES
define("_ST_FILTERSETTINGS", "Opciones del Filtro");
define("_ST_VIEWALL", "Ver resumen de todos los campos disponibles"); //New with 0.98rc8
define("_ST_SHOWRESULTS", "Ver Estadísticas"); //New with 0.98rc8
define("_ST_CLEAR", "Borrar"); //New with 0.98rc8
define("_ST_RESPONECONT", "Respuestas Que Contienen"); //New with 0.98rc8
define("_ST_NOGREATERTHAN", "Número Mayor Que"); //New with 0.98rc8
define("_ST_NOLESSTHAN", "Número Menor Que"); //New with 0.98rc8
define("_ST_DATEEQUALS", "Fecha (AAAA-MM-DD) igual a"); //New with 0.98rc8
define("_ST_ORBETWEEN", "O entre"); //New with 0.98rc8
define("_ST_RESULTS", "Resultados"); //New with 0.98rc8 (Plural)
define("_ST_RESULT", "Resultado"); //New with 0.98rc8 (Singular)
define("_ST_RECORDSRETURNED", "Número de elementos en esta consulta"); //New with 0.98rc8
define("_ST_TOTALRECORDS", "Total de elementos en esta encuesta"); //New with 0.98rc8
define("_ST_PERCENTAGE", "Porcentaje del total"); //New with 0.98rc8
define("_ST_FIELDSUMMARY", "Resumen de Campo para"); //New with 0.98rc8
define("_ST_CALCULATION", "Cálculo"); //New with 0.98rc8
define("_ST_SUM", "Suma"); //New with 0.98rc8 - Mathematical
define("_ST_STDEV", "Desviación Estandard"); //New with 0.98rc8 - Mathematical
define("_ST_AVERAGE", "Promedio"); //New with 0.98rc8 - Mathematical
define("_ST_MIN", "Mínimo"); //New with 0.98rc8 - Mathematical
define("_ST_MAX", "Máximo"); //New with 0.98rc8 - Mathematical
define("_ST_Q1", "Primer cuartal (Q1)"); //New with 0.98rc8 - Mathematical
define("_ST_Q2", "Segundo cuartal (Medio)"); //New with 0.98rc8 - Mathematical
define("_ST_Q3", "Tercer cuartal (Q3)"); //New with 0.98rc8 - Mathematical
define("_ST_NULLIGNORED", "*Valores nulos ignorados en los cálculos"); //New with 0.98rc8
define("_ST_QUARTMETHOD", "*Q1 y Q3 calculados usando <a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>método minitab</a>"); //New with 0.98rc8

//DATA ENTRY MESSAGES
define("_DE_NOMODIFY", "No puede ser modificado");
define("_DE_UPDATE", "Actualizar Entrada");
define("_DE_NOSID", "No ha seleccionado una encuesta para la cual realizar la entrada de datos.");
define("_DE_NOEXIST", "La encuesta que seleccionó no existe");
define("_DE_NOTACTIVE", "Esta encuesta no ha sido activada. Sus respuestas no serán almacenadas.");
define("_DE_INSERT", "Almacenando los Datos");
define("_DE_RECORD", "A la entrada se le asignó el siguiente id: ");
define("_DE_ADDANOTHER", "Agregar Otro Registro");
define("_DE_VIEWTHISONE", "Ver Este Registro");
define("_DE_BROWSE", "Examinar las Contestaciones");
define("_DE_DELRECORD", "Registro Borrado");
define("_DE_UPDATED", "El registro ha sido actualizado.");
define("_DE_EDITING", "Modificando Contestación");
define("_DE_QUESTIONHELP", "Ayuda sobre esta pregunta");
define("_DE_CONDITIONHELP1", "Contestar a esta pregunta sólo bajo las siguientes condiciones:");
define("_DE_CONDITIONHELP2", "a la pregunta {QUESTION}, contestó {ANSWER}"); //This will be a tricky one depending on your languages syntax. {ANSWER} is replaced with ALL ANSWERS, separated by _DE_OR (OR).
define("_DE_AND", "Y");
define("_DE_OR", "O");
define("_DE_SAVEENTRY", "Guardar como encuesta parcialmente completada"); //New in 0.99dev01
define("_DE_SAVEID", "Identificador:"); //New in 0.99dev01
define("_DE_SAVEPW", "Contraseña:"); //New in 0.99dev01
define("_DE_SAVEPWCONFIRM", "Confirmar Contraseña:"); //New in 0.99dev01
define("_DE_SAVEEMAIL", "Correo Electrónico:"); //New in 0.99dev01

//TOKEN CONTROL MESSAGES
define("_TC_TOTALCOUNT", "Número de Registros en la tabla de Tokens:"); //New in 0.98rc4
define("_TC_NOTOKENCOUNT", "Total Sin Token único:"); //New in 0.98rc4
define("_TC_INVITECOUNT", "Total de Invitaciones Enviadas:"); //New in 0.98rc4
define("_TC_COMPLETEDCOUNT", "Total de Encuestas Completadas:"); //New in 0.98rc4
define("_TC_NOSID", "No ha seleccionado una encuesta");
define("_TC_DELTOKENS", "A punto de borrar la tabla de tokens para esta encuesta.");
define("_TC_DELTOKENSINFO", "Si borra esta tabla de tokens, ya no será obligatorio el uso de tokens para acceder a esta encuesta. Se hará un respaldo de esta tabla si procede. Sólo el administrador del sistema tendrá acceso a esta tabla.");
define("_TC_DELETETOKENS", "Borrar Tokens");
define("_TC_TOKENSGONE", "La tabla de tokens ha sido borrada y los tokens ya no son obligatorios para acceder a esta encuesta. Una copia de esta tabla ha sido creada y sólo el administrador del sistema podrá acceder a ella.");
define("_TC_NOTINITIALISED", "No se han inicializado tokens para esta encuesta.");
define("_TC_INITINFO", "Si inicializa tokens para esta encuesta, esta solo será accesible a usuarios que tengan asignados un token.");
define("_TC_INITQ", "¿Desea crear una tabla de tokens para esta encuesta?");
define("_TC_INITTOKENS", "Inicializar Tokens");
define("_TC_CREATED", "Se ha creado una tabla de tokens para esta encuesta.");
define("_TC_DELETEALL", "Borrar todos los tokens");
define("_TC_DELETEALL_RUSURE", "¿Está seguro(a) de querer borrar TODOS los tokens?");
define("_TC_ALLDELETED", "Se han borrado todos los tokens");
define("_TC_CLEARINVITES", "Aplicar 'N' a todos los registros con invitación enviada");
define("_TC_CLEARINV_RUSURE", "¿Está seguro(a) de inicializar todas las invitaciones a NO?");
define("_TC_CLEARTOKENS", "Borrar todos los tokens con número único");
define("_TC_CLEARTOKENS_RUSURE", "¿Está seguro de querer borrar todos los tokens con números únicos?");
define("_TC_TOKENSCLEARED", "Todos los tokens con número único han sido borrados");
define("_TC_INVITESCLEARED", "Todas las entradas con invitación han sido puestas a N");
define("_TC_EDIT", "Modificar Token");
define("_TC_DEL", "Borrar Token");
define("_TC_DO", "Realizar Encuesta");
define("_TC_VIEW", "Ver Contestación");
define("_TC_UPDATE", "Actualizar Respuesta"); // New with 0.99 stable
define("_TC_INVITET", "Enviar invitación (email) a esta entrada");
define("_TC_REMINDT", "Enviar recordatorio (email) a esta entrada");
define("_TC_INVITESUBJECT", "Invitación para participar en la encuesta {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSUBJECT", "Recordatorio para participar en la encuesta {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSTARTAT", "Iniciar en el TID Núm:");
define("_TC_REMINDTID", "Enviando al TID Núm:");
define("_TC_CREATETOKENSINFO", "Dando clic a 'Sí' generará tokens para todos aquellos que estén en la lista y que no se les haya generado un token. ¿Es esto correcto?");
define("_TC_TOKENSCREATED", "{TOKENCOUNT} tokens han sido creados."); //Leave {TOKENCOUNT} for replacement in script with the number of tokens created
define("_TC_TOKENDELETED", "Se ha borrado el token.");
define("_TC_SORTBY", "Ordenar por: ");
define("_TC_ADDEDIT", "Agregar o Modificar Token");
define("_TC_TOKENCREATEINFO", "Puede dejar en blanco esto, y automáticamente generar tokens utilizando 'Crear Tokens'");
define("_TC_TOKENADDED", "Nuevo Token Agregado");
define("_TC_TOKENUPDATED", "Token Actualizado");
define("_TC_UPLOADINFO", "El archivo debe ser CSV estándar (delimitado por comas) sin comillas. La primera línea debe contener la información de la cabecera (que será eliminada). Los datos deben estar ordenados como 'nombre, apellido, correo electrónico, [token], [attribute1], [attribute2]'.");
define("_TC_UPLOADFAIL", "Archivo subido no encontrado. Compruebe sus permisos y trayectoria para saber si existe el directorio de subidas"); //New for 0.98rc5 (babelfish translation)
define("_TC_IMPORT", "Importando Archivo CSV");
define("_TC_CREATE", "Creando Tokens");
define("_TC_TOKENS_CREATED", "{TOKENCOUNT} Tokens Creados");
define("_TC_NONETOSEND", "No hubo correos electrónicos elegibles para enviar. Esto fue causado porque los criterios no se satisficieron - tener direcciones de correo electrónico, no haber enviado inivitaciones anteriormente, haber completado la encuesta y tener asignado un token.");
define("_TC_NOREMINDERSTOSEND", "No hubo correos electrónicos elegibles para enviar. Esto fue causado porque los criterios no se satisficieron - tener una dirección de correo electrónico, haber enviado una invitación, pero no haber completado la encuesta aún.");
define("_TC_NOEMAILTEMPLATE", "No se encontró la plantilla para la invitación. Este archivo debe existir dentro del directorio de plantillas.");
define("_TC_NOREMINDTEMPLATE", "No se encontró la plantilla para el recordatorio. Este archivo debe existir dentro del directorio de plantillas.");
define("_TC_SENDEMAIL", "Enviar Invitaciones");
define("_TC_SENDINGEMAILS", "Enviando Invitaciones");
define("_TC_SENDINGREMINDERS", "Enviando Recordatorios");
define("_TC_EMAILSTOGO", "Hay más correos pendientes de ser enviados que pueden ser enviados en un sólo viaje. Para continuar enviando da clic abajo.");
define("_TC_EMAILSREMAINING", "Hay {EMAILCOUNT} correos que no han sido enviados."); //Leave {EMAILCOUNT} for replacement in script by number of emails remaining
define("_TC_SENDREMIND", "Enviar Recordatorios");
define("_TC_INVITESENTTO", "Invitación Enviada a:"); //is followed by token name
define("_TC_REMINDSENTTO", "Recordatorio Enviado a:"); //is followed by token name
define("_TC_UPDATEDB", "Actualizar la tabla de tokens con nuevos campos"); //New for 0.98rc7
define("_TC_EMAILINVITE_SUBJ", "Invitación para participar en la encuesta"); //New for 0.99dev01
define("_TC_EMAILINVITE", "Estimado {FIRSTNAME},\n\nUsted ha sido invitado a participar en una encuesta.\n\n"
						 ."El título de la encuesta es:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."Para participar, por favor pulse en el enlace de abajo.\n\nSinceramente,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Haga click aquí para iniciar la encuesta:\n"
						 ."{SURVEYURL}"); //New for 0.98rc9 - default Email Invitation
define("_TC_EMAILREMIND_SUBJ", "Recordatorio para participar en la encuesta"); //New for 0.99dev01
define("_TC_EMAILREMIND", "Estimado {FIRSTNAME},\n\nRecientemente fue invitado a participar en una encuesta.\n\n"
						 ."Notamos que no la ha completado, y queríamos recordarle que la encuesta todavía se encuentra disponible si desea participar.\n\n"
						 ."El título de la encuesta es:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."Para participar, por favor haga click en el enlace de abajo.\n\nSinceramente,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Haga click aquí para iniciar la encuesta:\n"
						 ."{SURVEYURL}"); //New for 0.98rc9 - default Email Reminder
define("_TC_EMAILREGISTER_SUBJ", "Confirmación de Registro en la Encuesta"); //New for 0.99dev01
define("_TC_EMAILREGISTER", "Estimado {FIRSTNAME},\n\n"
						  ."Usted, o alguien utilizando su dirección de correo electrónico, se ha registrado para "
						  ."participar en una encuesta en línea titulada {SURVEYNAME}.\n\n"
						  ."Para completarla, haga click en la siguiente URL:\n\n"
						  ."{SURVEYURL}\n\n"
						  ."Si tiene dudas con respecto a dicha encuesta, o si usted "
						  ."no se registró para participar y cree que este correo "
						  ."está errado, por favor, póngase en contacto con {ADMINNAME} en {ADMINEMAIL}.");//NEW for 0.98rc9
define("_TC_EMAILCONFIRM_SUBJ", "Confirmación de encuesta completada"); //New for 0.99dev01
define("_TC_EMAILCONFIRM", "Estimado {FIRSTNAME},\n\nEste correo es para confirmar que ha completado la encuesta titulada {SURVEYNAME} "
						  ."y sus respuestas fueron guardadas. Gracias por su participación.\n\n"
						  ."Si tiene alguna otra duda con respecto a este correo, por favor póngase en contacto con {ADMINNAME} en {ADMINEMAIL}.\n\n"
						  ."Sinceramente,\n\n"
						  ."{ADMINNAME}"); //New for 0.98rc9 - Confirmation Email

//labels.php
define("_LB_NEWSET", "Crear Nuevo Conjunto de Etiquetas");
define("_LB_EDITSET", "Modificar Conjunto de Etiquetas");
define("_LB_FAIL_UPDATESET", "No se pudo actualizar el conjunto de etiquetas");
define("_LB_FAIL_INSERTSET", "No se pudo agregar el nuevo conjunto de etiquetas");
define("_LB_FAIL_DELSET", "No se pudo borrar el conjunto de etiquetas - Hay preguntas que dependen de ellas. Debe borrar estas preguntas primero antes de poder continuar.");
define("_LB_ACTIVEUSE", "No puede cambiar los códigos, agregar o borrar entradas a este conjunto de etiquetas porque está siendo utilizado por una encuesta activa.");
define("_LB_TOTALUSE", "Algunas encuestas están utilizando este conjunto de etiquetas. Modificar los códigos, agregar o borrar entradas a este conjunto puede producir resultados indeseados.");
//Export Labels
define("_EL_NOLID", "No se indicó el LID. No se puede exportar el conjunto de etiquetas.");
//Import Labels
define("_IL_GOLABELADMIN", "Regresar a la Administración de Etiquetas");

//PHPSurveyor System Summary
define("_PS_TITLE", "Resumen de PHPSurveyor");
define("_PS_DBNAME", "Nombre de la Base de Datos");
define("_PS_DEFLANG", "Idioma por defecto");
define("_PS_CURLANG", "Idioma Actual");
define("_PS_USERS", "Usuarios");
define("_PS_ACTIVESURVEYS", "Encuestas Activas");
define("_PS_DEACTSURVEYS", "Encuestas Desactivadas");
define("_PS_ACTIVETOKENS", "Tablas de Tokens Activadas");
define("_PS_DEACTTOKENS", "Tablas de Tokens Desactivadas");
define("_PS_CHECKDBINTEGRITY", "Verificar Integridad de Datos de PHPSurveyor"); //New with 0.98rc8

//Notification Levels
define("_NT_NONE", "Ninguna notificación por email"); //New with 098rc5
define("_NT_SINGLE", "Notificación básica por email"); //New with 098rc5
define("_NT_RESULTS", "Enviar notificación por email con códigos del resultado"); //New with 098rc5

//CONDITIONS TRANSLATIONS
define("_CD_CONDITIONDESIGNER", "Diseñador de Condiciones"); //New with 098rc9
define("_CD_ONLYSHOW", "Sólo mostrar la respuesta {QID} SI"); //New with 098rc9 - {QID} is repleaced leave there
define("_CD_AND", "Y"); //New with 098rc9
define("_CD_COPYCONDITIONS", "Copiar Condiciones"); //New with 098rc9
define("_CD_CONDITION", "Condición"); //New with 098rc9
define("_CD_ADDCONDITION", "Agregar Condición"); //New with 098rc9
define("_CD_EQUALS", "Equivale"); //New with 098rc9
define("_CD_COPYRUSURE", "¿Está seguro de que desea copiar estas condiciones a las preguntas que ha seleccionado?"); //New with 098rc9
define("_CD_NODIRECT", "No puede ejecutar este guión directamente."); //New with 098rc9
define("_CD_NOSID", "No ha seleccionado una encuesta."); //New with 098rc9
define("_CD_NOQID", "No ha seleccionado una pregunta."); //New with 098rc9
define("_CD_DIDNOTCOPYQ", "No se copiaron las preguntas"); //New with 098rc9
define("_CD_NOCONDITIONTOCOPY", "No se ha seleccionado una condición como origen para copiar"); //New with 098rc9
define("_CD_NOQUESTIONTOCOPYTO", "No se ha seleccionado una pregunta como destino para copiar"); //New with 098rc9
define("_CD_COPYTO", "copiar a"); //New with 0.991

//TEMPLATE EDITOR TRANSLATIONS
define("_TP_CREATENEW", "Crear nueva plantilla"); //New with 098rc9
define("_TP_NEWTEMPLATECALLED", "Crear nueva plantilla llamada:"); //New with 098rc9
define("_TP_DEFAULTNEWTEMPLATE", "NuevaPlantilla"); //New with 098rc9 (default name for new template)
define("_TP_CANMODIFY", "Esta plantilla puede ser modificada"); //New with 098rc9
define("_TP_CANNOTMODIFY", "Esta plantilla no puede ser modificada"); //New with 098rc9
define("_TP_RENAME", "Renombrar la plantilla");  //New with 098rc9
define("_TP_RENAMETO", "Renombrar esta plantilla a:"); //New with 098rc9
define("_TP_COPY", "Crear una copia de esta plantilla");  //New with 098rc9
define("_TP_COPYTO", "Crear una copia de esta plantilla llamada:"); //New with 098rc9
define("_TP_COPYOF", "copia_de_"); //New with 098rc9 (prefix to default copy name)
define("_TP_FILECONTROL", "Control de Archivo:"); //New with 098rc9
define("_TP_STANDARDFILES", "Archivo Estándar:");  //New with 098rc9
define("_TP_NOWEDITING", "Editando Ahora:");  //New with 098rc9
define("_TP_OTHERFILES", "Otros Archivos:"); //New with 098rc9
define("_TP_PREVIEW", "Vista Previa:"); //New with 098rc9
define("_TP_DELETEFILE", "Borrar"); //New with 098rc9
define("_TP_UPLOADFILE", "Enviar fichero"); //New with 098rc9
define("_TP_SCREEN", "Pantalla:"); //New with 098rc9
define("_TP_WELCOMEPAGE", "Página de Bienvenida"); //New with 098rc9
define("_TP_QUESTIONPAGE", "Página de Preguntas"); //New with 098rc9
define("_TP_SUBMITPAGE", "Página de Enviar Encuesta");
define("_TP_COMPLETEDPAGE", "Página de Encuesta Completada"); //New with 098rc9
define("_TP_CLEARALLPAGE", "Página de Borrar Encuesta"); //New with 098rc9
define("_TP_REGISTERPAGE", "Página de Registro"); //New with 098finalRC1
define("_TP_EXPORT", "Exportar Plantilla"); //New with 098rc10
define("_TP_LOADPAGE", "Página de Cargar Encuesta"); //New with 0.99dev01
define("_TP_SAVEPAGE", "Página de guardar Encuesta"); //New with 0.99dev01

//Saved Surveys
define("_SV_RESPONSES", "Respuestas guardadas:");
define("_SV_IDENTIFIER", "Identificador");
define("_SV_RESPONSECOUNT", "Contestado");
define("_SV_IP", "Dirección IP");
define("_SV_DATE", "Fecha guardada");
define("_SV_REMIND", "Recordar");
define("_SV_EDIT", "Editar");

//VVEXPORT/IMPORT
define("_VV_IMPORTFILE", "Importar un Archivo de encuesta VV");
define("_VV_EXPORTFILE", "Exportar un Archivo de encuesta VV");
define("_VV_FILE", "Archivo:");
define("_VV_SURVEYID", "ID de Encuesta:");
define("_VV_EXCLUDEID", "¿Excluir el ID del elemento?");
define("_VV_INSERT", "Cuando un elemento importado concuerda con el ID de un elemento existente:");
define("_VV_INSERT_ERROR", "Reportar un error (y saltear el nuevo elemento).");
define("_VV_INSERT_RENUMBER", "Renumerar el nuevo elemento.");
define("_VV_INSERT_IGNORE", "Ignorar el nuevo elemento.");
define("_VV_INSERT_REPLACE", "Remplazar el elemento existente.");
define("_VV_DONOTREFRESH", "Nota Importante:<br />NO refresque esta página, esto provocará que se importe nuevamente el archivo produzca duplicados");
define("_VV_IMPORTNUMBER", "Total de elementos importados:");
define("_VV_ENTRYFAILED", "Fallo en la importación en el Elemento");
define("_VV_BECAUSE", "por que");
define("_VV_EXPORTDEACTIVATE", "Exportar, después desactivar la encuesta");
define("_VV_EXPORTONLY", "Exportar, pero dejar la encuesta activa");
define("_VV_RUSURE", "Si ha elegido exportar y desactivar, ésto va a renombrar su tabla actual de respuestas y no será fácil restaurarla. ¿Está seguro?");

//ASSESSMENTS
define("_AS_TITLE", "Evaluaciones");
define("_AS_DESCRIPTION", "Si crea alguna evaluación en esta página, para la encuesta actualmente seleccionada, la evaluación será realizada al final, después de ser enviada la encuesta");
define("_AS_NOSID", "No SID Provisto");
define("_AS_SCOPE", "Alcance");
define("_AS_MINIMUM", "Mínimo");
define("_AS_MAXIMUM", "Máximo");
define("_AS_GID", "Grupo");
define("_AS_NAME", "Nombre/Cabecera");
define("_AS_HEADING", "Cabecera");
define("_AS_MESSAGE", "Mensaje");
define("_AS_URL", "URL");
define("_AS_SCOPE_GROUP", "Grupo");
define("_AS_SCOPE_TOTAL", "Total");
define("_AS_ACTIONS", "Acciones");
define("_AS_EDIT", "Editar");
define("_AS_DELETE", "Borrar");
define("_AS_ADD", "Agregar");
define("_AS_UPDATE", "Actualizar");

//Question Number regeneration
define("_RE_REGENNUMBER", "Regenerar Número de las Preguntas:"); //NEW for release 0.99dev2
define("_RE_STRAIGHT", "Todo"); //NEW for release 0.99dev2
define("_RE_BYGROUP", "Por Grupo"); //NEW for release 0.99dev2

// Database Consistency Check
define ("_DC_TITLE", "Verificación de Consistencia de Datos<br /><font size='1'>Si aparecen errores, puede que usted deba ejecutar este guión repetidas veces. </font>"); // New with 0.99stable
define ("_DC_QUESTIONSOK", "Todas las preguntas son consistentes"); // New with 0.99stable
define ("_DC_ANSWERSOK", "Todas las respuestas son consistentes"); // New with 0.99stable
define ("_DC_CONDITIONSSOK", "Todas las condiciones son consistentes"); // New with 0.99stable
define ("_DC_GROUPSOK", "Todos los grupos son consistentes"); // New with 0.99stable
define ("_DC_NOACTIONREQUIRED", "No se requiere acción de la base de datos"); // New with 0.99stable
define ("_DC_QUESTIONSTODELETE", "Las siguientes preguntas deberían ser borradas"); // New with 0.99stable
define ("_DC_ANSWERSTODELETE", "Las siguientes respuestas deberían ser borradas"); // New with 0.99stable
define ("_DC_CONDITIONSTODELETE", "Las siguientes condiciones deberían ser borradas"); // New with 0.99stable
define ("_DC_GROUPSTODELETE", "Los siguientes grupos deberían ser borrados"); // New with 0.99stable
define ("_DC_ASSESSTODELETE", "Las siguientes evaluaciones deberían ser borradas"); // New with 0.99stable
define ("_DC_QATODELETE", "Los siguientes atributos de preguntas deberían ser borrados"); // New with 0.99stable
define ("_DC_QAOK", "Todos los atributos de preguntas son consistentes"); // New with 0.99stable
define ("_DC_ASSESSOK", "Todas las evaluaciones son consistentes"); // New with 0.99stable

?>
