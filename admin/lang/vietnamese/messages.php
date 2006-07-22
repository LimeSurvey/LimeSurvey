<?php
define("_ADMINISTRATION", "Quản trị");
define("_SURVEY", "Khảo sát");
define("_GROUP", "Nhóm");
define("_QUESTION", "Câu hỏi");
define("_ANSWERS", "Câu trả lời");
define("_CONDITIONS", "Điều kiện");
define("_HELP", "Giúp đỡ");
define("_USERCONTROL", "User Control");
define("_ACTIVATE", "Kích hoạt khảo sát");
define("_DEACTIVATE", "Khử kích hoạt khảo sát");
define("_CHECKFIELDS", "Check Database Fields");
define("_CREATEDB", "Tạo cơ sở dữ liệu");
define("_CREATESURVEY", "Tạo mới survey"); //New for 0.98rc4
define("_SETUP", "Cài đặt PHPSurveyor");
define("_DELETESURVEY", "Xóa kháo sát");
define("_EXPORTQUESTION", "Xuất khảu câu hỏi");
define("_EXPORTSURVEY", "Xuất khẩu khảo sát");
define("_EXPORTLABEL", "Xuất khẩu tập nhãn");
define("_IMPORTQUESTION", "Nhập khẩu câu hỏi");
define("_IMPORTGROUP", "Nhập khẩu nhóm"); //New for 0.98rc5
define("_IMPORTSURVEY", "Nhập khẩu survey");
define("_IMPORTLABEL", "Nhập khẩu tập nhãn");
define("_EXPORTRESULTS", "Xuất khẩu các phản hồi");
define("_BROWSERESPONSES", "Tìm đến các phản hồi");
define("_BROWSESAVED", "Tìm đến các response đã lưu");
define("_STATISTICS", "Thống kê nhanh");
define("_VIEWRESPONSE", "Xem phản hồi");
define("_VIEWCONTROL", "Data View Control");
define("_DATAENTRY", "Data Entry");
define("_TOKENCONTROL", "Quản lý thẻ bài");
define("_TOKENDBADMIN", "Token Database Administration Options");
define("_DROPTOKENS", "Xóa các bảng Token");
define("_EMAILINVITE", "Email mời tham dự khảo sát");
define("_EMAILREMIND", "Email nhắc nhở");
define("_TOKENIFY", "Tạo thẻ bài");
define("_UPLOADCSV", "Tải tập tin CSV lên server");
define("_LABELCONTROL", "Quản lý tập nhãn"); //NEW with 0.98rc3
define("_LABELSET", "Tập nhãn"); //NEW with 0.98rc3
define("_LABELANS", "Nhãn"); //NEW with 0.98rc3
define("_OPTIONAL", "Tùy chọn"); //NEW with 0.98finalRC1

//DROPDOWN HEADINGS
define("_SURVEYS", "Khảo sát");
define("_GROUPS", "Nhóm");
define("_QUESTIONS", "Câu hỏi");
define("_QBYQ", "Câu hỏi này đến câu hỏi kia");
define("_GBYG", "Nhóm này đến nhóm kia");
define("_SBYS", "Tất cả trong một");
define("_LABELSETS", "Tập nhãn"); //New with 0.98rc3

//BUTTON MOUSEOVERS
//administration bar
define("_A_HOME_BT", "Trang quản trị mặc định");
define("_A_SECURITY_BT", "Thay đổi thiết lập bảo mật");
define("_A_BADSECURITY_BT", "Kích hoạt bảo mật");
define("_A_CHECKDB_BT", "Kiểm tra cơ sở dữ liệu");
define("_A_DELETE_BT", "Xóa toàn bộ khảo sát");
define("_A_ADDSURVEY_BT", "Tạo lập hoặc nhập khẩu một khảo sát mới");
define("_A_HELP_BT", "Hiển thị giúp đỡ");
define("_A_CHECKSETTINGS", "Kiểm tra cấu hình");
define("_A_BACKUPDB_BT", "Sao lưu dự phòng có sở dữ liệu"); //New for 0.98rc10
define("_A_TEMPLATES_BT", "Thiết kế các mẫu template"); //New for 0.98rc9
//Survey bar
define("_S_ACTIVE_BT", "Khảo sát đang được kích hoạt");
define("_S_INACTIVE_BT", "Khảo sát hiện thời chưa được kích hoạt");
define("_S_ACTIVATE_BT", "Kích hoạt khảo sát");
define("_S_DEACTIVATE_BT", "Khử kích hoạt khảo sát");
define("_S_CANNOTACTIVATE_BT", "Không thể kích hoạt khảo sát");
define("_S_DOSURVEY_BT", "Thực hiện khảo sát");
define("_S_DATAENTRY_BT", "Màn hình entry dữ liệu cho khảo sát");
define("_S_PRINTABLE_BT", "Phiên bản in ấn của khảo sát");
define("_S_EDIT_BT", "Hiệu chỉnh khảo sát hiện tại");
define("_S_DELETE_BT", "Xóa khảo sát hiện tại");
define("_S_EXPORT_BT", "Xuất khẩu khảo sát");
define("_S_BROWSE_BT", "Xem các thông tin phản hồi của khảo sát");
define("_S_TOKENS_BT", "Kích hoạt/hiệu chỉnh thẻ bài của khảo sát");
define("_S_ADDGROUP_BT", "Thêm một nhóm mới vào khảo sá");
define("_S_MINIMISE_BT", "Dấu các chi tiết của khảo sát");
define("_S_MAXIMISE_BT", "Hiển thị các chi tiết của khảo sát");
define("_S_CLOSE_BT", "Đóng khảo sát");
define("_S_SAVED_BT", "Cho xem các phản hồi đã lưu nhưng không gửi các phản hồi"); //New in 0.99dev01
define("_S_ASSESSMENT_BT", "Thiết lập các giới hạn của khảo sát"); //New in  0.99dev01
//Group bar
define("_G_EDIT_BT", "Hiệu chỉnh nhóm hiện tại");
define("_G_EXPORT_BT", "Xuất khẩu nhóm hiện tại"); //New in 0.98rc5
define("_G_DELETE_BT", "Xóa nhóm hiện tại");
define("_G_ADDQUESTION_BT", "Thêm mới câu hỏi vào nhóm");
define("_G_MINIMISE_BT", "Dấu các chi tiết của nhóm");
define("_G_MAXIMISE_BT", "Hiện thị các chi tiết của nhóm");
define("_G_CLOSE_BT", "Đóng nhóm");
//Question bar
define("_Q_EDIT_BT", "Hiệu chỉnh câu hỏi hiện tại");
define("_Q_COPY_BT", "Sao chép câu hỏi hiện tại"); //New in 0.98rc4
define("_Q_DELETE_BT", "Xóa câu hỏi hiện tại");
define("_Q_EXPORT_BT", "Xuất khẩu câu hỏi hiện tại");
define("_Q_CONDITIONS_BT", "Tạo điều kiệm cho câu hỏi");
define("_Q_ANSWERS_BT", "Hiệu chỉnh/thêm mới câu trả lời cho câu hỏi");
define("_Q_LABELS_BT", "Hiệu chỉnh/thêm mới tập nhãn");
define("_Q_MINIMISE_BT", "Dấu các chi tiết của câu hỏi");
define("_Q_MAXIMISE_BT", "Hiển thị các chi tiết của câu hỏi");
define("_Q_CLOSE_BT", "Đóng câu hỏi");
//Browse Button Bar
define("_B_ADMIN_BT", "Trở về mục quản trị khảo sát");
define("_B_SUMMARY_BT", "Hiển thị thông tin tổng kết");
define("_B_ALL_BT", "Trình bày các phản hồi");
define("_B_LAST_BT", "Trình bày 50 phản hồi cuối cùng");
define("_B_STATISTICS_BT", "Lấy thông tin thống kê của các phản hồi này");
define("_B_EXPORT_BT", "Xuất khẩu kết quả ra ứng dụng");
define("_B_BACKUP_BT", "Sao lưu dự phòng bảng kết quả dưới dạng tập tin SQL");
//Tokens Button Bar
define("_T_ALL_BT", "Trình bày các thẻ bài");
define("_T_ADD_BT", "Tạo mới một entry thẻ bài ");
define("_T_IMPORT_BT", "Nhâp khẩu thẻ bài từ tập tin CSV");
define("_T_EXPORT_BT", "Xuất khẩu thẻ bài từ tập tin CSV"); //New for 0.98rc7
define("_T_INVITE_BT", "Gửi email mời tham dự khảo sát");
define("_T_REMIND_BT", "Gửi email nhắc nhở");
define("_T_TOKENIFY_BT", "Tạo thẻ bài");
define("_T_KILL_BT", "Xóa bản token");
//Labels Button Bar
define("_L_ADDSET_BT", "Thêm mới một tập nhãn");
define("_L_EDIT_BT", "Hiểu chỉnh tập nhãn");
define("_L_DEL_BT", "Xóa tập nhãn");
//Datacontrols
define("_D_BEGIN", "Show start..");
define("_D_BACK", "Show last..");
define("_D_FORWARD", "Show next..");
define("_D_END", "Show last..");

