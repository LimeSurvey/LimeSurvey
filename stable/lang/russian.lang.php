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
    #                                                           #
    # This language file kindly provided by ?                   #
    #                                                           #
    # Updates 2006 by Alexander Shilov - alexsh at cpart dot kz #
    #                                                           #
    #############################################################
*/
//SINGLE WORDS
define("_YES", "Да");
define("_NO", "Нет");
define("_UNCERTAIN", "Не уверен");
define("_ADMIN", "Admin");
define("_TOKENS", "Фразы");
define("_FEMALE", "Женский");
define("_MALE", "Мужской");
define("_NOANSWER", "Нет ответа");
define("_NOTAPPLICABLE", "N/A"); //New for 0.98rc5
define("_OTHER", "Другие");
define("_PLEASECHOOSE", "Пожалуйста выберите");
define("_ERROR_PS", "Ошибка");
define("_COMPLETE", "выполнить");
define("_INCREASE", "Увеличить"); //NEW WITH 0.98
define("_SAME", "Такой же"); //NEW WITH 0.98
define("_DECREASE", "Уменьшить"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Подтверждение");
define("_TOKEN_PS", "Фраза");
define("_CONTINUE_PS", "Продолжить");

//BUTTONS
define("_ACCEPT", "Принять");
define("_PREV", "пред.");
define("_NEXT", "след.");
define("_LAST", "последний");
define("_SUBMIT", "отправить");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Пожалуйста выберите одно из перечисленного");
define("_ENTERCOMMENT", "Введите пожалуйста комментарий");
define("_NUMERICAL_PS", "Возможет ввод только числа");
define("_CLEARALL", "Выйти и очистить вопросник");
define("_MANDATORY", "Этот вопрос обязателен");
define("_MANDATORY_PARTS", "Заполните все части");
define("_MANDATORY_CHECK", "Отметьте хотя бы одно");
define("_MANDATORY_RANK", "Отранжируйте элементы");
define("_MANDATORY_POPUP", "Один или несколько обязательных вопросов не отвечено. Вы не можете продолжать дальше пока не ответите."); //NEW in 0.98rc4
define("_VALIDATION", "This question must be answered correctly"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Формат: ГГГГ-MM-ДД");
define("_DATEFORMATEG", "(напр: 2003-12-25 для Рождества)");
define("_REMOVEITEM", "Удалить эл-т");
define("_RANK_1", "Выберите элемент в левом списке, начиная с");
define("_RANK_2", "наиболее предпочитаемого и перемещаясь к наменее предпочитаемому.");
define("_YOURCHOICES", "Вы выбрали");
define("_YOURRANKING", "Ваше ранжирование");
define("_RANK_3", "Кликните на  ножницы рядом с элементов с правой стороны");
define("_RANK_4", "чтобы удалить последний элемент в отранжированном списке");
//From INDEX.PHP
define("_NOSID", "Вы не указали идентифицирующий номер опросника");
define("_CONTACT1", "Пожалуйста контактируйте");
define("_CONTACT2", "для дополнительной помощи");
define("_ANSCLEAR", "Ответы очищены");
define("_RESTART", "Престартовать опрос");
define("_CLOSEWIN_PS", "Закрыть окно");
define("_CONFIRMCLEAR", "Вы действительно хотите удалить все Ваше ответы?");
define("_CONFIRMSAVE", "Are you sure you want to save your responses?");
define("_EXITCLEAR", "Выйти о очистить опросник");
//From QUESTION.PHP
define("_BADSUBMIT1", "Не могу отправить результаты - нечего отправлять.");
define("_BADSUBMIT2", "Эта ошибка возникает если Вы уже отвечали на вопросы и нажали 'Обновить' в Вашем броузере. В этом случае Ваши ответы уже сохранены.<br /><br />Если Вы получили это сообщение в середине опроса, то Вы you должны выбрать '<- НАЗАД' в Вашем броузере и затем обновить/перезагрузить предыдущую страницу. Тогда Вы потеряеете ответы на последней странице, однако остальные сохранятся. Это проблема возникает если web-сервер перегружен. Мы извиняемся за такую проблему.");
define("_NOTACTIVE1", "Выши ответы на вопросы не записаны. Этот опросник не активен.");
define("_CLEARRESP", "Очистить ответы");
define("_THANKS", "Спасибо");
define("_SURVEYREC", "Ваши ответы записаны.");
define("_SURVEYCPL", "Опрос завершен");
define("_DIDNOTSAVE", "Не сохранено");
define("_DIDNOTSAVE2", "Произошла непредвиденная ошибка и Ваши  ответы не могут быть сохранены.");
define("_DIDNOTSAVE3", "Your responses have not been lost and have been emailed to the survey administrator and will be entered into our database at a later point.");
define("_DNSAVEEMAIL1", "Произошла ошибка при сохранении опроса с идентификатором");
define("_DNSAVEEMAIL2", "ДАННЫЕ ДЛЯ ВВОДА");
define("_DNSAVEEMAIL3", "SQL КОД КОТОРЫЙ НЕ ВЫПОЛНЕН");
define("_DNSAVEEMAIL4", "СООБЩЕНИЕ ОБ ОШИБКЕ");
define("_DNSAVEEMAIL5", "ОШИБКА СОХРАНЕНИЯ");
define("_SUBMITAGAIN", "Попробуйте отправить повторно");
define("_SURVEYNOEXIST", "Извините. Нет соответсвующего вопросника.");
define("_NOTOKEN1", "Это управляемы опрос. Вам необходима правильная кл. фраза для участия.");
define("_NOTOKEN2", "Если Вы получили кл. фразу, то, пожалуста, введите ее ниже и кликните продолжить.");
define("_NOTOKEN3", "Ввведеная кл. фраза либо неправильная либо уже использована.");
define("_NOQUESTIONS", "В опросе нет еще вопросов и он не может быть проверен или отвечен.");
define("_FURTHERINFO", "Для получения информации контактируйте");
define("_NOTACTIVE", "Опросник не активен. Вы не сможете сохранить ответы.");
define("_SURVEYEXPIRED", "Это опросник больше не доступен.");

define("_SURVEYCOMPLETE", "Вы уже ответили на этот опрос."); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "Выберите только одно"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Отметьте требуемое"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Ответы сохранены"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Новый ответ добавлен в опросник"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Выбарите следующую ссылку для просмота индивидуальный ответов:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Для просмотра статистики нажмите здесь:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Click the following link to edit the individual response:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>Замечания о приватности</i></strong><br />"
						  ."Этот опрос являкется анонимным.<br />"
						  ."Запись о Ваших ответах на вопрс не содержит никакой "
						  ."идентифицирующей информации о Вас, если специфичиский вопрос "
						  ."опросника не спрашивает о ней. Если Вы отвечали на опрос с "
						  ."использованием идентифицирующих кл. фраз для доступа к нему, "
						  ."Вы можете быть уверены что кл. фраза не сохранена с Вашими ответами."
						  ."Она хранится в отдельной БД и обновляется только для индикации того "
						  ."что Вы ответили или нет на этот опрос. "
						  ."Не существует способа для связывания кл. фраз и ответов на опрос."); //New for 0.98rc9


define("_THEREAREXQUESTIONS", "Всего {NUMBEROFQUESTIONS} вопросов в этом опросе."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Всего 1 вопрос в этом опросе."); //New for 0.98rc9 - singular version of above
						  
define ("_RG_REGISTER1", "Вы должны зарегистрироваться для ответа на опрос"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Вы должны зарегистрироваться для участи в опросе.<br />\n"
						."Введите Ваши данные ниже, и email со ссылкой на опрос "
						."для участия будет сразу выслана."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Email адрес"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Имя"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Фамилия"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "Введенный email некорретен. Попробуйте снова.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "Введенный Вами email уже зарегистрирован.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} подвержение регистрации");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Благодарим за регистрацию для участия в опросе.<br /><br />\n"
								   ."Email отправлено на адрес, который Вы указали в данный для участия "
								   ."в этом опросе. Пожалуйста выберите ссылку в письме для продолжения.<br /><br />\n"
								   ."Администратор опроса {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Благодарим Вас<br /><br />"
					   ."Вы полностью ответили на вопросы опроса.</strong><br /><br />"
					   ."Нажмите на ["._SUBMIT."] для завершения процесса и сохранения ответов.");
define("_SM_REVIEW", "Если Вы хотите проверить свои ответы, и/или изменить их, "
					."Вы можете нажать на [<< "._PREV."] кнопку и пройтись "
					."по Вашим ответам.");

//For the "printable" survey
define("_PS_CHOOSEONE", "Пожалуйста выберите <strong>только одно</strong> из перечисленного"); //New for 0.98finalRC1
define("_PS_WRITE", "Пожалуйста напишите Ваш ответ здесь"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Пожалуйста отметьте <strong>все</strong> необходимое"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Пожалуйства выберите все что соответсвует и дайте комментарий"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Пожалуйства выберите соответствующий ответ для каждого из элементов"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Пожалуйста напишите Ваш ответ(ы) здесь"); //New for 0.98finalRC1
define("_PS_DATE", "Пожалуйста введите дату"); //New for 0.98finalRC1
define("_PS_COMMENT", "Прокомментируйте Ваш выбор здесь"); //New for 0.98finalRC1
define("_PS_RANKING", "пронумеруйте в порядке предпочтения каждый элемент от 1 до"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Отправить Ваш опрос"); //New for 0.98finalRC1
define("_PS_THANKYOU", "Благодарим за участие в опросе."); //New for 0.98finalRC1
define("_PS_FAXTO", "Пожалуйста отправьте заполненный опрос по факсу:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Просто ответьте на вопрос"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "есл Вы ответили"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "и"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "к вопросу"); //New for 0.98finalRC1
define("_PS_CON_OR", "or"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Сохранить ответы и вернуться позже");
define("_SAVEHEADING", "Сохранение незаконченного опроса");
define("_RETURNTOSURVEY", "Вернуться к опросу");
define("_SAVENAME", "Логин");
define("_SAVEPASSWORD", "Пароль");
define("_SAVEPASSWORDRPT", "Повторить пароль");
define("_SAVE_EMAIL", "Ваш E-mail");
define("_SAVEEXPLANATION", "Введите логин и пароль к этому опросу и сохраните Ваш незаконченный опрос.<br />\n"
				  ."Вы сможете вернуться к опросу позже, введя этот логин и пароль и продолжить отвечать на вопросы.<br /><br />\n"
				  ."Если Вы ввели e-mail, то на него придет письмо с подробным описанием.");
define("_SAVESUBMIT", "сохранить");
define("_SAVENONAME", "Вы должны ввести логин для сохраненного опроса.");
define("_SAVENOPASS", "Вы должны ввести пароль для сохраненного опроса.");
define("_SAVENOMATCH", "Пароль не совпадает, введите еще раз");
define("_SAVEDUPLICATE", "Такой логин уже используется в настоящий момент. Введите пожалуйста другой уникальный логин.");
define("_SAVETRYAGAIN", "Попробуйте еще раз.");
define("_SAVE_EMAILSUBJECT", "Saved Survey Details");
define("_SAVE_EMAILTEXT", "Вы, или кто-то другой используя Ваш e-mail, сохранил незаконченный опрос."
						 ."Используя нижепреведенные логин, пароль и ссылку Вы можете вернуться и продолжить опрос "
						 ."с того места где остановились..");
define("_SAVE_EMAILURL", "Чтобы загрузить опрос нижмите эту ссылку:");
define("_SAVE_SUCCEEDED", "Ваши ответы были полностью сохранены");
define("_SAVE_FAILED", "При сохранении Ваших ответов возникла ошибка.");
define("_SAVE_EMAILSENT", "Сообщение с логином, паролем и ссылкой сохраненного опроса было послано на указанный e-mail.");



//Load Messages
define("_LOAD_SAVED", "Загрузить незаконченный опрос");
define("_LOADHEADING", "Загрузить сохраненный опрос");
define("_LOADEXPLANATION", "Вы можете загрузить сохраненный опрос.<br />\n"
			  ."Введите логин и пароль для Вашего сохраненного опроса.<br /><br />\n");
define("_LOADNAME", "Логин");
define("_LOADPASSWORD", "Пароль");
define("_LOADSUBMIT", "Загрузить");
define("_LOADNONAME", "Вы не ввели логин");
define("_LOADNOPASS", "Вы не ввели пароль");
define("_LOADNOMATCH", "Такого сохраненного опроса не найдено");

define("_ASSESSMENT_HEADING", "Your Assessment");
?>
