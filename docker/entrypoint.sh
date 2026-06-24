#!/bin/sh
set -e

# ---------------------------------------------------------------------------
# rbook Docker entrypoint
#
# Generates config.php from environment variables, ensures the Smarty
# compile directory exists and is writable, then starts Apache.
#
# Required environment variables:
#   DBHOST       – MySQL hostname (default: db)
#   DBNAME       – MySQL database name (default: rbook)
#   DBUSER       – MySQL username (default: rbook)
#   DBPASSWORD   – MySQL password (default: rbook)
#
# Optional environment variables:
#   APPROOT      – URL root path, must start and end with / (default: /)
#   APPTITLE     – Title shown in the browser (default: rbook)
#   SKIN         – Skin name under skins/ (default: default)
#   LANGUAGE     – Language code (default: en)
#   VIEW_POLICY  – 'member' or 'all' (default: member)
#   MAXINVITATIONS – Integer, 0 to disable (default: 0)
#   ALLOW_REGISTRATION – true/false (default: false)
#   IMAGEMAGICK  – Path to ImageMagick convert binary (optional)
#   IMPORTDIR    – Path to import drop-zone directory (optional)
#   DEBUG        – true/false (default: false)
# ---------------------------------------------------------------------------

DBHOST="${DBHOST:-db}"
DBNAME="${DBNAME:-rbook}"
DBUSER="${DBUSER:-rbook}"
DBPASSWORD="${DBPASSWORD:-rbook}"
APPROOT="${APPROOT:-/rbook/}"
APPTITLE="${APPTITLE:-rbook}"
SKIN="${SKIN:-default}"
LANGUAGE="${LANGUAGE:-en}"
VIEW_POLICY="${VIEW_POLICY:-member}"
MAXINVITATIONS="${MAXINVITATIONS:-0}"
ALLOW_REGISTRATION="${ALLOW_REGISTRATION:-false}"
DEBUG="${DEBUG:-false}"

CONFIG=/var/www/html/rbook/config.php

if [ -f "$CONFIG" ]; then
    echo "rbook: config.php already exists — skipping generation"
else

cat > "$CONFIG" <<PHP
<?php
if (!defined("DBHOST"))            define("DBHOST",       "${DBHOST}");
if (!defined("DBUSER"))            define("DBUSER",       "${DBUSER}");
if (!defined("DBPASSWORD"))        define("DBPASSWORD",   "${DBPASSWORD}");
if (!defined("DBNAME"))            define("DBNAME",       "${DBNAME}");
if (!defined("APPROOT"))           define("APPROOT",      "${APPROOT}");
if (!defined("STYLESHEET"))        define("STYLESHEET",   "style.css");
if (!defined("DISPLAYIFONLYONE")) define("DISPLAYIFONLYONE", true);
if (!defined("SKIN"))              define("SKIN",         "${SKIN}");
if (!defined("APPTITLE"))          define("APPTITLE",     "${APPTITLE}");
if (!defined("VIEW_POLICY"))       define("VIEW_POLICY",  "${VIEW_POLICY}");
if (!defined("MAXINVITATIONS"))    define("MAXINVITATIONS", ${MAXINVITATIONS});
if (!defined("ALLOW_REGISTRATION")) define("ALLOW_REGISTRATION", ${ALLOW_REGISTRATION});
if (!defined("LANGUAGE"))          define("LANGUAGE",     "${LANGUAGE}");
if (!defined("DEBUG"))             define("DEBUG",        ${DEBUG});
if (!defined("RECIPESUGGEST"))     define("RECIPESUGGEST", false);
PHP

if [ -n "${IMAGEMAGICK:-}" ]; then
    echo "define(\"IMAGEMAGICK\", \"${IMAGEMAGICK}\");" >> "$CONFIG"
fi
if [ -n "${IMPORTDIR:-}" ]; then
    echo "define(\"IMPORTDIR\", \"${IMPORTDIR}\");" >> "$CONFIG"
fi
if [ -n "${GOOGLE_ANALYTICS:-}" ]; then
    echo "define(\"GOOGLE_ANALYTICS\", \"${GOOGLE_ANALYTICS}\");" >> "$CONFIG"
fi

echo "?>" >> "$CONFIG"
echo "rbook: config.php generated from environment variables"

fi  # end config.php generation

# Ensure the Smarty compile cache directory exists and is writable
TEMPLATES_C="/var/www/html/rbook/skins/${SKIN}/templates_c"
mkdir -p "$TEMPLATES_C"
chown www-data:www-data "$TEMPLATES_C"
chmod 755 "$TEMPLATES_C"

echo "rbook: config.php written to ${CONFIG}, templates_c ready — starting Apache"
exec apache2-foreground
