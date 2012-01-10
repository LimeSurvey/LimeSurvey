<script src="<?php echo Yii::app()->getConfig('adminscripts'); ?>kcfinder/js/jquery.js" type="text/javascript"></script>
<script src="<?php echo Yii::app()->getConfig('adminscripts'); ?>kcfinder/js/jquery.rightClick.js" type="text/javascript"></script>
<script src="<?php echo Yii::app()->getConfig('adminscripts'); ?>kcfinder/js/jquery.drag.js" type="text/javascript"></script>
<script src="<?php echo Yii::app()->getConfig('adminscripts'); ?>kcfinder/js/helper.js" type="text/javascript"></script>
<script src="<?php echo Yii::app()->getConfig('adminscripts'); ?>kcfinder/js/browser/joiner.php" type="text/javascript"></script>
<script src="<?php echo Yii::app()->createUrl("admin/kcfinder/js_localize?lng={$this->lang}"); ?>" type="text/javascript"></script>
<?php IF (isset($this->opener['TinyMCE']) && $this->opener['TinyMCE']): ?>
<script src="<?php echo $this->config['_tinyMCEPath'] ?>/tiny_mce_popup.js" type="text/javascript"></script>
<?php ENDIF ?>
<?php IF (file_exists(ROOT . "/scripts/admin/kcfinder/themes/{$this->config['theme']}/init.js")): ?>
<script src="<?php echo Yii::app()->getConfig('adminscripts'); ?>kcfinder/themes/<?php echo $this->config['theme']; ?>/init.js" type="text/javascript"></script>
<?php ENDIF ?>
<script type="text/javascript">
browser.support.chromeFrame = <?php echo (strpos(strtolower($_SERVER['HTTP_USER_AGENT']), " chromeframe") !== false) ? "true" : "false" ?>;
browser.support.zip = <?php echo (class_exists('ZipArchive') && !$this->config['denyZipDownload']) ? "true" : "false" ?>;
browser.lang = "<?php echo text::jsValue($this->lang) ?>";
browser.type = "<?php echo text::jsValue($this->type) ?>";
browser.theme = "<?php echo text::jsValue($this->config['theme']) ?>";
browser.readonly = <?php echo $this->config['readonly'] ? "true" : "false" ?>;
browser.dir = "<?php echo text::jsValue($this->session['dir']) ?>";
browser.uploadURL = "<?php echo text::jsValue($this->config['uploadURL']) ?>";
browser.thumbsURL = browser.uploadURL + "/<?php echo text::jsValue($this->config['thumbsDir']) ?>";
<?php IF (isset($this->get['opener']) && strlen($this->get['opener'])): ?>
browser.opener.name = "<?php echo text::jsValue($this->get['opener']) ?>";
<?php ENDIF ?>
<?php IF (isset($this->opener['CKEditor']['funcNum']) && $this->opener['CKEditor']['funcNum']): ?>
browser.opener.CKEditor = {};
browser.opener.CKEditor.funcNum = <?php echo $this->opener['CKEditor']['funcNum'] ?>;
<?php ENDIF ?>
<?php IF (isset($this->opener['TinyMCE']) && $this->opener['TinyMCE']): ?>
browser.opener.TinyMCE = true;
<?php ENDIF ?>
_.kuki.domain = "<?php echo text::jsValue($this->config['cookieDomain']) ?>";
_.kuki.path = "<?php echo text::jsValue($this->config['cookiePath']) ?>";
_.kuki.prefix = "<?php echo text::jsValue($this->config['cookiePrefix']) ?>";
$(document).ready(function() {
    browser.resize();
    browser.init();
    $('#all').css('visibility', 'visible');
});
$(window).resize(browser.resize);
</script>
