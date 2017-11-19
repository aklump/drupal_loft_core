<?php


namespace AKlump\LoftLib\Component\Bash;

// Black        0;30     Dark Gray     1;30
// Red          0;31     Light Red     1;31
// Green        0;32     Light Green   1;32
// Brown/Orange 0;33     Yellow        1;33
// Blue         0;34     Light Blue    1;34
// Purple       0;35     Light Purple  1;35
// Cyan         0;36     Light Cyan    1;36
// Light Gray   0;37     White         1;37

class Color {

    protected static $colors = array(
        'black' => array(30, 0),
        'red' => array(31, 0),
        'green' => array(32, 0),
        'brown' => array(33, 0),
        'orange' => array(33, 0),
        'blue' => array(34, 0),
        'purple' => array(35, 0),
        'cyan' => array(36, 0),
        'light gray' => array(37, 0),
        'dark grey' => array(30, 1),
        'dark gray' => array(30, 1),
        'light red' => array(31, 1),
        'light green' => array(32, 1),
        'yellow' => array(33, 1),
        'light blue' => array(34, 1),
        'light purple' => array(35, 1),
        'pink' => array(35, 1),
        'light cyan' => array(36, 1),
        'white' => array(37, 1),
    );

    public static function wrap($color, $string, $intensity = null)
    {
        $color = strtolower($color);
        if (!isset(static::$colors[$color])) {
            throw new \InvalidArgumentException("Unknown color \"$color\".");
        }
        list ($code, $intensityDefault) = static::$colors[$color];
        $intensity = $intensity ? $intensity : $intensityDefault;

        return '\\033[' . $intensity . ';' . $code . 'm' . $string . '\\033[0m';
    }
}
