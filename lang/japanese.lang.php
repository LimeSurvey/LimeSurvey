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
	# Japanese translation by Masaru Ryumae, mryumae@gmail.com ##
	# 05/2006
	#############################################################
*/
//SINGLE WORDS
define("_YES", "はい");
define("_NO", "いいえ");
define("_UNCERTAIN", "わからない");
define("_ADMIN", "管理");
define("_TOKENS", "トークン");
define("_FEMALE", "女性");
define("_MALE", "男性");
define("_NOANSWER", "分からない");
define("_NOTAPPLICABLE", "該当なし"); //New for 0.98rc5
define("_OTHER", "その他");
define("_PLEASECHOOSE", "選んでください。");
define("_ERROR_PS", "エラー");
define("_COMPLETE", "完了");
define("_INCREASE", "増加"); //NEW WITH 0.98
define("_SAME", "同じ"); //NEW WITH 0.98
define("_DECREASE", "減少"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01

//from questions.php
define("_CONFIRMATION", "確認");
define("_TOKEN_PS", "トークン");
define("_CONTINUE_PS", "続ける");

//BUTTONS
define("_ACCEPT", "承諾する");
define("_PREV", "前へ");
define("_NEXT", "次へ");
define("_LAST", "最後へ");
define("_SUBMIT", "提出する");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Please choose one of the following");
define("_ENTERCOMMENT", "Please enter your comment here");
define("_NUMERICAL_PS", "Only numbers may be entered in this field");
define("_CLEARALL", "アンケートを削除する。");
define("_MANDATORY", "次の質問へ行くにはこの質問に答えてください。");
define("_MANDATORY_PARTS", "すべての質問に答えてください。");
define("_MANDATORY_CHECK", "Please check at least one item");
define("_MANDATORY_RANK", "Please rank all items");
define("_MANDATORY_POPUP", "質問に答えてください。 答えるまで先に進めません。"); //NEW in 0.98rc4
define("_VALIDATION", "This question must be answered correctly"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "One or more questions have not been answered in a valid manner. You cannot proceed until these answers are valid"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Format: YYYY-MM-DD");
define("_DATEFORMATEG", "(eg: 2003-12-25 for Christmas day)");
define("_REMOVEITEM", "Remove this item");
define("_RANK_1", "Click on an item in the list on the left, starting with your");
define("_RANK_2", "highest ranking item, moving through to your lowest ranking item.");
define("_YOURCHOICES", "Your Choices");
define("_YOURRANKING", "Your Ranking");
define("_RANK_3", "Click on the scissors next to each item on the right");
define("_RANK_4", "to remove the last entry in your ranked list");
//From INDEX.PHP
define("_NOSID", "You have not provided a survey identification number");
define("_CONTACT1", "Please contact");
define("_CONTACT2", "for further assistance");
define("_ANSCLEAR", "返答を削除しました。");
define("_RESTART", "もう一度このアンケートを始める。");
define("_CLOSEWIN_PS", "このウィンドウを閉じる。");
define("_CONFIRMCLEAR", "すべての返答を削除しますか？");
define("_CONFIRMSAVE", "Are you sure you want to save your responses?");
define("_EXITCLEAR", "アンケートを削除して、終える。");
//From QUESTION.PHP
define("_BADSUBMIT1", "Cannot submit results - none to submit.");
define("_BADSUBMIT2", "This error can occur if you have already submitted your responses and pressed 'refresh' on your browser. In this case, your responses have already been saved.<br /><br />If you receive this message in the middle of completing a survey, you should choose '<- BACK' on your browser and then refresh/reload the previous page. While you will lose answers from the last page all your others will still exist. This problem can occur if the webserver is suffering from overload or excessive use. We apologise for this problem.");
define("_NOTACTIVE1", "このアンケートはまだベータ版なので、返答内容を記録できません。");
define("_CLEARRESP", "返答を削除する。");
define("_THANKS", "ありがとうございます。");
define("_SURVEYREC", "アンケートの内容が記録されました。");
define("_SURVEYCPL", "アンケートが完成しました。");
define("_DIDNOTSAVE", "アンケートの結果内容を記録しませんでした。");
define("_DIDNOTSAVE2", "An unexpected error has occurred and your responses cannot be saved.");
define("_DIDNOTSAVE3", "Your responses have not been lost and have been emailed to the survey administrator and will be entered into our database at a later point.");
define("_DNSAVEEMAIL1", "An error occurred saving a response to survey id");
define("_DNSAVEEMAIL2", "DATA TO BE ENTERED");
define("_DNSAVEEMAIL3", "SQL CODE THAT FAILED");
define("_DNSAVEEMAIL4", "ERROR MESSAGE");
define("_DNSAVEEMAIL5", "ERROR SAVING");
define("_SUBMITAGAIN", "Try to submit again");
define("_SURVEYNOEXIST", "Sorry. There is no matching survey.");
define("_NOTOKEN1", "残念ですが、このアンケートはコントロールされており、参加するには有効なトークンが必要です。");
define("_NOTOKEN2", "トークンを発行されているなら、以下に入れて継続のクリックをしてください。");
define("_NOTOKEN3", "あなたのトークンは有効でないか、もうすでに使われています。");
define("_NOQUESTIONS", "このアンケートはまだ準備中なので、テストや試すことはできません。");
define("_FURTHERINFO", "質問があれば管理者に連絡を取ってください。 ===> ");
define("_NOTACTIVE", "このアンケートは現在有効でありません。そのため、アンケートの返答を記録することはできません。");
define("_SURVEYEXPIRED", "This survey is no longer available.");

define("_SURVEYCOMPLETE", "You have already completed this survey."); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "以下から一つだけ選んでください。"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "すべて適当なものをチェックしてください。"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "アンケートを提出しました。"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "A new response was entered for your survey"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Click the following link to see the individual response:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "View statistics by clicking here:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Click the following link to edit the individual response:"); //NEW for 0.99stable

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
						  
define("_THEREAREXQUESTIONS", "このアンケートには {NUMBEROFQUESTIONS}問　質問があります。"); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
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

define("_SM_COMPLETED", "ありがとうございました！<br　/><br />"
					   ."アンケートのすべての質問が終わりました。.<br /><br />"
					   ."Click on ["._SUBMIT."] now to complete the process and save your answers.");
define("_SM_REVIEW", "If you want to check any of the answers you have made, and/or change them, "
					."you can do that now by clicking on the [<< "._PREV."] button and browsing "
					."through your responses.");

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

//Save Messages
define("_SAVE_AND_RETURN", "今までの返答を記録する。");
define("_SAVEHEADING", "終わらなかったアンケートの記録");
define("_RETURNTOSURVEY", "アンケートに戻る。");
define("_SAVENAME", "Name");
define("_SAVEPASSWORD", "Password");
define("_SAVEPASSWORDRPT", "Repeat Password");
define("_SAVE_EMAIL", "Your Email");
define("_SAVEEXPLANATION", "このアンケート用に名前とパスワードを入れて(英数字だけ、日本語文字不可）、下の記録するをクリックしてください。<br />\n"
				  ."このアンケートはその名前とパスワードを使って記録され、"
				  ."後で同じ名前とパスワードを使って完成することができます。<br /><br />\n"
				  ."また、電子メールアドレスを入れた場合は詳細のメールが届きます。"
				  ."");
define("_SAVESUBMIT", "記録する");
define("_SAVENONAME", "この記録されたセッション用に名前を入れてください。");
define("_SAVENOPASS", "この記録されたセッションようにパスワードを入れてください。");
define("_SAVENOPASS2", "この記録されたセッションようにもう一度パスワードを入れてください。");
define("_SAVENOMATCH", "パスワードが合いません。");
define("_SAVEDUPLICATE", "この名前はこのアンケートにすでに使われています。違う名前を使って記録してください。");
define("_SAVETRYAGAIN", "もう一回試してください。");
define("_SAVE_EMAILSUBJECT", "Saved Survey Details");
define("_SAVE_EMAILTEXT", "You, or someone using your email address, have saved "
						 ."a survey in progress. The following details can be used "
						 ."to return to this survey and continue where you left"
						 ."off.");
define("_SAVE_EMAILURL", "Reload your survey by clicking on the following URL:");
define("_SAVE_SUCCEEDED", "アンケートの返答がすべて記録されました！");
define("_SAVE_FAILED", "残念ですが、エラーが起こり、アンケートの返答内容が記録できませんでした。");
define("_SAVE_EMAILSENT", "この記録されたアンケートの詳細がメールで送られました。");

//Load Messages
define("_LOAD_SAVED", "前回終わらなかったアンケートのデータを読み込んで続ける。");
define("_LOADHEADING", "前回記録されたアンケートのデータ読み込み");
define("_LOADEXPLANATION", "この画面から前回記録されたアンケートのデータを読み込むことができます。<br />\n"
			  ."記録したときに使った名前とパスワードを入れてください。<br /><br />\n");
define("_LOADNAME", "記録された名前");
define("_LOADPASSWORD", "パスワード");
define("_LOADSUBMIT", "データの読み込み");
define("_LOADNONAME", "名前を入れてください。");
define("_LOADNOPASS", "パスワードを入れてください。");
define("_LOADNOMATCH", "残念ですが、該当したアンケートは見つかりません。");

define("_ASSESSMENT_HEADING", "Your Assessment");
?>
