<?php
//Translation kindly provided by Alexei G. Tchernov  (alexei_g_chernov[at]mail[dot]ru)

//BUTTON BAR TITLES
define("_ADMINISTRATION", "Администрирование");
define("_SURVEY", "Опрос");
define("_GROUP", "Группа");
define("_QUESTION", "Вопрос");
define("_ANSWERS", "Ответы");
define("_CONDITIONS", "Условия");
define("_HELP", "Справка");
define("_USERCONTROL", "Управление пользователями");
define("_ACTIVATE", "Активировать опросник");
define("_DEACTIVATE", "Деактивировать опросник");
define("_CHECKFIELDS", "Проверить поля БД");
define("_CREATEDB", "Создать БД");
define("_CREATESURVEY", "Создать опрос"); //New for 0.98rc4
define("_SETUP", "PHPSurveyor Setup");
define("_DELETESURVEY", "Удалить опрос");
define("_EXPORTQUESTION", "Export вопроса");
define("_EXPORTSURVEY", "Export опроса");
define("_EXPORTLABEL", "Export Набора меток");
define("_IMPORTQUESTION", "Import вопроса");
define("_IMPORTGROUP", "Import группы"); //New for 0.98rc5
define("_IMPORTSURVEY", "Import опроса");
define("_IMPORTLABEL", "Import набора меток");
define("_EXPORTRESULTS", "Export результатов");
define("_BROWSERESPONSES", "Смотреть результаты");
define("_BROWSESAVED", "Browse Saved Responses");
define("_STATISTICS", "Быстрая статистика");
define("_VIEWRESPONSE", "Смотреть ответы");
define("_VIEWCONTROL", "Управление просмотром данных");
define("_DATAENTRY", "Ввод данных");
define("_TOKENCONTROL", "Управление Ключ. фразами");
define("_TOKENDBADMIN", "Опции администрирования БД Ключ. фраз");
define("_DROPTOKENS", "Удалить таблицу ключ.фраз");
define("_EMAILINVITE", "Email приглашение");
define("_EMAILREMIND", "Email напоминание");
define("_TOKENIFY", "Создать Ключ. фразы");
define("_UPLOADCSV", "Загрузить CSV File");
define("_LABELCONTROL", "Администрировать наборы меток"); //NEW with 0.98rc3
define("_LABELSET", "Набор меток"); //NEW with 0.98rc3
define("_LABELANS", "Метки"); //NEW with 0.98rc3
define("_OPTIONAL", "Необязательно"); //NEW with 0.98finalRC1

//DROPDOWN HEADINGS
define("_SURVEYS", "Опросы");
define("_GROUPS", "Группы");
define("_QUESTIONS", "Вопросы");
define("_QBYQ", "Вопрос за вопросом");
define("_GBYG", "Группа за группой");
define("_SBYS", "Все в одном");
define("_LABELSETS", "Наборы"); //New with 0.98rc3

//BUTTON MOUSEOVERS
//administration bar
define("_A_HOME_BT", "Административная страница по умолчанию");
define("_A_SECURITY_BT", "Изменение установок безопастности");
define("_A_BADSECURITY_BT", "Активизировать режим безопасности");
define("_A_CHECKDB_BT", "Проверить БД");
define("_A_DELETE_BT", "Удалить опрос полностью");
define("_A_ADDSURVEY_BT", "Создать или импортировать новый опрос");
define("_A_HELP_BT", "Показать справку");
define("_A_CHECKSETTINGS", "Проверить установки");
define("_A_BACKUPDB_BT", "Резерное копирование всей БД"); //New for 0.98rc10
define("_A_TEMPLATES_BT", "Редактор шаблонов"); //New for 0.98rc9
//Survey bar
define("_S_ACTIVE_BT", "Опрос активен");
define("_S_INACTIVE_BT", "Опрос неактивен");
define("_S_ACTIVATE_BT", "Активизировать опрос");
define("_S_DEACTIVATE_BT", "Деактивизировать опрос");
define("_S_CANNOTACTIVATE_BT", "Не могу активизировать опрос");
define("_S_DOSURVEY_BT", "Провести опрос");
define("_S_DATAENTRY_BT", "Ввод данных для опроса");
define("_S_PRINTABLE_BT", "Версия для печати");
define("_S_EDIT_BT", "Изменение текущего опроса");
define("_S_DELETE_BT", "Удалить текущий опрос");
define("_S_EXPORT_BT", "Экспортировать опрос");
define("_S_BROWSE_BT", "Показать ответы на опрос");
define("_S_TOKENS_BT", "Активизировать/Изменить Ключ. фразы для опроса");
define("_S_ADDGROUP_BT", "Добавить новую группу в опрос");
define("_S_MINIMISE_BT", "Спрятать детали опроса");
define("_S_MAXIMISE_BT", "Показать  детали опроса");
define("_S_CLOSE_BT", "Закрыть опрос");
define("_S_SAVED_BT", "View Saved but not submitted Responses"); //New in 0.99dev01
define("_S_ASSESSMENT_BT", "Set assessment rules"); //New in  0.99dev01
//Group bar
define("_G_EDIT_BT", "Редактировать текущую группу");
define("_G_EXPORT_BT", "Export текущей группы"); //New in 0.98rc5
define("_G_DELETE_BT", "Удалить текущую группу");
define("_G_ADDQUESTION_BT", "Добавить новый вопрос в группу");
define("_G_MINIMISE_BT", "Спрятать детали группы");
define("_G_MAXIMISE_BT", "Показать детали группы");
define("_G_CLOSE_BT", "Закрыть группу");
//Question bar
define("_Q_EDIT_BT", "Редактировать Текущий Вопрос");
define("_Q_COPY_BT", "Копировать Текущий Вопрос"); //New in 0.98rc4
define("_Q_DELETE_BT", "Удалить Текущий Вопрос");
define("_Q_EXPORT_BT", "Export Вопрос");
define("_Q_CONDITIONS_BT", "Установить условия для Вопроса");
define("_Q_ANSWERS_BT", "Редактировать/Добавить ответы для Вопроса");
define("_Q_LABELS_BT", "Редактировать/Добавить Наборы Меток");
define("_Q_MINIMISE_BT", "Спрятать детали для этого Вопроса");
define("_Q_MAXIMISE_BT", "Показать детали для этого Вопроса");
define("_Q_CLOSE_BT", "Закрыть Вопрос");
//Browse Button Bar
define("_B_ADMIN_BT", "Вернуться к редактированию опросов");
define("_B_SUMMARY_BT", "Показать итоговую информацию");
define("_B_ALL_BT", "Показать ответы");
define("_B_LAST_BT", "Показать последних 50 ответов");
define("_B_STATISTICS_BT", "Получить статистику по этим ответам");
define("_B_EXPORT_BT", "Export результатов в приложения");
define("_B_BACKUP_BT", "Резервное копирование таблицы результатов как SQL файл");
//Tokens Button Bar
define("_T_ALL_BT", "Показать Ключ. фразы");
define("_T_ADD_BT", "Добавить новую Ключ. фразу");
define("_T_IMPORT_BT", "Импорт Ключ. фраз из CSV файла");
define("_T_EXPORT_BT", "Export Ключ. фраз в CSV файл"); //New for 0.98rc7
define("_T_INVITE_BT", "Послать email приглашение");
define("_T_REMIND_BT", "Послать email напоминание");
define("_T_TOKENIFY_BT", "Генирировать Ключ. фразы");
define("_T_KILL_BT", "Уничтожить таблицу Ключ. фраз");
//Labels Button Bar
define("_L_ADDSET_BT", "Добавить набор меток");
define("_L_EDIT_BT", "Редактировать набор меток");
define("_L_DEL_BT", "Удалить набор меток");
//Datacontrols
define("_D_BEGIN", "Показать начальное..");
define("_D_BACK", "Показать предыдущее..");
define("_D_FORWARD", "Показать следующее..");
define("_D_END", "Показать следующее..");

