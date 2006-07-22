<?php
define("_YES", "Có");
define("_NO", "Không");
define("_UNCERTAIN", "Không chắc");
define("_ADMIN", "Quản trị");
define("_TOKENS", "Các thẻ bài");
define("_FEMALE", "Nữ");
define("_MALE", "Nam");
define("_NOANSWER", "Không có câu trả lời");
define("_NOTAPPLICABLE", "Không thích hợp"); //New for 0.98rc5
define("_OTHER", "Khác");
define("_PLEASECHOOSE", "Xin chọn");
define("_ERROR_PS", "Lỗi");
define("_COMPLETE", "Hoàn tất");
define("_INCREASE", "Tăng"); //NEW WITH 0.98
define("_SAME", "Không đổi"); //NEW WITH 0.98
define("_DECREASE", "Giảm"); //NEW WITH 0.98
define("_REQUIRED", "<font color='red'>*</font>"); //NEW WITH 0.99dev01
//from questions.php
define("_CONFIRMATION", "Xác nhận");
define("_TOKEN_PS", "Thẻ bài");
define("_CONTINUE_PS", "Tiếp tục");

//BUTTONS
define("_ACCEPT", "Đồng ý");
define("_PREV", "trước");
define("_NEXT", "tiếp");
define("_LAST", "cuối");
define("_SUBMIT", "gửi");


//MESSAGES
//From QANDA.PHP
define("_CHOOSEONE", "Xin chojn một trong các lựa chọn sau");
define("_ENTERCOMMENT", "Xin điền lời bình ở đây");
define("_NUMERICAL_PS", "Chỉ có thể điền số ở đây");
define("_CLEARALL", "Thoát và xóa khảo sát đã làm");
define("_MANDATORY", "Câu hỏi này là bắt buộc");
define("_MANDATORY_PARTS", "Xin hoàn tất tất cả các phần");
define("_MANDATORY_CHECK", "Xin chọn ít nhật một lựa chọn");
define("_MANDATORY_RANK", "Xin sắp hạn tất cả các mục");
define("_MANDATORY_POPUP", "Một hoặc nhiều câu hỏi bắt buộc chưa được trả lời.Bạn Không thể tiếp tục cho đến khi những câu hỏi này được hoàn tất"); //NEW in 0.98rc4
define("_VALIDATION", "Câu hỏi này phải được trả lời chính xác"); //NEW in VALIDATION VERSION
define("_VALIDATION_POPUP", "MỘt hoặc nhiều câu hỏi chưa được trả lời đúng nghĩa.Bạn không thể tiếp tục cho đến khi những câu trả lời này có nghĩa"); //NEW in VALIDATION VERSION
define("_DATEFORMAT", "Định dạng: YYYY-MM-DD");
define("_DATEFORMATEG", "(Ví dụ: 2003-12-25 cho ngày Giáng Sinh)");
define("_REMOVEITEM", "Bỏ mục này");
define("_RANK_1", "Nhấn vàn 1 mục trong danh sách bên trái, bắt đầu với");
define("_RANK_2", "Mục xếp hạng cao nhất,cho đến mục xếp hạng thấp nhất.");
define("_YOURCHOICES", "Những lựa chọn của bạn");
define("_YOURRANKING", "Bảng sắp hạng :");
define("_RANK_3", "Nhấn vào biểu tượng cây kéo kế bên mỗi mục bên tay phải");
define("_RANK_4", "để lấy entry cuối cùng trong danh sách xếp hạng của bạn :");
//From INDEX.PHP
define("_NOSID", "Bạn chưa cung cấp một số xác minh cho khảo sát");
define("_CONTACT1", "Xin liên lạc");
define("_CONTACT2", "để có nhiều thông tin hướng dẫn");
define("_ANSCLEAR", "Các câu trả lời đã được xóa");
define("_RESTART", "Khởi động lại khảo sát");
define("_CLOSEWIN_PS", "Tắt cửa sổ này");
define("_CONFIRMCLEAR", "Bạn có chắc xóa hết tất cả các phản hồi của bạn?");
define("_CONFIRMSAVE", "Bạn có chắc là muốn lưu tất cả các phản hồi?");
define("_EXITCLEAR", "Thoát và xóa khảo sát");
//From QUESTION.PHP
define("_BADSUBMIT1", "Không thể gửi kết quả - không có gì để gửi cả.");
define("_BADSUBMIT2", "Lỗi này có thể xảy ra nếu bạn đã gửi phản hồi của bạn và nhấn nút  'refresh' của trình duyệt web. Trong trường hợp này,các phản hồi của bạn đã được lưu.<br /><br />Nếu bạn nhận thông điệp này đang lúc dở dang của khảo sát,bạn nên chọn nút '<- BACK' trên trình duyệt web của bạn và sau đó làm tươi/load lại trang trước đó.Trong khi bạn co thẻ mất hết tất cả câu trả lời của trang cuối tất cả những phần khác vẫn sẽ tồn tại.Vấn đề này có thể xảy ra nếu webserver chịu quá tải hoặc vượt quá mức sử dụng.Chúng tôi thành thật xin lỗi vì vấn đề này.");
define("_NOTACTIVE1", "Các phản hồi trong khảo sát của nạn chưa được lưu. Khảo sát này chưa trong tình trạng hoạt động.");
define("_CLEARRESP", "Xóa các phản hồi");
define("_THANKS", "Chân thành cảm ơn");
define("_SURVEYREC", "Các phản hồi của khảo sát đã được lưu.");
define("_SURVEYCPL", "Khảo sát hoàn tất");
define("_DIDNOTSAVE", "Chưa được lưu");
define("_DIDNOTSAVE2", "Lỗi không mong muốn xả ra đã các phản hồi của bạn không thể đươc lưu lại.");
define("_DIDNOTSAVE3", "Các phản hồi của bạn chưa bị mất và đã ddyiwhc mail đên ban quản trị khảo sát và sẽ được đưa vào cơ sở dữ liệu của chúng tôi vào thời điểm sau này.");
define("_DNSAVEEMAIL1", "Lỗi xả ra trong việc lưu lại một phản hồi tới id khảo sát ");
define("_DNSAVEEMAIL2", "DỮ LIỆU ĐƯỢC ĐƯA VÀO");
define("_DNSAVEEMAIL3", "MÃ SQL BỊ LỖI");
define("_DNSAVEEMAIL4", "THÔNG ĐIỆP LỖI");
define("_DNSAVEEMAIL5", "Lỗi trong việc lưu kết quả khảo sát vào cơ sở dữ liệu");
define("_SUBMITAGAIN", "Cố gắng gửi thêm lần nữa");
define("_SURVEYNOEXIST", "Xin lỗi.Không có khảo sát nào trùng khớp.");
define("_NOTOKEN1", "Đây là một khảo sát co sự giám sát.Bạn cần thẻ bài hợp lệ để tham gia.");
define("_NOTOKEN2", "Nếu bạn đã có một thẻ bài,xin điền nó vào ô bên dưới và nhấn tiếp tục.");
define("_NOTOKEN3", "Thẻ bài bạn cung cấp không hợp lệ hoặc đã được sủ dụng.");
define("_NOQUESTIONS", "Khảo sát chưa có câu hỏi nào bạn không thể kiểm tra hoặc hoàn tất khảo sát này.");
define("_FURTHERINFO", "Để biết thêm thông tin xin liên hệ");
define("_NOTACTIVE", "Khảo sát này chưa được kích hoạt.Bạn không thẻ lưu lại các phản hồi.");
define("_SURVEYEXPIRED", "Khảo sát này không còn có giá trị.");

