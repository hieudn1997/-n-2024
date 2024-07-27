<?php

if (!function_exists('removeNullish')) {
    /**
     * Removing nullish values from an array.
     *
     * @param  array  $origin
     */
    function removeNullish($origin = [])
    {
        return  array_filter($origin, fn ($value) => !is_null($value) && $value !== '');
    }
}

if (!function_exists('getRandomString')) {
    /**
     * Generate random string.
     *
     * @param  number  $length
     */
    //create code transfer money
    function getRandomString($length = 16, $salt = '')
    {
        return substr(str_shuffle(str_repeat($x = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $salt, ceil($length / strlen($x)))), 1, $length);
    }
}

if (!function_exists('formatDate')) {
    function formatDate($dateString, $from = "d/m/Y", $to = "Y-m-d")
    {
        $date = \DateTime::createFromFormat($from, $dateString);

        if ($date) {
            return $date->format($to);
        }

        return $dateString;
    }
}