//DATA LABELS
//surveys
define("_SL_TITLE", "Заголовок:");
define("_SL_SURVEYURL", "Страница опроса (URL):"); //new in 0.98rc5
define("_SL_DESCRIPTION", "Описание:");
define("_SL_WELCOME", "Приветствие:");
define("_SL_ADMIN", "Администратор:");
define("_SL_EMAIL", "Admin Email:");
define("_SL_FAXTO", "Fax:");
define("_SL_ANONYMOUS", "Анонимный?");
define("_SL_EXPIRES", "Окончание:");
define("_SL_FORMAT", "Формат:");
define("_SL_DATESTAMP", "Отмечать время?");
define("_SL_TEMPLATE", "Шаблон:");
define("_SL_LANGUAGE", "Язык:");
define("_SL_LINK", "Ссылка:");
define("_SL_URL", "Финальный URL:");
define("_SL_URLDESCRIP", "URL описание:");
define("_SL_STATUS", "Статус:");
define("_SL_SELSQL", "Выберите SQL файл:");
define("_SL_USECOOKIES", "Использовать Cookies?"); //NEW with 098rc3
define("_SL_NOTIFICATION", "Извещение:"); //New with 098rc5
define("_SL_ALLOWREGISTER", "Разрешить публичную регистрацию?"); //New with 0.98rc9
define("_SL_ATTRIBUTENAMES", "Наименования атрибутов фраз:"); //New with 0.98rc9
define("_SL_EMAILINVITE_SUBJ", "Invitation Email Subject:"); //New with 0.99dev01
define("_SL_EMAILINVITE", "Email приглашение:"); //New with 0.98rc9
define("_SL_EMAILREMIND_SUBJ", "Email Reminder Subject:"); //New with 0.99dev01
define("_SL_EMAILREMIND", "Email напоминание:"); //New with 0.98rc9
define("_SL_EMAILREGISTER_SUBJ", "Public registration Email Subject:"); //New with 0.99dev01
define("_SL_EMAILREGISTER", "Email публичной регистрации:"); //New with 0.98rc9
define("_SL_EMAILCONFIRM_SUBJ", "Confirmation Email Subject"); //New with 0.99dev01
define("_SL_EMAILCONFIRM", "Email подтверждения"); //New with 0.98rc9
define("_SL_REPLACEOK", "Это изменит существующий текст. Продолжить?"); //New with 0.98rc9
define("_SL_ALLOWSAVE", "Allow Saves?"); //New with 0.99dev01
define("_SL_AUTONUMBER", "Start ID numbers at:"); //New with 0.99dev01
define("_SL_AUTORELOAD", "Automatically load URL when survey complete?"); //New with 0.99dev01
define("_SL_ALLOWPREV", "Show [<< Prev] button"); //New with 0.99dev01
define("_SL_USE_DEFAULT","Использовать умолчание");
define("_SL_UPD_SURVEY","Обновить опрос");

//groups
define("_GL_TITLE", "Заголовок:");
define("_GL_DESCRIPTION", "Описание:");
define("_GL_EDITGROUP","Edit Group for Survey ID"); // New with 0.99dev02
define("_GL_UPDATEGROUP","Update Group"); // New with 0.99dev02
//questions
define("_QL_EDITQUESTION", "Edit Question");// New with 0.99dev02
define("_QL_UPDATEQUESTION", "Update Question");// New with 0.99dev02
define("_QL_CODE", "Код:");
define("_QL_QUESTION", "Вопрос:");
define("_QL_VALIDATION", "Validation:"); //New in VALIDATION VERSION
define("_QL_HELP", "Помощь:");
define("_QL_TYPE", "Ввод:");
define("_QL_GROUP", "Группа:");
define("_QL_MANDATORY", "Обязательность:");
define("_QL_OTHER", "Другое:");
define("_QL_LABELSET", "Набор меток:");
define("_QL_COPYANS", "Копировать ответы?"); //New in 0.98rc3
define("_QL_QUESTIONATTRIBUTES", "Question Attributes:"); //New in 0.99dev01
define("_QL_COPYATT", "Copy Attributes?"); //New in 0.99dev01
//answers
define("_AL_CODE", "Код");
define("_AL_ANSWER", "Ответ");
define("_AL_DEFAULT", "По умолчанию");
define("_AL_MOVE", "Переместить");
define("_AL_ACTION", "Действие");
define("_AL_UP", "Вверх");
define("_AL_DN", "Вниз");
define("_AL_SAVE", "Сохранить");
define("_AL_DEL", "Удалить");
define("_AL_ADD", "Добавить");
define("_AL_FIXSORT", "Сохранить сортировку");
define("_AL_SORTALPHA", "Сортировать по алфавиту"); //New in 0.98rc8 - Sort Answers Alphabetically
//users
define("_UL_USER", "Пользователь");
define("_UL_PASSWORD", "Пароль");
define("_UL_SECURITY", "Режим безопастности");
define("_UL_ACTION", "Действие");
define("_UL_EDIT", "Изменить");
define("_UL_DEL", "Удалить");
define("_UL_ADD", "Добавить");
define("_UL_TURNOFF", "Выключить Режим безопастности");
//tokens
define("_TL_FIRST", "Имя");
define("_TL_LAST", "Фамилия");
define("_TL_EMAIL", "Email");
define("_TL_TOKEN", "Ключ. фраза");
define("_TL_INVITE", "Приглашение отправлено?");
define("_TL_DONE", "Окончено?");
define("_TL_ACTION", "Действия");
define("_TL_ATTR1", "Att_1"); //New for 0.98rc7
define("_TL_ATTR2", "Att_2"); //New for 0.98rc7
define("_TL_MPID", "MPID"); //New for 0.98rc7
//labels
define("_LL_NAME", "Имя набора"); //NEW with 098rc3
define("_LL_CODE", "Код"); //NEW with 098rc3
define("_LL_ANSWER", "Название"); //NEW with 098rc3
define("_LL_SORTORDER", "Порядок"); //NEW with 098rc3
define("_LL_ACTION", "Действие"); //New with 098rc3