//DATA LABELS
//surveys
define("_SL_TITLE", "Tiêu đề:");
define("_SL_SURVEYURL", "Địa chỉ tài nguyên của khảo sát (URL):"); //new in 0.98rc5
define("_SL_DESCRIPTION", "Mô tả :");
define("_SL_WELCOME", "Chào mừng:");
define("_SL_ADMIN", "Ban quản trị:");
define("_SL_EMAIL", "Địa chỉ mail của quản trị:");
define("_SL_FAXTO", "Fax:");
define("_SL_ANONYMOUS", "Nặc danh?");
define("_SL_EXPIRES", "Thời hạn :");
define("_SL_FORMAT", "Định dạng:");
define("_SL_DATESTAMP", "Tem thời gian?");
define("_SL_IPADDRESS", "Địa chỉ IP"); //New with 0.991
define("_SL_TEMPLATE", "Mẫu:");
define("_SL_LANGUAGE", "Ngôn ngữ:");
define("_SL_LINK", "Liên kết khi kết thúc:");  //Modified in 0.99
define("_SL_URL", "Địa chỉ URL kết thúc:");
define("_SL_URLDESCRIP", "Mô tả URL:");
define("_SL_STATUS", "Tình trạng:");
define("_SL_SELSQL", "Chọn tập tin SQL:");
define("_SL_USECOOKIES", "Dùng Cookies?"); //NEW with 098rc3
define("_SL_NOTIFICATION", "Thông báo:"); //New with 098rc5
define("_SL_ALLOWREGISTER", "Cho phép đăng ký tự do?"); //New with 0.98rc9
define("_SL_ATTRIBUTENAMES", "Tên thuộc tính của thẻ bài:"); //New with 0.98rc9
define("_SL_EMAILINVITE_SUBJ", "Tiêu đề mail mời tham dự:"); //New with 0.99dev01
define("_SL_EMAILINVITE", "Email mời tham dự:"); //New with 0.98rc9
define("_SL_EMAILREMIND_SUBJ", "Tiêu đề mail nhắc nhở:"); //New with 0.99dev01
define("_SL_EMAILREMIND", "Email nhắc nhở:"); //New with 0.98rc9
define("_SL_EMAILREGISTER_SUBJ", "Tiêu đề mail đăng ký khảo sát tự do"); //New with 0.99dev01
define("_SL_EMAILREGISTER", "Đăng ký tự do qua mail:"); //New with 0.98rc9
define("_SL_EMAILCONFIRM_SUBJ", "Tiêu đề mail xác nhận"); //New with 0.99dev01
define("_SL_EMAILCONFIRM", "Mail xác nhận"); //New with 0.98rc9
define("_SL_REPLACEOK", "Việc này sẽ thay thế những gì đã tồn tại. Tiếp tục ?"); //New with 0.98rc9
define("_SL_ALLOWSAVE", "Cho phép lưu?"); //New with 0.99dev01
define("_SL_AUTONUMBER", "Start ID numbers at:"); //New with 0.99dev01
define("_SL_AUTORELOAD", "Tự động đi đên URL khi kết thúc khảo sát?"); //New with 0.99dev01

define("_SL_ALLOWPREV", "Hiển thị nút [<< Trước] "); //New with 0.99dev01
define("_SL_USE_DEFAULT","Sử dụng mặc định");
define("_SL_UPD_SURVEY","Cập nhật khảo sát");

//groups
define("_GL_TITLE", "Tiêu đề:");
define("_GL_DESCRIPTION", "Mô tả:");
define("_GL_EDITGROUP","Hiệu chỉnh nhóm của Survey có ID :"); // New with 0.99dev02
define("_GL_UPDATEGROUP","Cập nhật nhóm"); // New with 0.99dev02
//questions
define("_QL_EDITQUESTION", "Hiệu chỉnh câu hỏi");// New with 0.99dev02
define("_QL_UPDATEQUESTION", "Cập nhật câu hỏi");// New with 0.99dev02
define("_QL_CODE", "Mã:");
define("_QL_QUESTION", "Câu hỏi:");
define("_QL_VALIDATION", "Tính hợp lệ:"); //New in VALIDATION VERSION
define("_QL_HELP", "Giúp đỡ:");
define("_QL_TYPE", "Loại:");
define("_QL_GROUP", "Nhóm:");
define("_QL_MANDATORY", "Tính bắt buộc:");
define("_QL_OTHER", "Thuộc tính khác:");
define("_QL_LABELSET", "Tập nhãn:");
define("_QL_COPYANS", "Sao chép câu trả lời?"); //New in 0.98rc3
define("_QL_QUESTIONATTRIBUTES", "Các thuộc tính câu hỏi :"); //New in 0.99dev01
define("_QL_COPYATT", "Sao chép các thuộc tính?"); //New in 0.99dev01
//answers
define("_AL_CODE", "Mã");
define("_AL_ANSWER", "Câu trả lời");
define("_AL_DEFAULT", "Mặc định");
define("_AL_MOVE", "Di chuyển");
define("_AL_ACTION", "Hành động");
define("_AL_UP", "Lên");
define("_AL_DN", "Xuống");
define("_AL_SAVE", "Luu");
define("_AL_DEL", "Xóa");
define("_AL_ADD", "Thêm");
define("_AL_FIXSORT", "Sắp xếp cố định");
define("_AL_SORTALPHA", "Sắp xếp Alpha"); //New in 0.98rc8 - Sort Answers Alphabetically
//users
define("_UL_USER", "Người dùng");
define("_UL_PASSWORD", "Mật khẩu");
define("_UL_SECURITY", "Bảo mật");
define("_UL_ACTION", "Hành động");
define("_UL_EDIT", "Hiểu chỉnh");
define("_UL_DEL", "Xóa");
define("_UL_ADD", "Thêm");
define("_UL_TURNOFF", "Tắt bảo mật");

//tokens
define("_TL_FIRST", "Tên");
define("_TL_LAST", "Họ");
define("_TL_EMAIL", "Email");
define("_TL_TOKEN", "Thẻ bài");
define("_TL_INVITE", "Thư mới đã gửi?");
define("_TL_DONE", "Đã hoàn tất?");
define("_TL_ACTION", "Hành động");
define("_TL_ATTR1", "Thuộc tính 1"); //New for 0.98rc7
define("_TL_ATTR2", "Thuộc tính 2"); //New for 0.98rc7
define("_TL_MPID", "MPID"); //New for 0.98rc7
//labels
define("_LL_NAME", "Tên tập hợp"); //NEW with 098rc3
define("_LL_CODE", "Mã"); //NEW with 098rc3
define("_LL_ANSWER", "Tiêu đề"); //NEW with 098rc3
define("_LL_SORTORDER", "Thứ tự"); //NEW with 098rc3
define("_LL_ACTION", "Hành động"); //New with 098rc3

//QUESTION TYPES
define("_5PT", "5 lựa chọn");
define("_DATE", "Ngày tháng");
define("_GENDER", "Giới tính");
define("_LIST", "Danh sách (nút chọn)"); //Changed with 0.99dev01
define("_LIST_DROPDOWN", "Danh sách (sổ xuống)"); //New with 0.99dev01
define("_LISTWC", "Danh sách chọn với chú thích");
define("_MULTO", "Đa lựa chọn");
define("_MULTOC", "Đa lựa chọn với chú thích");
define("_MULTITEXT", "Đa chổ trống ngắn");
define("_NUMERICAL", "Nhập số");
define("_RANK", "Xếp hạng");
define("_STEXT", "Đoạn văn bản ngắn");
define("_LTEXT", "Đoạn văn bản dài");
define("_HTEXT", "Văn bản lớn"); //New with 0.99dev01
define("_YESNO", "Có/không");
define("_ARR5", "Mãng (5 lựa chọn)");
define("_ARR10", "Mãng (10 lựa chọn)");
define("_ARRYN", "Mãng (Có/Không/Không chắc)");
define("_ARRMV", "Mãng (Tăng/Không đổi/Giảm)");
define("_ARRFL", "Mãng (Nhãn phức tạp)"); //Release 0.98rc3
define("_ARRFLC", "Mãng (Nhãn phức tạp) theo cột"); //Release 0.98rc8
define("_SINFL", "Single (Flexible Labels)"); //(FOR LATER RELEASE)
define("_EMAIL", "Địa chỉ email"); //FOR LATER RELEASE
define("_BOILERPLATE", "Boilerplate Question"); //New in 0.98rc6
define("_LISTFL_DROPDOWN", "Danh sách chọn (Nhãn phức tạp) (Dropdown)"); //New in 0.99dev01
define("_LISTFL_RADIO", "Danh sách chọn (Nhãn phức tạp) (nút chọn)"); //New in 0.99dev01
define("_SLIDER", "Slider"); //New for slider mod

//GENERAL WORDS AND PHRASES
define("_AD_YES", "Có");
define("_AD_NO", "Không");
define("_AD_CANCEL", "Thôi");
define("_AD_CHOOSE", "Xin chọn..");
define("_AD_OR", "Hoặc"); //New in 0.98rc4
define("_ERROR", "Lỗi");
define("_SUCCESS", "Thành công");
define("_REQ", "*Yêu cầu");
define("_ADDS", "Thêm khảo sát");
define("_ADDG", "Thêm nhóm");
define("_ADDQ", "Thêm câu hỏi");
define("_ADDA", "Thêm câu trả lời"); //New in 0.98rc4
define("_COPYQ", "Sao chéo câu hỏi"); //New in 0.98rc4
define("_ADDU", "Thêm người dùng");
define("_SEARCH", "Tìm kiếm"); //New in 0.98rc4
define("_SAVE", "Lưu các thay đổi");
define("_NONE", "Không có gì"); //as in "Do not display anything", "or none chosen";
define("_GO_ADMIN", "Màn hình quản trị chính"); //text to display to return/display main administration screen
define("_CONTINUE", "Tiếp tục");
define("_WARNING", "Cảnh báo");
define("_USERNAME", "Tên người dùng");
define("_PASSWORD", "Mật khẩu");
define("_DELETE", "Xóa");
define("_CLOSEWIN", "Đóng cửa sổ");
define("_TOKEN", "Thẻ bài");
define("_DATESTAMP", "Tem thời gian"); //Referring to the datestamp or time response submitted
define("_IPADDRESS", "Địa chỉ IP"); //Referring to the ip address of the submitter - New with 0.991
define("_COMMENT", "Chú thích");
define("_FROM", "Từ"); //For emails
define("_SUBJECT", "Tiêu đề"); //For emails
define("_MESSAGE", "Nội "); //For emails
define("_RELOADING", "Màn hình tải lại . Xin đợi trong giây lát!");
define("_ADD", "Thêm");
define("_UPDATE", "Cập nhật");
define("_BROWSE", "Đi đến"); //New in 098rc5
define("_AND", "và"); //New with 0.98rc8
define("_SQL", "SQL"); //New with 0.98rc8
define("_PERCENTAGE", "Phần trăm"); //New with 0.98rc8
define("_COUNT", "Đếm"); //New with 0.98rc8

