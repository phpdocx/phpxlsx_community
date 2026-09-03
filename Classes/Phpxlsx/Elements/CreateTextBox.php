<?php
namespace Phpxlsx\Elements;
/**
 * Create text box
 *
 * @category   Phpxlsx
 * @package    elements
 * @copyright  Copyright (c) Narcea Labs SL
 *             (https://www.narcealabs.com)
 * @license    phpxlsx Community License
 * @link       https://www.phpxlsx.com
 */
class CreateTextBox extends CreateElement
{
    /**
     * Generate a new text box
     *
     * @access public
     * @param mixed $contents Text box contents
     * @param array $position
     * @param array $textBoxStyles
     *      'align' (string) left, center, right
     *      'backgroundColor' (string) FFFF00, CCCCCC..., none Default as FFFFFF
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
     *      'colSize' (int) number of cols used by the image
     *      'editAs' (string) oneCell (default) (move but don't size with cells), twoCell (move and size with cells), absolute (don't move or size with cells)
     *      'name' (string) set a name value
     *      'rId' (string) textbox ID
     *      'rowOffset' (array) given in emus (1cm = 360000 emus). 0 as default
     *          'from' (int) from offset
     *          'to' (int) from offset
     *      'rowSize' (int) number of rows used by the image
     *  @return string
     */
    public function createElementTextBox($contents, $position, $textBoxStyles = array(), $options = array())
    {
        // default values
        if (!isset($textBoxStyles['backgroundColor'])) {
            $textBoxStyles['backgroundColor'] = 'FFFFFF';
        }

        // from and to column values
        $colFromValue = $this->wordToInt($position['text']);
        $colToValue = $colFromValue + 1;
        if (isset($options['colSize'])) {
            $colToValue = $colFromValue + $options['colSize'];
        }
        $colFromOffsetValue = 0;
        if (isset($options['colOffset']) && isset($options['colOffset']['from'])) {
            $colFromOffsetValue = $options['colOffset']['from'];
        }
        $colToOffsetValue = 0;
        if (isset($options['colOffset']) && isset($options['colOffset']['to'])) {
            $colToOffsetValue = $options['colOffset']['to'];
        }

        // from and to row values
        $rowFromValue = $position['number'];
        // start from 0
        $rowFromValue--;
        $rowToValue = $rowFromValue + 1;
        if (isset($options['rowSize'])) {
            $rowToValue = $rowFromValue + $options['rowSize'];
        }
        $rowFromOffsetValue = 0;
        if (isset($options['rowOffset']) && isset($options['rowOffset']['from'])) {
            $rowFromOffsetValue = $options['rowOffset']['from'];
        }
        $rowToOffsetValue = 0;
        if (isset($options['rowOffset']) && isset($options['rowOffset']['to'])) {
            $rowToOffsetValue = $options['rowOffset']['to'];
        }

        $name = 'TextBox ' . $options['rId'];
        if (isset($options['name'])) {
            $name = $this->parseAndCleanTextString($options['name']);
        }

        $cNvPrId = rand(999999, 999999999);

        $solidFill = '';
        if ($textBoxStyles['backgroundColor'] == 'none') {
            $solidFill = '<a:noFill/>';
        } else {
            // normalize color
            $textBoxStyles['backgroundColor'] = strtoupper(str_replace('#', '', $textBoxStyles['backgroundColor']));
            $solidFill = '<a:solidFill>';
            $solidFill .= '<a:srgbClr val="'.$textBoxStyles['backgroundColor'].'">';
            // transparency
            if (isset($textBoxStyles['backgroundTransparency'])) {
                $alphaValue = 100000;
                if ($textBoxStyles['backgroundTransparency'] >= 0 && $textBoxStyles['backgroundTransparency'] <= 100) {
                    $alphaValue = 100000 - ($textBoxStyles['backgroundTransparency'] * 1000);
                }
                $solidFill .= '<a:alpha val="'.$alphaValue.'"/>';
            }
            $solidFill .= '</a:srgbClr>';
            $solidFill .= '</a:solidFill>';
        }

        $anchor = 't';
        if (isset($textBoxStyles['verticalAlign'])) {
            if ($textBoxStyles['verticalAlign'] == 'top') {
                $anchor = 't';
            } else if ($textBoxStyles['verticalAlign'] == 'middle') {
                $anchor = 'ctr';
            } else if ($textBoxStyles['verticalAlign'] == 'bottom') {
                $anchor = 'b';
            }
        }

        $rotation = '';
        if (isset($textBoxStyles['rotation'])) {
            $rotation = ' rot="'.((int)$textBoxStyles['rotation'] * 60000).'"';
        }

        $line = '<a:ln cmpd="sng" w="9525"><a:solidFill><a:schemeClr val="lt1"><a:shade val="50000"/></a:schemeClr></a:solidFill></a:ln>';
        if (isset($textBoxStyles['border']) && is_array($textBoxStyles['border'])) {
            $line = '<a:ln cmpd="sng" ';
            if (isset($textBoxStyles['border']['width'])) {
                $line .= 'w="'.$textBoxStyles['border']['width'].'" ';
            } else {
                $line .= 'w="9525" ';
            }
            $line .= '><a:solidFill>';
            if (isset($textBoxStyles['border']['color'])) {
                $line .= '<a:srgbClr val="'.strtoupper(str_replace('#', '', $textBoxStyles['border']['color'])).'">';
            } else {
                $line .= '<a:schemeClr val="lt1"><a:shade val="50000"/>';
            }
            // transparency
            if (isset($textBoxStyles['border']['transparency'])) {
                $alphaValue = 100000;
                if ($textBoxStyles['border']['transparency'] >= 0 && $textBoxStyles['border']['transparency'] <= 100) {
                    $alphaValue = 100000 - ($textBoxStyles['border']['transparency'] * 1000);
                }
                $line .= '<a:alpha val="'.$alphaValue.'"/>';
            }
            if (isset($textBoxStyles['border']['color'])) {
                $line .= '</a:srgbClr>';
            } else {
                $line .= '</a:schemeClr>';
            }
            $line .= '</a:solidFill>';
            if (isset($textBoxStyles['border']['dashType'])) {
                $line .= '<a:prstDash val="'.$textBoxStyles['border']['dashType'].'"/>';
            }
            $line .= '</a:ln>';
        }

        $newTextBox = '<xdr:twoCellAnchor editAs="'.$options['editAs'].'" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing"><xdr:from><xdr:col>'.$colFromValue.'</xdr:col><xdr:colOff>'.$colFromOffsetValue.'</xdr:colOff><xdr:row>'.$rowFromValue.'</xdr:row><xdr:rowOff>'.$rowFromOffsetValue.'</xdr:rowOff></xdr:from><xdr:to><xdr:col>'.$colToValue.'</xdr:col><xdr:colOff>'.$colToOffsetValue.'</xdr:colOff><xdr:row>'.$rowToValue.'</xdr:row><xdr:rowOff>'.$rowToOffsetValue.'</xdr:rowOff></xdr:to><xdr:sp macro="" textlink=""><xdr:nvSpPr><xdr:cNvPr id="'.$cNvPrId.'" name="'.$name.'"></xdr:cNvPr><xdr:cNvSpPr txBox="1"/></xdr:nvSpPr><xdr:spPr><a:xfrm'.$rotation.'><a:off x="0" y="0"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom>'.$solidFill.$line.'</xdr:spPr><xdr:txBody><a:bodyPr anchor="'.$anchor.'" horzOverflow="clip" rtlCol="0" vertOverflow="clip" wrap="square"/><a:lstStyle/>'.$contents.'</xdr:txBody></xdr:sp><xdr:clientData/></xdr:twoCellAnchor>';

        return $newTextBox;
    }
}