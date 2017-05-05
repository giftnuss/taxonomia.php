
all:
	php -dzend_extension=xdebug.so vendor/bin/phpunit  \
		--coverage-html=cover_db
	firefox cover_db/index.html &

