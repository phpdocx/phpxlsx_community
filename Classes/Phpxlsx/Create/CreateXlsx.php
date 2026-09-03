<?php
namespace Phpxlsx\Create;

use Phpxlsx\AutoLoader;
use Phpxlsx\Elements\CreateDrawingLegacy;
use Phpxlsx\Elements\CreateElement;
use Phpxlsx\Elements\CreateImage;
use Phpxlsx\Elements\CreateParagraph;
use Phpxlsx\Elements\CreateProperties;
use Phpxlsx\Elements\CreateShape;
use Phpxlsx\Elements\CreateStyles;
use Phpxlsx\Elements\CreateText;
use Phpxlsx\Elements\CreateTextBox;
use Phpxlsx\Logger\PhpxlsxLogger;
use Phpxlsx\Resources\OOXMLResources;
use Phpxlsx\Utilities\ImageUtilities;
use Phpxlsx\Utilities\PhpxlsxUtilities;
use Phpxlsx\Utilities\XmlUtilities;
use Phpxlsx\Utilities\XlsxStructure;
use Phpxlsx\Utilities\XlsxStructureTemplate;

/**
 * Create an XLSX file
 *
 * @category   Phpxlsx
 * @package    create
 * @copyright  Copyright (c) Narcea Labs SL
 *             (https://www.narcealabs.com)
 * @license    phpxlsx Community License
 * @link       https://www.phpxlsx.com
 */
require_once __DIR__ . '/../AutoLoader.php';
AutoLoader::load();
PhpxlsxLogger::initErrorLevel();

class CreateXlsx
{
    const VERSION = '1.0';

    /**
     *
     * @access public
     * @static
     * @var bool
     */
    public static $cleanUTF8 = true;

    /**
     *
     * @access public
     * @static
     * @var array
     */
    public static $elementsId = array();

    /**
     *
     * @access public
     * @var bool
     * @static
     */
    public static $rtl;

    /**
     * @access public
     * @var array
     */
    public $activeSheet;

    /**
     *
     * @access protected
     * @var \DOMDocument
     */
    protected $excelContentTypesDOM;

    /**
     *
     * @access protected
     * @var \DOMDocument
     */
    protected $excelRelsWorkbookDOM;

    /**
     *
     * @access protected
     * @var \DOMDocument
     */
    protected $excelWorkbookDOM;

    /**
     *
     * @access protected
     * @var \DOMDocument
     */
    protected $excelStylesDOM;

    /**
     *
     * @access protected
     * @var array
     */
    protected $namespaces;

    /**
     *
     * @access protected
     * @var array
     */
    protected $newCellStyles;

    /**
     *
     * @access protected
     * @var XmlUtilities XML Utilities classes
     */
    protected $xmlUtilities;

    /**
     *
     * @access protected
     * @var XlsxStructure
     */
    protected $zipXlsx;

    /**
     *
     * @access public
     * @var string
     */
    public $target = 'document';

    /**
     * Constructor
     *
     * @access public
     * @param array $options
     *      'sheetName' (string) Sheet name in the XLSX created from scratch. Sheet1 as default
     */
    public function __construct($options = array())
    {
        $this->xmlUtilities = new XmlUtilities();

        // normalize sheet name if set
        if (isset($options['sheetName'])) {
            $options['sheetName'] = $this->parseAndCleanSheetName($options['sheetName']);
            $options['sheetName'] = $this->parseAndCleanTextString($options['sheetName']);
        }

        // default base template
        $templateStructure = new XlsxStructureTemplate();
        $this->zipXlsx = $templateStructure->getStructure($options);

        PhpxlsxLogger::logger('Default base template.', 'info');

        // initialize some required variables
        $this->activeSheet = array();
        $this->newCellStyles = array(
            'borders' => array(),
            'cellXfs' => array(),
            'fonts' => array(),
            'fills' => array(),
        );
        self::$elementsId = array();

        $this->excelContentTypesDOM = $this->zipXlsx->getContent('[Content_Types].xml', 'DOMDocument');
        $this->excelWorkbookDOM = $this->zipXlsx->getContent('xl/workbook.xml', 'DOMDocument');

        // get current namespaces
        $this->namespaces = $this->zipXlsx->getNamespaces();

        // update the active sheet value
        $this->updateActiveSheet();

        // include the standard image defaults
        $this->generateDefault('gif', 'image/gif');
        $this->generateDefault('jpg', 'image/jpg');
        $this->generateDefault('png', 'image/png');
        $this->generateDefault('jpeg', 'image/jpeg');
        $this->generateDefault('bmp', 'image/bmp');

        // get the rels file
        $this->excelRelsWorkbookDOM = $this->zipXlsx->getContent('xl/_rels/workbook.xml.rels', 'DOMDocument');

        // get the styles
        $this->excelStylesDOM = $this->zipXlsx->getContent('xl/styles.xml', 'DOMDocument');

        // set rtl static variable
        self::$rtl = false;
    }

    /**
     * Gets the active sheet
     *
     * @access public
     * @return array
     */
    public function getActiveSheet()
    {
        return $this->activeSheet;
    }

    /**
     * Sets the active sheet
     *
     * @access public
     * @param array $options
     *      'name' (string) Sheet name
     *      'position' (int) Sheet position. 0 is the first sheet. -1 can be used to choose the last sheet
     * @throws \Exception if the position or name don't exist
     */
    public function setActiveSheet($options)
    {
        // if no position or name is sheet, throw an Exception
        if (!isset($options['position']) && !isset($options['name'])) {
            PhpxlsxLogger::logger('Choose a position or name to set the active sheet.', 'fatal');
        }

        // get sheets
        $sheetsContents = $this->zipXlsx->getSheets();

        if (isset($options['position'])) {
            // handle last position
            if ($options['position'] == -1) {
                $options['position'] = count($sheetsContents) - 1;
            }

            if (!isset($sheetsContents[$options['position']])) {
                PhpxlsxLogger::logger('The sheet position doesn\'t exist.', 'fatal');
            } else {
                $this->activeSheet = array(
                    'position' => (int)$options['position'],
                    'name' => $sheetsContents[$options['position']]['name'],
                );
            }
        } else if (isset($options['name'])) {
            $iSheetPosition = 0;
            $sheetFound = false;
            foreach ($sheetsContents as $sheetContents) {
                if ($sheetContents['name'] == $options['name']) {
                    $this->activeSheet = array(
                        'position' => (int)$iSheetPosition,
                        'name' => $sheetsContents[$iSheetPosition]['name'],
                    );
                    $sheetFound = true;
                    break;
                }
                $iSheetPosition++;
            }
            if (!$sheetFound) {
                PhpxlsxLogger::logger('The sheet name doesn\'t exist.', 'fatal');
            }
        }

        // refresh contents
        $this->zipXlsx->addContent('xl/workbook.xml', $this->excelWorkbookDOM->saveXML());
        $this->excelWorkbookDOM = $this->zipXlsx->getContent('xl/workbook.xml', 'DOMDocument');
    }

    /**
     * Adds a break
     *
     * @access public
     * @param int $position Position: 1, 2, 3...
     * @param string $type row, col
     */
    public function addBreak($position, $type = 'row')
    {
        // normalize type value
        $type = strtolower($type);

        // get the active sheet
        $sheetsContents = $this->zipXlsx->getSheets();
        $activeSheetContent = $sheetsContents[$this->activeSheet['position']];
        $sheetDOM = $this->xmlUtilities->generateDomDocument($activeSheetContent['content']);

        if ($type == 'row' || $type == 'col') {
            // add new break
            // check if the parent break tag exists. Create it if needed
            $nodesBreaks = $sheetDOM->getElementsByTagName($type.'Breaks');
            if ($nodesBreaks->length == 0) {
                // no breaks found, generate a new one at the end of the sheet
                $newNode = $sheetDOM->createDocumentFragment();
                $newNode->appendXML('<'.$type.'Breaks count="0" manualBreakCount="0"></'.$type.'Breaks>');

                $colBreaksNodes = $sheetDOM->getElementsByTagName('colBreaks');
                if ($type == 'row' && $colBreaksNodes->length > 0) {
                    // if colBreaks tag exists add the new node before it
                    $nodeBreaks = $colBreaksNodes->item(0)->parentNode->insertBefore($newNode, $colBreaksNodes->item(0));
                } else {
                    $nodeBreaks = $sheetDOM->getElementsByTagName('worksheet')->item(0)->appendChild($newNode);
                }
            } else {
                $nodeBreaks = $nodesBreaks->item(0);
            }
            // check if the break position already exists. If true, don't add the new break
            $sheetXPath = new \DOMXPath($sheetDOM);
            $sheetXPath->registerNamespace('xmlns', $this->namespaces['xmlns']);
            $breakIdNodes = $sheetXPath->query('//xmlns:'.$type.'Breaks/xmlns:brk[@id="'.$position.'"]');
            if ($breakIdNodes->length == 0) {
                // new break content in the correct order
                $newBreak = '<brk id="'.$position.'" man="1"/>';
                $newNode = $nodeBreaks->ownerDocument->createDocumentFragment();
                $newNode->appendXML($newBreak);

                // get current break nodes to add the new one in the correct order
                $breakAdded = false;
                $nodesBrk = $nodeBreaks->getElementsByTagName('brk');
                foreach ($nodesBrk as $nodeBrk) {
                    if ((int)$nodeBrk->getAttribute('id') > $position) {
                        // the new position has a lower id than the current node. Add the new break
                        $nodeBrk->parentNode->insertBefore($newNode, $nodeBrk);
                        $breakAdded = true;
                        break;
                    }
                }

                // the break has not been added yet, add it
                if (!$breakAdded) {
                    $nodeBreaks->appendChild($newNode);
                    $breakAdded = true;
                }

                $countBreaks = (int)$nodeBreaks->getAttribute('count');
                $countBreaks++;
                $nodeBreaks->setAttribute('count', $countBreaks);
                if ($nodeBreaks->hasAttribute('manualBreakCount')) {
                    $countManualBreaks = (int)$nodeBreaks->getAttribute('manualBreakCount');
                    $countManualBreaks++;
                    $nodeBreaks->setAttribute('manualBreakCount', $countManualBreaks);
                }

                PhpxlsxLogger::logger('Add break.', 'info');
            }
        }

        // refresh contents
        $this->zipXlsx->addContent($activeSheetContent['path'], $sheetDOM->saveXML());

        // free DOMDocument resources
        $sheetDOM = null;
    }