//QUESTION TYPES
define("_5PT", "5 баллов выбор");
define("_DATE", "Дата");
define("_GENDER", "Пол");
define("_LIST", "Список");
define("_LIST_DROPDOWN", "List (Dropdown)"); //New with 0.99dev01
define("_LISTWC", "Список с комментарием");
define("_MULTO", "Множественный выбор");
define("_MULTOC", "Множественный выбор с комментарием");
define("_MULTITEXT", "Множественный короткий текст");
define("_NUMERICAL", "Числовой ввод");
define("_RANK", "Ранжирование");
define("_STEXT", "Произвольный короткий текст");
define("_LTEXT", "Произвольный длинный текст");
define("_HTEXT", "Huge free text"); //New with 0.99dev01
define("_YESNO", "Да/Нет");
define("_ARR5", "Массив (5-ти бальный выбор)");
define("_ARR10", "Массив (10-ти бальный выбор)");
define("_ARRYN", "Массив  (Да/Нет/Не знаю)");
define("_ARRMV", "Массив  (Увеличить, Тоже, Уменьшить)");
define("_ARRFL", "Массив  (Гибкие метки)"); //Release 0.98rc3
define("_ARRFLC", "Массив (Гибкие метки) по колонке"); //Release 0.98rc8
define("_SINFL", "Единичное (Гибкие метки)"); //(FOR LATER RELEASE)
define("_EMAIL", "Email адрес"); //FOR LATER RELEASE
define("_BOILERPLATE", "Горячий (Boilerplate) вопрос"); //New in 0.98rc6
define("_LISTFL_DROPDOWN", "List (Flexible Labels) (Dropdown)"); //New in 0.99dev01
define("_LISTFL_RADIO", "List (Flexible Labels) (Radio)"); //New in 0.99dev01

//GENERAL WORDS AND PHRASES
define("_AD_YES", "Да");
define("_AD_NO", "Нет");
define("_AD_CANCEL", "Отмена");
define("_AD_CHOOSE", "Пожалуйста выберите..");
define("_AD_OR", "ИЛИ"); //New in 0.98rc4
define("_ERROR", "Ошибка");
define("_SUCCESS", "Успех");
define("_REQ", "*Требуется");
define("_ADDS", "Добавить опрос");
define("_ADDG", "Добавить группу");
define("_ADDQ", "Добавить вопрос");
define("_ADDA", "Добавить ответ"); //New in 0.98rc4
define("_COPYQ", "Копировать вопрос"); //New in 0.98rc4
define("_ADDU", "Добавить пользователя");
define("_SEARCH", "Поиск"); //New in 0.98rc4
define("_SAVE", "Сохранить изменения");
define("_NONE", "None"); //as in "Do not display anything", "or none chosen";
define("_GO_ADMIN", "Главный административная страница"); //text to display to return/display main administration screen
define("_CONTINUE", "Продолжить");
define("_WARNING", "Предупреждение");
define("_USERNAME", "Имя пользователя");
define("_PASSWORD", "Пароль");
define("_DELETE", "Удалить");
define("_CLOSEWIN", "Закрыть окно");
define("_TOKEN", "Ключ. фраза");
define("_DATESTAMP", "Дата и время"); //Referring to the datestamp или time response submitted
define("_COMMENT", "Комментарий");
define("_FROM", "От"); //For emails
define("_SUBJECT", "Тема"); //For emails
define("_MESSAGE", "Сообщение"); //For emails
define("_RELOADING", "Обновление экрана. Ждите...");
define("_ADD", "Добавить");
define("_UPDATE", "Обновить");
define("_BROWSE", "Просмотреть"); //New in 098rc5
define("_AND", "И"); //New with 0.98rc8
define("_SQL", "SQL"); //New with 0.98rc8
define("_PERCENTAGE", "Процент"); //New with 0.98rc8
define("_COUNT", "Считать"); //New with 0.98rc8

//SURVEY STATUS MESSAGES (new in 0.98rc3)
define("_SS_NOGROUPS", "Число групп в опросе:"); //NEW for release 0.98rc3
define("_SS_NOQUESTS", "Число вопросов в опросе:"); //NEW for release 0.98rc3
define("_SS_ANONYMOUS", "Этот опрос анонимный."); //NEW for release 0.98rc3
define("_SS_TRACKED", "Этот опрос НЕ анонимный."); //NEW for release 0.98rc3
define("_SS_DATESTAMPED", "Ответы имеют дату"); //NEW for release 0.98rc3
define("_SS_COOKIES", "Используются cookie для контроля доступа."); //NEW for release 0.98rc3
define("_SS_QBYQ", "Форма \"Вопрос за Вопросом\"."); //NEW for release 0.98rc3
define("_SS_GBYG", "Форма \"Группа за Группой\"."); //NEW for release 0.98rc3
define("_SS_SBYS", "Форма \"На одной странице\"."); //NEW for release 0.98rc3
define("_SS_ACTIVE", "Опрос активен."); //NEW for release 0.98rc3
define("_SS_NOTACTIVE", "Опрос неактивен."); //NEW for release 0.98rc3
define("_SS_SURVEYTABLE", "Имя таблицы опроса:"); //NEW for release 0.98rc3
define("_SS_CANNOTACTIVATE", "Опрос не может быть активирован."); //NEW for release 0.98rc3
define("_SS_ADDGROUPS", "Необходимо добавить группы"); //NEW for release 0.98rc3
define("_SS_ADDQUESTS", "Необходимо добавить вопросы"); //NEW for release 0.98rc3
define("_SS_ALLOWREGISTER", "Если ключ. фразы используются, то любой может зарегистрироваться для этого опроса"); //NEW for release 0.98rc9
define("_SS_ALLOWSAVE", "Participants can save partially finished surveys"); //NEW for release 0.99dev01

//QUESTION STATUS MESSAGES (new in 0.98rc4)
define("_QS_MANDATORY", "Обязательный вопрос"); //New for release 0.98rc4
define("_QS_OPTIONAL", "Необязательный вопрос"); //New for release 0.98rc4
define("_QS_NOANSWERS", "Необходимо добавить ответы к этому вопросу"); //New for release 0.98rc4
define("_QS_NOLID", "Надо выдать набор меток для этого вопроса"); //New for release 0.98rc4
define("_QS_COPYINFO", "Заметьте: Вы ДОЛЖНЫ ввести новый код вопроса"); //New for release 0.98rc4

//General Setup Messages
define("_ST_NODB1", "Указанная БД не существует");
define("_ST_NODB2", "Либо выранная БД не создана или проблема доступа к ней.");
define("_ST_NODB3", "PHPSurveyor может попытаться создать БД для Вас.");
define("_ST_NODB4", "Имя выбранной БД:");
define("_ST_CREATEDB", "Создать БД");

