PORT="8010"

URL="http://localhost:${PORT}/"
SEARCH="Config\:\:set('URL', '.*')"
REPLACE="Config\:\:set('URL', 'http:\/\/localhost:${PORT}\/')"

sed -i  "s/${SEARCH}/${REPLACE}/g" /var/www/config.inc.php

echo "--------------"
echo "URL has been set to "$URL
echo "--------------"