define("_SURVEYCOMPLETE", "Bạn đã hoàn thành khảo sát này."); //NEW FOR 0.98rc6

define("_INSTRUCTION_LIST", "Chỉ chọn một trong số sau"); //NEW for 098rc3
define("_INSTRUCTION_MULTI", "Kiểm tra bất cứ những gì đã thiết lập"); //NEW for 098rc3

define("_CONFIRMATION_MESSAGE1", "Khảo sát đã được gửi đi"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE2", "Một phản hồi mới đã được đua vào khảo sát của bạn"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE3", "Nhấn vào liên kết sau để xem các phản hồi riêng lẻ:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE4", "Trình bày các thống kê bằng cách nhấn vào đây:"); //NEW for 098rc5
define("_CONFIRMATION_MESSAGE5", "Nhấn vào liên kết sau đê hiệu chỉnh từng phản hồi riêng lẻ:"); //NEW for 0.99stable

define("_PRIVACY_MESSAGE", "<strong><i>A Note On Privacy</i></strong><br />"
						  ."Khảo sát này là nặc danh.<br />"
						  ."Mẫu tin được giữ trong các phản hồi khảo sát không chứa bất kỳ "
						  ."thông tin xác minh về bạn trừ khi một câu hỏi cụ thể "
						  ."trong khảo sát đã hỏi .Nếu bạn đã phản hồi khảo sát "
						  ."mà có sử dụng thẻ bài xác địh đẻ cho phép bạn tham gia "
						  ."khảo sát,bạn có thể biết chắc và an tâm rằng thẻ bài xác minh "
						  ."không được giữ kèm với các phản hồi của bạn.Nó được quản lý bởi "
						  ."cơ sở dữ liệu khác tách rời,và chỉ cập nhất đê chỉ ra rằng bạn đã "
						  ."(hoặc chưa) hoàn tất khảo sát này.Không có cách thức trong việc đối sánh "
						  ."thẻ bài xác minh với các phản hồi của bạn trong khảo sát này."); //New for 0.98rc9

