<?php
namespace Phpxlsx\Utilities;
/**
 * PhpxlsxUtilities
 *
 * @category   Phpxlsx
 * @package    utilities
 * @copyright  Copyright (c) Narcea Labs SL
 *             (https://www.narcealabs.com)
 * @license    phpxlsx Community License
 * @link       https://www.phpxlsx.com
 */
class PhpxlsxUtilities
{
    /**
     * Check if string is UTF8
     *
     * @access public
     * @param string $string_input String to check
     * @static
     * @return boolean
     */
    public static function isUtf8($string_input)
    {
        $string = $string_input;

        $string = preg_replace("#[\x09\x0A\x0D\x20-\x7E]#", "", $string);
        $string = preg_replace("#[\xC2-\xDF][\x80-\xBF]#", "", $string);
        $string = preg_replace("#\xE0[\xA0-\xBF][\x80-\xBF]#", "", $string);
        $string = preg_replace("#[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}#", "", $string);
        $string = preg_replace("#\xED[\x80-\x9F][\x80-\xBF]#", "", $string);
        $string = preg_replace("#\xF0[\x90-\xBF][\x80-\xBF]{2}#", "", $string);
        $string = preg_replace("#[\xF1-\xF3][\x80-\xBF]{3}#", "", $string);
        $string = preg_replace("#\xF4[\x80-\x8F][\x80-\xBF]{2}#", "", $string);

        return ($string == "" ? true : false);
    }

    /**
     * Return a random number
     *
     * @access public
     * @static
     * @param int $min Min value
     * @param int $max Max value
     * @return int
     */
    public static function randomNumber($min, $max)
    {
        return mt_rand($min, $max);
    }

    /**
     * Return a uniqueid to be used in tags
     *
     * @access public
     * @static
     * @return string
     */
    public static function uniqueId()
    {
        $uniqueid = uniqid('phpxlsx_');

        return $uniqueid;
    }

}