//SURVEY STATUS MESSAGES (new in 0.98rc3)
define("_SS_NOGROUPS", "Số nhóm trong khảo sát:"); //NEW for release 0.98rc3
define("_SS_NOQUESTS", "Sô câu hỏi trong khảo sát:"); //NEW for release 0.98rc3
define("_SS_ANONYMOUS", "Khảo sát này là nặc danh."); //NEW for release 0.98rc3
define("_SS_TRACKED", "Khảo sát này là không nặc danh."); //NEW for release 0.98rc3
define("_SS_DATESTAMPED", "Các phản hồi sẽ được gắn tem thời gian"); //NEW for release 0.98rc3
define("_SS_IPADDRESS", "Địa chỉ IP sẽ được log vào."); //New with 0.991
define("_SS_COOKIES", "Khảo sát dùng cookies để truy cập đến các điều khiển."); //NEW for release 0.98rc3
define("_SS_QBYQ", "Khảo sát theo dạng câu hỏi này đến câu hỏi kia."); //NEW for release 0.98rc3
define("_SS_GBYG", "Khảo sát theo dạng nhóm này đến nhóm kia."); //NEW for release 0.98rc3
define("_SS_SBYS", "Khảo sát theo dạng 1 trang duy nhất."); //NEW for release 0.98rc3
define("_SS_ACTIVE", "Khảo sát hiện tại đang được kích hoạt."); //NEW for release 0.98rc3
define("_SS_NOTACTIVE", "Khảo sát hiện tại chưa được kích hoạt.."); //NEW for release 0.98rc3
define("_SS_SURVEYTABLE", "Tên bảng khảo sát là :"); //NEW for release 0.98rc3
define("_SS_CANNOTACTIVATE", "Khảo sát không thể được kích hoạt."); //NEW for release 0.98rc3
define("_SS_ADDGROUPS", "Bạn cần phải thêm nhóm"); //NEW for release 0.98rc3
define("_SS_ADDQUESTS", "Bạn cần phải thêm câu hỏi"); //NEW for release 0.98rc3
define("_SS_ALLOWREGISTER", "Nếu thẻ bài được dùng, mọi người phải đăng ký cho khảo sát này."); //NEW for release 0.98rc9
define("_SS_ALLOWSAVE", "Người tham gia có thể lưu nhiều lúc cho đến khi khảo sát kết thúc."); //NEW for release 0.99dev01

//QUESTION STATUS MESSAGES (new in 0.98rc4)
define("_QS_MANDATORY", "Câu hỏi bắt buộc"); //New for release 0.98rc4
define("_QS_OPTIONAL", "Câu hỏi tùy chọn"); //New for release 0.98rc4
define("_QS_NOANSWERS", "Bạn cần thêm câu trả lời cho câu hỏi này."); //New for release 0.98rc4
define("_QS_NOLID", "Bạn cần chọn 1 tập nhãn cho câu hỏi này"); //New for release 0.98rc4
define("_QS_COPYINFO", "Chú ý : Bạn phải điền vào mã cho 1 câu hỏi mới."); //New for release 0.98rc4

//General Setup Messages
define("_ST_NODB1", "Cơ sở dữ liệu surveyor không tồn tại.");
define("_ST_NODB2", "Cơ sở dữ liệu bạn đã chọn chưa được tạo hoặc có lỗi trong việc truy cập đên nó.");
define("_ST_NODB3", "PHPSurveyor có thể tạo cơ sở dữ liệu cho bạn");
define("_ST_NODB4", "Cơ sở dữ liệu được chọn là :");
define("_ST_CREATEDB", "Tạo cơ sở dữ liệu");

//USER CONTROL MESSAGES
define("_UC_CREATE", "Tạo tập tin htaccess mặc định");
define("_UC_NOCREATE", "Không thể tạo được tập tin htaccess . Kiểm tra tập tin config.php với cài đặt và bạn cần có quyền ghi trong thư mục của bạn.");
define("_UC_SEC_DONE", "Cấp độ bảo mật bây giờ đã được cài đặt!");
define("_UC_CREATE_DEFAULT", "Tạo người dùng mặc định");
define("_UC_UPDATE_TABLE", "Cập nhật bảng người dùng");
define("_UC_HTPASSWD_ERROR", "Lỗi khi tạo tập tin htpasswd");
define("_UC_HTPASSWD_EXPLAIN", "Nếu bạn dùng WIndows server ,đề nghị bạn copy tập tin htpasswd.exe từ apache vào thư mục admin của bạn.");
define("_UC_SEC_REMOVE", "Tháo bỏ thiết lập bảo mật");
define("_UC_ALL_REMOVED", "Truy nhập tập tin ,tập tin mật khẩu và bảng user đã xóa.");
define("_UC_ADD_USER", "Thêm người dùng");
define("_UC_ADD_MISSING", "Không thể thêm người dùng. Tên đăng nhập /mật khẩu chưa được cung cấp");
define("_UC_DEL_USER", "Xóa người dùng");
define("_UC_DEL_MISSING", "Không thể xóa người dùng. Tên đăng nhập chưa được cung cấp.");
define("_UC_MOD_USER", "Thay đổi thông tin người dùng");
define("_UC_MOD_MISSING", "Không thể thay đổi thông tin người dùng.Tên đăng nhập/mật khẩu chưa được cung cấp");
define("_UC_TURNON_MESSAGE1", "Bạn chưa khởi tạo thiết lập bảo mật cho hệ thống khảo sát .Nhấn vào Khởi tạo bảo mật.");
define("_UC_TURNON_MESSAGE2", "Khuyến cáo : khi khảo sát được khỏi tạo bạn cần đổi mật khẩu mặc định.");
define("_UC_INITIALISE", "Khởi tạo bảo mật");
define("_UC_NOUSERS", "Không có người dùng nào tồn tại trong bảng. Bạn nên tắt chức năng bảo mật, và có thể bật nó lại sau này.");
define("_UC_TURNOFF", "Tắt chức năng bảo mật");

//Activate and deactivate messages
define("_AC_MULTI_NOANSWER", "Câu hỏi này là dạng có nhiều câu trả lời nhưng chưa có câu trả lời nào.");
define("_AC_NOTYPE", "Câu hỏi này chưa có câu hỏi nào thuộc loại tập hợp.");
define("_AC_NOLID", "Câu hỏi này cần có 1 tập nhãn nhưng không có tập nhãn nào được thiết lập."); //New for 0.98rc8
define("_AC_CON_OUTOFORDER", "Câu hỏi này có 1 tập hợp các điều kiện.Tuy nhiên điều kiện dựa vào câu hỏi ngay sau nó.");
define("_AC_FAIL", "Khảo sát không được đánh dấu kiểm tra tính nhất quán");
define("_AC_PROBS", "Những vần đề sau được tìm thấy :");
define("_AC_CANNOTACTIVATE", "Khảo sát không thể được kích hoạt nếu những vấn đề này chưa được giải quyết.");
define("_AC_READCAREFULLY", "ĐỌC KỸ ĐIỀU CẨN THẬN NÀY TRƯỚC KHI TIẾP TỤC");
define("_AC_ACTIVATE_MESSAGE1", "Bạn chỉ nên kích hoạt khảo sát kgi bạn chắc chắn phần cài đặt của bạn đã hoàn tất và sẽ không cần phải thay đổi .");
define("_AC_ACTIVATE_MESSAGE2", "Một khi khảo sát của bạn được kích hoạt ,bạn sẽ không còn:<ul><li>Thêm hoặc xóa nhóm</li><li>Thêm hoặc xóa câu trả lời cho các câu hỏi có nhiều câu trả lời</li><li>Thêm hoặc xóa câu hỏi</li></ul>");
define("_AC_ACTIVATE_MESSAGE3", "Tuy nhiện bạn vẫn có thể:<ul><li>Hiệu chỉnh (thay đổi) mã của câu hỏi nội dung hoặc loại</li><li>Hiệu chỉnh (thay đổi) tên nhóm</li><li>Thêm, xóa, hiệu chỉnh các câu trả lời trước (trừ loại câu hỏi có nhiều câu trả lời)</li><li>Thay đổi tên và mô tả của khảo sát</li></ul>");
define("_AC_ACTIVATE_MESSAGE4", "Một khi bạn vào khảo sát này, nếu bạn muốn thêm hoặc xóa nhóm hoặc câu hỏi , bạn cần phải khư kích hoạt,điều này sẽ di chuyển tất cả dữ liệu bạn đã dùng vào một bảng dữ liệu khác.");
define("_AC_ACTIVATE", "Kích hoạt");
define("_AC_ACTIVATED", "Khảo sát đã được kích hoạt.Bảng kết quả được tạo thành công.");
define("_AC_NOTACTIVATED", "Khảo sát không thê được kích hoạt.");
define("_AC_NOTPRIVATE", "Đây không phải là một khảo sát nặc danh.Bảng token cũng phải được tạo ra.");
define("_AC_REGISTRATION", "Khảo sát này cho phép đăng ký tự do .Bảng token cũng phải được tạo ra.");
define("_AC_CREATETOKENS", "Khởi tạo thẻ bài");
define("_AC_SURVEYACTIVE", "Khảo sát này đang hoạt động, và các phản hồi đang được ghi nhận lại.");
define("_AC_DEACTIVATE_MESSAGE1", "Khi một khảo sát hoạt động, một bàn được tạo ra để lưu tất cả các entry dữ liểu .");
define("_AC_DEACTIVATE_MESSAGE2", "Khi bạn khử kích hoạt một khảo sát,tất cả các dữ liệu của bảng gốc sẽ được dời đi nơi khác va khi bạn kích hoạt lại khảo sát, bảng này sẽ trống rỗng bạn không thể truy cập các dữ liệu này với phpSurveyor nữa.");
define("_AC_DEACTIVATE_MESSAGE3", "Các dữ liệu củakhảo sát bị khử kích hoạt có thể truy nhập bằng cách dùng các công cụ truy vấn mySQL ví dụ như phpMyAdmin.Nếu khảo sát của bạn dùng thẻ bài,bàng này cũng sẽ được đổi tên và cũng được truy cập bằng cách này.");
define("_AC_DEACTIVATE_MESSAGE4", "Các phản hồi của bạn cũng sẽ được đổi tên thành :");
define("_AC_DEACTIVATE_MESSAGE5", "Bạn nên xuất khẩu các phản hồi của bạn trước khi khử kích hoạt Nhấn \"Thôi\" để quay lại màn hình quản trị mà không khử kích hoạt khảo sát này .");
define("_AC_DEACTIVATE", "Khử kích hoạt");
define("_AC_DEACTIVATED_MESSAGE1", "Bảng response (phản hồi) được đổi tên thành : ");
define("_AC_DEACTIVATED_MESSAGE2", "Các phản hồi của khảo sát này sẽ không còn giá trị trong phpSurveyors .");
define("_AC_DEACTIVATED_MESSAGE3", "Bạn nên ghi chú lại tên của bảng nàu để có thể có lúc bạn cần truy cập thông tin sau này.");
define("_AC_DEACTIVATED_MESSAGE4", "Các thẻ bài liên quan đên khao sát này đã được đổi tên thành : ");
//CHECKFIELDS
define("_CF_CHECKTABLES", "Kiêm trả đê chắc rằng các bảng tồn tại");
define("_CF_CHECKFIELDS", "Kiêm trả đê chắc rằng các trường tồn tại");
define("_CF_CHECKING", "Kiểm tra");
define("_CF_TABLECREATED", "Bảng đã được tạo");
define("_CF_FIELDCREATED", "Trường đã được tạo");
define("_CF_OK", "Đồng ý");
define("_CFT_PROBLEM", "Dường như có vài bảng hay trường bị thiếu trong cơ sở dữ liệu.");

