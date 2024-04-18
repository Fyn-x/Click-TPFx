<?php
if (!function_exists('cleanData')) {
    function cleanData($data)
    {
        return htmlentities(preg_replace('/[^A-Za-z0-9 ]/', '', $data));
    }
}

if (!function_exists('cleanPhoneNumber')) {
    function cleanPhoneNumber($phoneNumber)
    {
        return preg_replace('/^(\+628|\+6208|628|08)/', '8', $phoneNumber);
    }
}
