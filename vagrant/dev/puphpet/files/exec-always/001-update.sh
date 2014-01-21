SITEMASTER_BASEDIR="/var/www"
SITEMASTER_UPDATE="scripts/update.php"

echo "updating sitemaster"

#Go to the basedir to preform commands.
cd $SITEMASTER_BASEDIR

php $SITEMASTER_UPDATE

echo "FINISHED updating sitemaster"
