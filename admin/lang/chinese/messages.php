<?php


//BUTTON BAR TITLES
define("_ADMINISTRATION", "管理");
define("_SURVEY", "問卷");
define("_GROUP", "題目組別");
define("_QUESTION", "問題");
define("_ANSWERS", "答案");
define("_CONDITIONS", "條件句式");
define("_HELP", "求助說明");
define("_USERCONTROL", "管制用戶");
define("_ACTIVATE", "啟用問卷");
define("_DEACTIVATE", "停用問卷");
define("_CHECKFIELDS", "檢查資料庫欄位");
define("_CREATEDB", "建立資料庫");
define("_CREATESURVEY", "建立問卷"); //New for 0.98rc4
define("_SETUP", "PHPSurveyor 組態");
define("_DELETESURVEY", "刪除問卷");
define("_EXPORTQUESTION", "輸出題目");
define("_EXPORTSURVEY", "輸出問卷");
define("_EXPORTLABEL", "輸出標籤集");
define("_IMPORTQUESTION", "輸入題目");
define("_IMPORTGROUP", "輸入題目組別"); //New for 0.98rc5
define("_IMPORTSURVEY", "輸入問卷");
define("_IMPORTLABEL", "輸入標籤集");
define("_EXPORTRESULTS", "輸出回應結果");
define("_BROWSERESPONSES", "瀏覽回應結果");
define("_STATISTICS", "快速統計");
define("_VIEWRESPONSE", "檢視回應結果");
define("_VIEWCONTROL", "資料的檢視控制");
define("_DATAENTRY", "資料輸入");
define("_TOKENCONTROL", "操作代碼控制");
define("_TOKENDBADMIN", "操作代碼的管理台");
define("_DROPTOKENS", "刪除操作代碼表");
define("_EMAILINVITE", "電郵邀請");
define("_EMAILREMIND", "電郵提示單");
define("_TOKENIFY", "建立操作代碼");
define("_UPLOADCSV", "上傳CSV 檔案");
define("_LABELCONTROL", "標籤集管理台"); //NEW with 0.98rc3
define("_LABELSET", "標籤集"); //NEW with 0.98rc3
define("_LABELANS", "標籤"); //NEW with 0.98rc3

//DROPDOWN HEADINGS
define("_SURVEYS", "問卷");
define("_GROUPS", "題目組別");
define("_QUESTIONS", "問題");
define("_QBYQ", "逐題回應問卷");
define("_GBYG", "逐個組別回應問卷");
define("_SBYS", "一次過回應問卷");
define("_LABELSETS", "標籤集"); //New with 0.98rc3

//BUTTON MOUSEOVERS
//administration bar
define("_A_HOME_BT", "預設管理頁");
define("_A_SECURITY_BT", "修改安全設定");
define("_A_BADSECURITY_BT", "啟動安全設定");
define("_A_CHECKDB_BT", "檢查資料庫");
define("_A_DELETE_BT", "刪除整個問卷");
define("_A_ADDSURVEY_BT", "建立或輸入新問卷");
define("_A_HELP_BT", "顯示求助說明");
define("_A_CHECKSETTINGS", "檢查設定");
//Survey bar
define("_S_ACTIVE_BT", "本問卷現已啟用");
define("_S_INACTIVE_BT", "本問卷尚未啟用");
define("_S_ACTIVATE_BT", "啟用本問卷");
define("_S_DEACTIVATE_BT", "停用本問卷");
define("_S_CANNOTACTIVATE_BT", "不能啟用本問卷");
define("_S_DOSURVEY_BT", "填寫問卷");
define("_S_DATAENTRY_BT", "問卷的資料輸入畫面");
define("_S_PRINTABLE_BT", "問卷的可打印顯示模式");
define("_S_EDIT_BT", "修改目前的問卷");
define("_S_DELETE_BT", "刪除目前的問卷");
define("_S_EXPORT_BT", "輸除本問卷");
define("_S_BROWSE_BT", "瀏覽本問卷的回應結果");
define("_S_TOKENS_BT", "啟用/修改本問卷的操作代碼");
define("_S_ADDGROUP_BT", "把新增的題目組別加入問卷內");
define("_S_MINIMISE_BT", "隱藏本問卷的詳細資料");
define("_S_MAXIMISE_BT", "顯示本問卷的詳細資料");
define("_S_CLOSE_BT", "關閉本問卷");
//Group bar
define("_G_EDIT_BT", "修改目前的題目組別");
define("_G_EXPORT_BT", "輸出目前的題目組別"); //New in 0.98rc5
define("_G_DELETE_BT", "刪除目前的題目組別");
define("_G_ADDQUESTION_BT", "新增題目到題目組別");
define("_G_MINIMISE_BT", "隱藏本題目組別的詳細資料");
define("_G_MAXIMISE_BT", "顯示本題目組別的詳細資料");
define("_G_CLOSE_BT", "關閉本題目組別");
//Question bar
define("_Q_EDIT_BT", "修改目前的題目");
define("_Q_COPY_BT", "複製目前的題目"); //New in 0.98rc4
define("_Q_DELETE_BT", "刪除目前的題目");
define("_Q_EXPORT_BT", "輸出本題目");
define("_Q_CONDITIONS_BT", "為本題目設定條件句式");
define("_Q_ANSWERS_BT", "修改/新增題目的答案");
define("_Q_LABELS_BT", "修改/新增標籤集");
define("_Q_MINIMISE_BT", "隱藏本題目的詳細資料");
define("_Q_MAXIMISE_BT", "顯示本題目的詳細資料");
define("_Q_CLOSE_BT", "關閉本題目");
//Browse Button Bar
define("_B_ADMIN_BT", "返回問卷管理");
define("_B_SUMMARY_BT", "顯示簡報資料");
define("_B_ALL_BT", "顯示回應結果");
define("_B_LAST_BT", "顯示最近 50 個回應結果");
define("_B_STATISTICS_BT", "根據這些回應取得統計資料");
define("_B_EXPORT_BT", "輸出回應結果到應用程式");
define("_B_BACKUP_BT", "把回應結果的表格備份成 SQL 的檔案格式");
//Tokens Button Bar
define("_T_ALL_BT", "顯示操作代碼");
define("_T_ADD_BT", "新增操作代碼");
define("_T_IMPORT_BT", "輸入CSV檔的操作代碼");
define("_T_INVITE_BT", "送出電郵邀請");
define("_T_REMIND_BT", "送出電郵提示");
define("_T_TOKENIFY_BT", "產生操作代碼");
define("_T_KILL_BT", "移除操作代碼表");

