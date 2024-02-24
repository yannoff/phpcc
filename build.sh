#!/bin/bash

#
# This file is part of the PHP Code Compiler project
#
# Copyright (c) Yannoff (https://github.com/yannoff)
#
# @project   PHP Code Compiler (yannoff/phpcc)
# @homepage  https://github.com/yannoff/phpcc
# @license   https://github.com/yannoff/phpcc/blob/main/LICENSE
#
# For the full copyright and license information, please view
# the LICENSE file that was distributed with this source code.
#

version=$1
phar=bin/phpcc
main=bin/compile.php

if [ -z "$version" ]
then
    echo "Error: missing version argument."
    exit 1
fi

sed -i "s/\$VERSION *=.*/\$VERSION = '$version';/" $main

php -dphar.readonly=0 bin/compile.php -d src:php -d vendor:php -e $main -o $phar -b .banner