define("_THEREAREXQUESTIONS", "Có {NUMBEROFQUESTIONS} câu hỏi trong khảo sát này."); //New for 0.98rc9 Must contain {NUMBEROFQUESTIONS} which gets replaced with a question count.
define("_THEREAREXQUESTIONS_SINGLE", "Cps 1 câu hỏi trong khảo sát này."); //New for 0.98rc9 - singular version of above
						  
define ("_RG_REGISTER1", "Bạn phải đăng ký để tham gia khảo sát này"); //NEW for 0.98rc9
define ("_RG_REGISTER2", "Bạn có thể đăng ký khảo sát  nếu bạn muốn tham gia.<br />\n"
						."Hãy điền thoogn tin vào bên dưới,và một email chứa liên kết để"
						."tham gia khảo sát sẽ được gửi đến bạn ngay lập tức."); //NEW for 0.98rc9
define ("_RG_EMAIL", "Địa chỉ mail"); //NEW for 0.98rc9
define ("_RG_FIRSTNAME", "Tên"); //NEW for 0.98rc9
define ("_RG_LASTNAME", "Họ"); //NEW for 0.98rc9
define ("_RG_INVALIDEMAIL", "Địa chỉ mail bạn sử dụng không hợp lệ.Hãy thủ lại lần nữa.");//NEW for 0.98rc9
define ("_RG_USEDEMAIL", "Địa chỉ mail của bạn đã được đăng ký.");//NEW for 0.98rc9
define ("_RG_EMAILSUBJECT", "{SURVEYNAME} xác nhận đăng ký");//NEW for 0.98rc9
define ("_RG_REGISTRATIONCOMPLETE", "Cám ơn bạn đã đăng ký tham gia khảo xác của chúng tôi.<br /><br />\n"
								   ."Một bức mail đã được gửi đến địa chỉ bạn đã cung cấp cho chúng tôi với các thông tin cho việc "
								   ."tham gia khảo sát.Xin vui lòng mở liên kết trong bức mail đó để tiếp tục.<br /><br />\n"
								   ."Quản trị khảo sát {ADMINNAME} ({ADMINEMAIL})");//NEW for 0.98rc9

define("_SM_COMPLETED", "<strong>Chân thành cám ơn<br /><br />"
					   ."Bạn đã hoàn tất việc trả lời các cau hỏi cho khảo sát này.</strong><br /><br />"
					   ."Nhấn vào nút ["._SUBMIT."] bây giờ để hoàn tất tiến trình và lưu các câu trả lời.");
define("_SM_REVIEW", "Nếu bạn muốn kiểm tra bất kỳ câu trả lời bạn đã làm, và/hoặc thay đổi chúng, "
					."bạn co thể làm điều đó bây giờ bằng cách nhấn vào nút [<< "._PREV."] và duyệt  "
					."đên các phản hồi của bạn.");

//For the "printable" survey
define("_PS_CHOOSEONE", "Vui lòng chỉ chọn  <strong>một</strong> trong số sau:"); //New for 0.98finalRC1
define("_PS_WRITE", "Vui lòng điền câu trả lời ở đây:"); //New for 0.98finalRC1
define("_PS_CHOOSEANY", "Vui lòng chọn <strong>tất cả </strong> :"); //New for 0.98finalRC1
define("_PS_CHOOSEANYCOMMENT", "Vui lòng chọn tất cả và cung cấp lời phê bình/ghi chú:"); //New for 0.98finalRC1
define("_PS_EACHITEM", "Vui lòng chọn phản hồi thích hợp cho mỗi mục:"); //New for 0.98finalRC1
define("_PS_WRITEMULTI", "Vui lòng điền câu trả lời của bạn ở đây:"); //New for 0.98finalRC1
define("_PS_DATE", "Vui lòng điền vào ngày tháng:"); //New for 0.98finalRC1
define("_PS_COMMENT", "Điền vào chú thích ứng với lựa chọn của bạn ở đây:"); //New for 0.98finalRC1
define("_PS_RANKING", "Vui lòng điền vào mỗi ô theo thứ tự từ 1 đến "); //New for 0.98finalRC1
define("_PS_SUBMIT", "Gửi khảo sát của bạn."); //New for 0.98finalRC1
define("_PS_THANKYOU", "Cám ơn bạn đã hoàn thành khảo sát."); //New for 0.98finalRC1
define("_PS_FAXTO", "Xin gửi fax bảng hoàn tất khảo sát của bạn đến:"); //New for 0.98finaclRC1

