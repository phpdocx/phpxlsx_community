<?php
namespace Phpxlsx\Utilities;

use Phpxlsx\Logger\PhpxlsxLogger;

/**
 * Storage XLSX internal structure
 *
 * @category   Phpxlsx
 * @package    utilities
 * @copyright  Copyright (c) Narcea Labs SL
 *             (https://www.narcealabs.com)
 * @license    phpxlsx Community License
 * @link       https://www.phpxlsx.com
 */
class XlsxStructure
{
    /**
     * Footer ID scopes
     */
    public static $idsFooters = array('LFFIRST', 'CFFIRST', 'RFFIRST', 'LF', 'CF', 'RF', 'LFEVEN', 'CFEVEN', 'RFEVEN');

    /**
     * Header ID scopes
     */
    public static $idsHeaders = array('LHFIRST', 'CHFIRST', 'RHFIRST', 'LH', 'CH', 'RH', 'LHEVEN', 'CHEVEN', 'RHEVEN');

    /**
     * File type
     *
     * @var string
     */
    public $fileType = 'xlsx';

    /**
     * Keep namespaces to work with transitional and strict variants
     *
     * @var array
     */
    public $namespaces = array();

    /**
     * XLSX structure
     *
     * @access private
     * @var array
     */
    private $xlsxStructure = array();

    /**
     * Parse an XLSX file
     *
     * @access public
     */
    public function __construct() {
        // default as transitional variant
        $this->namespaces = array(
            'xmlns' => 'http://schemas.openxmlformats.org/spreadsheetml/2006/main',
        );
    }

    /**
     * Getter file type
     *
     * @return string
     */
    public function getFileType() {
        return $this->fileType;
    }

    /**
     * Setter file type
     *
     * @param string $fileType File type: xlsx, xlsm, xltx, xltm
     */
    public function setFileType($fileType) {
        $this->fileType = $fileType;
    }

    /**
     * Getter namespaces
     *
     * @return array
     */
    public function getNamespaces() {
        return $this->namespaces;
    }

    /**
     * Setter namespaces
     *
     * @param array $namespaces
     */
    public function setNamespaces($namespaces) {
        $this->namespaces = $namespaces;
    }

    /**
     * Getter xlsxStructure
     *
     * @param string $format array or stream
     * @return mixed XLSX structure
     */
    public function getXlsx($format) {
        return $this->xlsxStructure;
    }

    /**
     * Setter xlsxStructure
     *
     * @param array $xlsxContents
     */
    public function setXlsx($xlsxContents) {
        $this->xlsxStructure = $xlsxContents;
    }

    /**
     * Add new content to the XLSX
     *
     * @param string $internalFilePath Path in the XLSX
     * @param string $content Content to be added
     */
    public function addContent($internalFilePath, $content)
    {
        $this->xlsxStructure[$internalFilePath] = $content;
    }

    /**
     * Add a new file to the XLSX
     *
     * @param string $internalFilePath Path in the XLSX
     * @param string $file File path to be added
     */
    public function addFile($internalFilePath, $file)
    {
        $this->xlsxStructure[$internalFilePath] = file_get_contents($file);
    }

    /**
     * Delete content in the XLSX
     *
     * @param string $internalFilePath Path in the XLSX
     */
    public function deleteContent($internalFilePath)
    {
        if (isset($this->xlsxStructure[$internalFilePath])) {
            unset($this->xlsxStructure[$internalFilePath]);
        }
    }

    /**
     * Get existing content from the XLSX
     *
     * @param string $internalFilePath Path in the XLSX
     * @param string $format null, string or DOMDocument
     * @return mixed File content as string, DOMDocument or false
     */
    public function getContent($internalFilePath, $format = null)
    {
        if (isset($this->xlsxStructure[$internalFilePath])) {
            $content = $this->xlsxStructure[$internalFilePath];
            if (empty($format) || $format == 'string') {
                // return content as string
                return $content;
            } else if ($format == 'DOMDocument') {
                // return content as DOMDocument
                $xmlUtilities = new XmlUtilities();
                $domDocument = $xmlUtilities->generateDomDocument($content);
                return $domDocument;
            }
        }

        return false;
    }

