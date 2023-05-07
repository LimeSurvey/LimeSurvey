<?php
/**
 * TbHtml class file.
 * @author Antonio Ramirez <ramirez.cobos@gmail.com>
 * @author Christoffer Niska <christoffer.niska@gmail.com>
 * @copyright Copyright &copy; Christoffer Niska 2013-
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @package bootstrap.helpers
 */

/**
 * Bootstrap HTML helper.
 */
class TbHtml extends CHtml // required in order to access the protected methods in CHtml
{
    //
    // TYPOGRAPHY
    // --------------------------------------------------

    const TEXT_ALIGN_LEFT = 'left';
    const TEXT_ALIGN_CENTER = 'center';
    const TEXT_ALIGN_RIGHT = 'right';
    const TEXT_ALIGN_JUSTIFY = 'justify';
    const TEXT_ALIGN_NOWRAP = 'nowrap';

    const TEXT_COLOR_DEFAULT = '';
    const TEXT_COLOR_WARNING = 'warning';
    const TEXT_COLOR_ERROR = 'error';
    const TEXT_COLOR_INFO = 'info';
    const TEXT_COLOR_SUCCESS = 'success';

    const HELP_TYPE_INLINE = 'inline';
    const HELP_TYPE_BLOCK = 'block';

    //
    // FORM
    // --------------------------------------------------

    const FORM_LAYOUT_VERTICAL = 'vertical';
    const FORM_LAYOUT_HORIZONTAL = 'horizontal';
    const FORM_LAYOUT_INLINE = 'inline';
    const FORM_LAYOUT_SEARCH = 'search';

    const INPUT_TYPE_TEXT = 'textField';
    const INPUT_TYPE_PASSWORD = 'passwordField';
    const INPUT_TYPE_URL = 'urlField';
    const INPUT_TYPE_EMAIL = 'emailField';
    const INPUT_TYPE_NUMBER = 'numberField';
    const INPUT_TYPE_RANGE = 'rangeField';
    const INPUT_TYPE_DATE = 'dateField';
    const INPUT_TYPE_TEXTAREA = 'textArea';
    const INPUT_TYPE_FILE = 'fileField';
    const INPUT_TYPE_RADIOBUTTON = 'radioButton';
    const INPUT_TYPE_CHECKBOX = 'checkBox';
    const INPUT_TYPE_DROPDOWNLIST = 'dropDownList';
    const INPUT_TYPE_LISTBOX = 'listBox';
    const INPUT_TYPE_CHECKBOXLIST = 'checkBoxList';
    const INPUT_TYPE_INLINECHECKBOXLIST = 'inlineCheckBoxList';
    const INPUT_TYPE_RADIOBUTTONLIST = 'radioButtonList';
    const INPUT_TYPE_INLINERADIOBUTTONLIST = 'inlineRadioButtonList';
    const INPUT_TYPE_UNEDITABLE = 'uneditableField';
    const INPUT_TYPE_SEARCH = 'searchQuery';
    const INPUT_TYPE_HIDDEN = 'hidden';
    const INPUT_TYPE_CUSTOM = 'widget';

    // Input sizes are deprecated in BS3, use col-*-* instead.
    const INPUT_SIZE_MINI = 'mini';
    const INPUT_SIZE_SMALL = 'small';
    const INPUT_SIZE_DEFAULT = '';
    const INPUT_SIZE_MEDIUM = 'medium';
    const INPUT_SIZE_LARGE = 'large';
    const INPUT_SIZE_XLARGE = 'xlarge';
    const INPUT_SIZE_XXLARGE = 'xxlarge';

    const INPUT_HEIGHT_SMALL = 'sm';
    const INPUT_HEIGHT_DEFAULT = '';
    const INPUT_HEIGHT_LARGE = 'lg';

    const INPUT_COLOR_DEFAULT = '';
    const INPUT_COLOR_WARNING = 'has-warning';
    const INPUT_COLOR_ERROR = 'has-error';
    const INPUT_COLOR_SUCCESS = 'has-success';

    //
    // BUTTONS
    // --------------------------------------------------

    const BUTTON_TYPE_LINK = 'link';
    const BUTTON_TYPE_HTML = 'htmlButton';
    const BUTTON_TYPE_SUBMIT = 'submitButton';
    const BUTTON_TYPE_RESET = 'resetButton';
    const BUTTON_TYPE_IMAGE = 'imageButton';
    const BUTTON_TYPE_LINKBUTTON = 'linkButton';
    const BUTTON_TYPE_AJAXLINK = 'ajaxLink';
    const BUTTON_TYPE_AJAXBUTTON = 'ajaxButton';
    const BUTTON_TYPE_INPUTBUTTON = 'inputButton';
    const BUTTON_TYPE_INPUTSUBMIT = 'inputSubmit';

    const BUTTON_COLOR_DEFAULT = 'default';
    const BUTTON_COLOR_PRIMARY = 'primary';
    const BUTTON_COLOR_INFO = 'info';
    const BUTTON_COLOR_SUCCESS = 'success';
    const BUTTON_COLOR_WARNING = 'warning';
    const BUTTON_COLOR_DANGER = 'danger';
    // todo: remove this as it is deprecated in bs3
    const BUTTON_COLOR_INVERSE = 'inverse';
    const BUTTON_COLOR_LINK = 'link';

    const BUTTON_SIZE_MINI = 'xs'; // BS2 compatibility
    const BUTTON_SIZE_XS = 'xs';
    const BUTTON_SIZE_SMALL = 'sm'; // BS2 compatibility
    const BUTTON_SIZE_SM = 'sm';
    const BUTTON_SIZE_DEFAULT = 'default';
    const BUTTON_SIZE_LARGE = 'lg'; // BS2 compatibility
    const BUTTON_SIZE_LG = 'lg';


    const BUTTON_TOGGLE_CHECKBOX = 'checkbox';
    const BUTTON_TOGGLE_RADIO = 'radio';

    //
    // IMAGES
    // --------------------------------------------------

    const IMAGE_TYPE_ROUNDED = 'rounded';
    const IMAGE_TYPE_CIRCLE = 'circle';
    // todo: remove this as it is deprecated in bs3
    const IMAGE_TYPE_POLAROID = 'thumbnail';
    const IMAGE_TYPE_THUMBNAIL = 'thumbnail';

    //
    // NAV
    // --------------------------------------------------

    const NAV_TYPE_NONE = '';
    const NAV_TYPE_TABS = 'tabs';
    const NAV_TYPE_PILLS = 'pills';
    const NAV_TYPE_LIST = 'list';

    const TABS_PLACEMENT_ABOVE = '';
    // todo: remove this as it is deprecated in bs3
    const TABS_PLACEMENT_BELOW = 'below';
    const TABS_PLACEMENT_LEFT = 'left';
    const TABS_PLACEMENT_RIGHT = 'right';

    //
    // NAVBAR
    // --------------------------------------------------

    const NAVBAR_DISPLAY_NONE = '';
    const NAVBAR_DISPLAY_FIXEDTOP = 'fixed-top';
    const NAVBAR_DISPLAY_FIXEDBOTTOM = 'fixed-bottom';
    const NAVBAR_DISPLAY_STATICTOP = 'static-top';

    const NAVBAR_COLOR_INVERSE = 'inverse';

    //
    // PAGINATION
    // --------------------------------------------------

    const PAGINATION_SIZE_MINI = 'mini'; // deprecated, does not exist in BS3
    const PAGINATION_SIZE_SMALL = 'sm'; // deprecated, BS3 compatibility
    const PAGINATION_SIZE_SM = 'sm';
    const PAGINATION_SIZE_DEFAULT = '';
    const PAGINATION_SIZE_LARGE = 'lg'; // deprecated, BS3 compatibility
    const PAGINATION_SIZE_LG = 'lg';

    const PAGINATION_ALIGN_LEFT = 'left'; // deprecated in BS3?
    const PAGINATION_ALIGN_CENTER = 'centered'; // deprecated in BS3?
    const PAGINATION_ALIGN_RIGHT = 'right'; // deprecated in BS3?

    //
    // LABELS AND BADGES
    // --------------------------------------------------

    const LABEL_COLOR_DEFAULT = 'default';
    const LABEL_COLOR_PRIMARY = 'primary';
    const LABEL_COLOR_SUCCESS = 'success';
    const LABEL_COLOR_INFO = 'info';
    const LABEL_COLOR_WARNING = 'warning';
    const LABEL_COLOR_DANGER = 'danger';

    const BADGE_COLOR_DEFAULT = ''; // deprecated, only a single badge color in BS3
    const BADGE_COLOR_SUCCESS = 'success'; // deprecated, only a single badge color in BS3
    const BADGE_COLOR_WARNING = 'warning'; // deprecated, only a single badge color in BS3
    const BADGE_COLOR_IMPORTANT = 'important'; // deprecated, only a single badge color in BS3
    const BADGE_COLOR_INFO = 'info'; // deprecated, only a single badge color in BS3
    const BADGE_COLOR_INVERSE = 'inverse'; // deprecated, only a single badge color in BS3

    //
    // TOOLTIPS AND POPOVERS
    // --------------------------------------------------

    const TOOLTIP_PLACEMENT_TOP = 'top';
    const TOOLTIP_PLACEMENT_BOTTOM = 'bottom';
    const TOOLTIP_PLACEMENT_LEFT = 'left';
    const TOOLTIP_PLACEMENT_RIGHT = 'right';

    const TOOLTIP_TRIGGER_CLICK = 'click';
    const TOOLTIP_TRIGGER_HOVER = 'hover';
    const TOOLTIP_TRIGGER_FOCUS = 'focus';
    const TOOLTIP_TRIGGER_MANUAL = 'manual';

    const POPOVER_PLACEMENT_TOP = 'top';
    const POPOVER_PLACEMENT_BOTTOM = 'bottom';
    const POPOVER_PLACEMENT_LEFT = 'left';
    const POPOVER_PLACEMENT_RIGHT = 'right';

    const POPOVER_TRIGGER_CLICK = 'click';
    const POPOVER_TRIGGER_HOVER = 'hover';
    const POPOVER_TRIGGER_FOCUS = 'focus';
    const POPOVER_TRIGGER_MANUAL = 'manual';

    //
    // ALERT
    // --------------------------------------------------

    const ALERT_COLOR_DEFAULT = '';
    const ALERT_COLOR_INFO = 'info';
    const ALERT_COLOR_SUCCESS = 'success';
    const ALERT_COLOR_WARNING = 'warning';
    const ALERT_COLOR_DANGER = 'danger';

    //
    // PROGRESS BARS
    // --------------------------------------------------

    const PROGRESS_COLOR_DEFAULT = '';
    const PROGRESS_COLOR_INFO = 'info';
    const PROGRESS_COLOR_SUCCESS = 'success';
    const PROGRESS_COLOR_WARNING = 'warning';
    const PROGRESS_COLOR_DANGER = 'danger';

    //
    // MISC
    // --------------------------------------------------

    const WELL_SIZE_SMALL = 'small';
    const WELL_SIZE_DEFAULT = '';
    const WELL_SIZE_LARGE = 'large';

    const PULL_LEFT = 'left';
    const PULL_RIGHT = 'right';

    const CLOSE_DISMISS_ALERT = 'alert';
    const CLOSE_DISMISS_MODAL = 'modal';

    //
    // DETAIL VIEW
    // --------------------------------------------------

    const DETAIL_TYPE_STRIPED = 'striped';
    const DETAIL_TYPE_BORDERED = 'bordered';
    const DETAIL_TYPE_CONDENSED = 'condensed';
    const DETAIL_TYPE_HOVER = 'hover';

    //
    // GRID VIEW
    // --------------------------------------------------

    const GRID_TYPE_STRIPED = 'striped';
    const GRID_TYPE_BORDERED = 'bordered';
    const GRID_TYPE_CONDENSED = 'condensed';
    const GRID_TYPE_HOVER = 'hover';

    //
    // AFFIX
    // --------------------------------------------------

    const AFFIX_POSITION_TOP = 'top';
    const AFFIX_POSITION_BOTTOM = 'bottom';

	//
    // COLUMNS
    // --------------------------------------------------

    const COLUMN_SIZE_XS = 'xs';
    const COLUMN_SIZE_SM = 'sm';
    const COLUMN_SIZE_MD = 'md';
    const COLUMN_SIZE_LG = 'lg';
    // Verbose
    const COLUMN_SIZE_EXTRA_SMALL = 'xs';
    const COLUMN_SIZE_SMALL = 'sm';
    const COLUMN_SIZE_MEDIUM = 'md';
    const COLUMN_SIZE_LARGE = 'lg';

    //
    // MODAL
    // --------------------------------------------------

    const MODAL_SIZE_SMALL = ' modal-sm';
    const MODAL_SIZE_DEFAULT = '';
    const MODAL_SIZE_LARGE = ' modal-lg';

    //
    // ICON
    // --------------------------------------------------

    const ICON_COLOR_DEFAULT = '';
    const ICON_COLOR_WHITE = 'fa-white';

    const ICON_ADJUST = 'fa-adjust';
    const ICON_ALIGN_CENTER = 'fa-align-center';
    const ICON_ALIGN_JUSTIFY = 'fa-align-justify';
    const ICON_ALIGN_LEFT = 'fa-align-left';
    const ICON_ALIGN_RIGHT = 'fa-align-right';
    const ICON_ARROW_DOWN = 'fa-arrow-down';
    const ICON_ARROW_LEFT = 'fa-arrow-left';
    const ICON_ARROW_RIGHT = 'fa-arrow-right';
    const ICON_ARROW_UP = 'fa-arrow-up';
    const ICON_ASTERISK = 'fa-asterisk';
    const ICON_BACKWARD = 'fa-backward';
    const ICON_BAN_CIRCLE = 'fa-ban-circle';
    const ICON_BARCODE = 'fa-barcode';
    const ICON_BELL = 'fa-bell';
    const ICON_BOLD = 'fa-bold';
    const ICON_BOOK = 'fa-book';
    const ICON_BOOKMARK = 'fa-bookmark';
    const ICON_BRIEFCASE = 'fa-briefcase';
    const ICON_BULLHORN = 'fa-bullhorn';
    const ICON_CALENDAR = 'fa-calendar';
    const ICON_CAMERA = 'fa-camera';
    const ICON_CERTIFICATE = 'fa-certificate';
    const ICON_CHECK = 'fa-check';
    const ICON_CHEVRON_DOWN = 'fa-chevron-down';
    const ICON_CHEVRON_LEFT = 'fa-chevron-left';
    const ICON_CHEVRON_RIGHT = 'fa-chevron-right';
    const ICON_CHEVRON_UP = 'fa-chevron-up';
    const ICON_CIRCLE_ARROW_DOWN = 'fa-circle-arrow-down';
    const ICON_CIRCLE_ARROW_LEFT = 'fa-circle-arrow-left';
    const ICON_CIRCLE_ARROW_RIGHT = 'fa-circle-arrow-right';
    const ICON_CIRCLE_ARROW_UP = 'fa-circle-arrow-up';
    const ICON_CLOUD = 'fa-cloud';
    const ICON_CLOUD_DOWNLOAD = 'fa-cloud-download';
    const ICON_CLOUD_UPLOAD = 'fa-cloud-upload';
    const ICON_COG = 'fa-cog';
    const ICON_COLLAPSE_DOWN = 'fa-collapse-down';
    const ICON_COLLAPSE_UP = 'fa-collapse-up';
    const ICON_COMMENT = 'fa-comment';
    const ICON_COMPRESSED = 'fa-compressed';
    const ICON_COPYRIGHT_MARK = 'fa-copyright-mark';
    const ICON_CREDIT_CARD = 'fa-credit-card';
    const ICON_CUTLERY = 'fa-cutlery';
    const ICON_DASHBOARD = 'fa-dashboard';
    const ICON_DOWNLOAD = 'fa-download';
    const ICON_DOWNLOAD_ALT = 'fa-download-alt';
    const ICON_EARPHONE = 'fa-earphone';
    const ICON_EDIT = 'fa-edit';
    const ICON_EJECT = 'fa-eject';
    const ICON_ENVELOPE = 'fa-envelope';
    const ICON_EURO = 'fa-euro';
    const ICON_EXCLAMATION_SIGN = 'fa-exclamation-sign';
    const ICON_EXPAND = 'fa-expand';
    const ICON_EXPORT = 'fa-export';
    const ICON_EYE_CLOSE = 'fa-eye-close';
    const ICON_EYE_OPEN = 'fa-eye';
    const ICON_FACETIME_VIDEO = 'fa-facetime-video';
    const ICON_FAST_BACKWARD = 'fa-fast-backward';
    const ICON_FAST_FORWARD = 'fa-fast-forward';
    const ICON_FILE = 'fa-file';
    const ICON_FILM = 'fa-film';
    const ICON_FILTER = 'fa-filter';
    const ICON_FIRE = 'fa-fire';
    const ICON_FLAG = 'fa-flag';
    const ICON_FLASH = 'fa-flash';
    const ICON_FLOPPY_DISK = 'fa-floppy-disk';
    const ICON_FLOPPY_OPEN = 'fa-floppy-open';
    const ICON_FLOPPY_REMOVE = 'fa-floppy-remove';
    const ICON_FLOPPY_SAVE = 'fa-floppy-save';
    const ICON_FLOPPY_SAVED = 'fa-floppy-saved';
    const ICON_FOLDER_CLOSE = 'fa-folder-close';
    const ICON_FOLDER_OPEN = 'fa-folder-open';
    const ICON_FONT = 'fa-font';
    const ICON_FORWARD = 'fa-forward';
    const ICON_FULLSCREEN = 'fa-fullscreen';
    const ICON_GBP = 'fa-gbp';
    const ICON_GIFT = 'fa-gift';
    const ICON_GLASS = 'fa-glass';
    const ICON_GLOBE = 'fa-globe';
    const ICON_HAND_DOWN = 'fa-hand-down';
    const ICON_HAND_LEFT = 'fa-hand-left';
    const ICON_HAND_RIGHT = 'fa-hand-right';
    const ICON_HAND_UP = 'fa-hand-up';
    const ICON_HD_VIDEO = 'fa-hd-video';
    const ICON_HDD = 'fa-hdd';
    const ICON_HEADER = 'fa-header';
    const ICON_HEADPHONES = 'fa-headphones';
    const ICON_HEART = 'fa-heart';
    const ICON_HEART_EMPTY = 'fa-heart-empty';
    const ICON_HOME = 'fa-home';
    const ICON_IMPORT = 'fa-import';
    const ICON_INBOX = 'fa-inbox';
    const ICON_INDENT_LEFT = 'fa-indent-left';
    const ICON_INDENT_RIGHT = 'fa-indent-right';
    const ICON_INFO_SIGN = 'fa-info-sign';
    const ICON_ITALIC = 'fa-italic';
    const ICON_LEAF = 'fa-leaf';
    const ICON_LINK = 'fa-link';
    const ICON_LIST = 'fa-list';
    const ICON_LIST_ALT = 'fa-list-alt';
    const ICON_LOCK = 'fa-lock';
    const ICON_LOG_IN = 'fa-log-in';
    const ICON_LOG_OUT = 'fa-log-out';
    const ICON_MAGNET = 'fa-magnet';
    const ICON_MAP_MARKER = 'fa-map-marker';
    const ICON_MINUS = 'fa-minus';
    const ICON_MINUS_SIGN = 'fa-minus-sign';
    const ICON_MOVE = 'fa-bars bigIcons';
    const ICON_MUSIC = 'fa-music';
    const ICON_NEW_WINDOW = 'fa-new-window';
    const ICON_OFF = 'fa-off';
    const ICON_OK = 'fa-ok';
    const ICON_OK_CIRCLE = 'fa-ok-circle';
    const ICON_OK_SIGN = 'fa-ok-sign';
    const ICON_OPEN = 'fa-open';
    const ICON_PAPERCLIP = 'fa-paperclip';
    const ICON_PAUSE = 'fa-pause';
    const ICON_PENCIL = 'fa-pencil';
    const ICON_PHONE = 'fa-phone';
    const ICON_PHONE_ALT = 'fa-phone-alt';
    const ICON_PICTURE = 'fa-picture';
    const ICON_PLANE = 'fa-plane';
    const ICON_PLAY = 'fa-play';
    const ICON_PLAY_CIRCLE = 'fa-play-circle';
    const ICON_PLUS = 'fa-plus';
    const ICON_PLUS_SIGN = 'fa-plus-sign';
    const ICON_PRINT = 'fa-print';
    const ICON_PUSHPIN = 'fa-pushpin';
    const ICON_QRCODE = 'fa-qrcode';
    const ICON_QUESTION_SIGN = 'fa-question-sign';
    const ICON_RANDOM = 'fa-random';
    const ICON_RECORD = 'fa-record';
    const ICON_REFRESH = 'fa-refresh';
    const ICON_REGISTRATION_MARK = 'fa-registration-mark';
    const ICON_REMOVE = 'fa-remove';
    const ICON_REMOVE_CIRCLE = 'fa-remove-circle';
    const ICON_REMOVE_SIGN = 'fa-remove-sign';
    const ICON_REPEAT = 'fa-repeat';
    const ICON_RESIZE_FULL = 'fa-resize-full';
    const ICON_RESIZE_HORIZONTAL = 'fa-resize-horizontal';
    const ICON_RESIZE_SMALL = 'fa-resize-small';
    const ICON_RESIZE_VERTICAL = 'fa-resize-vertical';
    const ICON_RETWEET = 'fa-retweet';
    const ICON_ROAD = 'fa-road';
    const ICON_SAVE = 'fa-save';
    const ICON_SAVED = 'fa-saved';
    const ICON_SCREENSHOT = 'fa-screenshot';
    const ICON_SD_VIDEO = 'fa-sd-video';
    const ICON_SEARCH = 'fa-search';
    const ICON_SEND = 'fa-send';
    const ICON_SHARE = 'fa-share';
    const ICON_SHARE_ALT = 'fa-share-alt';
    const ICON_SHOPPING_CART = 'fa-shopping-cart';
    const ICON_SIGNAL = 'fa-signal';
    const ICON_SORT = 'fa-sort';
    const ICON_SORT_BY_ALPHABET = 'fa-sort-by-alphabet';
    const ICON_SORT_BY_ALPHABET_ALT = 'fa-sort-by-alphabet-alt';
    const ICON_SORT_BY_ATTRIBUTES = 'fa-sort-by-attributes';
    const ICON_SORT_BY_ATTRIBUTES_ALT = 'fa-sort-by-attributes-alt';
    const ICON_SORT_BY_ORDER = 'fa-sort-by-order';
    const ICON_SORT_BY_ORDER_ALT = 'fa-sort-by-order-alt';
    const ICON_SOUND_5_1 = 'fa-sound-5-1';
    const ICON_SOUND_6_1 = 'fa-sound-6-1';
    const ICON_SOUND_7_1 = 'fa-sound-7-1';
    const ICON_SOUND_DOLBY = 'fa-sound-dolby';
    const ICON_SOUND_STEREO = 'fa-sound-stereo';
    const ICON_STAR = 'fa-star';
    const ICON_STAR_EMPTY = 'fa-star-empty';
    const ICON_STATS = 'fa-bar-chart';
    const ICON_STEP_BACKWARD = 'fa-step-backward';
    const ICON_STEP_FORWARD = 'fa-step-forward';
    const ICON_STOP = 'fa-stop';
    const ICON_SUBTITLES = 'fa-subtitles';
    const ICON_TAG = 'fa-tag';
    const ICON_TAGS = 'fa-tags';
    const ICON_TASKS = 'fa-tasks';
    const ICON_TEXT_HEIGHT = 'fa-text-height';
    const ICON_TEXT_WIDTH = 'fa-text-width';
    const ICON_TH = 'fa-th';
    const ICON_TH_LARGE = 'fa-th-large';
    const ICON_TH_LIST = 'fa-th-list';
    const ICON_THUMBS_DOWN = 'fa-thumbs-down';
    const ICON_THUMBS_UP = 'fa-thumbs-up';
    const ICON_TIME = 'fa-time';
    const ICON_TINT = 'fa-tint';
    const ICON_TOWER = 'fa-tower';
    const ICON_TRANSFER = 'fa-transfer';
    const ICON_TRASH = 'fa-trash';
    const ICON_TREE_CONIFER = 'fa-tree-conifer';
    const ICON_TREE_DECIDUOUS = 'fa-tree-deciduous';
    const ICON_UNCHECKED = 'fa-unchecked';
    const ICON_UPLOAD = 'fa-upload';
    const ICON_USD = 'fa-usd';
    const ICON_USER = 'fa-user';
    const ICON_VOLUME_DOWN = 'fa-volume-down';
    const ICON_VOLUME_OFF = 'fa-volume-off';
    const ICON_VOLUME_UP = 'fa-volume-up';
    const ICON_WARNING_SIGN = 'fa-warning-sign';
    const ICON_WRENCH = 'fa-wrench';
    const ICON_ZOOM_IN = 'fa-zoom-in';
    const ICON_ZOOM_OUT = 'fa-zoom-out';

