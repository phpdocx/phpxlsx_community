<?php
namespace Phpxlsx\Resources;
/**
 * This class contains a series of static variables with useful OOXML structure info
 *
 * @category   Phpxlsx
 * @package    Resources
 * @copyright  Copyright (c) Narcea Labs SL
 *             (https://www.narcealabs.com)
 * @license    phpxlsx Community License
 * @link       https://www.phpxlsx.com
 */
class OOXMLResources
{
    /**
     * @access public
     * @var string
     * @static
     */
    public static $commentContentXML = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><comments mc:Ignorable="xr" xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:xr="http://schemas.microsoft.com/office/spreadsheetml/2014/revision"><authors><author>phpxlsx</author></authors></comments>';

    /**
     * @access public
     * @var string
     * @static
     */
    public static $customProperties = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?><Properties xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes" xmlns="http://schemas.openxmlformats.org/officeDocument/2006/custom-properties"></Properties>';

    /**
     * @access public
     * @var string
     * @static
     */
    public static $drawingContentVML = '<xml xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:x="urn:schemas-microsoft-com:office:excel"><o:shapelayout v:ext="edit"><o:idmap data="1" v:ext="edit"/></o:shapelayout><v:shapetype coordsize="21600,21600" filled="f" id="_x0000_t75" o:preferrelative="t" o:spt="75" path="m@4@5l@4@11@9@11@9@5xe" stroked="f"><v:stroke joinstyle="miter"/><v:formulas><v:f eqn="if lineDrawn pixelLineWidth 0"/><v:f eqn="sum @0 1 0"/><v:f eqn="sum 0 0 @1"/><v:f eqn="prod @2 1 2"/><v:f eqn="prod @3 21600 pixelWidth"/><v:f eqn="prod @3 21600 pixelHeight"/><v:f eqn="sum @0 0 1"/><v:f eqn="prod @6 1 2"/><v:f eqn="prod @7 21600 pixelWidth"/><v:f eqn="sum @8 21600 0"/><v:f eqn="prod @7 21600 pixelHeight"/><v:f eqn="sum @10 21600 0"/></v:formulas><v:path gradientshapeok="t" o:connecttype="rect" o:extrusionok="f"/><o:lock aspectratio="t" v:ext="edit"/></v:shapetype></xml>';

    /**
     * @access public
     * @var string
     * @static
     */
    public static $drawingContentRelsVML = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>';

    /**
     * @access public
     * @var string
     * @static
     */
    public static $drawingContentXML = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><xdr:wsDr xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing"></xdr:wsDr>';

    /**
     * @access public
     * @var string
     * @static
     */
    public static $drawingContentRelsXML = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>';

    /**
     * @access public
     * @var string
     * @static
     */
    public static $sharedStrings = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="0" uniqueCount="0"></sst>';

    /**
     * @access public
     * @var string
     * @static
     */
    public static $sheetXML = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" mc:Ignorable="x14ac xr xr2 xr3" xmlns:x14ac="http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac" xmlns:xr="http://schemas.microsoft.com/office/spreadsheetml/2014/revision" xmlns:xr2="http://schemas.microsoft.com/office/spreadsheetml/2015/revision2" xmlns:xr3="http://schemas.microsoft.com/office/spreadsheetml/2016/revision3" xr:uid="{8018FA30-F65F-4DD3-95D4-6C181C480377}"><dimension ref="A1"/><sheetViews><sheetView workbookViewId="0"/></sheetViews><sheetFormatPr defaultRowHeight="14.4" x14ac:dyDescent="0.3"/><sheetData/><pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/></worksheet>';

    /**
     * @access public
     * @var string
     * @static
     */
    public static $sheetRelsXML = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>';

    /**
     * @access public
     * @var string
     * @static
     */
    public static $stylesCellStyles = '<cellStyles count="1"><cellStyle builtinId="0" name="Normal" xfId="0"/></cellStyles>';

    /**
     * @access public
     * @var string
     * @static
     */
    public static $stylesTableStyles = '<tableStyles count="0" defaultTableStyle="TableStyleMedium2" defaultPivotStyle="PivotStyleLight16"/>';

