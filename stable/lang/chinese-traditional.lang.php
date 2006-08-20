<?php
/*
    #############################################################
    # >>> PHPSurveyor                                          #
    #############################################################
    # > Author:  Jason Cleeland                                 #
    # > E-mail:  jason@cleeland.org                             #
    # > Mail:    Box 99, Trades Hall, 54 Victoria St,           #
    # > Translated by: Mark Yeung (kaisuny@yahoo.com)           #
    # >                of www.pstudy.net                        #
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
*/
//SINGLE WORDS
define("_YES", "是");
define("_NO", "否");
define("_UNCERTAIN", "無意見");
define("_ADMIN", "管理");
define("_TOKENS", "代碼");
define("_FEMALE", "女");
define("_MALE", "男");
define("_NOANSWER", "未有回答");
define("_NOTAPPLICABLE", "不適用"); //New for 0.98rc5
define("_OTHER", "其他");
define("_PLEASECHOOSE", "請選擇");
define("_ERROR_PS", "出錯");
define("_COMPLETE", "完成");
define("_INCREASE", "增加"); //NEW WITH 0.98
define("_SAME", "相同"); //NEW WITH 0.98
define("_DECREASE", "下降"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "確認");
define("_TOKEN_PS", "操作代碼");
define("_CONTINUE_PS", "繼續");

//BUTTONS
define("_ACCEPT", "接受");
define("_PREV", "上一題");
define("_NEXT", "下一題");
define("_LAST", "完成");
define("_SUBMIT", "送出");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "請選擇");
define("_ENTERCOMMENT", "請輸入評語");
define("_NUMERICAL_PS", "本欄位限輸入數值");
define("_CLEARALL", "清除問卷內容後離開");
define("_MANDATORY", "本題必須作答");
define("_MANDATORY_PARTS", "請回答全部問卷內容");
define("_MANDATORY_CHECK", "請在項目打鉤");
define("_MANDATORY_RANK", "請為所有項目排序");
define("_MANDATORY_POPUP", "尚有部份題目未完成作答，請完成後才繼續填寫問卷餘下的題目"); //NEW in 0.98rc4
define("_VALIDATION", "請正確地回答這條問題"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "一或更多問題未被有被正確地回答。  請先更正然後前進。"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "日期格式: YYYY-MM-DD");
define("_DATEFORMATEG", "(例: 2003-12-25 是聖誕節)");
define("_REMOVEITEM", "移除本項");
define("_RANK_1", "點擊左方列表的一個項目, 開始您的");
define("_RANK_2", "最高排名的項目, 移到排名的最低位置.");
define("_YOURCHOICES", "您的選擇");
define("_YOURRANKING", "您的排名");
define("_RANK_3", "點擊右方項目旁的剪刀");
define("_RANK_4", "移除排名表的最後一個項目");
//From INDEX.PHP
define("_NOSID", "您未提供問卷編號");
define("_CONTACT1", "請聯絡");
define("_CONTACT2", "進一步協助");
define("_ANSCLEAR", "答案被清除");
define("_RESTART", "重新開始問卷");
define("_CLOSEWIN_PS", "關閉本視窗");
define("_CONFIRMCLEAR", "您肯定要清除全部問卷內容嗎?");
define("_CONFIRMSAVE", "您肯定要儲存問卷內容嗎?");
define("_EXITCLEAR", "離開及清除問卷");
//From QUESTION.PHP
define("_BADSUBMIT1", "不能送出問卷結果 - 未有人回答問卷.");
define("_BADSUBMIT2", "照理您已完成及送出問卷<br /><br />如果您是在填寫問卷期間出現本訊息，請按瀏覽器的 '<- 返回' 鍵及按[重新整理]鍵更新上一頁面。這情況可能是因為系統資源不足造成部份答題內容散失，對此我們感到遺憾。");
define("_NOTACTIVE1", "由於本問卷已停用，所以您的問卷未被儲存.");
define("_CLEARRESP", "清除問卷內容");
define("_THANKS", "多謝");
define("_SURVEYREC", "您的問卷內容已被儲存.");
define("_SURVEYCPL", "問卷完成");
define("_DIDNOTSAVE", "未被儲存");
define("_DIDNOTSAVE2", "系統出錯，無法儲存完成的問卷.");
define("_DIDNOTSAVE3", "您完成的問卷已送交網管作進一步處理.");
define("_DNSAVEEMAIL1", "無法儲存到本問卷 id");
define("_DNSAVEEMAIL2", "須輸入數據");
define("_DNSAVEEMAIL3", "SQL CODE 出錯");
define("_DNSAVEEMAIL4", "訊息出錯");
define("_DNSAVEEMAIL5", "儲存出錯");
define("_SUBMITAGAIN", "嘗試再送出問卷");
define("_SURVEYNOEXIST", "對不起！找不到相關的問卷.");
define("_NOTOKEN1", "本問卷限於擁有代幣的人士才可以作答.");
define("_NOTOKEN2", "如果您已擁有代幣，請輸入本方格內，再繼續作答.");
define("_NOTOKEN3", "您提供的代幣無效，或已被使用.");
define("_NOQUESTIONS", "本問卷未有題目，所以您無法完成作答.");
define("_FURTHERINFO", "如有查問，請聯絡");
define("_NOTACTIVE", "本問卷已關閉. 您不能儲存問卷.");
define("_SURVEYEXPIRED", "本問卷已停用.");