    // Default close text.
    const CLOSE_TEXT = '';

    /**
     * @var string the CSS class for displaying error summaries.
     */
    public static $errorSummaryCss = 'alert alert-block alert-danger';
    /**
     * @var string the CSS class for displaying error inputs
     */
    public static $errorCss = 'has-error';
    /**
     * @var string the icon vendor
     */
    public static $iconVendor = 'fa';
    /**
     * @var string default form label width
     */
    // todo: remove this.
    protected static $defaultFormLabelWidthClass = 'col-sm-2';
    /**
     * @var string default form control width
     */
    // todo: remove this.
    protected static $defaultFormControlWidthClass = 'col-sm-10';

    //
    // BASE CSS
    // --------------------------------------------------

    // Typography
    // http://getbootstrap.com/css/#type
    // --------------------------------------------------

    /**
     * Generates a paragraph that stands out.
     * @param string $text the lead text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated paragraph.
     */
    public static function lead($text, $htmlOptions = array())
    {
        self::addCssClass('lead', $htmlOptions);
        return self::tag('p', $htmlOptions, $text);
    }

    /**
     * Generates small text.
     * @param string $text the text to style.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated text.
     */
    public static function small($text, $htmlOptions = array())
    {
        return self::tag('small', $htmlOptions, $text);
    }

    /**
     * Generates bold text.
     * @param string $text the text to style.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated text.
     */
    public static function b($text, $htmlOptions = array())
    {
        return self::tag('strong', $htmlOptions, $text);
    }

    /**
     * Generates italic text.
     * @param string $text the text to style.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated text.
     */
    public static function i($text, $htmlOptions = array())
    {
        return self::tag('em', $htmlOptions, $text);
    }

    /**
     * Generates an emphasized text.
     * @param string $text the text to emphasize.
     * @param array $htmlOptions additional HTML attributes.
     * @param string $tag the HTML tag.
     * @return string the generated text.
     */
    public static function em($text, $htmlOptions = array(), $tag = 'p')
    {
        $color = TbArray::popValue('color', $htmlOptions);
        if (TbArray::popValue('muted', $htmlOptions, false)) {
            self::addCssClass('muted', $htmlOptions);
        } else {
            if (!empty($color)) {
                self::addCssClass('text-' . $color, $htmlOptions);
            }
        }
        return self::tag($tag, $htmlOptions, $text);
    }

    /**
     * Generates a muted text block.
     * @param string $text the text.
     * @param array $htmlOptions additional HTML attributes.
     * @param string $tag the HTML tag.
     * @return string the generated text block.
     */
    public static function muted($text, $htmlOptions = array(), $tag = 'p')
    {
        $htmlOptions['muted'] = true;
        return self::em($text, $htmlOptions, $tag);
    }

    /**
     * Generates a muted span.
     * @param string $text the text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated span.
     */
    public static function mutedSpan($text, $htmlOptions = array())
    {
        return self::muted($text, $htmlOptions, 'span');
    }

    /**
     * Generates an abbreviation with a help text.
     * @param string $text the abbreviation.
     * @param string $word the word the abbreviation is for.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated abbreviation.
     */
    public static function abbr($text, $word, $htmlOptions = array())
    {
        if (TbArray::popValue('small', $htmlOptions, false)) {
            self::addCssClass('initialism', $htmlOptions);
        }
        $htmlOptions['title'] = $word;
        return self::tag('abbr', $htmlOptions, $text);
    }

    /**
     * Generates a small abbreviation with a help text.
     * @param string $text the abbreviation.
     * @param string $word the word the abbreviation is for.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated abbreviation.
     */
    public static function smallAbbr($text, $word, $htmlOptions = array())
    {
        $htmlOptions['small'] = true;
        return self::abbr($text, $word, $htmlOptions);
    }

    /**
     * Generates an address block.
     * @param $text
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated block.
     */
    public static function address($text, $htmlOptions = array())
    {
        return self::tag('address', $htmlOptions, $text);
    }

    /**
     * Generates a quote.
     * @param string $text the quoted text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated quote.
     */
    public static function quote($text, $htmlOptions = array())
    {
        $paragraphOptions = TbArray::popValue('paragraphOptions', $htmlOptions, array());
        $source = TbArray::popValue('source', $htmlOptions);
        $sourceOptions = TbArray::popValue('sourceOptions', $htmlOptions, array());
        $cite = TbArray::popValue('cite', $htmlOptions);
        $citeOptions = TbArray::popValue('citeOptions', $htmlOptions, array());
        $cite = isset($cite) ? ' ' . self::tag('cite', $citeOptions, $cite) : '';
        $source = isset($source) ? self::tag('small', $sourceOptions, $source . $cite) : '';
        $text = self::tag('p', $paragraphOptions, $text) . $source;
        return self::tag('blockquote', $htmlOptions, $text);
    }

    /**
     * Generates a help text.
     * @param string $text the help text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated text.
     */
    public static function help($text, $htmlOptions = array())
    {
        $type = TbArray::popValue('type', $htmlOptions, self::HELP_TYPE_BLOCK);
        self::addCssClass('help-' . self::HELP_TYPE_BLOCK, $htmlOptions);
        return self::tag($type === self::HELP_TYPE_INLINE ? 'span' : 'p', $htmlOptions, $text);
    }

    /**
     * Generates a help block.
     * @param string $text the help text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated block.
     * @deprecated
     */
    public static function helpBlock($text, $htmlOptions = array())
    {
        // todo: remove this as it is no longer valid for bs3
        $htmlOptions['type'] = self::HELP_TYPE_BLOCK;
        return self::help($text, $htmlOptions);
    }

    // Code
    // http://getbootstrap.com/css/#code
    // --------------------------------------------------

    /**
     * Generates inline code.
     * @param string $code the code.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated code.
     */
    public static function code($code, $htmlOptions = array())
    {
        return self::tag('code', $htmlOptions, self::encode($code));
    }

    /**
     * Generates a code block.
     * @param string $code the code.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated block.
     */
    public static function codeBlock($code, $htmlOptions = array())
    {
        return self::tag('pre', $htmlOptions, self::encode($code));
    }

    /**
     * Generates an HTML element.
     * @param string $tag the tag name.
     * @param array $htmlOptions the element attributes.
     * @param mixed $content the content to be enclosed between open and close element tags.
     * @param boolean $closeTag whether to generate the close tag.
     * @return string the generated HTML element tag.
     */
    public static function tag($tag, $htmlOptions = array(), $content = false, $closeTag = true)
    {
        self::addSpanClass($htmlOptions);
        self::addColClass($htmlOptions);
        self::addPullClass($htmlOptions);
        self::addTextAlignClass($htmlOptions);
        return parent::tag($tag, $htmlOptions, $content, $closeTag);
    }

    /**
     * Generates an open HTML element.
     * @param string $tag the tag name.
     * @param array $htmlOptions the element attributes.
     * @return string the generated HTML element tag.
     */
    public static function openTag($tag, $htmlOptions = array())
    {
        return self::tag($tag, $htmlOptions, false, false);
    }

    // Tables
    // http://getbootstrap.com/css/#tables
    // --------------------------------------------------

    // todo: create table methods here.

    // Forms
    // http://getbootstrap.com/css/#forms
    // --------------------------------------------------

    /**
     * Generates a form tag.
     * @param string $layout the form layout.
     * @param string $action the form action URL.
     * @param string $method form method (e.g. post, get).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated tag.
     */
    public static function formTb(
        $layout = self::FORM_LAYOUT_VERTICAL,
        $action = '',
        $method = 'post',
        $htmlOptions = array()
    ) {
        return self::beginFormTb($layout, $action, $method, $htmlOptions);
    }

    /**
     * Generates an open form tag.
     * @param string $layout the form layout.
     * @param string $action the form action URL.
     * @param string $method form method (e.g. post, get).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated tag.
     */
    public static function beginFormTb(
        $layout = self::FORM_LAYOUT_VERTICAL,
        $action = '',
        $method = 'post',
        $htmlOptions = array()
    ) {
        if (!empty($layout)) {
            // refactor to not use a switch
            switch ($layout) {
                case self::FORM_LAYOUT_HORIZONTAL:
                    self::addCssClass('form-' . self::FORM_LAYOUT_HORIZONTAL, $htmlOptions);
                    break;
                case self::FORM_LAYOUT_INLINE:
                case self::FORM_LAYOUT_SEARCH:
                    self::addCssClass('form-' . self::FORM_LAYOUT_INLINE, $htmlOptions);
                    break;
                default:
                    self::addCssClass('form-' . $layout, $htmlOptions);
            }
        }
        return parent::beginForm($action, $method, $htmlOptions);
    }

    /**
     * Generates a stateful form tag.
     * @param string string $layout
     * @param mixed $action the form action URL.
     * @param string $method form method (e.g. post, get).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated form tag.
     */
    public static function statefulFormTb(
        $layout = self::FORM_LAYOUT_VERTICAL,
        $action = '',
        $method = 'post',
        $htmlOptions = array()
    ) {
        return self::formTb($layout, $action, $method, $htmlOptions)
        . self::tag('div', array('style' => 'display: none'), parent::pageStateField(''));
    }

    /**
     * Generates a text field input.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field.
     * @see self::textInputField
     */
    public static function textField($name, $value = '', $htmlOptions = array())
    {
        return self::textInputField('text', $name, $value, $htmlOptions);
    }

    /**
     * Generates a password field input.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field.
     * @see self::textInputField
     */
    public static function passwordField($name, $value = '', $htmlOptions = array())
    {
        return self::textInputField('password', $name, $value, $htmlOptions);
    }

    /**
     * Generates an url field input.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field.
     * @see self::textInputField
     */
    public static function urlField($name, $value = '', $htmlOptions = array())
    {
        return self::textInputField('url', $name, $value, $htmlOptions);
    }

    /**
     * Generates an email field input.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field.
     * @see self::textInputField
     */
    public static function emailField($name, $value = '', $htmlOptions = array())
    {
        return self::textInputField('email', $name, $value, $htmlOptions);
    }

    /**
     * Generates a number field input.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field.
     * @see self::textInputField
     */
    public static function numberField($name, $value = '', $htmlOptions = array())
    {
        return self::textInputField('number', $name, $value, $htmlOptions);
    }

    /**
     * Generates a range field input.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field.
     * @see self::textInputField
     */
    public static function rangeField($name, $value = '', $htmlOptions = array())
    {
        return self::textInputField('range', $name, $value, $htmlOptions);
    }

    /**
     * Generates a date field input.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field.
     * @see self::textInputField
     */
    public static function dateField($name, $value = '', $htmlOptions = array())
    {
        return self::textInputField('date', $name, $value, $htmlOptions);
    }

    /**
     * Generates a file field input.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field.
     * @see CHtml::fileField
     */
    public static function fileField($name, $value = '', $htmlOptions = array())
    {
        return parent::fileField($name, $value, $htmlOptions);
    }

    /**
     * Generates a text area input.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated text area.
     */
    public static function textArea($name, $value = '', $htmlOptions = array())
    {
        // In case we do need to create a div container for the text area
        $containerOptions = array();

        // Get the intended input width before the rest of the options are normalized
        self::addSpanClass($htmlOptions);
        self::addColClass($htmlOptions);
        $col = self::popColClasses($htmlOptions);

        $htmlOptions = self::normalizeInputOptions($htmlOptions);
        self::addCssClass('form-control', $htmlOptions);

        $output = '';
        if (!empty($col)) {
            self::addCssClass($col, $containerOptions);
            $output .= self::openTag('div', $containerOptions);
        }
        $output .= parent::textArea($name, $value, $htmlOptions);
        if (!empty($col)) {
            $output .= '</div>';
        }
        return $output;
    }

    /**
     * Generates a radio button.
     * @param string $name the input name.
     * @param boolean $checked whether the radio button is checked.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated radio button.
     */
    public static function radioButton($name, $checked = false, $htmlOptions = array())
    {
        $label = TbArray::popValue('label', $htmlOptions, false);
        $labelOptions = TbArray::popValue('labelOptions', $htmlOptions, array());
        $input = parent::radioButton($name, $checked, $htmlOptions);
        // todo: refactor to make a single call to createCheckBoxAndRadioButtonLabel
        if (TbArray::popValue('useContainer', $htmlOptions, false)) {
            return self::tag(
                'div',
                array('class' => 'radio'),
                self::createCheckBoxAndRadioButtonLabel($label, $input, $labelOptions)
            );
        } else {
            return self::createCheckBoxAndRadioButtonLabel($label, $input, $labelOptions);
        }
    }

    /**
     * Generates a check box.
     * @param string $name the input name.
     * @param boolean $checked whether the check box is checked.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated check box.
     */
    public static function checkBox($name, $checked = false, $htmlOptions = array())
    {
        $label = TbArray::popValue('label', $htmlOptions, false);
        $labelOptions = TbArray::popValue('labelOptions', $htmlOptions, array());
        $input = parent::checkBox($name, $checked, $htmlOptions);
        // todo: refactor to make a single call to createCheckBoxAndRadioButtonLabel
        if (TbArray::popValue('useContainer', $htmlOptions, false)) {
            return self::tag(
                'div',
                array('class' => 'checkbox'),
                self::createCheckBoxAndRadioButtonLabel($label, $input, $labelOptions)
            );
        } else {
            return self::createCheckBoxAndRadioButtonLabel($label, $input, $labelOptions);
        }
    }