    /**
     * Get contents by type
     *
     * @param string $type Content type: calcChain, comments, sharedStrings, sheets, tables, workbooks
     * @return array Contents
     */
    public function getContentByType($type)
    {
        // Content_Types
        $contentTypesDOM = $this->getContent('[Content_Types].xml', 'DOMDocument');
        $contentTypesXPath = new \DOMXPath($contentTypesDOM);
        $contentTypesXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/package/2006/content-types');

        $queryXpath = '';
        switch ($type) {
            case 'calcChain':
                $queryXpath = '//xmlns:Override[@ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.calcChain+xml"]';
                break;
            case 'comments':
                $queryXpath = '//xmlns:Override[@ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.comments+xml"]';
                break;
            case 'sharedStrings':
                $queryXpath = '//xmlns:Override[@ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"]';
                break;
            case 'sheets':
                $queryXpath = '//xmlns:Override[@ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"]';
                break;
            case 'styles':
                $queryXpath = '//xmlns:Override[@ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"]';
                break;
            case 'tables':
                $queryXpath = '//xmlns:Override[@ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.table+xml"]';
                break;
            case 'workbooks':
            case 'default':
                $queryXpath = '//xmlns:Override[@ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"]';
                break;
        }

        $contents = array();
        if (!empty($queryXpath)) {
            $xpathEntries = $contentTypesXPath->query($queryXpath);

            foreach ($xpathEntries as $xpathEntry) {
                $contents[] = array(
                    'content' => $this->getContent(substr($xpathEntry->getAttribute('PartName'), 1)),
                    'path' => substr($xpathEntry->getAttribute('PartName'), 1),
                );
            }
        }

        return $contents;
    }

    /**
     * Get existing sheets keeping the workbook order
     *
     * @return array Sheet contents
     */
    public function getSheets()
    {
        // workbook
        $workbookDOM = $this->getContent('xl/workbook.xml', 'DOMDocument');
        // workbook rels
        $workbookRelsDOM = $this->getContent('xl/_rels/workbook.xml.rels', 'DOMDocument');
        $workbookRelsXpath = new \DOMXPath($workbookRelsDOM);
        $workbookRelsXpath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/package/2006/relationships');
        // keep sheet informations
        $sheetsContents = array();

        $sheetsTags = $workbookDOM->getElementsByTagName('sheets');
        if ($sheetsTags->length > 0) {
            $sheetTags = $sheetsTags->item(0)->getElementsByTagName('sheet');
            // iterate existing sheets
            foreach ($sheetTags as $sheetTags) {
                // get content from the sheet id
                $sheetRId = $sheetTags->getAttribute('r:id');
                $queryByRelsId = '//xmlns:Relationship[@Id="' . $sheetRId . '"]';
                $relationshipNodes = $workbookRelsXpath->query($queryByRelsId);
                if ($relationshipNodes->length > 0) {
                    $sheetPath = 'xl/' . $relationshipNodes->item(0)->getAttribute('Target');
                    $contentSheet = $this->getContent($sheetPath);

                    $sheetsContents[] = array(
                        'id' => $sheetTags->getAttribute('r:id'),
                        'name' => $sheetTags->getAttribute('name'),
                        'content' => $contentSheet,
                        'path' => $sheetPath,
                    );
                }
            }
        }

        return $sheetsContents;
    }

