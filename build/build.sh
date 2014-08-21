#!/bin/sh
mkdir bin
rm bin/servicecheck.phar
php /usr/bin/phar.phar pack \
-f bin/servicecheck.phar \
-s pharStub.php \
-c gz \
-i ".+\.php|vendor" \
-x "\.git|tests?|\.DS_Store|README*|AUTHORS|CHANGES|Makefile|phpunit\.xml*|\.travis\.yml|composer\.+" \
./..
chmod +x bin/servicecheck.phar