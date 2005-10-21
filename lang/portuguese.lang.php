<?php
/*
	#############################################################
	# >>> PHPSurveyor  										#
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
	# This language file kindly provided by Rosaura Gazzola/Job Vieira Lúcio	#
	#															#
	#############################################################
*/
//SINGLE WORDS
define("_YES", "Sim");
define("_NO", "Não");
define("_UNCERTAIN", "Duvidoso");
define("_ADMIN", "Admin");
define("_TOKENS", "Senhas");
define("_FEMALE", "Feminino");
define("_MALE", "Masculino");
define("_NOANSWER", "Sem resposta");
define("_NOTAPPLICABLE", "N/A"); //New for 0.98rc5
define("_OTHER", "Outro");
define("_PLEASECHOOSE", "Favor escolher um alternativa");
define("_ERROR_PS", "Erro");
define("_COMPLETE", "Completo");
define("_INCREASE", "Aumento"); //NEW WITH 0.98 BABELFISH TRANSLATION
define("_SAME", "Iguais"); //NEW WITH 0.98 BABELFISH TRANSLATION
define("_DECREASE", "Diminuição"); //NEW WITH 0.98 BABELFISH TRANSLATION
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Confirmação");
define("_TOKEN_PS", "Senha");
define("_CONTINUE_PS", "Continuar");

