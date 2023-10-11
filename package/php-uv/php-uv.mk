################################################################################
#
# php-yaml
#
################################################################################

PHP_UV_VERSION = 0.2.4
PHP_UV_SOURCE = uv-$(PHP_UV_VERSION).tgz
PHP_UV_SITE = https://pecl.php.net/get
PHP_UV_CONF_OPTS = --with-php-config=$(STAGING_DIR)/usr/bin/php-config \
	--with-uv=$(STAGING_DIR)/usr
# phpize does the autoconf magic
PHP_UV_DEPENDENCIES = libuv php host-autoconf
PHP_UV_LICENSE = MIT
PHP_UV_LICENSE_FILES = LICENSE

define PHP_UV_PHPIZE
	(cd $(@D); \
		PHP_AUTOCONF=$(HOST_DIR)/usr/bin/autoconf \
		PHP_AUTOHEADER=$(HOST_DIR)/usr/bin/autoheader \
		$(STAGING_DIR)/usr/bin/phpize)
endef

PHP_UV_PRE_CONFIGURE_HOOKS += PHP_UV_PHPIZE

$(eval $(autotools-package))