//USER CONTROL MESSAGES
define("_UC_CREATE", "Создание htaccess файла по умолчанию");
define("_UC_NOCREATE", "Не могу создать htaccess файл. Проверьте в файле config.php строку с \$homedir установками и наличие прав на запись в соответсвующий каталог.");
define("_UC_SEC_DONE", "Настройте уровни режима безопастности!");
define("_UC_CREATE_DEFAULT", "Создание пользователей по умолчанию");
define("_UC_UPDATE_TABLE", "Обновление таблицы пользователей");
define("_UC_HTPASSWD_ERROR", "Ошибка при создании htpasswd файла");
define("_UC_HTPASSWD_EXPLAIN", "Если Вы используете windows сервер, то рекомендуем скопировать  htpasswd.exe файл из Apache в Ваш каталог admin  для правитьной работы. Этот файл обычно находится в каталоге /apache group/apache/bin/");
define("_UC_SEC_REMOVE", "Удаление настроек безопастности");
define("_UC_ALL_REMOVED", "Файл доступа, файл паролей и БД пользователей удалены");
define("_UC_ADD_USER", "Добавление пользователя");
define("_UC_ADD_MISSING", "Не могу добавить пользователя. Имя и/или пароль не указаны");
define("_UC_DEL_USER", "Удаление пользователя");
define("_UC_DEL_MISSING", "Не могу удалить пользователя. Имя не указано.");
define("_UC_MOD_USER", "Редактирование пользователя");
define("_UC_MOD_MISSING", "Не могу изменить пользователя. Имя и/или пароль не указаны");
define("_UC_TURNON_MESSAGE1", "Вы не инициализировали настройки безопастности для системы опросов и, следовательно, нет ограничений доступа.</p>\nЕсли Вы кликните на кнопку 'Инициализировать режим безопастности' ниже, стандартные средства безопастности APACHE будут задействованы для административной части системы опросов. Вам потребуется использовать имя пользователя и пароль по  умолчанию для доступа к админитративному интерфейсу.");
define("_UC_TURNON_MESSAGE2", "Настоятельно рекомендуется после инициализации режима безопастности сменить пароль по умолчанию.");
define("_UC_INITIALISE", "Инициализировать Режим безопастности");
define("_UC_NOUSERS", "Нет ни одного пользователя. Рекомендуем 'Выключить' безопасный режим. Вы сможете снова включить его позже.");
define("_UC_TURNOFF", "Выключить Режим безопастности");

//Activate and deactivate messages
define("_AC_MULTI_NOANSWER", "Этот вопрос с типом 'множественный ответ', однако без ответов.");
define("_AC_NOTYPE", "Не установлен тип вопроса.");
define("_AC_NOLID", "Этот вопрос требует набора меток, но набор не определен."); //New for 0.98rc8
define("_AC_CON_OUTOFORDER", "Этот вопрос содержит набор условий, однако условие базируется на вопросе, который идет за ним.");
define("_AC_FAIL", "Опрос не прошел проверку на целостность");
define("_AC_PROBS", "Обнаружены след. проблемы:");
define("_AC_CANNOTACTIVATE", "Опрос не может быть активирован пока эти проблемы не будут решены");
define("_AC_READCAREFULLY", "ВНИМАТЕЛЬНО ПРОЧИТАЙТЕ ПЕРЕД ПРОДОЛЖЕНИЕМ");
define("_AC_ACTIVATE_MESSAGE1", "You should only activate a survey when you are absolutely certain that your survey setup is finished and will not need changing.");
define("_AC_ACTIVATE_MESSAGE2", "Once a survey is activated you can no longer:<ul><li>Добавить или delete groups</li><li>Добавить или remove answers to Multiple Answer questions</li><li>Добавить или delete questions</li></ul>");
define("_AC_ACTIVATE_MESSAGE3", "Однако Вы все же можете:<ul><li>Редактировать (менять) коды вопросов, текст или тип</li><li>Редактировать (менять) имена групп</li><li>Добавлять, Удалять или Редактировать предопределенные ответы (исключая вопрoсы с многожественными ответами)</li><li>Менять имя опроса или его описание</li></ul>");
define("_AC_ACTIVATE_MESSAGE4", "Once data has been entered into this survey, if you want to add или remove groups или questions, you will need to de-activate this survey, which will move all data that has already been entered into a seperate archived table.");
define("_AC_ACTIVATE", "Активировать");
define("_AC_ACTIVATED", "Опрос активирован. Табица результатов успешно создана.");
define("_AC_NOTACTIVATED", "Опрос не может быть активирован.");
define("_AC_NOTPRIVATE", "Это неанонимный опрос. Таблица ключ. фраз также создана.");
define("_AC_REGISTRATION", "Это опрос позволяет проводить регистрацию. Таблица кл. фраз тоже создана.");
define("_AC_CREATETOKENS", "Инициализация ключ. фраз");
define("_AC_SURVEYACTIVE", "Опрос активирован, и ответы могут сохраняться.");
define("_AC_DEACTIVATE_MESSAGE1", "In an active survey, a table is created to store all the data-entry records.");
define("_AC_DEACTIVATE_MESSAGE2", "When you de-activate a survey all the data entered in the original table will be moved elsewhere, and when you activate the survey again, the table will be empty. You will not be able to access this data using PHPSurveyor any more.");
define("_AC_DEACTIVATE_MESSAGE3", "De-activated survey data can only be accessed by system administrators using a MySQL data access tool like phpmyadmin. If your survey uses tokens, this table will also be renamed and will only be accessible by system administrators.");
define("_AC_DEACTIVATE_MESSAGE4", "Your responses table will be renamed to:");
define("_AC_DEACTIVATE_MESSAGE5", "You should export your responses before de-activating. Click \"Cancel\" to return to the main admin screen without de-activating this survey.");
define("_AC_DEACTIVATE", "Деактивировать");
define("_AC_DEACTIVATED_MESSAGE1", "Таблица ответов переименована в: ");
define("_AC_DEACTIVATED_MESSAGE2", "Ответы на этот опрос больше не могут использоваться PHPSurveyor.");
define("_AC_DEACTIVATED_MESSAGE3", "Для доальнейшего доступа к информации Вы должны запомнить имя таблицы.");
define("_AC_DEACTIVATED_MESSAGE4", "Таблица ключ. фраз связанная с опросом переменована в: ");

//CHECKFIELDS
define("_CF_CHECKTABLES", "Проверка существования таблиц");
define("_CF_CHECKFIELDS", "проверка существования всех полей");
define("_CF_CHECKING", "Проверка");
define("_CF_TABLECREATED", "Таблица создана");
define("_CF_FIELDCREATED", "Поле создано");
define("_CF_OK", "OK");
define("_CFT_PROBLEM", "Похоже что некоторые поля или таблицы не существуют в вашей БД.");

//CREATE DATABASE (createdb.php)
define("_CD_DBCREATED", "БД создана.");
define("_CD_POPULATE_MESSAGE", "Пожалуйста клинтете для заполения БД");
define("_CD_POPULATE", "Заполнение БД");
define("_CD_NOCREATE", "Не могу создать БД");
define("_CD_NODBNAME", "Нет информации о БД. Этот скрипт может быть запущен только через admin.php.");