//Labels Button Bar
define("_L_ADDSET_BT", "新增標籤集");
define("_L_EDIT_BT", "修改標籤集");
define("_L_DEL_BT", "刪除標籤集");
//Datacontrols
define("_D_BEGIN", "顯示最前..");
define("_D_BACK", "顯示前一個..");
define("_D_FORWARD", "顯示下一個..");
define("_D_END", "顯示最後..");

//DATA LABELS
//surveys
define("_SL_TITLE", "標題:");
define("_SL_SURVEYURL", "問卷連結 URL:"); //new in 0.98rc5
define("_SL_DESCRIPTION", "說明:");
define("_SL_WELCOME", "歡迎:");
define("_SL_ADMIN", "管理員:");
define("_SL_EMAIL", "管理員電郵地址:");
define("_SL_FAXTO", "傳真到學校傳真機:");
define("_SL_ANONYMOUS", "要保持匿名方式回應嗎?");
define("_SL_EXPIRES", "有效期:");
define("_SL_FORMAT", "格式:");
define("_SL_DATESTAMP", "要建立日期印章嗎?");
define("_SL_TEMPLATE", "風格模組:");
define("_SL_LANGUAGE", "語言:");
define("_SL_LINK", "連結:");
define("_SL_URL", "結束問卷後的自動連結 URL:");
define("_SL_URLDESCRIP", "URL 連結說明:");
define("_SL_STATUS", "狀態:");
define("_SL_SELSQL", "選擇 SQL 檔案:");
define("_SL_USECOOKIES", "使用 Cookies 嗎?"); //NEW with 098rc3
define("_SL_NOTIFICATION", "通知:"); //New with 098rc5
define("_SL_ALLOWREGISTER", "Allow public registration?"); //New with 0.98rc9
define("_SL_ATTRIBUTENAMES", "Token Attribute Names:"); //New with 0.98rc9
define("_SL_EMAILINVITE", "Invitation Email:"); //New with 0.98rc9
define("_SL_EMAILREMIND", "Email Reminder:"); //New with 0.98rc9
define("_SL_EMAILREGISTER", "Public registration Email:"); //New with 0.98rc9
define("_SL_EMAILCONFIRM", "Confirmation Email"); //New with 0.98rc9

//groups
define("_GL_TITLE", "標題:");
define("_GL_DESCRIPTION", "說明:");
//questions
define("_QL_CODE", "編號:");
define("_QL_QUESTION", "題目:");
define("_QL_HELP", "求助說明:");
define("_QL_TYPE", "類型:");
define("_QL_GROUP", "題目組別:");
define("_QL_MANDATORY", "必須作答:");
define("_QL_OTHER", "其他:");
define("_QL_LABELSET", "標籤集:");
define("_QL_COPYANS", "要複製答案嗎?"); //New in 0.98rc3
//answers
define("_AL_CODE", "編號");
define("_AL_ANSWER", "答案");
define("_AL_DEFAULT", "預設");
define("_AL_MOVE", "移動");
define("_AL_ACTION", "管理動作");
define("_AL_UP", "移上");
define("_AL_DN", "移下");
define("_AL_SAVE", "儲存");
define("_AL_DEL", "刪除");
define("_AL_ADD", "新增");
define("_AL_FIXSORT", "修正排序");
define("_AL_SORTALPHA", "Sort Alpha"); //New in 0.98rc8 - Sort Answers Alphabetically
//users
define("_UL_USER", "用戶");
define("_UL_PASSWORD", "密碼");
define("_UL_SECURITY", "安全設定");
define("_UL_ACTION", "管理動作");
define("_UL_EDIT", "修改");
define("_UL_DEL", "刪除");
define("_UL_ADD", "新增");
define("_UL_TURNOFF", "關閉安全設定");
//tokens
define("_TL_FIRST", "名字");
define("_TL_LAST", "姓氏");
define("_TL_EMAIL", "電郵");
define("_TL_TOKEN", "操作代碼");
define("_TL_INVITE", "已送出邀請嗎?");
define("_TL_DONE", "已完成問卷嗎?");
define("_TL_ACTION", "管理動作");
define("_TL_ATTR1", "Att_1"); //New for 0.98rc7 (Attribute 1)
define("_TL_ATTR2", "Att_2"); //New for 0.98rc7 (Attribute 2)
define("_TL_MPID", "MPID"); //New for 0.98rc7   (MPID - short for "Master Preferences ID")
//labels
define("_LL_NAME", "設定名字"); //NEW with 098rc3
define("_LL_CODE", "編號"); //NEW with 098rc3
define("_LL_ANSWER", "標題"); //NEW with 098rc3
define("_LL_SORTORDER", "排序"); //NEW with 098rc3
define("_LL_ACTION", "管理動作"); //New with 098rc3