define("_PS_CON_ONLYANSWER", "Chỉ trả lời câu hỏi này"); //New for 0.98finalRC1
define("_PS_CON_IFYOU", "nếu bạn trả lời"); //New for 0.98finalRC1
define("_PS_CON_JOINER", "và"); //New for 0.98finalRC1
define("_PS_CON_TOQUESTION", "câu hỏi"); //New for 0.98finalRC1
define("_PS_CON_OR", "hoặc"); //New for 0.98finalRC2

//Save Messages
define("_SAVE_AND_RETURN", "Lưu các các phản hồi của bạn");
define("_SAVEHEADING", "Lưu khảo sát chưa hoàn tất của bạn");
define("_RETURNTOSURVEY", "Quay trở lại khảo sát");
define("_SAVENAME", "Tên");
define("_SAVEPASSWORD", "Mật khẩu");
define("_SAVEPASSWORDRPT", "Lập lại mật khẩu");
define("_SAVE_EMAIL", "DDiajn chỉ mail của bạn");
define("_SAVEEXPLANATION", "Điền tên và mật khẩu của bạn cho khảo sát này và nhấn lưu bên dưới.<br />\n"
				  ."Khảo sát của bạn sẽ được lưu với tên và mật khẩu, và có thể "
				  ."làm tiếp sau này bằng cách đăng nhập với cùng tên và mật khẩu.<br /><br />\n"
				  ."Nếu bạn cung cấp địa chỉ mail, một bức mail chưa thông tin sẽ được gửi "
				  ."đến bạn.");
define("_SAVESUBMIT", "Lưu bây giờ");
define("_SAVENONAME", "Bạn phải cung cấp một tên để lưu.");
define("_SAVENOPASS", "Bạn phải cung cấp một mật khẩu để lưu lại.");
define("_SAVENOPASS2", "Bạn phải nhập lại mật khẩu để lưu .");
define("_SAVENOMATCH", "Hai mật khẩu của bạn không giống nhau.");
define("_SAVEDUPLICATE", "Tên này đã được dùng cho khảo sát này.Bạn phải dùng một tên độc lập.");
define("_SAVETRYAGAIN", "Vui lòng làm lại.");
define("_SAVE_EMAILSUBJECT", "Thông tin lưu khảo sát");
define("_SAVE_EMAILTEXT", "Bạn hoặc ai đó sử dụng địa chỉ mail của bạn, đã lưu một "
						 ."khảo sát trong tiến trình.Thông tin sau sẽ được sử dụng "
						 ."để quay lại khảo sát này và tiếp tục những phần còn dang "
						 ."dở.");
define("_SAVE_EMAILURL", "Mở lại khảo sát của bạn bằng cách nhấn vào URL sau:");
define("_SAVE_SUCCEEDED", "Các phản hồi của khảo sát đã được lưu lại thành công");
define("_SAVE_FAILED", "Lỗi xảy ra và các phản hồi trong khảo sát của bạn không được lưu lại.");
define("_SAVE_EMAILSENT", "Một bức mail đã được gửi đi với thông tin về khảo sát đã được lưu của bạn.");

//Load Messages
define("_LOAD_SAVED", "Mở khảo sát chưa hoàn tấty");
define("_LOADHEADING", "Mở khảo sát trước đó đã được lưu");
define("_LOADEXPLANATION", "Bạn có thể mở một khảo sát mà trước đó đã được lưu từ màn hình này.<br />\n"
			  ."Điền vào 'tên' bạn đã dùng để lưu khảo sát,và mật khẩu.<br /><br />\n");
define("_LOADNAME", "Tên được lưu");
define("_LOADPASSWORD", "Mật khẩu");
define("_LOADSUBMIT", "Mở ngay bây giờ");
define("_LOADNONAME", "Bạn đã không cung cấp một cái tên");
define("_LOADNOPASS", "Bạn đã không cung cấp mật khẩu");
define("_LOADNOMATCH", "Không có khảo sát được lưu nào trùng khớp");

define("_ASSESSMENT_HEADING", "Ấn định");