    /**
     * Parse an existing XLSX
     * @param string $path File path
     * @throws \Exception error opening the source file
     */
    public function parseXlsx($path)
    {
        $zip = new \ZipArchive();

        if ($zip->open($path) === TRUE) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $fileName = $zip->getNameIndex($i);
                $this->xlsxStructure[$zip->getNameIndex($i)] = $zip->getFromName($fileName);
            }
        } else {
            PhpxlsxLogger::logger('Error while trying to open ' . $path . ' as a ZIP file', 'fatal');
        }

        // parse the Content_Types
        $contentTypesContent = $this->xlsxStructure['[Content_Types].xml'];
        $xmlUtilities = new XmlUtilities();
        $contentTypesXml = $xmlUtilities->generateSimpleXmlElement($contentTypesContent);
        $contentTypesDom = dom_import_simplexml($contentTypesXml);
        $contentTypesXpath = new \DOMXPath($contentTypesDom->ownerDocument);
        $contentTypesXpath->registerNamespace('ct', 'http://schemas.openxmlformats.org/package/2006/content-types');

        // if there's no workbook.xml file, get application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml and rels files and rename them
        if (!isset($this->xlsxStructure['xl/workbook.xml'])) {
            $relsEntries = $contentTypesXpath->query('//ct:Override[@ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"]');
            $partNameMainXML = $relsEntries->item(0)->getAttribute('PartName');
            $partNameMainXMLNameStructure = substr($partNameMainXML, 1);
            $this->xlsxStructure['xl/workbook.xml'] = $this->xlsxStructure[$partNameMainXMLNameStructure];
            $partNameMainRelsXMLNameStructure = str_replace('xl/', 'xl/_rels/', $partNameMainXMLNameStructure) . '.rels';
            $this->xlsxStructure['xl/_rels/workbook.xml.rels'] = $this->xlsxStructure[$partNameMainRelsXMLNameStructure];
            unset($this->xlsxStructure[$partNameMainXMLNameStructure]);
            unset($this->xlsxStructure[$partNameMainRelsXMLNameStructure]);

            // replace the previous main document name by the new one
            $this->xlsxStructure['[Content_Types].xml'] = str_replace('"/'.$partNameMainXMLNameStructure.'"', '"/xl/workbook.xml"', $this->xlsxStructure['[Content_Types].xml']);
            $this->xlsxStructure['_rels/.rels'] = str_replace('"/'.$partNameMainXMLNameStructure.'"', '"/xl/workbook.xml"', $this->xlsxStructure['_rels/.rels']);
            $this->xlsxStructure['_rels/.rels'] = str_replace('"'.$partNameMainXMLNameStructure.'"', '"xl/workbook.xml"', $this->xlsxStructure['_rels/.rels']);
        }

        // get file type
        $xpathPresentationOverride = $contentTypesXpath->query('//ct:Override[@PartName="/xl/workbook.xml"]');
        if ($xpathPresentationOverride->length > 0) {
            $contentType = $xpathPresentationOverride->item(0)->getAttribute('ContentType');
            switch ($contentType) {
                case 'application/vnd.ms-excel.sheet.macroEnabled.main+xml':
                    $this->fileType = 'xlsm';
                    break;
                case 'application/vnd.openxmlformats-officedocument.spreadsheetml.template.main+xml':
                    $this->fileType = 'xltx';
                    break;
                case 'application/vnd.ms-excel.template.macroEnabled.main+xml':
                    $this->fileType = 'xltm';
                    break;
                case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml':
                default:
                    $this->fileType = 'xlsx';
                    break;
            }
        }

        // get XML namespaces based on strict variants if used in the XLSX
        $excelWorkbookDOM = $this->getContent('xl/workbook.xml', 'DOMDocument');
        $nodesWorkbook = $excelWorkbookDOM->getElementsByTagName('workbook');
        if ($nodesWorkbook->length > 0) {
            if ($nodesWorkbook->item(0)->hasAttribute('conformance') && $nodesWorkbook->item(0)->hasAttribute('conformance') == 'strict') {
                $this->namespaces = array(
                    'xmlns' => 'http://purl.oclc.org/ooxml/spreadsheetml/main',
                );
            }
        }
    }

    /**
     * Save xlsxStructure as ZIP
     * @param string $path File path
     * @param bool $forceFile Force XLSX as file, needed for charts when working with streams
     * @return XlsxStructure Self
     */
    public function saveXlsx($path, $forceFile = false) {
        // check if the path has the correct extension
        if (substr($path, -5) !== '.' . $this->fileType) {
            $path .= '.' . $this->fileType;
        }


        $xlsxFile = new \ZipArchive();

        // if dest file exits remove it to avoid duplicate content
        if (file_exists($path) && is_writable($path)) {
            unlink($path);
        }

        if ($xlsxFile->open($path, \ZipArchive::CREATE) === TRUE) {
            foreach ($this->xlsxStructure as $key => $value) {
                $xlsxFile->addFromString($key, $value);
            }

            $xlsxFile->close();
        } else {
            PhpxlsxLogger::logger('Error while trying to write to ' . $path, 'fatal');
        }

        // return the structure object after creating the file
        return $this;
    }
}