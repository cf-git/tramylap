<?php
/**
 * @author Shubin Sergei <is.captain.fail@gmail.com>
 * @license MIT
 * 26.06.2020 2020
 */

if (!function_exists('cfLangBySlug')) {
    function cfLangBySlug($slug) {
        $locales = config('tramylap.locales');
        $locales = array_combine(array_column($locales, 'slug'), $locales);
        return $locales[$slug];
    }
}