    /**
     * Generates a drop down list.
     * @param string $name the input name.
     * @param string $select the selected value.
     * @param array $data data for generating the list options (value=>display).
     * @param array $htmlOptions
     * @return string the generated drop down list.
     */
    public static function dropDownList($name, $select, $data, $htmlOptions = array())
    {
        $displaySize = TbArray::popValue('displaySize', $htmlOptions);

        // In case we do need to create a div container for the input element (i.e. has addon or defined col)
        $containerOptions = array();

        // Get the intended input width before the rest of the options are normalized
        self::addSpanClass($htmlOptions);
        self::addColClass($htmlOptions);
        $col = self::popColClasses($htmlOptions);

        $htmlOptions = self::normalizeInputOptions($htmlOptions);
        self::addCssClass('form-control', $htmlOptions);
        if (!empty($displaySize)) {
            $htmlOptions['size'] = $displaySize;
        }

        if (!empty($col)) {
            self::addCssClass($col, $containerOptions);
        }

        $output = '';

        if (!empty($containerOptions)) {
            $output .= self::openTag('div', $containerOptions);
        }
        $output .= parent::dropDownList($name, $select, $data, $htmlOptions);

        if (!empty($containerOptions)) {
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Generates a list box.
     * @param string $name the input name.
     * @param mixed $select the selected value(s).
     * @param array $data data for generating the list options (value=>display).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated list box
     */
    public static function listBox($name, $select, $data, $htmlOptions = array())
    {
        if (isset($htmlOptions['multiple'])) {
            if (substr($name, -2) !== '[]') {
                $name .= '[]';
            }
        }
        TbArray::defaultValue('displaySize', 4, $htmlOptions);
        return self::dropDownList($name, $select, $data, $htmlOptions);
    }

    /**
     * Generates a radio button list.
     * @param string $name name of the radio button list.
     * @param mixed $select selection of the radio buttons.
     * @param array $data $data value-label pairs used to generate the radio button list.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated list.
     */
    public static function radioButtonList($name, $select, $data, $htmlOptions = array())
    {
        $inline = TbArray::popValue('inline', $htmlOptions, false);
        $separator = TbArray::popValue('separator', $htmlOptions, ' ');
        $container = TbArray::popValue('container', $htmlOptions, 'div');
        $containerOptions = TbArray::popValue('containerOptions', $htmlOptions, array());
        $labelOptions = TbArray::popValue('labelOptions', $htmlOptions, array());
        $empty = TbArray::popValue('empty', $htmlOptions);
        if (isset($empty)) {
            $empty = !is_array($empty) ? array('' => $empty) : $empty;
            $data = TbArray::merge($empty, $data);
        }

        $items = array();
        $baseID = $containerOptions['id'] = TbArray::popValue('baseID', $htmlOptions, parent::getIdByName($name));

        $id = 0;
        foreach ($data as $value => $label) {
            $checked = !strcmp((string) $value, (string) $select);
            $htmlOptions['value'] = $value;
            $htmlOptions['id'] = $baseID . '_' . $id++;
            if ($inline) {
                $htmlOptions['label'] = $label;
                self::addCssClass('radio-inline', $labelOptions);
                $htmlOptions['labelOptions'] = $labelOptions;
                $items[] = self::radioButton($name, $checked, $htmlOptions);
            } else {
                $option = self::radioButton($name, $checked, $htmlOptions);
                $items[] = self::tag(
                    'div',
                    array('class' => 'radio'),
                    self::label($option . ' ' . $label, false, $labelOptions)
                );
            }
        }

        $inputs = implode($separator, $items);
        return !empty($container) ? self::tag($container, $containerOptions, $inputs) : $inputs;
    }

    /**
     * Generates an inline radio button list.
     * @param string $name name of the radio button list.
     * @param mixed $select selection of the radio buttons.
     * @param array $data $data value-label pairs used to generate the radio button list.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated list.
     */
    public static function inlineRadioButtonList($name, $select, $data, $htmlOptions = array())
    {
        $htmlOptions['inline'] = true;
        return self::radioButtonList($name, $select, $data, $htmlOptions);
    }

    /**
     * Generates a check box list.
     * @param string $name name of the check box list.
     * @param mixed $select selection of the check boxes.
     * @param array $data $data value-label pairs used to generate the check box list.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated list.
     */
    public static function checkBoxList($name, $select, $data, $htmlOptions = array())
    {
        $inline = TbArray::popValue('inline', $htmlOptions, false);
        $separator = TbArray::popValue('separator', $htmlOptions, ' ');
        $container = TbArray::popValue('container', $htmlOptions, 'div');
        $containerOptions = TbArray::popValue('containerOptions', $htmlOptions, array());

        $labelOptions = TbArray::popValue('labelOptions', $htmlOptions, array());

        if (substr($name, -2) !== '[]') {
            $name .= '[]';
        }

        $checkAll = TbArray::popValue('checkAll', $htmlOptions);
        $checkAllLast = TbArray::popValue('checkAllLast', $htmlOptions);
        if ($checkAll !== null) {
            $checkAllLabel = $checkAll;
            $checkAllLast = $checkAllLast !== null;
        }

        $items = array();
        $baseID = $containerOptions['id'] = TbArray::popValue('baseID', $htmlOptions, parent::getIdByName($name));
        $id = 0;
        $checkAll = true;

        foreach ($data as $value => $label) {
            $checked = !is_array($select) && !strcmp($value, (string) $select) || is_array($select) && in_array($value, $select);
            $checkAll = $checkAll && $checked;
            $htmlOptions['value'] = $value;
            $htmlOptions['id'] = $baseID . '_' . $id++;
            if ($inline) {
                $htmlOptions['label'] = $label;
                self::addCssClass('checkbox-inline', $labelOptions);
                $htmlOptions['labelOptions'] = $labelOptions;
                $items[] = self::checkBox($name, $checked, $htmlOptions);
            } else {
                $option = self::checkBox($name, $checked, $htmlOptions);
                $items[] = self::tag(
                    'div',
                    array('class' => 'checkbox'),
                    self::label($option . ' ' . $label, false, $labelOptions)
                );
            }
        }

        if (isset($checkAllLabel)) {
            $htmlOptions['value'] = 1;
            $htmlOptions['id'] = $id = $baseID . '_all';
            $htmlOptions['label'] = $checkAllLabel;
            $htmlOptions['labelOptions'] = $labelOptions;
            $item = self::checkBox($id, $checkAll, $htmlOptions);
            if ($inline) {
                self::addCssClass('checkbox-inline', $labelOptions);
            } else {
                $item = self::checkBox($id, $checkAll, $htmlOptions);
                $item = self::tag(
                    'div',
                    array('class' => 'checkbox'),
                    $item
                );
            }
            if ($checkAllLast) {
                $items[] = $item;
            } else {
                array_unshift($items, $item);
            }
            $name = strtr($name, array('[' => '\\[', ']' => '\\]'));
            $js = <<<EOD
jQuery('#$id').on('click', function() {
	jQuery("input[name='$name']").prop('checked', this.checked);
});
jQuery("input[name='$name']").on('click', function() {
	jQuery('#$id').prop('checked', !jQuery("input[name='$name']:not(:checked)").length);
});
jQuery('#$id').prop('checked', !jQuery("input[name='$name']:not(:checked)").length);
EOD;
            $cs = Yii::app()->getClientScript();
            $cs->registerCoreScript('jquery');
            $cs->registerScript($id, $js);
        }

        $inputs = implode($separator, $items);
        return !empty($container) ? self::tag($container, $containerOptions, $inputs) : $inputs;
    }

    /**
     * Generates an inline check box list.
     * @param string $name name of the check box list.
     * @param mixed $select selection of the check boxes.
     * @param array $data $data value-label pairs used to generate the check box list.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated list.
     */
    public static function inlineCheckBoxList($name, $select, $data, $htmlOptions = array())
    {
        $htmlOptions['inline'] = true;
        return self::checkBoxList($name, $select, $data, $htmlOptions);
    }

    /**
     * Generates an uneditable input.
     * @param string $value the value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input.
     */
    public static function uneditableField($value, $htmlOptions = array())
    {
        self::addCssClass('uneditable-input', $htmlOptions);
        $htmlOptions = self::normalizeInputOptions($htmlOptions);
        return self::tag('span', $htmlOptions, $value);
    }

    /**
     * Generates a search input.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input.
     */
    public static function searchQueryField($name, $value = '', $htmlOptions = array())
    {
        return self::textInputField('search', $name, $value, $htmlOptions);
    }

    /**
     * Generates a control group with a text field.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function textFieldControlGroup($name, $value = '', $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_TEXT, $name, $value, $htmlOptions);
    }

    /**
     * Generates a control group with a password field.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::textInputField
     */
    public static function passwordFieldControlGroup($name, $value = '', $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_PASSWORD, $name, $value, $htmlOptions);
    }

    /**
     * Generates a control group with an url field.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function urlFieldControlGroup($name, $value = '', $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_URL, $name, $value, $htmlOptions);
    }

    /**
     * Generates a control group with an email field.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function emailFieldControlGroup($name, $value = '', $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_EMAIL, $name, $value, $htmlOptions);
    }

    /**
     * Generates a control group with a number field.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::textInputField
     */
    public static function numberFieldControlGroup($name, $value = '', $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_NUMBER, $name, $value, $htmlOptions);
    }

    /**
     * Generates a control group with a range field.
     * @param string $name the input name
     * @param string $value the input value
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function rangeFieldControlGroup($name, $value = '', $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_RANGE, $name, $value, $htmlOptions);
    }

    /**
     * Generates a control group with a file field.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function dateFieldControlGroup($name, $value = '', $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_DATE, $name, $value, $htmlOptions);
    }

    /**
     * Generates a control group with a text area.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function textAreaControlGroup($name, $value = '', $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_TEXTAREA, $name, $value, $htmlOptions);
    }

    /**
     * Generates a control group with a file field.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function fileFieldControlGroup($name, $value = '', $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_FILE, $name, $value, $htmlOptions);
    }

    /**
     * Generates a control group with a radio button.
     * @param string $name the input name.
     * @param bool|string $checked whether the radio button is checked.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function radioButtonControlGroup($name, $checked = false, $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_RADIOBUTTON, $name, $checked, $htmlOptions);
    }

    /**
     * Generates a control group with a check box.
     * @param string $name the input name.
     * @param bool|string $checked whether the check box is checked.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function checkBoxControlGroup($name, $checked = false, $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_CHECKBOX, $name, $checked, $htmlOptions);
    }

    /**
     * Generates a control group with a drop down list.
     * @param string $name the input name.
     * @param string $select the selected value.
     * @param array $data data for generating the list options (value=>display).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function dropDownListControlGroup($name, $select = '', $data = array(), $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_DROPDOWNLIST, $name, $select, $htmlOptions, $data);
    }

    /**
     * Generates a control group with a list box.
     * @param string $name the input name.
     * @param string $select the selected value.
     * @param array $data data for generating the list options (value=>display).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function listBoxControlGroup($name, $select = '', $data = array(), $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_LISTBOX, $name, $select, $htmlOptions, $data);
    }

    /**
     * Generates a control group with a radio button list.
     * @param string $name the input name.
     * @param string $select the selected value.
     * @param array $data data for generating the list options (value=>display).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function radioButtonListControlGroup($name, $select = '', $data = array(), $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_RADIOBUTTONLIST, $name, $select, $htmlOptions, $data);
    }

    /**
     * Generates a control group with an inline radio button list.
     * @param string $name the input name.
     * @param string $select the selected value.
     * @param array $data data for generating the list options (value=>display).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function inlineRadioButtonListControlGroup(
        $name,
        $select = '',
        $data = array(),
        $htmlOptions = array()
    ) {
        return self::controlGroup(self::INPUT_TYPE_INLINERADIOBUTTONLIST, $name, $select, $htmlOptions, $data);
    }

    /**
     * Generates a control group with a check box list.
     * @param string $name the input name.
     * @param string $select the selected value.
     * @param array $data data for generating the list options (value=>display).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function checkBoxListControlGroup($name, $select = '', $data = array(), $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_CHECKBOXLIST, $name, $select, $htmlOptions, $data);
    }

    /**
     * Generates a control group with an inline check box list.
     * @param string $name the input name.
     * @param string $select the selected value.
     * @param array $data data for generating the list options (value=>display).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function inlineCheckBoxListControlGroup($name, $select = '', $data = array(), $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_INLINECHECKBOXLIST, $name, $select, $htmlOptions, $data);
    }

    /**
     * Generates a control group with an uneditable field.
     * @param string $value
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function uneditableFieldControlGroup($value = '', $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_UNEDITABLE, '', $value, $htmlOptions);
    }

    /**
     * Generates a control group with a search field.
     * @param string $name the input name.
     * @param string $value
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::controlGroup
     */
    public static function searchQueryControlGroup($name, $value = '', $htmlOptions = array())
    {
        return self::controlGroup(self::INPUT_TYPE_SEARCH, $name, $value, $htmlOptions);
    }

    /**
     * Generates a form group.
     * @param string $type the input type.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @param array $data data for multiple select inputs.
     * @return string the generated control group.
     */
    public static function controlGroup($type, $name, $value = '', $htmlOptions = array(), $data = array())
    {
        $color = TbArray::popValue('color', $htmlOptions);
        $groupOptions = TbArray::popValue('groupOptions', $htmlOptions, array());
        $controlOptions = TbArray::popValue('controlOptions', $htmlOptions, array());
        $label = TbArray::popValue('label', $htmlOptions);
        $labelOptions = TbArray::popValue('labelOptions', $htmlOptions, array());

        // todo: remove everything that has to do with form layouts.
        $formLayout = TbArray::popValue('formLayout', $htmlOptions, self::FORM_LAYOUT_VERTICAL);
        $labelWidthClass = TbArray::popValue('labelWidthClass', $htmlOptions, self::$defaultFormLabelWidthClass);
        // Retrieve the old-style "span" option
        $span = TbArray::popValue('span', $htmlOptions);
        if (!empty($span)) {
            $controlWidthClass = 'col-md-' . $span;
        } else {
            $controlWidthClass = TbArray::popValue('controlWidthClass', $htmlOptions, self::$defaultFormControlWidthClass);
        }
        $useFormGroup = true;
        $useControls = true;
        $output = '';

        // Special label case case for individual checkboxes and radios
        if ($type == self::INPUT_TYPE_CHECKBOX || $type == self::INPUT_TYPE_RADIOBUTTON) {
            $htmlOptions['label'] = $label;
            $htmlOptions['labelOptions'] = $labelOptions;
            $htmlOptions['useContainer'] = true;
            $label = false;
            $useFormGroup = false;
        }

        // Special conditions depending on the form type
        if ($formLayout == self::FORM_LAYOUT_HORIZONTAL) {
            switch ($type) {
                case self::INPUT_TYPE_CHECKBOX:
                case self::INPUT_TYPE_RADIOBUTTON:
                    self::addCssClass(self::switchColToOffset($labelWidthClass), $controlOptions);
                    self::addCssClass(self::switchOffsetToCol($controlWidthClass), $controlOptions);
                    $useFormGroup = true;
                    break;
                default:
                    self::addCssClass(self::switchOffsetToCol($labelWidthClass), $labelOptions);
                    self::addCssClass(self::switchOffsetToCol($controlWidthClass), $controlOptions);
            }
        } elseif ($formLayout == self::FORM_LAYOUT_INLINE || $formLayout == self::FORM_LAYOUT_SEARCH) {
            switch ($type) {
                case self::INPUT_TYPE_TEXT:
                case self::INPUT_TYPE_PASSWORD:
                case self::INPUT_TYPE_URL:
                case self::INPUT_TYPE_EMAIL:
                case self::INPUT_TYPE_NUMBER:
                case self::INPUT_TYPE_RANGE:
                case self::INPUT_TYPE_DATE:
                case self::INPUT_TYPE_FILE:
                case self::INPUT_TYPE_SEARCH:
                    self::addCssClass('sr-only', $labelOptions);
                    if (($label !== null) && (TbArray::getValue('placeholder', $htmlOptions) !== null)) {
                        $htmlOptions['placeholder'] = $label;
                    }
                    break;
                case self::INPUT_TYPE_CHECKBOX:
                case self::INPUT_TYPE_RADIOBUTTON:
                    $useControls = false;
                    break;
            }
        }
        // remove until here.

        $help = TbArray::popValue('help', $htmlOptions, '');
        $helpOptions = TbArray::popValue('helpOptions', $htmlOptions, array());
        if (!empty($help)) {
            $help = self::inputHelp($help, $helpOptions);
        }

        $input = isset($htmlOptions['input'])
            ? $htmlOptions['input']
            : self::createInput($type, $name, $value, $htmlOptions, $data);

        if (!empty($color)) {
            self::addCssClass($color, $groupOptions);
        }
        self::addCssClass('form-label', $labelOptions);
        if ($label !== false) {
            $output .= parent::label($label, $name, $labelOptions);
        }
        if ($useControls) {
            $output .= self::controls($input . $help, $controlOptions);
        } else {
            $output .= $input;
        }

        if ($useFormGroup) {
            self::addCssClass('form-group', $groupOptions);
            return self::tag(
                'div',
                $groupOptions,
                $output
            );
        } else {
            return $output;
        }
    }

    /**
     * Generates a custom (pre-rendered) form control group.
     * @param string $input the rendered input.
     * @param string $name the input name.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     */
    public static function customControlGroup($input, $name, $htmlOptions = array())
    {
        $htmlOptions['input'] = $input;
        return self::controlGroup(self::INPUT_TYPE_CUSTOM, $name, '', $htmlOptions);
    }

    /**
     * Creates a form input of the given type.
     * @param string $type the input type.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @param array $data data for multiple select inputs.
     * @return string the input.
     * @throws CException if the input type is invalid.
     */
    public static function createInput($type, $name, $value, $htmlOptions = array(), $data = array())
    {
        switch ($type) {
            case self::INPUT_TYPE_TEXT:
                return self::textField($name, $value, $htmlOptions);
            case self::INPUT_TYPE_PASSWORD:
                return self::passwordField($name, $value, $htmlOptions);
            case self::INPUT_TYPE_URL:
                return self::urlField($name, $value, $htmlOptions);
            case self::INPUT_TYPE_EMAIL:
                return self::emailField($name, $value, $htmlOptions);
            case self::INPUT_TYPE_NUMBER:
                return self::numberField($name, $value, $htmlOptions);
            case self::INPUT_TYPE_RANGE:
                return self::rangeField($name, $value, $htmlOptions);
            case self::INPUT_TYPE_DATE:
                return self::dateField($name, $value, $htmlOptions);
            case self::INPUT_TYPE_TEXTAREA:
                return self::textArea($name, $value, $htmlOptions);
            case self::INPUT_TYPE_FILE:
                return self::fileField($name, $value, $htmlOptions);
            case self::INPUT_TYPE_RADIOBUTTON:
                return self::radioButton($name, $value, $htmlOptions);
            case self::INPUT_TYPE_CHECKBOX:
                return self::checkBox($name, $value, $htmlOptions);
            case self::INPUT_TYPE_DROPDOWNLIST:
                return self::dropDownList($name, $value, $data, $htmlOptions);
            case self::INPUT_TYPE_LISTBOX:
                return self::listBox($name, $value, $data, $htmlOptions);
            case self::INPUT_TYPE_CHECKBOXLIST:
                return self::checkBoxList($name, $value, $data, $htmlOptions);
            case self::INPUT_TYPE_INLINECHECKBOXLIST:
                return self::inlineCheckBoxList($name, $value, $data, $htmlOptions);
            case self::INPUT_TYPE_RADIOBUTTONLIST:
                return self::radioButtonList($name, $value, $data, $htmlOptions);
            case self::INPUT_TYPE_INLINERADIOBUTTONLIST:
                return self::inlineRadioButtonList($name, $value, $data, $htmlOptions);
            case self::INPUT_TYPE_UNEDITABLE:
                return self::uneditableField($value, $htmlOptions);
            case self::INPUT_TYPE_SEARCH:
                return self::searchQueryField($name, $value, $htmlOptions);
            default:
                throw new CException('Invalid input type "' . $type . '".');
        }
    }

    /**
     * Generates an input HTML tag.
     * This method generates an input HTML tag based on the given input name and value.
     * @param string $type the input type.
     * @param string $name the input name.
     * @param string $value the input value.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input tag.
     */
    protected static function textInputField($type, $name, $value, $htmlOptions)
    {
        parent::clientChange('change', $htmlOptions);

        // In case we do need to create a div container for the input element (i.e. has addon or defined col)
        $containerOptions = array();

        // Get the intended input width before the rest of the options are normalized
        self::addSpanClass($htmlOptions);
        self::addColClass($htmlOptions);
        $col = self::popColClasses($htmlOptions);

        $htmlOptions = self::normalizeInputOptions($htmlOptions);
        self::addCssClass('form-control', $htmlOptions);

        $addOnClass = self::getAddOnClasses($htmlOptions);
        $addOnOptions = TbArray::popValue('addOnOptions', $htmlOptions, array());
        self::addCssClass($addOnClass, $addOnOptions);

        $prepend = TbArray::popValue('prepend', $htmlOptions, '');
        $prependOptions = TbArray::popValue('prependOptions', $htmlOptions, array());
        if (!empty($prepend)) {
            $prepend = self::inputAddOn($prepend, $prependOptions, 'prepend');
        }

        $append = TbArray::popValue('append', $htmlOptions, '');
        $appendOptions = TbArray::popValue('appendOptions', $htmlOptions, array());
        if (!empty($append)) {
            $append = self::inputAddOn($append, $appendOptions, 'append');
        }

        if (!empty($addOnClass)) {
            $containerOptions = $addOnOptions;
        }

        if (!empty($col)) {
            self::addCssClass($col, $containerOptions);
        }

        $output = '';
        if (!empty($containerOptions)) {
            $output .= self::openTag('div', $containerOptions);
        }
        $output .= $prepend . parent::inputField($type, $name, $value, $htmlOptions) . $append;
        if (!empty($containerOptions)) {
            $output .= '</div>';
        }
        return $output;
    }

