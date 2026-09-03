<?php
namespace Phpxlsx\Elements;

use Phpxlsx\Utilities\XmlUtilities;

/**
 * Create tag elements
 *
 * @category   Phpxlsx
 * @package    elements
 * @copyright  Copyright (c) Narcea Labs SL
 *             (https://www.narcealabs.com)
 * @license    phpxlsx Community License
 * @link       https://www.phpxlsx.com
 */
class CreateElement
{
    /**
     * Parse and clean a text string to be added
     *
     * @access public
     * @param string $content
     * @return string
     */
    public function parseAndCleanTextString($content) {
        $xmlUtilities = new XmlUtilities();
        $content = $xmlUtilities->parseAndCleanTextString($content);

        return $content;
    }

    /**
     * Transform a string position to int starting from 0
     *
     * @access public
     * @param string $value
     * @return int
     */
    public function wordToInt($value) {
        $valueInt = 0;

        $strValue = array_reverse(str_split($value));

        for ($i = 0; $i < strlen($value); $i++) {
            $valueInt += (ord($strValue[$i])-64) * pow(26,$i);
        }
        // 0 is the first value, not 1
        $valueInt--;

        return $valueInt;
    }
}