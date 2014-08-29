SITEMASTER_BASEDIR="/var/www/html"
SITEMASTER_INSTALL="scripts/install.php"

cp ${SITEMASTER_BASEDIR}/scripts/example-upstart.conf /etc/init/sitemaster.conf
start sitemaster
