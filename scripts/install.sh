#!/bin/bash

set -x
set -e

composer install
./scripts/install-pdf2txt.sh 
 
mkdir -p app/data
mkdir -p logs
php scripts/indexer.php

cp .ENV.example .ENV

git submodule update --init