//DATABASE MODIFICATION MESSAGES
define("_DB_FAIL_GROUPNAME", "Группа не может быть добавлена. Опущено обязательное имя группы");
define("_DB_FAIL_GROUPUPDATE", "Группа не может быть обновлена");
define("_DB_FAIL_GROUPDELETE", "Группа не может быть удалена");
define("_DB_FAIL_NEWQUESTION", "Вопрос не может быть создан.");
define("_DB_FAIL_QUESTIONTYPECONDITIONS", "Вопрос не может быть обновлен. Есть условия для других вопросов, которые связаны с ответами на него. И изменение типа вызовет проблемы. Удалите сначала условия перед изменением типа этого вопроса.");
define("_DB_FAIL_QUESTIONUPDATE", "Вопрос  не может быть обновлен");
define("_DB_FAIL_QUESTIONDELCONDITIONS", "Вопрос  не может быть удален. Есть условия для других вопросов, которые связаны с ответами на него. Удалите сначала условия.");
define("_DB_FAIL_QUESTIONDELETE", "Вопрос не может быть удален.");
define("_DB_FAIL_NEWANSWERMISSING", "Нельзя добавить ответ. Вы должены указать и КОД и ОТВЕТ");
define("_DB_FAIL_NEWANSWERDUPLICATE", "Нельзя добавить ответ. Уже есть ответ с таким кодом");
define("_DB_FAIL_ANSWERUPDATEMISSING", "Нельзя изменить ответ. Вы должены указать и КОД и ОТВЕТ");
define("_DB_FAIL_ANSWERUPDATEDUPLICATE", "Нельзя изменить ответ. Уже есть ответ с таким кодом");
define("_DB_FAIL_ANSWERUPDATECONDITIONS", "Нельзя изменить ответ. Вы изменили код ответа, но есть уловия других вопросов, которые зависят от старого кода ответа. Вы должны удалить условия до изменения кода ответа.");
define("_DB_FAIL_ANSWERDELCONDITIONS", "Нельзя удалить ответ. Есть условия других вопросов, связанные с этим ответом. Вы не можете удалить ответ пока не удалены условия");
define("_DB_FAIL_NEWSURVEY_TITLE", "Опрос нельзя создать, так как не указан короткий заголовок");
define("_DB_FAIL_NEWSURVEY", "Нельзя создать опрос");
define("_DB_FAIL_SURVEYUPDATE", "Нельзя обновить опрос");
define("_DB_FAIL_SURVEYDELETE", "Нельзя удалить опрос");

//DELETE SURVEY MESSAGES
define("_DS_NOSID", "Вы не вбрали опрос для удаления");
define("_DS_DELMESSAGE1", "Вы удаляете этот опрос");
define("_DS_DELMESSAGE2", "Это удалит опрос и все связанные группы, ответы на вопросы и условия.");
define("_DS_DELMESSAGE3", "Мы рекомендуем перед удлением опроса экспортировать его данные.");
define("_DS_SURVEYACTIVE", "Опрос активен и существует таблица ответов. Если Вы удалите опрос, то  эти ответы будут удалены. Рекомендуем экспортировать ответы перед удалением опроса.");
define("_DS_SURVEYTOKENS", "Опрос имеет связанную таблицу кл. фраз. Удаление опроса повлечет удаление таблицы ключевых фраз. Рекомендуем экспортировать и сделать резервную копию таблицы клю фраз перед удалением опроса.");
define("_DS_DELETED", "Это опрос удален.");

//DELETE QUESTION AND GROUP MESSAGES
define("_DG_RUSURE", "Удаление этой группы полечет удаление вопрос  и ответов, содержащихся в ней. Продолжить?"); //New for 098rc5
define("_DQ_RUSURE", "Удаление вопроса повлечет удаление всех его ответов. Продолжить?"); //New for 098rc5

//EXPORT MESSAGES
define("_EQ_NOQID", "Нет QID. Не могу выгрузить вопрос.");
define("_ES_NOSID", "Нет SID. Не могу выгрузить опрос");

//EXPORT RESULTS
define("_EX_FROMSTATS", "Отфильтрованная статистика");
define("_EX_HEADINGS", "Вопросы");
define("_EX_ANSWERS", "Ответы");
define("_EX_FORMAT", "Формат");
define("_EX_HEAD_ABBREV", "Сокращенные заголовки");
define("_EX_HEAD_FULL", "Полные заголовки");
define("_EX_ANS_ABBREV", "Коды вопросов");
define("_EX_ANS_FULL", "Полные ответы");
define("_EX_FORM_WORD", "Microsoft Word");
define("_EX_FORM_EXCEL", "Microsoft Excel");
define("_EX_FORM_CSV", "CSV Comma Delimited");
define("_EX_EXPORTDATA", "Export Data");
define("_EX_COLCONTROLS", "Управление колонкой"); //New for 0.98rc7
define("_EX_TOKENCONTROLS", "Управление кл. фразами"); //New for 0.98rc7
define("_EX_COLSELECT", "Выбор столбцов"); //New for 0.98rc7
define("_EX_COLOK", "Выберите колонки для экспорта. Еслит ничего не выбрано, то экспортируются все колонки."); //New for 0.98rc7
define("_EX_COLNOTOK", "Опрос содержит более 255 колонок отвентов. Табличные приложения, такие как Excel, не могут импортировать более 255. Выберите колонки для экспота из приведенного списка."); //New for 0.98rc7
define("_EX_TOKENMESSAGE", "Ваш опрос может экспортировать кл. фразы с ответами. Выберите любые дополнительные поля для экспорта."); //New for 0.98rc7
define("_EX_TOKSELECT", "Выберите поля кл. фраз"); //New for 0.98rc7

//IMPORT SURVEY MESSAGES
define("_IS_FAILUPLOAD", "Ошибка призагрузке Вашего файла. Это может быть вызвано недостаточными провами на каталог admin.");
define("_IS_OKUPLOAD", "Файл успешно загружен.");
define("_IS_READFILE", "Чтение файла..");
define("_IS_WRONGFILE", "Это не файл опроска для PHPSurveyor. Импорт не завершен.");
define("_IS_IMPORTSUMMARY", "Итоги импорта опроса");
define("_IS_SUCCESS", "Импорт опроса закончен.");
define("_IS_IMPFAILED", "Сбой при мпорте файла опроса");
define("_IS_FILEFAILS", "AФайл не содержит данных для PHPSurveyor в правильном формате.");

//IMPORT GROUP MESSAGES
define("_IG_IMPORTSUMMARY", "Итоги импорта группы");
define("_IG_SUCCESS", "Импорт группы выполнен.");
define("_IG_IMPFAILED", "Сбой при импорте группы");
define("_IG_WRONGFILE", "Это не файл группы для PHPSurveyor. Импорт не завершен.");

//IMPORT QUESTION MESSAGES
define("_IQ_NOSID", "Нет SID (опрос). Нельзя импортировать вопрос.");
define("_IQ_NOGID", "Нет GID (группа). Нельзя импортировать вопрос");
define("_IQ_WRONGFILE", "Это не файл вопроса для PHPSurveyor. Импорт не завершен.");
define("_IQ_IMPORTSUMMARY", "Итоги импорта вопроса");
define("_IQ_SUCCESS", "Импорт вопроса завершен");

//IMPORT LABELSET MESSAGES
define("_IL_DUPLICATE", "There was a duplicate labelset, so this set was not imported. The duplicate will be used instead.");

//BROWSE RESPONSES MESSAGES
define("_BR_NOSID", "Вы не выбрали опрос для просмотра ответов.");
define("_BR_NOTACTIVATED", "Опрос не активирован. Нечего смотреть.");
define("_BR_NOSURVEY", "Нет соответсвующих опрсов.");
define("_BR_EDITRESPONSE", "Изменить");
define("_BR_DELRESPONSE", "Удалить");
define("_BR_DISPLAYING", "Отображено записей:");
define("_BR_STARTING", "Начиная с:");
define("_BR_SHOW", "Показать");
define("_DR_RUSURE", "Вы хотите удалить этот ответ?"); //New for 0.98rc6

