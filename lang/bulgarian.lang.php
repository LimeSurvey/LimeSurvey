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
*/
//SINGLE WORDS
define("_YES", "Да");
define("_NO", "Не");
define("_UNCERTAIN", "Неопределен");
define("_ADMIN", "Администратор");
define("_TOKENS", "Токени");
define("_FEMALE", "Женски");
define("_MALE", "Мъжки");
define("_NOANSWER", "Няма отговор");
define("_NOTAPPLICABLE", "N/A"); //New for 0.98rc5
define("_OTHER", "Друго");
define("_PLEASECHOOSE", "Моля изберете");
define("_ERROR_PS", "Грешка");
define("_COMPLETE", "попълнен");
define("_INCREASE", "Увеличава"); //NEW WITH 0.98
define("_SAME", "Същият"); //NEW WITH 0.98
define("_DECREASE", "Намалява"); //NEW WITH 0.98
//from questions.php
define("_CONFIRMATION", "Потвърждение");
define("_TOKEN_PS", "Токен");
define("_CONTINUE_PS", "Продължи");

//BUTTONS
define("_ACCEPT", "Приема");
define("_PREV", "Предишен");
define("_NEXT", "Следващ");
define("_LAST", "Последен");
define("_SUBMIT", "Изпрати");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Моля изберете едно от следните");
define("_ENTERCOMMENT", "Моля въведете своя коментар тук");
define("_NUMERICAL_PS", "В това поле могат да бъдат въвеждани само числа");
define("_CLEARALL", "Изход и нулиране на въпросника");
define("_MANDATORY", "Този въпрос е задължителен");
define("_MANDATORY_PARTS", "Моля попълнете всички части");
define("_MANDATORY_CHECK", "Моля маркирайте поне един елемент");
define("_MANDATORY_RANK", "Моля подредете всички елементи");
define("_MANDATORY_POPUP", "На един или повече от задължителните въпроси не е отговорено. Не можете да продължите без да има отговорите..."); //NEW in 0.98rc4
define("_VALIDATION", "This question must be answered correctly"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Формат: YYYY-MM-DD");
define("_DATEFORMATEG", "(напр.: 2003-12-25 за Коледа.)");
define("_REMOVEITEM", "Изтриите този елемент");
define("_RANK_1", "Щракнете елемент от списъка в ляво, започвайки с Вашия");
define("_RANK_2", "най-висок по ранг елемент, преминавайки през елементите с по-нисък ранг.");
define("_YOURCHOICES", "Вашият избор");
define("_YOURRANKING", "Вашето подреждане по ранг");
define("_RANK_3", "Щракнете на ножиците, намиращи се в дясно от всеки елемент");
define("_RANK_4", "за да премахнете последната въведена позиция във Вашия списък.");
//From INDEX.PHP
define("_NOSID", "Не сте предоставили идентификационен номер на въпросника");
define("_CONTACT1", "Моля обърнете се към");
define("_CONTACT2", "за допълнително съдействие.");
define("_ANSCLEAR", "Отговорите са изчистени");
define("_RESTART", "Рестартиране на въпросника");
define("_CLOSEWIN_PS", "Затваряне на прозореца");
define("_CONFIRMCLEAR", "Сигурни ли сте, че желаете да изчистите всички свои отговори?");
define("_CONFIRMSAVE", "Are you sure you want to save your responses?");
define("_EXITCLEAR", "Изход и изчистване на въпросника");
//From QUESTION.PHP
define("_BADSUBMIT1", "Резултатите не могат да бъдат изпратение - няма нищо за изпращане.");
define("_BADSUBMIT2", "Тази грешка би могла да се получи ако вече сте изпратили  своите отговори и натиснете  'refresh' на Вашия браузър. В този случай Вашите отговори вече са били записани.<br /><br />Ако получавате това съобщение по време на попълването на въпросника, изберете '<- BACK' на Вашия браузър, след което refresh/reload на предната страница. Ще загубите отговорите от последната страница, но всичко останало ще се запази. Този проблем може да се получи ако  web-сървърът  е претоварен. Извиняваме се за този проблем.");
define("_NOTACTIVE1", "Вашите отговори на въпросника не са записани. Този въпросник все още не е активен.");
define("_CLEARRESP", "Изчисти отговорите");
define("_THANKS", "Благодаря");
define("_SURVEYREC", "Вашите отговори на въпросника са записани.");
define("_SURVEYCPL", "Въпросникът попълнен");
define("_DIDNOTSAVE", "Не е записан");
define("_DIDNOTSAVE2", "Получена е неочаквана грешка и Вашите въпроси не могат да бъдат записани.");
define("_DIDNOTSAVE3", "Вашите отговори не са загубени и са изпратени по e-mail на администратора на въпросника. Те ще бъдат въведени в базата данни по-късно.");
define("_DNSAVEEMAIL1", "Получена е грешка при записването на отговор в id на въпросника");
define("_DNSAVEEMAIL2", "ДА БЪДАТ ВЪВЕДЕНИ ДАННИ");
define("_DNSAVEEMAIL3", "SQL КОЙТО НЕ РАБОТИ");
define("_DNSAVEEMAIL4", "СЪОБЩЕНИЕ ЗА ГРЕШКА");
define("_DNSAVEEMAIL5", "ГРЕШКА ПРИ ЗАПИС");
define("_SUBMITAGAIN", "Опитайте да изпратите отново");
define("_SURVEYNOEXIST", "Съжаляваме, но няма съответстващ въпросник.");
define("_NOTOKEN1", "Това е контролиран въпросник. Необходим Ви е токен за да участвате.");
define("_NOTOKEN2", "Ако сте получили токен, моля въведете го в реда по-долу и натиснете продължение.");
define("_NOTOKEN3", "Токенът, който сте предоставили е или невалиден, или вече е бил използван.");
define("_NOQUESTIONS", "Този въпросник все още не съдържа никакви въпроси и не може да бъде тестван или попълван.");
define("_FURTHERINFO", "За допълнителна информация се обърнете към");
define("_NOTACTIVE", "Този въпросник понастоящем не е активен. Няма да можете да запишете своите отговори.");
define("_SURVEYEXPIRED", "Този въпросник вече не е наличен.");

define("_SURVEYCOMPLETE", "Вече сте попълнили този въпросник."); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "Изберете само едно от следните"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Изберете някой, които съответства"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Въпросникът изпратен"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Въведен е нов отговор на Вашия въпросник"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Щракнете на следния линк, за да видите индивидуалния отговор:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Щракнете тук, за да изведете статистика:"); //NEW for 098rc5

