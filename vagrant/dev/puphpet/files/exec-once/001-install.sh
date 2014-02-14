SITEMASTER_BASEDIR="/var/www/html"
SITEMASTER_INSTALL="scripts/install.php"

echo "installing sitemaster"

#Go to the basedir to perform commands.
cd $SITEMASTER_BASEDIR

git submodule init
git submodule update

php $SITEMASTER_INSTALL

#copy .htaccess
if [ ! -f ${SITEMASTER_BASEDIR}/.htaccess ]; then
    echo "Creating .htaccess"
    cp ${SITEMASTER_BASEDIR}/sample.htaccess ${SITEMASTER_BASEDIR}/.htaccess
fi

#copy config
if [ ! -f ${SITEMASTER_BASEDIR}/config.inc.php ]; then
    echo "Creating config.inc.php"
    cp ${SITEMASTER_BASEDIR}/config.sample.php ${SITEMASTER_BASEDIR}/config.inc.php
fi

echo "FINISHED installing sitemaster"