//QUESTION TYPES
define("_5PT", "5 項式選擇");
define("_DATE", "日期");
define("_GENDER", "性別");
define("_LIST", "列表");
define("_LISTWC", "列表附上評語功能");
define("_MULTO", "多項選擇");
define("_MULTOC", "多項選擇附上評語功能");
define("_MULTITEXT", "多項式短答");
define("_NUMERICAL", "數值欄位");
define("_RANK", "排列級別");
define("_STEXT", "自由短答");
define("_LTEXT", "自由長答");
define("_YESNO", "是/否");
define("_ARR5", "Array (5項式選擇)");
define("_ARR10", "Array (10項式選擇)");
define("_ARRYN", "Array (是/否/不肯定)");
define("_ARRMV", "Array (增加, 不變, 減少)");
define("_ARRFL", "Array (彈性標籤)"); //Release 0.98rc3
define("_ARRFLC", "Array (Flexible Labels) by Column"); //Release 0.98rc8
define("_SINFL", "Single (彈性標籤)"); //(FOR LATER RELEASE)
define("_EMAIL", "電郵地址"); //FOR LATER RELEASE
define("_BOILERPLATE", "樣版題目"); //New in 0.98rc6

//GENERAL WORDS AND PHRASES
define("_AD_YES", "是");
define("_AD_NO", "否");
define("_AD_CANCEL", "取消");
define("_AD_CHOOSE", "請選擇..");
define("_AD_OR", "或"); //New in 0.98rc4
define("_ERROR", "出錯");
define("_SUCCESS", "成功/");
define("_REQ", "*必須欄位");
define("_ADDS", "新增問卷");
define("_ADDG", "新增題目組別");
define("_ADDQ", "新增題目");
define("_ADDA", "新增答案"); //New in 0.98rc4
define("_COPYQ", "複製題目"); //New in 0.98rc4
define("_ADDU", "新增用戶");
define("_SEARCH", "搜尋"); //New in 0.98rc4
define("_SAVE", "儲存改變");
define("_NONE", "沒有"); //as in "Do not display anything", "or none chosen";
define("_GO_ADMIN", "主管理畫面"); //text to display to return/display main administration screen
define("_CONTINUE", "繼續");
define("_WARNING", "警告");
define("_USERNAME", "用戶名稱");
define("_PASSWORD", "密碼");
define("_DELETE", "刪除");
define("_CLOSEWIN", "關閉視窗");
define("_TOKEN", "操作代碼");
define("_DATESTAMP", "日期印章"); //Referring to the datestamp or time response submitted
define("_COMMENT", "評語");
define("_FROM", "由"); //For emails
define("_SUBJECT", "標題"); //For emails
define("_MESSAGE", "內文"); //For emails
define("_RELOADING", "更新畫面，請稍候.");
define("_ADD", "新增");
define("_UPDATE", "更新");
define("_BROWSE", "瀏覽"); //New in 098rc5
define("_AND", "and"); //New with 0.98rc8
define("_SQL", "SQL"); //New with 0.98rc8
define("_PERCENTAGE", "Percentage"); //New with 0.98rc8
define("_COUNT", "Count"); //New with 0.98rc8

//SURVEY STATUS MESSAGES (new in 0.98rc3)
define("_SS_NOGROUPS", "問卷的題目組別數目"); //NEW for release 0.98rc3
define("_SS_NOQUESTS", "問卷的題目數目:"); //NEW for release 0.98rc3
define("_SS_ANONYMOUS", "本問卷是採用匿名方式."); //NEW for release 0.98rc3
define("_SS_TRACKED", "本問卷並非採用匿名方式."); //NEW for release 0.98rc3
define("_SS_DATESTAMPED", "回應的問卷會被加上日期的印章"); //NEW for release 0.98rc3
define("_SS_COOKIES", "它採用 cookies 控制問卷的使用權限."); //NEW for release 0.98rc3
define("_SS_QBYQ", "它是採用逐題回應的方式."); //NEW for release 0.98rc3
define("_SS_GBYG", "它是採用逐個題目組別的回應方式."); //NEW for release 0.98rc3
define("_SS_SBYS", "它是採用單一頁面一次過回應."); //NEW for release 0.98rc3
define("_SS_ACTIVE", "問卷現已啟用."); //NEW for release 0.98rc3
define("_SS_NOTACTIVE", "問卷並未啟用."); //NEW for release 0.98rc3
define("_SS_SURVEYTABLE", "問卷表格名稱是:"); //NEW for release 0.98rc3
define("_SS_CANNOTACTIVATE", "問卷無法啟用."); //NEW for release 0.98rc3
define("_SS_ADDGROUPS", "您需要新增題目組別"); //NEW for release 0.98rc3
define("_SS_ADDQUESTS", "您需要新增題目"); //NEW for release 0.98rc3
define("_SS_ALLOWREGISTER", "If tokens are used, the public may register for this survey"); //NEW for release 0.98rc9

//QUESTION STATUS MESSAGES (new in 0.98rc4)
define("_QS_MANDATORY", "必須回應的問題"); //New for release 0.98rc4
define("_QS_OPTIONAL", "選擇性回應的問題"); //New for release 0.98rc4
define("_QS_NOANSWERS", "您需要把答案新增到本題目"); //New for release 0.98rc4
define("_QS_NOLID", "您需要選擇本題目的標籤集"); //New for release 0.98rc4
define("_QS_COPYINFO", "注意︰ 您必須輸入題目的編號"); //New for release 0.98rc4

//General Setup Messages
define("_ST_NODB1", "定義的問卷數據庫並不存在");
define("_ST_NODB2", "原因有二︰1.您選擇的數據庫仍未建立. 2. 存取資料發生故障.");
define("_ST_NODB3", "PHPSurveyor 試圖為您建立數據庫.");
define("_ST_NODB4", "您選擇的數據庫名稱是:");
define("_ST_CREATEDB", "建立數據庫");