define("_PRIVACY_MESSAGE", "<b><i>Защита на личните данни</i></b><br />"
						  ."Този въпросник може да бъде анонимен.<br />"
						  ."Записът, съдържащ Вашите отговори не съдържа никаква лична информация,"
						  ."с изключение на отговорите на специфични въпроси от въпросника. "
						  ."Ако сте попълнили въпросник, който изполва идентификационен токен"
						  ."за достъп, допълнително Ви уверяваме,"
						  ."че идентификационният токен"
						  ."не се съхранява при Вашите отговори. Той се управлява чрез отделна "
						  ."база данни, и ще бъде използван само за да се определи, дали Вие сте"
						  ."(или не сте) попълвали въпросника. Не съществува начин за отъждествяване на"
						  ."идентификационните токени с отговорите на въпросите в даден въпросник."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Въпросникът съдържа {NUMBEROFQUESTIONS} въпроса."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Въпросникът съдържа 1 въпрос."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "За да попълните този въпросник трябва да се регистрирате"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Можете да се регистрирате за този въпросник в случай, че желаете да участвате.<br />\n"
						."Попълнете своите данни по-долу и веднага ще Ви бъде изпратен email,"
						."съдържащ линк към въпросника."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Email адрес"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Име"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Фамилия"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "Еmail-ът не е валиден. Моля въведете друг.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "Еmail-ът, който сте въвели вече е регистриран.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} Потвърждение на регистрацията");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Благодаря за това, че се регистрирахте за този въпросник.<br /><br />\n"
								   ."Изпратен е email на адреса, който сте посочили в данните за контакт  "
								   ."във връзка с този въпросник. Моля използвайте линк-а, който се съдържа в еmail-a.<br /><br />\n"
								   ."Администратор на въпросника {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<b>Благодаря<br /><br />"
					   ."Вие приключихте с отговорите на въпросите от този въпросник.</b><br /><br />"
					   ."Щракнете върху ["._SUBMIT."] за да завършите процеса и запишете своите отговори.");
define("_SM_REVIEW", "Ако желаете да проверите някои от отговорите си и/или да ги промените, "
					."можете да направите това сега, щраквайки върху бутона [<< "._PREV."] и прелиствайки "
					."своите отговори.");

//For the "printable" survey
define("_PS_CHOOSEONE", "Моля изберете <b>само едно от </b> следните"); //New for 0.98finalRC1
define("_PS_WRITE", "Моля напишете своя отговор тук"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Моля изберете <b>всички,</b> които съответстват"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Моля изберете всички, които съответстват и напишете коментар"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Моля изберете подходящ отговор за всеки елемент"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Моля въведете своя(-ите) отговор(и) тук"); //New for 0.98finalRC1
define("_PS_DATE", "Моля въведете дата"); //New for 0.98finalRC1
define("_PS_COMMENT", "Коментирайте своя избор тук"); //New for 0.98finalRC1
define("_PS_RANKING", "Моля номерирайте всеки правоъгълник според предпочитанията си от 1 до"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Изпратете своя въпросник"); //New for 0.98finalRC1
define("_PS_THANKYOU", "Благодаря за попълването на този въпросник."); //New for 0.98finalRC1
define("_PS_FAXTO", "Моля изпратете попълнения въпросник на следния факс:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Отговорете на този въпрос само"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "ако сте отговорили на"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "и"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "на въпрос"); //New for 0.98finalRC1
define("_PS_CON_OR", "или"); //New for 0.98finalRC2

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