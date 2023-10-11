################################################################################
#
# php-event
#
################################################################################

PHP_EVENT_VERSION = 3.0.6
PHP_EVENT_SOURCE = event-$(PHP_EVENT_VERSION).tgz
PHP_EVENT_SITE = https://pecl.php.net/get
PHP_EVENT_CONF_OPTS = --with-php-config=$(STAGING_DIR)/usr/bin/php-config \
	--with-event-libevent-dir=$(STAGING_DIR)/usr
# phpize does the autoconf magic
PHP_EVENT_DEPENDENCIES = libevent php host-autoconf
PHP_EVENT_LICENSE = PHP
PHP_EVENT_LICENSE_FILES = LICENSE

define PHP_EVENT_PHPIZE
	(cd $(@D); \
		PHP_AUTOCONF=$(HOST_DIR)/usr/bin/autoconf \
		PHP_AUTOHEADER=$(HOST_DIR)/usr/bin/autoheader \
		$(STAGING_DIR)/usr/bin/phpize)
endef

PHP_EVENT_PRE_CONFIGURE_HOOKS += PHP_EVENT_PHPIZE

$(eval $(autotools-package))