//STATISTICS MESSAGES
define("_ST_FILTERSETTINGS", "Установки фильтрации");
define("_ST_VIEWALL", "Посмотреть итоги по всем доступным полям"); //New with 0.98rc8
define("_ST_SHOWRESULTS", "Посмотреть статистику"); //New with 0.98rc8
define("_ST_CLEAR", "Очистить"); //New with 0.98rc8
define("_ST_RESPONECONT", "Ответы содержащие"); //New with 0.98rc8
define("_ST_NOGREATERTHAN", "Число больше чем"); //New with 0.98rc8
define("_ST_NOLESSTHAN", "Число меньше чем"); //New with 0.98rc8
define("_ST_DATEEQUALS", "Дата (ГГГГ-ММ-ДД) равна"); //New with 0.98rc8
define("_ST_ORBETWEEN", "ИЛИ между"); //New with 0.98rc8
define("_ST_RESULTS", "Результаты"); //New with 0.98rc8 (Plural)
define("_ST_RESULT", "Результат"); //New with 0.98rc8 (Singular)
define("_ST_RECORDSRETURNED", "Нет записей по этому запросу"); //New with 0.98rc8
define("_ST_TOTALRECORDS", "Всего записей в опросе"); //New with 0.98rc8
define("_ST_PERCENTAGE", "Процент от общего"); //New with 0.98rc8
define("_ST_FIELDSUMMARY", "Итоговое поля для"); //New with 0.98rc8
define("_ST_CALCULATION", "Вычисление"); //New with 0.98rc8
define("_ST_SUM", "Сумма"); //New with 0.98rc8 - Mathematical
define("_ST_STDEV", "Стандартное отклонение"); //New with 0.98rc8 - Mathematical
define("_ST_AVERAGE", "Среднее"); //New with 0.98rc8 - Mathematical
define("_ST_MIN", "Минимум"); //New with 0.98rc8 - Mathematical
define("_ST_MAX", "Максимум"); //New with 0.98rc8 - Mathematical
define("_ST_Q1", "1ая Quartile (Q1)"); //New with 0.98rc8 - Mathematical
define("_ST_Q2", "2ая Quartile (Median)"); //New with 0.98rc8 - Mathematical
define("_ST_Q3", "3ья Quartile (Q3)"); //New with 0.98rc8 - Mathematical
define("_ST_NULLIGNORED", "*Незаданные значения (Null) игнорируются при вычислениях"); //New with 0.98rc8
define("_ST_QUARTMETHOD", "*Q1 и Q3 вычислены с использованием <a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>minitab method</a>"); //New with 0.98rc8

//DATA ENTRY MESSAGES
define("_DE_NOMODIFY", "Нельзя изменить");
define("_DE_UPDATE", "Обновление данных");
define("_DE_NOSID", "Вы не выбрали опрос для ввода данных.");
define("_DE_NOEXIST", "Выбранный опрос не существует");
define("_DE_NOTACTIVE", "Опрос еще не активен. Выш ответ не может быть сохранен");
define("_DE_INSERT", "Вставка данных");
define("_DE_RECORD", "Данным назначен след. идентификатор записи: ");
define("_DE_ADDANOTHER", "Добавить другую запись ");
define("_DE_VIEWTHISONE", "Посмотреть эту запись");
define("_DE_BROWSE", "Просмотр ответов");
define("_DE_DELRECORD", "Запись удалена");
define("_DE_UPDATED", "Запись обновлена.");
define("_DE_EDITING", "Редатирование ответа");
define("_DE_QUESTIONHELP", "Справка по данному вопросу");
define("_DE_CONDITIONHELP1", "Отвечать только если выполняются след. условия:"); 
define("_DE_CONDITIONHELP2", "по вопроса {QUESTION}, Вы ответили {ANSWER}"); //This will be a tricky one depending on your languages syntax. {ANSWER} is replaced with ALL ANSWERS, seperated by _DE_OR (OR).
define("_DE_AND", "И");
define("_DE_OR", "ИЛИ");
define("_DE_SAVEENTRY", "Save as a partially completed survey"); //New in 0.99dev01
define("_DE_SAVEID", "Identifier:"); //New in 0.99dev01
define("_DE_SAVEPW", "Password:"); //New in 0.99dev01
define("_DE_SAVEPWCONFIRM", "Confirm Password:"); //New in 0.99dev01
define("_DE_SAVEEMAIL", "Email:"); //New in 0.99dev01

