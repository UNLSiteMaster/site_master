SITEMASTER_BASEDIR="/var/www/html"
SITEMASTER_INSTALL="scripts/install.php"

sudo cp ${SITEMASTER_BASEDIR}/scripts/example-upstart.conf /etc/init/sitemaster.conf
sudo start sitemaster
