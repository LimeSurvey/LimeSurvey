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
	# Translation kindly provided by Liang Zhao                 #
	#############################################################
*/
//SINGLE WORDS
define("_YES", "是");
define("_NO", "否");
define("_UNCERTAIN", "不确定");
define("_ADMIN", "管理");
define("_TOKENS", "令牌");
define("_FEMALE", "女");
define("_MALE", "男");
define("_NOANSWER", "不回答");
define("_NOTAPPLICABLE", "不适用"); //New for 0.98rc5
define("_OTHER", "其它");
define("_PLEASECHOOSE", "请选择");
define("_ERROR_PS", "出错");
define("_COMPLETE", "完成");
define("_INCREASE", "增加"); //NEW WITH 0.98
define("_SAME", "相同"); //NEW WITH 0.98
define("_DECREASE", "减少"); //NEW WITH 0.98
//from questions.php
define("_CONFIRMATION", "确认");
define("_TOKEN_PS", "令牌");
define("_CONTINUE_PS", "继续");

//BUTTONS
define("_ACCEPT", "接受");
define("_PREV", "上一题");
define("_NEXT", "下一题");
define("_LAST", "完成");
define("_SUBMIT", "提交");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "请选择一项");
define("_ENTERCOMMENT", "请输入意见");
define("_NUMERICAL_PS", "这一项只能输入数字");
define("_CLEARALL", "清除所有答案");
define("_MANDATORY", "本题必须回答");
define("_MANDATORY_PARTS", "请完成所有部分");
define("_MANDATORY_CHECK", "请至少选择一项");
define("_MANDATORY_RANK", "请评价所有项目");
define("_MANDATORY_POPUP", "部分必答问题尚未回答，完成它们才能继续。"); //NEW in 0.98rc4
define("_DATEFORMAT", "日期格式: YYYY-MM-DD");
define("_DATEFORMATEG", "(例: 2003年圣诞节是2003-12-25)");
define("_REMOVEITEM", "删除这一项");
define("_RANK_1", "点击左边表格中的某一项，从您的");
define("_RANK_2", "打分最高的项目开始，直到您的打分最低的项目。");
define("_YOURCHOICES", "您的选择");
define("_YOURRANKING", "您的打分");
define("_RANK_3", "点击每一项右边的剪刀");
define("_RANK_4", "删除打分选项表的最后一项");
//From INDEX.PHP
define("_NOSID", "您没有提供问卷编号");
define("_CONTACT1", "请联系");
define("_CONTACT2", "以获得进一步帮助");
define("_ANSCLEAR", "答案被清除了");
define("_RESTART", "重新开始问卷");
define("_CLOSEWIN_PS", "关闭窗口");
define("_CONFIRMCLEAR", "您确定要清除所有答案吗？");
define("_EXITCLEAR", "清除所有答案并退出问卷");
//From QUESTION.PHP
define("_BADSUBMIT1", "不能提交结果 - 没有内容可提交。");
define("_BADSUBMIT2", "如果您已经提交了结果又按了浏览器的'刷新'钮，可能导致这个错误。如果是这样的话，您的回答已经被保存了。<br /><br />如果您是在回答问题中间出现本信息，请选择浏览器的'返回'钮，然后刷新前一页。您最后一页的答案将丢失，但其它答案还在。这种情况可能是由于服务器负载过重引起的。我们对此引起的不便道歉。");
define("_NOTACTIVE1", "此问卷尚未启用，您的回答没有被保存。");
define("_CLEARRESP", "清除答案");
define("_THANKS", "谢谢");
define("_SURVEYREC", "您的回答已被保存。");
define("_SURVEYCPL", "问卷完成");
define("_DIDNOTSAVE", "未保存");
define("_DIDNOTSAVE2", "系统出错，无法保存您的回答。");
define("_DIDNOTSAVE3", "您的回答并没有丢失，它们以被e-mail给问卷管理人员并将在晚些时候被录入到数据库。");
define("_DNSAVEEMAIL1", "保存回答出错，id ");
define("_DNSAVEEMAIL2", "须输入数据");
define("_DNSAVEEMAIL3", "失败SQL语句");
define("_DNSAVEEMAIL4", "错误信息");
define("_DNSAVEEMAIL5", "保存出错");
define("_SUBMITAGAIN", "重试提交");
define("_SURVEYNOEXIST", "对不起，找不到相关问卷。");
define("_NOTOKEN1", "只有有令牌的人才能参加此问卷调查。");
define("_NOTOKEN2", "如果您有令牌，请将它输入到下面的格子内，再继续。");
define("_NOTOKEN3", "您提供的令牌无效，或已被使用过了。");
define("_NOQUESTIONS", "此问卷还没有任何问题，不能测试或使用。");
define("_FURTHERINFO", "有关详情，请联系");
define("_NOTACTIVE", "此问卷尚未启用，您不能保存回答。");
define("_SURVEYEXPIRED", "此问卷已不再进行。");

define("_SURVEYCOMPLETE", "您已经回答过了此问卷。"); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "请选择下列一项"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "选择所有合适的选项"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "问卷已提交"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "一份新的回答被录入到您的问卷"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "点击下面的链接查看单个回答："); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "点击这里查看统计结果："); //NEW for 098rc5

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
?>
