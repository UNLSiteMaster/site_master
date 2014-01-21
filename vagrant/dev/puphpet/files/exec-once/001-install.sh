SITEMASTER_BASEDIR="../../../../../"
SITEMASTER_INSTALL="scripts/install.php"

echo "installing sitemaster"

#Go to the basedir to preform commands.
cd $SITEMASTER_BASEDIR

php $SITEMASTER_INSTALL

#copy .htaccess
if [ ! -f .htaccess ]; then
    echo "Creating .htaccess"
    cp sample.htaccess .htaccess
fi

#copy config
if [ ! -f config.inc.php ]; then
    echo "Creating config.inc.php"
    cp config.sample.php config.inc.php
fi

echo "FINISHED installing sitemaster"
