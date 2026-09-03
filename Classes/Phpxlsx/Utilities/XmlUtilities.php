<?php
namespace Phpxlsx\Utilities;
/**
 * XML functions
 *
 * @category   Phpxlsx
 * @package    utilities
 * @copyright  Copyright (c) Narcea Labs SL
 *             (https://www.narcealabs.com)
 * @license    phpxlsx Community License
 * @link       https://www.phpxlsx.com
 */
class XmlUtilities
{
    /**
     * Invalid XML characters regex
     *
     * @access public
     * @static
     * @var string
     */
    public static $invalidXmlChars = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/';

    /**
     *
     * @access public
     * @static
     * @var mixed
     */
    public static $xmlFlags = null;

    /**
     * Sets LIBXML_PARSEHUGE option
     *
     * @access public
     * @static
     */
    public static function enableHugeXmlMode() {
        self::$xmlFlags = LIBXML_PARSEHUGE;
    }

    /**
     * Generates a DOM document from a XML string
     *
     * @param string $xml XML content
     */
    public function generateDomDocument($xml)
    {
        $domDocument = new \DOMDocument();
        if (PHP_VERSION_ID < 80000) {
            $optionEntityLoader = libxml_disable_entity_loader(true);
        }
        if (!self::$xmlFlags) {
            $domDocument->loadXML($xml);
        } else {
            $domDocument->loadXML($xml, self::$xmlFlags);
        }
        if (PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader($optionEntityLoader);
        }

        return $domDocument;
    }

    /**
     * Generates a SimpleXMLElement from an XML string
     *
     * @param string $xml XML content
     * @return \SimpleXMLElement
     */
    public function generateSimpleXmlElement($xml)
    {
        if (PHP_VERSION_ID < 80000) {
            $optionEntityLoader = libxml_disable_entity_loader(true);
        }
        if (!self::$xmlFlags) {
            $simpleXmlElement = simplexml_load_string($xml);
        } else {
            $simpleXmlElement = simplexml_load_string($xml, 'SimpleXMLElement', self::$xmlFlags);
        }
        if (PHP_VERSION_ID < 80000) {
            libxml_disable_entity_loader($optionEntityLoader);
        }

        return $simpleXmlElement;
    }

    /**
     * Parses and clean a text string to be added
     *
     * @access protected
     * @param string $content
     * @return string
     */
    public function parseAndCleanTextString($content)
    {
        $content = htmlspecialchars($content);

        // cleans UTF-8 charset removing not UTF-8 valid chars
        if (\Phpxlsx\Create\CreateXlsx::$cleanUTF8) {
            // removes invalid XML characters
            $content = preg_replace(
                self::$invalidXmlChars,
                '',
                $content
            );
        }

        return $content;
    }
}