//USER CONTROL MESSAGES
define("_UC_CREATE", "建立預設的 htaccess 檔");
define("_UC_NOCREATE", "不能建立 htaccess 檔，請檢查您在\$homedir 的config.php 檔的設定，請設定該目錄有寫入資料的使用權限.");
define("_UC_SEC_DONE", "現已建立安全設定!");
define("_UC_CREATE_DEFAULT", "建立預設的用戶");
define("_UC_UPDATE_TABLE", "更新用戶資料表");
define("_UC_HTPASSWD_ERROR", "無法產生 htpasswd 檔");
define("_UC_HTPASSWD_EXPLAIN", "如果您正在使 windows 系統的伺服器，建議您複製 apache htpasswd.exe 檔案到您的admin 資料夾，才能正常運行本功能，您可以在/apache group/apache/bin/ 找到這個檔案.");
define("_UC_SEC_REMOVE", "移除安全設定");
define("_UC_ALL_REMOVED", "存取檔、密碼檔及用戶數據庫均已被刪除");
define("_UC_ADD_USER", "新增用戶");
define("_UC_ADD_MISSING", "不能新增用戶，因為您未有提供會員名稱及/或密碼.");
define("_UC_DEL_USER", "正在刪除用戶");
define("_UC_DEL_MISSING", "不能刪除用戶，因為您未有提供用戶名稱.");
define("_UC_MOD_USER", "正在修改用戶");
define("_UC_MOD_MISSING", "不能修改用戶資料，因為您未有提供用戶名稱及/或密碼");
define("_UC_TURNON_MESSAGE1", "您並未為問卷系統啟動安全設定，這表示問卷的使用權限不受安全保障.</p>\n如果您 click 下列 '啟動安全設定' 按鈕, 標準的 APACHE 安全設定會加進本程式的 admin 目錄內。然後您需要使用預設的用戶名稱及密碼，才可以使本程式的管理選單及輸入架設問卷的資料.");
define("_UC_TURNON_MESSAGE2", "建議您啟動安全系統後，馬上更改預設的密碼.");
define("_UC_INITIALISE", "啟動安全設定");
define("_UC_NOUSERS", "您的資料表內未有用戶資料，建議您先 '關閉' 安全設定後，稍後再重新 '開啟安全設定' .");
define("_UC_TURNOFF", "關閉安全設定");

//Activate and deactivate messages
define("_AC_MULTI_NOANSWER", "本題目是多項選擇的類型，但未有答案.");
define("_AC_NOTYPE", "本題目並未設定題目 '類型' .");
define("_AC_NOLID", "This question requires a Labelset, but none is set."); //New for 0.98rc8
define("_AC_CON_OUTOFORDER", "本題目有條件句式的設定，但條件句式是基於題目出現的正確次序才能生效.");
define("_AC_FAIL", "問卷不能通過系統的檢查");
define("_AC_PROBS", "已發現以下的問題:");
define("_AC_CANNOTACTIVATE", "除非問題獲得解決，否則問卷不能被啟用");
define("_AC_READCAREFULLY", "請仔細閱讀本文，才繼續執行本功能.");
define("_AC_ACTIVATE_MESSAGE1", "當您確定問卷已成功架設，又不再需要修改的情況下，您才可以啟用問卷.");
define("_AC_ACTIVATE_MESSAGE2", "問卷一經啟用，您就不能再:<ul><li>新增或刪除題目組別</li><li>新增或移除多項式選擇題的答案</li><li>新增或刪除題目</li></ul>");
define("_AC_ACTIVATE_MESSAGE3", "但您仍可以︰<ul><li>修改 (更改) 題目的編號文字或類型</li><li>修改 (更改) 您的題目組別</li><li>新增、移除或修改預製的題目答案 (多項式選擇的答案除外)</li><li>更改問卷的名稱或補充說明</li></ul>");
define("_AC_ACTIVATE_MESSAGE4", "問卷的數據一經輸入之後，如果您要新增或移除題目組別或題目，您需要停用問卷，問卷數據會自動搬移到專門存放舊檔的資料表內.");
define("_AC_ACTIVATE", "啟用");
define("_AC_ACTIVATED", "已啟用問卷，回應的問卷資料表已成功建立.");
define("_AC_NOTACTIVATED", "不能啟用問卷.");
define("_AC_NOTPRIVATE", "這並非匿名方式的問卷，因此您必須建立操作代碼表.");
define("_AC_CREATETOKENS", "啟動操作代碼");
define("_AC_SURVEYACTIVE", "本問卷現已啟用，回應的問卷會馬上記錄下來.");
define("_AC_DEACTIVATE_MESSAGE1", "在已啟用的問卷, 系統會建立資料表來存放全部輸入的資料記錄.");
define("_AC_DEACTIVATE_MESSAGE2", "當您關閉問卷之後，在原有資料表上的數據資料均會被移除；當您重新啟用問卷, 資料表就會空空如也。這表示您不可以再透過 PHPSurveyor 存取這些舊有數據了.");
define("_AC_DEACTIVATE_MESSAGE3", "停用問卷的數據只能經由系統管理員透過MySQL 的數據存取工具，例如是phpmyadmin 進行存取。如果您的問卷使用操作代碼, 本資料表亦會改名，而且只有系統管理員才可存取相關的資料.");
define("_AC_DEACTIVATE_MESSAGE4", "回應問卷的資料表會被改名為:");
define("_AC_DEACTIVATE_MESSAGE5", "您應該把回應的問卷輸出成文字檔備份，才停用問卷. Click \"取消\" 可放棄停用問卷，而返回主管理畫面.");
define("_AC_DEACTIVATE", "停用");
define("_AC_DEACTIVATED_MESSAGE1", "回應問卷的資料表已改名為: ");
define("_AC_DEACTIVATED_MESSAGE2", "回應的問卷不再能透過PHPSurveyor使用.");
define("_AC_DEACTIVATED_MESSAGE3", "請您記下這資料表的名稱，以便日後有需要時，您可以存取這些資料.");
define("_AC_DEACTIVATED_MESSAGE4", "連結本問卷的操作代碼表已經改名為: ");

