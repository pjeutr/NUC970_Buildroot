################################################################################
#
# php-meminfo
#
################################################################################

PHP_MEMINFO_VERSION = 1.1.1
PHP_MEMINFO_SITE = $(call github,BitOne,php-meminfo,v$(PHP_MEMINFO_VERSION))
PHP_MEMINFO_SUBDIR = extension/php7 
PHP_MEMINFO_CONF_OPTS = --with-php-config=$(STAGING_DIR)/usr/bin/php-config \
        --enable-meminfo
# phpize does the autoconf magic
PHP_MEMINFO_DEPENDENCIES = php host-autoconf
PHP_MEMINFO_LICENSE = PHP
PHP_MEMINFO_LICENSE_FILES = LICENSE


define PHP_MEMINFO_PHPIZE
	(cd $(@D)/extension/php7; \
		PHP_AUTOCONF=$(HOST_DIR)/usr/bin/autoconf \
		PHP_AUTOHEADER=$(HOST_DIR)/usr/bin/autoheader \
		$(STAGING_DIR)/usr/bin/phpize)
endef

PHP_MEMINFO_PRE_CONFIGURE_HOOKS += PHP_MEMINFO_PHPIZE

$(eval $(autotools-package))