    /**
     * @access public
     * @var string
     * @static
     */
    public static $tableXML = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><table xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006" xmlns:xr="http://schemas.microsoft.com/office/spreadsheetml/2014/revision"><tableColumns count="0"/><tableStyleInfo/><extLst><ext uri="{504A1905-F514-4f6f-8877-14C23A59335A}" xmlns:x14="http://schemas.microsoft.com/office/spreadsheetml/2009/9/main"><x14:table altText="" altTextSummary=""/></ext></extLst></table>';

    /**
     * @access public
     * @var array
     * @static
     */
    public static $presetStyles = array(
        'Bad' => array(
            'backgroundColor' => 'FFFFC7CE',
            'color' => 'FF9C0006',
        ),
        'Calculation' => array(
            'backgroundColor' => 'FFF2F2F2',
            'bold' => true,
            'border' => 'thin',
            'borderColor' => 'FF7F7F7F',
            'color' => 'FFFA7D00',
        ),
        'Check Cell' => array(
            'bold' => true,
            'backgroundColor' => 'FFA5A5A5',
            'border' => 'double',
            'borderColor' => 'FF3F3F3F',
            'color' => 'FFFFFF',
        ),
        'Comma' => array(
            'typeOptions' => array(
                'formatCode' => '_-* #,##0.00_-;\-* #,##0.00_-;_-* &quot;-&quot;??_-;_-@_-',
            ),
        ),
        'Currency' => array(
            'typeOptions' => array(
                'formatCode' => '_-* #,##0.00\ &quot;€&quot;_-;\-* #,##0.00\ &quot;€&quot;_-;_-* &quot;-&quot;??\ &quot;€&quot;_-;_-@_-',
            ),
        ),
        'Explanatory Text' => array(
            'color' => 'FF7F7F7F',
            'italic' => true,
        ),
        'Good' => array(
            'backgroundColor' => 'FFC6EFCE',
            'color' => 'FF006100',
        ),
        'Heading 1' => array(
            'bold' => true,
            'borderBottom' => 'thick',
            'borderColorBottom' => '4472C4',
            'color' => '44546A',
            'fontSize' => '15',
        ),
        'Heading 2' => array(
            'bold' => true,
            'borderBottom' => 'thick',
            'borderColorBottom' => 'A2B8E1',
            'color' => '44546A',
            'fontSize' => '13',
        ),
        'Heading 3' => array(
            'bold' => true,
            'borderBottom' => 'thick',
            'borderColorBottom' => '8EA9DB',
            'color' => '44546A',
            'fontSize' => '11',
        ),
        'Heading 4' => array(
            'bold' => true,
            'borderBottom' => 'thick',
            'borderColorBottom' => '4472C4',
            'color' => '44546A',
            'fontSize' => '11',
        ),
        'Input' => array(
            'backgroundColor' => 'FFFFCC99',
            'borderBottom' => 'thin',
            'borderColorBottom' => 'FF3F3F76',
            'color' => 'FF3F3F76',
        ),
        'Linked Cell' => array(
            'borderBottom' => 'double',
            'borderColorBottom' => 'FFFF8001',
            'color' => 'FFFA7D00',
        ),
        'Neutral' => array(
            'backgroundColor' => 'FFFFEB9C',
            'color' => 'FF9C5700',
        ),
        'Normal' => array(),
        'Note' => array(
            'backgroundColor' => 'FFFFFFCC',
            'border' => 'thin',
            'borderColor' => 'FFB2B2B2',
        ),
        'Output' => array(
            'backgroundColor' => 'FFF2F2F2',
            'bold' => true,
            'border' => 'thin',
            'borderColor' => 'FF3F3F3F',
            'color' => 'FF3F3F3F',
        ),
        'Percent' => array(
            'typeOptions' => array(
                'formatCode' => '0.00%',
            ),
        ),
        'Warning Text' => array(
            'color' => 'FFFF0000',
        ),
    );
}