//CHECKFIELDS
define("_CF_CHECKTABLES", "正在檢查表格是否齊備");
define("_CF_CHECKFIELDS", "正在檢查所有欄位是否齊備");
define("_CF_CHECKING", "正在檢查中");
define("_CF_TABLECREATED", "已建立表格");
define("_CF_FIELDCREATED", "已建立欄位");
define("_CF_OK", "OK");
define("_CFT_PROBLEM", "數據庫內部份欄位或資料表似乎不存在.");

//CREATE DATABASE (createdb.php)
define("_CD_DBCREATED", "已建立資料庫.");
define("_CD_POPULATE_MESSAGE", "請 click 下方以組建資料庫");
define("_CD_POPULATE", "組建資料庫");
define("_CD_NOCREATE", "不能建立資料庫");
define("_CD_NODBNAME", "未提供資料庫的資料，本程式必須透過admin.php 執行.");

//DATABASE MODIFICATION MESSAGES
define("_DB_FAIL_GROUPNAME", "它缺少了必須的組別名稱，因此不能新增題目組別");
define("_DB_FAIL_GROUPUPDATE", "不能更新題目組別");
define("_DB_FAIL_GROUPDELETE", "不能刪除題目組別");
define("_DB_FAIL_NEWQUESTION", "不能建立題目");
define("_DB_FAIL_QUESTIONTYPECONDITIONS", "不能更新題目，因為它指定的答案正被其他題目以條件句式的設定交叉使用，如更改類型會造成系統出錯。您必須先刪除有關的條件句式，才可以更改本題目的類型.");
define("_DB_FAIL_QUESTIONUPDATE", "不能更新題目");
define("_DB_FAIL_QUESTIONDELCONDITIONS", "不能刪除題目，因為它正被其他題目以條件句式的設定交叉使用。除非有關的條件句式被移除，否則您無法刪除本題目.");
define("_DB_FAIL_QUESTIONDELETE", "不能刪除題目");
define("_DB_FAIL_NEWANSWERMISSING", "不能新增答案，因為您必須提供編號及答案.");
define("_DB_FAIL_NEWANSWERDUPLICATE", "不能新增答案，因為這編號已有答案使用.");
define("_DB_FAIL_ANSWERUPDATEMISSING", "不能更新答案，您必須一併提供編號及答案.");
define("_DB_FAIL_ANSWERUPDATEDUPLICATE", "不能更新答案, 因為這編號已有答案使用.");
define("_DB_FAIL_ANSWERUPDATECONDITIONS", "不能更新答案，因為您已更改答案的編號，但其他題目的條件句式仍使用本題目的舊答案對應的舊編號。您必須先刪除這些條件句式，才可以更改答案的舊編號.");
define("_DB_FAIL_ANSWERDELCONDITIONS", "不能刪除答案，因為其他題目的條件句式仍使用本答案。除非您先刪除有關的條件句式，否則不能刪除這答案.");
define("_DB_FAIL_NEWSURVEY_TITLE", "不能建立問卷，因為它沒有短標題.");
define("_DB_FAIL_NEWSURVEY", "不能建立問卷");
define("_DB_FAIL_SURVEYUPDATE", "不能更新問卷");
define("_DB_FAIL_SURVEYDELETE", "不能刪除問卷");

//DELETE SURVEY MESSAGES
define("_DS_NOSID", "您未選擇問卷，所以未能刪除問卷.");
define("_DS_DELMESSAGE1", "您將要刪除本問卷");
define("_DS_DELMESSAGE2", "本程序會刪除本問卷及其相關的全部題目組別、題目答案及相應的條件句式.");
define("_DS_DELMESSAGE3", "建議您先在主管理畫面輸出問卷，才刪除本問卷.");
define("_DS_SURVEYACTIVE", "本問卷已啟用，並有回應結果的表格。如果您現在刪除問卷，亦會把回應結果的表格一併刪除。建議您把回應的結果輸出成文字檔，才刪除本問卷.");
define("_DS_SURVEYTOKENS", "本問卷擁有相關的操作代碼表. 如果您要刪除本問卷，請先刪除本操作代碼表。建議您先對這些操作代碼表進行備份或輸出成文字檔，才刪除問卷.");
define("_DS_DELETED", "本問卷已被刪除.");

//DELETE QUESTION AND GROUP MESSAGES
define("_DG_RUSURE", "刪除本題目組別亦會一併刪除組別連結的題目和答案。您肯定要刪除嗎?"); //New for 098rc5
define("_DQ_RUSURE", "刪除本題目亦會一併刪除答案。您肯定要刪除嗎?"); //New for 098rc5

//EXPORT MESSAGES
define("_EQ_NOQID", "未提供 QID ，不能移除題目.");
define("_ES_NOSID", "未提供 SID ，不能移除問卷");

//EXPORT RESULTS
define("_EX_FROMSTATS", "依照統計程式篩選顯示的條件");
define("_EX_HEADINGS", "題目");
define("_EX_ANSWERS", "答案");
define("_EX_FORMAT", "格式");
define("_EX_HEAD_ABBREV", "縮寫的表頭標題");
define("_EX_HEAD_FULL", "完整的表頭標題");
define("_EX_ANS_ABBREV", "答案編號");
define("_EX_ANS_FULL", "完整答案");
define("_EX_FORM_WORD", "Microsoft Word");
define("_EX_FORM_EXCEL", "Microsoft Excel");
define("_EX_FORM_CSV", "CSV 逗號分隔");
define("_EX_EXPORTDATA", "輸出數據");
define("_EX_COLCONTROLS", "Column Control"); //New for 0.98rc7
define("_EX_TOKENCONTROLS", "Token Control"); //New for 0.98rc7
define("_EX_COLSELECT", "Choose columns"); //New for 0.98rc7
define("_EX_COLOK", "Choose the columns you wish to export. Leave all unselected to export all columns."); //New for 0.98rc7
define("_EX_COLNOTOK", "Your survey contains more than 255 columns of responses. Spreadsheet applications such as Excel are limited to loading no more than 255. Select the columns you wish to export in the list below."); //New for 0.98rc7
define("_EX_TOKENMESSAGE", "Your survey can export associated token data with each response. Select any additional fields you would like to export."); //New for 0.98rc7
define("_EX_TOKSELECT", "Choose Token Fields"); //New for 0.98rc7