define("_SURVEYCOMPLETE", "您已完成本回卷"); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "請選擇一項"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "可選多個答案"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "已送出問卷"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "恭喜您！您收到一份剛完成的問卷"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "點擊此連結查看個別的問卷內容:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "點擊這裡查看統計資料:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Click the following link to edit the individual response:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<b><i>私隱聲明</i></b><br />"
                          ."這是一份不記名的問卷.<br />"
                          ."問卷內容並不包含任何可辨認身份的資料，除非問卷內有此具體問題。 "
                          ."如果您參與了一個以代碼(token)作為通入控制的問卷， "
                          ."請放心，該個代碼(token)並不與您的答案儲存在一起。  "
                          ."系統只會在您更改或完成問卷時才更新該個代碼。  "
                          ."我們並不能將代碼和問卷匹對上."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "此問卷有{NUMBEROFQUESTIONS}條問題."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "此問卷有一條問題."); //New for 0.98rc9 - singular version of above

define ("_RG_REGISTER1", "請先登記然後再填此問卷"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "如果您希望參與這次問卷,請先登記您的細節。  一份內有這次問卷詳情的電子郵件即將奉上。"); //NEW for 0.98rc9
define ("_RG_EMAIL", "電郵"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "名"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "姓"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "您輸入的電郵有誤。  請再輸入。");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "您輸入的電郵已被登記。");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} 確認登記");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "多謝登記參與這次問卷。<br /><br />\n"
                                   ."您將收到一份內有這次問卷網址及通入細節的電子郵件。  請點擊該郵件內的連線繼續進行。<br /><br />\n"
                                   ."問卷主任 {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<b>多謝<br /><br />"
                       ."您已完成這份問卷.</b><br /><br />"
                       ."請按 ["._SUBMIT."] 把答案遞送給我們."); //New for 0.98finalRC1
define("_SM_REVIEW", "如果您想要檢查您之前的答案或予以更改, 請按 [<< "._PREV."]."); //New for 0.98finalRC1

//For the "printable" survey
define("_PS_CHOOSEONE", "請任選其<b>一</b>:"); //New for 0.98finalRC1
define("_PS_WRITE", "請在這兒填上您的答案:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "請選擇<b>所有</b>適用的答案:"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "請選擇所有適用的答案申請及提供意見:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "請為以下各項選擇適當答案:"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "請在這兒填上您的答案:"); //New for 0.98finalRC1
define("_PS_DATE", "請填上日期:"); //New for 0.98finalRC1
define("_PS_COMMENT", "請為您的答案作注解:"); //New for 0.98finalRC1
define("_PS_RANKING", "請順序按偏好編號各箱子從１至"); //New for 0.98finalRC1
define("_PS_SUBMIT", "遞上您的問卷."); //New for 0.98finalRC1
define("_PS_THANKYOU", "多謝您完成這份問卷."); //New for 0.98finalRC1
define("_PS_FAXTO", "請將問卷傳真至:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "回答這條問題"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "如果您有回答"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "和"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "於問題"); //New for 0.98finalRC1
define("_PS_CON_OR", "或"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "儲存您到目前為止的答案");
define("_SAVEHEADING", "儲存末完成的問卷");
define("_RETURNTOSURVEY", "返回問卷");
define("_SAVENAME", "名稱");
define("_SAVEPASSWORD", "密碼");
define("_SAVEPASSWORDRPT", "重複密碼");
define("_SAVE_EMAIL", "電郵");
define("_SAVEEXPLANATION", "為這問卷輸入一個名字和密碼然後點擊「儲存」。<br />\n"
                  ."您的問卷將會以這個名字和密碼儲存在系統內，日後您可以用這個名字和密碼登入繼續完成問卷。<br /><br />\n"
                  ."如果您填上電郵地址，以下細節會以電郵送上。");
define("_SAVESUBMIT", "儲存");
define("_SAVENONAME", "請填上名字。");
define("_SAVENOPASS", "請填上密碼。");
define("_SAVENOMATCH", "密碼不匹對。");
define("_SAVEDUPLICATE", "這個名字已經被使用。  您必須使用一個獨特的名字。");
define("_SAVETRYAGAIN", "請再試。");
define("_SAVE_EMAILSUBJECT", "已儲存的問卷之細節");
define("_SAVE_EMAILTEXT", "您,或一個使用您電郵的人,儲存了一個未完成的問卷。  "
                         ."您可使用以下細節返回並繼續完成該問卷。");
define("_SAVE_EMAILURL", "請點擊以下URL去檢索您的問卷:");
define("_SAVE_SUCCEEDED", "您的問卷經已成功存檔");
define("_SAVE_FAILED", "存檔其間發生了錯誤，您的問卷並未被保存。");
define("_SAVE_EMAILSENT", "您問卷的細節已用電子郵件送上。");

//Load Messages
define("_LOAD_SAVED", "檢索未完成的問卷");
define("_LOADHEADING", "檢索先前儲存好的問卷");
define("_LOADEXPLANATION", "您可在這兒檢索您先前儲存了的問卷。<br />\n"
              ."請輸入該問卷的名字及密碼。<br /><br />\n");
define("_LOADNAME", "名字");
define("_LOADPASSWORD", "密碼");
define("_LOADSUBMIT", "檢索");
define("_LOADNONAME", "您沒有輸入名字");
define("_LOADNOPASS", "您沒有輸入密碼");
define("_LOADNOMATCH", "找不到相對的問卷");

define("_ASSESSMENT_HEADING", "您的評估");
?>
