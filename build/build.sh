#!/bin/sh
mkdir bin
php /usr/bin/phar.phar pack \
-f bin/servicecheck.phar \
-s pharStub.php \
-c gz \
-i "index\.php|src|vendor" \
-x "\.git|tests?" \
./..
chmod +x bin/servicecheck.phar