//IMPORT SURVEY MESSAGES
define("_IS_FAILUPLOAD", "上傳檔案時出錯，可能是您對 admin 資料夾的使用權限設定出錯.");
define("_IS_OKUPLOAD", "檔案成功上傳.");
define("_IS_READFILE", "檔案讀取中.");
define("_IS_WRONGFILE", "本檔不是 PHPSurveyor 的問卷檔，因此無法輸入.");
define("_IS_IMPORTSUMMARY", "輸入問卷的簡報");
define("_IS_SUCCESS", "問卷輸入工作已完成.");
define("_IS_IMPFAILED", "無法輸入本問卷");
define("_IS_FILEFAILS", "檔案內的 PHPSurveyor 資料不符合正確的格式.");

//IMPORT GROUP MESSAGES
define("_IG_IMPORTSUMMARY", "輸入題目組別的簡報");
define("_IG_SUCCESS", "輸入題目組別的工作已完成.");
define("_IG_IMPFAILED", "無法輸入本題目組別");
define("_IG_WRONGFILE", "本檔案並非 PHPSurveyor 的題目組別檔，因此無法輸入.");

//IMPORT QUESTION MESSAGES
define("_IQ_NOSID", "未有提供 SID (問卷) ，不能輸入題目.");
define("_IQ_NOGID", "未提供 GID (題目組別) ，因此無法輸入題目.");
define("_IQ_WRONGFILE", "本檔案並非 PHPSurveyor 的題目檔，因此無法輸入.");
define("_IQ_IMPORTSUMMARY", "輸入題目的簡報");
define("_IQ_SUCCESS", "題目的輸入已完成");

//BROWSE RESPONSES MESSAGES
define("_BR_NOSID", "您尚未選擇問卷，以致無法瀏覽問卷內容.");
define("_BR_NOTACTIVATED", "本問卷尚未被啟用, 因此無法瀏覽回應結果.");
define("_BR_NOSURVEY", "找不到吻合的問卷.");
define("_BR_EDITRESPONSE", "修改本項目");
define("_BR_DELRESPONSE", "刪除本項目");
define("_BR_DISPLAYING", "顯示記錄:");
define("_BR_STARTING", "啟始於:");
define("_BR_SHOW", "顯示");
define("_DR_RUSURE", "您肯定要刪除本項目嗎?"); //New for 0.98rc6

//STATISTICS MESSAGES
define("_ST_FILTERSETTINGS", "篩選條件的設定");
define("_ST_VIEWALL", "View summary of all available fields"); //New with 0.98rc8
define("_ST_SHOWRESULTS", "View Stats"); //New with 0.98rc8
define("_ST_CLEAR", "Clear"); //New with 0.98rc8
define("_ST_RESPONECONT", "Responses Containing"); //New with 0.98rc8
define("_ST_NOGREATERTHAN", "Number greater than"); //New with 0.98rc8
define("_ST_NOLESSTHAN", "Number Less Than"); //New with 0.98rc8
define("_ST_DATEEQUALS", "Date (YYYY-MM-DD) equals"); //New with 0.98rc8
define("_ST_ORBETWEEN", "OR between"); //New with 0.98rc8
define("_ST_RESULTS", "Results"); //New with 0.98rc8 (Plural)
define("_ST_RESULT", "Result"); //New with 0.98rc8 (Singular)
define("_ST_RECORDSRETURNED", "No of records in this query"); //New with 0.98rc8
define("_ST_TOTALRECORDS", "Total records in survey"); //New with 0.98rc8
define("_ST_PERCENTAGE", "Percentage of total"); //New with 0.98rc8
define("_ST_FIELDSUMMARY", "Field Summary for"); //New with 0.98rc8
define("_ST_CALCULATION", "Calculation"); //New with 0.98rc8
define("_ST_SUM", "Sum"); //New with 0.98rc8 - Mathematical
define("_ST_STDEV", "Standard Deviation"); //New with 0.98rc8 - Mathematical
define("_ST_AVERAGE", "Average"); //New with 0.98rc8 - Mathematical
define("_ST_MIN", "Minimum"); //New with 0.98rc8 - Mathematical
define("_ST_MAX", "Maximum"); //New with 0.98rc8 - Mathematical
define("_ST_Q1", "1st Quartile (Q1)"); //New with 0.98rc8 - Mathematical
define("_ST_Q2", "2nd Quartile (Median)"); //New with 0.98rc8 - Mathematical
define("_ST_Q3", "3rd Quartile (Q3)"); //New with 0.98rc8 - Mathematical
define("_ST_NULLIGNORED", "*Null values are ignored in calculations"); //New with 0.98rc8
define("_ST_QUARTMETHOD", "*Q1 and Q3 calculated using <a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>minitab method</a>"); //New with 0.98rc8

