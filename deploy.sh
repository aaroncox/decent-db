#!/bin/bash
rsync ./ explorers:/var/www/com_decentdb/ --rsh ssh --rsync-path="sudo rsync" --recursive --perms --delete --verbose --exclude=.git* --exclude=cache --exclude=vendor/ezyang/htmlpurifier/library/HTMLPurifier/DefinitionCache/Serializer/ --exclude=app/storage/views/*.php --checksum -a