    /**
     * Generates a text field input for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field.
     * @see self::activeTextInputField
     */
    public static function activeTextField($model, $attribute, $htmlOptions = array())
    {
        return self::activeTextInputField('text', $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a password field input for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field.
     * @see self::activeTextInputField
     */
    public static function activePasswordField($model, $attribute, $htmlOptions = array())
    {
        return self::activeTextInputField('password', $model, $attribute, $htmlOptions);
    }

    /**
     * Generates an url field input for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field.
     * @see self::activeTextInputField
     */
    public static function activeUrlField($model, $attribute, $htmlOptions = array())
    {
        return self::activeTextInputField('url', $model, $attribute, $htmlOptions);
    }

    /**
     * Generates an email field input for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field.
     * @see self::activeTextInputField
     */
    public static function activeEmailField($model, $attribute, $htmlOptions = array())
    {
        return self::activeTextInputField('email', $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a number field input for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field.
     * @see self::activeTextInputField
     */
    public static function activeNumberField($model, $attribute, $htmlOptions = array())
    {
        return self::activeTextInputField('number', $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a range field input for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field.
     * @see self::activeTextInputField
     */
    public static function activeRangeField($model, $attribute, $htmlOptions = array())
    {
        return self::activeTextInputField('range', $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a date field input for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field.
     * @see self::activeTextInputField
     */
    public static function activeDateField($model, $attribute, $htmlOptions = array())
    {
        return self::activeTextInputField('date', $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a file field input for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input field.
     * @see CHtml::activeFileField
     */
    public static function activeFileField($model, $attribute, $htmlOptions = array())
    {
        return parent::activeFileField($model, $attribute, $htmlOptions);
    }

    /**
     * Generates a text area input for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated text area.
     */
    public static function activeTextArea($model, $attribute, $htmlOptions = array())
    {
        // In case we do need to create a div container for the text area
        $containerOptions = array();

        // Get the intended input width before the rest of the options are normalized
        self::addSpanClass($htmlOptions);
        self::addColClass($htmlOptions);
        $col = self::popColClasses($htmlOptions);

        $htmlOptions = self::normalizeInputOptions($htmlOptions);
        self::addCssClass('form-control', $htmlOptions);

        $output = '';
        if (!empty($col)) {
            self::addCssClass($col, $containerOptions);
            $output .= self::openTag('div', $containerOptions);
        }
        $output .= parent::activeTextArea($model, $attribute, $htmlOptions);
        if (!empty($col)) {
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Generates a radio button for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated radio button.
     */
    public static function activeRadioButton($model, $attribute, $htmlOptions = array())
    {
        $label = TbArray::popValue('label', $htmlOptions, false);
        $labelOptions = TbArray::popValue('labelOptions', $htmlOptions, array());
        $input = parent::activeRadioButton($model, $attribute, $htmlOptions);
        if (TbArray::popValue('useContainer', $htmlOptions, false)) {
            return self::tag(
                'div',
                array('class' => 'radio'),
                self::createCheckBoxAndRadioButtonLabel($label, $input, $labelOptions)
            );
        } else {
            return self::createCheckBoxAndRadioButtonLabel($label, $input, $labelOptions);
        }

    }

    /**
     * Generates a check box for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated check box.
     */
    public static function activeCheckBox($model, $attribute, $htmlOptions = array())
    {
        $label = TbArray::popValue('label', $htmlOptions, false);
        $labelOptions = TbArray::popValue('labelOptions', $htmlOptions, array());
        $input = parent::activeCheckBox($model, $attribute, $htmlOptions);
        if (TbArray::popValue('useContainer', $htmlOptions, false)) {
            return self::tag(
                'div',
                array('class' => 'checkbox'),
                self::createCheckBoxAndRadioButtonLabel($label, $input, $labelOptions)
            );
        } else {
            return self::createCheckBoxAndRadioButtonLabel($label, $input, $labelOptions);
        }
    }

    /**
     * Generates a label for a checkbox or radio input by wrapping the input.
     * @param string $label the label text.
     * @param string $input the input.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated label.
     */
    protected static function createCheckBoxAndRadioButtonLabel($label, $input, $htmlOptions)
    {
        list ($hidden, $input) = self::normalizeCheckBoxAndRadio($input);
        return $hidden . ($label !== false
            ? self::tag('label', $htmlOptions, $input . ' ' . $label)
            : $input);
    }

    /**
     * Normalizes the inputs in the given string by splitting them up into an array.
     * @param string $input the inputs.
     * @return array an array with the following structure: array($hidden, $input)
     */
    protected static function normalizeCheckBoxAndRadio($input)
    {
        $parts = preg_split("/(<.*?>)/", $input, 2, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        if (isset($parts[1])) {
            return $parts;
        } else {
            return array('', $parts[0]);
        }
    }

    /**
     * Generates a drop down list for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $data data for generating the list options (value=>display).
     * @param array $htmlOptions additional HTML attributes
     * @return string the generated drop down list.
     */
    public static function activeDropDownList($model, $attribute, $data, $htmlOptions = array())
    {
        $displaySize = TbArray::popValue('displaySize', $htmlOptions);

        // In case we do need to create a div container for the input element (i.e. has addon or defined col)
        $containerOptions = array();

        // Get the intended input width before the rest of the options are normalized
        self::addSpanClass($htmlOptions);
        self::addColClass($htmlOptions);
        $col = self::popColClasses($htmlOptions);

        $htmlOptions = self::normalizeInputOptions($htmlOptions);
        self::addCssClass('form-control', $htmlOptions);
        if (!empty($displaySize)) {
            $htmlOptions['size'] = $displaySize;
        }

        if (!empty($col)) {
            self::addCssClass($col, $containerOptions);
        }

        $output = '';

        if (!empty($containerOptions)) {
            $output .= self::openTag('div', $containerOptions);
        }
        $output .= parent::activeDropDownList($model, $attribute, $data, $htmlOptions);

        if (!empty($containerOptions)) {
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Generates a list box for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $data data for generating the list options (value=>display).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated list box
     */
    public static function activeListBox($model, $attribute, $data, $htmlOptions = array())
    {
        TbArray::defaultValue('displaySize', 4, $htmlOptions);
        self::addCssClass('form-control', $htmlOptions);
        return self::activeDropDownList($model, $attribute, $data, $htmlOptions);
    }

    /**
     * Generates a radio button list for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $data $data value-label pairs used to generate the radio button list.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated list.
     */
    public static function activeRadioButtonList($model, $attribute, $data, $htmlOptions = array())
    {
        parent::resolveNameID($model, $attribute, $htmlOptions);
        $selection = parent::resolveValue($model, $attribute);
        $name = TbArray::popValue('name', $htmlOptions);
        $uncheckValue = TbArray::popValue('uncheckValue', $htmlOptions, '');
        $hiddenOptions = isset($htmlOptions['id']) ? array('id' => parent::ID_PREFIX . $htmlOptions['id']) : array('id' => false);
        $hidden = $uncheckValue !== null ? parent::hiddenField($name, $uncheckValue, $hiddenOptions) : '';
        return $hidden . self::radioButtonList($name, $selection, $data, $htmlOptions);
    }

    /**
     * Generates an inline radio button list for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $data $data value-label pairs used to generate the radio button list.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated list.
     */
    public static function activeInlineRadioButtonList($model, $attribute, $data, $htmlOptions = array())
    {
        $htmlOptions['inline'] = true;
        return self::activeRadioButtonList($model, $attribute, $data, $htmlOptions);
    }

    /**
     * Generates a check box list for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $data $data value-label pairs used to generate the check box list.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated list.
     */
    public static function activeCheckBoxList($model, $attribute, $data, $htmlOptions = array())
    {
        parent::resolveNameID($model, $attribute, $htmlOptions);
        $selection = parent::resolveValue($model, $attribute);
        if ($model->hasErrors($attribute)) {
            parent::addErrorCss($htmlOptions);
        }
        $name = TbArray::popValue('name', $htmlOptions);
        $uncheckValue = TbArray::popValue('uncheckValue', $htmlOptions, '');
        $hiddenOptions = isset($htmlOptions['id']) ? array('id' => parent::ID_PREFIX . $htmlOptions['id']) : array('id' => false);
        $hidden = $uncheckValue !== null ? parent::hiddenField($name, $uncheckValue, $hiddenOptions) : '';
        return $hidden . self::checkBoxList($name, $selection, $data, $htmlOptions);
    }

    /**
     * Generates an inline check box list for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $data $data value-label pairs used to generate the check box list.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated list.
     */
    public static function activeInlineCheckBoxList($model, $attribute, $data, $htmlOptions = array())
    {
        $htmlOptions['inline'] = true;
        return self::activeCheckBoxList($model, $attribute, $data, $htmlOptions);
    }

    /**
     * Generates an uneditable input for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input.
     */
    public static function activeUneditableField($model, $attribute, $htmlOptions = array())
    {
        parent::resolveNameID($model, $attribute, $htmlOptions);
        $value = parent::resolveValue($model, $attribute);
        TbArray::removeValues(array('name', 'id'), $htmlOptions);
        return self::uneditableField($value, $htmlOptions);
    }

    /**
     * Generates a search query input for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input.
     */
    public static function activeSearchQueryField($model, $attribute, $htmlOptions = array())
    {
        return self::activeTextInputField('search', $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a control group with a text field for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeTextFieldControlGroup($model, $attribute, $htmlOptions = array())
    {
        return self::activeControlGroup(self::INPUT_TYPE_TEXT, $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a control group with a password field for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activePasswordFieldControlGroup($model, $attribute, $htmlOptions = array())
    {
        return self::activeControlGroup(self::INPUT_TYPE_PASSWORD, $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a control group with a url field for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeUrlFieldControlGroup($model, $attribute, $htmlOptions = array())
    {
        return self::activeControlGroup(self::INPUT_TYPE_URL, $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a control group with a email field for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeEmailFieldControlGroup($model, $attribute, $htmlOptions = array())
    {
        return self::activeControlGroup(self::INPUT_TYPE_EMAIL, $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a control group with a number field for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeNumberFieldControlGroup($model, $attribute, $htmlOptions = array())
    {
        return self::activeControlGroup(self::INPUT_TYPE_NUMBER, $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a control group with a range field for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeRangeFieldControlGroup($model, $attribute, $htmlOptions = array())
    {
        return self::activeControlGroup(self::INPUT_TYPE_RANGE, $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a control group with a date field for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeDateFieldControlGroup($model, $attribute, $htmlOptions = array())
    {
        return self::activeControlGroup(self::INPUT_TYPE_DATE, $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a control group with a text area for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeTextAreaControlGroup($model, $attribute, $htmlOptions = array())
    {
        return self::activeControlGroup(self::INPUT_TYPE_TEXTAREA, $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a control group with a file field for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeFileFieldControlGroup($model, $attribute, $htmlOptions = array())
    {
        return self::activeControlGroup(self::INPUT_TYPE_FILE, $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a control group with a radio button for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeRadioButtonControlGroup($model, $attribute, $htmlOptions = array())
    {
        return self::activeControlGroup(self::INPUT_TYPE_RADIOBUTTON, $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a control group with a check box for a model attribute.
     * @param CModel $model the data model
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeCheckBoxControlGroup($model, $attribute, $htmlOptions = array())
    {
        return self::activeControlGroup(self::INPUT_TYPE_CHECKBOX, $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a control group with a drop down list for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $data data for generating the list options (value=>display).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeDropDownListControlGroup($model, $attribute, $data = array(), $htmlOptions = array())
    {
        return self::activeControlGroup(self::INPUT_TYPE_DROPDOWNLIST, $model, $attribute, $htmlOptions, $data);
    }

    /**
     * Generates a control group with a list box for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $data data for generating the list options (value=>display).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeListBoxControlGroup($model, $attribute, $data = array(), $htmlOptions = array())
    {
        return self::activeControlGroup(self::INPUT_TYPE_LISTBOX, $model, $attribute, $htmlOptions, $data);
    }

    /**
     * Generates a control group with a radio button list for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $data data for generating the list options (value=>display).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeRadioButtonListControlGroup(
        $model,
        $attribute,
        $data = array(),
        $htmlOptions = array()
    ) {
        return self::activeControlGroup(self::INPUT_TYPE_RADIOBUTTONLIST, $model, $attribute, $htmlOptions, $data);
    }

    /**
     * Generates a control group with an inline radio button list for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $data data for generating the list options (value=>display).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeInlineRadioButtonListControlGroup(
        $model,
        $attribute,
        $data = array(),
        $htmlOptions = array()
    ) {
        return self::activeControlGroup(
            self::INPUT_TYPE_INLINERADIOBUTTONLIST,
            $model,
            $attribute,
            $htmlOptions,
            $data
        );
    }

    /**
     * Generates a control group with a check box list for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $data data for generating the list options (value=>display).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeCheckBoxListControlGroup($model, $attribute, $data = array(), $htmlOptions = array())
    {
        return self::activeControlGroup(self::INPUT_TYPE_CHECKBOXLIST, $model, $attribute, $htmlOptions, $data);
    }

    /**
     * Generates a control group with an inline check box list for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $data data for generating the list options (value=>display).
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeInlineCheckBoxListControlGroup(
        $model,
        $attribute,
        $data = array(),
        $htmlOptions = array()
    ) {
        return self::activeControlGroup(self::INPUT_TYPE_INLINECHECKBOXLIST, $model, $attribute, $htmlOptions, $data);
    }

    /**
     * Generates a control group with a uneditable field for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeUneditableFieldControlGroup($model, $attribute, $htmlOptions = array())
    {
        return self::activeControlGroup(self::INPUT_TYPE_UNEDITABLE, $model, $attribute, $htmlOptions);
    }

    /**
     * Generates a control group with a search field for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     * @see self::activeControlGroup
     */
    public static function activeSearchQueryControlGroup($model, $attribute, $htmlOptions = array())
    {
        return self::activeControlGroup(self::INPUT_TYPE_SEARCH, $model, $attribute, $htmlOptions);
    }

    /**
     * Generates an active form row.
     * @param string $type the input type.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @param array $data data for multiple select inputs.
     * @return string the generated control group.
     */
    public static function activeControlGroup($type, $model, $attribute, $htmlOptions = array(), $data = array())
    {
        $color = TbArray::popValue('color', $htmlOptions);
        $groupOptions = TbArray::popValue('groupOptions', $htmlOptions, array());
        $controlOptions = TbArray::popValue('controlOptions', $htmlOptions, array());
        $label = TbArray::popValue('label', $htmlOptions);
        $labelOptions = TbArray::popValue('labelOptions', $htmlOptions, array());

        // todo: remove everything that has to do with form layout
        $formLayout = TbArray::popValue('formLayout', $htmlOptions, self::FORM_LAYOUT_VERTICAL);
        $labelWidthClass = TbArray::popValue('labelWidthClass', $htmlOptions, self::$defaultFormLabelWidthClass);
        // Retrieve the old-style "span" option
        $span = TbArray::popValue('span', $htmlOptions);
        if (!empty($span)) {
            $controlWidthClass = 'col-md-' . $span;
        } else {
            $controlWidthClass = TbArray::popValue('controlWidthClass', $htmlOptions, self::$defaultFormControlWidthClass);
        }
        $useFormGroup = true;
        $useControls = true;
        $output = '';

        // Special label case case for individual checkboxes and radios
        if ($type == self::INPUT_TYPE_CHECKBOX || $type == self::INPUT_TYPE_RADIOBUTTON) {
            $htmlOptions['label'] = isset($label) ? $label : $model->getAttributeLabel($attribute);
            $htmlOptions['labelOptions'] = $labelOptions;
            $htmlOptions['useContainer'] = true;
            $label = false;
            $useFormGroup = false;
        }

        // Special conditions depending on the form type
        if ($formLayout == self::FORM_LAYOUT_HORIZONTAL) {
            switch ($type) {
                case self::INPUT_TYPE_CHECKBOX:
                case self::INPUT_TYPE_RADIOBUTTON:
                    self::addCssClass(self::switchColToOffset($labelWidthClass), $controlOptions);
                    self::addCssClass(self::switchOffsetToCol($controlWidthClass), $controlOptions);
                    $useFormGroup = true;
                    break;
                default:
                    self::addCssClass(self::switchOffsetToCol($labelWidthClass), $labelOptions);
                    self::addCssClass(self::switchOffsetToCol($controlWidthClass), $controlOptions);
            }
        } elseif ($formLayout == self::FORM_LAYOUT_INLINE || $formLayout == self::FORM_LAYOUT_SEARCH) {
            switch ($type) {
                case self::INPUT_TYPE_TEXT:
                case self::INPUT_TYPE_PASSWORD:
                case self::INPUT_TYPE_URL:
                case self::INPUT_TYPE_EMAIL:
                case self::INPUT_TYPE_NUMBER:
                case self::INPUT_TYPE_RANGE:
                case self::INPUT_TYPE_DATE:
                case self::INPUT_TYPE_FILE:
                case self::INPUT_TYPE_SEARCH:
                    self::addCssClass('sr-only', $labelOptions);
                    if (($label !== null) && (TbArray::getValue('placeholder', $htmlOptions) !== null)) {
                        $htmlOptions['placeholder'] = $label;
                    }
                    break;
                case self::INPUT_TYPE_CHECKBOX:
                case self::INPUT_TYPE_RADIOBUTTON:
                    $useControls = false;
                    break;
            }
        }
        // remove until here.

        if (isset($label) && $label !== false) {
            $labelOptions['label'] = $label;
        }
        $help = TbArray::popValue('help', $htmlOptions, '');
        $helpOptions = TbArray::popValue('helpOptions', $htmlOptions, array());
        if (!empty($help)) {
            $help = self::inputHelp($help, $helpOptions);
        }
        $error = TbArray::popValue('error', $htmlOptions, '');

        $input = isset($htmlOptions['input'])
            ? $htmlOptions['input']
            : self::createActiveInput($type, $model, $attribute, $htmlOptions, $data);

        if (!empty($color)) {
            self::addCssClass($color, $groupOptions);
        }
        self::addCssClass('form-label', $labelOptions);
        if ($label !== false) {
            $output .= parent::activeLabelEx($model, $attribute, $labelOptions);
        }
        if ($useControls) {
            $output .= self::controls($input . $error . $help, $controlOptions);
        } else {
            $output .= $input;
        }

        if ($useFormGroup) {
            self::addCssClass('form-group', $groupOptions);
            return self::tag(
                'div',
                $groupOptions,
                $output
            );
        } else {
            return $output;
        }
    }

    /**
     * Generates a custom (pre-rendered) active form control group.
     * @param string $input the rendered input.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated control group.
     */
    public static function customActiveControlGroup($input, $model, $attribute, $htmlOptions = array())
    {
        $htmlOptions['input'] = $input;
        return self::activeControlGroup(self::INPUT_TYPE_CUSTOM, $model, $attribute, $htmlOptions);
    }

    /**
     * Creates an active form input of the given type.
     * @param string $type the input type.
     * @param CModel $model the model instance.
     * @param string $attribute the attribute name.
     * @param array $htmlOptions additional HTML attributes.
     * @param array $data data for multiple select inputs.
     * @return string the input.
     * @throws CException if the input type is invalid.
     */
    public static function createActiveInput($type, $model, $attribute, $htmlOptions = array(), $data = array())
    {
        switch ($type) {
            case self::INPUT_TYPE_TEXT:
                return self::activeTextField($model, $attribute, $htmlOptions);
            case self::INPUT_TYPE_PASSWORD:
                return self::activePasswordField($model, $attribute, $htmlOptions);
            case self::INPUT_TYPE_URL:
                return self::activeUrlField($model, $attribute, $htmlOptions);
            case self::INPUT_TYPE_EMAIL:
                return self::activeEmailField($model, $attribute, $htmlOptions);
            case self::INPUT_TYPE_NUMBER:
                return self::activeNumberField($model, $attribute, $htmlOptions);
            case self::INPUT_TYPE_RANGE:
                return self::activeRangeField($model, $attribute, $htmlOptions);
            case self::INPUT_TYPE_DATE:
                return self::activeDateField($model, $attribute, $htmlOptions);
            case self::INPUT_TYPE_TEXTAREA:
                return self::activeTextArea($model, $attribute, $htmlOptions);
            case self::INPUT_TYPE_FILE:
                return self::activeFileField($model, $attribute, $htmlOptions);
            case self::INPUT_TYPE_RADIOBUTTON:
                return self::activeRadioButton($model, $attribute, $htmlOptions);
            case self::INPUT_TYPE_CHECKBOX:
                return self::activeCheckBox($model, $attribute, $htmlOptions);
            case self::INPUT_TYPE_DROPDOWNLIST:
                return self::activeDropDownList($model, $attribute, $data, $htmlOptions);
            case self::INPUT_TYPE_LISTBOX:
                return self::activeListBox($model, $attribute, $data, $htmlOptions);
            case self::INPUT_TYPE_CHECKBOXLIST:
                return self::activeCheckBoxList($model, $attribute, $data, $htmlOptions);
            case self::INPUT_TYPE_INLINECHECKBOXLIST:
                return self::activeInlineCheckBoxList($model, $attribute, $data, $htmlOptions);
            case self::INPUT_TYPE_RADIOBUTTONLIST:
                return self::activeRadioButtonList($model, $attribute, $data, $htmlOptions);
            case self::INPUT_TYPE_INLINERADIOBUTTONLIST:
                return self::activeInlineRadioButtonList($model, $attribute, $data, $htmlOptions);
            case self::INPUT_TYPE_UNEDITABLE:
                return self::activeUneditableField($model, $attribute, $htmlOptions);
            case self::INPUT_TYPE_SEARCH:
                return self::activeSearchQueryField($model, $attribute, $htmlOptions);
            default:
                throw new CException('Invalid input type "' . $type . '".');
        }
    }

    /**
     * Displays a summary of validation errors for one or several models.
     * @param mixed $model the models whose input errors are to be displayed.
     * @param string $header a piece of HTML code that appears in front of the errors.
     * @param string $footer a piece of HTML code that appears at the end of the errors.
     * @param array $htmlOptions additional HTML attributes to be rendered in the container div tag.
     * @return string the error summary. Empty if no errors are found.
     */
    public static function errorSummary($model, $header = null, $footer = null, $htmlOptions = array())
    {
        // kind of a quick fix but it will do for now.
        self::addCssClass(self::$errorSummaryCss, $htmlOptions);
        return parent::errorSummary($model, $header, $footer, $htmlOptions);
    }

    /**
     * Displays the first validation error for a model attribute.
     * @param CModel $model the data model.
     * @param string $attribute the attribute name.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the rendered error. Empty if no errors are found.
     */
    public static function error($model, $attribute, $htmlOptions = array())
    {
        parent::resolveName($model, $attribute); // turn [a][b]attr into attr
        $error = $model->getError($attribute);
        $htmlOptions['type'] = self::HELP_TYPE_INLINE;
        return !empty($error) ? self::help($error, $htmlOptions) : '';
    }

    /**
     * Generates an input HTML tag  for a model attribute.
     * This method generates an input HTML tag based on the given input name and value.
     * @param string $type the input type.
     * @param CModel $model the data model.
     * @param string $attribute the attribute.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated input tag.
     */
    protected static function activeTextInputField($type, $model, $attribute, $htmlOptions)
    {
        parent::resolveNameID($model, $attribute, $htmlOptions);
        parent::clientChange('change', $htmlOptions);

        // In case we do need to create a div container for the input element (i.e. has addon or defined col)
        $containerOptions = array();

        // Get the intended input width before the rest of the options are normalized
        self::addSpanClass($htmlOptions);
        self::addColClass($htmlOptions);
        $col = self::popColClasses($htmlOptions);

        $htmlOptions = self::normalizeInputOptions($htmlOptions);
        self::addCssClass('form-control', $htmlOptions);

        $addOnClass = self::getAddOnClasses($htmlOptions);
        $addOnOptions = TbArray::popValue('addOnOptions', $htmlOptions, array());
        self::addCssClass($addOnClass, $addOnOptions);

        $prepend = TbArray::popValue('prepend', $htmlOptions, '');
        $prependOptions = TbArray::popValue('prependOptions', $htmlOptions, array());
        if (!empty($prepend)) {
            $prepend = self::inputAddOn($prepend, $prependOptions);
        }

        $append = TbArray::popValue('append', $htmlOptions, '');
        $appendOptions = TbArray::popValue('appendOptions', $htmlOptions, array());
        if (!empty($append)) {
            $append = self::inputAddOn($append, $appendOptions);
        }

        if (!empty($addOnClass)) {
            $containerOptions = $addOnOptions;
        }

        if (!empty($col)) {
            self::addCssClass($col, $containerOptions);
        }

        $output = '';
        if (!empty($containerOptions)) {
            $output .= self::openTag('div', $containerOptions);
        }
        $output .= $prepend . parent::activeInputField($type, $model, $attribute, $htmlOptions) . $append;
        if (!empty($containerOptions)) {
            $output .= '</div>';
        }
        return $output;
    }

    /**
     * Returns the add-on classes based on the given options.
     * @param array $htmlOptions the options.
     * @return string the classes.
     */
    protected static function getAddOnClasses($htmlOptions)
    {
        return (TbArray::getValue('append', $htmlOptions, false) || TbArray::getValue('prepend', $htmlOptions, false))
            ? 'input-group'
            : '';
    }

    /**
     * Generates an add-on for an input field.
     * @param string|array $addOns the add-on.
     * @param array $htmlOptions additional HTML attributes.
     * @param string $position either 'prepend' or 'append'. Position is only important if you are passing multiple
     * addons and it's a mixture of text/radio/checkboxes or buttons. The current styling needs buttons at the ends.
     * @return string the generated add-on.
     */
    protected static function inputAddOn($addOns, $htmlOptions, $position = 'prepend')
    {
        // todo: refactor this method
        $normal = array();
        $buttons = array();
        $addOnOptions = TbArray::popValue('addOnOptions', $htmlOptions, array());
        $normalAddOnOptions = $addOnOptions;
        $buttonAddOnOptions = $addOnOptions;
        self::addCssClass('input-group-text', $normalAddOnOptions);
        self::addCssClass('input-group-btn', $buttonAddOnOptions);

        if (!is_array($addOns)) {
            $addOns = array($addOns);
        }

        foreach ($addOns as $addOn) {
            if (strpos((string) $addOn, 'btn') === false) {
                $normal[] = $addOn;
            } else { // TbHtml::butonDropdown() requires special parsing
                if (preg_match('/^<div.*class="(.*)".*>(.*)<\/div>$/U', (string) $addOn, $matches) > 0
                    && (isset($matches[1]))
                    && strpos($matches[1], 'btn-group') !== false
                ) {
                    $buttons[] = $matches[2];
                } else {
                    $buttons[] = $addOn;
                }
            }
        }
        $output = '';

        if ($position == 'prepend') {
            if (!empty($buttons)) {
                $output .= self::tag('span', $buttonAddOnOptions, implode(' ', $buttons));
            }
            if (!empty($normal)) {
                $output .= self::tag('span', $normalAddOnOptions, implode(' ', $normal));
            }
        } else { // append
            if (!empty($normal)) {
                $output .= self::tag('span', $normalAddOnOptions, implode(' ', $normal));
            }
            if (!empty($buttons)) {
                $output .= self::tag('span', $buttonAddOnOptions, implode(' ', $buttons));
            }
        }
        return $output;
    }

    /**
     * Generates a help text for an input field.
     * @param string $help the help text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated help text.
     */
    protected static function inputHelp($help, $htmlOptions)
    {
        $htmlOptions['type'] = self::HELP_TYPE_INLINE;
        return self::help($help, $htmlOptions);
    }

    /**
     * Normalizes input options.
     * @param array $options the options.
     * @return array the normalized options.
     */
    protected static function normalizeInputOptions($options)
    {
        self::addSpanClass($options);
        self::addTextAlignClass($options);
        $size = TbArray::popValue('size', $options);
        if (TbArray::popValue('block', $options, false)) {
            self::addCssClass('input-block-level', $options);
        } else {
            if (!empty($size)) {
                self::addCssClass('input-' . $size, $options);
            }
        }
        return $options;
    }

    /**
     * Generates form controls.
     * @param mixed $controls the controls.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated controls.
     */
    public static function controls($controls, $htmlOptions = array())
    {
        if (TbArray::popValue('row', $htmlOptions, false)) {
            self::addCssClass('row', $htmlOptions);
        }
        $before = TbArray::popValue('before', $htmlOptions, '');
        $after = TbArray::popValue('after', $htmlOptions, '');
        if (is_array($controls)) {
            $controls = implode('', $controls);
        }
        $content = $before . $controls . $after;
        return self::tag('div', $htmlOptions, $content);
    }

    /**
     * Generates form controls row.
     * @deprecated BS3 only requires a div.row container for all inputs if you want a controls row
     * @param mixed $controls the controls.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated controls.
     */
    public static function controlsRow($controls, $htmlOptions = array())
    {
        $htmlOptions['row'] = true;
        return self::controls($controls, $htmlOptions);
    }

    /**
     * Generates form actions div. This is no longer necessary in Bootstrap 3, but it is still useful to use for
     * horizontal forms. When used with a horizontal form, it will appropriately align the actions below other form
     * controls.
     * @param mixed $actions the actions.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated actions.
     */
    public static function formActions($actions, $htmlOptions = array())
    {
        self::addCssClass('form-actions', $htmlOptions);
        if (is_array($actions)) {
            $actions = implode(' ', $actions);
        }
        // todo: remove this
        $labelWidthClass = TbArray::popValue('labelWidthClass', $htmlOptions, self::$defaultFormLabelWidthClass);
        $controlWidthClass = TbArray::popValue('controlWidthClass', $htmlOptions, self::$defaultFormControlWidthClass);

        // todo: remove everything that has to do with form layout
        if (TbArray::popValue('formLayout', $htmlOptions, self::FORM_LAYOUT_VERTICAL) == self::FORM_LAYOUT_HORIZONTAL) {
            self::addCssClass(self::switchColToOffset($labelWidthClass), $htmlOptions);
            self::addCssClass(self::switchOffsetToCol($controlWidthClass), $htmlOptions);

            return self::tag('div', array('class' => 'form-group'), self::tag('div', $htmlOptions, $actions));
        } else {
            return self::tag('div', $htmlOptions, $actions);
        }
    }

    /**
     * Generates a search form.
     * @param mixed $action the form action URL.
     * @param string $method form method (e.g. post, get).
     * @param array $htmlOptions additional HTML options.
     * @return string the generated form.
     */
    public static function searchForm($action, $method = 'post', $htmlOptions = array())
    {
        self::addCssClass('form-search', $htmlOptions);
        $inputOptions = TbArray::popValue('inputOptions', $htmlOptions, array());
        $inputOptions = TbArray::merge(array('type' => 'text', 'placeholder' => 'Search'), $inputOptions);
        $name = TbArray::popValue('name', $inputOptions, 'search');
        $value = TbArray::popValue('value', $inputOptions, '');
        $output = self::beginFormTb(self::FORM_LAYOUT_SEARCH, $action, $method, $htmlOptions);
        $output .= self::searchQueryField($name, $value, $inputOptions);
        $output .= parent::endForm();
        return $output;
    }

    // Buttons
    // http://getbootstrap.com/css/#buttons
    // --------------------------------------------------

    /**
     * Generates a hyperlink tag.
     * @param string $text link body. It will NOT be HTML-encoded.
     * @param mixed $url a URL or an action route that can be used to create a URL.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated hyperlink
     */
    public static function link($text, $url = '#', $htmlOptions = array())
    {
        $htmlOptions['href'] = parent::normalizeUrl($url);
        self::clientChange('click', $htmlOptions);
        return self::tag('a', $htmlOptions, $text);
    }

    /**
     * Generates an button.
     * @param string $label the button label text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated button.
     */
    public static function button($label = 'Button', $htmlOptions = array())
    {
        return self::htmlButton($label, $htmlOptions);
    }

    /**
     * Generates an image submit button.
     * @param string $label
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated button.
     */
    public static function htmlButton($label = 'Button', $htmlOptions = array())
    {
        return self::btn(self::BUTTON_TYPE_HTML, $label, $htmlOptions);
    }

    /**
     * Generates a submit button.
     * @param string $label the button label
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated button.
     */
    public static function submitButton($label = 'Submit', $htmlOptions = array())
    {
        return self::btn(self::BUTTON_TYPE_SUBMIT, $label, $htmlOptions);
    }

    /**
     * Generates a reset button.
     * @param string $label the button label
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated button.
     */
    public static function resetButton($label = 'Reset', $htmlOptions = array())
    {
        return self::btn(self::BUTTON_TYPE_RESET, $label, $htmlOptions);
    }

    /**
     * Generates an image submit button.
     * @param string $src the image URL
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated button.
     */
    public static function imageButton($src, $htmlOptions = array())
    {
        return self::btn(self::BUTTON_TYPE_IMAGE, $src, $htmlOptions);
    }

    /**
     * Generates a link submit button.
     * @param string $label the button label.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated button tag.
     */
    public static function linkButton($label = 'Submit', $htmlOptions = array())
    {
        return self::btn(self::BUTTON_TYPE_LINK, $label, $htmlOptions);
    }

    /**
     * Generates a link that can initiate AJAX requests.
     * @param string $text the link body (it will NOT be HTML-encoded.)
     * @param mixed $url the URL for the AJAX request.
     * @param array $ajaxOptions AJAX options.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated link.
     */
    public static function ajaxLink($text, $url, $ajaxOptions = array(), $htmlOptions = array())
    {
        if (!isset($htmlOptions['href'])) {
            $htmlOptions['href'] = '#';
        }
        $ajaxOptions['url'] = $url;
        $htmlOptions['ajax'] = $ajaxOptions;
        parent::clientChange('click', $htmlOptions);
        return self::tag('a', $htmlOptions, $text);
    }

    /**
     * Generates a push button that can initiate AJAX requests.
     * @param string $label the button label.
     * @param mixed $url the URL for the AJAX request.
     * @param array $ajaxOptions AJAX options.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated button.
     */
    public static function ajaxButton($label, $url, $ajaxOptions = array(), $htmlOptions = array())
    {
        $ajaxOptions['url'] = $url;
        $htmlOptions['ajaxOptions'] = $ajaxOptions;
        return self::btn(self::BUTTON_TYPE_AJAXBUTTON, $label, $htmlOptions);
    }

    /**
     * Generates a push button that can submit the current form in POST method.
     * @param string $label the button label
     * @param mixed $url the URL for the AJAX request.
     * @param array $ajaxOptions AJAX options.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated button.
     */
    public static function ajaxSubmitButton($label, $url, $ajaxOptions = array(), $htmlOptions = array())
    {
        $ajaxOptions['type'] = 'POST';
        $htmlOptions['type'] = 'submit';
        return self::ajaxButton($label, $url, $ajaxOptions, $htmlOptions);
    }

     /**
     * Generates a form input push button.
     * @param string $label the button label
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated button.
     */
    public static function inputButton($label, $htmlOptions = array()) {
        return self::btn(self::BUTTON_TYPE_INPUTBUTTON, $label, $htmlOptions);
    }

	/**
     * Generates a form input submit push button.
     * @param string $label the button label
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated button.
     */
    public static function inputSubmit($label = 'Submit', $htmlOptions = array()) {
        return self::btn(self::BUTTON_TYPE_INPUTSUBMIT, $label, $htmlOptions);
    }

    /**
     * Generates a button.
     * @param string $type the button type.
     * @param string $label the button label text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated button.
     */
    public static function btn($type, $label, $htmlOptions = array())
    {
        self::addCssClass('btn', $htmlOptions);
        $color = TbArray::popValue('color', $htmlOptions, self::BUTTON_COLOR_DEFAULT);
        if (!empty($color)) {
            self::addCssClass('btn-' . $color, $htmlOptions);
        }
        $size = TbArray::popValue('size', $htmlOptions);
        if (!empty($size)) {
            self::addCssClass('btn-' . $size, $htmlOptions);
        }
        if (TbArray::popValue('block', $htmlOptions, false)) {
            self::addCssClass('btn-block', $htmlOptions);
        }
        if (TbArray::popValue('disabled', $htmlOptions, false)) {
            self::addCssClass('disabled', $htmlOptions);
            $htmlOptions['disabled'] = 'disabled';
        }
        $loading = TbArray::popValue('loading', $htmlOptions);
        if (!empty($loading)) {
            $htmlOptions['data-loading-text'] = $loading;
        }
        if (TbArray::popValue('toggle', $htmlOptions, false)) {
            $htmlOptions['data-bs-toggle'] = 'button';
        }
        $icon = TbArray::popValue('icon', $htmlOptions);
        $iconOptions = TbArray::popValue('iconOptions', $htmlOptions, array());
        if (!is_array($type) && strpos($type, 'input') === false) {
            if (!empty($icon)) {
                $label = self::icon($icon, $iconOptions) . ' ' . $label;
            }
            $items = TbArray::popValue('items', $htmlOptions);
        }
        $dropdownOptions = $htmlOptions;
        TbArray::removeValues(array('groupOptions', 'menuOptions', 'dropup'), $htmlOptions);
        self::addSpanClass($htmlOptions); // must be called here as parent renders buttons
        self::addPullClass($htmlOptions); // must be called here as parent renders buttons
        return isset($items)
            ? self::btnDropdown($type, $label, $items, $dropdownOptions)
            : self::createButton($type, $label, $htmlOptions);
    }

    /**
     * Generates a button dropdown.
     * @param string $type the button type.
     * @param string $label the button label text.
     * @param array $items the menu items.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated button.
     */
    protected static function btnDropdown($type, $label, $items, $htmlOptions)
    {
        $menuOptions = TbArray::popValue('menuOptions', $htmlOptions, array());
        $groupOptions = TbArray::popValue('groupOptions', $htmlOptions, array());
        self::addCssClass('btn-group', $groupOptions);
        if (TbArray::popValue('dropup', $htmlOptions, false)) {
            self::addCssClass('dropup', $groupOptions);
        }
        $output = self::openTag('div', $groupOptions);
        $toggleButtonType = TbArray::popValue('type', $htmlOptions, self::BUTTON_TYPE_HTML);
        $toggleButtonType = is_array($toggleButtonType) ? $toggleButtonType[1] : $toggleButtonType;
        if (TbArray::popValue('split', $htmlOptions, false)) {
            $output .= self::createButton($type, $label, $htmlOptions);
            $label = '';
        }        
        if(in_array($toggleButtonType, array(self::BUTTON_TYPE_LINKBUTTON, self::BUTTON_TYPE_LINK))){
            $output .= self::dropdownToggleLink($label, $htmlOptions);       
        } else {
            $output .= self::dropdownToggleButton($label, $htmlOptions);
        }
        $output .= self::dropdown($items, $menuOptions);
        $output .= '</div>';
        return $output;
    }

    /**
     * Creates a button the of given type.
     * @param string $type the button type.
     * @param string $label the button label.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the button.
     * @throws CException if the button type is valid.
     */
    protected static function createButton($type, $label, $htmlOptions)
    {
        $url = TbArray::popValue('url', $htmlOptions, '#');
        $ajaxOptions = TbArray::popValue('ajaxOptions', $htmlOptions, array());
        switch ($type) {
            case self::BUTTON_TYPE_HTML:
                return parent::htmlButton($label, $htmlOptions);

            case self::BUTTON_TYPE_SUBMIT:
                $htmlOptions['type'] = 'submit';
                return parent::htmlButton($label, $htmlOptions);

            case self::BUTTON_TYPE_RESET:
                $htmlOptions['type'] = 'reset';
                return parent::htmlButton($label, $htmlOptions);

            case self::BUTTON_TYPE_IMAGE:
                return parent::imageButton($label, $htmlOptions);

            case self::BUTTON_TYPE_LINKBUTTON:
                return parent::linkButton($label, $htmlOptions);

            case self::BUTTON_TYPE_AJAXLINK:
                return parent::ajaxLink($label, $url, $ajaxOptions, $htmlOptions);

            case self::BUTTON_TYPE_AJAXBUTTON:
                $htmlOptions['ajax'] = $ajaxOptions;
                return parent::htmlButton($label, $htmlOptions);

            case self::BUTTON_TYPE_INPUTBUTTON:
                return parent::button($label, $htmlOptions);

            case self::BUTTON_TYPE_INPUTSUBMIT:
                $htmlOptions['type'] = 'submit';
                return parent::button($label, $htmlOptions);

            case self::BUTTON_TYPE_LINK:
                return self::link($label, $url, $htmlOptions);

            default:
                throw new CException('Invalid button type "' . $type . '".');
        }
    }

    // Images
    // http://getbootstrap.com/css/#images
    // --------------------------------------------------

    /**
     * Generates an image tag with rounded corners.
     * @param string $src the image URL.
     * @param string $alt the alternative text display.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated image tag.
     */
    public static function imageRounded($src, $alt = '', $htmlOptions = array())
    {
        $htmlOptions['type'] = self::IMAGE_TYPE_ROUNDED;
        return self::image($src, $alt, $htmlOptions);
    }

    /**
     * Generates an image tag with circle.
     * @param string $src the image URL.
     * @param string $alt the alternative text display.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated image tag.
     */
    public static function imageCircle($src, $alt = '', $htmlOptions = array())
    {
        $htmlOptions['type'] = self::IMAGE_TYPE_CIRCLE;
        return self::image($src, $alt, $htmlOptions);
    }

    /**
     * Generates an image tag within thumbnail frame.
     * @param string $src the image URL.
     * @param string $alt the alternative text display.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated image tag.
     */
    public static function imageThumbnail($src, $alt = '', $htmlOptions = array())
    {
        $htmlOptions['type'] = self::IMAGE_TYPE_THUMBNAIL;
        return self::image($src, $alt, $htmlOptions);
    }

    /**
     * Generates an image tag within polaroid frame.
     * @deprecated See {@link imageThumbnail()}
     * @param string $src the image URL.
     * @param string $alt the alternative text display.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated image tag.
     */
    public static function imagePolaroid($src, $alt = '', $htmlOptions = array())
    {
        $htmlOptions['type'] = self::IMAGE_TYPE_POLAROID;
        return self::image($src, $alt, $htmlOptions);
    }

    /**
     * Generates an image tag.
     * @param string $src the image URL.
     * @param string $alt the alternative text display.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated image tag.
     */
    public static function image($src, $alt = '', $htmlOptions = array())
    {
        $type = TbArray::popValue('type', $htmlOptions);
        if (!empty($type)) {
            self::addCssClass('img-' . $type, $htmlOptions);
        }
        if (TbArray::popValue('responsive', $htmlOptions, false)) {
            self::addCssClass('img-responsive', $htmlOptions);
        }
        return parent::image($src, $alt, $htmlOptions);
    }

    // Icons by fas
    // http://getbootstrap.com/components/#fas
    // --------------------------------------------------

    /**
     * Generates an icon.
     * @param string $icon the icon type.
     * @param array $htmlOptions additional HTML attributes.
     * @param string $tagName the icon HTML tag.
     * @param string $vendor the icon vendor.
     * @return string the generated icon.
     */
    public static function icon($icon, $htmlOptions = array(), $tagName = 'span')
    {
        if (is_string($icon)) {
            if (strpos($icon, 'fa-') === false) {
                $icon = 'fa-' . implode(' fa-', explode(' ', $icon));
            }
            self::addCssClass(array('fa', $icon), $htmlOptions);
            $color = TbArray::popValue('color', $htmlOptions);
            if (!empty($color) && $color === self::ICON_COLOR_WHITE) {
                self::addCssClass("fa-white", $htmlOptions);
            }
            return self::openTag($tagName, $htmlOptions) . parent::closeTag($tagName); // tag won't work in this case
        }
        return '';
    }

    //
    // COMPONENTS
    // --------------------------------------------------

    // Dropdowns
    // http://getbootstrap.com/components/#dropdowns
    // --------------------------------------------------

    /**
     * Generates a dropdown menu.
     * @param array $items the menu items.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated menu.
     */
    protected static function dropdown($items, $htmlOptions = array())
    {
        TbArray::defaultValue('role', 'menu', $htmlOptions);
        self::addCssClass('dropdown-menu', $htmlOptions);
        return self::menu($items, $htmlOptions);
    }

    /**
     * Generates a dropdown toggle link.
     * @param string $label the link label text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated link.
     */
    public static function dropdownToggleLink($label, $htmlOptions = array())
    {
        return self::dropdownToggle(self::BUTTON_TYPE_LINK, $label, $htmlOptions);
    }

    /**
     * Generates a dropdown toggle button.
     * @param string $label the button label text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated button.
     */
    public static function dropdownToggleButton($label = '', $htmlOptions = array())
    {
        return self::dropdownToggle(self::BUTTON_TYPE_HTML, $label, $htmlOptions);
    }

    /**
     * Generates a dropdown toggle element.
     * @param string $type the type of dropdown.
     * @param string $label the element text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated element.
     */
    protected static function dropdownToggle($type, $label, $htmlOptions)
    {
        self::addCssClass('dropdown-toggle', $htmlOptions);
        $label .= ' <b class="caret"></b>';
        $htmlOptions['data-bs-toggle'] = 'dropdown';
        return self::btn($type, $label, $htmlOptions);
    }

    /**
     * Generates a dropdown toggle menu item.
     * @param string $label the menu item text.
     * @param string $url the menu item URL.
     * @param array $htmlOptions additional HTML attributes.
     * @param int $depth the menu depth at which this link is located
     * @return string the generated menu item.
     */
    public static function dropdownToggleMenuLink($label, $url = '#', $htmlOptions = array(), $depth = 0)
    {
        self::addCssClass('dropdown-toggle', $htmlOptions);
        if ($depth === 0) {
            $label .= ' <b class="caret"></b>';
        }
        $htmlOptions['data-bs-toggle'] = 'dropdown';
        return self::link($label, $url, $htmlOptions);
    }

    // Button groups
    // http://getbootstrap.com/components/#btn-groups
    // --------------------------------------------------

    /**
     * Generates a button group.
     * @param array $buttons the button configurations.
     * @param array $htmlOptions additional HTML options.
     * @return string the generated button group.
     */
    public static function buttonGroup(array $buttons, $htmlOptions = array())
    {
        if (!empty($buttons)) {
            self::addCssClass('btn-group', $htmlOptions);
            if (TbArray::popValue('vertical', $htmlOptions, false)) {
                self::addCssClass('btn-group-vertical', $htmlOptions);
            }
            $toggle = TbArray::popValue('toggle', $htmlOptions);
            $name = TbArray::popValue('name', $htmlOptions);
            if (!empty($name) && substr((string) $name, -2) !== '[]') {
                $name .= '[]';
            }
            if (in_array($toggle, array(self::BUTTON_TOGGLE_CHECKBOX, self::BUTTON_TOGGLE_RADIO))) {
                $htmlOptions['data-bs-toggle'] = 'buttons';
                if (empty($name)) {
                    if ($toggle === self::BUTTON_TOGGLE_CHECKBOX) {
                        $name = 'checkbox[]';
                    } elseif ($toggle === self::BUTTON_TOGGLE_RADIO) {
                        $name = 'radio[]';
                    }
                }
            } else {
                $htmlOptions['data-bs-toggle'] = $toggle;
            }
            $parentOptions = array(
                'color' => TbArray::popValue('color', $htmlOptions),
                'size' => TbArray::popValue('size', $htmlOptions),
                'disabled' => TbArray::popValue('disabled', $htmlOptions)
            );
            $output = self::openTag('div', $htmlOptions);
            foreach ($buttons as $buttonOptions) {
                if (isset($buttonOptions['visible']) && $buttonOptions['visible'] === false) {
                    continue;
                }
                // todo: consider removing the support for htmlOptions.
                $options = TbArray::popValue('htmlOptions', $buttonOptions, array());
                if (!empty($options)) {
                    $buttonOptions = TbArray::merge($options, $buttonOptions);
                }
                $buttonLabel = TbArray::popValue('label', $buttonOptions, '');
                $buttonOptions = TbArray::copyValues(array('color', 'size', 'disabled'), $parentOptions, $buttonOptions);
                TbArray::defaultValue('color', 'default', $buttonOptions);
                $items = TbArray::popValue('items', $buttonOptions, array());
                if (!empty($items)) {
                    $output .= self::buttonDropdown($buttonLabel, $items, $buttonOptions);
                } else {
                    if (in_array($toggle, array(self::BUTTON_TOGGLE_CHECKBOX, self::BUTTON_TOGGLE_RADIO))) {
                        // Put the "button" label back into its options and add a few label options as well
                        $buttonOptions['label'] = $buttonLabel;
                        self::addCssClass(
                            array('btn', 'btn-' . TbArray::getValue('color', $buttonOptions)),
                            $buttonOptions['labelOptions']
                        );

                        $checked = TbArray::popValue('checked', $buttonOptions, false);
                        if ($checked) {
                            self::addCssClass('active', $buttonOptions['labelOptions']);
                        }
                        if ($toggle === self::BUTTON_TOGGLE_CHECKBOX) { // BS3 toggle uses checkbox...
                            $output .= self::checkBox($name, $checked, $buttonOptions);
                        } elseif ($toggle === self::BUTTON_TOGGLE_RADIO) { // ...or BS3 toggle uses radio
                            $output .= self::radioButton($name, $checked, $buttonOptions);
                        }
                    } else {
                        $output .= self::linkButton($buttonLabel, $buttonOptions);
                    }
                }
            }
            $output .= '</div>';
            return $output;
        }
        return '';
    }

    /**
     * Generates a vertical button group.
     * @param array $buttons the button configurations.
     * @param array $htmlOptions additional HTML options.
     * @return string the generated button group.
     */
    public static function verticalButtonGroup(array $buttons, $htmlOptions = array())
    {
        $htmlOptions['vertical'] = true;
        return self::buttonGroup($buttons, $htmlOptions);
    }

    /**
     * Generates a button toolbar.
     * @param array $groups the button group configurations.
     * @param array $htmlOptions additional HTML options.
     * @return string the generated button toolbar.
     */
    public static function buttonToolbar(array $groups, $htmlOptions = array())
    {
        if (!empty($groups)) {
            self::addCssClass('btn-toolbar', $htmlOptions);
            TbArray::defaultValue('role', 'toolbar', $htmlOptions);
            $parentOptions = array(
                'color' => TbArray::popValue('color', $htmlOptions),
                'size' => TbArray::popValue('size', $htmlOptions),
                'disabled' => TbArray::popValue('disabled', $htmlOptions)
            );
            $output = self::openTag('div', $htmlOptions);
            foreach ($groups as $groupOptions) {
                if (isset($groupOptions['visible']) && $groupOptions['visible'] === false) {
                    continue;
                }
                $items = TbArray::popValue('items', $groupOptions, array());
                if (empty($items)) {
                    continue;
                }
                // todo: consider removing the support for htmlOptions.
                $options = TbArray::popValue('htmlOptions', $groupOptions, array());
                if (!empty($options)) {
                    $groupOptions = TbArray::merge($options, $groupOptions);
                }
                $groupOptions = TbArray::copyValues(array('color', 'size', 'disabled'), $parentOptions, $groupOptions);
                $output .= self::buttonGroup($items, $groupOptions);
            }
            $output .= '</div>';
            return $output;
        }
        return '';
    }

    // Button dropdowns
    // http://getbootstrap.com/components/#btn-dropdowns
    // --------------------------------------------------

    /**
     * Generates a button with a dropdown menu.
     * @param string $label the button label text.
     * @param array $items the menu items.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated button.
     */
    public static function buttonDropdown($label, $items, $htmlOptions = array())
    {
        $htmlOptions['items'] = $items;
        $type = isset($htmlOptions['type']) ? $htmlOptions['type'] : self::BUTTON_TYPE_SUBMIT;
        $type = is_array($type) ? $type[0] : $type;
        return self::btn($type, $label, $htmlOptions);
    }

    /**
     * Generates a button with a split dropdown menu.
     * @param string $label the button label text.
     * @param array $items the menu items.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated button.
     */
    public static function splitButtonDropdown($label, $items, $htmlOptions = array())
    {
        $htmlOptions['split'] = true;
        return self::buttonDropdown($label, $items, $htmlOptions);
    }

    // Navs
    // http://getbootstrap.com/components/#nav
    // --------------------------------------------------

    /**
     * Generates a tab navigation.
     * @param array $items the menu items.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated menu.
     */
    public static function tabs($items, $htmlOptions = array())
    {
        return self::nav(self::NAV_TYPE_TABS, $items, $htmlOptions);
    }

    /**
     * Generates a stacked tab navigation.
     * @deprecated Style does not exist in BS3
     * @param array $items the menu items.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated menu.
     */
    public static function stackedTabs($items, $htmlOptions = array())
    {
        $htmlOptions['stacked'] = true;
        return self::tabs($items, $htmlOptions);
    }

    /**
     * Generates a pills navigation.
     * @param array $items the menu items.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated menu.
     */
    public static function pills($items, $htmlOptions = array())
    {
        return self::nav(self::NAV_TYPE_PILLS, $items, $htmlOptions);
    }

    /**
     * Generates a stacked pills navigation.
     * @param array $items the menu items.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated menu.
     */
    public static function stackedPills($items, $htmlOptions = array())
    {
        $htmlOptions['stacked'] = true;
        return self::pills($items, $htmlOptions);
    }

    /**
     * Generates a list navigation.
     * @param array $items the menu items.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated menu.
     */
    public static function navList($items, $htmlOptions = array())
    {
        foreach ($items as $i => $itemOptions) {
            if (is_string($itemOptions)) {
                continue;
            }
            if (!isset($itemOptions['url']) && !isset($itemOptions['items'])) {
                $label = TbArray::popValue('label', $itemOptions, '');
                $items[$i] = self::menuHeader($label, $itemOptions);
            }
        }
        return self::nav(self::NAV_TYPE_LIST, $items, $htmlOptions);
    }

    /**
     * Generates a navigation menu.
     * @param string $type the menu type.
     * @param array $items the menu items.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated menu.
     */
    public static function nav($type, $items, $htmlOptions = array())
    {
        self::addCssClass('nav', $htmlOptions);
        if (!empty($type)) {
            self::addCssClass('nav-' . $type, $htmlOptions);
        } else {
            self::addCssClass('navbar-nav', $htmlOptions);
        }
        $stacked = TbArray::popValue('stacked', $htmlOptions, false);
        if ($type !== self::NAV_TYPE_LIST && $stacked) {
            self::addCssClass('nav-stacked', $htmlOptions);
        }
        return self::menu($items, $htmlOptions);
    }

    /**
     * Generates a menu.
     * @param array $items the menu items.
     * @param array $htmlOptions additional HTML attributes.
     * @param integer $depth the current depth.
     * @return string the generated menu.
     */
    public static function menu(array $items, $htmlOptions = array(), $depth = 0)
    {
        // todo: consider making this method protected.
        if (!empty($items)) {
            $htmlOptions['role'] = 'menu';
            $output = self::openTag('ul', $htmlOptions);
            foreach ($items as $itemOptions) {
                if (is_string($itemOptions)) {
                    if ($itemOptions == '---') {
                        $output .= self::menuDivider();
                    } else {
                        $output .= $itemOptions;
                    }
                } else {
                    if (TbArray::popValue('visible', $itemOptions, true)  === false) {
                        continue;
                    }
                    // todo: consider removing the support for htmlOptions.
                    $options = TbArray::popValue('htmlOptions', $itemOptions, array());
                    if (!empty($options)) {
                        $itemOptions = TbArray::merge($options, $itemOptions);
                    }
                    $label = TbArray::popValue('label', $itemOptions, '');
                    if (TbArray::popValue('active', $itemOptions, false)) {
                        self::addCssClass('active', $itemOptions);
                    }
                    if (TbArray::popValue('disabled', $itemOptions, false)) {
                        self::addCssClass('disabled', $itemOptions);
                    }
                    if (!isset($itemOptions['linkOptions'])) {
                        $itemOptions['linkOptions'] = array();
                    }
                    $icon = TbArray::popValue('icon', $itemOptions);
                    if (!empty($icon)) {
                        $label = self::icon($icon) . ' ' . $label;
                    }
                    $items = TbArray::popValue('items', $itemOptions, array());
                    $url = TbArray::popValue('url', $itemOptions, false);
                    if (empty($items)) {
                        if (!$url) {
                            $output .= self::menuHeader($label);
                        } else {
                            $itemOptions['linkOptions']['tabindex'] = -1;
                            $output .= self::menuLink($label, $url, $itemOptions);
                        }
                    } else {
                        $output .= self::menuDropdown($label, $url, $items, $itemOptions, $depth);
                    }
                }
            }
            $output .= '</ul>';
            return $output;
        } else {
            return '';
        }
    }

    /**
     * Generates a menu link.
     * @param string $label the link label.
     * @param array $url the link url.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated menu item.
     */
    public static function menuLink($label, $url, $htmlOptions = array())
    {
        TbArray::defaultValue('role', 'menuitem', $htmlOptions);
        $linkOptions = TbArray::popValue('linkOptions', $htmlOptions, array());
        $content = self::link($label, $url, $linkOptions);
        return self::tag('li', $htmlOptions, $content);
    }

    /**
     * Generates a menu dropdown.
     * @param string $label the link label.
     * @param string $url the link URL.
     * @param array $items the menu configuration.
     * @param array $htmlOptions additional HTML attributes.
     * @param integer $depth the current depth.
     * @return string the generated dropdown.
     */
    protected static function menuDropdown($label, $url, $items, $htmlOptions, $depth = 0)
    {
        self::addCssClass($depth === 0 ? 'dropdown' : 'dropdown-submenu', $htmlOptions);
        TbArray::defaultValue('role', 'menuitem', $htmlOptions);
        $linkOptions = TbArray::popValue('linkOptions', $htmlOptions, array());
        $menuOptions = TbArray::popValue('menuOptions', $htmlOptions, array());
        self::addCssClass('dropdown-menu', $menuOptions);
        if ($depth === 0) {
            $defaultId = parent::ID_PREFIX . parent::$count++;
            TbArray::defaultValue('id', $defaultId, $menuOptions);
            $menuOptions['aria-labelledby'] = $menuOptions['id'];
            $menuOptions['role'] = 'menu';
        }
        $output = self::openTag('li', $htmlOptions);
        $output .= self::dropdownToggleMenuLink($label, $url, $linkOptions, $depth);
        $output .= self::menu($items, $menuOptions, $depth + 1);
        $output .= '</li>';
        return $output;
    }

    /**
     * Generates a menu header.
     * @param string $label the header text.
     * @param array $htmlOptions additional HTML options.
     * @return string the generated header.
     */
    public static function menuHeader($label, $htmlOptions = array())
    {
        self::addCssClass('dropdown-header', $htmlOptions);
        return self::tag('li', $htmlOptions, $label);
    }

    /**
     * Generates a menu divider.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated menu item.
     */
    public static function menuDivider($htmlOptions = array())
    {
        self::addCssClass('divider', $htmlOptions);
        return self::tag('li', $htmlOptions);
    }

    /**
     * Generates a tabbable tabs menu.
     * @param array $tabs the tab configurations.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated menu.
     */
    public static function tabbableTabs($tabs, $htmlOptions = array())
    {
        return self::tabbable(self::NAV_TYPE_TABS, $tabs, $htmlOptions);
    }

    /**
     * Generates a tabbable pills menu.
     * @param array $pills the pills.
     * @param array $htmlOptions additional HTML attributes.
     * @internal param array $tabs the tab configurations.
     * @return string the generated menu.
     */
    public static function tabbablePills($pills, $htmlOptions = array())
    {
        return self::tabbable(self::NAV_TYPE_PILLS, $pills, $htmlOptions);
    }

    /**
     * Generates a tabbable menu.
     * @param string $type the menu type.
     * @param array $tabs the tab configurations.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated menu.
     */
    public static function tabbable($type, $tabs, $htmlOptions = array())
    {
        self::addCssClass('tabbable', $htmlOptions);
        $placement = TbArray::popValue('placement', $htmlOptions);
        if (!empty($placement)) {
            self::addCssClass('tabs-' . $placement, $htmlOptions);
        }
        $menuOptions = TbArray::popValue('menuOptions', $htmlOptions, array());
        $contentOptions = TbArray::popValue('contentOptions', $htmlOptions, array());
        self::addCssClass('tab-content', $contentOptions);
        $panes = array();
        $items = self::normalizeTabs($tabs, $panes);
        $menu = self::nav($type, $items, $menuOptions);
        $content = self::tag('div', $contentOptions, implode('', $panes));
        $output = self::openTag('div', $htmlOptions);
        $output .= $placement === self::TABS_PLACEMENT_BELOW ? $content . $menu : $menu . $content;
        $output .= '</div>';
        return $output;
    }

    /**
     * Normalizes the tab configuration.
     * @param array $tabs the tab configuration.
     * @param array $panes a reference to the panes array.
     * @param integer $i the running index.
     * @return array the items.
     */
    protected static function normalizeTabs($tabs, &$panes, $i = 0)
    {
        $menuItems = array();
        foreach ($tabs as $tabOptions) {
            if (isset($tabOptions['visible']) && $tabOptions['visible'] === false) {
                continue;
            }
            $menuItem = array();
            $menuItem['icon'] = TbArray::popValue('icon', $tabOptions);
            $menuItem['label'] = TbArray::popValue('label', $tabOptions, '');
            $menuItem['active'] = TbArray::getValue('active', $tabOptions, false);
            $menuItem['disabled'] = TbArray::popValue('disabled', $tabOptions, false);
            $menuItem['linkOptions'] = TbArray::popValue('linkOptions', $tabOptions, array());
            $menuItem['htmlOptions'] = TbArray::popValue('htmlOptions', $tabOptions, array());
            $items = TbArray::popValue('items', $tabOptions, array());
            if (!empty($items)) {
                $menuItem['linkOptions']['data-bs-toggle'] = 'dropdown';
                $menuItem['items'] = self::normalizeTabs($items, $panes, $i);
            } else {
                $paneOptions = TbArray::popValue('paneOptions', $tabOptions, array());
                $id = $paneOptions['id'] = TbArray::popValue('id', $tabOptions, 'tab_' . ++$i);
                $menuItem['linkOptions']['data-bs-toggle'] = 'tab';
                $menuItem['url'] = '#' . $id;
                self::addCssClass('tab-pane', $paneOptions);
                if (TbArray::popValue('fade', $tabOptions, true)) {
                    self::addCssClass('fade', $paneOptions);
                }
                if (TbArray::popValue('active', $tabOptions, false)) {
                    self::addCssClass('active in', $paneOptions);
                }
                $paneContent = TbArray::popValue('content', $tabOptions, '');
                $panes[] = self::tag('div', $paneOptions, $paneContent);
            }
            $menuItems[] = $menuItem;
        }
        return $menuItems;
    }

    // Navbar
    // http://getbootstrap.com/components/#navbar
    // --------------------------------------------------

    /**
     * Generates a navbar.
     * @param string $content the navbar content.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated navbar.
     */
    public static function navbar($content, $htmlOptions = array())
    {
        self::addCssClass('navbar', $htmlOptions);
        $display = TbArray::popValue('display', $htmlOptions);
        if (!empty($display)) {
            self::addCssClass('navbar-' . $display, $htmlOptions);
        }
        $color = TbArray::popValue('color', $htmlOptions, 'default');
        if (!empty($color)) {
            self::addCssClass('navbar-' . $color, $htmlOptions);
        }
        $htmlOptions['role'] = 'navigation';
        $output = self::openTag('nav', $htmlOptions);
        $output .= $content;
        $output .= '</nav>';
        return $output;
    }

    /**
     * Generates a brand link for the navbar.
     * @param string $label the link label text.
     * @param string $url the link url.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated link.
     */
    public static function navbarBrandLink($label, $url, $htmlOptions = array())
    {
        self::addCssClass('navbar-brand', $htmlOptions);
        return self::link($label, $url, $htmlOptions);
    }

    /**
     * Generates a text for the navbar.
     * @param string $text the text.
     * @param array $htmlOptions additional HTML attributes.
     * @param string $tag the HTML tag.
     * @return string the generated text block.
     */
    public static function navbarText($text, $htmlOptions = array(), $tag = 'p')
    {
        self::addCssClass('navbar-text', $htmlOptions);
        return self::tag($tag, $htmlOptions, $text);
    }

    /**
     * Generates a menu divider for the navbar.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated divider.
     */
    public static function navbarMenuDivider($htmlOptions = array())
    {
        self::addCssClass('divider-vertical', $htmlOptions);
        return self::tag('li', $htmlOptions);
    }

    /**
     * Generates a navbar form.
     * @param mixed $action the form action URL.
     * @param string $method form method (e.g. post, get).
     * @param array $htmlOptions additional HTML attributes
     * @return string the generated form.
     */
    public static function navbarForm($action, $method = 'post', $htmlOptions = array())
    {
        self::addCssClass('navbar-form', $htmlOptions);
        return self::form($action, $method, $htmlOptions);
    }

    /**
     * Generates a navbar search form.
     * @param mixed $action the form action URL.
     * @param string $method form method (e.g. post, get).
     * @param array $htmlOptions additional HTML attributes
     * @return string the generated form.
     */
    public static function navbarSearchForm($action, $method = 'post', $htmlOptions = array())
    {
        self::addCssClass('navbar-form', $htmlOptions);
        return self::searchForm($action, $method, $htmlOptions);
    }

    /**
     * Generates a collapse element.
     * @param string $target the CSS selector for the target element.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated icon.
     */
    public static function navbarCollapseLink($target, $htmlOptions = array())
    {
        self::addCssClass('btn btn-navbar', $htmlOptions);
        $htmlOptions['type'] = 'button';
        $htmlOptions['data-bs-toggle'] = 'collapse';
        $htmlOptions['data-target'] = $target;
        self::addCssClass('navbar-toggle', $htmlOptions);
        $content = self::tag('span', array('class' => 'sr-only'), 'Toggle navigation');
        $content .= '<span class="navbar-toggler-icon"></span>';
        return self::tag('button', $htmlOptions, $content);
    }

    // Breadcrumbs
    // http://getbootstrap.com/components/#breadcrumbs
    // --------------------------------------------------

    /**
     * Generates a breadcrumb menu.
     * @param array $links the breadcrumb links.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated breadcrumb.
     */
    public static function breadcrumbs($links, $htmlOptions = array())
    {
        self::addCssClass('breadcrumb', $htmlOptions);
        $output = self::openTag('ol', $htmlOptions);
        foreach ($links as $label => $url) {
            if (is_string($label)) {
                $output .= self::openTag('li');
                $output .= self::link($label, $url);
                $output .= '</li>';
            } else {
                $output .= self::tag('li', array('class' => 'active'), $url);
            }
        }
        $output .= '</ol>';
        return $output;
    }

    // Pagination
    // http://getbootstrap.com/components/#pagination
    // --------------------------------------------------

    /**
     * Generates a pagination.
     * @param array $items the pagination buttons.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated pagination.
     */
    public static function pagination(array $items, $htmlOptions = array())
    {
        if (!empty($items)) {
            self::addCssClass('pagination', $htmlOptions);
            $size = TbArray::popValue('size', $htmlOptions);
            if (!empty($size)) {
                self::addCssClass('pagination-' . $size, $htmlOptions);
            }
            $align = TbArray::popValue('align', $htmlOptions);
            if (!empty($align)) {
                self::addCssClass('pagination-' . $align, $htmlOptions);
            }
            $output = self::openTag('ul', $htmlOptions);
            foreach ($items as $itemOptions) {
                // todo: consider removing the support for htmlOptions.
                $options = TbArray::popValue('htmlOptions', $itemOptions, array());
                if (!empty($options)) {
                    $itemOptions = TbArray::merge($options, $itemOptions);
                }
                $label = TbArray::popValue('label', $itemOptions, '');
                $url = TbArray::popValue('url', $itemOptions, false);
                $output .= self::paginationLink($label, $url, $itemOptions);
            }
            $output .= '</ul>';
            return $output;
        }
        return '';
    }

    /**
     * Generates a pagination link.
     * @param string $label the link label text.
     * @param mixed $url the link url.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated link.
     */
    public static function paginationLink($label, $url, $htmlOptions = array())
    {
        $linkOptions = TbArray::popValue('linkOptions', $htmlOptions, array());
        if (TbArray::popValue('active', $htmlOptions, false)) {
            self::addCssClass('active', $htmlOptions);
            $label .= ' ' . self::tag('span', array('class' => 'sr-only'), '(current)');
        }
        if (TbArray::popValue('disabled', $htmlOptions, false)) {
            self::addCssClass('disabled', $htmlOptions);
        }
        $content = self::link($label, $url, $linkOptions);
        return self::tag('li', $htmlOptions, $content);
    }

    /**
     * Generates a pager.
     * @param array $links the pager buttons.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated pager.
     */
    public static function pager(array $links, $htmlOptions = array())
    {
        if (!empty($links)) {
            self::addCssClass('pager', $htmlOptions);
            $output = self::openTag('ul', $htmlOptions);
            foreach ($links as $itemOptions) {
                // todo: consider removing the support for htmlOptions.
                $options = TbArray::popValue('htmlOptions', $itemOptions, array());
                if (!empty($options)) {
                    $itemOptions = TbArray::merge($options, $itemOptions);
                }
                $label = TbArray::popValue('label', $itemOptions, '');
                $url = TbArray::popValue('url', $itemOptions, false);
                $output .= self::pagerLink($label, $url, $itemOptions);
            }
            $output .= '</ul>';
            return $output;
        }
        return '';
    }

    /**
     * Generates a pager link.
     * @param string $label the link label text.
     * @param mixed $url the link url.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated link.
     */
    public static function pagerLink($label, $url, $htmlOptions = array())
    {
        $linkOptions = TbArray::popValue('linkOptions', $htmlOptions, array());
        if (TbArray::popValue('previous', $htmlOptions, false)) {
            self::addCssClass('previous', $htmlOptions);
        }
        if (TbArray::popValue('next', $htmlOptions, false)) {
            self::addCssClass('next', $htmlOptions);
        }
        if (TbArray::popValue('disabled', $htmlOptions, false)) {
            self::addCssClass('disabled', $htmlOptions);
        }
        $content = self::link($label, $url, $linkOptions);
        return self::tag('li', $htmlOptions, $content);
    }

    // Labels and badges
    // http://getbootstrap.com/components/#labels
    // --------------------------------------------------

    /**
     * Generates a label span.
     * @param string $label the label text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated span.
     */
    public static function labelTb($label, $htmlOptions = array())
    {
        self::addCssClass('label', $htmlOptions);
        $color = TbArray::popValue('color', $htmlOptions);
        if (!empty($color)) {
            self::addCssClass('label-' . $color, $htmlOptions);
        } else {
            self::addCssClass('label-default', $htmlOptions);
        }
        return self::tag('span', $htmlOptions, $label);
    }

    /**
     * Generates a badge span.
     * @param string $label the badge text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated span.
     */
    public static function badge($label, $htmlOptions = array())
    {
        self::addCssClass('badge', $htmlOptions);
        return self::tag('span', $htmlOptions, $label);
    }

    // Typography
    // http://getbootstrap.com/components/#jumbotron
    // http://getbootstrap.com/components/#page-header
    // --------------------------------------------------

    /**
     * Generates a jumbotron unit.
     * @param string $heading the heading text.
     * @param string $content the content text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated hero unit.
     */
    public static function heroUnit($heading, $content, $htmlOptions = array())
    {
        self::addCssClass('jumbotron', $htmlOptions);
        $headingOptions = TbArray::popValue('headingOptions', $htmlOptions, array());
        $output = self::openTag('div', $htmlOptions);
        $output .= self::tag('h1', $headingOptions, $heading);
        $output .= $content;
        $output .= '</div>';
        return $output;
    }

    /**
     * Generates a pager header.
     * @param string $heading the heading text.
     * @param string $subtext the subtext.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated pager header.
     */
    public static function pageHeader($heading, $subtext, $htmlOptions = array())
    {
        self::addCssClass('page-header', $htmlOptions);
        $headerOptions = TbArray::popValue('headerOptions', $htmlOptions, array());
        $subtextOptions = TbArray::popValue('subtextOptions', $htmlOptions, array());
        $output = self::openTag('div', $htmlOptions);
        $output .= self::openTag('h1', $headerOptions);
        $output .= parent::encode($heading) . ' ' . self::tag('small', $subtextOptions, $subtext);
        $output .= '</h1>';
        $output .= '</div>';
        return $output;
    }

    // Thumbnails
    // http://getbootstrap.com/components/#thumbnails
    // --------------------------------------------------

    /**
     * Generates a list of thumbnails.
     * @param array $thumbnails the list configuration.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated thumbnails.
     */
    public static function thumbnails(array $thumbnails, $htmlOptions = array())
    {
        if (!empty($thumbnails)) {
            self::addCssClass('thumbnails', $htmlOptions);
            $defaultSpan = TbArray::popValue('span', $htmlOptions, 3);
            $output = self::openTag('ul', $htmlOptions);
            foreach ($thumbnails as $thumbnailOptions) {
                if (isset($thumbnailOptions['visible']) && $thumbnailOptions['visible'] === false) {
                    continue;
                }
                // todo: consider removing the support for htmlOptions.
                $options = TbArray::popValue('htmlOptions', $thumbnailOptions, array());
                if (!empty($options)) {
                    $thumbnailOptions = TbArray::merge($options, $thumbnailOptions);
                }
                $thumbnailOptions['itemOptions']['span'] = TbArray::popValue('span', $thumbnailOptions, $defaultSpan);
                $caption = TbArray::popValue('caption', $thumbnailOptions, '');
                $captionOptions = TbArray::popValue('captionOptions', $thumbnailOptions, array());
                self::addCssClass('caption', $captionOptions);
                $label = TbArray::popValue('label', $thumbnailOptions);
                $labelOptions = TbArray::popValue('labelOptions', $thumbnailOptions, array());
                if (!empty($label)) {
                    $caption = self::tag('h3', $labelOptions, $label) . $caption;
                }
                $content = !empty($caption) ? self::tag('div', $captionOptions, $caption) : '';
                $image = TbArray::popValue('image', $thumbnailOptions);
                $imageOptions = TbArray::popValue('imageOptions', $thumbnailOptions, array());
                $imageAlt = TbArray::popValue('alt', $imageOptions, '');
                if (!empty($image)) {
                    $content = parent::image($image, $imageAlt, $imageOptions) . $content;
                }
                $url = TbArray::popValue('url', $thumbnailOptions, false);
                $output .= $url !== false
                    ? self::thumbnailLink($content, $url, $thumbnailOptions)
                    : self::thumbnail($content, $thumbnailOptions);
            }
            $output .= '</ul>';
            return $output;
        } else {
            return '';
        }
    }

    /**
     * Generates a thumbnail.
     * @param string $content the thumbnail content.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated thumbnail.
     */
    public static function thumbnail($content, $htmlOptions = array())
    {
        $itemOptions = TbArray::popValue('itemOptions', $htmlOptions, array());
        self::addCssClass('thumbnail', $htmlOptions);
        $output = self::openTag('li', $itemOptions);
        $output .= self::tag('div', $htmlOptions, $content);
        $output .= '</li>';
        return $output;
    }

    /**
     * Generates a link thumbnail.
     * @param string $content the thumbnail content.
     * @param mixed $url the url that the thumbnail links to.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated thumbnail.
     */
    public static function thumbnailLink($content, $url = '#', $htmlOptions = array())
    {
        $itemOptions = TbArray::popValue('itemOptions', $htmlOptions, array());
        self::addCssClass('thumbnail', $htmlOptions);
        $content = self::link($content, $url, $htmlOptions);
        return self::tag('li', $itemOptions, $content);
    }

    // Alerts
    // http://getbootstrap.com/components/#alerts
    // --------------------------------------------------

    /**
     * Generates an alert.
     * @param string $color the color of the alert.
     * @param string $message the message to display.
     * @param array $htmlOptions additional HTML options.
     * @return string the generated alert.
     */
    public static function alert($color, $message, $htmlOptions = array())
    {
        self::addCssClass('alert', $htmlOptions);
        if (!empty($color)) {
            self::addCssClass('alert-' . $color, $htmlOptions);
        }
        if (TbArray::popValue('in', $htmlOptions, true)) {
            self::addCssClass('in', $htmlOptions);
        }
        if (TbArray::popValue('block', $htmlOptions, false)) {
            self::addCssClass('alert-block', $htmlOptions);
        }
        if (TbArray::popValue('fade', $htmlOptions, true)) {
            self::addCssClass('fade', $htmlOptions);
        }
        $closeText = TbArray::popValue('closeText', $htmlOptions, self::CLOSE_TEXT);
        $closeOptions = TbArray::popValue('closeOptions', $htmlOptions, array());
        $closeOptions['dismiss'] = self::CLOSE_DISMISS_ALERT;
        $output = self::openTag('div', $htmlOptions);
        $output .= $closeText !== false ? self::closeLink($closeText, '#', $closeOptions) : '';
        $output .= $message;
        $output .= '</div>';
        return $output;
    }

    /**
     * Generates an alert block.
     * @param string $color the color of the alert.
     * @param string $message the message to display.
     * @param array $htmlOptions additional HTML options.
     * @return string the generated alert.
     */
    public static function blockAlert($color, $message, $htmlOptions = array())
    {
        $htmlOptions['block'] = true;
        return self::alert($color, $message, $htmlOptions);
    }

    // Progress bars
    // http://getbootstrap.com/components/#progress
    // --------------------------------------------------

    /**
     * Generates a progress bar.
     * @param integer $width the progress in percent.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated progress bar.
     */
    public static function progressBar($width = 0, $htmlOptions = array())
    {
        self::addCssClass('progress', $htmlOptions);
        if (TbArray::popValue('striped', $htmlOptions, false)) {
            self::addCssClass('progress-striped', $htmlOptions);
        }
        if (TbArray::popValue('animated', $htmlOptions, false)) {
            self::addCssClass('active', $htmlOptions);
        }
        $barOptions = TbArray::popValue('barOptions', $htmlOptions, array());
        $color = TbArray::popValue('color', $htmlOptions);
        if (!empty($color)) {
            $barOptions['color'] = $color;
        }
        $content = TbArray::popValue('content', $htmlOptions);
        if (!empty($content)) {
            $barOptions['content'] = $content;
        }
        $content = self::bar($width, $barOptions);
        return self::tag('div', $htmlOptions, $content);
    }

    /**
     * Generates a striped progress bar.
     * @param integer $width the progress in percent.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated progress bar.
     */
    public static function stripedProgressBar($width = 0, $htmlOptions = array())
    {
        $htmlOptions['striped'] = true;
        return self::progressBar($width, $htmlOptions);
    }

    /**
     * Generates an animated progress bar.
     * @param integer $width the progress in percent.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated progress bar.
     */
    public static function animatedProgressBar($width = 0, $htmlOptions = array())
    {
        $htmlOptions['animated'] = true;
        return self::stripedProgressBar($width, $htmlOptions);
    }

    /**
     * Generates a stacked progress bar.
     * @param array $bars the bar configurations.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated progress bar.
     */
    public static function stackedProgressBar(array $bars, $htmlOptions = array())
    {
        if (!empty($bars)) {
            self::addCssClass('progress', $htmlOptions);
            $output = self::openTag('div', $htmlOptions);
            $totalWidth = 0;
            foreach ($bars as $barOptions) {
                if (isset($barOptions['visible']) && !$barOptions['visible']) {
                    continue;
                }
                $width = TbArray::popValue('width', $barOptions, 0);
                $tmp = $totalWidth;
                $totalWidth += $width;
                if ($totalWidth > 100) {
                    $width = 100 - $tmp;
                }
                $output .= self::bar($width, $barOptions);
            }
            $output .= '</div>';
            return $output;
        }
        return '';
    }

    /**
     * Generates a progress bar.
     * @param integer $width the progress in percent.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated bar.
     */
    protected static function bar($width = 0, $htmlOptions = array())
    {
        self::addCssClass('progress-bar', $htmlOptions);
        $color = TbArray::popValue('color', $htmlOptions);
        if (!empty($color)) {
            self::addCssClass('progress-bar-' . $color, $htmlOptions);
        }
        if ($width < 0) {
            $width = 0;
        }
        if ($width > 100) {
            $width = 100;
        }
        if ($width > 0) {
            $width .= '%';
        }
        self::addCssStyle("width: {$width};", $htmlOptions);
        $content = TbArray::popValue('content', $htmlOptions, '');
        return self::tag('div', $htmlOptions, $content);
    }

    // Media objects
    // http://getbootstrap.com/components/#media
    // --------------------------------------------------

    /**
     * Generates a list of media objects.
     * @param array $items item configurations.
     * @param array $htmlOptions additional HTML attributes.
     * @return string generated list.
     */
    public static function mediaList(array $items, $htmlOptions = array())
    {
        if (!empty($items)) {
            self::addCssClass('media-list', $htmlOptions);
            $output = '';
            $output .= self::openTag('ul', $htmlOptions);
            $output .= self::medias($items, 'li');
            $output .= '</ul>';
            return $output;
        }
        return '';
    }

    /**
     * Generates multiple media objects.
     * @param array $items item configurations.
     * @param string $tag the item tag name.
     * @return string generated objects.
     */
    public static function medias(array $items, $tag = 'div')
    {
        if (!empty($items)) {
            $output = '';
            foreach ($items as $itemOptions) {
                if (isset($itemOptions['visible']) && $itemOptions['visible'] === false) {
                    continue;
                }
                // todo: consider removing the support for htmlOptions.
                $options = TbArray::popValue('htmlOptions', $itemOptions, array());
                if (!empty($options)) {
                    $itemOptions = TbArray::merge($options, $itemOptions);
                }
                $image = TbArray::popValue('image', $itemOptions);
                $heading = TbArray::popValue('heading', $itemOptions, '');
                $content = TbArray::popValue('content', $itemOptions, '');
                TbArray::defaultValue('tag', $tag, $itemOptions);
                $output .= self::media($image, $heading, $content, $itemOptions);
            }
            return $output;
        }
        return '';
    }

    /**
     * Generates a single media object.
     * @param string $image the image url.
     * @param string $heading the heading text.
     * @param string $content the content text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the media object.
     */
    public static function media($image, $heading, $content, $htmlOptions = array())
    {
        $tag = TbArray::popValue('tag', $htmlOptions, 'div');
        self::addCssClass('media', $htmlOptions);
        $linkOptions = TbArray::popValue('linkOptions', $htmlOptions, array());
        TbArray::defaultValue('pull', self::PULL_LEFT, $linkOptions);
        $imageOptions = TbArray::popValue('imageOptions', $htmlOptions, array());
        self::addCssClass('media-object', $imageOptions);
        $contentOptions = TbArray::popValue('contentOptions', $htmlOptions, array());
        self::addCssClass('media-body', $contentOptions);
        $headingOptions = TbArray::popValue('headingOptions', $htmlOptions, array());
        self::addCssClass('media-heading', $headingOptions);
        $items = TbArray::popValue('items', $htmlOptions);

        $output = self::openTag($tag, $htmlOptions);
        $alt = TbArray::popValue('alt', $imageOptions, '');
        $href = TbArray::popValue('href', $linkOptions, '#');
        if (!empty($image)) {
            $output .= self::link(parent::image($image, $alt, $imageOptions), $href, $linkOptions);
        }
        $output .= self::openTag('div', $contentOptions);
        $output .= self::tag('h4', $headingOptions, $heading);
        $output .= $content;
        if (!empty($items)) {
            $output .= self::medias($items);
        }
        $output .= '</div>';
        $output .= self::closeTag($tag);
        return $output;
    }

    // Misc
    // http://getbootstrap.com/components/#wells
    // --------------------------------------------------

    /**
     * Generates a well element.
     * @param string $content the well content.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated well.
     */
    public static function well($content, $htmlOptions = array())
    {
        self::addCssClass('well', $htmlOptions);
        $size = TbArray::popValue('size', $htmlOptions);
        if (!empty($size)) {
            self::addCssClass('well-' . $size, $htmlOptions);
        }
        return self::tag('div', $htmlOptions, $content);
    }

    /**
     * Generates a close link.
     * @param string $label the link label text.
     * @param mixed $url the link url.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated link.
     */
    public static function closeLink($label = self::CLOSE_TEXT, $url = '#', $htmlOptions = array())
    {
        $htmlOptions['href'] = $url;
        return self::close('a', $label, $htmlOptions);
    }

    /**
     * Generates a close button.
     * @param string $label the button label text.
     * @param array $htmlOptions the HTML options for the button.
     * @return string the generated button.
     */
    public static function closeButton($label = self::CLOSE_TEXT, $htmlOptions = array())
    {
        return self::close('button', $label, $htmlOptions);
    }

    /**
     * Generates a close element.
     * @param string $tag the tag name.
     * @param string $label the element label text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated element.
     */
    protected static function close($tag, $label, $htmlOptions = array())
    {
        self::addCssClass('close', $htmlOptions);
        $dismiss = TbArray::popValue('dismiss', $htmlOptions);
        if (!empty($dismiss)) {
            $htmlOptions['data-dismiss'] = $dismiss;
        }
        $htmlOptions['type'] = 'button';
        return self::tag($tag, $htmlOptions, $label);
    }

    /**
     * Generates a collapse link.
     * @param string $label the link label.
     * @param string $target the CSS selector.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated link.
     */
    public static function collapseLink($label, $target, $htmlOptions = array())
    {
        $htmlOptions['data-bs-toggle'] = 'collapse';
        return self::link($label, $target, $htmlOptions);
    }

    //
    // JAVASCRIPT
    // --------------------------------------------------

    // Modals
    // http://getbootstrap.com/javascript/#modals
    // --------------------------------------------------

    /**
     * Generates a modal header.
     * @param string $content the header content.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated header.
     */
    public static function modalHeader($content, $htmlOptions = array())
    {
        self::addCssClass('modal-header', $htmlOptions);
        $closeOptions = TbArray::popValue('closeOptions', $htmlOptions, array());
        $closeOptions['dismiss'] = 'modal';
        $closeOptions['data-bs-dismiss'] = 'modal';
        $closeOptions['class'] = 'btn-close';
        $headingOptions = TbArray::popValue('headingOptions', $htmlOptions, array());
        $closeLabel = TbArray::popValue('closeLabel', $htmlOptions, self::CLOSE_TEXT);
        $closeButton = self::closeButton($closeLabel, $closeOptions);
        self::addCssClass('modal-title', $headingOptions);
        $header = self::tag('h5', $headingOptions, $content);
        return self::tag('div', $htmlOptions, $header . $closeButton);
    }

    /**
     * Generates a modal body.
     * @param string $content the body content.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated body.
     */
    public static function modalBody($content, $htmlOptions = array())
    {
        self::addCssClass('modal-body', $htmlOptions);
        return self::tag('div', $htmlOptions, $content);
    }

    /**
     * Generates a modal footer.
     * @param string $content the footer content.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated footer.
     */
    public static function modalFooter($content, $htmlOptions = array())
    {
        self::addCssClass('modal-footer', $htmlOptions);
        return self::tag('div', $htmlOptions, $content);
    }

    // Tooltips and Popovers
    // http://getbootstrap.com/javascript/#tooltips
    // http://getbootstrap.com/javascript/#popovers
    // --------------------------------------------------

    /**
     * Generates a tooltip.
     * @param string $label the tooltip link label text.
     * @param mixed $url the link url.
     * @param string $content the tooltip content text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated tooltip.
     */
    public static function tooltip($label, $url, $content, $htmlOptions = array())
    {
        $htmlOptions['rel'] = 'tooltip';
        return self::tooltipPopover($label, $url, $content, $htmlOptions);
    }

    /**
     * Generates a popover.
     * @param string $label the popover link label text.
     * @param string $title the popover title text.
     * @param string $content the popover content text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated popover.
     */
    public static function popover($label, $title, $content, $htmlOptions = array())
    {
        $htmlOptions['rel'] = 'popover';
        $htmlOptions['data-content'] = $content;
        $htmlOptions['data-bs-toggle'] = 'popover';
        return self::tooltipPopover($label, '#', $title, $htmlOptions);
    }

    /**
     * Generates a base tooltip.
     * @param string $label the tooltip link label text.
     * @param mixed $url the link url.
     * @param string $title the tooltip title text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated tooltip.
     */
    protected static function tooltipPopover($label, $url, $title, $htmlOptions)
    {
        $htmlOptions['title'] = $title;
        if (TbArray::popValue('animation', $htmlOptions)) {
            $htmlOptions['data-animation'] = 'true';
        }
        if (TbArray::popValue('html', $htmlOptions)) {
            $htmlOptions['data-html'] = 'true';
        }
        $selector = TbArray::popValue('selector', $htmlOptions);
        if (!empty($selector)) {
            $htmlOptions['data-selector'] = $selector;
        }
        $placement = TbArray::popValue('placement', $htmlOptions);
        if (!empty($placement)) {
            $htmlOptions['data-placement'] = $placement;
        }
        $trigger = TbArray::popValue('trigger', $htmlOptions);
        if (!empty($trigger)) {
            $htmlOptions['data-trigger'] = $trigger;
        }
        if (($delay = TbArray::popValue('delay', $htmlOptions)) !== null) {
            $htmlOptions['data-delay'] = $delay;
        }
        return self::link($label, $url, $htmlOptions);
    }

    // Carousel
    // http://getbootstrap.com/javascript/#carousel
    // --------------------------------------------------

    /**
     * Generates an image carousel.
     * @param array $items the item configurations.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated carousel.
     */
    public static function carousel(array $items, $htmlOptions = array())
    {
        if (!empty($items)) {
            $id = TbArray::getValue('id', $htmlOptions, parent::ID_PREFIX . parent::$count++);
            TbArray::defaultValue('id', $id, $htmlOptions);
            $selector = '#' . $id;
            self::addCssClass('carousel', $htmlOptions);
            if (TbArray::popValue('slide', $htmlOptions, true)) {
                self::addCssClass('slide', $htmlOptions);
            }
            $interval = TbArray::popValue('data-interval', $htmlOptions);
            if ($interval) {
                $htmlOptions['data-interval'] = $interval;
            }
            $pause = TbArray::popValue('data-pause', $htmlOptions);
            if ($pause) {
                $htmlOptions['data-pause'] = $pause;
            }
            $indicatorOptions = TbArray::popValue('indicatorOptions', $htmlOptions, array());
            $innerOptions = TbArray::popValue('innerOptions', $htmlOptions, array());
            self::addCssClass('carousel-inner', $innerOptions);
            $prevOptions = TbArray::popValue('prevOptions', $htmlOptions, array());
            $prevLabel = TbArray::popValue('label', $prevOptions, '&lsaquo;');
            $nextOptions = TbArray::popValue('nextOptions', $htmlOptions, array());
            $nextLabel = TbArray::popValue('label', $nextOptions, '&rsaquo;');
            $hidePrevAndNext = TbArray::popValue('hidePrevAndNext', $htmlOptions, false);
            $output = self::openTag('div', $htmlOptions);
            $output .= self::carouselIndicators($selector, count($items), $indicatorOptions);
            $output .= self::openTag('div', $innerOptions);
            foreach ($items as $i => $itemOptions) {
                if (isset($itemOptions['visible']) && $itemOptions['visible'] === false) {
                    continue;
                }
                if ($i === 0) { // first item should be active
                    self::addCssClass('active', $itemOptions);
                }
                $content = TbArray::popValue('content', $itemOptions, '');
                $image = TbArray::popValue('image', $itemOptions, '');
                $imageOptions = TbArray::popValue('imageOptions', $itemOptions, array());
                $imageAlt = TbArray::popValue('alt', $imageOptions, '');
                if (!empty($image)) {
                    $content = parent::image($image, $imageAlt, $imageOptions);
                }
                $label = TbArray::popValue('label', $itemOptions);
                $caption = TbArray::popValue('caption', $itemOptions);
                $output .= self::carouselItem($content, $label, $caption, $itemOptions);
            }
            $output .= '</div>';
            if (!$hidePrevAndNext) {
                $output .= self::carouselPrevLink($prevLabel, $selector, $prevOptions);
                $output .= self::carouselNextLink($nextLabel, $selector, $nextOptions);
            }
            $output .= '</div>';
            return $output;
        }
        return '';
    }

    /**
     * Generates a carousel item.
     * @param string $content the content.
     * @param string $label the item label text.
     * @param string $caption the item caption text.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated item.
     */
    public static function carouselItem($content, $label, $caption, $htmlOptions = array())
    {
        self::addCssClass('item', $htmlOptions);
        $overlayOptions = TbArray::popValue('overlayOptions', $htmlOptions, array());
        self::addCssClass('carousel-caption', $overlayOptions);
        $labelOptions = TbArray::popValue('labelOptions', $htmlOptions, array());
        $captionOptions = TbArray::popValue('captionOptions', $htmlOptions, array());
        $url = TbArray::popValue('url', $htmlOptions, false);
        if ($url !== false) {
            $content = self::link($content, $url);
        }
        $output = self::openTag('div', $htmlOptions);
        $output .= $content;
        if (isset($label) || isset($caption)) {
            $output .= self::openTag('div', $overlayOptions);
            if ($label) {
                $output .= self::tag('h4', $labelOptions, $label);
            }
            if ($caption) {
                $output .= self::tag('p', $captionOptions, $caption);
            }
            $output .= '</div>';
        }
        $output .= '</div>';
        return $output;
    }

    /**
     * Generates a previous link for the carousel.
     * @param string $label the link label text.
     * @param mixed $url the link url.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated link.
     */
    public static function carouselPrevLink($label, $url = '#', $htmlOptions = array())
    {
        self::addCssClass('carousel-control left', $htmlOptions);
        $htmlOptions['data-slide'] = 'prev';
        return self::link($label, $url, $htmlOptions);
    }

    /**
     * Generates a next link for the carousel.
     * @param string $label the link label text.
     * @param mixed $url the link url.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated link.
     */
    public static function carouselNextLink($label, $url = '#', $htmlOptions = array())
    {
        self::addCssClass('carousel-control right', $htmlOptions);
        $htmlOptions['data-slide'] = 'next';
        return self::link($label, $url, $htmlOptions);
    }

    /**
     * Generates an indicator for the carousel.
     * @param string $target the CSS selector for the target element.
     * @param integer $numSlides the number of slides.
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated indicators.
     */
    public static function carouselIndicators($target, $numSlides, $htmlOptions = array())
    {
        self::addCssClass('carousel-indicators', $htmlOptions);
        $output = self::openTag('ol', $htmlOptions);
        for ($i = 0; $i < $numSlides; $i++) {
            $itemOptions = array('data-target' => $target, 'data-slide-to' => $i);
            if ($i === 0) {
                $itemOptions['class'] = 'active';
            }
            $output .= self::tag('li', $itemOptions, '', true);
        }
        $output .= '</ol>';
        return $output;
    }

    // UTILITIES
    // --------------------------------------------------

    /**
     * Appends new class names to the given options..
     * @param mixed $className the class(es) to append.
     * @param array $htmlOptions the options.
     * @return array the options.
     */
    public static function addCssClass($className, &$htmlOptions)
    {
        // Always operate on arrays
        if (is_string($className)) {
            $className = explode(' ', $className);
        }
        if (isset($htmlOptions['class'])) {
            $classes = array_filter(explode(' ', (string) $htmlOptions['class']));
            foreach ($className as $class) {
                $class = trim((string) $class);
                // Don't add the class if it already exists
                if (array_search($class, $classes) === false) {
                    $classes[] = $class;
                }
            }
            $className = $classes;
        }
        $htmlOptions['class'] = implode(' ', $className);
    }

    /**
     * Appends a CSS style string to the given options.
     * @param string $style the CSS style string.
     * @param array $htmlOptions the options.
     * @return array the options.
     */
    public static function addCssStyle($style, &$htmlOptions)
    {
        if (is_array($style)) {
            $style = implode('; ', $style);
        }
        $style = rtrim($style, ';');
        $htmlOptions['style'] = isset($htmlOptions['style'])
            ? rtrim((string) $htmlOptions['style'], ';') . '; ' . $style
            : $style;
    }

    /**
     * Adds the grid span class to the given options is applicable. BS3 no longer use span classes. During the BS3
     * transition, this will use the col-md-* CSS class.
     * @param array $htmlOptions the HTML attributes.
     * @deprecated
     */
    protected static function addSpanClass(&$htmlOptions)
    {
        // todo: remove this method
        $span = TbArray::popValue('span', $htmlOptions);
        if (!empty($span)) {
            self::addCssClass('col-md-' . $span, $htmlOptions);
        }
    }

    /**
     * Adds the appropriate column class to the given options applicable. The available columns are 'xs', 'sm', 'md',
     * 'lg' for extra small, small, medium, and large to be used for the appropriate screen sizes. It is also possible
     * to prevent your columns from stacking on smaller devices by combining a small column with a larger column:
     * <code>
     *  $htmlOptions = array(
     *      'xs' => 12,
     *      'md' => 8,
     * )
     * </code>
     * Both classes will be applied.
     * @param $htmlOptions
     */
    protected static function addColClass(&$htmlOptions)
    {
        $colSizes = array(self::COLUMN_SIZE_XS, self::COLUMN_SIZE_SM, self::COLUMN_SIZE_MD, self::COLUMN_SIZE_LG);

        // It's possible to stack an xs and md grid together
        foreach ($colSizes as $colSize) {
            $span = TbArray::popValue($colSize, $htmlOptions);
            if (!empty($span)) {
                self::addCssClass('col-' . $colSize . '-' . $span, $htmlOptions);
            }
        }
    }

    /**
     * Adds the pull class to the given options is applicable.
     * @param array $htmlOptions the HTML attributes.
     */
    protected static function addPullClass(&$htmlOptions)
    {
        $pull = TbArray::popValue('pull', $htmlOptions);
        if (!empty($pull)) {
            self::addCssClass('pull-' . $pull, $htmlOptions);
        }
    }

    /**
     * Adds the text align class to the given options if applicable.
     * @param array $htmlOptions the HTML attributes.
     */
    protected static function addTextAlignClass(&$htmlOptions)
    {
        $align = TbArray::popValue('textAlign', $htmlOptions);
        if (!empty($align)) {
            self::addCssClass('text-' . $align, $htmlOptions);
        }
    }

    /**
     * Switches the column class to and from the col width itself to its offset counterpart. For example, passing in
     * col-md-2 would be switched to col-md-offset-2
     * @param string $class
     * @return string
     */
    protected static function switchOffsetCol($class)
    {
        // todo: why would you want to do this
        if (strpos($class, 'offset') !== false) {
            return str_replace('-offset', '', $class);
        } else {
            preg_match('/^(col-.*-)([0-9]*)$/', $class, $matches);
            return $matches[1] . 'offset-' . $matches[2];
        }
    }

    /**
     * Nearly identical to {@link switchOffsetCol()} except it forces the class to be returned as its offset
     * counterpart. It is also safe to pass in a class that is already an offset and it will just re-return it. For
     * example, passing in col-md-2 will return col-md-offset-2. Passing in col-md-offset-4 will still return
     * col-md-offset-4.
     * @param string $class
     * @return string
     */
    protected static function switchColToOffset($class)
    {
        // todo: why would you want to do this
        if ((strpos($class, 'offset') === false) && (preg_match('/^(col-.*-)([0-9]*)$/', $class, $matches) > 0)) {
            return $matches[1] . 'offset-' . $matches[2];
        } else {
            return $class;
        }
    }

    /**
     * Nearly identical to {@link switchOffsetCol()} except it forces teh class to be returned as its column
     * (e.g. "span") width counterpart. It is also safe to pass in a class that is already the column width and it will
     * re-return it. For example, passing in col-md-offset-2 will return col-md-2. Passing in col-md-4 will still
     * return col-md-4.
     * @param string $class
     * @return string
     */
    protected static function switchOffsetToCol($class)
    {
        // todo: why would you want to do this
        if (strpos($class, 'offset') !== false) {
            return str_replace('-offset', '', $class);
        } else {
            return $class;
        }
    }

    /**
     * Returns the col-* classes
     * @param array $htmlOptions with "class" set
     * @return string
     */
    protected static function getColClasses($htmlOptions)
    {
        // todo: why would you want to do this
        $colClasses = array();
        if (isset($htmlOptions['class']) && !empty($htmlOptions['class'])) {
            $classes = explode(' ', (string) $htmlOptions['class']);
            foreach ($classes as $class) {
                if (substr($class, 0, 4) == 'col-') {
                    $colClasses[] = $class;
                }
            }
        }
        return implode(' ', array_unique($colClasses));
    }

    /**
     * Returns the col-* classes and removes the classes from $htmlOptions['class']
     * @param string $htmlOptions with class set
     * @return string
     */
    protected static function popColClasses(&$htmlOptions)
    {
        // todo: why would you want to do this
        $colClasses = array();
        $returnClasses = array();
        if (isset($htmlOptions['class']) && !empty($htmlOptions['class'])) {
            $classes = explode(' ', (string) $htmlOptions['class']);
            foreach ($classes as $class) {
                if (substr($class, 0, 4) == 'col-') {
                    $colClasses[] = $class;
                } elseif (!empty($class)) {
                    $returnClasses[] = $class;
                }
            }
            $htmlOptions['class'] = implode(' ', $returnClasses);
        }
        return implode(' ', array_unique($colClasses));
    }
}
