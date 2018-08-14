#! /bin/bash

cd /var/www/vwgroup/data/www/dm.vw-servicepool.ru
FILE="/usr/bin/php /var/www/vwgroup/data/www/dm.vw-servicepool.ru/symfony sp:webSocket"
killall /usr/bin/php
MAX_TIME=598s
    $FILE &
    CHILD_PID=$!
    bash -c "sleep $MAX_TIME; kill -HUP $CHILD_PID" &
    wait $CHILD_PID
