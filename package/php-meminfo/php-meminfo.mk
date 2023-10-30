################################################################################
#
# php-meminfo
#
################################################################################

PHP_MEMINFO_VERSION = 1.1.1
PHP_MEMINFO_SITE = $(call github,BitOne,php-meminfo,v$(PHP_MEMINFO_VERSION))
PHP_MEMINFO_CONF_OPTS = --enable-meminfo
# phpize does the autoconf magic
PHP_MEMINFO_DEPENDENCIES = php host-autoconf
PHP_MEMINFO_LICENSE = PHP
PHP_MEMINFO_LICENSE_FILES = LICENSE

https://github.com/BitOne/php-meminfo/archive/refs/tags/v1.1.1.tar.gz

define PHP_MEMINFO_PHPIZE
	(cd $(@D); \
		PHP_AUTOCONF=$(HOST_DIR)/usr/bin/autoconf \
		PHP_AUTOHEADER=$(HOST_DIR)/usr/bin/autoheader \
		$(STAGING_DIR)/usr/bin/phpize)
endef

PHP_MEMINFO_PRE_CONFIGURE_HOOKS += PHP_MEMINFO_PHPIZE

$(eval $(autotools-package))
