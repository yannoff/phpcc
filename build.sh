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

# Ensure dependencies are up-to-date
offenbach install --no-dev --no-interaction --optimize-autoloader | awk '!/Deprec/' | tr -d "\r"

# Update version in main application bootstrap file
sed -i "s/\$VERSION *=.*/\$VERSION = '$version';/" $main

args=()

# Add src/ & vendor/ as source directories
args+=(--dir src:php)
args+=(--dir vendor:php)

# Set bootstrap & output properties
args+=(--main $main)
args+=(--output $phar)

# Set the legal banner file
args+=(--banner .banner)

# Add license file
args+=(--file LICENSE)

# Add archive metadata properties
args+=(--meta license:MIT)
args+=(--meta author:yannoff)
args+=(--meta copyright:yannoff)

# Build, display and execute compiling command
set -- php -dphar.readonly=0 bin/compile.php "${args[@]}"
echo "$@"
"$@"