//CREATE DATABASE (createdb.php)
define("_CD_DBCREATED", "Cơ sỡ dữ liệu đã được tạo.");
define("_CD_POPULATE_MESSAGE", "Xin hãy nhân vào bên dưới để tạo cơ sở dữ liệu");
define("_CD_POPULATE", "Tạo cơ sở dư liệu");
define("_CD_NOCREATE", "Không thể tạo cơ sở dữ liệu");
define("_CD_NODBNAME", "Thông tin cơ sở dữ liệu không được cung cấp.câu lệnh này phải được thực hiện từ admin.php .");

//DATABASE MODIFICATION MESSAGES
define("_DB_FAIL_GROUPNAME", "Nhóm mới không thể được thêm vào.Thiếu tên nhóm bắt buộc.");
define("_DB_FAIL_GROUPUPDATE", "Nhóm không thể được cập nhật");
define("_DB_FAIL_GROUPDELETE", "Nhóm không thể xóa được");
define("_DB_FAIL_NEWQUESTION", "Câu hỏi không thể được tạo ra");
define("_DB_FAIL_QUESTIONTYPECONDITIONS", "Câu hỏi không thể được cập nhật. Có những điều kiện cho các câu hỏi khác phụ thuộc vào câu trả lời của câu hỏi này và khi thay đổi loại câu hỏi sẽ gây ra vấn đề này . Bạn phải xóa các điều kiện trước khi đổi kiểu câu hỏi.");
define("_DB_FAIL_QUESTIONUPDATE", "Câu hỏi không thể được cập nhật");
define("_DB_FAIL_QUESTIONDELCONDITIONS", "Câu hỏi không thể được xóa.Vì có điều kiện cho các câu hỏi khác có liên quan đến câu hỏi này.Bạn không thể xóa câu hỏi này cho đến khi các điều kiện của nó được xóa");
define("_DB_FAIL_QUESTIONDELETE", "Câu hỏi không thể được xóa");
define("_DB_FAIL_NEWANSWERMISSING", "Câu trả lời không thể được thêm vào.Bạn phải đính kèm mã và 1 câu trả lời.");
define("_DB_FAIL_NEWANSWERDUPLICATE", "Câu trả lwofi không thể được thêm vào.Vì đã có câu trả lới tương ứng với mã này.");
define("_DB_FAIL_ANSWERUPDATEMISSING", "Câu trả lời không thể cập nhật.Bạn phải ddsinh kèm cả mã và 1 câu trả lời.");
define("_DB_FAIL_ANSWERUPDATEDUPLICATE", "Câu trả lời không thể cập nhật Vì đã tồn tại câu trả lời tương ứng với mã này.");
define("_DB_FAIL_ANSWERUPDATECONDITIONS", "Câu trả lời không thể cập nhật. Bạn đã thay đổi mã câu trả lời nhưng có nhưng điều kiện tới nhưng câu hỏi khác mà phụ thuộc vào mã cũ của của trả lời của câu hỏi này. Bạn phải xóa những điều kiện này trước khi thay đỏi mã của câu trả lời này.");
define("_DB_FAIL_ANSWERDELCONDITIONS", "Câu trả lời không thê xóa.Vì có những điều kiện cho những câu hỏi khác phụ thuộc vào câu trả lời này . Bạn không thể xóa câu trả lời này cho đến khi những điều kiện đó được xóa bỏ");
define("_DB_FAIL_NEWSURVEY_TITLE", "Khảo sát không được tạo ra vì nó không có tiêu đề vắn tắt");
define("_DB_FAIL_NEWSURVEY", "Khảo sát không thê được tạo ra");
define("_DB_FAIL_SURVEYUPDATE", "Khảo sát không thể được cập nhật");
define("_DB_FAIL_SURVEYDELETE", "Khảo sát không thể được xóa");

//DELETE SURVEY MESSAGES
define("_DS_NOSID", "Bạn chưa chọn khảo sát cần xóa");
define("_DS_DELMESSAGE1", "Bạn sắp xóa khảo sát này");
define("_DS_DELMESSAGE2", "Tiến trình này sẽ xóa khảo sát này và tất cả những nhóm, câu hỏi, câu trả lời và các câu hỏi có liên quan .");
define("_DS_DELMESSAGE3", "Chúng tôi đề nghị trước khi xóa khảo sát này bạn nên xuất khảu toàn bộ khảo sát từ màn hình quản trị khảo sát.");
define("_DS_SURVEYACTIVE", "Khảo sát này đang hoạt động và các bảng response đang tồn tại.Nếu bạn xóa khảo sát này,những phản hồi sẽ bị xóa theo. Chúng tôi đề nghị bạn nên xuất khẩu các phản hồi trước khi xóa khảo sát này.");
define("_DS_SURVEYTOKENS", "Khảo sát này có liên quan đên bảng token Nêu bạn xóa khảo sát này các bảng giữ các token cũng sẽ bị xóa. Chúng tôi đề nghị bạn nên xuất khảu thẻ bài hoặc sao lưu chúng trước khi xóa khảo sát này.");
define("_DS_DELETED", "Khảo sát này đã được xóa.");

//DELETE QUESTION AND GROUP MESSAGES
define("_DG_RUSURE", "Xóa nhóm này đồng nghĩa với xóa các câu hỏi và các câu trả lời chứa trong nó. Bạn có chắc là muốn tiếp tục không ?"); //New for 098rc5
define("_DQ_RUSURE", "Xóa câu hỏi này đồng nghĩa với việc xóa các câu trả lời của nó . Bạn có chắc là muốn tiếp tục không?"); //New for 098rc5

//EXPORT MESSAGES
define("_EQ_NOQID", "Không cung cấp mã QID . Không thể sao lưu hàng loạt(dump) câu hỏi dump question.");
define("_ES_NOSID", "Không cung cấp mã SID. Không thê sao lưu hàng loạt khảo sát");

//EXPORT RESULTS
define("_EX_FROMSTATS", "Lọc từ các script thống kê");
define("_EX_HEADINGS", "Các câu hỏi");
define("_EX_ANSWERS", "Các câu trả lời");
define("_EX_FORMAT", "Định dạng");
define("_EX_HEAD_ABBREV", "Các heading viết tắt");
define("_EX_HEAD_FULL", "Heading toàn phần");
define("_EX_HEAD_CODES", "Các mã câu hỏi");
define("_EX_ANS_ABBREV", "Các mã câu trả lời");
define("_EX_ANS_FULL", "Câu trả lời toàn phần");
define("_EX_FORM_WORD", "Microsoft Word");
define("_EX_FORM_EXCEL", "Microsoft Excel");
define("_EX_FORM_CSV", "CSV Comma Delimited");
define("_EX_EXPORTDATA", "Xuất khẩu dữ liệu");
define("_EX_COLCONTROLS", "Điều khiên cột"); //New for 0.98rc7
define("_EX_TOKENCONTROLS", "Điều khiển thẻ bài"); //New for 0.98rc7
define("_EX_COLSELECT", "Chọn các cột"); //New for 0.98rc7
define("_EX_COLOK", "Chọn các cột mà bạn muốn xuất khảu "); //New for 0.98rc7
define("_EX_COLNOTOK", "Khảo sát của bạn chứa nhiều hơn 255 cột của các phả hồi.Các ứng dụng bảng tính như Excel bị giời hạn nhỏ hơn 255. Hãy chọn những cột bạn muốn xuất khẩu trong danh sách bên dưới ."); //New for 0.98rc7
define("_EX_TOKENMESSAGE", "Khảo sát của bạn có liên quan đên các dữ liệu thẻ bài tương ứng với mỗi phản hồi .Hãy chọn bất cứ các trường cộng thêm mà bạn muốn xuất khẩu"); //New for 0.98rc7
define("_EX_TOKSELECT", "Hãy chọn các trường thẻ bài"); //New for 0.98rc7

//IMPORT SURVEY MESSAGES
define("_IS_FAILUPLOAD", "Lỗi xảy ra khi upload tập tin.Điều này có thể do quyền truy cập thư mục admin không đúng.");
define("_IS_OKUPLOAD", "Tập tin upload thành công.");
define("_IS_READFILE", "Đang đọc tập tin..");
define("_IS_WRONGFILE", "Tập ti này không phải là 1 tập tin của PHPSurveyor. Nhập khẩu hỏng.");
define("_IS_IMPORTSUMMARY", "Tổng kết nhập khẩu khảo sát");
define("_IS_SUCCESS", "Quá trình nhập khẩu khảo sát hoàn tất.");
define("_IS_IMPFAILED", "Quá trình nhập khẩu khảo sát này hỏng");
define("_IS_FILEFAILS", "Tập tin không chứa dữ liệu PHPSurveyor theo đúng định dạng.");

//IMPORT GROUP MESSAGES
define("_IG_IMPORTSUMMARY", "Tổng kết nhập khẩu nhóm ");
define("_IG_SUCCESS", "Nhập khẩu nhóm hoàn tất.");
define("_IG_IMPFAILED", "Nhập khẩu tập tin về nhóm thất bại");
define("_IG_WRONGFILE", "Tập tin này không phải là tập tin về nhóm của PHPSurveyor .Nhập khẩu thất bại.");

//IMPORT QUESTION MESSAGES
define("_IQ_NOSID", "Không cung cấp mã SID(khảo sát) Không thể nhạp khẩu câu hỏi.");
define("_IQ_NOGID", "Không cung câp mã GID (nhóm) has Không thể nhập khẩu câu hỏi");
define("_IQ_WRONGFILE", "Tập tin này không phải là tập tin câu hỏi của PHPSurveyor question file. Nhập khẩu thất bại.");
define("_IQ_IMPORTSUMMARY", "Tooeng kết nhập khẩu câu hỏi");
define("_IQ_SUCCESS", "Nhập khẩu câu hỏi hoàn tất");

//IMPORT LABELSET MESSAGES
define("_IL_DUPLICATE", "Tồn tại tập nhãn trùng lấp , do đó tập hộ này không thể nhập khẩu được. Tập trùng lấp sẽ được sử dụng.");