//DATA ENTRY MESSAGES
define("_DE_NOMODIFY", "不能被修改");
define("_DE_UPDATE", "更新項目");
define("_DE_NOSID", "您必須先選擇問卷，才可以輸入資料.");
define("_DE_NOEXIST", "您選擇的問卷並不存在");
define("_DE_NOTACTIVE", "本問卷尚未啟用，因此您的回應不能儲存到問卷去。");
define("_DE_INSERT", "插入資料");
define("_DE_RECORD", "項目分派到以下的記錄 id: ");
define("_DE_ADDANOTHER", "新增另一筆記錄");
define("_DE_VIEWTHISONE", "檢視這筆記錄");
define("_DE_BROWSE", "瀏覽回應結果");
define("_DE_DELRECORD", "記錄已被刪除");
define("_DE_UPDATED", "記錄已被更新.");
define("_DE_EDITING", "修改回應結果");
define("_DE_QUESTIONHELP", "有關本問題的求助說明");
define("_DE_CONDITIONHELP1", "符合條件才可回答本題目:"); 
define("_DE_CONDITIONHELP2", "問題: {QUESTION}, 您的答案: {ANSWER}"); //This will be a tricky one depending on your languages syntax. {ANSWER} is replaced with ALL ANSWERS, seperated by _DE_OR (OR).
define("_DE_AND", "及");
define("_DE_OR", "或");

//TOKEN CONTROL MESSAGES
define("_TC_TOTALCOUNT", "操作代碼表的紀錄總數:"); //New in 0.98rc4
define("_TC_NOTOKENCOUNT", "未有操作代碼的總數:"); //New in 0.98rc4
define("_TC_INVITECOUNT", "已發出邀請的總數:"); //New in 0.98rc4
define("_TC_COMPLETEDCOUNT", "完成問卷的總數:"); //New in 0.98rc4
define("_TC_NOSID", "您仍未選擇問卷");
define("_TC_DELTOKENS", "正要刪除本問卷的操作代碼表.");
define("_TC_DELTOKENSINFO", "如果您刪除本表內的操作代碼，問卷就會自動開放給公眾使用；您亦可以把本表先行備份，讓系統管理員代為存取備份表格的資料。");
define("_TC_DELETETOKENS", "刪除操作代碼");
define("_TC_TOKENSGONE", "操作代碼項目表已被移除，本問卷不再需要操作代碼，就可以使用。如把本表備份，系統管理員可以存取表上的資料。.");
define("_TC_NOTINITIALISED", "本問卷的操作代碼已啟用.");
define("_TC_INITINFO", "如果您啟用本問卷的操作代碼, 只有獲發相關操作代碼的用戶才可以使用本問卷.");
define("_TC_INITQ", "您要為本問卷建立操作代碼表嗎?");
define("_TC_INITTOKENS", "啟重操作代碼");
define("_TC_CREATED", "本問卷的操作代碼表已經建立.");
define("_TC_DELETEALL", "刪除全部操作代碼項目");
define("_TC_DELETEALL_RUSURE", "您肯定要刪除全部操作代碼項目嗎?");
define("_TC_ALLDELETED", "全部操作代碼項目已被刪除");
define("_TC_CLEARINVITES", "把全部項目設定成 '否' 的發出邀請的狀態");
define("_TC_CLEARINV_RUSURE", "您肯定要把全部邀請紀錄重設成 '否'的狀態嗎?");
define("_TC_CLEARTOKENS", "刪除全部操作代碼");
define("_TC_CLEARTOKENS_RUSURE", "您肯定要刪除全部操作代碼嗎? ");
define("_TC_TOKENSCLEARED", "全部操作代碼項目已成功移除");
define("_TC_INVITESCLEARED", "全部邀請項已設定成否的狀態");
define("_TC_EDIT", "修改操作代碼項目");
define("_TC_DEL", "刪除操作代碼項目");
define("_TC_DO", "填寫問卷");
define("_TC_VIEW", "查看回應結果");
define("_TC_INVITET", "送出本項目的邀請電郵");
define("_TC_REMINDT", "送出本項目的提示電郵");
define("_TC_INVITESUBJECT", "參與 {SURVEYNAME}的邀請單"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSUBJECT", "參與{SURVEYNAME}的提示單"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSTARTAT", "TID 的啟始編號:");
define("_TC_REMINDTID", "送到 TID 的編號:");
define("_TC_CREATETOKENSINFO", "Click  /'是/' 會為操作代碼表上未有操作代碼的用戶自動產生操作代碼。您肯定要這樣做嗎?");
define("_TC_TOKENSCREATED", "已建立操作代碼 {TOKENCOUNT} "); //Leave {TOKENCOUNT} for replacement in script with the number of tokens created
define("_TC_TOKENDELETED", "已刪除操作代碼.");
define("_TC_SORTBY", "排序法︰");
define("_TC_ADDEDIT", "新增或修改操作代碼");
define("_TC_TOKENCREATEINFO", "您可以留空本欄位, 使用 '建立操作代碼' 來自動建立操作代碼 ");
define("_TC_TOKENADDED", "新增操作代碼");
define("_TC_TOKENUPDATED", "更新操作代碼");
define("_TC_UPLOADINFO", "採用標準的CSV 檔案格式 (逗號分隔欄位資料) ，毋須使用引號。首行包括 '名字, 姓氏, 電郵地址, [操作代碼]'.");
define("_TC_UPLOADFAIL", "找不到上傳檔案，請檢查上傳目錄的使用權限及路徑是否正確?"); //New for 0.98rc5
define("_TC_IMPORT", "輸入CSV 檔案");
define("_TC_CREATE", "建立操作代碼的項目");
define("_TC_TOKENS_CREATED", "建立了{TOKENCOUNT} 筆紀錄");
define("_TC_NONETOSEND", "未有合資格的電郵地址可以發出，原因可能是未符合電郵格式的要求 - 之前未曾對用戶發出邀請，又或者是因為用戶已完成問卷並擁有操作代碼。");
define("_TC_NOREMINDERSTOSEND", "未有合資格的電郵地址可以發出。這是因為未符合以下條件的要求 - 擁有電郵地址、事前已發出邀請，但對方尚未完成問卷.");
define("_TC_NOEMAILTEMPLATE", "找不到邀請信的風格模組，本檔案必須存在於預設的風格模組資料夾內.");
define("_TC_NOREMINDTEMPLATE", "找不到提示單的風格模組，本檔案必須存在於預設的風格模組資料夾內.");
define("_TC_SENDEMAIL", "發出邀請");
define("_TC_SENDINGEMAILS", "發出邀請");
define("_TC_SENDINGREMINDERS", "送出提示單");
define("_TC_EMAILSTOGO", "有一批電郵尚未寄出，請 click 以下按鈕寄出電郵。");
define("_TC_EMAILSREMAINING", "還有 {EMAILCOUNT} 封電郵有待寄出。"); //Leave {EMAILCOUNT} for replacement in script by number of emails remaining
define("_TC_SENDREMIND", "送出提示單");
define("_TC_INVITESENTTO", "邀請寄給:"); //is followed by token name
define("_TC_REMINDSENTTO", "提示單寄給:"); //is followed by token name
define("_TC_UPDATEDB", "Update tokens table with new fields"); //New for 0.98rc7
define("_TC_EMAILINVITE", "Dear {FIRSTNAME},\n\nYou have been invited to participate in a survey.\n\n"
						 ."The survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."To participate, please click on the link below.\n\nSincerely,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Click here to do the survey:\n"
						 ."{SURVEYURL}"); //New for 0.98rc9 - default Email Invitation
