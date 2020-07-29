<?php
/**
 * @author Shubin Sergei <is.captain.fail@gmail.com>
 * @license GNU General Public License v3.0
 * 26.06.2020 2020
 */

if (!function_exists('cfLangBySlug')) {
    function cfLangBySlug($slug) {
        $locales = config('tramylap.locales');
        $locales = array_combine(array_column($locales, 'slug'), $locales);
        return $locales[$slug];
    }
}
