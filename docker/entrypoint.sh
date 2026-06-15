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
APPROOT="${APPROOT:-/}"
APPTITLE="${APPTITLE:-rbook}"
SKIN="${SKIN:-default}"
LANGUAGE="${LANGUAGE:-en}"
VIEW_POLICY="${VIEW_POLICY:-member}"
MAXINVITATIONS="${MAXINVITATIONS:-0}"
ALLOW_REGISTRATION="${ALLOW_REGISTRATION:-false}"
DEBUG="${DEBUG:-false}"

CONFIG=/var/www/html/config.php

cat > "$CONFIG" <<PHP
<?php
define("DBHOST",       "${DBHOST}");
define("DBUSER",       "${DBUSER}");
define("DBPASSWORD",   "${DBPASSWORD}");
define("DBNAME",       "${DBNAME}");
define("APPROOT",      "${APPROOT}");
define("STYLESHEET",   "style.css");
define("DISPLAYIFONLYONE", true);
define("SKIN",         "${SKIN}");
define("APPTITLE",     "${APPTITLE}");
define("VIEW_POLICY",  "${VIEW_POLICY}");
define("MAXINVITATIONS", ${MAXINVITATIONS});
define("ALLOW_REGISTRATION", ${ALLOW_REGISTRATION});
define("LANGUAGE",     "${LANGUAGE}");
define("DEBUG",        ${DEBUG});
define("RECIPESUGGEST", false);
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

# Ensure the Smarty compile cache directory exists and is writable
TEMPLATES_C="/var/www/html/skins/${SKIN}/templates_c"
mkdir -p "$TEMPLATES_C"
chown www-data:www-data "$TEMPLATES_C"
chmod 755 "$TEMPLATES_C"

echo "rbook: config.php written, templates_c ready — starting Apache"
exec apache2-foreground