//BROWSE RESPONSES MESSAGES
define("_BR_NOSID", "Bạn chưa chọn 1 khảo sat nào để duyệt.");
define("_BR_NOTACTIVATED", "Khảo sát này chưa được kích hoạt. Không có kết quả nào để duyệt");
define("_BR_NOSURVEY", "Không có khảo sát nào khớp.");
define("_BR_EDITRESPONSE", "Hiệu chỉnh phần(entry) này");
define("_BR_DELRESPONSE", "Xóa mục(entry) này");
define("_BR_DISPLAYING", "Các bảng ghi được trình bày:");
define("_BR_STARTING", "Bắt đầu từ:");
define("_BR_SHOW", "Thể hiện");
define("_DR_RUSURE", "Bạn co chắc bạn muốn xóa mục (entry) này?"); //New for 0.98rc6

//STATISTICS MESSAGES
define("_ST_FILTERSETTINGS", "Cấu hình bộ lọc");
define("_ST_VIEWALL", "Thể hiện tổng kết của tất cả các trường có giá trị"); //New with 0.98rc8
define("_ST_SHOWRESULTS", "Thể hiện Stats"); //New with 0.98rc8
define("_ST_CLEAR", "Làm sạch"); //New with 0.98rc8
define("_ST_RESPONECONT", "Nội dung các phản hồi"); //New with 0.98rc8
define("_ST_NOGREATERTHAN", "Số lớn hơn"); //New with 0.98rc8
define("_ST_NOLESSTHAN", "Số nhỏ hơn"); //New with 0.98rc8
define("_ST_DATEEQUALS", "Ngày (YYYY-MM-DD) bằng"); //New with 0.98rc8
define("_ST_ORBETWEEN", "Điều kiện OR giữa"); //New with 0.98rc8
define("_ST_RESULTS", "Các kết quả"); //New with 0.98rc8 (Plural)
define("_ST_RESULT", "Kết quả"); //New with 0.98rc8 (Singular)
define("_ST_RECORDSRETURNED", "Không có bảng tin nào trong khảo sát này"); //New with 0.98rc8
define("_ST_TOTALRECORDS", "Toàn bộ các bảng tin của khảo sát"); //New with 0.98rc8
define("_ST_PERCENTAGE", "Phần trăm của toàn bộ"); //New with 0.98rc8
define("_ST_FIELDSUMMARY", "Tổng kết cho trường :"); //New with 0.98rc8
define("_ST_CALCULATION", "Tính toán"); //New with 0.98rc8
define("_ST_SUM", "Tổng"); //New with 0.98rc8 - Mathematical
define("_ST_STDEV", "Độ lệc tiêu chuẩn"); //New with 0.98rc8 - Mathematical
define("_ST_AVERAGE", "Trung bình"); //New with 0.98rc8 - Mathematical
define("_ST_MIN", "Tối thiểu"); //New with 0.98rc8 - Mathematical
define("_ST_MAX", "Tối đa"); //New with 0.98rc8 - Mathematical
define("_ST_Q1", "Quartile thư nhất(Q1)"); //New with 0.98rc8 - Mathematical
define("_ST_Q2", "Quartile thứ hai(Median)"); //New with 0.98rc8 - Mathematical
define("_ST_Q3", "Quartile thứ ba (Q3)"); //New with 0.98rc8 - Mathematical
define("_ST_NULLIGNORED", "*Các giá trị Null đều được bỏ qua trong quá trình tính toán"); //New with 0.98rc8
define("_ST_QUARTMETHOD", "*Q1 vaf 3 được tính toán bằng cách dùng <a href='http://mathforum.org/library/drmath/view/60969.html' target='_blank'>minitab method</a>"); //New with 0.98rc8

//DATA ENTRY MESSAGES
define("_DE_NOMODIFY", "Không thể thay đổi");
define("_DE_UPDATE", "Cập nhật Entry");
define("_DE_NOSID", "Bạn chưa chọn 1 khảo sát cho data-entry.");
define("_DE_NOEXIST", "Khảo sát bạn chọn không tồn tại");
define("_DE_NOTACTIVE", "Khảo sát này chưa được kích hoạt.Các phản hồi của bạn chưa thể được lưu lại");
define("_DE_INSERT", "Chèn dữ liệu");
define("_DE_RECORD", "Mẫu entry được gán bởi mẫu tin có id: ");
define("_DE_ADDANOTHER", "Thêm một mẫu tin khác");
define("_DE_VIEWTHISONE", "Thể hiện mẫu tin này");
define("_DE_BROWSE", "Duyệt các phản hồi");
define("_DE_DELRECORD", "Mẫu tin được xóa");
define("_DE_UPDATED", "Mẫu tin đã được cập nhật.");
define("_DE_EDITING", "Hiệu chỉnh phản hồi");
define("_DE_QUESTIONHELP", "Giúp đỡ về câu hỏi này");
define("_DE_CONDITIONHELP1", "Chỉ trả lời câu hỏi này nếu những điều kiện sau được thõa:"); 
define("_DE_CONDITIONHELP2", "đối với câu hỏi {QUESTION}, bạn trả lời {ANSWER}"); //This will be a tricky one depending on your languages syntax. {ANSWER} is replaced with ALL ANSWERS, separated by _DE_OR (OR).
define("_DE_AND", "Và");
define("_DE_OR", "hoặc");
define("_DE_SAVEENTRY", "Lưu như 1 phần khảo sát hoàn tất"); //New in 0.99dev01
define("_DE_SAVEID", "Nhận dạng:"); //New in 0.99dev01
define("_DE_SAVEPW", "Mật khẩu:"); //New in 0.99dev01
define("_DE_SAVEPWCONFIRM", "Xác nhận lại mật khẩu:"); //New in 0.99dev01
define("_DE_SAVEEMAIL", "Email:"); //New in 0.99dev01

//TOKEN CONTROL MESSAGES
define("_TC_TOTALCOUNT", "Tổng số mẫu tin trong bảng Token này:"); //New in 0.98rc4
define("_TC_NOTOKENCOUNT", "Tổng số mà không tính những token duy nhất:"); //New in 0.98rc4
define("_TC_INVITECOUNT", "Tổng sô thư mới được gửi đi:"); //New in 0.98rc4
define("_TC_COMPLETEDCOUNT", "Tổng sô khảo sát đã hoàn tất:"); //New in 0.98rc4
define("_TC_NOSID", "Bạn chưa chọn một khảo sát");
define("_TC_DELTOKENS", "Sắp sửa xóa bảng token của khảo sát này.");
define("_TC_DELTOKENSINFO", "Nếu bạn xóa bảng này các thẻ bài sẽ không còn đòi hỏi để truy nhập vào khảo sát này.<br>Một bảng dự phòng của bảng này sẽ được tạo ra nếu bạn tiếp tục.Hệ thống quản trị sẽ có thể truy nhập đến bảng này.");
define("_TC_DELETETOKENS", "Xóa các thẻ bài");
define("_TC_TOKENSGONE", "Các bảng token đã được xóa và các thẻ bài không còn được yêu cầu để tham gia khảo sát này.<BR> Mọt bảng sao của bảng này đã được tạp thành và có thể truy cập bằng bởi quản trị hệ thống.");
define("_TC_NOTINITIALISED", "Các thẻ bài chưa được khởi tạo cho khảo sát này.");
define("_TC_INITINFO", "Nếu bạn khởi tạo các thẻ bài cho khảo sát này, khảo sát chỉ sẽ được truy cập bởi những người dùng đã được gán thẻ bài.");
define("_TC_INITQ", "Do you want to create a tokens table for this survey?");
define("_TC_INITTOKENS", "Initialise Tokens");
define("_TC_CREATED", "Bảng toke đã được tạo nên cho khảo sát này.");
define("_TC_DELETEALL", "Xóa tất cả các entry thẻ bài");
define("_TC_DELETEALL_RUSURE", "Bạn có chắc là xóa tất cả các entry thẻ bài?");
define("_TC_ALLDELETED", "Tất cả các entry thẻ bài đã được xóa.");
define("_TC_CLEARINVITES", "Xác lập thuôc tính cho tất cả entry trờ thành 'Chưa gửi thư mời'-'No invitation sent'.");
define("_TC_CLEARINV_RUSURE", "Bạn có chắc là muốn xác lập tất cả các mẫu tin thành NO?");
define("_TC_CLEARTOKENS", "Xóa tất cả các số thẻ bài duy nhất");
define("_TC_CLEARTOKENS_RUSURE", "Bạn cóc chắc muốn xóa tất cả các số thẻ bài Are you sure you want to delete all unique token numbers?");
define("_TC_TOKENSCLEARED", "Tất cả các sô thẻ bài duy nhất đã được xóa.");
define("_TC_INVITESCLEARED", "Tất cả các entry đã được thiết lập thành Không được mời- 'Not Invited'.");
define("_TC_EDIT", "Hiệu chỉnh các entry thẻ bài");
define("_TC_DEL", "Xóa các entry thẻ bài");
define("_TC_DO", "Tiến hành khảo sát");
define("_TC_VIEW", "Xem các phản hồi");
define("_TC_UPDATE", "Cập nhật phản hồi"); // New with 0.99 stable
define("_TC_INVITET", "Gửi thư mời cho entry này");
define("_TC_REMINDT", "Gửi thư nhắc nhở đên entry này");
define("_TC_INVITESUBJECT", "Lời mời tham dự khảo sát {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSUBJECT", "Lời nhắc nhở tham dự khảo sát {SURVEYNAME}"); //Leave {SURVEYNAME} for replacement in scripts
define("_TC_REMINDSTARTAT", "Bắt đầu từ sô TID :");
define("_TC_REMINDTID", "Gửi đến số TID :");
define("_TC_CREATETOKENSINFO", "Nhấn đồng ý để tạo các thẻ bài cho tất các entry trong danh sách này mà chưa được gán.Bạn có chắc đồng ý?");
define("_TC_TOKENSCREATED", "{TOKENCOUNT} thẻ bài đã được tạo ra"); //Leave {TOKENCOUNT} for replacement in script with the number of tokens created
define("_TC_TOKENDELETED", "Thẻ bài đã được xóa.");
define("_TC_SORTBY", "Sắp xếp theo: ");
define("_TC_ADDEDIT", "Thêm hoặc hiệu chỉnh thẻ bài");
define("_TC_TOKENCREATEINFO", "Bạn có thể để trống,và tự động tạo các thẻ bài bằng cách nhấn'Tạo các thẻ bài'");
define("_TC_TOKENADDED", "Tạo thẻ bài mới");
define("_TC_TOKENUPDATED", "Cập nhật thẻ bài");
define("_TC_UPLOADINFO", "Tập tin phải thuộc dạng chuẩn CSV (comma delimited) tập tin không có dấu nháy.Dòng đầu tiên chứa thông tin header (sẽ được bỏ). Dữ liệu được sắp xếp theo dạng \"firstname, lastname, email, [token], [attribute1], [attribute2]\".");
define("_TC_UPLOADFAIL", "Tập tin upload không tìm thấy.Kiểm tra sự cho phép truy nhập và đường dẫn của thư mục được upload lên"); //New for 0.98rc5
define("_TC_IMPORT", "Nhập khẩu tập tin CSV");
define("_TC_CREATE", "Tạo các entry thẻ bài");
define("_TC_TOKENS_CREATED", "{TOKENCOUNT} Các mẫu tin đã được tạo");
define("_TC_NONETOSEND", "Không có các địa chỉ mail thích hợp để gửi.Lý do bởi vì không có mail nào thỏa tiêu chuẩn  - có địa chỉ mail,nhưng chưa được gửi thư mời,và đã thực hiện xong khảo sát và có thẻ bài.");
define("_TC_NOREMINDERSTOSEND", "Không có mail thích hợp nào được gửi.Lý do vì không có mail nào thỏa mãn tiêu chuẩn  - có địa chỉ mail,đã gửi thư mời,nhưng chưa hoàn tất khảo sát.");
define("_TC_NOEMAILTEMPLATE", "Mẫu thư mời không tìm thấy.Tạp tin này phải tồn tại trong thư mục mẫu template mặc định.");
define("_TC_NOREMINDTEMPLATE", "Mẫu nhắc nhở không tìm thấy.Tạp tin này phải tồn tại trong thư mục mẫu template mặc định");
define("_TC_SENDEMAIL", "Gửi thư mời");
define("_TC_SENDINGEMAILS", "Đang gửi thư mời");
define("_TC_SENDINGREMINDERS", "Đang gửi thư nhắc nhở");
define("_TC_EMAILSTOGO", "Đang có nhiều mail đang bị treo hơn là số gửi đi trong 1 gói.Tiếp tục gửi mail bằng cách nhấn vào bên dưới.");
define("_TC_EMAILSREMAINING", "Vẫn còn  {EMAILCOUNT} email chuản bị gửi đi."); //Leave {EMAILCOUNT} for replacement in script by number of emails remaining
define("_TC_SENDREMIND", "Gửi thư nhắc nhở");
define("_TC_INVITESENTTO", "Thư mời gửi tới:"); //is followed by token name
define("_TC_REMINDSENTTO", "Thư nhắc nhở gửi tới"); //is followed by token name
define("_TC_UPDATEDB", "Cập nhật bảng token với các trường mới"); //New for 0.98rc7
define("_TC_MAILTOFAILED", "Gửi mail đến {FIRSTNAME} {LASTNAME} ({EMAIL}) thất bại"); //New for 0.991
define("_TC_EMAILINVITE_SUBJ", "Thư mới đên tham gia khảo sát"); //New for 0.99dev01
define("_TC_EMAILINVITE", "Chào {FIRSTNAME},\n\nBạn được mời tham gia khảo sát của chúng tôi.\n\n"
						 ."Tiêu đề của khảo sát:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."Để tham gia nhân vào liên kết bên dưới.\n\nSincerely,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Nhấn vào đâu để tham gia khảo sát:\n"
						 ."{SURVEYURL}"); //New for 0.98rc9 - default Email Invitation
