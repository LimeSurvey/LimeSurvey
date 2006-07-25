PACKAGE = php-gettext-$(VERSION)
VERSION = 1.0.7

DIST_FILES = \
	gettext.php \
	gettext.inc \
	streams.php \
	AUTHORS     \
	ChangeLog   \
	README      \
	COPYING     \
	Makefile    \
	examples/index.php    \
	examples/pigs_dropin.php    \
	examples/pigs_fallback.php    \
	examples/locale/sr_CS/LC_MESSAGES/messages.po \
	examples/locale/sr_CS/LC_MESSAGES/messages.mo \
	examples/locale/de_CH/LC_MESSAGES/messages.po \
	examples/locale/de_CH/LC_MESSAGES/messages.mo \
	examples/update

dist:
	if [ -d $(PACKAGE) ]; then \
	    rm -rf $(PACKAGE); \
	fi; \
	mkdir $(PACKAGE); \
	if [ -d $(PACKAGE) ]; then \
	    cp -rp --parents $(DIST_FILES) $(PACKAGE); \
	    tar cvzf $(PACKAGE).tar.gz $(PACKAGE); \
	    rm -rf $(PACKAGE); \
	fi;

