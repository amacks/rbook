<?php
/*
 * rbook Recipe Management System
 * Copyright (C) 2005 Andrew Violette andrew@andrewviolette.net
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

/**
 * Smarty 4 custom resource handler for the 'skin:' template scheme.
 *
 * Replaces the Smarty 2 function-array approach:
 *   $smarty->register_resource('skin', array("skin_get_template", ...))
 *
 * Templates referenced as {include file="skin:header.tpl"} are resolved
 * through getTemplateName() (defined in helpers/ui.php) which handles
 * skin fallback to the default skin directory.
 */
class SkinResource extends Smarty_Resource_Custom {

    /**
     * Populates $source with the template content and $mtime with the
     * file's modification timestamp.
     *
     * @param string $name     The template name after 'skin:' (e.g. "header.tpl")
     * @param string $source   Output: template source content
     * @param int    $mtime    Output: file modification time (unix timestamp)
     */
    protected function fetch($name, &$source, &$mtime) {
        $path = getTemplateName($name);
        if (!file_exists($path)) {
            $source = null;
            $mtime  = null;
            return;
        }
        $source = file_get_contents($path);
        $mtime  = filemtime($path);
    }
}
