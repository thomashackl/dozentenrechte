#!/bin/sh

#
#  STEP 2:
#  convert all Stud.IP message strings into a binary format
#

LOCALE_RELATIVE_PATH="locale"

for language in en
do
    test -f "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/dozentenrechte.mo" && mv "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/dozentenrechte.mo" "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/dozentenrechte.mo.old"
    msgfmt "$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/dozentenrechte.po" --output-file="$LOCALE_RELATIVE_PATH/$language/LC_MESSAGES/dozentenrechte.mo"
done