define("_TC_EMAILREMIND_SUBJ", "Thư nhắc nhở tham gia khảo sát"); //New for 0.99dev01
define("_TC_EMAILREMIND", "Dear {FIRSTNAME},\n\nGần đây chúng tôi đã gửi thư mời tham gia khảo sát đến bạn.\n\n"
						 ."Chúng tôi lưu ý rằng bạn chưa hoàn tất khảo sát,và chúng tôi muốn nhắc bạn răng khảo sát vẫn đang được tiến hành và mong bạn tham gia.\n\n"
						 ."Tiêu đề của khảo sát là:\n\"{SURVEYNAME}\"\n\n\"{SURVEYDESCRIPTION}\"\n\n"
						 ."Để tham gia,xin nhấn vào liên kết bên dưới.\n\nThân,\n\n"
						 ."{ADMINNAME} ({ADMINEMAIL})\n\n"
						 ."----------------------------------------------\n"
						 ."Nhấn vào đây để tiến hành khảo sát:\n"
						 ."{SURVEYURL}"); //New for 0.98rc9 - default Email Reminder
define("_TC_EMAILREGISTER_SUBJ", "Xác nhận đăng ký khảo sát"); //New for 0.99dev01
define("_TC_EMAILREGISTER", "Dear {FIRSTNAME},\n\n"
						  ."Bạn hay ai đó sử dụng địa chỉ email của bạn, đã đăng ký "
						  ."tham gia khảo sát trục tuyến với tiêu đề {SURVEYNAME}.\n\n"
						  ."Đê hoàn tất khảo sát này nhấn vào liên kết URL sau:\n\n"
						  ."{SURVEYURL}\n\n"
						  ."Nếu bạn có bất kỳ câu hỏi nào về khảo sát, hoặc nếu bạn "
						  ."không đăng ký tham gia được và tin rằng địa chỉ mail "
						  ."có lỗi, xin liên lạc {ADMINNAME} với {ADMINEMAIL}.");//NEW for 0.98rc9
define("_TC_EMAILCONFIRM_SUBJ", "Xác nhận hoàn tất khảo sáty"); //New for 0.99dev01
define("_TC_EMAILCONFIRM", "Dear {FIRSTNAME},\n\nMail này nhằm xác nhận bạn đã hoàn tất khảo sát mang tên {SURVEYNAME} "
						  ."và các phản hồi của bạn đã được lưu lại Chân thành cám ơn sự tham gia của bạn.\n\n"
						  ."Nếu bạn có câu hỏi nào thêm về địa chỉ mail này xin liên hệ  {ADMINNAME} tại{ADMINEMAIL}.\n\n"
						  ."Thân chào,\n\n"
						  ."{ADMINNAME}"); //New for 0.98rc9 - Confirmation Email

//labels.php
define("_LB_NEWSET", "Tạo mới tập nhãn");
define("_LB_EDITSET", "Hiệu chỉnh tập nhãn");
define("_LB_FAIL_UPDATESET", "Cập nhậ tập nhãn thất bại");
define("_LB_FAIL_INSERTSET", "Thêm mới một tập nhãn thất bại");
define("_LB_FAIL_DELSET", "Không thể xóa tập nhãn -Có những câu hỏi phụ thuộc vào nó.Bạn phải xóa những câu hỏi này trước .");
define("_LB_ACTIVEUSE", "Bạn không thể thay đổi mã,xóa hoặc thêm các entry trong tập nhãn vì nó đang được sử dụng bởi một kháo sát đang hoạt động.");
define("_LB_TOTALUSE", "Một số khảo sát đang sử dụng tập nhãn.Chỉnh sửa mã , thêm hoặc xóa các entry trong tập nhãncó thể phát sinh những kết quả không mong muốn cho các khảo sát khác.");
//Export Labels
define("_EL_NOLID", "Không cung cấp mã LID . Không thể đỏ hàng loạt (dump) các tập nhãn.");
//Import Labels
define("_IL_GOLABELADMIN", "Trở về trang quản lý nhãn");

//PHPSurveyor System Summary
define("_PS_TITLE", "PHPSurveyort tóm tắt hệ thống");
define("_PS_DBNAME", "Tên cơ sở dữ liệu");
define("_PS_DEFLANG", "Ngô ngữ mặc định");
define("_PS_CURLANG", "Ngôn ngữ hiện tại");
define("_PS_USERS", "Các người dùng");
define("_PS_ACTIVESURVEYS", "Các khảo sát được kích hoạt");
define("_PS_DEACTSURVEYS", "Các khảo sát chưa được kích hoạt");
define("_PS_ACTIVETOKENS", "Bảng các thẻ bài đang được kích hoạt");
define("_PS_DEACTTOKENS", "Bảng các thẻ bài đã bị khử kích hoạtDe-activated Token Tables");
define("_PS_CHECKDBINTEGRITY", "Kiểm tra tích hơp dữ liệu Data Integrity của PHPSurveyor "); //New with 0.98rc8

//Notification Levels
define("_NT_NONE", "Không có mail thông báo"); //New with 098rc5
define("_NT_SINGLE", "Mail thông báo cơ bản"); //New with 098rc5
define("_NT_RESULTS", "Gửi maul thông báo với các mã kết quả"); //New with 098rc5

//CONDITIONS TRANSLATIONS
define("_CD_CONDITIONDESIGNER", "Thiết kế điều kiện"); //New with 098rc9
define("_CD_ONLYSHOW", "Chỉ thể hiện câu hỏi {QID} NẾU"); //New with 098rc9 - {QID} is repleaced leave there
define("_CD_AND", "VÀ"); //New with 098rc9
define("_CD_COPYCONDITIONS", "Sao chép điều kiện"); //New with 098rc9
define("_CD_CONDITION", "Điều kiện"); //New with 098rc9
define("_CD_ADDCONDITION", "Thêm điều kiện"); //New with 098rc9
define("_CD_EQUALS", "Bằng"); //New with 098rc9
define("_CD_COPYRUSURE", "Bạn có chắc muốn chép các điều kiện này đến các câu hỏi bạn đã lựa chọn?"); //New with 098rc9
define("_CD_NODIRECT", "Bạn không thể chạy đoạn mã này trực tiếp."); //New with 098rc9
define("_CD_NOSID", "Bạn chưa chọn một khảo sát."); //New with 098rc9
define("_CD_NOQID", "Bạn chưa chịn một câu hỏi."); //New with 098rc9
define("_CD_DIDNOTCOPYQ", "Không thẻ sao chép câu hỏi"); //New with 098rc9
define("_CD_NOCONDITIONTOCOPY", "Không có điều kiện nào được chọn để sao chép"); //New with 098rc9
define("_CD_NOQUESTIONTOCOPYTO", "Không có câu hỏi được chọn đê sao chép điều kiện tới"); //New with 098rc9
define("_CD_COPYTO", "sao chép tới"); //New with 0.991