    /**
     * Adds content to a cell
     *
     * @access public
     * @param mixed $contents Contents and styles
     *      'text' (string)
     *      'bold' (bool)
     *      'color' (string) FFFFFF, FF0000 ...
     *      'font' (string) Arial, Times New Roman ...
     *      'fontSize' (int) 8, 9, 10, 11 ...
     *      'italic' (bool)
     *      'strikethrough' (bool)
     *      'subscript' (bool)
     *      'superscript' (bool)
     *      'underline' (string) single, double
     * Theme (Premium licenses):
     * 'theme' (array):
     *      'color' (int) theme value (zero-based index)
     *      'tint' (float) tint value (-1.0 to 1.0). Applied to color style
     * @param string $position Cell position in the current active sheet: A1, C3, AB7...
     * @param array $cellStyles Cell styles
     *      'backgroundColor' (string) FFFF00, CCCCCC ...
     *      'border' (string) thin, thick, dashed, double, mediumDashDotDot, hair ... apply for each side with 'borderTop', 'borderRight', 'borderBottom', 'borderLeft' and 'borderDiagonal'
     *      'borderColor' (string) FFFFFF, FF0000... apply for each side with 'borderColorTop', 'borderColorRight', 'borderColorBottom', 'borderColorLeft' and 'borderColorDiagonal'
     *      'cellStyleName' (string) cell style name. Applying a cell style name ignores other cell styles. Preset style names: Bad, Calculation, Check Cell, Comma, Currency, Explanatory Text, Good, Heading 1, Heading 2, Heading 3, Heading 4, Input, Linked Cell, Neutral, Normal, Note, Output, Percent, Warning Text
     *      'horizontalAlign' (string) left, center, right
     *      'indent' (int)
     *      'isFunction' (bool) set as function. Default as false. If not set, if the content starts with = , isFunction is set as true (set isFunction as false to avoid this)
     *      'locked' (bool)
     *      'rotation' (int) 60.000ths of a degree
     *      'shrinkToFit' (bool)
     *      'textDirection' (string) context, ltr, rtl
     *      'type' (string) general (default), number, currency, accounting, date, time, percentage, fraction, scientific, text, special, boolean, empty
     *      'typeOptions' (array)
     *          'formatCode' (string) format code
     *      'verticalAlign' (string) top, center, bottom
     *      'wrapText' (bool)
     * Theme (Premium licenses):
     * 'theme' (array):
     *      'backgroundColor' (int) theme value (zero-based index)
     *      'tint' (float) tint value (-1.0 to 1.0). Applied to backgroundColor style
     * @param array $options
     *      'insertMode' (string) replace, ignore. Default as replace
     *      'raw' (bool) add the raw content if true. Default as false
     *      'useCellStyles' (bool) use existing cell styles. Default as false
     */
    public function addCell($contents, $position, $cellStyles = array(), $options = array())
    {
        if (!file_exists(__DIR__ . '/../Theme/Themes.php')) {
            if (is_array($contents) && isset($contents['theme'])) {
                unset($contents['theme']);
            }
            if (is_array($cellStyles) && isset($cellStyles['theme'])) {
                unset($cellStyles['theme']);
            }
        }

        // normalize the position
        $position = $this->getPositionInfo($position);

        // allow string as $contents instead of an array. Transform string to array
        if (!is_array($contents)) {
            $contentsNormalized = array();
            $contentsNormalized['text'] = $contents;
            $contents = $contentsNormalized;
        }
        // normalize if DateTime format
        if (isset($contents['text']) && $contents['text'] instanceof \DateTime) {
            $contents['text'] = $contents['text']->format('Y-m-d H:i');
        }
        // normalize empty type adding an empty text
        if (isset($cellStyles['type']) && $cellStyles['type'] == 'empty') {
            $contents['text'] = '';
        }

        // if isFunction is not set and the text content starts with = set the content as function
        if (!isset($cellStyles['isFunction']) && isset($contents['text']) && substr($contents['text'], 0, 1) == '=') {
            $cellStyles['isFunction'] = true;
        }

        //  default options
        if (!isset($cellStyles['isFunction'])) {
            $cellStyles['isFunction'] = false;
        }
        if (!isset($options['insertMode'])) {
            $options['insertMode'] = 'replace';
        }
        if (!isset($options['raw'])) {
            $options['raw'] = false;
        }
        if (!isset($options['useCellStyles'])) {
            $options['useCellStyles'] = false;
        }

        // clean calcChain
        $this->cleanCalcChain($position);

        // get date format from the workbook. Default as 1900
        $cellStyles['dateFormat'] = '1900';
        $nodesWorkbookPr = $this->excelWorkbookDOM->getElementsByTagName('workbookPr');
        if ($nodesWorkbookPr->length > 0) {
            if ($nodesWorkbookPr->item(0)->hasAttribute('date1904') && $nodesWorkbookPr->item(0)->getAttribute('date1904') == '1') {
                $cellStyles['dateFormat'] = '1904';
            }
        }

        // create the text tag and the cell style
        $text = new CreateText();
        $elementsText = $text->createElementText($contents, $position, $cellStyles);

        // cell style name. Add the cell style if it's a preset one
        if (isset($cellStyles['cellStyleName'])) {
            $presetStyles = array('Bad', 'Calculation', 'Check Cell', 'Comma', 'Currency', 'Explanatory Text', 'Good', 'Heading 1', 'Heading 2', 'Heading 3', 'Heading 4', 'Input', 'Linked Cell', 'Neutral', 'Normal', 'Note', 'Output', 'Percent', 'Warning Text');
            if (in_array($cellStyles['cellStyleName'], $presetStyles)) {
                $this->createCellStyle($cellStyles['cellStyleName']);
            }
        }

        // add the shared string if not empty cellType
        $countSharedString = '';
        if (isset($elementsText['type']['sharedString']) && $elementsText['type']['sharedString']) {
            $sharedStringsContents = $this->zipXlsx->getContentByType('sharedStrings');
            // if no sharedStrings content can be found, generate and add an empty one
            if (count($sharedStringsContents) == 0) {
                $this->generateOverride('/xl/sharedStrings.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml');
                // refresh contents
                $this->zipXlsx->addContent('xl/sharedStrings.xml', OOXMLResources::$sharedStrings);
                $sharedStringsContents = $this->zipXlsx->getContentByType('sharedStrings');

                // add the new relationship
                $newId = $this->generateRelationshipId($this->excelRelsWorkbookDOM);

                $this->generateRelationshipWorkbook('rId' . $newId, 'sharedStrings.xml', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings');

                // refresh contents
                $this->zipXlsx->addContent('xl/_rels/workbook.xml.rels', $this->excelRelsWorkbookDOM->saveXML());
                $this->excelRelsWorkbookDOM = $this->zipXlsx->getContent('xl/_rels/workbook.xml.rels', 'DOMDocument');
            }
            $sharedStringsDOM = $this->xmlUtilities->generateDomDocument($sharedStringsContents[0]['content']);
            // check if the sharedString already exists and get the position if true. Otherwise create and add it
            $sharedStringsXPath = new \DOMXPath($sharedStringsDOM);
            $sharedStringsXPath->registerNamespace('xmlns', $this->namespaces['xmlns']);
            // check if the text exists to avoid count in evaluate returning 0 in two cases: there's a matched text but no preceding siblings and no matched text
            $nodesSharedStringsExists = false;
            $nodesSharedStringsCount = 0;
            if (isset($contents['text'])) {
                // do not reuse rich text contents
                // cleans UTF-8 charset removing not UTF-8 valid chars
                $contents['text'] = $this->parseAndCleanTextString($contents['text']);
                // replace ' by " to avoid XPath throwing an error
                $nodesSharedStringsExists = $sharedStringsXPath->evaluate('boolean(//xmlns:si/xmlns:t[text()=\''.str_replace('\'', '"', $contents['text']).'\'])');
                $nodesSharedStringsCount = $sharedStringsXPath->evaluate('count(//xmlns:si/xmlns:t[text()=\''.str_replace('\'', '"', $contents['text']).'\']/../preceding-sibling::*)');
            }
            if ($nodesSharedStringsExists) {
                // used to set the sharedString position when adding the cell content. The first position is 0
                $countSharedString = $nodesSharedStringsCount;
            } else {
                // the text doesn't exist as sharedString. Add a new one
                $nodesSst = $sharedStringsDOM->getElementsByTagName('sst');
                $valueUniqueCountSst = (int)$nodesSst->item(0)->getAttribute('uniqueCount');
                if (empty($valueUniqueCountSst)) {
                    $valueUniqueCountSst = 0;
                }
                $valueUniqueCountSst++;
                $nodesSst->item(0)->setAttribute('uniqueCount', $valueUniqueCountSst);
                // add the new child
                $newNode = $nodesSst->item(0)->ownerDocument->createDocumentFragment();
                $newNode->appendXML($elementsText['sharedStrings']);
                $nodesSst->item(0)->appendChild($newNode);

                // used to set the sharedString position when adding the cell content. The first position is 0
                $countSharedString = $valueUniqueCountSst - 1;
            }

            // get the sst count and increment it
            $nodesSst = $sharedStringsDOM->getElementsByTagName('sst');
            $valueCountSst = (int)$nodesSst->item(0)->getAttribute('count');
            if (empty($valueCountSst)) {
                $valueCountSst = 0;
            }
            $valueCountSst++;
            $nodesSst->item(0)->setAttribute('count', $valueCountSst);

            // refresh contents
            $this->zipXlsx->addContent($sharedStringsContents[0]['path'], $sharedStringsDOM->saveXML());

            // free DOMDocument resources
            $sharedStringsDOM = null;
        }

        // generate and add the styles
        $infoStyle = $this->generateXf($elementsText['textStyles'], $elementsText['cellStyles'], $elementsText['type'], '0');

        // get the active sheet
        $sheetsContents = $this->zipXlsx->getSheets();
        $activeSheetContent = $sheetsContents[$this->activeSheet['position']];
        $sheetDOM = $this->xmlUtilities->generateDomDocument($activeSheetContent['content']);
        // row and column position
        $cellPosition = $this->getPositionInfo($position, 'array');
        // check if the row to add the text exists in the sheet
        $sheetXPath = new \DOMXPath($sheetDOM);
        $sheetXPath->registerNamespace('xmlns', $this->namespaces['xmlns']);
        $rowPositionNode = $sheetXPath->query('//xmlns:sheetData/xmlns:row[@r="'.$cellPosition['number'].'"]');
        // set tag type to be added to the cell. <v> default tag, <f> for functions
        $tagType = 'v';
        if (isset($cellStyles['isFunction']) && $cellStyles['isFunction']) {
            $tagType = 'f';
        }
        // if the cell type has a new type set when the styles are set, assign it
        if (isset($elementsText['type']['newTagType']) && !empty($elementsText['type']['newTagType'])) {
            $tagType = $elementsText['type']['newTagType'];
        }
        // new cell content to be added.
        // If it's a numeric value, there's no related shared string
        if (isset($elementsText['type']['sharedString']) && $elementsText['type']['sharedString']) {
            // shared string content
            if (!empty($elementsText['textStyles']) || !empty($elementsText['cellStyles']) || !empty($elementsText['type'])) {
                // style
                $cellContent = '<c r="'.$cellPosition['text'].$cellPosition['number'].'" s="'.$infoStyle['styleIdSharedString'].'" '.$elementsText['type']['cellType'].'><'.$tagType.'>'.$countSharedString.'</'.$tagType.'></c>';
            } else {
                // no style
                $cellContent = '<c r="'.$cellPosition['text'].$cellPosition['number'].'" '.$elementsText['type']['cellType'].'><'.$tagType.'>'.$countSharedString.'</'.$tagType.'></c>';
            }
        } else {
            // handle content types that transform the new text, such as when adding dates and times
            if (isset($elementsText['type']['newContentText']) && (!empty($elementsText['type']['newContentText']) || $elementsText['type']['newContentText'] == 0) && (isset($options['raw']) && !$options['raw'])) {
                $contents['text'] = $elementsText['type']['newContentText'];
            }

            // not shared string content
            if (!empty($elementsText['textStyles']) || !empty($elementsText['cellStyles']) || !empty($elementsText['type'])) {
                // style
                if ($elementsText['type']['cellType'] == 'empty') {
                    // empty cell type
                    $cellContent = '<c r="'.$cellPosition['text'].$cellPosition['number'].'" s="'.$infoStyle['styleIdSharedString'].'"></c>';
                } else {
                    // other cell types
                    $cellContent = '<c r="'.$cellPosition['text'].$cellPosition['number'].'" s="'.$infoStyle['styleIdSharedString'].'" '.$elementsText['type']['cellType'].'><'.$tagType.'>'.$contents['text'].'</'.$tagType.'></c>';
                }
            } else {
                // no style
                $cellContent = '<c r="'.$cellPosition['text'].$cellPosition['number'].'" '.$elementsText['type']['cellType'].'><'.$tagType.'>'.$contents['text'].'</'.$tagType.'></c>';
            }
        }
        if ($rowPositionNode->length == 0) {
            // there's no matching row. Create and add it
            $rowsNodes = $sheetXPath->query('//xmlns:sheetData/xmlns:row');
            // new row content to be added
            $rowContent = '<row r="'.$cellPosition['number'].'">'.$cellContent.'</row>';
            if ($rowsNodes->length > 0) {
                // there are more rows. Add the new row in the correct order: before the next row by position
                $rowNewPosition = 0;
                foreach ($rowsNodes as $rowsNode) {
                    $currentRowValue = (int)$rowsNode->getAttribute('r');
                    if ($currentRowValue > (int)$cellPosition['number']) {
                        break;
                    }
                    $rowNewPosition++;
                }
                if ($rowsNodes->length > $rowNewPosition) {
                    // append before an existing row
                    $newNode = $rowsNodes->item($rowNewPosition)->ownerDocument->createDocumentFragment();
                    $newNode->appendXML($rowContent);
                    $rowsNodes->item($rowNewPosition)->parentNode->insertBefore($newNode, $rowsNodes->item($rowNewPosition));
                } else {
                    // append at the end
                    $newNode = $rowsNodes->item(0)->parentNode->ownerDocument->createDocumentFragment();
                    $newNode->appendXML($rowContent);
                    $rowsNodes->item(0)->parentNode->appendChild($newNode);
                }
            } else {
                // there aren't rows. Add the new one as sheetData child element
                $sheetDataNodes = $sheetXPath->query('//xmlns:sheetData');
                $newNode = $sheetDataNodes->item(0)->ownerDocument->createDocumentFragment();
                $newNode->appendXML($rowContent);
                $sheetDataNodes->item(0)->appendChild($newNode);
            }
        } else {
            // there's a matching row. Add the new cell in the correct order: before the next column by position
            $cellNewPosition = 0;
            $cellsNodes = $rowPositionNode->item(0)->getElementsByTagName('c');
            $cellPositionUsed = false;
            foreach ($cellsNodes as $cellNodes) {
                $currentCellValue = $this->getPositionInfo($cellNodes->getAttribute('r'), 'array');
                // get int values to compare the col positions correctly
                $elementObject = new CreateElement();
                $intCurrentCellValue = $elementObject->wordToInt($currentCellValue['text']);
                $intCellPosition = $elementObject->wordToInt($cellPosition['text']);
                if ($intCurrentCellValue == $intCellPosition) {
                    // equals
                    $cellPositionUsed = true;
                    break;
                }
                if ($intCurrentCellValue > $intCellPosition) {
                    // greater than
                    break;
                }
                $cellNewPosition++;
            }
            if ($cellPositionUsed) {
                // the cell position is already being used, choose how to handle it
                if (!isset($options['insertMode']) || (isset($options['insertMode']) && $options['insertMode'] == 'replace')) {
                    // replace the existing element

                    // append the new element
                    $currentCellNode = $cellsNodes->item($cellNewPosition);
                    $newNode = $currentCellNode->ownerDocument->createDocumentFragment();
                    $newNode->appendXML($cellContent);
                    $newCellNode = $cellsNodes->item($cellNewPosition)->parentNode->insertBefore($newNode, $currentCellNode->nextSibling);

                    // check if existing cell styles must be preserved
                    if (isset($options['useCellStyles']) && $options['useCellStyles'] && $currentCellNode->hasAttribute('s')) {
                        $newCellNode->setAttribute('s', $currentCellNode->getAttribute('s'));
                    }

                    // remove the previous duplicated element
                    $currentCellNode->parentNode->removeChild($currentCellNode);

                } else if (isset($options['insertMode']) && $options['insertMode'] == 'ignore') {
                    // ignore the new element
                }
            } else if ($cellsNodes->length > $cellNewPosition) {
                // append before an existing cell
                $newNode = $cellsNodes->item($cellNewPosition)->ownerDocument->createDocumentFragment();
                $newNode->appendXML($cellContent);
                $cellsNodes->item($cellNewPosition)->parentNode->insertBefore($newNode, $cellsNodes->item($cellNewPosition));
            } else {
                // append at the end of the row as a new child
                $newNode = $rowPositionNode->item(0)->ownerDocument->createDocumentFragment();
                $newNode->appendXML($cellContent);
                $rowPositionNode->item(0)->appendChild($newNode);
            }
        }

        PhpxlsxLogger::logger('Add cell content.', 'info');

        // refresh contents
        $this->zipXlsx->addContent($activeSheetContent['path'], $sheetDOM->saveXML());
        $this->zipXlsx->addContent('xl/styles.xml', $this->excelStylesDOM->saveXML());
        $this->excelStylesDOM = $this->zipXlsx->getContent('xl/styles.xml', 'DOMDocument');

        // free DOMDocument resources
        $sheetDOM = null;
    }

    /**
     * Adds ranges of cell contents
     *
     * @access public
     * @param array $contents Contents and styles @see addCell
     * @param string $position Cell position in the current active sheet: A1, C3, AB7...
     * @param array $cellStyles @see addCell
     * @param array $options @see addCell
     * @return array range
     */
    public function addCellRange($contents, $position, $cellStyles = array(), $options = array())
    {
        // normalize the position
        $position = $this->getPositionInfo($position);

        // get the content position and keep it to iterate the positions correctly
        $cellPosition = $this->getPositionInfo($position, 'array');
        // used to set the content position to the correct cells
        $cellPositionNew = $cellPosition;

        $lastColumnAdded = $cellPosition['text'];
        $lastRowAdded = $cellPosition['number'];

        foreach ($contents as $rowContents) {
            foreach ($rowContents as $columnContents) {
                // allow setting cell styles per cell content or as global cell styles using $cellStyles parameter
                $cellStylesContent = $cellStyles;
                if (isset($columnContents['cellStyles'])) {
                    $cellStylesContent = $columnContents['cellStyles'];
                }
                $this->addCell($columnContents, $cellPositionNew['text'] . $cellPositionNew['number'], $cellStylesContent, $options);

                // keep last column added to return it
                $lastColumnAdded = $cellPositionNew['text'];
                if (function_exists('str_increment')) {
                    $cellPositionNew['text'] = str_increment($cellPositionNew['text']);
                } else {
                    $cellPositionNew['text']++;
                }
            }
            // keep last row added to return it
            $lastRowAdded = (int)$cellPositionNew['number'];
            (int)$cellPositionNew['number']++;

            // restore the content position to the first value to iterate the positions correctly
            $cellPositionNew['text'] = $cellPosition['text'];
        }

        return array(
            'from' => $cellPosition['text'] . $cellPosition['number'],
            'to' => $lastColumnAdded . $lastRowAdded,
        );
    }

    /**
     * Adds a comment
     *
     * @param string|array $contents Contents and styles
     *      'text' (string)
     *      'bold' (bool)
     *      'color' (string) FFFFFF, FF0000 ...
     *      'font' (string) Arial, Times New Roman ...
     *      'fontSize' (int) 8, 9, 10, 11 ...
     *      'italic' (bool)
     *      'strikethrough' (bool)
     *      'subscript' (bool)
     *      'superscript' (bool)
     *      'underline' (string) single, double
     * @param string $position Cell position in the current active sheet: A1, C3, AB7...
     * @param array $options
     *      'author' (string) author name
     *      'replace' (bool) if true replaces the existing comment if it exists. Default as true
     * @throws \Exception author doesn't exist. The author must exist in the sheet
     */
    public function addComment($contents, $position, $options = array())
    {
        // default options
        if (!isset($options['replace'])) {
            $options['replace'] = true;
        }

        // get the active sheet
        $sheetsContents = $this->zipXlsx->getSheets();
        $activeSheetContent = $sheetsContents[$this->activeSheet['position']];
        $sheetDOM = $this->xmlUtilities->generateDomDocument($activeSheetContent['content']);

        $commentsContents = $this->getCommentsContent();
        $commentsContentsDOM = $this->xmlUtilities->generateDomDocument($commentsContents['content']);

        // check if the comment position is already being used
        $commentsContentXPath = new \DOMXPath($commentsContentsDOM);
        $commentsContentXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $nodesCommentPosition = $commentsContentXPath->query('//xmlns:commentList/xmlns:comment[@ref="'.$position.'"]');

        // handle if the comment will be added
        $addComment = true;

        if ($nodesCommentPosition->length > 0) {
            if ($options['replace']) {
                $nodesCommentPosition->item(0)->parentNode->removeChild($nodesCommentPosition->item(0));
            } else {
                $addComment = false;
            }
        }

        if ($addComment) {
            // check if the author name exists
            if (isset($options['author'])) {
                $nodesAuthors = $commentsContentsDOM->getElementsByTagName('authors');
                if ($nodesAuthors->length > 0) {
                    $nodesAuthor = $nodesAuthors->item(0)->getElementsByTagName('author');
                    $authorFound = false;
                    foreach ($nodesAuthor as $nodeAuthor) {
                        if ($nodeAuthor->nodeValue == $options['author']) {
                            $authorFound = true;
                            break;
                        }
                    }
                    if (!$authorFound) {
                        PhpxlsxLogger::logger('The author name \'' . $options['author'] . '\' doesn\'t exist.', 'fatal');
                    }
                }
            }

            // add the new comment

            // get the comment list. Create it if needed
            $nodesCommentList = $commentsContentsDOM->getElementsByTagName('commentList');
            if ($nodesCommentList->length == 0) {
                $newCommentListFragment = $commentsContentsDOM->createDocumentFragment();
                $newCommentListFragment->appendXML('<commentList/>');
                $nodeCommentList = $commentsContentsDOM->documentElement->appendChild($newCommentListFragment);
            } else {
                $nodeCommentList = $nodesCommentList->item(0);
            }

            // get the author id. Empty as default
            $authorIdContent = '';
            $nodesAuthors = $commentsContentsDOM->getElementsByTagName('authors');
            // at least one author exists, set the first one as default
            $authorIdContent = ' authorId="0" ';
            if (isset($options['author'])) {
                // custom author set. Get the author index
                $nodesAuthor = $nodesAuthors->item(0)->getElementsByTagName('author');
                $iAuthor = 0;
                foreach ($nodesAuthor as $nodeAuthor) {
                    if ($nodeAuthor->nodeValue == $options['author']) {
                        $authorIdContent = ' authorId="'.$iAuthor.'" ';
                        break;
                    }
                    $iAuthor++;
                }
            }

            // allow string as $contents instead of an array. Transform string to array
            // Generate sharedStrings
            if (!is_array($contents)) {
                $contentsNormalized = array();
                $contentsNormalized['text'] = $contents;
                $contents = $contentsNormalized;
            }
            if (isset($contents['text'])) {
                $contents = array($contents);
            }
            // create the text tag and the cell style
            $text = new CreateText();
            $commentElementsText = $text->createElementText($contents, $position);
            $commentElementsText['sharedStrings'] = str_replace('<si>', '<text>', $commentElementsText['sharedStrings']);
            $commentElementsText['sharedStrings'] = str_replace('</si>', '</text>', $commentElementsText['sharedStrings']);

            $newCommentXML = '<comment '.$authorIdContent.'ref="'.$position.'" shapeId="0">';
            $newCommentXML .= $commentElementsText['sharedStrings'];
            $newCommentXML .= '</comment>';

            $newCommentFragment = $commentsContentsDOM->createDocumentFragment();
            $newCommentFragment->appendXML($newCommentXML);
            $nodeCommentList->appendChild($newCommentFragment);

            // get the legacy drawing tag of the current sheet
            $nodesLegacyDrawing = $this->getNodesDrawing($sheetDOM, $activeSheetContent, array('extension' => 'vml', 'tag' => 'legacyDrawing', 'type' => 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/vmlDrawing'));

            // get the drawing content to add the new image
            $relsFilePath = str_replace('worksheets/', 'worksheets/_rels/', $activeSheetContent['path']) . '.rels';
            $sheetRelsContent = $this->zipXlsx->getContent($relsFilePath);
            $sheetRelsDOM = $this->xmlUtilities->generateDomDocument($sheetRelsContent);
            $sheetRelsContentXPath = new \DOMXPath($sheetRelsDOM);
            $sheetRelsContentXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/package/2006/relationships');
            $nodesRelationshipDrawing = $sheetRelsContentXPath->query('//xmlns:Relationships/xmlns:Relationship[@Id="'.$nodesLegacyDrawing->item(0)->getAttribute('r:id').'"]');
            if ($nodesRelationshipDrawing->length > 0) {
                $drawingTarget = str_replace('../drawings/', 'xl/drawings/', $nodesRelationshipDrawing->item(0)->getAttribute('Target'));
                $drawingContent = $this->zipXlsx->getContent($drawingTarget);
                if (!$drawingContent) {
                    // generate a new drawing content
                    $drawingContent = OOXMLResources::$drawingContentVML;

                    // add Default
                    $this->generateDefault('vml', 'application/vnd.openxmlformats-officedocument.vmlDrawing');
                }
                $drawingDOM = $this->xmlUtilities->generateDomDocument($drawingContent);

                // internal drawing ID
                $drawingId = $this->generateUniqueId();
                $options['position'] = $this->getPositionInfo($position, 'array');

                $drawingElement = new CreateDrawingLegacy();
                $elementsDrawing = $drawingElement->createElementDrawingComment($options);

                // check if the position is currently being used. Add the new content only if there's no content or replace if set as true
                $drawingXPath = new \DOMXPath($drawingDOM);
                $drawingXPath->registerNamespace('v', 'urn:schemas-microsoft-com:vml');
                $drawingXPath->registerNamespace('x', 'urn:schemas-microsoft-com:office:excel');
                $nodesDrawingId = $drawingXPath->query('//v:shape/x:ClientData[x:Row[text()='.$elementsDrawing['position']['row'].'] and x:Column[text()='.$elementsDrawing['position']['column'].']]');
                $addNewDrawing = true;
                if ($nodesDrawingId->length > 0 && isset($options['replace'])) {
                    if ($options['replace']) {
                        // add the new drawing. Remove the previous one
                        foreach ($nodesDrawingId as $nodeDrawingId) {
                            $nodeDrawingId->parentNode->removeChild($nodeDrawingId);
                        }
                    }

                    if (!$options['replace']) {
                        // do not add the new drawing
                        $addNewDrawing = false;
                    }
                }

                if ($addNewDrawing) {
                    // add the new drawing XML
                    $newNodeDrawing = $drawingDOM->createDocumentFragment();
                    $newNodeDrawing->appendXML($elementsDrawing['drawingVml']);
                    $drawingDOM->documentElement->appendChild($newNodeDrawing);

                    // get documentElement to avoid adding extra XML tag
                    $this->zipXlsx->addContent($drawingTarget, $drawingDOM->saveXML($drawingDOM->documentElement));
                }

                // free DOMDocument resources
                $drawingDOM = null;
                $drawingRelsDOM = null;
            }

            // refresh contents
            $this->zipXlsx->addContent($commentsContents['path'], $commentsContentsDOM->saveXML());

            // free DOMDocument resources
            $sheetRelsDOM = null;

            PhpxlsxLogger::logger('Add comment.', 'info');
        }

        // free DOMDocument resources
        $commentsContentsDOM = null;
    }

    /**
     * Adds a comment author
     *
     * @param string $author Author name
     * @throws \Exception author name exists
     */
    public function addCommentAuthor($author)
    {
        $commentsContents = $this->getCommentsContent();
        $commentsContentsDOM = $this->xmlUtilities->generateDomDocument($commentsContents['content']);

        // get author tag. Create it if needed
        $nodesAuthors = $commentsContentsDOM->getElementsByTagName('authors');
        $nodeAuthors = $nodesAuthors->item(0);

        // check if the author name exists
        $nodesAuthor = $nodeAuthors->getElementsByTagName('author');
        foreach ($nodesAuthor as $nodeAuthor) {
            if ($nodeAuthor->nodeValue == $author) {
                PhpxlsxLogger::logger('The author name \'' . $author . '\' exists. Choose another name.', 'fatal');
            }
        }

        // add the new author name
        $newAuthorFragment = $commentsContentsDOM->createDocumentFragment();
        $newAuthorFragment->appendXML('<author>'.$author.'</author>');
        $nodeAuthors->appendChild($newAuthorFragment);

        // refresh contents
        $this->zipXlsx->addContent($commentsContents['path'], $commentsContentsDOM->saveXML());

        // free DOMDocument resources
        $commentsContentsDOM = null;

        PhpxlsxLogger::logger('Add comment author.', 'info');
    }

    /**
     * Adds a function
     *
     * @access public
     * @param string $function
     * @param string $position Cell position in the current active sheet: A1, C3, AB7...
     * @param array $contentStyles @see addCell
     * @param array $cellStyles @see addCell
     * @param array $options @see addCell
     */
    public function addFunction($function, $position, $contentStyles = array(), $cellStyles = array(), $options = array())
    {
        // normalize the position
        $position = $this->getPositionInfo($position);

        // add the function as text content and set type function
        $contents = $contentStyles;
        $contents['text'] = $function;
        $cellStyles['isFunction'] = true;

        $this->addCell($contents, $position, $cellStyles, $options);
    }

    /**
     * Adds an image
     *
     * @param mixed $image Image path, stream, base64 or GdImage
     * @param string $position Cell position in the current active sheet: A1, C3, AB7...
     * @param array $options
     *      'colOffset' (array) given in emus (1cm = 360000 emus). 0 as default
     *          'from' (int) from offset
     *          'to' (int) from offset
     *      'colSize' (int) number of cols used by the image
     *      'descr' (string) set a descr value
     *      'dpi' (int) dots per inch
     *      'editAs' (string) oneCell (default) (move but don't size with cells), twoCell (move and size with cells), absolute (don't move or size with cells)
     *      'hyperlink' (string) hyperlink
     *      'mime' (string) forces a mime (image/jpg, image/jpeg, image/png, image/gif, image/bmp, image/webp)
     *      'name' (string) set a name value
     *      'rotation' (int) 60.000ths of a degree
     *      'rowOffset' (array) given in emus (1cm = 360000 emus). 0 as default
     *          'from' (int) from offset
     *          'to' (int) from offset
     *      'rowSize' (int) number of rows used by the image
     *      'transparency' (int) 0 to 100
     * @throws \Exception image doesn't exist
     * @throws \Exception image format is not supported
     * @throws \Exception mime option is not set and getimagesizefromstring is not available
     */
    public function addImage($image, $position, $options = array())
    {
        // normalize the position
        $position = $this->getPositionInfo($position);

        // default options
        if (!isset($options['editAs'])) {
            $options['editAs'] = 'oneCell';
        }

        // get image information
        $imageInformation = new ImageUtilities();
        $imageContents = $imageInformation->returnImageContents($image, $options);

        // add image information with size to $options to be used when generating the drawing tag
        $options['imageInformation'] = $imageContents;

        // make sure that there exists the corresponding content type
        $this->generateDefault($imageContents['extension'], 'image/' . $imageContents['extension']);

        // get the active sheet
        $sheetsContents = $this->zipXlsx->getSheets();
        $activeSheetContent = $sheetsContents[$this->activeSheet['position']];
        $sheetDOM = $this->xmlUtilities->generateDomDocument($activeSheetContent['content']);

        // get the drawing tag of the current sheet
        $nodesDrawing = $this->getNodesDrawing($sheetDOM, $activeSheetContent, array('extension' => 'xml', 'tag' => 'drawing', 'type' => 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing'));

        // get the drawing content to add the new image
        $relsFilePath = str_replace('worksheets/', 'worksheets/_rels/', $activeSheetContent['path']) . '.rels';
        $sheetRelsContent = $this->zipXlsx->getContent($relsFilePath);
        $sheetRelsDOM = $this->xmlUtilities->generateDomDocument($sheetRelsContent);
        $sheetRelsContentXPath = new \DOMXPath($sheetRelsDOM);
        $sheetRelsContentXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/package/2006/relationships');
        $nodesRelationshipDrawing = $sheetRelsContentXPath->query('//xmlns:Relationships/xmlns:Relationship[@Id="'.$nodesDrawing->item(0)->getAttribute('r:id').'"]');
        if ($nodesRelationshipDrawing->length > 0) {
            $drawingTarget = str_replace('../drawings/', 'xl/drawings/', $nodesRelationshipDrawing->item(0)->getAttribute('Target'));
            $drawingContent = $this->zipXlsx->getContent($drawingTarget);
            if (!$drawingContent) {
                // generate a new drawing content
                $drawingContent = OOXMLResources::$drawingContentXML;

                // add Override
                $this->generateOverride('/' . $drawingTarget, 'application/vnd.openxmlformats-officedocument.drawing+xml');
            }
            $drawingDOM = $this->xmlUtilities->generateDomDocument($drawingContent);

            // internal image ID
            $imageId = $this->generateUniqueId();
            $options['rId'] = $imageId;

            // drawing relationship
            $drawingTargetRels = str_replace('drawings/', 'drawings/_rels/', $drawingTarget) . '.rels';
            $drawingRelsContent = $this->zipXlsx->getContent($drawingTargetRels);
            if (empty($drawingRelsContent)) {
                $drawingRelsContent = OOXMLResources::$drawingContentRelsXML;
            }
            $drawingRelsDOM = $this->xmlUtilities->generateDomDocument($drawingRelsContent);
            $newRelationshipImage = '<Relationship Id="rId'.$imageId.'" Target="../media/img'.$imageId.'.'.$imageContents['extension'].'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" />';
            $relsNodeImage = $drawingRelsDOM->createDocumentFragment();
            $relsNodeImage->appendXML($newRelationshipImage);
            $drawingRelsDOM->documentElement->appendChild($relsNodeImage);

            // add the image into the XLSX file
            $this->zipXlsx->addContent('xl/media/img'.$imageId.'.'.$imageContents['extension'], $imageContents['content']);

            // handle hyperlink
            if (isset($options['hyperlink']) && !empty(isset($options['hyperlink']))) {
                $hyperlinkId = $this->generateUniqueId();
                if (substr($options['hyperlink'], 0, 1) == '#') {
                    // position link
                    $newRelationshipHyperlink = '<Relationship Id="rId'.$hyperlinkId.'" Target="'.$this->parseAndCleanTextString($options['hyperlink']).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" />';
                } else {
                    // external link
                    $newRelationshipHyperlink = '<Relationship Id="rId'.$hyperlinkId.'" Target="'.$this->parseAndCleanTextString($options['hyperlink']).'" TargetMode="External" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" />';
                }
                $relsNodeHyperlink = $drawingRelsDOM->createDocumentFragment();
                $relsNodeHyperlink->appendXML($newRelationshipHyperlink);
                $drawingRelsDOM->documentElement->appendChild($relsNodeHyperlink);

                $options['rIdHyperlink'] = $hyperlinkId;
            }

            $cellPosition = $this->getPositionInfo($position, 'array');

            $imageElement = new CreateImage();
            $elementsImage = $imageElement->createElementImage($image, $cellPosition, $options);

            // add the new drawing XML
            $newNodeDrawing = $drawingDOM->createDocumentFragment();
            $newNodeDrawing->appendXML($elementsImage['drawingXml']);
            $drawingDOM->documentElement->appendChild($newNodeDrawing);

            PhpxlsxLogger::logger('Add image.', 'info');

            // refresh contents
            $this->zipXlsx->addContent($drawingTarget, $drawingDOM->saveXML());
            $this->zipXlsx->addContent($drawingTargetRels, $drawingRelsDOM->saveXML());

            // free DOMDocument resources
            $drawingDOM = null;
            $drawingRelsDOM = null;
        }

        // free DOMDocument resources
        $sheetDOM = null;
        $sheetRelsDOM = null;
    }

    /**
     * Adds a link
     *
     * @access public
     * @param string $link URL or #location
     * @param string $position Cell position in the current active sheet: A1, C3, AB7...
     * @param string|array $contents @see addCell
     * @param array $cellStyles @see addCell
     * @param array $options @see addCell
     */
    public function addLink($link, $position, $contents = array(), $cellStyles = array(), $options = array())
    {
        // normalize the position
        $position = $this->getPositionInfo($position);

        $link = $this->parseAndCleanTextString($link);

        // add the text content if set
        if (count($contents) > 0) {
            if (isset($contents['text'])) {
                // regular text string

                // default values
                if (!isset($contents['color'])) {
                    $contents['color'] = '0000FF';
                }
                if (!isset($contents['underline'])) {
                    $contents['underline'] = 'single';
                }
            } else if (is_array($contents)) {
                // rich text string

                $i = 0;
                foreach ($contents as $content) {
                    // default values
                    if (!isset($content['color'])) {
                        $contents[$i]['color'] = '0000FF';
                    }
                    if (!isset($content['underline'])) {
                        $contents[$i]['underline'] = 'single';
                    }

                    $i++;
                }
            }

            $this->addCell($contents, $position, $cellStyles, $options);
        }

        // get the active sheet
        $sheetsContents = $this->zipXlsx->getSheets();
        $activeSheetContent = $sheetsContents[$this->activeSheet['position']];
        $sheetDOM = $this->xmlUtilities->generateDomDocument($activeSheetContent['content']);

        $hyperlinkContent = '';
        if (substr($link, 0, 1) == '#') {
            // position link
            $hyperlinkContent = '<hyperlink location="'.substr($link, 1).'" ref="'.$position.'"/>';
        } else {
            // external link
            $hyperlinkId = $this->generateUniqueId();
            $hyperlinkContent = '<hyperlink r:id="rId'.$hyperlinkId.'" ref="'.$position.'" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/>';

            // add the new relationship
            // get sheet rels. This file may not exists, so generate it from a skeleton
            $relsFilePath = str_replace('worksheets/', 'worksheets/_rels/', $activeSheetContent['path']) . '.rels';
            $sheetRels = $this->zipXlsx->getContent($relsFilePath);
            if (empty($sheetRels)) {
                $sheetRels = OOXMLResources::$sheetRelsXML;
            }
            $sheetRelsDOM = $this->xmlUtilities->generateDomDocument($sheetRels);

            $newRelationship = '<Relationship Id="rId'.$hyperlinkId.'" Target="'.$link.'" TargetMode="External" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" />';

            $relsNodeImage = $sheetRelsDOM->createDocumentFragment();
            $relsNodeImage->appendXML($newRelationship);
            $sheetRelsDOM->documentElement->appendChild($relsNodeImage);

            // refresh contents
            $this->zipXlsx->addContent($relsFilePath, $sheetRelsDOM->saveXML());
        }

        // add the new hyperlink content
        $nodesHyperlinks = $sheetDOM->getElementsByTagName('hyperlinks');
        if ($nodesHyperlinks->length == 0) {
            // no hyperlinks tag found, generate a new one before the pageMargins tag. If there's no pageMargins, add it after the sheetData
            $nodesPageMargins = $sheetDOM->getElementsByTagName('pageMargins');
            $elementHyperlinks = $sheetDOM->createElement('hyperlinks');
            if ($nodesPageMargins->length > 0) {
                $nodesPageMargins->item(0)->parentNode->insertBefore($elementHyperlinks, $nodesPageMargins->item(0));
            } else {
                $nodesSheetData = $sheetDOM->getElementsByTagName('sheetData');
                $nodesSheetData->item(0)->nextSibling->parentNode->insertBefore($elementHyperlinks, $nodesSheetData->item(0)->nextSibling);
            }

            $nodesHyperlinks = $sheetDOM->getElementsByTagName('hyperlinks');
        }
        $newNodeHyperlink = $nodesHyperlinks->item(0)->ownerDocument->createDocumentFragment();
        $newNodeHyperlink->appendXML($hyperlinkContent);

        // check if the current position contains an existing hyperlink
        $sheetXPath = new \DOMXPath($sheetDOM);
        $sheetXPath->registerNamespace('xmlns', $this->namespaces['xmlns']);
        $cellNodeHyperlinks = $sheetXPath->query('//xmlns:hyperlinks/xmlns:hyperlink[@ref="'.$position.'"]');

        if ($cellNodeHyperlinks->length > 0) {
            // the cell position is already being used for a hyperlink, choose how to handle it
            if (!isset($options['insertMode']) || (isset($options['insertMode']) && $options['insertMode'] == 'replace')) {
                // replace the existing hyperlink

                // keep the current node to be used to add the new node and then to be removed
                $currentCellNode = $cellNodeHyperlinks->item(0);

                // append the new element
                $currentCellNode->parentNode->insertBefore($newNodeHyperlink, $currentCellNode);

                // remove the previous duplicated element
                $currentCellNode->parentNode->removeChild($currentCellNode);
            } else if (isset($options['insertMode']) && $options['insertMode'] == 'ignore') {
                // ignore the new element
            }
        } else {
            // the cell position doesn't include a hyperlink
            $nodesHyperlinks->item(0)->appendChild($newNodeHyperlink);
        }

        PhpxlsxLogger::logger('Add link.', 'info');

        // refresh contents
        $this->zipXlsx->addContent($activeSheetContent['path'], $sheetDOM->saveXML());

        // free DOMDocument resources
        $sheetDOM = null;
    }

    /**
     * Adds properties to document
     *
     * @access public
     * @param array $values Parameters to use
     *      'category' (string)
     *      'Company' (string)
     *      'contentStatus' (string)
     *      'created' (string) W3CDTF without time zone
     *      'creator' (string)
     *      'custom' (array)
     *          'name' (array) 'type' => 'value'
     *      'description' (string)
     *      'keywords' (string)
     *      'lastModifiedBy' (string)
     *      'Manager' (string)
     *      'modified' (string) W3CDTF without time zone
     *      'revision' (string)
     *      'subject' (string)
     *      'title' (string)
     */
    public function addProperties($values)
    {
        $propsCore = $this->zipXlsx->getContent('docProps/core.xml', 'DOMDocument');
        $propsApp = $this->zipXlsx->getContent('docProps/app.xml', 'DOMDocument');
        $propsCustom = $this->zipXlsx->getContent('docProps/custom.xml', 'DOMDocument');
        $generateCustomRels = false;
        if ($propsCustom === false) {
            $generateCustomRels = true;
            $propsCustom = $this->xmlUtilities->generateDomDocument(OOXMLResources::$customProperties);
            // write the new Override node associated to the new custom.xml file in [Content_Types].xml
            $this->generateOverride('/docProps/custom.xml', 'application/vnd.openxmlformats-officedocument.custom-properties+xml');
            $this->zipXlsx->addContent('docProps/custom.xml', $propsCustom->saveXML());
        }
        $relsRels = $this->zipXlsx->getContent('_rels/.rels', 'DOMDocument');

        $prop = new CreateProperties();
        if (!empty($values['title']) || !empty($values['subject']) || !empty($values['creator']) || !empty($values['keywords']) || !empty($values['description']) || !empty($values['category']) || !empty($values['contentStatus']) || !empty($values['created']) || !empty($values['modified']) || !empty($values['lastModifiedBy']) || !empty($values['revision']) ) {
            $propsCore = $prop->createElementProperties($values, $propsCore);
        }
        if (isset($values['contentStatus']) && $values['contentStatus'] == 'Final') {
            $propsCustom = $prop->createPropertiesCustom(array('_MarkAsFinal' => array('boolean' => 'true')), $propsCustom);
        }
        if (!empty($values['Manager']) || !empty($values['Company'])) {
            $propsApp = $prop->createPropertiesApp($values, $propsApp);
        }
        if (!empty($values['custom']) && is_array($values['custom'])) {
            $propsCustom = $prop->createPropertiesCustom($values['custom'], $propsCustom);
            // write the new Override node associated to the new custom.xml file in [Content_Types].xml
            $this->generateOverride('/docProps/custom.xml', 'application/vnd.openxmlformats-officedocument.custom-properties+xml');
        }
        if ($generateCustomRels) {
            $strCustom = '<Relationship Id="rId' . self::uniqueNumberId(999, 9999) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/custom-properties" Target="docProps/custom.xml" />';
            $tempNode = $relsRels->createDocumentFragment();
            $tempNode->appendXML($strCustom);
            $relsRels->documentElement->appendChild($tempNode);
            // refresh contents
            $this->zipXlsx->addContent('xl/_rels/workbook.xml.rels', $this->excelRelsWorkbookDOM->saveXML());
            $this->excelRelsWorkbookDOM = $this->zipXlsx->getContent('xl/_rels/workbook.xml.rels', 'DOMDocument');
        }

        PhpxlsxLogger::logger('Set properties.', 'info');

        // refresh contents
        $this->zipXlsx->addContent('docProps/core.xml', $propsCore->saveXML());
        $this->zipXlsx->addContent('docProps/app.xml', $propsApp->saveXML());
        $this->zipXlsx->addContent('docProps/custom.xml', $propsCustom->saveXML());
        $this->zipXlsx->addContent('_rels/.rels', $relsRels->saveXML());

        // free DOMDocument resources
        $propsCore = null;
        $propsApp = null;
        $propsCustom = null;
        $relsRels = null;

        PhpxlsxLogger::logger('Adding properties to XLSX.', 'info');
    }

    /**
     * Adds a shape
     *
     * @param string $type
     *      accentBorderCallout1, accentBorderCallout2, accentBorderCallout3, accentCallout1, accentCallout2, accentCallout3, actionButtonBackPrevious, actionButtonBeginning, actionButtonBlank, actionButtonDocument, actionButtonEnd, actionButtonForwardNext, actionButtonHelp, actionButtonHome, actionButtonInformation, actionButtonMovie, actionButtonReturn, actionButtonSound, arc
     *      bentArrow, bentConnector2, bentConnector3, bentConnector4, bentConnector5, bentUpArrow, bevel, blockArc, borderCallout1, borderCallout2, borderCallout3, bracePair, bracketPair
     *      callout1, callout2, callout3, can, chartPlus, chartStar, chartX, chevron, chord, circularArrow, cloud, cloudCallout, corner, cornerTabs, cube, curvedConnector2, curvedConnector3, curvedConnector4, curvedConnector5, curvedDownArrow, curvedLeftArrow, curvedRightArrow, curvedUpArrow
     *      decagon, diagStripe, diamond, dodecagon, donut, doubleWave, downArrow, downArrowCallout
     *      ellipse, ellipseRibbon, ellipseRibbon2
     *      flowChartAlternateProcess, flowChartCollate, flowChartConnector, flowChartDecision, flowChartDelay, flowChartDisplay, flowChartDocument, flowChartExtract, flowChartInputOutput, flowChartInternalStorage, flowChartMagneticDisk, flowChartMagneticDrum, flowChartMagneticTape, flowChartManualInput, flowChartManualOperation, flowChartMerge, flowChartMultidocument, flowChartOfflineStorage, flowChartOffpageConnector, flowChartOnlineStorage, flowChartOr, flowChartPredefinedProcess, flowChartPreparation, flowChartProcess, flowChartPunchedCard, flowChartPunchedTape, flowChartSort, flowChartSummingJunction, flowChartTerminator, folderCorner, frame, funnel
     *      gear6, gear9
     *      halfFrame, heart, heptagon, hexagon, homePlate, horizontalScroll
     *      irregularSeal1, irregularSeal2
     *      leftArrow, leftArrowCallout, leftBrace, leftBracket, leftCircularArrow, leftRightArrow, leftRightArrowCallout, leftRightCircularArrow, leftRightRibbon, leftRightUpArrow, leftUpArrow, lightningBolt, line, lineInv
     *      mathDivide, mathEqual, mathMinus, mathMultiply, mathNotEqual, mathPlus, moon
     *      nonIsoscelesTrapezoid, noSmoking, notchedRightArrow
     *      octagon
     *      parallelogram, pentagon, pie, pieWedge, plaque, plaqueTabs, plus
     *      quadArrow, quadArrowCallout
     *      rect, ribbon, ribbon2, rightArrow, rightArrowCallout, rightBrace, rightBracket, round1Rect, round2DiagRect, round2SameRect, roundRect, rtTriangle
     *      smileyFace, snip1Rect, snip2DiagRect, snip2SameRect, snipRoundRect, squareTabs, star10, star12, star16, star24, star32, star4, star5, star6, star7, star8, straightConnector1, stripedRightArrow, sun, swooshArrow
     *      teardrop, trapezoid, triangle
     *      upArrow, upArrowCallout, upDownArrow, upDownArrowCallout, uturnArrow
     *      verticalScroll
     *      wave, wedgeEllipseCallout, wedgeRectCallout, wedgeRoundRectCallout
     * @param string $position Cell position in the current active sheet: A1, C3, AB7...
     * @param array $options
     *      'colOffset' (array) given in emus (1cm = 360000 emus). 0 as default
     *          'from' (int) from offset
     *          'to' (int) from offset
     *      'colSize' (int) number of cols used by the shape
     *      'customGeom' (string) custom geometry
     *      'editAs' (string) oneCell (default) (move but don't size with cells), twoCell (move and size with cells), absolute (don't move or size with cells)
     *      'fillColor' (string) #FF0000, #00FFFF,...
     *      'fillColorTransparency' (int) 0 to 100
     *      'imageContent' (mixed) image path, base64, stream or GdImage. Image formats: png, jpg, jpeg, gif, bmp, webp
     *      'name' (string) set a name value
     *      'outlineColor' (string) #FF0000, #00FFFF,...
     *      'rotation' (int) 60.000ths of a degree
     *      'rowOffset' (array) given in emus (1cm = 360000 emus). 0 as default
     *          'from' (int) from offset
     *          'to' (int) from offset
     *      'rowSize' (int) number of rows used by the shape
     *      'tailEnd' (string) arrow, diamond, none, oval, stealth, triangle
     *      'textContents' (array) @see addTextBox
     *      'textStyles' (array)
     *          'align' (string) left, center, right
     *          'verticalAlign' (string) top, middle, bottom
     * @throws \Exception image doesn't exist
     * @throws \Exception image format is not supported
     * @throws \Exception mime option is not set and getimagesizefromstring is not available
     */
    public function addShape($type, $position, $options = array())
    {
        // default options
        if (!isset($options['editAs'])) {
            $options['editAs'] = 'oneCell';
        }

        // normalize the position
        $position = $this->getPositionInfo($position);
        $cellPosition = $this->getPositionInfo($position, 'array');

        // internal shape ID
        $shapeId = $this->generateUniqueId();
        $options['rId'] = $shapeId;

        $imageId = '';
        if (isset($options['imageContent'])) {
            // internal image ID
            $imageId = $this->generateUniqueId();
            $options['rIdImage'] = $imageId;
        }

        // create shape content
        $shapeElement = new CreateShape();
        $contentShape = $shapeElement->createElementShape($type, $cellPosition, $options);

        // insert text box
        $this->insertTextBox($contentShape);

        // insert the image
        if (isset($options['imageContent'])) {
            // get image information
            $imageInformation = new ImageUtilities();
            $imageContents = $imageInformation->returnImageContents($options['imageContent'], $options);

            // add image information with size to $options to be used when generating the drawing tag
            $options['imageInformation'] = $imageContents;

            // make sure that there exists the corresponding content type
            $this->generateDefault($imageContents['extension'], 'image/' . $imageContents['extension']);

            // get the active sheet
            $sheetsContents = $this->zipXlsx->getSheets();
            $activeSheetContent = $sheetsContents[$this->activeSheet['position']];
            $sheetDOM = $this->xmlUtilities->generateDomDocument($activeSheetContent['content']);

            // get the drawing tag of the current sheet
            $nodesDrawing = $this->getNodesDrawing($sheetDOM, $activeSheetContent, array('extension' => 'xml', 'tag' => 'drawing', 'type' => 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing'));

            // get the drawing content to add the new image
            $relsFilePath = str_replace('worksheets/', 'worksheets/_rels/', $activeSheetContent['path']) . '.rels';
            $sheetRelsContent = $this->zipXlsx->getContent($relsFilePath);
            $sheetRelsDOM = $this->xmlUtilities->generateDomDocument($sheetRelsContent);
            $sheetRelsContentXPath = new \DOMXPath($sheetRelsDOM);
            $sheetRelsContentXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/package/2006/relationships');
            $nodesRelationshipDrawing = $sheetRelsContentXPath->query('//xmlns:Relationships/xmlns:Relationship[@Id="'.$nodesDrawing->item(0)->getAttribute('r:id').'"]');
            if ($nodesRelationshipDrawing->length > 0) {
                $drawingTarget = str_replace('../drawings/', 'xl/drawings/', $nodesRelationshipDrawing->item(0)->getAttribute('Target'));
                $drawingContent = $this->zipXlsx->getContent($drawingTarget);
                if (!$drawingContent) {
                    // generate a new drawing content
                    $drawingContent = OOXMLResources::$drawingContentXML;

                    // add Override
                    $this->generateOverride('/' . $drawingTarget, 'application/vnd.openxmlformats-officedocument.drawing+xml');
                }
                $drawingDOM = $this->xmlUtilities->generateDomDocument($drawingContent);

                // drawing relationship
                $drawingTargetRels = str_replace('drawings/', 'drawings/_rels/', $drawingTarget) . '.rels';
                $drawingRelsContent = $this->zipXlsx->getContent($drawingTargetRels);
                if (empty($drawingRelsContent)) {
                    $drawingRelsContent = OOXMLResources::$drawingContentRelsXML;
                }
                $drawingRelsDOM = $this->xmlUtilities->generateDomDocument($drawingRelsContent);
                $newRelationshipImage = '<Relationship Id="rId'.$imageId.'" Target="../media/img'.$imageId.'.'.$imageContents['extension'].'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" />';
                $relsNodeImage = $drawingRelsDOM->createDocumentFragment();
                $relsNodeImage->appendXML($newRelationshipImage);
                $drawingRelsDOM->documentElement->appendChild($relsNodeImage);

                // add the image into the XLSX file
                $this->zipXlsx->addContent('xl/media/img'.$imageId.'.'.$imageContents['extension'], $imageContents['content']);

                PhpxlsxLogger::logger('Add image.', 'info');

                // refresh contents
                $this->zipXlsx->addContent($drawingTargetRels, $drawingRelsDOM->saveXML());

                // free DOMDocument resources
                $drawingDOM = null;
                $drawingRelsDOM = null;
            }
        }

        PhpxlsxLogger::logger('Add shape.', 'info');
    }

    /**
     * Adds a sheet
     *
     * @access public
     * @param array $options
     *      'active' (bool) if true set as active sheet. Default as false
     *      'color' (string) HEX value
     *      'defaultColWidth' (int)
     *      'defaultRowHeight' (int)
     *      'name' (string) it can't contain : \ / ? * [ ]
     *      'pageMargins' (array) left (float), right (float), top (float), bottom (float), header (float), footer (float)
     *      'position' (int) sheet position. As default use the last position. 0 is the first sheet
     *      'removeSelected' (bool) if true remove selected property in all sheets. Default as false
     *      'rtl' (bool) set to true for right to left
     *      'selected' (bool) if true set the new sheet as selected. Default as false
     *      'state' (string) hidden
     * @throws \Exception sheet name exists
     */
    public function addSheet($options = array())
    {
        // default options
        if (!isset($options['active'])) {
            $options['active'] = false;
        }
        if (!isset($options['removeSelected'])) {
            $options['removeSelected'] = false;
        }
        if (!isset($options['selected'])) {
            $options['selected'] = false;
        }

        // get current sheets
        $currentSheetsTag = $this->excelWorkbookDOM->getElementsByTagName('sheets')->item(0);
        $sheetsContents = $this->zipXlsx->getSheets();
        $currentSheetsIds = array();
        foreach ($sheetsContents as $sheetContents) {
            $currentSheetsIds[] = $sheetContents['id'];
        }
        // generate a new name ID getting the current number of sheets
        $newNameId = count($sheetsContents) + 1;

        // generate a new ID for the relationship
        $newId = $this->generateRelationshipId($this->excelRelsWorkbookDOM);

        // add Override
        $this->generateOverride('/xl/worksheets/sheet' . $newId . '.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml');

        // add Relationship
        $this->generateRelationshipWorkbook('rId' . $newId, 'worksheets/sheet' . $newId . '.xml', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet');

        // add sheet to the workbook
        $nameSheet = 'Sheet' . $newNameId;
        if (isset($options['name'])) {
            $nameSheet = $options['name'];

            // clean not valid characters
            $options['sheetName'] = $this->parseAndCleanSheetName($nameSheet);
        }
        // check if sheet name is already being used
        $sheetNameExists = false;
        foreach ($sheetsContents as $sheetContents) {
            if ($nameSheet == $sheetContents['name']) {
                $sheetNameExists = true;
            }
        }
        if ($sheetNameExists) {
            PhpxlsxLogger::logger('The chosen sheet name \'' . $nameSheet . '\' exists. Choose another name.', 'fatal');
        }

        $nameSheet = $this->parseAndCleanTextString($nameSheet);

        // state option
        $state = '';
        if (isset($options['state']) && $options['state'] == 'hidden') {
            $state = 'state="'. $options['state'] . '"';
        }

        $newSheetXML = '<sheet name="' . $nameSheet . '" sheetId="' . $newId . '" r:id="rId' . $newId . '" ' . $state . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/>';
        $newSheetFragment = $this->excelWorkbookDOM->createDocumentFragment();
        $newSheetFragment->appendXML($newSheetXML);

        if (isset($options['position']) && is_int($options['position'])) {
            // handle sheet position
            $currentSheetNodes = $currentSheetsTag->childNodes;
            if ($options['position'] < count($currentSheetNodes)) {
                $currentSheetNodes->item($options['position'])->parentNode->insertBefore($newSheetFragment, $currentSheetNodes->item($options['position']));
            } else {
                // use the last position
                $currentSheetsTag->appendChild($newSheetFragment);
            }
        } else {
            // as default, adds the sheet in the last position
            $currentSheetsTag->appendChild($newSheetFragment);
        }

        // add new sheet to TitlesOfParts in app.xml
        $docPropsApp = $this->zipXlsx->getContent('docProps/app.xml', 'DOMDocument');
        if (!empty($docPropsApp)) {
            $headingPairsNodes = $docPropsApp->getElementsByTagName('HeadingPairs');
            if ($headingPairsNodes->length > 0) {
                $vectorNodes = $headingPairsNodes->item(0)->getElementsByTagNameNS('http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes', 'vector');
                if ($vectorNodes->length > 0) {
                    // increment vt:i4
                    $i4Nodes = $vectorNodes->item(0)->getElementsByTagNameNS('http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes', 'i4');
                    if ($i4Nodes->length > 0) {
                        $i4Nodes->item(0)->nodeValue = (int)$i4Nodes->item(0)->nodeValue + 1;
                    }

                    // refresh contents
                    $this->zipXlsx->addContent('docProps/app.xml', $docPropsApp->saveXML());
                }
            }

            $titlesOfPartsNodes = $docPropsApp->getElementsByTagName('TitlesOfParts');
            if ($titlesOfPartsNodes->length > 0) {
                $vectorNodes = $titlesOfPartsNodes->item(0)->getElementsByTagNameNS('http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes', 'vector');
                if ($vectorNodes->length > 0) {
                    // add the new vt:lpstr node
                    $newLpstrXML = '<vt:lpstr xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'.$nameSheet.'</vt:lpstr>';
                    $lpstrNewNode = $vectorNodes->item(0)->ownerDocument->createDocumentFragment();
                    $lpstrNewNode->appendXML($newLpstrXML);
                    $vectorNodes->item(0)->appendChild($lpstrNewNode);

                    // increment vt:vector size
                    if ($vectorNodes->item(0)->hasAttribute('size')) {
                        $sizeVector = (int)$vectorNodes->item(0)->getAttribute('size');
                        $sizeVector++;
                        $vectorNodes->item(0)->setAttribute('size', $sizeVector);
                    }

                    // refresh contents
                    $this->zipXlsx->addContent('docProps/app.xml', $docPropsApp->saveXML());
                }
            }
        }

        // handle selected option in sheets. If removeSelected is true, disable other selected tabs
        if ($options['removeSelected']) {
            foreach ($sheetsContents as $sheetContents) {
                $sheetDOM = $this->xmlUtilities->generateDomDocument($sheetContents['content']);
                $nodesSheetView = $sheetDOM->getElementsByTagName('sheetView');
                if ($nodesSheetView->length > 0) {
                    foreach ($nodesSheetView as $nodeSheetView) {
                        $nodeSheetView->removeAttribute('tabSelected');
                    }

                    // refresh contents
                    $this->zipXlsx->addContent($sheetContents['path'], $sheetDOM->saveXML());

                    // free DOMDocument resources
                    $sheetDOM = null;
                }
            }
        }

        // handle active option
        if ($options['active']) {
            $workbookViewTag = $this->excelWorkbookDOM->getElementsByTagName('workbookView');
            if ($workbookViewTag->length > 0) {
                $workbookViewTag->item(0)->setAttribute('activeTab', (string)count($currentSheetsIds));
            }
        }

        // add sheet to the XLSX
        // generate the XML of the new sheet from the sheet skeleton
        $sheetSkeletonContent = OOXMLResources::$sheetXML;
        $sheetSkeletonDOM = $this->xmlUtilities->generateDomDocument($sheetSkeletonContent);

        // handle color options
        if (isset($options['color'])) {
            // normalize color
            $options['color'] = strtoupper(str_replace('#', '', $options['color']));
            $nodesDimension = $sheetSkeletonDOM->getElementsByTagName('dimension');
            if ($nodesDimension->length > 0) {
                $nodesTabcolor = $sheetSkeletonDOM->getElementsByTagName('tabColor');

                if ($nodesTabcolor->length > 0) {
                    // update existing tab color
                    $nodesTabcolor->item(0)->setAttribute('rgb', $options['color']);
                } else {
                    $relsNodeSheetPr = $sheetSkeletonDOM->createDocumentFragment();
                    // append 00 to add alpha information
                    $relsNodeSheetPr->appendXML('<sheetPr><tabColor rgb="'.$options['color'].'"/></sheetPr>');

                    $nodesDimension->item(0)->parentNode->insertBefore($relsNodeSheetPr, $nodesDimension->item(0));
                }
            }
        }

        // handle default sizes
        if (isset($options['defaultColWidth'])) {
            $nodesSheetFormatPr = $sheetSkeletonDOM->getElementsByTagName('sheetFormatPr');
            if ($nodesSheetFormatPr->length > 0) {
                $nodesSheetFormatPr->item(0)->setAttribute('defaultColWidth', (string)$options['defaultColWidth']);
            }
        }
        if (isset($options['defaultRowHeight'])) {
            $nodesSheetFormatPr = $sheetSkeletonDOM->getElementsByTagName('sheetFormatPr');
            if ($nodesSheetFormatPr->length > 0) {
                $nodesSheetFormatPr->item(0)->setAttribute('defaultRowHeight', (string)$options['defaultRowHeight']);
                $nodesSheetFormatPr->item(0)->setAttribute('customHeight', 'true');
            }
        }

        // handle pageMargins options
        if (isset($options['pageMargins'])) {
            $nodesPageMargins = $sheetSkeletonDOM->getElementsByTagName('pageMargins');
            if ($nodesPageMargins->length > 0) {
                if (isset($options['pageMargins']['left'])) {
                    $nodesPageMargins->item(0)->setAttribute('left', $options['pageMargins']['left']);
                }
                if (isset($options['pageMargins']['right'])) {
                    $nodesPageMargins->item(0)->setAttribute('right', $options['pageMargins']['right']);
                }
                if (isset($options['pageMargins']['top'])) {
                    $nodesPageMargins->item(0)->setAttribute('top', $options['pageMargins']['top']);
                }
                if (isset($options['pageMargins']['bottom'])) {
                    $nodesPageMargins->item(0)->setAttribute('bottom', $options['pageMargins']['bottom']);
                }
                if (isset($options['pageMargins']['header'])) {
                    $nodesPageMargins->item(0)->setAttribute('header', $options['pageMargins']['header']);
                }
                if (isset($options['pageMargins']['footer'])) {
                    $nodesPageMargins->item(0)->setAttribute('footer', $options['pageMargins']['footer']);
                }
            }
        }

        // handle rtl options
        if ((isset($options['rtl']) && $options['rtl']) || self::$rtl) {
            $nodesSheetViews = $sheetSkeletonDOM->getElementsByTagName('sheetViews');
            if ($nodesSheetViews->length > 0) {
                foreach ($nodesSheetViews as $nodeSheetViews) {
                    $nodesSheetView = $nodeSheetViews->getElementsByTagName('sheetView');
                    if ($nodesSheetView->length > 0) {
                        foreach ($nodesSheetView as $nodeSheetView) {
                            $nodeSheetView->setAttribute('rightToLeft', '1');
                        }
                    }
                }
            }
        }

        PhpxlsxLogger::logger('Add sheet.', 'info');

        // refresh contents
        $this->zipXlsx->addContent('xl/worksheets/sheet'.$newId.'.xml', $sheetSkeletonDOM->saveXML());
        $this->zipXlsx->addContent('xl/workbook.xml', $this->excelWorkbookDOM->saveXML());
        $this->zipXlsx->addContent('xl/_rels/workbook.xml.rels', $this->excelRelsWorkbookDOM->saveXML());
        $this->excelWorkbookDOM = $this->zipXlsx->getContent('xl/workbook.xml', 'DOMDocument');
        $this->excelRelsWorkbookDOM = $this->zipXlsx->getContent('xl/_rels/workbook.xml.rels', 'DOMDocument');

        // free DOMDocument resources
        $sheetSkeletonDOM = null;

        if ($options['active']) {
            // update the active sheet value
            $this->updateActiveSheet();
        }
    }

    /**
     * Adds a table
     *
     * @access public
     * @param array $contents Data contents and styles @see addCell
     * @param string $position Cell position in the current active sheet: A1, C3, AB7...
     * @param array $tableStyles
     *      'bandedColumn' (bool) Default as false
     *      'bandedRow' (bool) Default as true
     *      'firstColumn' (bool) Default as false
     *      'headerRow' (bool) Default as true
     *      'lastColumn' (bool) Default as false
     *      'tableStyle' (string) Table style name
     *      'totalRow' (bool) Default as false
     * @param array $options
     *      'columnNames' (array) if not set, use Column string
     *      'columnTotals' (array)
     *          'cellStyles' (array) @see addCell
     *          'contentStyles' (array) @see addCell
     *          'type' (string) formula, function, label
     *          'value' (string) formula (string), function (average, count, countNums, max, min, stdDev, sum, var), label (string)
     *      'description' (string) alternative text description
     *      'filters' (array)
     *          'custom' (array)
     *              'andMode' (bool) Default as false. If true, enable and option
     *              'operator' (string) equal, greaterThan, greaterThanOrEqual, lessThan, lessThanOrEqual, notEqual
     *              'value' (string) ? (any single character), * (any series of characters)
     *          'values' (array)
     *      'tableName' (string) autogenerate if not set
     *      'title' (string) alternative text title
     * @return array range
     * @throws \Exception table name exists
     */
    public function addTable($contents, $position, $tableStyles = array(), $options = array())
    {
        // normalize the position
        $position = $this->getPositionInfo($position);

        // get the content position and keep it
        $cellPosition = $this->getPositionInfo($position, 'array');
        // used to set the content position to the correct cells
        $cellPositionNew = $cellPosition;

        // default options
        if (!isset($tableStyles['bandedColumn'])) {
            $tableStyles['bandedColumn'] = false;
        }
        if (!isset($tableStyles['bandedRow'])) {
            $tableStyles['bandedRow'] = true;
        }
        if (!isset($tableStyles['firstColumn'])) {
            $tableStyles['firstColumn'] = false;
        }
        if (!isset($tableStyles['filterButton'])) {
            $tableStyles['filterButton'] = true;
        }
        if (!isset($tableStyles['headerRow'])) {
            $tableStyles['headerRow'] = true;
        }
        if (!isset($tableStyles['lastColumn'])) {
            $tableStyles['lastColumn'] = false;
        }
        if (!isset($tableStyles['totalRow'])) {
            $tableStyles['totalRow'] = false;
        }

        // get the active sheet
        $sheetsContents = $this->zipXlsx->getSheets();
        $activeSheetContent = $sheetsContents[$this->activeSheet['position']];
        $sheetDOM = $this->xmlUtilities->generateDomDocument($activeSheetContent['content']);
        $relsFilePath = str_replace('worksheets/', 'worksheets/_rels/', $activeSheetContent['path']) . '.rels';
        $sheetRelsContent = $this->zipXlsx->getContent($relsFilePath);
        if (empty($sheetRelsContent)) {
            $sheetRelsContent = OOXMLResources::$sheetRelsXML;
        }
        $sheetRelsDOM = $this->xmlUtilities->generateDomDocument($sheetRelsContent);

        // get existing tables information in the whole workbook. Unique table names and IDs must be generated
        $tablesContentsWorksheet = $this->zipXlsx->getContentByType('tables');
        $tablesIds = array();
        $tablesNames = array();
        if (count($tablesContentsWorksheet) > 0) {
            foreach ($tablesContentsWorksheet as $tableContentsWorksheet) {
                $tableDOM = $this->xmlUtilities->generateDomDocument($tableContentsWorksheet['content']);
                $nodesTable = $tableDOM->getElementsByTagName('table');
                if ($nodesTable->length > 0) {
                    $nameTable = null;
                    $idTable = null;
                    if ($nodesTable->item(0)->hasAttribute('id')) {
                        $tablesIds[] = $nodesTable->item(0)->getAttribute('id');
                    }
                    if ($nodesTable->item(0)->hasAttribute('name')) {
                        $tablesNames[] = $nodesTable->item(0)->getAttribute('name');
                    }
                }
            }
        }

        // default table id to be added
        $newTableId = 1;
        // iterate $tablesIds until a new table id and name is generated
        if (count($tablesIds) > 0 && in_array($newTableId, $tablesIds)) {
            $iNewTableId = 1;
            while ($newTableId == 1) {
                if (!in_array($iNewTableId, $tablesIds) && !in_array('Table' . $iNewTableId, $tablesNames)) {
                    // generated new table id
                    $newTableId = $iNewTableId;
                    $tablesIds[] = $newTableId;
                }

                $iNewTableId++;
            }
        }

        // default table name to be added
        $newTableName = 'Table' . $newTableId;
        if (isset($options['tableName'])) {
            // use a custom table name
            $newTableName = $options['tableName'];
        }

        // check if table name exists in the current sheet
        if (in_array($newTableName, $tablesNames)) {
            PhpxlsxLogger::logger('The chosen table name \'' . $newTableName . '\' exists. Choose another name.', 'fatal');
        }

        // keep last column and row values added to use them later when setting ref attributes
        $lastColumnAdded = '';
        $lastRowAdded = 1;

        // add row headers. StringShared contents are not added if headerRow is false
        $headerDefaultString = 'Column';
        $columnsNumber = count($contents[0]);
        $newTableColumnNames = array();
        for ($i = 0; $i < $columnsNumber; $i++) {
            $columnRowName = '';
            $cellStylesContent = array();
            if (isset($options['columnNames']) && isset($options['columnNames'][$i])) {
                // custom row header name
                $columnRowName = $options['columnNames'][$i];

                // cell styles
                if (isset($options['columnNames'][$i]['cellStyles'])) {
                    $cellStylesContent = $options['columnNames'][$i]['cellStyles'];
                }
            } else {
                // default row header name. $i iterator begins from 0, sum one to do not set Column0 as name
                $columnRowName = $headerDefaultString . ($i + 1);
            }

            // normalize column name to be added later to the table
            $newTableColumnName = null;
            if (!is_array($columnRowName)) {
                // regular text string
                $newTableColumnName = $columnRowName;
            } else {
                if (isset($columnRowName['text'])) {
                    // array content
                    $newTableColumnName = $columnRowName['text'];
                } else if (is_array($columnRowName)) {
                    // rich text string. Get only the first value
                    $newTableColumnName = $columnRowName[0]['text'];
                }
            }

            // avoid duplicating row header name
            if (in_array($newTableColumnName, $newTableColumnNames)) {
                $newTableColumnNames[] = $newTableColumnName . $i;

                if (!is_array($columnRowName)) {
                    // regular text string
                    $columnRowName = $newTableColumnName . $i;
                } else {
                    if (isset($columnRowName['text'])) {
                        // array content
                        $columnRowName['text'] = $newTableColumnName . $i;
                    } else if (is_array($columnRowName)) {
                        // rich text string. Get only the first value
                        $columnRowName[0]['text'] = $newTableColumnName . $i;
                    }
                }
            } else {
                $newTableColumnNames[] = $newTableColumnName;
            }

            if (isset($tableStyles['headerRow'])) {
                $this->addCell($columnRowName, $cellPositionNew['text'] . $cellPositionNew['number'], $cellStylesContent, $options);

                // keep last column added to use it later when setting ref attributes
                $lastColumnAdded = $cellPositionNew['text'];
                if (function_exists('str_increment')) {
                    $cellPositionNew['text'] = str_increment($cellPositionNew['text']);
                } else {
                    $cellPositionNew['text']++;
                }
            }
        }

        // restore the column position to the first value to iterate the positions correctly and increment the row to add the new contents if row header have been added
        $cellPositionNew['text'] = $cellPosition['text'];
        if (isset($tableStyles['headerRow'])) {

            // keep last row added to use it later when setting ref attributes
            $lastRowAdded = (int)$cellPositionNew['number'];
            (int)$cellPositionNew['number']++;
        }

        // add sheet contents
        foreach ($contents as $rowContents) {
            foreach ($rowContents as $columnContents) {
                $cellStylesContent = array();
                if (isset($columnContents) && isset($columnContents['cellStyles'])) {
                    // cell styles are applied to the first array position
                    $cellStylesContent = $columnContents['cellStyles'];
                }
                // avoid empty columns
                if (empty($columnContents)) {
                    $columnContents = array(
                        'text' => '',
                    );
                }
                $this->addCell($columnContents, $cellPositionNew['text'] . $cellPositionNew['number'], $cellStylesContent, $options);

                // keep last column added to use it later when setting ref attributes
                $lastColumnAdded = $cellPositionNew['text'];
                if (function_exists('str_increment')) {
                    $cellPositionNew['text'] = str_increment($cellPositionNew['text']);
                } else {
                    $cellPositionNew['text']++;
                }
            }
            // keep last row added to use it later when setting ref attributes
            $lastRowAdded = (int)$cellPositionNew['number'];
            (int)$cellPositionNew['number']++;

            // restore the content position to the first value to iterate the positions correctly
            $cellPositionNew['text'] = $cellPosition['text'];
        }

        // restore the column position to the first value to iterate the positions correctly and increment the row to add the new contents if row header have been added
        $cellPositionNew['text'] = $cellPosition['text'];

        // add totalsRowCount if set
        // keep the totals row function to be added to tableColumn tags if set
        $totalsRowFunction = null;
        if (isset($tableStyles['totalRow']) && $tableStyles['totalRow']) {
            if (isset($options['columnTotals'])) {
                $totalsRowFunction = array();
                $iColumnTotals = 0;
                foreach ($options['columnTotals'] as $columnTotal) {
                    // add only the total if it has contents. Increment the column position even if a new cell is not added to allow setting the total into the needed column
                    if (count($columnTotal) > 0) {
                        if (isset($columnTotal['type'])) {
                            if ($columnTotal['type'] == 'formula') {
                                $totalsRowFunction[] = array(
                                    'type' => 'formula',
                                    'value' => $columnTotal['value'],
                                );

                                // default styles
                                $contentStyles = array('bold' => true);
                                if (isset($columnTotal['contentStyles'])) {
                                    $contentStyles = $columnTotal['contentStyles'];
                                }
                                $cellStylesContent = array();
                                if (isset($columnTotal['cellStyles'])) {
                                    $cellStylesContent = $columnTotal['cellStyles'];
                                }
                                $this->addFunction($columnTotal['value'], $cellPositionNew['text'] . $cellPositionNew['number'], $contentStyles, $cellStylesContent, $options);
                            } else if ($columnTotal['type'] == 'function') {
                                // function names have a preset function-number
                                $functionsAllowed = array('average' => '101', 'countNums' => '102', 'count' => '103', 'max' => '104', 'min' => '105', 'product' => '106', 'stdDev' => '107', 'stDevp' => '108', 'sum' => '109', 'var' => '110', 'varp' => '111');
                                if (isset($columnTotal['value']) && array_key_exists($columnTotal['value'], $functionsAllowed)) {
                                    $totalsRowFunction[] = array(
                                        'type' => 'function',
                                        'value' => $columnTotal['value'],
                                    );
                                } else {
                                    // default as count if not other valid value is set
                                    $totalsRowFunction[] = array(
                                        'type' => 'function',
                                        'value' => 'count',
                                    );
                                }

                                // default styles
                                $contentStyles = array('bold' => true);
                                if (isset($columnTotal['contentStyles'])) {
                                    $contentStyles = $columnTotal['contentStyles'];
                                }
                                $cellStylesContent = array();
                                if (isset($columnTotal['cellStyles'])) {
                                    $cellStylesContent = $columnTotal['cellStyles'];
                                }
                                $this->addFunction('SUBTOTAL('.$functionsAllowed[$columnTotal['value']].','.$newTableName.'['.$newTableColumnNames[$iColumnTotals].'])', $cellPositionNew['text'] . $cellPositionNew['number'], $contentStyles, $cellStylesContent, $options);
                            } if ($columnTotal['type'] == 'label') {
                                $totalsRowFunction[] = array(
                                    'type' => 'label',
                                    'value' => $columnTotal['value'],
                                );

                                // default styles
                                $contents = array('bold' => true);
                                if (isset($columnTotal['contentStyles'])) {
                                    $contents = $columnTotal['contentStyles'];
                                }
                                $contents['text'] = $columnTotal['value'];
                                $cellStylesContent = array();
                                if (isset($columnTotal['cellStyles'])) {
                                    $cellStylesContent = $columnTotal['cellStyles'];
                                }
                                $this->addCell($contents, $cellPositionNew['text'] . $cellPositionNew['number'], $cellStylesContent, $options);
                            }
                        }
                    } else {
                        // empty totals row for this column position
                        $totalsRowFunction[] = null;
                    }

                    $iColumnTotals++;

                    // keep last column added to use it later when setting ref attributes
                    $lastColumnAdded = $cellPositionNew['text'];
                    if (function_exists('str_increment')) {
                        $cellPositionNew['text'] = str_increment($cellPositionNew['text']);
                    } else {
                        $cellPositionNew['text']++;
                    }
                }
            }

            // keep last row added to use it later when setting ref attributes
            $lastRowAdded = (int)$cellPositionNew['number'];
            (int)$cellPositionNew['number']++;
        }

        // restore the column position to the first value to iterate the positions correctly and increment the row to add the new contents if row header have been added
        $cellPositionNew['text'] = $cellPosition['text'];

        // refresh sheet after adding sheet contents
        $sheetsContents = $this->zipXlsx->getSheets();
        $activeSheetContent = $sheetsContents[$this->activeSheet['position']];
        $sheetDOM = $this->xmlUtilities->generateDomDocument($activeSheetContent['content']);

        // generate new Id for the new relationship in the sheet
        $newIdRelationship = $this->generateRelationshipId($sheetRelsDOM);

        // table target
        $tableTarget = 'tables/table' . $newIdRelationship . '.xml';

        // add Override
        $this->generateOverride('/xl/' . $tableTarget, 'application/vnd.openxmlformats-officedocument.spreadsheetml.table+xml');

        // add new relationship to the sheet
        $newRelationshipTable = '<Relationship Id="rId'.$newIdRelationship.'" Target="../'.$tableTarget.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/table" />';
        $relsNodeTable = $sheetRelsDOM->createDocumentFragment();
        $relsNodeTable->appendXML($newRelationshipTable);
        $sheetRelsDOM->documentElement->appendChild($relsNodeTable);

        // add new table file
        $newTableContent = OOXMLResources::$tableXML;
        $newTableDOM = $this->xmlUtilities->generateDomDocument($newTableContent);

        // add autofilter if row headers are added
        if (isset($tableStyles['headerRow']) && $tableStyles['headerRow']) {
            $lastRowAddedAutofilter = $lastRowAdded;
            if (isset($tableStyles['totalRow']) && $tableStyles['totalRow']) {
                // if totalRow is added, the row values is one less
                $lastRowAddedAutofilter--;
            }
            $autoFilterContent = '<autoFilter ref="' . $cellPosition['text'] . $cellPosition['number'] . ':' . $lastColumnAdded . $lastRowAddedAutofilter . '">';

            // filters
            $iFilterColumns = 0;
            if (isset($options['filters'])) {
                $filterContent = '';
                foreach ($options['filters'] as $filters) {
                    // each key sets a column
                    // add only the filter if the key has values
                    if (count($filters) > 0) {
                        $filterContent .= '<filterColumn colId="' . $iFilterColumns . '">';

                        // filter values
                        if (isset($filters['values'])) {
                            $filterContent .= '<filters>';
                            foreach ($filters['values'] as $filterValue) {
                                $filterContent .= '<filter val="' . $filterValue . '"/>';
                            }
                            $filterContent .= '</filters>';
                        }

                        // custom filters
                        if (isset($filters['custom'])) {
                            $filterContent .= '<customFilters>';
                            foreach ($filters['custom'] as $filterCustom) {
                                $operatorContent = '';
                                if (isset($filterCustom['operator']) && $filterCustom['operator'] != 'equal') {
                                    $operatorContent = 'operator="' . $filterCustom['operator']. '"';
                                }
                                if (isset($filterCustom['value'])) {
                                    $filterContent .= '<customFilter ' . $operatorContent . ' val="' . $filterCustom['value'] . '"/>';
                                }
                            }
                            $filterContent .= '</customFilters>';
                        }

                        $filterContent .= '</filterColumn>';
                    }

                    $iFilterColumns++;
                }

                $autoFilterContent .= $filterContent;
            }

            $autoFilterContent .= '</autoFilter>';
            $autoFilterNode = $newTableDOM->createDocumentFragment();
            $autoFilterNode->appendXML($autoFilterContent);
            $newTableDOM->firstChild->insertBefore($autoFilterNode, $newTableDOM->firstChild->firstChild);
        }

        // add table attributes
        $nodesTableNewTable = $newTableDOM->getElementsByTagName('table');
        $nodesTableNewTable->item(0)->setAttribute('displayName', $newTableName);
        $nodesTableNewTable->item(0)->setAttribute('name', $newTableName);
        $nodesTableNewTable->item(0)->setAttribute('id', $newTableId);
        $nodesTableNewTable->item(0)->setAttribute('ref', $cellPosition['text'] . $cellPosition['number'] . ':' . $lastColumnAdded . $lastRowAdded);
        if (isset($tableStyles['totalRow']) && $tableStyles['totalRow']) {
            $nodesTableNewTable->item(0)->setAttribute('totalsRowCount', '1');
        }

        // tableColumns
        $nodesTableColumn = $newTableDOM->getElementsByTagName('tableColumns');
        $nodesTableColumn->item(0)->setAttribute('count', count($newTableColumnNames));
        $iTableColumns = 1;
        foreach ($newTableColumnNames as $newTableColumnName) {
            $tableColumnContent = '<tableColumn id="' . $iTableColumns . '" name="' . $newTableColumnName . '"/>';
            $tableColumnNode = $newTableDOM->createDocumentFragment();
            $tableColumnNode->appendXML($tableColumnContent);
            $tableColumnNewNode = $nodesTableColumn->item(0)->appendChild($tableColumnNode);

            if (isset($totalsRowFunction[$iTableColumns-1]) && !empty($totalsRowFunction[$iTableColumns-1])) {
                if ($totalsRowFunction[$iTableColumns-1]['type'] == 'formula') {
                    $tableColumnNodeFormula = $tableColumnNewNode->ownerDocument->createDocumentFragment();
                    $tableColumnNodeFormula->appendXML('<totalsRowFormula>'.$totalsRowFunction[$iTableColumns-1]['value'].'</totalsRowFormula>');
                    $tableColumnNewNode->appendChild($tableColumnNodeFormula);
                    $tableColumnNewNode->setAttribute('totalsRowFunction', 'custom');
                } else if ($totalsRowFunction[$iTableColumns-1]['type'] == 'function') {
                    // function row total
                    $tableColumnNewNode->setAttribute('totalsRowFunction', $totalsRowFunction[$iTableColumns-1]['value']);
                } else if ($totalsRowFunction[$iTableColumns-1]['type'] == 'label') {
                    // label row total
                    $tableColumnNewNode->setAttribute('totalsRowLabel', $totalsRowFunction[$iTableColumns-1]['value']);
                }
            }

            $iTableColumns++;
        }

        // add tableStyleInfo
        $nodesTableStyleInfo = $newTableDOM->getElementsByTagName('tableStyleInfo');
        if (isset($tableStyles['bandedColumn'])) {
            if ($tableStyles['bandedColumn']) {
                $nodesTableStyleInfo->item(0)->setAttribute('showColumnStripes', '1');
            } else {
                $nodesTableStyleInfo->item(0)->setAttribute('showColumnStripes', '0');
            }
        }
        if (isset($tableStyles['bandedRow'])) {
            if ($tableStyles['bandedRow']) {
                $nodesTableStyleInfo->item(0)->setAttribute('showRowStripes', '1');
            } else {
                $nodesTableStyleInfo->item(0)->setAttribute('showRowStripes', '0');
            }
        }
        if (isset($tableStyles['firstColumn'])) {
            if ($tableStyles['firstColumn']) {
                $nodesTableStyleInfo->item(0)->setAttribute('showFirstColumn', '1');
            } else {
                $nodesTableStyleInfo->item(0)->setAttribute('showFirstColumn', '0');
            }
        }
        if (isset($tableStyles['lastColumn'])) {
            if ($tableStyles['lastColumn']) {
                $nodesTableStyleInfo->item(0)->setAttribute('showLastColumn', '1');
            } else {
                $nodesTableStyleInfo->item(0)->setAttribute('showLastColumn', '0');
            }
        }
        if (isset($tableStyles['tableStyle'])) {
            $nodesTableStyleInfo->item(0)->setAttribute('name', $tableStyles['tableStyle']);
        }

        // alt text contents
        if (isset($options['title']) || isset($options['description'])) {
            $nodesX14Table = $newTableDOM->getElementsByTagNameNS('http://schemas.microsoft.com/office/spreadsheetml/2009/9/main', 'table');
            if ($nodesX14Table->length > 0) {
                if (isset($options['title'])) {
                    $nodesX14Table->item(0)->setAttribute('altText', $options['title']);
                }
                if (isset($options['description'])) {
                    $nodesX14Table->item(0)->setAttribute('altTextSummary', $options['description']);
                }
            }
        }

        // add table file
        $this->zipXlsx->addContent('xl/' . $tableTarget, $newTableDOM->saveXML());

        //  add tableParts to the sheet
        $nodesTableParts = $sheetDOM->getElementsByTagName('tableParts');
        if ($nodesTableParts->length == 0) {
            // there's no tableParts tag. Create and add a new one
            $tablePartsContent = '<tableParts />';
            $tablePartsNode = $sheetDOM->createDocumentFragment();
            $tablePartsNode->appendXML($tablePartsContent);
            $nodeTableParts = $sheetDOM->getElementsByTagName('worksheet')->item(0)->appendChild($tablePartsNode);
        } else {
            $nodeTableParts = $nodesTableParts->item(0);
        }
        if ($nodeTableParts->hasAttribute('count')) {
            $countTableParts = (int)$nodeTableParts->getAttribute('count');
            $countTableParts++;
            $nodeTableParts->setAttribute('count', $countTableParts);
        } else {
            $nodeTableParts->setAttribute('count', '1');
        }
        $tablePartContent = '<tablePart r:id="rId' . $newIdRelationship . '" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" />';
        $tablePartNode = $nodeTableParts->ownerDocument->createDocumentFragment();
        $tablePartNode->appendXML($tablePartContent);
        $nodeTableParts->appendChild($tablePartNode);

        PhpxlsxLogger::logger('Add table.', 'info');

        // refresh contents
        $this->zipXlsx->addContent($activeSheetContent['path'], $sheetDOM->saveXML());
        $this->zipXlsx->addContent($relsFilePath, $sheetRelsDOM->saveXML());

        // free DOMDocument resources
        $sheetDOM = null;
        $sheetRelsDOM = null;

        return array(
            'from' => $cellPosition['text'] . $cellPosition['number'],
            'to' => $lastColumnAdded . $lastRowAdded,
        );
    }

    /**
     * Adds a text box
     *
     * @access public
     * @param mixed $contents Text box contents
     *      'text' (string)
     *      'bold' (bool)
     *      'color' (string) FFFFFF, FF0000 ...
     *      'font' (string) Arial, Times New Roman ...
     *      'fontSize' (int) 8, 9, 10, 11 ...
     *      'highlight' (string) FFFFFF, FF0000 ...
     *      'italic' (bool)
     *      'strikethrough' (bool)
     *      'transparency' (int) 0 to 100. Use with the color option
     *      'underline' (string) single, double
     * @param string $position Cell position in the current active sheet: A1, C3, AB7...
     * @param array $textBoxStyles
     *      'align' (string) left, center, right
     *      'backgroundColor' (string) FFFF00, CCCCCC..., none. Default as FFFFFF
     *      'backgroundTransparency' (int) 0 to 100
     *      'border' (array)
     *          'color' (string) FFFFFF, FF0000 ...
     *          'dashType' (string) solid, roundDot, squareDot, dash
     *          'transparency' (int) 0 to 100
     *          'width' (int) EMUs (1cm = 360000 emus)
     *      'rotation' (int) 60.000ths of a degree
     *      'verticalAlign' (string) top, middle, bottom
     * @param array $options
     *      'colOffset' (array) given in emus (1cm = 360000 emus). 0 as default
     *          'from' (int) from offset
     *          'to' (int) from offset
     *      'colSize' (int) number of cols used by the text box
     *      'editAs' (string) oneCell (default) (move but don't size with cells), twoCell (move and size with cells), absolute (don't move or size with cells)
     *      'name' (string) set a name value
     *      'rowOffset' (array) given in emus (1cm = 360000 emus). 0 as default
     *          'from' (int) from offset
     *          'to' (int) from offset
     *      'rowSize' (int) number of rows used by the text box
     */
    public function addTextBox($contents, $position, $textBoxStyles = array(), $options = array())
    {
        // default options
        if (!isset($options['editAs'])) {
            $options['editAs'] = 'oneCell';
        }

        // allow string as $contents instead of an array. Transform string to array
        if (!is_array($contents)) {
            $contentsNormalized = array();
            $contentsNormalized['text'] = $contents;
            $contents = $contentsNormalized;
        }
        // if not using a subarray, generate it
        if (isset($contents['text'])) {
            $contents = array($contents);
        }

        // create paragraph elements from $contents
        $paragraph = new CreateParagraph();
        $elementsParagraph = $paragraph->createElementParagraph($contents, $position, $textBoxStyles);

        // normalize the position
        $position = $this->getPositionInfo($position);
        $cellPosition = $this->getPositionInfo($position, 'array');

        // internal text box ID
        $textBoxId = $this->generateUniqueId();
        $options['rId'] = $textBoxId;

        // create text box content
        $textBoxElement = new CreateTextBox();
        $contentTextBox = $textBoxElement->createElementTextBox($elementsParagraph, $cellPosition, $textBoxStyles, $options);

        // insert text box
        $this->insertTextBox($contentTextBox);

        PhpxlsxLogger::logger('Add text box.', 'info');
    }

    /**
     * Creates cell style
     *
     * @access public
     * @param string $name Style name. Preset style names: Bad, Calculation, Check Cell, Comma, Currency, Explanatory Text, Good, Heading 1, Heading 2, Heading 3, Heading 4, Input, Linked Cell, Neutral, Normal, Note, Output, Percent, Warning Text
     * @param array $styles
     *      'backgroundColor' (string) FFFF00, CCCCCC ...
     *      'bold' (bool)
     *      'border' (string) thin, thick, dashed, double, mediumDashDotDot, hair ... apply for each side with 'borderTop', 'borderRight', 'borderBottom', 'borderLeft' and 'borderDiagonal'
     *      'borderColor' (string) FFFFFF, FF0000... apply for each side with 'borderColorTop', 'borderColorRight', 'borderColorBottom', 'borderColorLeft' and 'borderColorDiagonal'
     *      'color' (string) FFFFFF, FF0000 ...
     *      'font' (string) Arial, Times New Roman ...
     *      'fontSize' (int) 8, 9, 10, 11 ...
     *      'horizontalAlign' (string) left, center, right
     *      'indent' (int)
     *      'italic' (bool)
     *      'locked' (bool)
     *      'rotation' (int) 60.000ths of a degree
     *      'shrinkToFit' (bool)
     *      'strikethrough' (bool)
     *      'subscript' (bool)
     *      'superscript' (bool)
     *      'textDirection' (string) context, ltr, rtl
     *      'typeOptions' (array)
     *          'formatCode' (string) format code
     *      'underline' (string) single, double
     *      'verticalAlign' (string) top, center, bottom
     *      'wrapText' (bool)
     * @param array $options
     *      'hidden' (bool) Default as false
     */
    public function createCellStyle($name, $styles = array(), $options = array())
    {
        // default options
        if (!isset($options['hidden'])) {
            $options['hidden'] = false;
        }

        // normalize the name
        $name = $this->parseAndCleanTextString($name);

        // preset styles: name => builtinId
        $presetStyles = array(
            'Bad' => '27',
            'Calculation' => '22',
            'Check Cell' => '23',
            'Comma' => '3',
            'Currency' => '4',
            'Explanatory Text' => '53',
            'Good' => '26',
            'Heading 1' => '16',
            'Heading 2' => '17',
            'Heading 3' => '18',
            'Heading 4' => '19',
            'Input' => '20',
            'Linked Cell' => '24',
            'Neutral' => '28',
            'Normal' => '0',
            'Note' => '10',
            'Output' => '21',
            'Percent' => '5',
            'Warning Text' => '11',
        );

        $presetStylesXML = OOXMLResources::$presetStyles;

        // cell styles
        $nodesCellStyles = $this->excelStylesDOM->getElementsByTagName('cellStyles');
        $nodescellStyleXfs = $this->excelStylesDOM->getElementsByTagName('cellStyleXfs');
        if ($nodesCellStyles->length > 0 && $nodescellStyleXfs->length > 0) {
            $nodeCellStyles = $nodesCellStyles->item(0);
            $nodeCellStylesXfs = $nodescellStyleXfs->item(0);

            // check if the style name already exists. If true don't add it
            $excelStylesXPath = new \DOMXPath($this->excelStylesDOM);
            $excelStylesXPath->registerNamespace('xmlns', $this->namespaces['xmlns']);
            $nodesCellStyleName = $excelStylesXPath->query('//xmlns:cellStyles/xmlns:cellStyle[@name="'.$name.'"]');

            if ($nodesCellStyleName->length == 0) {
                $countCellStyles = (int)$nodeCellStyles->getAttribute('count');
                $countCellStylesXfs = (int)$nodeCellStylesXfs->getAttribute('count');
                $newXfId = $countCellStyles;

                // generate and add cellStyles
                if (array_key_exists($name, $presetStyles)) {
                    // add preset style
                    $newCellStyleContent = '<cellStyle builtinId="'.$presetStyles[$name].'" name="'.$name.'" xfId="'.$newXfId.'"/>';

                    // set preset styles
                    $styles = $presetStylesXML[$name];
                } else {
                    // add custom style
                    $newCellStyleContent = '<cellStyle name="'.$name.'" xfId="'.$newXfId.'"/>';
                }
                $newNodeCellStyle = $nodeCellStyles->ownerDocument->createDocumentFragment();
                $newNodeCellStyle->appendXML($newCellStyleContent);
                $nodeCellStyles->appendChild($newNodeCellStyle);

                // generate and add styles. position as 0 and text as empty
                $styles['text'] = '';
                $newStyle = new CreateText();
                $elementsStyle = $newStyle->createElementText($styles, '0', $styles);

                // generate and add cellXfs and cellStyleXfs
                $this->generateXf($elementsStyle['textStyles'], $elementsStyle['cellStyles'], $elementsStyle['type'], (string)$newXfId, array('named' => true));

                $countCellStyles++;
                $nodeCellStyles->setAttribute('count', (string)$countCellStyles);

                $countCellStylesXfs++;
                $nodeCellStylesXfs->setAttribute('count', (string)$countCellStylesXfs);

                // refresh contents
                $this->zipXlsx->addContent('xl/styles.xml', $this->excelStylesDOM->saveXML());
                $this->excelStylesDOM = $this->zipXlsx->getContent('xl/styles.xml', 'DOMDocument');
            }
        }
    }

    /**
     * Creates table style
     *
     * @access public
     * @param string $name Style name
     * @param array $styles
     *      key (string) 'wholeTable', 'headerRow', 'totalRow', 'firstColumn', 'lastColumn', 'firstRowStripe', 'secondRowStripe', 'firstColumnStripe', 'secondColumnStripe', 'firstHeaderCell', 'lastHeaderCell', 'firstTotalCell', 'lastTotalCell'
     *      value (array)
     *          'backgroundColor' (string) FFFF00, CCCCCC ...
     *          'bold' (bool)
     *          'border' (string) thin, thick, dashed, double, mediumDashDotDot, hair ... apply for each side with 'borderTop', 'borderRight', 'borderBottom', 'borderLeft' and 'borderDiagonal'
     *          'borderColor' (string) FFFFFF, FF0000... apply for each side with 'borderColorTop', 'borderColorRight', 'borderColorBottom', 'borderColorLeft' and 'borderColorDiagonal'
     *          'color' (string) FFFFFF, FF0000 ...
     *          'font' (string) Arial, Times New Roman ...
     *          'fontSize' (int) 8, 9, 10, 11 ...
     *          'italic' (bool)
     *          'strikethrough' (bool)
     *          'underline' (string) single, double
     * @param array $options
     */
    public function createTableStyle($name, $styles = array(), $options = array())
    {
        // normalize the name
        $name = $this->parseAndCleanTextString($name);

        $supportedTypes = array('wholeTable', 'headerRow', 'totalRow', 'firstColumn', 'lastColumn', 'firstRowStripe', 'secondRowStripe', 'firstColumnStripe', 'secondColumnStripe', 'firstHeaderCell', 'lastHeaderCell', 'firstTotalCell', 'lastTotalCell');

        // check if the tableStyles tag exists
        $nodeTableStyles = null;
        $nodesTableStyles = $this->excelStylesDOM->getElementsByTagName('tableStyles');
        if ($nodesTableStyles->length == 0) {
            // there's no tableStyles tag. Create and add a tableStyles tag after the cellStyles tag
            $contentTableStyles = OOXMLResources::$stylesTableStyles;
            $nodeTableStyles = $this->excelStylesDOM->createDocumentFragment();
            $nodeTableStyles->appendXML($contentTableStyles);
            $nodesDfxs = $this->excelStylesDOM->getElementsByTagName('dxfs');
            if ($nodesDfxs->length > 0) {
                $nodeTableStyles = $nodesDfxs->item(0)->parentNode->insertBefore($nodeTableStyles, $nodesDfxs->item(0)->nextSibling);
            } else {
                $nodesCellStyles = $this->excelStylesDOM->getElementsByTagName('cellStyles');

                if ($nodesCellStyles->length > 0) {
                    $nodeTableStyles = $nodesCellStyles->item(0)->parentNode->insertBefore($nodeTableStyles, $nodesCellStyles->item(0)->nextSibling);
                }
            }
        } else {
            $nodeTableStyles = $nodesTableStyles->item(0);
        }

        // check if the dxfs tag exists
        $nodeDxfs = null;
        $nodesDxfs = $this->excelStylesDOM->getElementsByTagName('dxfs');
        if ($nodesDxfs->length == 0) {
            // there's no dxfs tag. Create and add a dxfs tag after cellStyles tag
            $nodeDxfs = $this->excelStylesDOM->createDocumentFragment();
            $nodeDxfs->appendXML('<dxfs count="0">');
            $nodesCellStyles = $this->excelStylesDOM->getElementsByTagName('cellStyles');
            if ($nodesCellStyles->length > 0) {
                $nodeDxfs = $nodesCellStyles->item(0)->parentNode->insertBefore($nodeDxfs, $nodesCellStyles->item(0)->nextSibling);
            }
        } else {
            $nodeDxfs = $nodesDxfs->item(0);
        }

        if (!empty($nodeTableStyles) && !empty($nodeDxfs)) {
            // add the new table style if tableStyles and dxfs nodes exist
            $newTableStyle = '';
            $addTableStyle = false;
            $countDxfs = $nodeDxfs->getAttribute('count');
            $countTableStyle = 0;
            $dxfNewContents = array();
            foreach ($styles as $stylesKey => $stylesValue) {
                if (in_array($stylesKey, $supportedTypes)) {
                    $addTableStyle = true;
                    $countDxfs++;
                    $newTableStyle .= '<tableStyleElement dxfId="'.(int)($countDxfs-1).'" type="'.$stylesKey.'" />';
                    $styles = new CreateStyles();
                    $dxfNewContents[] = $styles->createDxfStyles($stylesValue);
                    $countTableStyle++;
                }
            }

            if ($addTableStyle) {
                // update dxfs count
                $nodeDxfs->setAttribute('count', $countDxfs);

                // insert new dxf tags
                foreach ($dxfNewContents as $dxfNewContent) {
                    $nodeDxf = $this->excelStylesDOM->createDocumentFragment();
                    $nodeDxf->appendXML($dxfNewContent);
                    $nodeDxfs->appendChild($nodeDxf);
                }

                // table style content
                $newTableStyle = '<tableStyle count="'.$countTableStyle.'" name="'.$name.'" pivot="0">'.$newTableStyle.'</tableStyle>';

                // update table styles count
                $countTableStyles = (int)$nodeTableStyles->getAttribute('count');
                $countTableStyles++;
                $nodeTableStyles->setAttribute('count', (string)$countTableStyles);

                // insert new tableStyle tag
                $nodeTableStyle = $this->excelStylesDOM->createDocumentFragment();
                $nodeTableStyle->appendXML($newTableStyle);
                $nodeTableStyles->appendChild($nodeTableStyle);

                // refresh contents
                $this->zipXlsx->addContent('xl/styles.xml', $this->excelStylesDOM->saveXML());
                $this->excelStylesDOM = $this->zipXlsx->getContent('xl/styles.xml', 'DOMDocument');
            }
        }
    }

    /**
     * Gets information from a cell
     *
     * @access public
     * @param string $position Cell position in the current active sheet: A1, C3, AB7...
     * @return array|null
     *      'function' (string)
     *      'numFmtId' (string)
     *      'sharedString' (string)
     *      'styleIndex' (string)
     *      'styles' (array)
     *      'type' (string)
     *      'value' (string)
     */
    public function getCell($position)
    {
        $cellInformation = null;
        $activeSheetContent = null;

        // get cell information
        $cellPosition = $this->getPositionInfo($position, 'array');

        // get sheets
        $sheetsContents = $this->zipXlsx->getSheets();
        // the active sheet is the current one unless $cellPosition['sheet'] has a value to set other sheet
        if (isset($cellPosition['sheet']) && !empty($cellPosition['sheet'])) {
            $iSheetIndex = 0;
            foreach ($sheetsContents as $sheetsContent) {
                if ($sheetsContent['name'] == $cellPosition['sheet']) {
                    $activeSheetContent = $sheetsContents[$iSheetIndex];
                    break;
                }
                $iSheetIndex++;
            }
        } else {
            $activeSheetContent = $sheetsContents[$this->activeSheet['position']];
        }
        if ($activeSheetContent) {
            $sheetDOM = $this->xmlUtilities->generateDomDocument($activeSheetContent['content']);

            // check if the row cell exists in the sheet
            $sheetXPath = new \DOMXPath($sheetDOM);
            $sheetXPath->registerNamespace('xmlns', $this->namespaces['xmlns']);
            $cellNode = $sheetXPath->query('//xmlns:sheetData/xmlns:row[@r="'.$cellPosition['number'].'"]/xmlns:c[@r="'.$cellPosition['text'].$cellPosition['number'].'"]');
            if ($cellNode->length > 0) {
                $cellInformation = array();

                if ($cellNode->item(0)->hasAttribute('t')) {
                    $cellInformation['type'] = $cellNode->item(0)->getAttribute('t');
                }
                // check if there's a f node
                $fNode = $cellNode->item(0)->getElementsByTagName('f');
                if ($fNode->length > 0) {
                    $cellInformation['function'] = $fNode->item(0)->nodeValue;
                }
                // check if there's a v node
                $vNode = $cellNode->item(0)->getElementsByTagName('v');
                if ($vNode->length > 0) {
                    $cellInformation['value'] = $vNode->item(0)->nodeValue;
                }
                // check if there's a cell style
                if ($cellNode->item(0)->hasAttribute('s')) {
                    $cellInformation['styleIndex'] = $cellNode->item(0)->getAttribute('s');

                    // check if the style has numFmtId
                    $nodesCellXfs = $this->excelStylesDOM->getElementsByTagName('cellXfs');
                    if ($nodesCellXfs->length > 0) {
                        $nodesXf = $nodesCellXfs->item(0)->getElementsByTagName('xf');
                        if ($nodesXf->length >= $cellInformation['styleIndex']) {
                            $nodeXf = $nodesXf->item($cellInformation['styleIndex']);
                            if ($nodeXf->hasAttribute('numFmtId')) {
                                $cellInformation['numFmtId'] = $nodeXf->getAttribute('numFmtId');
                            }

                            $cellStyles = array();
                            if ($nodeXf->hasAttributes()) {
                                // attribute styles
                                foreach ($nodeXf->attributes as $attributeStyle) {
                                    $cellStyles[$attributeStyle->nodeName] = $attributeStyle->nodeValue;
                                }
                            }
                            if ($nodeXf->childNodes->length > 0) {
                                // tag style
                                foreach ($nodeXf->childNodes as $childStyle) {
                                    $cellStyles[$childStyle->tagName] = array();
                                    if ($childStyle->hasAttributes()) {
                                        // internal tag styles
                                        foreach ($childStyle->attributes as $attributeStyle) {
                                            $cellStyles[$childStyle->tagName][$attributeStyle->nodeName] = $attributeStyle->nodeValue;
                                        }
                                    }
                                }
                            }
                            if (count($cellStyles) > 0) {
                                // keep cell styles
                                $cellInformation['styles'] = $cellStyles;
                            }
                        }
                    }
                }

                // if s type, get the shared string content instead of the v value
                if (isset($cellInformation['type']) && $cellInformation['type'] == 's' && isset($cellInformation['value'])) {
                    $sharedStringsContents = $this->zipXlsx->getContentByType('sharedStrings');
                    if (count($sharedStringsContents) > 0) {
                        $sharedStringsDOM = $this->xmlUtilities->generateDomDocument($sharedStringsContents[0]['content']);
                        $sharedStringsXPath = new \DOMXPath($sharedStringsDOM);
                        $sharedStringsXPath->registerNamespace('xmlns', $this->namespaces['xmlns']);
                        // get by position. XPath begins from 1
                        $siNode = $sharedStringsXPath->query('//xmlns:si['.((int)$cellInformation['value']+1).']');
                        if ($siNode->length > 0) {
                            $cellInformation['sharedString'] = $siNode->item(0)->nodeValue;
                        }
                    }
                }
            }

            // free DOMDocument resources
            $sheetDOM = null;

            PhpxlsxLogger::logger('Get cell information.', 'info');
        }

        return $cellInformation;
    }

    /**
     * Gets existing cell positions from the active sheet
     *
     * @access public
     * @return array row (array key) and columns (array values)
     */
    public function getCellPositions()
    {
        $cellPositions = array();

        // get the active sheet
        $sheetsContents = $this->zipXlsx->getSheets();
        $activeSheetContent = $sheetsContents[$this->activeSheet['position']];
        $sheetDOM = $this->xmlUtilities->generateDomDocument($activeSheetContent['content']);
        // check if the row to add the text exists in the sheet
        $sheetXPath = new \DOMXPath($sheetDOM);
        $sheetXPath->registerNamespace('xmlns', $this->namespaces['xmlns']);
        $rowNodes = $sheetXPath->query('//xmlns:sheetData/xmlns:row');

        foreach ($rowNodes as $rowNode) {
            $columnNodes = array();

            $cNodes = $rowNode->getElementsByTagName('c');
            if ($cNodes->length > 0) {
                foreach ($cNodes as $cNode) {
                    if ($cNode->hasAttribute('r')) {
                        $columnNodes[] = $cNode->getAttribute('r');
                    }
                }
            }

            if ($rowNode->hasAttribute('r')) {
                $cellPositions[$rowNode->getAttribute('r')] = $columnNodes;
            }
        }

        // free DOMDocument resources
        $sheetDOM = null;

        PhpxlsxLogger::logger('Get existing cell positions.', 'info');

        return $cellPositions;
    }

    /**
     * Gets column settings
     *
     * @access public
     * @param string $position Column position in the current active sheet: A, B, C... A1, C3, AB7...
     * @return array|null
     */
    public function getColumnSettings($position)
    {
        $columnSettings = null;

        // normalize the position
        $position = $this->getPositionInfo($position);

        // get the content position and keep it
        $cellPosition = $this->getPositionInfo($position, 'array');

        // get the active sheet elements
        $activeSheetElements = $this->getActiveSheetElements();

        // check if the cols tag exists
        $colsNodes = $activeSheetElements['sheetXPath']->query('//xmlns:cols');
        if ($colsNodes->length > 0) {
            $colsNode = $colsNodes->item(0);

            // get the value that corresponds to the column string of the position
            $elementObject = new CreateElement();
            $columnIntValue = $elementObject->wordToInt($cellPosition['text']);
            // 1 is the first value, not 0 as wordToInt returns
            $columnIntValue++;

            // check if the col tag exist
            $colPositionNode = $activeSheetElements['sheetXPath']->query('//xmlns:cols/xmlns:col[@min="'.$columnIntValue.'"]');
            if ($colPositionNode->length > 0) {
                $colNode = $colPositionNode->item(0);

                if ($colNode->hasAttributes()) {
                    $columnSettings = array();
                    foreach ($colNode->attributes as $colNodeAttribute) {
                        $columnSettings[$colNodeAttribute->nodeName] = $colNodeAttribute->nodeValue;
                    }
                }
            }
        }

        return $columnSettings;
    }

    /**
     * Gets row settings
     *
     * @access public
     * @param string $position Row position in the current active sheet: 1, 2... A1, B3...
     * @return array|null
     */
    public function getRowSettings($position)
    {
        $rowSettings = null;

        // normalize the position
        $position = $this->getPositionInfo($position);

        // get the content position and keep it
        $cellPosition = $this->getPositionInfo($position, 'array');

        // get the active sheet elements
        $activeSheetElements = $this->getActiveSheetElements();

        // check if the row number exists
        $rowPositionNode = $activeSheetElements['sheetXPath']->query('//xmlns:sheetData/xmlns:row[@r="'.$cellPosition['number'].'"]');
        if ($rowPositionNode->length > 0) {
            $rowNode = $rowPositionNode->item(0);

            if ($rowNode->hasAttributes()) {
                $rowSettings = array();
                foreach ($rowNode->attributes as $rowNodeAttribute) {
                    $rowSettings[$rowNodeAttribute->nodeName] = $rowNodeAttribute->nodeValue;
                }
            }
        }

        return $rowSettings;
    }

    /**
     * Removes cell
     *
     * @access public
     * @param string $position Cell position in the current active sheet: A1, C3, AB7...
     * @param array $options
     */
    public function removeCell($position, $options = array())
    {
        // normalize the position
        $position = $this->getPositionInfo($position);

        // get the active sheet
        $sheetsContents = $this->zipXlsx->getSheets();
        $activeSheetContent = $sheetsContents[$this->activeSheet['position']];
        $sheetDOM = $this->xmlUtilities->generateDomDocument($activeSheetContent['content']);

        // check if the cell to be removed exists
        $sheetXPath = new \DOMXPath($sheetDOM);
        $sheetXPath->registerNamespace('xmlns', $this->namespaces['xmlns']);
        $cellPositionNode = $sheetXPath->query('//xmlns:sheetData/xmlns:row/xmlns:c[@r="'.$position.'"]');
        if ($cellPositionNode->length > 0) {
            $cellPositionNode->item(0)->parentNode->removeChild($cellPositionNode->item(0));
        }

        // clean calcChain
        $this->cleanCalcChain($position);

        PhpxlsxLogger::logger('Remove cell.', 'info');

        // refresh contents
        $this->zipXlsx->addContent($activeSheetContent['path'], $sheetDOM->saveXML());

        // free DOMDocument resources
        $sheetDOM = null;
    }

    /**
     * Generates the new XLSX file
     *
     * @access public
     * @param string $fileName path to the resulting xlsx
     * @param array $options
     *      'removeCalcChain' (bool) if true, remove calcChain file from the XLSX. MS Excel regenerates it automatically. Default as false
     * @throws \Exception license is not valid
     * @throws \Exception the XLSX can't be saved
     * @return XlsxStructure
     */
    public function saveXlsx($fileName = 'book', $options = array())
    {
        PhpxlsxLogger::logger('Set XLSX name to: ' . $fileName . '.', 'info');

        // refresh contents
        $this->zipXlsx->addContent('[Content_Types].xml', $this->excelContentTypesDOM->saveXML());
        $this->zipXlsx->addContent('xl/_rels/workbook.xml.rels', $this->excelRelsWorkbookDOM->saveXML());
        $this->zipXlsx->addContent('xl/styles.xml', $this->excelStylesDOM->saveXML());
        $this->zipXlsx->addContent('xl/workbook.xml', $this->excelWorkbookDOM->saveXML());

        if (isset($options['removeCalcChain']) && $options['removeCalcChain']) {
            $calcChainContents = $this->zipXlsx->getContentByType('calcChain');
            foreach ($calcChainContents as $calcChainContent) {
                $this->zipXlsx->deleteContent($calcChainContent['path']);
            }
        }

        PhpxlsxLogger::logger('Create XLSX.', 'info');

        return $this->zipXlsx->saveXlsx($fileName);
    }

    /**
     * Generate and download a new XLSX file
     *
     * @access public
     * @param string $fileName file name
     * @param bool $removeAfterDownload remove the file after download it
     * @param array $options
     *      'removeCalcChain' (bool) if true, remove calcChain file from the XLSX. MS Excel regenerates it automatically. Default as false
     * @throws \Exception the XLSX can't be saved
     */
    public function saveXlsxAndDownload($fileName, $removeAfterDownload = false, $options = array())
    {
        try {
            $this->saveXlsx($fileName);
        } catch (\Exception $e) {
            PhpxlsxLogger::logger('Error while trying to write to ' . $fileName . ' . Check write access.', 'fatal');
        }

        if (isset($fileName) && !empty($fileName)) {
            $fileName = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $fileName);
            $completeName = explode(DIRECTORY_SEPARATOR, $fileName);
            $fileNameDownload = array_pop($completeName);
        } else {
            $fileName = 'Book';
            $fileNameDownload = 'Book';
        }

        // check if the path has as extension, and remove it if true
        if (substr($fileNameDownload, -5) == '.xlsx' || substr($fileNameDownload, -5) == '.xlsm') {
            $fileNameDownload = substr($fileNameDownload, 0, -5);
        }

        // get absolute path to the file to be used with filesize and readfile methods
        $fileInfo = pathinfo($fileName);
        $filePath = $fileInfo['dirname'] . '/' . $fileNameDownload;

        $extension = 'xlsx';
        PhpxlsxLogger::logger('Download file ' . $fileNameDownload . '.' . $extension . '.', 'info');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileNameDownload . '.' . $extension . '"');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($filePath . '.' . $extension));
        readfile($filePath . '.' . $extension);

        // remove the generated file
        if ($removeAfterDownload) {
            unlink($filePath . '.' . $extension);
        }
    }

    /**
     * Sets a cell value keeping existing styles
     *
     * @access public
     * @param string|array $contents @see addCell
     * @param string $position Cell position in the current active sheet: A1, C3, AB7...
     */
    public function setCellValue($contents, $position)
    {
        $this->addCell($contents, $position, array(), array('useCellStyles' => true));
    }

    /**
     * Sets column settings
     *
     * @access public
     * @param string $position Column position in the current active sheet: A, B, C... A1, C3, AB7...
     * @param array $options
     *      'hidden' (bool)
     *      'width' (float) Column width
     */
    public function setColumnSettings($position, $options = array())
    {
        // normalize the position
        $position = $this->getPositionInfo($position);

        // get the content position and keep it
        $cellPosition = $this->getPositionInfo($position, 'array');

        // get the active sheet elements
        $activeSheetElements = $this->getActiveSheetElements();

        // check if the cols tag exists
        $colsNodes = $activeSheetElements['sheetXPath']->query('//xmlns:cols');
        if ($colsNodes->length == 0) {
            // there's no cols tag. Create and add a cols tag before the sheetData tag
            $colsContent = '<cols />';
            $nodesSheetData = $activeSheetElements['sheetDOM']->getElementsByTagName('sheetData');
            $colsNewNode = $nodesSheetData->item(0)->ownerDocument->createDocumentFragment();
            $colsNewNode->appendXML($colsContent);
            $colsNode = $nodesSheetData->item(0)->parentNode->insertBefore($colsNewNode, $nodesSheetData->item(0));
        } else {
            $colsNode = $colsNodes->item(0);
        }

        // get the value that corresponds to the column string of the position
        $elementObject = new CreateElement();
        $columnIntValue = $elementObject->wordToInt($cellPosition['text']);
        // 1 is the first value, not 0 as wordToInt returns
        $columnIntValue++;

        // check if the col tag exist
        $colPositionNode = $activeSheetElements['sheetXPath']->query('//xmlns:cols/xmlns:col[@min="'.$columnIntValue.'"]');
        if ($colPositionNode->length == 0) {
            // there's no matching col. Create and add it
            $colNodes = $activeSheetElements['sheetXPath']->query('//xmlns:cols/xmlns:col');
            // new col content to be added
            $colContent = '<col/>';
            $colNewNode = $colsNode->ownerDocument->createDocumentFragment();
            $colNewNode->appendXML($colContent);
            if ($colNodes->length > 0) {
                // there are more cols. Add the new col in the correct order: before the next col by position
                $colNewPosition = 0;
                foreach ($colNodes as $colNode) {
                    $currentColValue = (int)$colNode->getAttribute('min');
                    if ($currentColValue > $columnIntValue) {
                        break;
                    }
                    $colNewPosition++;
                }
                if ($colNodes->length > $colNewPosition) {
                    // append before an existing col
                    $colNode = $colNodes->item($colNewPosition)->parentNode->insertBefore($colNewNode, $colNodes->item($colNewPosition));
                } else {
                    // append at the end
                    $colNode = $colNodes->item(0)->parentNode->appendChild($colNewNode);
                }
            } else {
                // there aren't col tags. Add the new one as cols child element
                $colNode = $colsNode->appendChild($colNewNode);
            }
        } else {
            // there's a matching col, modify it
            $colNode = $colPositionNode->item(0);
        }

        // apply the attributes to the column node created from scratch or already existing
        if (isset($options['hidden'])) {
            if ($options['hidden']) {
                $colNode->setAttribute('hidden', 1);

                $colNode->setAttribute('min', $columnIntValue);
                $colNode->setAttribute('max', $columnIntValue);
            } else {
                $colNode->removeAttribute('hidden');
            }
        }
        if (isset($options['width'])) {
            $colNode->setAttribute('customWidth', 1);
            $colNode->setAttribute('width', $options['width']);
            $colNode->setAttribute('min', $columnIntValue);
            $colNode->setAttribute('max', $columnIntValue);
        }

        // refresh contents
        $this->zipXlsx->addContent($activeSheetElements['activeSheetContent']['path'], $activeSheetElements['sheetDOM']->saveXML());

        // free DOMDocument resources
        $sheetDOM = null;

        PhpxlsxLogger::logger('Set column settings.', 'info');
    }

    /**
     * Sets row settings
     *
     * @access public
     * @param string $position Row position in the current active sheet: 1, 2... A1, B3...
     * @param array $options
     *      'height' (float) Row height
     *      'hidden' (bool)
     */
    public function setRowSettings($position, $options = array())
    {
        // normalize the position
        $position = $this->getPositionInfo($position);

        // get the content position and keep it
        $cellPosition = $this->getPositionInfo($position, 'array');

        // get the active sheet elements
        $activeSheetElements = $this->getActiveSheetElements();

        $rowPositionNode = $activeSheetElements['sheetXPath']->query('//xmlns:sheetData/xmlns:row[@r="'.$cellPosition['number'].'"]');
        if ($rowPositionNode->length == 0) {
            // there's no matching row. Create and add it
            $rowsNodes = $activeSheetElements['sheetXPath']->query('//xmlns:sheetData/xmlns:row');
            // new row content to be added
            $rowContent = '<row r="'.$cellPosition['number'].'"/>';
            $nodesSheetData = $activeSheetElements['sheetDOM']->getElementsByTagName('sheetData');
            $rowNewNode = $nodesSheetData->item(0)->ownerDocument->createDocumentFragment();
            $rowNewNode->appendXML($rowContent);
            if ($rowsNodes->length > 0) {
                // there are more rows. Add the new row in the correct order: before the next row by position
                $rowNewPosition = 0;
                foreach ($rowsNodes as $rowsNode) {
                    $currentRowValue = (int)$rowsNode->getAttribute('r');
                    if ($currentRowValue > (int)$cellPosition['number']) {
                        break;
                    }
                    $rowNewPosition++;
                }
                if ($rowsNodes->length > $rowNewPosition) {
                    // append before an existing row
                    $rowNode = $rowsNodes->item($rowNewPosition)->parentNode->insertBefore($rowNewNode, $rowsNodes->item($rowNewPosition));
                } else {
                    // append at the end
                    $rowNode = $rowsNodes->item(0)->parentNode->appendChild($rowNewNode);
                }
            } else {
                // there aren't rows. Add the new one as sheetData child element
                $rowNode = $nodesSheetData->item(0)->appendChild($rowNewNode);
            }
        } else {
            // there's a matching row, modify it
            $rowNode = $rowPositionNode->item(0);
        }

        // apply the attributes to the row node created from scratch or already existing
        if (isset($options['height'])) {
            $rowNode->setAttribute('customHeight', 1);
            $rowNode->setAttribute('ht', $options['height']);
        }
        if (isset($options['hidden'])) {
            if ($options['hidden']) {
                $rowNode->setAttribute('hidden', 1);
            } else {
                $rowNode->removeAttribute('hidden');
            }
        }

        // refresh contents
        $this->zipXlsx->addContent($activeSheetElements['activeSheetContent']['path'], $activeSheetElements['sheetDOM']->saveXML());

        // free DOMDocument resources
        $sheetDOM = null;

        PhpxlsxLogger::logger('Set row settings.', 'info');
    }

    /**
     * Sets global right to left options
     * @access public
     * @param array $options
     *      'rtl' (bool)
     */
    public function setRtl($options = array('rtl' => true))
    {
        if (isset($options['rtl']) && $options['rtl']) {
            self::$rtl = true;
        } else if (isset($options['rtl']) && !$options['rtl']) {
            self::$rtl = false;
        }

        PhpxlsxLogger::logger('Enable RTL mode.', 'info');
    }

    /**
     * Sets sheet settings
     *
     * @access public
     * @param array $options
     *      'activeCell' (string) active cell
     *      'color' (string) HEX value
     *      'defaultColWidth' (int)
     *      'defaultRowHeight' (int)
     *      'marginTop' (float) measurement in inches
     *      'marginRight' (float) measurement in inches
     *      'marginBottom' (float) measurement in inches
     *      'marginLeft' (float) measurement in inches
     *      'marginHeader' (float) measurement in inches
     *      'marginFooter' (float) measurement in inches
     *      'orient' (string) portrait, landscape
     *      'paperHeight' (string) custom paper height (mm, cm, in, pt, pc, pi)
     *      'paperSize' (int) paper size code (SpreadsheetML values)
     *      'paperType' (string) preset values: A4, A3, letter, legal, A4-landscape, A3-landscape, letter-landscape, legal-landscape
     *      'paperWidth' (string) custom paper width (mm, cm, in, pt, pc, pi)
     *      'rtl' (bool) set to true for right to left
     *      'state' (string) hidden, visible
     *      'tabSelected' (bool) tab selected
     *      'view' (string) normal, pageBreakPreview, pageLayout
     */
    public function setSheetSettings($options = array())
    {
        $referenceTypes = array(
            'A4' => array(
                'orientation' => 'portrait',
                'paperSize' => 9,
            ),
            'A4-landscape' => array(
                'orientation' => 'landscape',
                'paperSize' => 9,
            ),
            'A3' => array(
                'orientation' => 'portrait',
                'paperSize' => 8,
            ),
            'A3-landscape' => array(
                'orientation' => 'landscape',
                'paperSize' => 8,
            ),
            'letter' => array(
                'orientation' => 'portrait',
                'paperSize' => 1,
            ),
            'letter-landscape' => array(
                'orientation' => 'landscape',
                'paperSize' => 1,
            ),
            'legal' => array(
                'orientation' => 'portrait',
                'paperSize' => 5,
            ),
            'legal-landscape' => array(
                'orientation' => 'landscape',
                'paperSize' => 5,
            ),
        );

        // get the active sheet
        $sheetsContents = $this->zipXlsx->getSheets();
        $activeSheetContent = $sheetsContents[$this->activeSheet['position']];
        $sheetDOM = $this->xmlUtilities->generateDomDocument($activeSheetContent['content']);

        // pageMargins node
        if (isset($options['marginTop']) || isset($options['marginRight']) || isset($options['marginBottom']) || isset($options['marginLeft']) || isset($options['marginHeader']) || isset($options['marginFooter'])) {
            $nodePageMargins = $sheetDOM->getElementsByTagName('pageMargins');
            // create pageMargins node if no one is found
            if ($nodePageMargins->length == 0) {
                // get sheetData node to be used as reference of the new node
                $nodesSheetData = $sheetDOM->getElementsByTagName('sheetData');
                $elementPageMargins = $sheetDOM->createElement('pageMargins');
                $nodesSheetData->item(0)->nextSibling->parentNode->insertBefore($elementPageMargins, $nodesSheetData->item(0)->nextSibling);

                // set the new node
                $nodePageMargins = $sheetDOM->getElementsByTagName('pageMargins');
            }
            if ($nodePageMargins->length > 0) {
                if (isset($options['marginTop'])) {
                    $nodePageMargins->item(0)->setAttribute('top', $options['marginTop']);
                }
                if (isset($options['marginRight'])) {
                    $nodePageMargins->item(0)->setAttribute('right', $options['marginRight']);
                }
                if (isset($options['marginBottom'])) {
                    $nodePageMargins->item(0)->setAttribute('bottom', $options['marginBottom']);
                }
                if (isset($options['marginLeft'])) {
                    $nodePageMargins->item(0)->setAttribute('left', $options['marginLeft']);
                }
                if (isset($options['marginHeader'])) {
                    $nodePageMargins->item(0)->setAttribute('header', $options['marginHeader']);
                }
                if (isset($options['marginFooter'])) {
                    $nodePageMargins->item(0)->setAttribute('footer', $options['marginFooter']);
                }
            }
        }

        // pageSetup node
        if (isset($options['paperHeight']) || isset($options['paperSize']) || isset($options['paperType']) || isset($options['paperWidth']) || isset($options['orientation'])) {
            $nodePageSetup = $sheetDOM->getElementsByTagName('pageSetup');
            // create pageSetup node if no one is found
            if ($nodePageSetup->length == 0) {
                // get pageMargins node to be used as reference of the new node
                $nodesPageMargins = $sheetDOM->getElementsByTagName('pageMargins');
                $elementPageSetup = $sheetDOM->createElement('pageSetup');
                $nodesPageMargins->item(0)->parentNode->insertBefore($elementPageSetup, $nodesPageMargins->item(0)->nextSibling);

                // set the new node
                $nodePageSetup = $sheetDOM->getElementsByTagName('pageSetup');
            }
            if ($nodePageSetup->length > 0) {
                // set preset values
                if (isset($options['paperType']) && isset($referenceTypes[$options['paperType']])) {
                    if (!isset($options['orientation'])) {
                        $options['orientation'] = $referenceTypes[$options['paperType']]['orientation'];
                    }
                    if (!isset($options['paperSize'])) {
                        $options['paperSize'] = $referenceTypes[$options['paperType']]['paperSize'];
                    }
                }

                if (isset($options['orientation'])) {
                    $nodePageSetup->item(0)->setAttribute('orientation', $options['orientation']);
                }
                if (isset($options['paperHeight'])) {
                    $nodePageSetup->item(0)->setAttribute('paperHeight', $options['paperHeight']);
                }
                if (isset($options['paperSize'])) {
                    $nodePageSetup->item(0)->setAttribute('paperSize', $options['paperSize']);
                }
                if (isset($options['paperWidth'])) {
                    $nodePageSetup->item(0)->setAttribute('paperWidth', $options['paperWidth']);
                }
            }
        }

        // rtl
        if (isset($options['rtl']) || self::$rtl) {
            $nodesSheetViews = $sheetDOM->getElementsByTagName('sheetViews');
            if ($nodesSheetViews->length > 0) {
                foreach ($nodesSheetViews as $nodeSheetViews) {
                    $nodesSheetView = $nodeSheetViews->getElementsByTagName('sheetView');
                    if ($nodesSheetView->length > 0) {
                        foreach ($nodesSheetView as $nodeSheetView) {
                            if ($options['rtl'] || self::$rtl) {
                                $nodeSheetView->setAttribute('rightToLeft', '1');
                            } else if ($nodeSheetView->hasAttribute('rightToLeft')) {
                                $nodeSheetView->removeAttribute('rightToLeft');
                            }
                        }
                    }
                }
            }
        }

        // tabSelected
        if (isset($options['tabSelected'])) {
            $nodesSheetViews = $sheetDOM->getElementsByTagName('sheetViews');
            if ($nodesSheetViews->length > 0) {
                foreach ($nodesSheetViews as $nodeSheetViews) {
                    $nodesSheetView = $nodeSheetViews->getElementsByTagName('sheetView');
                    if ($nodesSheetView->length > 0) {
                        foreach ($nodesSheetView as $nodeSheetView) {
                            if ($options['tabSelected']) {
                                $nodeSheetView->setAttribute('tabSelected', '1');
                            } else if ($nodeSheetView->hasAttribute('tabSelected')) {
                                $nodeSheetView->removeAttribute('tabSelected');
                            }
                        }
                    }
                }
            }
        }

        // activeCell
        if (isset($options['activeCell']) && !empty($options['activeCell'])) {
            $nodesSheetViews = $sheetDOM->getElementsByTagName('sheetViews');
            if ($nodesSheetViews->length > 0) {
                foreach ($nodesSheetViews as $nodeSheetViews) {
                    $nodesSheetView = $nodeSheetViews->getElementsByTagName('sheetView');
                    if ($nodesSheetView->length > 0) {
                        foreach ($nodesSheetView as $nodeSheetView) {
                            $nodesSelection = $nodeSheetView->getElementsByTagName('selection');
                            if ($nodesSelection->length == 0) {
                                $newSelection = '<selection activeCell="'.$options['activeCell'].'" sqref="'.$options['activeCell'].'"/>';
                                $newNode = $nodeSheetView->ownerDocument->createDocumentFragment();
                                $newNode->appendXML($newSelection);

                                $nodeSheetView->appendChild($newNode);

                                $nodesSelection = $nodeSheetView->getElementsByTagName('selection');
                            }
                            $nodesSelection->item(0)->setAttribute('activeCell', $options['activeCell']);
                            $nodesSelection->item(0)->setAttribute('sqref', $options['activeCell']);
                        }
                    }
                }
            }
        }

        // view
        if (isset($options['view']) && !empty($options['view'])) {
            $nodesSheetViews = $sheetDOM->getElementsByTagName('sheetViews');
            if ($nodesSheetViews->length > 0) {
                foreach ($nodesSheetViews as $nodeSheetViews) {
                    $nodesSheetView = $nodeSheetViews->getElementsByTagName('sheetView');
                    if ($nodesSheetView->length > 0) {
                        foreach ($nodesSheetView as $nodeSheetView) {
                            $nodeSheetView->setAttribute('view', $options['view']);
                        }
                    }
                }
            }
        }

        // color
        if (isset($options['color']) && !empty($options['color'])) {
            // normalize color
            $options['color'] = strtoupper(str_replace('#', '', $options['color']));
            $nodesDimension = $sheetDOM->getElementsByTagName('dimension');
            if ($nodesDimension->length > 0) {
                $nodesTabcolor = $sheetDOM->getElementsByTagName('tabColor');

                if ($nodesTabcolor->length > 0) {
                    // update existing tab color
                    $nodesTabcolor->item(0)->setAttribute('rgb', $options['color']);
                } else {
                    $relsNodeSheetPr = $sheetDOM->createDocumentFragment();
                    // append 00 to add alpha information
                    $relsNodeSheetPr->appendXML('<sheetPr><tabColor rgb="'.$options['color'].'"/></sheetPr>');

                    $nodesDimension->item(0)->parentNode->insertBefore($relsNodeSheetPr, $nodesDimension->item(0));
                }
            }
        }

        // handle default sizes
        if (isset($options['defaultColWidth'])) {
            $nodesSheetFormatPr = $sheetDOM->getElementsByTagName('sheetFormatPr');
            if ($nodesSheetFormatPr->length > 0) {
                $nodesSheetFormatPr->item(0)->setAttribute('defaultColWidth', (string)$options['defaultColWidth']);
            }
        }
        if (isset($options['defaultRowHeight'])) {
            $nodesSheetFormatPr = $sheetDOM->getElementsByTagName('sheetFormatPr');
            if ($nodesSheetFormatPr->length > 0) {
                $nodesSheetFormatPr->item(0)->setAttribute('defaultRowHeight', (string)$options['defaultRowHeight']);
                $nodesSheetFormatPr->item(0)->setAttribute('customHeight', 'true');
            }
        }

        // refresh contents
        $this->zipXlsx->addContent($activeSheetContent['path'], $sheetDOM->saveXML());

        // Workbook scope
        // state
        if (isset($options['state']) && !empty($options['state'])) {
            $currentSheetsTag = $this->excelWorkbookDOM->getElementsByTagName('sheets')->item(0);
            $sheetTags = $currentSheetsTag->getElementsByTagName('sheet');
            if ($options['state'] == 'hidden') {
                $sheetTags->item($this->activeSheet['position'])->setAttribute('state', 'hidden');
            } else if ($options['state'] == 'visible') {
                $sheetTags->item($this->activeSheet['position'])->removeAttribute('state');
            }

            // refresh contents
            $this->zipXlsx->addContent('xl/workbook.xml', $this->excelWorkbookDOM->saveXML());
            $this->excelWorkbookDOM = $this->zipXlsx->getContent('xl/workbook.xml', 'DOMDocument');
        }

        // free DOMDocument resources
        $sheetDOM = null;

        PhpxlsxLogger::logger('Set sheet settings.', 'info');
    }

    /**
     * Sets workbook settings
     *
     * @access public
     * @param array $options
     *      'activeTab' (int) active tab
     *      'activeSheetAsActiveTab' (bool) active sheet number as the active tab. Default as false
     *      'forceFullCalc' (bool) force Full Calculation
     *      'fullCalcOnLoad' (bool) full Calculation On Load
     *      'readOnly' (bool) set as read only
     */
    public function setWorkbookSettings($options = array())
    {
        // handle activeTab option
        if (isset($options['activeTab'])) {
            $workbookViewTag = $this->excelWorkbookDOM->getElementsByTagName('workbookView');
            if ($workbookViewTag->length > 0) {
                $workbookViewTag->item(0)->setAttribute('activeTab', $options['activeTab']);
            }
        }

        // handle activeSheetAsActiveTab option
        if (isset($options['activeSheetAsActiveTab']) && $options['activeSheetAsActiveTab']) {
            $workbookViewTag = $this->excelWorkbookDOM->getElementsByTagName('workbookView');
            if ($workbookViewTag->length > 0) {
                $workbookViewTag->item(0)->setAttribute('activeTab', $this->activeSheet['position']);
            }
        }

        // handle calcPr options
        if (isset($options['forceFullCalc']) || isset($options['fullCalcOnLoad'])) {
            $calcPrTag = $this->excelWorkbookDOM->getElementsByTagName('calcPr');
            if ($calcPrTag->length > 0) {
                if (isset($options['forceFullCalc'])) {
                    if ($options['forceFullCalc']) {
                        $calcPrTag->item(0)->setAttribute('forceFullCalc', '1');
                    } else {
                        $calcPrTag->item(0)->setAttribute('forceFullCalc', '0');
                    }
                }
                if (isset($options['fullCalcOnLoad'])) {
                    if ($options['fullCalcOnLoad']) {
                        $calcPrTag->item(0)->setAttribute('fullCalcOnLoad', '1');
                    } else {
                        $calcPrTag->item(0)->setAttribute('fullCalcOnLoad', '0');
                    }
                }
            }
        }

        // handle readOnly option
        if (isset($options['readOnly'])) {
            $fileSharingTag = $this->excelWorkbookDOM->getElementsByTagName('fileSharing');
            if ($options['readOnly']) {
                if ($fileSharingTag->length > 0) {
                    // enable readOnly in the existing option
                    $fileSharingTag->item(0)->setAttribute('readOnlyRecommended', '1');
                } else {
                    // the option doesn't exist. Create it after the fileVersion tag
                    $newNodeFileSharingContent = '<fileSharing readOnlyRecommended="1"/>';
                    $newNodeFileSharing = $this->excelWorkbookDOM->createDocumentFragment();
                    $newNodeFileSharing->appendXML($newNodeFileSharingContent);

                    $fileVersionTag = $this->excelWorkbookDOM->getElementsByTagName('fileVersion');
                    if ($fileVersionTag->length > 0) {
                        $fileVersionTag->item(0)->parentNode->insertBefore($newNodeFileSharing, $fileVersionTag->item(0)->nextSibling);
                    }
                }
            } else {
                if ($fileSharingTag->length > 0) {
                    // disable readOnly in the existing option.
                    // If the option doesn't exist, it's not applied as default
                    $fileSharingTag->item(0)->setAttribute('readOnlyRecommended', '0');
                }
            }
        }

        // refresh contents
        $this->zipXlsx->addContent('xl/workbook.xml', $this->excelWorkbookDOM->saveXML());
        $this->excelWorkbookDOM = $this->zipXlsx->getContent('xl/workbook.xml', 'DOMDocument');

        PhpxlsxLogger::logger('Set workbook settings.', 'info');
    }

    /**
     * Generates a unique number not used in elements
     *
     * @access public
     * @static
     * @param int $min
     * @param int $max
     * @return int
     */
    public static function uniqueNumberId($min, $max)
    {
        $proposedId = mt_rand($min, $max);
        if (in_array($proposedId, self::$elementsId)) {
            $proposedId = self::uniqueNumberId($min, $max);
        }
        self::$elementsId[] = $proposedId;

        PhpxlsxLogger::logger('New ID: ' . $proposedId, 'debug');

        return $proposedId;
    }

    /**
     * Cleans calcChain file
     *
     * @param string $position Cell position in the current active sheet: A1, C3, AB7...
     * @return void
     */
    protected function cleanCalcChain($position)
    {
        // remove calcChain contents for the position
        $calcChainContents = $this->zipXlsx->getContentByType('calcChain');
        foreach ($calcChainContents as $calcChainContent) {
            $calcChainDOM = $this->xmlUtilities->generateDomDocument($calcChainContent['content']);
            $calcChainXPath = new \DOMXPath($calcChainDOM);
            $calcChainXPath->registerNamespace('xmlns', $this->namespaces['xmlns']);
            $cellPositionsNode = $calcChainXPath->query('//xmlns:calcChain/xmlns:c[@r="'.$position.'"]');
            if ($cellPositionsNode->length > 0) {
                foreach ($cellPositionsNode as $cellPositionNode) {
                    $cellPositionNode->parentNode->removeChild($cellPositionNode);
                }
            }

            // check if calcChain contains at least one child. Otherwise remove the file
            $addCalcChain = true;
            if ($calcChainDOM->documentElement) {
                $calcChainChildren = $calcChainDOM->documentElement->childNodes;
                if ($calcChainChildren->length == 0) {
                    $this->zipXlsx->deleteContent($calcChainContent['path']);
                    $addCalcChain = false;
                }
            }

            // refresh contents
            if ($addCalcChain) {
                $this->zipXlsx->addContent($calcChainContent['path'], $calcChainDOM->saveXML());
            }

            // free DOMDocument resources
            $calcChainDOM = null;

        }

        PhpxlsxLogger::logger('Clean calc chain.', 'info');
    }

    /**
     * Generate DEFAULT
     *
     * @access protected
     * @param string $extension
     * @param string $contentType
     */
    protected function generateDefault($extension, $contentType)
    {
        $strContent = $this->excelContentTypesDOM->saveXML();
        if (
            stripos($strContent, 'Extension="' . strtolower($extension) . '"') === false
        ) {
            $strContentTypes = '<Default Extension="' . $extension . '" ContentType="' . $contentType . '"> </Default>';
            $tempNode = $this->excelContentTypesDOM->createDocumentFragment();
            $tempNode->appendXML($strContentTypes);
            $this->excelContentTypesDOM->documentElement->appendChild($tempNode);

            // refresh contents
            $this->zipXlsx->addContent('[Content_Types].xml', $this->excelContentTypesDOM->saveXML());
        }
    }

    /**
     * Generate OVERRIDE
     *
     * @access protected
     * @param string $partName
     * @param string $contentType
     */
    protected function generateOverride($partName, $contentType)
    {
        $strContent = $this->excelContentTypesDOM->saveXML();
        if (
                strpos($strContent, 'PartName="' . $partName . '"') === false
        ) {
            $strContentTypes = '<Override PartName="' . $partName . '" ContentType="' . $contentType . '" />';
            $tempNode = $this->excelContentTypesDOM->createDocumentFragment();
            $tempNode->appendXML($strContentTypes);
            $this->excelContentTypesDOM->documentElement->appendChild($tempNode);

            // refresh contents
            $this->zipXlsx->addContent('[Content_Types].xml', $this->excelContentTypesDOM->saveXML());
        }
    }

    /**
     * Generate new relationship ID
     *
     * @access protected
     * @param \DOMDocument $domRels
     * @return mixed
     */
    protected function generateRelationshipId($domRels) {
        // get current IDs
        $currentRelsIds = array();
        $nodesRelationship = $domRels->getElementsByTagName('Relationship');
        foreach ($nodesRelationship as $nodeRelationship) {
            $currentRelsIds[] = $nodeRelationship->getAttribute('Id');
        }
        // generate a new ID that is not currently being used
        $newId = null;
        while (!$newId) {
            $proposedId = self::uniqueNumberId(1000, 30000);
            if (!in_array('rId' . $proposedId, $currentRelsIds)) {
                $newId = $proposedId;
            }
        }

        return $newId;
    }

    /**
     * Generate relationship
     *
     * @access protected
     * @param string $id
     * @param string $target
     * @param string $type
     */
    protected function generateRelationshipWorkbook($id, $target, $type)
    {
        $newRelationship = '<Relationship Id="'.$id.'" Target="'.$target.'" Type="'.$type.'"/>';
        $relsNode = $this->excelRelsWorkbookDOM->createDocumentFragment();
        $relsNode->appendXML($newRelationship);
        $this->excelRelsWorkbookDOM->documentElement->appendChild($relsNode);
    }

    /**
     * Generate uniqueID
     *
     * @access protected
     * @return string
     */
    protected function generateUniqueId() {
        $uniqueId = uniqid((string)mt_rand(999, 9999));

        return $uniqueId;
    }

    /**
     * Generate xf tags
     *
     * @access protected
     * @param string $textStyles
     * @param array $cellStyles
     * @param array $type
     * @param string $xfId
     * @param array $options
     *      'named' (bool) named style
     * @return array
     */
    protected function generateXf($textStyles, $cellStyles, $type, $xfId, $options = array())
    {
        $styleIdSharedString = '';

        // add styles
        if (!empty($textStyles) || !empty($cellStyles) || !empty($type)) {
            // text styles
            $nodesCellXfs = $this->excelStylesDOM->getElementsByTagName('cellXfs');
            $nodesCellStyleXfs = $this->excelStylesDOM->getElementsByTagName('cellStyleXfs');
            if ($nodesCellXfs->length > 0) {
                // fonts
                $nodesFonts = $this->excelStylesDOM->getElementsByTagName('fonts');
                $countFonts = (int)$nodesFonts->item(0)->getAttribute('count');

                $countCellFx = (int)$nodesCellXfs->item(0)->getAttribute('count');

                // choose cell styles
                $defaultApplyFont = 0;
                $defaultApplyNumberFormat = 1;
                $defaultBorderId = 0;
                $defaultFillId = 0;
                $defaultFontId = $countFonts;
                $defaultNumFmtId = 0;
                $fontsStyleExists = false;
                if (!empty($textStyles)) {
                    // there're text styles. Use a custom font
                    $defaultApplyFont = 1;

                    // check if the fonts/font style has been already added
                    foreach ($this->newCellStyles['fonts'] as $fontStyleContent) {
                        if ($fontStyleContent['content'] == $textStyles) {
                            $defaultFontId = $fontStyleContent['id'];
                            $fontsStyleExists = true;
                            break;
                        }
                    }
                    if (!$fontsStyleExists) {
                        // keep fonts/font style to be reused later to apply the same style instead of adding a new one
                        $this->newCellStyles['fonts'][] = array(
                            'id' => $defaultFontId,
                            'content' => $textStyles,
                        );
                    }
                } else {
                    // there're no text styles. Use the default font ID
                    $defaultFontId = 0;
                }

                // check if it's a named style
                if (isset($cellStyles['cellStyleName'])) {
                    // check if the style name already exists. If false don't assign it. If true set the proper xfId
                    $excelStylesXPath = new \DOMXPath($this->excelStylesDOM);
                    $excelStylesXPath->registerNamespace('xmlns', $this->namespaces['xmlns']);
                    $nodesCellStyleName = $excelStylesXPath->query('//xmlns:cellStyles/xmlns:cellStyle[@name="'.$cellStyles['cellStyleName'].'"]');
                    if ($nodesCellStyleName->length > 0) {
                        $xfId = $nodesCellStyleName->item(0)->getAttribute('xfId');
                    }
                }

                $defaultCellStyles = array(
                    'applyFont' => $defaultApplyFont,
                    'applyNumberFormat' => $defaultApplyNumberFormat,
                    'borderId' => $defaultBorderId,
                    'fillId' => $defaultFillId,
                    'fontId' => $defaultFontId, // font IDs start from 0, so the current count value is the new ID
                    'numFmtId' => $defaultNumFmtId,
                    'xfId' => $xfId,
                );

                // type
                if (isset($type['defaultCellStyles']) && count($type['defaultCellStyles']) > 0) {
                    foreach ($type['defaultCellStyles'] as $defaultCellStyleKey => $defaultCellStyleValue) {
                        $defaultCellStyles[$defaultCellStyleKey] = $defaultCellStyleValue;
                    }
                }

                // number format
                if (isset($type['typeOptions'])) {
                    if (isset($type['typeOptions']['formatCode'])) {
                        $newFormatCode = $type['typeOptions']['formatCode'];

                        // check if the format code exists
                        $excelStylesXPath = new \DOMXPath($this->excelStylesDOM);
                        $excelStylesXPath->registerNamespace('xmlns', $this->namespaces['xmlns']);
                        $numFmtCell = $excelStylesXPath->query('//xmlns:numFmts/xmlns:numFmt[@formatCode="'.$newFormatCode.'"]');
                        if ($numFmtCell->length > 0) {
                            // numFmt, use it for the new content
                            $defaultCellStyles['numFmtId'] = $numFmtCell->item(0)->getAttribute('numFmtId');
                        } else {
                            // no numFmt found, generate a new one

                            // generate a new numFmtIt until a not used one has been found
                            $newNumFmtId = 1000;
                            $newNumFmtIdFound = false;
                            while (!$newNumFmtIdFound) {
                                $numFmtCell = $excelStylesXPath->query('//xmlns:numFmts/xmlns:numFmt[@numFmtId="'.$newNumFmtId.'"]');
                                if ($numFmtCell->length == 0) {
                                    $defaultCellStyles['numFmtId'] = $newNumFmtId;
                                    $newNumFmtIdFound = true;
                                }
                                $newNumFmtId++;
                            }

                            // append the new numFmt at the end of its parent node
                            $nodesNumFmts = $this->excelStylesDOM->getElementsByTagName('numFmts');
                            if ($nodesNumFmts->length == 0) {
                                // no numFmts found, generate a new one before the fonts tag
                                $newNode = $nodesFonts->item(0)->ownerDocument->createDocumentFragment();
                                $newNode->appendXML('<numFmts count="0"></numFmts>');
                                $nodesFonts->item(0)->parentNode->insertBefore($newNode, $nodesFonts->item(0));
                                $nodesNumFmts = $this->excelStylesDOM->getElementsByTagName('numFmts');
                            }
                            $newNumFmt = '<numFmt formatCode="'.$newFormatCode.'" numFmtId="'.$defaultCellStyles['numFmtId'].'"/>';
                            $newNode = $nodesNumFmts->item(0)->ownerDocument->createDocumentFragment();
                            $newNode->appendXML($newNumFmt);
                            $nodesNumFmts->item(0)->appendChild($newNode);
                            $countNumFmts = (int)$nodesNumFmts->item(0)->getAttribute('count');
                            $countNumFmts++;
                            $nodesNumFmts->item(0)->setAttribute('count', (string)$countNumFmts);
                        }
                    }
                }

                if (count($cellStyles) > 0) {
                    // cell styles

                    // background
                    if (isset($cellStyles['backgroundColor'])) {
                        $nodesFills = $this->excelStylesDOM->getElementsByTagName('fills');
                        $countFills = (int)$nodesFills->item(0)->getAttribute('count');

                        // append the new fill at the end of its parent node
                        $newNode = $nodesFills->item(0)->ownerDocument->createDocumentFragment();
                        $newNode->appendXML($cellStyles['backgroundColor']);
                        $nodesFills->item(0)->appendChild($newNode);

                        $defaultCellStyles['applyFill'] = 1;
                        $defaultCellStyles['fillId'] = $countFills;

                        $countFills++;
                        $nodesFills->item(0)->setAttribute('count', (string)$countFills);
                    }

                    // alignment, only the xf attribute
                    if (isset($cellStyles['alignment'])) {
                        $defaultCellStyles['applyAlignment'] = 1;
                    }

                    // border
                    if (isset($cellStyles['border'])) {
                        $nodesBorders = $this->excelStylesDOM->getElementsByTagName('borders');
                        $countBorders = (int)$nodesBorders->item(0)->getAttribute('count');

                        // append the new border at the end of its parent node
                        $newNode = $nodesBorders->item(0)->ownerDocument->createDocumentFragment();
                        $newNode->appendXML($cellStyles['border']);
                        $nodesBorders->item(0)->appendChild($newNode);

                        $defaultCellStyles['applyBorder'] = 1;
                        $defaultCellStyles['borderId'] = $countBorders;

                        $countBorders++;
                        $nodesBorders->item(0)->setAttribute('count', (string)$countBorders);
                    }

                    // lock
                    if (isset($cellStyles['locked'])) {
                        $defaultCellStyles['applyProtection'] = 1;
                    }
                }
                $cellXfStyle = '';
                foreach ($defaultCellStyles as $defaultCellStyleKey => $defaultCellStyleValue) {
                    $cellXfStyle .= ' ' . $defaultCellStyleKey . '="' . $defaultCellStyleValue . '"';
                }

                // xf tag content
                $newCellXfs = '<xf' . $cellXfStyle . '>';
                if (isset($cellStyles) && isset($cellStyles['alignment'])) {
                    $newCellXfs .= $cellStyles['alignment'];
                }
                if (isset($cellStyles) && isset($cellStyles['locked'])) {
                    $newCellXfs .= $cellStyles['locked'];
                }
                $newCellXfs .= '</xf>';

                $cellXfStyleExists = false;
                $styleIdSharedString = $countCellFx;
                // check if the cellXfs/xf style has been already added
                foreach ($this->newCellStyles['cellXfs'] as $cellXfsStyleContent) {
                    if ($cellXfsStyleContent['content'] == $newCellXfs) {
                        $styleIdSharedString = $cellXfsStyleContent['id'];
                        $cellXfStyleExists = true;
                        break;
                    }
                }
                // check if the cellXfs/xf style is named and exists
                if (isset($cellStyles['cellStyleName'])) {
                    $excelStylesXPath = new \DOMXPath($this->excelStylesDOM);
                    $excelStylesXPath->registerNamespace('xmlns', $this->namespaces['xmlns']);
                    $nodesCellStyleXfsName = $excelStylesXPath->query('//xmlns:cellStyleXfs/xmlns:xf['.(int)($xfId+1).']');
                    if ($nodesCellStyleXfsName->length > 0) {
                        $iNodeCellXfs = 0;
                        if ($nodesCellXfs->length > 0 && $nodesCellXfs->item(0)->hasChildNodes()) {
                            foreach ($nodesCellXfs->item(0)->childNodes as $nodeXfs) {
                                if ($nodeXfs->getAttribute('xfId') == $xfId) {
                                    $styleIdSharedString = $iNodeCellXfs;
                                    $cellXfStyleExists = true;
                                    break;
                                }
                                $iNodeCellXfs++;
                            }
                        }
                    }
                }

                if (!$cellXfStyleExists) {
                    // keep cellXfs/xf style to be reused later to apply the same style instead of adding a new one
                    $this->newCellStyles['cellXfs'][] = array(
                        'id' => $styleIdSharedString,
                        'content' => $newCellXfs,
                    );

                    // append the new cellXfs at the end of its parent node
                    $newNode = $nodesCellXfs->item(0)->ownerDocument->createDocumentFragment();
                    $newNode->appendXML($newCellXfs);
                    $newNodeCellXfs = $nodesCellXfs->item(0)->appendChild($newNode);
                    // append the new font at the end of its parent node if the font style has not been reused and there're text styles
                    if (!$fontsStyleExists && !empty($textStyles)) {
                        $newNode = $nodesFonts->item(0)->ownerDocument->createDocumentFragment();
                        $newNode->appendXML($textStyles);
                        $nodesFonts->item(0)->appendChild($newNode);

                        $countFonts++;
                        $nodesFonts->item(0)->setAttribute('count', (string)$countFonts);
                    }
                    // increment the count value
                    $countCellFx++;
                    $nodesCellXfs->item(0)->setAttribute('count', (string)$countCellFx);

                    // if named style, add a cellStyleXfs tag removing xfId
                    if (isset($options['named']) && $options['named']) {
                        $newNode = $nodesCellStyleXfs->item(0)->ownerDocument->createDocumentFragment();
                        $newNode->appendXML($newCellXfs);
                        $newNodeCellStyleXfs = $nodesCellStyleXfs->item(0)->appendChild($newNode);
                        $newNodeCellStyleXfs->removeAttribute('xfId');
                    }
                }
            }
        }

        return array(
            'styleIdSharedString' => $styleIdSharedString,
        );
    }

    /**
     * Returns active sheet elements
     *
     * @return array
     */
    protected function getActiveSheetElements()
    {
        $sheetsContents = $this->zipXlsx->getSheets();
        $activeSheetContent = $sheetsContents[$this->activeSheet['position']];
        $sheetDOM = $this->xmlUtilities->generateDomDocument($activeSheetContent['content']);
        $sheetXPath = new \DOMXPath($sheetDOM);
        $sheetXPath->registerNamespace('xmlns', $this->namespaces['xmlns']);

        return array(
            'activeSheetContent' => $sheetsContents[$this->activeSheet['position']],
            'sheetDOM' => $sheetDOM,
            'sheetXPath' => $sheetXPath,
        );
    }

    /**
     * Gets comment content. Create a and add new comment XML if needed in the active sheet
     *
     * @access protected
     * @return array
     */
    protected function getCommentsContent()
    {
        // get the active sheet
        $sheetsContents = $this->zipXlsx->getSheets();
        $activeSheetContent = $sheetsContents[$this->activeSheet['position']];

        // get sheet rels. This file may not exists, so generate it from a skeleton
        $relsFilePath = str_replace('worksheets/', 'worksheets/_rels/', $activeSheetContent['path']) . '.rels';
        $sheetRelsContent = $this->zipXlsx->getContent($relsFilePath);
        if (empty($sheetRelsContent)) {
            $sheetRelsContent = OOXMLResources::$sheetRelsXML;
        }
        $sheetRelsDOM = $this->xmlUtilities->generateDomDocument($sheetRelsContent);

        // check if the relationship includes a comment XML
        $sheetRelsContentXPath = new \DOMXPath($sheetRelsDOM);
        $sheetRelsContentXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/package/2006/relationships');
        $nodesRelationshipComments = $sheetRelsContentXPath->query('//xmlns:Relationships/xmlns:Relationship[@Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/comments"]');
        if ($nodesRelationshipComments->length == 0) {
            $newIdRelationship = $this->generateUniqueId();

            // generate and add XML comment relationship
            $newRelationship = '<Relationship Id="rIdcomment'.$newIdRelationship.'" Target="../comments'.$newIdRelationship.'.xml" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/comments"/>';
            $relsNodeComment = $sheetRelsDOM->createDocumentFragment();
            $relsNodeComment->appendXML($newRelationship);
            $nodeRelationshipComments = $sheetRelsDOM->documentElement->appendChild($relsNodeComment);

            // generate and add XML comment
            $commentsContentsDOM = $this->xmlUtilities->generateDomDocument(OOXMLResources::$commentContentXML);

            // write the new Override node associated to the new comment file in [Content_Types].xml
            $this->generateOverride('/xl/comments'.$newIdRelationship.'.xml', 'application/vnd.openxmlformats-officedocument.spreadsheetml.comments+xml');
            $this->zipXlsx->addContent('xl/comments'.$newIdRelationship.'.xml', $commentsContentsDOM->saveXML());

            // refresh contents
            $this->zipXlsx->addContent($relsFilePath, $sheetRelsDOM->saveXML());
        } else {
            $nodeRelationshipComments = $nodesRelationshipComments->item(0);
        }

        // get comments content and path
        $commentsPath = str_replace('../', 'xl/', $nodeRelationshipComments->getAttribute('Target'));
        $commentsContents = $this->zipXlsx->getContent($commentsPath);

        $commentsInfo = array(
            'content' => $commentsContents,
            'path' => $commentsPath,
        );

        return $commentsInfo;
    }

    /**
     * Gets drawing nodes. Create a new node if needed
     *
     * @access protected
     * @param \DOMDocument $sheetDOM
     * @param array $activeSheetContent
     * @param array $settings
     *      'extension' (string) vml, xml
     *      'tag' (string) legacyDrawingHF, legacyDrawing
     *      'type' (string)
     * @return \DOMNodeList
     */
    protected function getNodesDrawing($sheetDOM, $activeSheetContent, $settings)
    {
        $nodesDrawing = $sheetDOM->getElementsByTagName($settings['tag']);
        if ($nodesDrawing->length == 0) {
            // no drawing found, generate a new one at end of the sheet
            $newNode = $sheetDOM->createDocumentFragment();
            $drawingId = $this->generateUniqueId();
            $newNode->appendXML('<'.$settings['tag'].' r:id="rId'.$drawingId.'" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" />');

            // add the drawing before tableParts or legacyDrawing if some exists, otherwise add it as new child
            $nodesTableParts = $sheetDOM->getElementsByTagName('tableParts');
            $nodesLegacyDrawing = $sheetDOM->getElementsByTagName('legacyDrawing');
            $nodesLegacyDrawingHF = $sheetDOM->getElementsByTagName('legacyDrawingHF');
            if ($nodesTableParts->length > 0) {
                // tableParts node
                $nodesTableParts->item(0)->parentNode->insertBefore($newNode, $nodesTableParts->item(0));
            } else if ($settings['tag'] == 'legacyDrawingHF' && $nodesLegacyDrawingHF->length > 0) {
                // legacyDrawingHF node
                $nodesLegacyDrawingHF->item(0)->parentNode->insertBefore($newNode, $nodesLegacyDrawingHF->item(0));
            } else if ($settings['tag'] == 'legacyDrawing' && $nodesLegacyDrawing->length > 0) {
                // legacyDrawing node
                $nodesLegacyDrawing->item(0)->parentNode->insertBefore($newNode, $nodesLegacyDrawing->item(0)->nextSibling);
            } else {
                // insert in the correct order: drawing, legacyDrawing, legacyDrawingHF
                if ($nodesLegacyDrawing->length > 0) {
                    $nodesLegacyDrawing->item(0)->parentNode->insertBefore($newNode, $nodesLegacyDrawing->item(0));
                } else if ($nodesLegacyDrawingHF->length > 0) {
                    $nodesLegacyDrawingHF->item(0)->parentNode->insertBefore($newNode, $nodesLegacyDrawingHF->item(0));
                } else {
                    $sheetDOM->documentElement->appendChild($newNode);
                }
            }
            $nodesDrawing = $sheetDOM->getElementsByTagName($settings['tag']);

            // add the new relationship
            // get sheet rels. This file may not exists, so generate it from a skeleton
            $relsFilePath = str_replace('worksheets/', 'worksheets/_rels/', $activeSheetContent['path']) . '.rels';
            $sheetRelsContent = $this->zipXlsx->getContent($relsFilePath);
            if (empty($sheetRelsContent)) {
                $sheetRelsContent = OOXMLResources::$sheetRelsXML;
            }
            $sheetRelsDOM = $this->xmlUtilities->generateDomDocument($sheetRelsContent);

            $newRelationship = '<Relationship Id="rId'.$drawingId.'" Target="../drawings/drawing'.$drawingId.'.'.$settings['extension'].'" Type="'.$settings['type'].'" />';

            $relsNodeImage = $sheetRelsDOM->createDocumentFragment();
            $relsNodeImage->appendXML($newRelationship);
            $sheetRelsDOM->documentElement->appendChild($relsNodeImage);

            // refresh contents
            $this->zipXlsx->addContent($activeSheetContent['path'], $sheetDOM->saveXML());
            $this->zipXlsx->addContent($relsFilePath, $sheetRelsDOM->saveXML());
        }

        return $nodesDrawing;
    }

    /**
     * Gets sheet, text and number from a position
     *
     * @access protected
     * @param string $position
     * @param string $type string or array
     * @return array|string
     */
    protected function getPositionInfo($position, $type = 'string')
    {
        // by default consider the position doesn't have a sheet set
        $sheetPositions = '';
        $cellPositions = $position;

        if (strrpos($position, '!')) {
            // contains sheet info
            $cellPositions = substr(strrchr($position, '!'), 1);
            // sheet position, remove ! from the string
            $sheetPositions = str_replace('!' . $cellPositions, '', $position);
            $sheetPositions = $this->parseAndCleanTextString($sheetPositions);

            // if the method returns a string, add the ! after the sheet name to keep it
            if ($type == 'string') {
                $sheetPositions .= '!';
            }
            // remove ' and ' at the beginning and end to get the correct sheet name
            if (substr($sheetPositions, 0, 6) == '&#039;') {
                $sheetPositions = substr($sheetPositions, 6);
            }
            if (substr($sheetPositions, -6) == '&#039;') {
                $sheetPositions = substr($sheetPositions, 0, -6);
            }
            $sheetPositions = htmlspecialchars_decode($sheetPositions);
        }
        // cell text content
        $textPositions = preg_replace('/[0-9]/', '', $cellPositions);
        // cell number content
        $numberPositions = preg_replace('/[^0-9]/', '', $cellPositions);
        if ($type == 'array') {
            return array(
                'number' => $numberPositions,
                'sheet' => $sheetPositions,
                'text' => strtoupper($textPositions),
            );
        } else {
            return $sheetPositions . strtoupper($textPositions) . $numberPositions;
        }
    }

    /**
     * Gets positions from a cell range
     *
     * @access protected
     * @param string $range
     * @return array
     */
    protected function getPositionsFromRange($range)
    {
        $positions = array();

        // normalize range
        // use ; as ,
        $range = str_replace(';', ',', $range);

        // ; splits ranges or cells
        $positionElements = explode(',', $range);

        // iterate elements to get the positions
        foreach ($positionElements as $positionElement) {
            // : sets sequential cells
            $sequentialElements = explode(':', $positionElement);
            // sequence from
            $sequenceFrom = $this->getPositionInfo($sequentialElements[0], 'array');
            // sequence to
            if (isset($sequentialElements[1])) {
                // there's : sequence
                $sequenceTo = $this->getPositionInfo($sequentialElements[1], 'array');
            } else {
                // there's no : sequence
                $sequenceTo = $sequenceFrom;
            }

            // iterate the sequence to get all positions
            for ($iRow = $sequenceFrom['number']; $iRow <= $sequenceTo['number']; $iRow++) {
                if (function_exists('str_increment')) {
                    for ($iColumn = $sequenceFrom['text']; $iColumn <= $sequenceTo['text']; $iColumn = str_increment($iColumn)) {
                        if (!empty($sequenceFrom['sheet'])) {
                            $positions[] = $sequenceFrom['sheet'] . '!' . $iColumn . $iRow;
                        } else {
                            $positions[] = $iColumn . $iRow;
                        }
                    }
                } else {
                    for ($iColumn = $sequenceFrom['text']; $iColumn <= $sequenceTo['text']; $iColumn++) {
                        if (!empty($sequenceFrom['sheet'])) {
                            $positions[] = $sequenceFrom['sheet'] . '!' . $iColumn . $iRow;
                        } else {
                            $positions[] = $iColumn . $iRow;
                        }
                    }
                }
            }
        }

        // TODO table range

        return $positions;
    }

    /**
     * Gets standalone positions from a position: standalone position, range
     *
     * @access protected
     * @param string|array $positions
     * @return array
     */
    protected function getStandalonePositions($positions)
    {
        $newPositions = array();

        if (is_array($positions)) {
            foreach ($positions as $position) {
                $newPositions = array_merge($newPositions, $this->getPositionsFromRange($position));
            }
        } else {
            $newPositions = array_merge($newPositions, $this->getPositionsFromRange($positions));
        }

        return $newPositions;
    }

    /**
     * Inserts a text box in a drawing
     *
     * @access protected
     * @param string $content
     */
    protected function insertTextBox($content)
    {
        // get the active sheet
        $sheetsContents = $this->zipXlsx->getSheets();
        $activeSheetContent = $sheetsContents[$this->activeSheet['position']];
        $sheetDOM = $this->xmlUtilities->generateDomDocument($activeSheetContent['content']);

        // get the drawing tag of the current sheet
        $nodesDrawing = $this->getNodesDrawing($sheetDOM, $activeSheetContent, array('extension' => 'xml', 'tag' => 'drawing', 'type' => 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing'));

        // get the drawing content to add the new equation
        $relsFilePath = str_replace('worksheets/', 'worksheets/_rels/', $activeSheetContent['path']) . '.rels';
        $sheetRelsContent = $this->zipXlsx->getContent($relsFilePath);
        $sheetRelsDOM = $this->xmlUtilities->generateDomDocument($sheetRelsContent);
        $sheetRelsContentXPath = new \DOMXPath($sheetRelsDOM);
        $sheetRelsContentXPath->registerNamespace('xmlns', 'http://schemas.openxmlformats.org/package/2006/relationships');
        $nodesRelationshipDrawing = $sheetRelsContentXPath->query('//xmlns:Relationships/xmlns:Relationship[@Id="'.$nodesDrawing->item(0)->getAttribute('r:id').'"]');
        if ($nodesRelationshipDrawing->length > 0) {
            $drawingTarget = str_replace('../drawings/', 'xl/drawings/', $nodesRelationshipDrawing->item(0)->getAttribute('Target'));
            $drawingContent = $this->zipXlsx->getContent($drawingTarget);
            if (!$drawingContent) {
                // generate a new drawing content
                $drawingContent = OOXMLResources::$drawingContentXML;

                // add Override
                $this->generateOverride('/' . $drawingTarget, 'application/vnd.openxmlformats-officedocument.drawing+xml');
            }
            $drawingDOM = $this->xmlUtilities->generateDomDocument($drawingContent);

            // add the new drawing XML
            $newNodeDrawing = $drawingDOM->createDocumentFragment();
            $newNodeDrawing->appendXML($content);
            $drawingDOM->documentElement->appendChild($newNodeDrawing);

            // refresh contents
            $this->zipXlsx->addContent($drawingTarget, $drawingDOM->saveXML());

            // free DOMDocument resources
            $drawingDOM = null;
            $drawingRelsDOM = null;
        }

        // free DOMDocument resources
        $sheetDOM = null;
    }

    /**
     * Parse and clean a sheet name to be added
     *
     * @access protected
     * @param string $content
     * @return string
     */
    protected function parseAndCleanSheetName($content)
    {
        $content = str_replace(array('\\', '/', '?', '*' , '[', ']'), '', $content);

        return $content;
    }

    /**
     * Parse and clean a text string to be added
     *
     * @access protected
     * @param string $content
     * @return string
     */
    protected function parseAndCleanTextString($content)
    {
        $content = $this->xmlUtilities->parseAndCleanTextString($content);

        return $content;
    }

    /**
     * Updates the active sheet value from the workbook content
     *
     * @access protected
     */
    protected function updateActiveSheet()
    {
        // get current active sheet. If no default active sheet, set the first sheet (0) as the active one
        $sheetsContents = $this->zipXlsx->getSheets();
        // default active sheet as 0 position
        $currentActiveTabPosition = 0;
        // query current activeTab in the workbook
        $bookViewsTags = $this->excelWorkbookDOM->getElementsByTagName('bookViews');
        if ($bookViewsTags->length > 0) {
            $workbookViewTags = $bookViewsTags->item(0)->getElementsByTagName('workbookView');
            if ($workbookViewTags->length > 0) {
                if ($workbookViewTags->item(0)->hasAttribute('activeTab')) {
                    $currentActiveTabPosition = $workbookViewTags->item(0)->getAttribute('activeTab');
                }
            }
        }
        $currentActiveTabName = null;
        if (isset($sheetsContents[$currentActiveTabPosition])) {
            $currentActiveTabName = $sheetsContents[$currentActiveTabPosition]['name'];
        }
        $this->activeSheet = array(
            'position' => (int)$currentActiveTabPosition,
            'name' => $currentActiveTabName,
        );

        PhpxlsxLogger::logger('Update active sheet.', 'info');
    }
}