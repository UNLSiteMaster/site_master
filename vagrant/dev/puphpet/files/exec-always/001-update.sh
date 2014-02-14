SITEMASTER_BASEDIR="/var/www/html"
SITEMASTER_UPDATE="scripts/update.php"

echo "updating sitemaster"

#Go to the basedir to perform commands.
cd $SITEMASTER_BASEDIR

php $SITEMASTER_UPDATE

echo "FINISHED updating sitemaster"
