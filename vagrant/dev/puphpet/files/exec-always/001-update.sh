SITEMASTER_BASEDIR="/var/www/html"
SITEMASTER_UPDATE="scripts/update.php"
SITEMASTER_UPDATE_LIBS="scripts/update_libs.php"

echo "updating sitemaster"

#Go to the basedir to perform commands.
cd $SITEMASTER_BASEDIR

php $SITEMASTER_UPDATE
php $SITEMASTER_UPDATE_LIBS

echo "FINISHED updating sitemaster"
