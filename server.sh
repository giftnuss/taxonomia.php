#!/bin/bash

set -x
set -e

. .ENV
(cd public ; php -S 127.0.0.1:8000 index.php )