//TOKEN CONTROL MESSAGES
define("_TC_TOTALCOUNT", "Всего записей в этой таблице кл. фраз:"); //New in 0.98rc4
define("_TC_NOTOKENCOUNT", "Всего с неуникальными кл.фразами:"); //New in 0.98rc4
define("_TC_INVITECOUNT", "Всего послано приглашений:"); //New in 0.98rc4
define("_TC_COMPLETEDCOUNT", "Всего завершено опросов:"); //New in 0.98rc4
define("_TC_NOSID", "Вы не выбрали опрос");
define("_TC_DELTOKENS", "Удаление таблицы кл.фраз для данного опроса.");
define("_TC_DELTOKENSINFO", "Если Вы удалите эту таблицу, то кл. фразы больше не будут требоваться для доступа к опросу. Будет сдлеана резервная копия таблицы если Вы продолжите. Ваш системный администратор сможет получить доступ к этой таблице.");
define("_TC_DELETETOKENS", "Удаление кл.фраз");
define("_TC_TOKENSGONE", "Таблица кл. фраз удалена, и кл. фразы не требуются для доступа к опросу. Резервная копия таблицы создана, к ней может получить доступ системный администратор.");
define("_TC_NOTINITIALISED", "Кл. фразы не инициализированы для данного опроса.");
define("_TC_INITINFO", "После инициализации кл. фраз то опрос будет только пользователям с назначенными клю фразами.");
define("_TC_INITQ", "Вы хотите создать таблицу кл. фраз для этого опроса?");
define("_TC_INITTOKENS", "Инициализация кл. фраз");
define("_TC_CREATED", "Таблица кл. фраз создана для данного опроса.");
define("_TC_DELETEALL", "Удалить все кл. фразы");
define("_TC_DELETEALL_RUSURE", "Вы уверены что хотите удалить ВСЕ кл. фразы?");
define("_TC_ALLDELETED", "Все кл. фразы удалены");
define("_TC_CLEARINVITES", "Проставить везде  'N' по отправке приглашений");
define("_TC_CLEARINV_RUSURE", "Вы дествительно хотить сбросить все записи об отправке приглашений (установить NO)?");
define("_TC_CLEARTOKENS", "Удалить все уникальные номера кл. фраз");
define("_TC_CLEARTOKENS_RUSURE", "Вы дествительно хотите удалить уникальные номера кл. фраз?");
define("_TC_TOKENSCLEARED", "Все уникальные ноера кл. фраз удалены");
define("_TC_INVITESCLEARED", "По отправке приглашений везде установлено значение N");
define("_TC_EDIT", "Изменение кл. фразы");
define("_TC_DEL", "Удаление кл. фразы");
define("_TC_DO", "Провести опрос");
define("_TC_VIEW", "Смотреть ответы");
define("_TC_INVITET", "Отправить email приглашение для этого элемента");
define("_TC_REMINDT", "Отправить email напоминание для этого элемента");
define("_TC_INVITESUBJECT", "Приглашение участнику опроса {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSUBJECT", "Напоминание участнику опроса {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSTARTAT", "Начать с TID No:");
define("_TC_REMINDTID", "Отправка для TID No:");
define("_TC_CREATETOKENSINFO", "Выбор ДА приведет к генерации кл. фраз
для всех в списке кто не имел такие. Все правильно?");
define("_TC_TOKENSCREATED", "{TOKENCOUNT} кл. фраз создано"); //Leave {TOKENCOUNT} for replacement in script with the number of tokens created
define("_TC_TOKENDELETED", "Кл.фраза удалена.");
define("_TC_SORTBY", "Сортировка по: ");
define("_TC_ADDEDIT", "Добавить или изменить кл. фразу");
define("_TC_TOKENCREATEINFO", "Вы можете оставить это пустым, и кл. фразы будут созданы при выборе 'Создать кл. фразы'");
define("_TC_TOKENADDED", "Добавлена новая кл. фраза");
define("_TC_TOKENUPDATED", "Кл. фраза обновлена");
define("_TC_UPLOADINFO", "Файл должен быть стндартным CSV (разделенным запятыми) файлом без кавычек. Первая строка должна содежать  заголовок (будет удалена). Данные должны быть упорядочены как  \"Имя, Фамилия, email, [кл.фраза], [attribute1], [attribute2]\".");
define("_TC_UPLOADFAIL", "Файл для загрузки не обнаружен. Проверьте Ваши права и путь к каталогу загрузки"); //New for 0.98rc5
define("_TC_IMPORT", "Импорт CSV файла");
define("_TC_CREATE", "Создание кл. фраз");
define("_TC_TOKENS_CREATED", "{TOKENCOUNT} записей создано");
define("_TC_NONETOSEND", "Нет отобранных email адресов для отправки. Это произошло из-за отсутствия удовлетворяющих критерию - есть email адрес, еще не отправлено приглашение, уже закончил опрос и имеет кл.фразу.");
define("_TC_NOREMINDERSTOSEND", "Нет отобранных email адресов для отправки. Это произошло из-за отсутствия удовлетворяющих критерию - есть email адрес, уже отправлено приглашение, но еще закончил опрос.");
define("_TC_NOEMAILTEMPLATE", "Шаблон приглашения не найден. Это файл должен существовать в каталоге шаблонов по умолчанию.");
define("_TC_NOREMINDTEMPLATE", "Шаблон напоминания не найден.  Это файл должен существовать в каталоге шаблонов по умолчанию.");
define("_TC_SENDEMAIL", "Отправить приглашения");
define("_TC_SENDINGEMAILS", "Отправка приглашений");
define("_TC_SENDINGREMINDERS", "Отправка напоминаний");
define("_TC_EMAILSTOGO", "Есть email письма ожидающие отправки одним пакетом. Для продолжения отправки писем нажмите ниже.");
define("_TC_EMAILSREMAINING", "Еще {EMAILCOUNT} писем для отправки."); //Leave {EMAILCOUNT} for replacement in script by number of emails remaining
define("_TC_SENDREMIND", "Отправить наминания");
define("_TC_INVITESENTTO", "Приглашение оправлено:"); //is followed by token name
define("_TC_REMINDSENTTO", "Напоминание отправлено:"); //is followed by token name
define("_TC_UPDATEDB", "Обновление таблицы кл. фраз с новыми полями"); //New for 0.98rc7
define("_TC_EMAILINVITE_SUBJ", "Invitation to participate in survey"); //New for 0.99dev01
define("_TC_EMAILINVITE", "Уважаемая/-ый {FIRSTNAME},\n\nВы приглашения для участия в опросе.\n\n"
						 ."Опрос называется:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."Для участия, пожалуйста выберите ссылку внизу.\n\nС уважением,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Нажмите здесь для участия в опросе:\n"
						 ."{SURVEYURL}"); //New for 0.98rc9 - default Email Invitation
define("_TC_EMAILREMIND_SUBJ", "Reminder to participate in survey"); //New for 0.99dev01
define("_TC_EMAILREMIND", "Уважаемая/-ый {FIRSTNAME},\n\nНекоторое время назад мы пригласили Вас участвовать в опросе.\n\n"
						 ."Мы заметили, что Вы еще не закончили опрос, и хотели бы напомнить Вам что опрос еще доступен если Вы захотите принять в нем участие.\n\n"
						 ."Опрос называется:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."Для участия, пожалуйста выберите ссылку внизу.\n\nС уважением,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Нажмите здесь для участия в опросе:\n"
						 ."{SURVEYURL}"); //New for 0.98rc9 - default Email Reminder
define("_TC_EMAILREGISTER_SUBJ", "Survey Registration Confirmation"); //New for 0.99dev01
define("_TC_EMAILREGISTER", "Уважаемая/-ый {FIRSTNAME},\n\n"
						  ."Вы, или кто-то используя Ваш email адрес, зарегистрировался для "
						  ."в онлайн опросе {SURVEYNAME}.\n\n"
						  ."Для участия опросе перерйдите по указанному адресу (URL):\n\n"
						  ."{SURVEYURL}\n\n"
						  ."Если у Вас есть вопросы по опросу или если Вы "
						  ."нем регистрировались для опроса и считаете это письмо "
						  ."ошибочным, пожалуйста, сообщите {ADMINNAME} по адресу {ADMINEMAIL}.");//NEW for 0.98rc9
define("_TC_EMAILCONFIRM_SUBJ", "Confirmation of completed survey"); //New for 0.99dev01
define("_TC_EMAILCONFIRM", "Уважаемая/-ый {FIRSTNAME},\n\nЭт описьмо подтверждает, что Вы закончили опрос {SURVEYNAME} "
						  ."и Ваши ответы сохранены. Спасибо за участие.\n\n"
						  ."Если у Вас есть вопросы по данному письму, пожалуйста, свяжитесь с {ADMINNAME} по адресу {ADMINEMAIL}.\n\n"
						  ."С уважением,\n\n"
						  ."{ADMINNAME}"); //New for 0.98rc9 - Confirmation Email

//labels.php
define("_LB_NEWSET", "Создание нового набора меток");
define("_LB_EDITSET", "Изменение набора меток");
define("_LB_FAIL_UPDATESET", "Ошибка обновления набора меток");
define("_LB_FAIL_INSERTSET", "Ошибка добавления нового набора меток");
define("_LB_FAIL_DELSET", "Нельзя удалить набор меток - Есть вопросы связанные с ним. Вы должны сначала удалить этот вопрос.");
define("_LB_ACTIVEUSE", "Вы не можете изменять коды, добавлять или удалять элементы в этот набор меток так как от используется в активном опросе.");
define("_LB_TOTALUSE", "Некоторые опросы используют это набор меток. Изменяя коды, добавляя или удаляя элементы этого набора меток, Вы можете получитьнежелательные результаты в других опросах.");
//Export Labels
define("_EL_NOLID", "Не указан LID. Нельзя создать слепок набора меток.");
//Import Labels
define("_IL_GOLABELADMIN", "Вернуться к администрированию меток");

//PHPSurveyor System Summary
define("_PS_TITLE", "Общие системные данные PHPSurveyor");
define("_PS_DBNAME", "Имя базы данных");
define("_PS_DEFLANG", "Язык по умолчанию");
define("_PS_CURLANG", "Текущий язык");
define("_PS_USERS", "Пользователи");
define("_PS_ACTIVESURVEYS", "Активные опросы");
define("_PS_DEACTSURVEYS", "Деактивированные опросы");
define("_PS_ACTIVETOKENS", "Активные таблицы кл. фраз");
define("_PS_DEACTTOKENS", "Деактивированные таблицы кл. фраз");
define("_PS_CHECKDBINTEGRITY", "проверка целостности данных PHPSurveyor"); //New with 0.98rc8

//Notification Levels
define("_NT_NONE", "Нет email извещение"); //New with 098rc5
define("_NT_SINGLE", "Простое email извещение"); //New with 098rc5
define("_NT_RESULTS", "Отправка email извещения с кодами результатов"); //New with 098rc5

//CONDITIONS TRANSLATIONS
define("_CD_CONDITIONDESIGNER", "Дизайнер условий"); //New with 098rc9
define("_CD_ONLYSHOW", "Показывать вопрос {QID} только ЕСЛИ"); //New with 098rc9 - {QID} is repleaced leave there
define("_CD_AND", "И"); //New with 098rc9
define("_CD_COPYCONDITIONS", "Копирование условий"); //New with 098rc9
define("_CD_CONDITION", "Условие"); //New with 098rc9
define("_CD_ADDCONDITION", "Добавить условие"); //New with 098rc9
define("_CD_EQUALS", "Равенства"); //New with 098rc9
define("_CD_COPYRUSURE", "Вы уверены что хотите скопировать это условие(ия) в выбранные вопросы?"); //New with 098rc9
define("_CD_NODIRECT", "Вы не можете выполнить это скрипт непосредственно."); //New with 098rc9
define("_CD_NOSID", "Вы не выбрали опрос."); //New with 098rc9
define("_CD_NOQID", "Вы не выбрали вопрос."); //New with 098rc9
define("_CD_DIDNOTCOPYQ", "Вопросы не скопированы"); //New with 098rc9
define("_CD_NOCONDITIONTOCOPY", "Нет условия для копирования из"); //New with 098rc9
define("_CD_NOQUESTIONTOCOPYTO", "Нет условия для копирования в"); //New with 098rc9

//TEMPLATE EDITOR TRANSLATIONS
define("_TP_CREATENEW", "Создать новый шаблон"); //New with 098rc9
define("_TP_NEWTEMPLATECALLED", "Создать новый шаблон с именем:"); //New with 098rc9
define("_TP_DEFAULTNEWTEMPLATE", "Новый шаблон"); //New with 098rc9 (default name for new template)
define("_TP_CANMODIFY", "Этот шаблон  может быть изменен"); //New with 098rc9
define("_TP_CANNOTMODIFY", "Этот шаблон не может быть изменен"); //New with 098rc9
define("_TP_RENAME", "Переименовать этот шаблон");  //New with 098rc9
define("_TP_RENAMETO", "Переименовать этот шаблон в:"); //New with 098rc9
define("_TP_COPY", "Сделать копию этого шаблона");  //New with 098rc9
define("_TP_COPYTO", "Создать копию этого шаблона с именем:"); //New with 098rc9
define("_TP_COPYOF", "copy_of_"); //New with 098rc9 (prefix to default copy name)
define("_TP_FILECONTROL", "Управление файлами:"); //New with 098rc9
define("_TP_STANDARDFILES", "Стандартные файлы:");  //New with 098rc9
define("_TP_NOWEDITING", "Редактируется:");  //New with 098rc9
define("_TP_OTHERFILES", "Другие файлы:"); //New with 098rc9
define("_TP_PREVIEW", "Предпросмотр:"); //New with 098rc9
define("_TP_DELETEFILE", "Удалить"); //New with 098rc9
define("_TP_UPLOADFILE", "Загрузить"); //New with 098rc9
define("_TP_SCREEN", "Экран:"); //New with 098rc9
define("_TP_WELCOMEPAGE", "Страница приветствия"); //New with 098rc9
define("_TP_QUESTIONPAGE", "Страница вопроса"); //New with 098rc9
define("_TP_SUBMITPAGE", "Страница отправки");
define("_TP_COMPLETEDPAGE", "Страница завершения"); //New with 098rc9
define("_TP_CLEARALLPAGE", "Очистить все страницы"); //New with 098rc9
define("_TP_REGISTERPAGE", "Страница регистрации"); //New with 098finalRC1
define("_TP_EXPORT", "Export шаблона"); //New with 098rc10
define("_TP_LOADPAGE", "Load Page"); //New with 0.99dev01
define("_TP_SAVEPAGE", "Save Page"); //New with 0.99dev01

//Saved Surveys
define("_SV_RESPONSES", "Saved Responses:");
define("_SV_IDENTIFIER", "Identifier");
define("_SV_RESPONSECOUNT", "Answered");
define("_SV_IP", "IP Address");
define("_SV_DATE", "Date Saved");
define("_SV_REMIND", "Remind");
define("_SV_EDIT", "Edit");

//VVEXPORT/IMPORT
define("_VV_IMPORTFILE", "Import a VV survey file");
define("_VV_EXPORTFILE", "Export a VV survey file");
define("_VV_FILE", "File:");
define("_VV_SURVEYID", "Survey ID:");
define("_VV_EXCLUDEID", "Exclude record IDs?");
define("_VV_INSERT", "When an imported record matches an existing record ID:");
define("_VV_INSERT_ERROR", "Report an error (and skip the new record).");
define("_VV_INSERT_RENUMBER", "Renumber the new record.");
define("_VV_INSERT_IGNORE", "Ignore the new record.");
define("_VV_INSERT_REPLACE", "Replace the existing record.");
define("_VV_DONOTREFRESH", "Important Note:<br />Do NOT refresh this page, as this will import the file again and produce duplicates");
define("_VV_IMPORTNUMBER", "Total records imported:");
define("_VV_ENTRYFAILED", "Import Failed on Record");
define("_VV_BECAUSE", "because");
define("_VV_EXPORTDEACTIVATE", "Export, then de-activate survey");
define("_VV_EXPORTONLY", "Export but leave survey active");
define("_VV_RUSURE", "If you have chosen to export and de-activate, this will rename your current responses table and it will not be easy to restore it. Are you sure?");

//ASSESSMENTS
define("_AS_TITLE", "Assessments");
define("_AS_DESCRIPTION", "If you create any assessments in this page, for the currently selected survey, the assessment will be performed at the end of the survey after submission");
define("_AS_NOSID", "No SID Provided");
define("_AS_SCOPE", "Scope");
define("_AS_MINIMUM", "Minimum");
define("_AS_MAXIMUM", "Maximum");
define("_AS_GID", "Group");
define("_AS_NAME", "Name/Header");
define("_AS_HEADING", "Heading");
define("_AS_MESSAGE", "Message");
define("_AS_URL", "URL");
define("_AS_SCOPE_GROUP", "Group");
define("_AS_SCOPE_TOTAL", "Total");
define("_AS_ACTIONS", "Actions");
define("_AS_EDIT", "Edit");
define("_AS_DELETE", "Delete");
define("_AS_ADD", "Add");
define("_AS_UPDATE", "Update");

//Question Number regeneration
define("_RE_REGENNUMBER", "Regenerate Question Numbers:"); //NEW for release 0.99dev2
define("_RE_STRAIGHT", "Straight"); //NEW for release 0.99dev2
define("_RE_BYGROUP", "By Group"); //NEW for release 0.99dev2
?>