define("_QL_IMAGE","Hình ảnh:");
define("_NOUSER1", "Đây là khảo sát được quản lý. Bạn cần có tài khoản đăng nhập để tham gia.");
define("_NOUSER2", "Nếu bạn đã có tài khoản đăng nhập ,xin nhập vào ô bên dưới và tiếp tục.");
define("_NOUSER3", "Tên đăng nhập của bạn không cho phép thực hiện khảo sát này.");
define("_USER_PS", "Tên đăng nhập");
define("_PASSWORD_PS", "Mật khẩu ");
define("_TL_USERNAME", "Tên đăng nhập");
define("_TL_PASSWORD", "Mật khẩu");
define("_TL_CPASSWORD", "Xác nhận mật khẩu");
define("_TL_FIRSTNAME", "Tên");
define("_TL_LASTNAME", "Họ");
define("_TL_BIRTHDAY", "Ngày sinh");

define("_TL_PHONE", "Điện thoại");
define("_TL_EMAIL", "Email");
define("_TL_ADDRESS", "Địa chỉ");
define("_TL_DEPART", "Tổ chức");
define("_REGISTER", "Đăng ký");
define("_TOKENPROMT", "Sử dụng thẻ bài để tham gia khảo sát này");
define("_USERPROMT", "Bạn cần có 1 tài khoản để tham gia khảo sát này");
define("_DOSURVEYPROMT", "Just click this link to do survey");
define("_GUESTPROMT", "Mọi người dều có thể thực hiện khảo sát này");
define("_BACKINDEX", "Trở về trang chủ");
define("_LOGGEDIN", "đã đăng nhập");
define("_LOGOUT", "Đăng xuất");
define("_LOGIN", "Đăng nhập");
define("_NOTLOGIN", "chưa đăng nhập");
define("_CHECKSETTINGS", "Cầu hình hệ thống");
define("_USERMANAGE", "Quản lý thành viên");
define("_SURVEYMANAGE", "Quản lý khảo sát");
define("_SURVEYLIST", "Danh sách khảo sát");
define("_USERMANAGE", "Quản lý thành viên");
define("_ADMINPAGE", "Đi đến trang admin");
define("_USERNAME", "Tên đăng nhập");
define("_CURRENTPASSWORD", "Mật khẩu hiện tại");
define("_NEWPASSWORD", "Mật khẩu mới");
define("_CNEWPASSWORD", "Xác nhận mật khẩu");
define("_FIRSTNAME", "Tên");
define("_LASTNAME", "Họ");
define("_BIRTHDAY", "Ngày sinh");
define("_ADDRESS", "Địa chỉ");
define("_PHONE", "Điện thoại");
define("_EMAIL", "Email");
define("_UPDATEPROFILE", "Cập nhật hồ sơ");
define("_UPDATEPRIVATEPROFILE", "Cập nhật hồ sơ cá nhân");
define("_DAY", "Ngày");
define("_MONTH", "Tháng");
define("_YEAR", "Năm");
define("_UPDATEPROFILESUCCESS", "Hiệu chỉnh hồ sơ thành công");
define("_UPDATEPROFILEERROR", "Lỗi hiệu chỉnh hồ sơ");
define("_PASSWORDNOTCORRECT", "Mật khẩu không đúng");
define("_SIGNUP", "Đăng ký");
define("_PASSWORD", "Mật khẩu");
define("_CPASSWORD", "Xác nhận mật khẩu");
define("_ROLE", "Quyền");
define("_UL_MEMBER", "Thành viên");
define("_UL_MODERATOR", "Moderator");
define("_SIGNUPSUCCESS", "Đăng ký thành công");
define("_SIGNUPERROR", "Đăng ký thất bại");
define("_SIGNUPEXSISTED", "Tên đăng nhập đã tồn tại! Xin vui lòng chọn tên khác");
define("_CURRENTSURVEY", "Khảo sát hiện tại");
define("_FORGETPASSWORD", "Quên mật khẩu?");

define("_AVAILABLEACTION", "Hành động");
define("_BYWAY", "Chọn ");

define("_CONTINUE", "Tiếp tục");
define("_CHANGEPASSWORD", "Thay đổi mật khẩu");

define("_PASSWORDNOTCORRECT", "Mật khẩu không đúng");
define("__PASSWORDHASCHANGED", "Thay đổi mật khẩu thành công");
define("_PASSWORDCHANGEERROR", "Lỗi trong khi đổi mật khẩu");
define("_LOGINFAIL", "Đăng nhập thất bại");
define("_CANNOTDOSURVEY", "Không thể thực hiện khảo sát này");
define("_USERSURVEYPROMT", "Bạn phải có tài khoản để thực hiện khảo sát này");


?>
