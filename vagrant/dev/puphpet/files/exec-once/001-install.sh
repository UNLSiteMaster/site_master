SITEMASTER_BASEDIR="/var/www"
SITEMASTER_INSTALL="scripts/install.php"

echo "installing sitemaster"

#Go to the basedir to preform commands.
cd $SITEMASTER_BASEDIR

git submodule init
git submodule update

php $SITEMASTER_INSTALL

#copy .htaccess
if [ ! -f /var/www/.htaccess ]; then
    echo "Creating .htaccess"
    cp /var/www/sample.htaccess /var/www/.htaccess
fi

#copy config
if [ ! -f /var/www/config.inc.php ]; then
    echo "Creating config.inc.php"
    cp /var/www/config.sample.php /var/www/config.inc.php
fi

echo "FINISHED installing sitemaster"