define("_TC_EMAILREMIND", "Dear {FIRSTNAME},\n\nRecently we invited you to participate in a survey.\n\n"
						 ."We note that you have not yet completed the survey, and wish to remind you that the survey is still available should you wish to take part.\n\n"
						 ."The survey is titled:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."To participate, please click on the link below.\n\nSincerely,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Click here to do the survey:\n"
						 ."{SURVEYURL}"); //New for 0.98rc9 - default Email Reminder
define("_TC_EMAILREGISTER", "Dear {FIRSTNAME},\n\n"
						  ."You, or someone using your email address, have registered to "
						  ."participate in an online survey titled {SURVEYNAME}.\n\n"
						  ."To complete this survey, click on the following URL:\n\n"
						  ."{SURVEYURL}\n\n"
						  ."If you have any questions about this survey, or if you "
						  ."did not register to participate and believe this email "
						  ."is in error, please contact {ADMINNAME} at {ADMINEMAIL}.");//NEW for 0.98rc9
define("_TC_EMAILCONFIRM", "Dear {FIRSTNAME},\n\nThis email is to confirm that you have completed the survey titled {SURVEYNAME} "
						  ."and your response has been saved. Thank you for participating.\n\n"
						  ."If you have any further questions about this email, please contact {ADMINNAME} on {ADMINEMAIL}.\n\n"
						  ."Sincerely,\n\n"
						  ."{ADMINNAME}"); //New for 0.98rc9 - Confirmation Email

//labels.php
define("_LB_NEWSET", "新增標籤集");
define("_LB_EDITSET", "修改標籤集");
define("_LB_FAIL_UPDATESET", "無法更新標籤集");
define("_LB_FAIL_INSERTSET", "無法插入新的標籤集");
define("_LB_FAIL_DELSET", "不能刪除標籤集 - 因為仍有其他題目正在使用它，您必須先刪除這些題目，才可刪除標籤集。");
define("_LB_ACTIVEUSE", "您不能更改編號、新增或刪除標籤集的項目，因為有其也問卷正在使用此標籤集。");
define("_LB_TOTALUSE", "部份問卷仍在使用這個標籤集，對此標籤集作出任何修改、新增或刪除，均會對有關問卷造成不良的後果。");
//Export Labels
define("_EL_NOLID", "未有提供 LID，無法清除標籤集.");
//Import Labels
define("_IL_GOLABELADMIN", "返回標籤管理台");

//PHPSurveyor System Summary
define("_PS_TITLE", "PHPSurveyor 系統簡報");
define("_PS_DBNAME", "資料庫名稱");
define("_PS_DEFLANG", "預設的語言");
define("_PS_CURLANG", "現時使用的語言");
define("_PS_USERS", "用戶");
define("_PS_ACTIVESURVEYS", "啟用問卷");
define("_PS_DEACTSURVEYS", "停用問卷");
define("_PS_ACTIVETOKENS", "啟用操作代碼表");
define("_PS_DEACTTOKENS", "停用操作代碼表");
define("_PS_CHECKDBINTEGRITY", "Check PHPSurveyor Data Integrity"); //New with 0.98rc8

//Notification Levels
define("_NT_NONE", "未有電郵通知"); //New with 098rc5
define("_NT_SINGLE", "基本電郵通知"); //New with 098rc5
define("_NT_RESULTS", "送出電郵通知/(附上回應結果)/"); //New with 098rc5

//CONDITIONS TRANSLATIONS
define("_CD_CONDITIONDESIGNER", "Condition Designer"); //New with 098rc9
define("_CD_ONLYSHOW", "Only show question {QID} IF"); //New with 098rc9 - {QID} is repleaced leave there
define("_CD_AND", "AND"); //New with 098rc9
define("_CD_COPYCONDITIONS", "Copy Conditions"); //New with 098rc9
define("_CD_CONDITION", "Condition"); //New with 098rc9
define("_CD_ADDCONDITION", "Add Condition"); //New with 098rc9
define("_CD_EQUALS", "Equals"); //New with 098rc9
define("_CD_COPYRUSURE", "Are you sure you want to copy these condition(s) to the questions you have selected?"); //New with 098rc9
define("_CD_NODIRECT", "You cannot run this script directly."); //New with 098rc9
define("_CD_NOSID", "You have not selected a Survey."); //New with 098rc9
define("_CD_NOQID", "You have not selected a Question."); //New with 098rc9
define("_CD_DIDNOTCOPYQ", "Did not copy questions"); //New with 098rc9
define("_CD_NOCONDITIONTOCOPY", "No condition selected to copy from"); //New with 098rc9
define("_CD_NOQUESTIONTOCOPYTO", "No question selected to copy condition to"); //New with 098rc9

?>