//TEMPLATE EDITOR TRANSLATIONS
define("_TP_CREATENEW", "Tạo mới template"); //New with 098rc9
define("_TP_NEWTEMPLATECALLED", "Tạo template mới có tên :"); //New with 098rc9
define("_TP_DEFAULTNEWTEMPLATE", "NewTemplate"); //New with 098rc9 (default name for new template)
define("_TP_CANMODIFY", "Mẫu template này có thể được chỉnh sửa"); //New with 098rc9
define("_TP_CANNOTMODIFY", "Mẫu template này không thể được chỉnh sửa"); //New with 098rc9
define("_TP_RENAME", "Đổi tên template này");  //New with 098rc9
define("_TP_RENAMETO", "Đổi tên template này thành:"); //New with 098rc9
define("_TP_COPY", "Tạo một bảng sao của mẫu template này");  //New with 098rc9
define("_TP_COPYTO", "Tạo một bản sao của mẫu template này có tên là:"); //New with 098rc9
define("_TP_COPYOF", "copy_of_"); //New with 098rc9 (prefix to default copy name)
define("_TP_FILECONTROL", "Điều khiển tập tin:"); //New with 098rc9
define("_TP_STANDARDFILES", "Các tập tin chuẩn:");  //New with 098rc9
define("_TP_NOWEDITING", "Bắt đầu biên soạn:");  //New with 098rc9
define("_TP_OTHERFILES", "Các tập tin khác:"); //New with 098rc9
define("_TP_PREVIEW", "Xem trước:"); //New with 098rc9
define("_TP_DELETEFILE", "Xóa"); //New with 098rc9
define("_TP_UPLOADFILE", "Upload"); //New with 098rc9
define("_TP_SCREEN", "Màn hình:"); //New with 098rc9
define("_TP_WELCOMEPAGE", "Trang chào mừng"); //New with 098rc9
define("_TP_QUESTIONPAGE", "Trang câu hỏi"); //New with 098rc9
define("_TP_SUBMITPAGE", "Trang submit");
define("_TP_COMPLETEDPAGE", "Trang hoàn tất"); //New with 098rc9
define("_TP_CLEARALLPAGE", "Trang clear all"); //New with 098rc9
define("_TP_REGISTERPAGE", "Trang đăng ký"); //New with 098finalRC1
define("_TP_EXPORT", "Xuất khẩu mẫu"); //New with 098rc10
define("_TP_LOADPAGE", "Trang load"); //New with 0.99dev01
define("_TP_SAVEPAGE", "Trang lưu"); //New with 0.99dev01

//Saved Surveys
define("_SV_RESPONSES", "Các phản hồi đã được lưu:");
define("_SV_IDENTIFIER", "Mã phân biệt");
define("_SV_RESPONSECOUNT", "Đã trả lời");
define("_SV_IP", "Địa chỉ IP");
define("_SV_DATE", "Ngày tháng lưu");
define("_SV_REMIND", "Nhắc nhở");
define("_SV_EDIT", "Biên soạn");

//VVEXPORT/IMPORT
define("_VV_IMPORTFILE", "Nhập khẩu tập tin khảo sát VV ");
define("_VV_EXPORTFILE", "Xuất khảu tâp tin khảo sát VV ");
define("_VV_FILE", "Tập tin:");
define("_VV_SURVEYID", "Mã khảo sát ID:");
define("_VV_EXCLUDEID", "Loại trừ các mẫy tin ID?");
define("_VV_INSERT", "Khi một mẫu tin trùng với 1 mẫu tin ID :");
define("_VV_INSERT_ERROR", "Thông báo lỗi (và bỏ qua mẫu tin mới).");
define("_VV_INSERT_RENUMBER", "Đánh số lại mẫu tin mới.");
define("_VV_INSERT_IGNORE", "Bỏ qua mẫu tin mới.");
define("_VV_INSERT_REPLACE", "Thay thế mẫu tin đã tồn tại.");
define("_VV_DONOTREFRESH", "Chú ý:<br />Không được làm tươi trang này,điều này sẽ dẫn đến nhập khẩu tập tin 1 lần nữa và tạo ra trùng lấp");
define("_VV_IMPORTNUMBER", "Tổng cộng các mẫu tin được nhập khẩu:");
define("_VV_ENTRYFAILED", "Nhập khẩu thất bải trên mẫu tin");
define("_VV_BECAUSE", "bởi vì");
define("_VV_EXPORTDEACTIVATE", "Xuất khẩu , sau đó khử kích hoạt khảo sát");
define("_VV_EXPORTONLY", "Xuất khẩu nhưng để khảo sát trong tình trạng kích hoạt");
define("_VV_RUSURE", "Nếu bạn chọn chức năng xuất khẩu và khử kích hoạt,dẫn đến việc đổi tên các bảng phản hồi response và nó không dễ dàng đê phục hồi lại.Bạn có chắc ?");

//ASSESSMENTS
define("_AS_TITLE", "Ấn định");
define("_AS_DESCRIPTION", "Nếu bạn tạo bất kỳ sự ấn định nào trong trang này cho khảo sát hiện thời được chọn,sự ấn định sẽ thực hiện  the assessment will be performed at the end of the survey after submission");
define("_AS_NOSID", "Chưa cung cấp SID ");
define("_AS_SCOPE", "Phạm vi");
define("_AS_MINIMUM", "Tối thiểu");
define("_AS_MAXIMUM", "Tối đa");
define("_AS_GID", "Nhómp");
define("_AS_NAME", "Tên/Header");
define("_AS_HEADING", "Heading");
define("_AS_MESSAGE", "Message");
define("_AS_URL", "URL");
define("_AS_SCOPE_GROUP", "Nhóm");
define("_AS_SCOPE_TOTAL", "Tổng kết");
define("_AS_ACTIONS", "Các hành động");
define("_AS_EDIT", "Biên soạn");
define("_AS_DELETE", "Xóa");
define("_AS_ADD", "Thêm");
define("_AS_UPDATE", "Cập nhật");

//Question Number regeneration
define("_RE_REGENNUMBER", "Tạo lại số của câu hỏi:"); //NEW for release 0.99dev2
define("_RE_STRAIGHT", "Liên tục"); //NEW for release 0.99dev2
define("_RE_BYGROUP", "Theo nhóm"); //NEW for release 0.99dev2

// Databse Consistency Check
define ("_DC_TITLE", "Kiêm tra tính nhất quán của dữ liệu<br /><font size='1'>Nếu lỗi xuất hiện bạn phải chạy script này lặp lại nhiều lần. </font>"); // New with 0.99stable
define ("_DC_QUESTIONSOK", "Mọi câu hỏi tuân theo tiêu chuẩn nhất quán"); // New with 0.99stable
define ("_DC_ANSWERSOK", "Mọi câu trả lời tuân theo các tiêu chuản nhất quán"); // New with 0.99stable
define ("_DC_CONDITIONSSOK", "Mọi điều kiện tuân theo các tiêu chuẩn nhất quán"); // New with 0.99stable
define ("_DC_GROUPSOK", "Mọi nhóm tuân theo các tiêu chuẩn nhất quán"); // New with 0.99stable
define ("_DC_NOACTIONREQUIRED", "Không có hành động nào cơ sở dữ liệu yêu cầu"); // New with 0.99stable
define ("_DC_QUESTIONSTODELETE", "Những câu hỏi sau đây nên được xóa"); // New with 0.99stable
define ("_DC_ANSWERSTODELETE", "Những câu trả lời sau cần được xóa"); // New with 0.99stable
define ("_DC_CONDITIONSTODELETE", "Những điều kiện sau cần được xóa"); // New with 0.99stable
define ("_DC_GROUPSTODELETE", "Những nhóm sau cần được xóa"); // New with 0.99stable
define ("_DC_ASSESSTODELETE", "Những ấn định sau cần được xóa"); // New with 0.99stable
define ("_DC_QATODELETE", "Những thuộc tính câu hỏi sau cần được xóa"); // New with 0.99stable
define ("_DC_QAOK", "Tất cả các thuốc tính câu hỏi tuân theo các tiêu chuẩn nhất quán"); // New with 0.99stable
define ("_DC_ASSESSOK", "Tất cả các ấn định tuân theo các tiêu chuẩn nhất quán"); // New with 0.99stable

//new
define("_QL_IMAGE","Hình ảnh:");
define("_SL_DEPARTMENT", "Tổ chức:");
define("_SL_TYPE", "Loại:");
define("_SL_TOKEN", "Token");
define("_SL_USER", "Thành viên");
define("_QL_ORDER_TABLE", "Trình tự");
define("_QL_QUESTION_TABLE", "Câu hỏi");
define("_QL_HELP_TABLE", "Trợ giúp");
define("_QL_TYPE_TABLE", "Loại");
define("_QL_DETAILS_TABLE", "Chi tiết");
define("_QL_ACTION_TABLE", "Hành động");
define("_QL_IMPORT_TABLE", "Import");
define("_AC_CREATEUSERS","Tạo thành viên");
define("_UC_CREATED","1 bảng user đã được tạo ra cho khảo sát này");
define("_TC_DELETEALLUSER", "Xóa tất cả các quyền thành viên tham gia khảo sát này");
define("_TC_DELETEALLUSER_RUSURE", "Bạn có chắc bạn muốn xóa các quyền tham gia của thành viên đến khảo sát này?");
define("_TC_ALLDELETEDUSER", "Tất cả các quyền thành viên tham gia khảo sát này đã được xóa.");
define("_T_KILLUSER_BT", "Xóa bảng users");
define("_TC_NOUSERCOUNT", "Tổng cộng:"); 
define("_TC_TOTALUSERCOUNT", "Toàn bộ Records trong bảng User:"); 
define("_DROPUSERS", "Xóa bản User");
define("_TC_DELUSERSINFO", "Nếu bạn xóa bảng này , các thành viên sẽ không còn yêu cầu đến khảo sát này<br>ruy cập bản này.");
define("_TC_DELETEUSERS", "Xóa các thành viên");
define("_TC_USERSGONE", "Bảng users đã được xóa các thành viên không còn có thể đến khảo sát này.<BR> Một bảng lưu dự phòng sẽ được tạo nếu bạn tiếp tụcd.Admin của hệ thống có thể chỉnh sửa được.");
define("_USERCONTROL", "Quản lý thành viên");
define("_USERDBADMIN", "Tùy chọn quản trị CSDL thành viên");
define("_AC_NOTPRIVATEUSER", "Đây không phải là khảo sát nặc danh.Bảng user cần phải được tạo.");
define("_U_ALL_BT", "Thể hiện các thành viên");
define("_U_UPDATE_BT", "Hiệu chỉnh thông tin của bạn");
define("_U_ADD_BT", "Tạo mới 1 thành viên");
define("_U_IMPORT_BT", "Import thành viên từ tập tin Excel");
define("_US_IMPORT_BT", "Import thành viên từ khảo sát khác "); 
define("_UA_BT", "Chứng thực thành viên từ nguồn CSDL khác"); 
define("_UADD_BT", "Tạo mới thông tin chứng thực"); 

