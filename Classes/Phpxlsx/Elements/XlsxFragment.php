<?php
namespace Phpxlsx\Elements;

use Phpxlsx\Create\CreateXlsx;
use Phpxlsx\Utilities\XmlUtilities;

/**
 * Creates a XLSX fragment to be inserted elsewhere
 *
 * @category   Phpxlsx
 * @package    elements
 * @copyright  Copyright (c) Narcea Labs SL
 *             (https://www.narcealabs.com)
 * @license    phpxlsx Community License
 * @link       https://www.phpxlsx.com
 */
class XlsxFragment extends CreateXlsx
{
    /**
     *
     * @access public
     * @var string
     */
    public $xlsxML;

    /**
     *
     * @access public
     * @var array
     */
    public $externalRelationships;

    /**
     * Construct
     *
     * @access public
     */
    public function __construct()
    {
        $this->xlsxML = '';
        $this->externalRelationships = array();
        $this->xmlUtilities = new XmlUtilities();
    }

    /**
     * Returns current XML
     *
     * @access public
     * @return string current XML
     */
    public function __toString()
    {
        return $this->xlsxML;
    }

    /**
     * Getter externalRelationships
     *
     * @access public
     * @return array
     */
    public function getExternalRelationships()
    {
        return $this->externalRelationships;
    }

    /**
     * Adds a new externalRelationships
     *
     * @access public
     * @param array $relationship new relationship
     */
    public function addExternalRelationshipFragment($relationship)
    {
        $this->externalRelationships[] = $relationship;
    }

    /**
     * Returns the block content
     *
     * @access public
     * @return \DOMNodeList
     */
    public function blockXlsxXML()
    {
        $tags = '//a:p';

        return $this->queryNodes($tags);
    }

    /**
     * Returns the inline content
     *
     * @access public
     * @return \DOMNodeList
     */
    public function inlineXlsxXML()
    {
        $tags = '//a:r | //a:br | //a14:m';

        return $this->queryNodes($tags);
    }

    /**
     * Returns query nodes
     *
     * @access protected
     * @param string $tags
     * @return \DOMNodeList
     */
    protected function queryNodes($tags)
    {
        $newContentDOM = $this->xmlUtilities->generateDomDocument('<?xml version="1.0" encoding="UTF-8" standalone="yes" ?><a:root xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' . $this->xlsxML . '</a:root>');
        $newContentXPath = new \DOMXPath($newContentDOM);
        $newContentXPath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
        $newContentXPath->registerNamespace('a14', 'http://schemas.microsoft.com/office/drawing/2010/main');

        $nodes = $newContentXPath->query($tags);

        return $nodes;
    }
}