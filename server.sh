#!/bin/bash

# during development, this makes sense
# rm app/data/taxonomia.sqlite

(cd public ; php -S 127.0.0.1:8000 index.php)