define("_U_KILL_BT", "Xóa bảng users");

define("_TL_USERNAME", "Tên đăng nhập");
define("_TL_NAME", "Tên");
define("_TL_BIRTHDAY", "Ngày sinh");
define("_TL_DAY", "Ngày");
define("_TL_MONTH", "Tháng");
define("_TL_YEAR", "Năm");
define("_TL_PASSWORD", "Mật khẩu");
define("_TL_NEWPASSWORD", "Mật khẩu mới");
define("_TL_CPASSWORD", "Confirm Password");
define("_TL_PHONE", "Điện thoại");
define("_TL_ADDRESS", "Địa chỉ");
define("_TL_DEPART", "Tổ chức");
define("_TC_ADDUSER", "Tạo mới thành viên");
define("_SU_SHOWUSER", "Liệt kê các thành vi");
define("_SUS_SHOWUSER", "Liệt kê các thành viên từ các khảo sát khác");
define("_TC_USERDELETED", "Thành viên đã được xóa khỏi khảo sát này.");
define("_IU_COPYUSER", "Import thành viên");
//authentication
define("_AUTH_ORDER", "Thứ tự");
define("_AUTH_DESCRIPTION", "Mô tả");
define("_AUTH_DBHOST", "Chứng thực CSDL");
define("_AUTH_DBHOST_DETAILS", "Host của server chứa CSDL");
define("_AUTH_DBTYPE", "Lọa CSDL");
define("_AUTH_DBTYPE_DETAILS", "Loại CSDL ");
define("_AUTH_DBNAME", "Tên CSDL");
define("_AUTH_DBNAME_DETAILS", "Tên của chính CSDL");
define("_AUTH_DBUSER", "CSDL username");
define("_AUTH_DBUSER_DETAILS", "Username của CSDL  ");
define("_AUTH_DBPASS", "CSDL password");
define("_AUTH_DBPASS_DETAILS", "Password đi cùng với username ở trên  ");
define("_AUTH_DBUSERTABLE", "Bảng User ");
define("_AUTH_DBUSERTABLE_DETAILS", "Tên của bảng trong CSDL ");
define("_AUTH_DBRELATIONTABLE", "Bản quan hệ");
define("_AUTH_DBRELATIONTABLE_DETAILS", "Tên của bảng quan hệ trong CSDL(tùy chọn) ");
define("_AUTH_DBDEPARTMENTTABLE", "Bảng tổ chức");
define("_AUTH_DBDEPARTMENTTABLE_DETAILS", "Tên của bảng tổ chức trong CSDL(tùy chọn) ");

define("_AUTH_DBFIELDUSER", "Thành viên");
define("_AUTH_DBFIELDUSER_DETAILS", "Tên của trường chứa usernames  ");
define("_AUTH_DBFIELDID", "ID");
define("_AUTH_DBFIELDID_DETAILS", "Tên của trường chứa department (tổ chức) ");

define("_AUTH_DBFIELDPASS", "Mật khẩu");
define("_AUTH_DBFIELDPASS_DETAILS", "Tên của trường chứa passwords ");
define("_AUTH_DBPASSTYPE", "Loại mật khẩu");
define("_AUTH_DBPASSTYPE_DETAILS", "Chọn định dạng của trưowfng password được dùng. Mã hóa MD5 hữu ích khi kết nối đến ứng dụng web khác như PostNuke  ");
define("_AUTH_DBMAPDATA", "Làm trùng dữ liệu");
define("_AUTH_UPDATELOCAL", "Cập nhật địa phương ");
define("_AUTH_TABLE", "Bảng Authentication ");
define("_AUTH_RELATION", "Chứng thực quan hệ");
define("_AUTH_DBFIELDDEPART", "Tổ chức");
define("_AUTH_DBFIELDDEPART_DETAILS", "Tên của trường chứa department");
define("_AUTH_GET", "Lấy danh sách các tổ chức");
define("_AUTH_ACTION", "Hành động");
define("_AUTH_SELECTDEPART", "--Chọn tổ chức--");
define("_AUTH_DBFIELDMAP_DETAILS", "<p>Các trường này là tùy chọn. Bạn có thể chọn điền trước một vài trường của Moodle <b>các trường CSDL bên ngoài</b>mà bạn có thể thiệt lập được .Nêu bạn để trống mặc định sẽ được thiết lập</p>
");
define("_AUTH_DELETE","Thông tin này đã được xóa");
define("_S_HISTORY_BT","Xem lịch sử khảo sát");
//users_admin
define("_UL_ORDER","Thứ tự");
define("_UL_FIRSTNAME","Tên");
define("_UL_LASTNAME","Họ");
define("_UL_BIRTHDAY","Ngày sinh");
define("_UL_ADDRESS","Địa chỉ");
define("_UL_TELEPHONE","Điện thoại");
define("_UL_EMAIL","Email");
define("_UL_DEPARTMENT","Tổ chức");
define("_UL_ROLE","Quyền");
define("_UL_ACTION","Hành động");
define("_UL_RESET","Đặt lại mật khẩu");
define("_UL_MODERATOR","Morderator");
define("_UL_SUPPTERMODE","Supper mode");
define("_UL_MEMBER","Thành viên");
define("_A_USERMANGER","Quản lý thành viên");
define("_U_ALL_BT", "Thể hiện thành viên");
define("_T_ADD_BT", "Tạo mới thành viên");
define("_UC_USERADDED", "Tạo mới thành viên");
define("_UC_EDIT", "Hiệu chỉnh thông tin");
define("_UC_CHANGEROLE", "Thay đổi quyền");
define("_UC_CHANGE", "Thay đổi mật khẩu");
define("_U_CHANGE_BT", "Thay đổi mật khẩu");
define("_CHANGE", "Thay đổi mật khẩu");
define("_UC_USERDELETED","Thành viên đã được xóa");
define("_UC_USERRESETED","Mật khẩu đã được đặt lại trùng với tên đăng nhập");
define("_UC_USERCHANGED","Quyền của thành viên đã được thay đổi");
define("_UC_ERRORROLE","Bạn không thể thực hiện được chức năng này");

define("_A_LOGOUT_BT","Đăng xuất");
define("_A_INDEX_BT","Trở về trang chủ");
define("_DETAILS","Chi tiết");
define("_EDIT","Thay đổi");

define("_S_EXPORTHTML_BT","Export tập tin html");
define("_SL_VIEWRESULT", "Xem kết quả?");
define("_AUTH_DBFIELDUSERRELATION","Field user link");
define("_AUTH_DBFIELDUSERRELATION_DETAILS","Trường user của bảng user cha ");
define("_AUTH_DBFIELDDEPARTMENTRELATION","Quan hệ tổ chức");
define("_AUTH_DBFIELDDEPARTMENTRELATION_DETAILS","Trường Department của bảng department cha ");
define("_AUTH_DBFIELDDEPARTID","Kết nối trừong department");
define("_A_USERSURVEY","Quản lý khảo sát");
define("_SL_ID","ID");
define("_SL_ORDER","Thứ tự");
define("_SL_ACTIVE","Kích hoạt");
define("_MSL_ADMIN","Admin");
define("_MSL_TITLE","Tự đề");
define("_MSL_TYPE","Loại");
define("_MSL_DESCRIPTION","Mô tả");
define("_MSL_SHOW","Thể hiện");
define("_SL_DEDTAILS","Chi tiết");
define("_ACTIVE","Các thành viên kích hoạt");
define("_MEMBERACTIVE","Thành viên này chưa được kích hoạt");
define("_ADD_CATALORY","Tạo mới category");
define("_EDIT_CATALORY","Hiệu chỉnh catalogy");
define("_BROWSE_CATALORY","Xem danh sách catagory");
define("_NAME","Tên");
define("_CATAGORY","Category");
define("_CHOOSECATAGORY","Chọn category");
define("_SURVEYLIST","Survey list");
define("_ERRDUPLICATE","Category này đã tồn tại");
define("_SEARCH","Tìm kiếm");
define("_CHOOSETYPE","Chọn loại");
define("_MSL_ACTIVE","Kích hoạt");
define("_BROWSE_ACTIVESURVEY","Liệt kê các khảo sát kích hoạt");
define("_BROWSE_SURVEY","Liệt kê các khảo sát");
define("_RESPONSENO","Phản hồi");
define("_MSL_EXPIREDDATE","Hết hạn");
define("_MSL_ACTION","Hành động");
define("_ST_SHOWUSERS","Thể hiện báo cáo");

define("_ST_REPORT","Xem bảng thành viên");
define("_ORDER","Thứ tự");
define("_CURRENTSURVEY","Khảo sát hiện tại");
define("_DEACTIVEDATE","Ngày khử kích hoạt:");
define("_CHOOSESURVEY","Chọn");
define("_SURVEY_PHARSE","Chọn thời điểm khảo sát");
define("_UC_UPLOADINFO", "Tập tin phải ở dạng chuẩn CSV (ngăn cách bởi dấu phẩy) . Dòng đầu tiên chứa thông tin header (sẽ bị lọa). Dữ liệu sắp theo thứ tựs \"username, firstname, lastname, email\". Mật khẩu sẽ được mã hóa bằng thuật toán MD5");
define("_DUPLICATEUSER"," đã tồn tại");
define("_TC_USERS_CREATED", "{USERCOUNT} mẫu tin được taoj");
define("_UC_CREATE", "Đang tạo tài khoản");
define("_NOREPONSEDATA", "Không có phản hồi");


define("_EX_USERCONTROLS", "Điều khiển thành viên"); //New for 0.98rc7

define("_EX_USERSELECT", "Chọn trường users"); //New for 0.98rc7
define("_EX_USERMESSAGE", "Khảo sát của bạn có thể associated các phản hồi. Chọn các trường cần export."); //New for 0.98rc7
define("_NOTVIEWRESULT", "Bạn không thể xem các phản hồi của khảo sát này .Liên hẹ admin cho quyền này !"); //New for 0.98rc7
//define("_NOTVIEWRESULT", "You can't not view response of this survey . Contact admin do have this role !"); //New for 0.98rc7
define("_MAINPAGE","Trở về trang chính");
?>
