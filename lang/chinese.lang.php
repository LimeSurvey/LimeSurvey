<?php
/*
	#############################################################
	# >>> PHP Surveyor  										#
	#############################################################
	# > Author:  Jason Cleeland								
	# > E-mail:  jason@cleeland.org							
	# > Mail:    Box 99, Trades Hall, 54 Victoria St,		
	# > Translated by: Mark Yeung (kaisuny@yahoo.com) of    www.pstudy.net	
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
define("_YES", "是");
define("_NO", "否");
define("_UNCERTAIN", "無意見");
define("_ADMIN", "管理");
define("_TOKENS", "代幣");
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
define("_MANDATORY", "本題必須回答");
define("_MANDATORY_PARTS", "請回答全部問卷內容");
define("_MANDATORY_CHECK", "請在項目打鉤");
define("_MANDATORY_RANK", "請為所有項目排序");
define("_MANDATORY_POPUP", "尚有部份題目未完成作答，請完成後才繼續填寫問卷靘下的題目"); //NEW in 0.98rc4
define("_VALIDATION", "This question must be answered correctly"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "日期格式: YYYY-MM-DD");
define("_DATEFORMATEG", "(例: 2003-12-25 是聖誕節)");
define("_REMOVEITEM", "移除本項");
define("_RANK_1", "Click 左方列表的一個項目, 開始您的");
define("_RANK_2", "最高排名的項目, 移到排名的最低位置.");
define("_YOURCHOICES", "您的選擇");
define("_YOURRANKING", "您的排名");
define("_RANK_3", "Click 右方項目旁的剪刀");
define("_RANK_4", "移除排名表的最後一個項目");
//From INDEX.PHP
define("_NOSID", "您未提供問卷編號");
define("_CONTACT1", "請聯絡");
define("_CONTACT2", "進一步協助");
define("_ANSCLEAR", "答案被清除");
define("_RESTART", "重新開始問卷");
define("_CLOSEWIN_PS", "關閉本視窗");
define("_CONFIRMCLEAR", "您肯定要清除全部問卷內容嗎?");
define("_EXITCLEAR", "離開及清除問卷");
//From QUESTION.PHP
define("_BADSUBMIT1", "不能送出問卷結果 - 未有人回答問卷.");
define("_BADSUBMIT2", "照理您已完成及送出問卷<br /><br />如果您是在填寫問卷期間出現本訊息，請按瀏覽器的 '<- 返回' 鍵及按[重新整理]鍵更新上一頁面。這情況可能是因為系統資源不足造成部份答題內容散失，對此我們感到遺憾。");
//define("_BADSUBMIT2", "您已完成及送出問卷.");
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
define("_CONFIRMATION_MESSAGE3", "Click 這連結查看個別的問卷內容:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Click 這裡查看統計資料:"); //NEW for 098rc5

define("_PRIVACY_MESSAGE", "<b><i>A Note On Privacy</i></b><br />"
						  ."This survey is anonymous.<br />"
						  ."The record kept of your survey responses does not contain any "
						  ."identifying information about you unless a specific question "
						  ."in the survey has asked for this. If you have responded to a "
						  ."survey that used an identifying token to allow you to access "
						  ."the survey, you can rest assured that the identifying token "
						  ."is not kept with your responses. It is managed in a seperate "
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

define("_SM_COMPLETED", "<b>Thank You<br /><br />"
					   ."You have completed answering the questions in this survey.</b><br /><br />"
					   ."Click on ["._SUBMIT."] now to complete the process and save your answers."); //New for 0.98finalRC1
define("_SM_REVIEW", "If you want to check any of the answers you have made, and/or change them, "
					."you can do that now by clicking on the [<< "._PREV."] button and browsing "
					."through your responses."); //New for 0.98finalRC1

//For the "printable" survey
define("_PS_CHOOSEONE", "Please choose <b>only one</b> of the following"); //New for 0.98finalRC1
define("_PS_WRITE", "Please write your answer here"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Please choose <b>all</b> that apply"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Please choose all that apply and provide a comment"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Please choose the appropriate response for each item"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Please write your answer(s) here"); //New for 0.98finalRC1
define("_PS_DATE", "Please enter a date"); //New for 0.98finalRC1
define("_PS_COMMENT", "Make a comment on your choice here"); //New for 0.98finalRC1
define("_PS_RANKING", "Please number each box in order of preference from 1 to"); //New for 0.98finalRC1
define("_PS_SUBMIT", "Submit Your Survey"); //New for 0.98finalRC1
define("_PS_THANKYOU", "Thank you for completing this survey."); //New for 0.98finalRC1
define("_PS_FAXTO", "Please fax your completed survey to:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Only answer this question"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "if you answered"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "and"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "to question"); //New for 0.98finalRC1
define("_PS_CON_OR", "or"); //New for 0.98finalRC2
?>