//BUTTONS
define("_ACCEPT", "Aceitar");
define("_PREV", "Anterior");
define("_NEXT", "seg");
define("_LAST", "último");
define("_SUBMIT", "enviar");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Favor escolher uma das seguintes");
define("_ENTERCOMMENT", "Favor escrever aqui seu comentário");
define("_NUMERICAL_PS", "Nesse campo só se aceitam números");
define("_CLEARALL", "Limpar Questionário");
define("_MANDATORY", "Esta pergunta é obrigatória");
define("_MANDATORY_PARTS", "Favor completar todas as partes");
define("_MANDATORY_CHECK", "Favor seleccionar ao menos um elemento");
define("_MANDATORY_RANK", "Favor clasificar todos os elementos");
define("_MANDATORY_POPUP", "Uma ou mais perguntas obrigatórias não foram respondidas. Você não pode continuar até respondê-las"); //NEW in 0.98rc4
define("_VALIDATION", "This question must be answered correctly"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Formato: AAAA-MM-DD");
define("_DATEFORMATEG", "(p.ex: 2005-12-25 para Natal)");
define("_REMOVEITEM", "Eliminar este elemento");
define("_RANK_1", "Clique num elemento da lista da esquerda, começando pelo");
define("_RANK_2", "Elemento com mais alta classificação até chegar ao elemento com mais baixa clasificação.");
define("_YOURCHOICES", "Suas Opções");
define("_YOURRANKING", "Sua Clasificação");
define("_RANK_3", "Clique na tesoura à direita de cada elemento");
define("_RANK_4", "Para eliminar a última captura da sua classificação");
//From INDEX.PHP
define("_NOSID", "Não proporcionou um número identificador do questionário");
define("_CONTACT1", "Favor contactar");
define("_CONTACT2", "Para mais assistência");
define("_ANSCLEAR", "Respostas eliminadas");
define("_RESTART", "Recomeçar o Questionário");
define("_CLOSEWIN_PS", "Fechar esta Janela");
define("_CONFIRMCLEAR", "Estás certo de eliminar todas suas respostas?");
define("_CONFIRMSAVE", "Are you sure you want to save your responses?");
define("_EXITCLEAR", "Sair e Limpar o Questionário");
//From QUESTION.PHP
define("_BADSUBMIT1", "Não é possível enviar os resultados - não há resultados para enviar.");
define("_BADSUBMIT2", "Este erro pode ocorrer se enviou suas respostas e presionou 'renovar' no seu navegador. Neste caso, suas respostas já foram guardadas.");
define("_NOTACTIVE1", "Suas respostas não foram guardadas, porque o Questionário ainda não foi ativada.");
define("_CLEARRESP", "Limpar Respostas");
define("_THANKS", "Muito Obrigado(a)");
define("_SURVEYREC", "Suas respostas foram guardadas.");
define("_SURVEYCPL", "Questionário Respondida");
define("_DIDNOTSAVE", "Não foi guardada");
define("_DIDNOTSAVE2", "Ocorreu um erro inesperado e suas respostas não puderam ser guardadas.");
define("_DIDNOTSAVE3", "Suas respostas não foram perdidas e foram enviadas por correio eletrônico ao administrador(a) do questionário para ser capturadas na nossa base de dados posteriormente.");
define("_DNSAVEEMAIL1", "Ocorreu um erro ao guardar uma resposta do questionário identificada com");
define("_DNSAVEEMAIL2", "DADOS PARA SER CAPTURADOS");
define("_DNSAVEEMAIL3", "O CÓDIGO SQL FALHOU");
define("_DNSAVEEMAIL4", "MENSAGEM DE ERRO");
define("_DNSAVEEMAIL5", "ERRO GUARDANDO");
define("_SUBMITAGAIN", "Tente enviar outra vez");
define("_SURVEYNOEXIST", "Sentimos muito. Não há questionários coincidentes.");
define("_NOTOKEN1", "Este questionário tem controle de acesso. Necessita uma senha válida para participar.");
define("_NOTOKEN2", "Se lhe foi concedida uma senha, favor digitá-la na caixa abaixo e clicar em continuar.");
define("_NOTOKEN3", "A senha que lhe foi concedida não é válida ou já foi usada.");
define("_NOQUESTIONS", "Este questionário ainda não tem perguntas e não pode ser testado nem completado.");
define("_FURTHERINFO", "Para mais informação contactar");
define("_NOTACTIVE", "Este questionário não está ativo. Não poderá guardar suas respuestas.");
define("_SURVEYEXPIRED", "Este questionário já expirou."); //NEW for 098rc5

define("_SURVEYCOMPLETE", "Questionário Completo.");

define("_INSTRUCTION_LIST", "Escolha somente um dos seguintes"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Escolha uma ou mais alternativas"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Questionário Enviado"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Uma nova resposta foi incorporada para seu exame"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Clique no link abaixo para ver a resposta individual:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Clique aqui para ver a Estatística:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Click the following link to edit the individual response:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>A Note On Privacy</i></strong><br />"
						  ."This survey is anonymous.<br />"
						  ."The record kept of your survey responses does not contain any "
						  ."identifying information about you unless a specific question "
						  ."in the survey has asked for this. If you have responded to a "
						  ."survey that used an identifying token to allow you to access "
						  ."the survey, you can rest assured that the identifying token "
						  ."is not kept with your responses. It is managed in a separate "
						  ."database, and will only be updated to indicate that you have "
						  ."(or haven't) completed this survey. There is no way of matching "
						  ."identification tokens with survey responses in this survey."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "There are {NUMBEROFQUESTIONS} questions in this survey."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "There is 1 question in this survey."); //New for 0.98rc9 - singular version of above
						  
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

define("_SM_COMPLETED", "<strong>Thank You<br /><br />"
					   ."You have completed answering the questions in this survey.</strong><br /><br />"
					   ."Click on ["._SUBMIT."] now to complete the process and save your answers.");
define("_SM_REVIEW", "If you want to check any of the answers you have made, and/or change them, "
					."you can do that now by clicking on the [<< "._PREV."] button and browsing "
					."through your responses.");

//For the "printable" survey
define("_PS_CHOOSEONE", "Please choose <strong>only one</strong> of the following:"); //New for 0.98finalRC1
define("_PS_WRITE", "Please write your answer here:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Please choose <strong>all</strong> that apply:"); //New for 0.98finalRC1
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
