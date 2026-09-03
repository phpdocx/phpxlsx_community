<?php
namespace Phpxlsx\Elements;
/**
 * Create shape
 *
 * @category   Phpxlsx
 * @package    elements
 * @copyright  Copyright (c) Narcea Labs SL
 *             (https://www.narcealabs.com)
 * @license    phpxlsx Community License
 * @link       https://www.phpxlsx.com
 */
class CreateShape extends CreateElement
{
    /**
     * Generate a new shape
     *
     * @access public
     * @param mixed $type Shape type
     * @param array $position
     * @param array $options
     *      'colOffset' (array) given in emus (1cm = 360000 emus). 0 as default
     *          'from' (int) from offset
     *          'to' (int) from offset
     *      'colSize' (int) number of cols used by the image
     *      'customGeom' (string) custom geometry
     *      'editAs' (string) oneCell (default) (move but don't size with cells), twoCell (move and size with cells), absolute (don't move or size with cells)
     *      'fillColor' (string) #FF0000, #00FFFF,...
     *      'fillColorTransparency' (int) 0 to 100
     *      'imageContent' (mixed) image path, base64 or stream. Image formats: png, jpg, jpeg, gif, bmp, webp
     *      'name' (string) set a name value
     *      'outlineColor' (string) #FF0000, #00FFFF,...
     *      'rId' (string) shape ID
     *      'rIdImage' (string) image ID
     *      'rotation' (int) 60.000ths of a degree
     *      'rowOffset' (array) given in emus (1cm = 360000 emus). 0 as default
     *          'from' (int) from offset
     *          'to' (int) from offset
     *      'rowSize' (int) number of rows used by the image
     *      'tailEnd' (string) arrow, diamond, none, oval, stealth, triangle
     *      'textContents' (array)
     *      'textStyles' (array)
     *          'align' (string) left, center, right
     *          'verticalAlign' (string) top, middle, bottom
     *  @return string
     */
    public function createElementShape($type, $position, $options = array())
    {
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

        $name = 'Shape ' . $options['rId'];
        if (isset($options['name'])) {
            $name = $this->parseAndCleanTextString($options['name']);
        }

        $cNvPrId = rand(999999, 999999999);

        $fillColorContents = '';
        if (isset($options['fillColor'])) {
            $fillColorContents = '<a:solidFill><a:srgbClr val="'.strtoupper(str_replace('#', '', $options['fillColor'])).'">';
            if (isset($options['fillColorTransparency'])) {
                $fillColorContents .= '<a:alpha val="'.((100 - (int)$options['fillColorTransparency'])*1000).'"/>';
            }
            $fillColorContents .= '</a:srgbClr></a:solidFill>';
        }
        $outlineContents = '';
        if (isset($options['outlineColor']) || isset($options['tailEnd'])) {
            $outlineContents = '<a:ln>';
            if (isset($options['outlineColor'])) {
                $outlineContents .= '<a:solidFill><a:srgbClr val="'.strtoupper(str_replace('#', '', $options['outlineColor'])).'"/></a:solidFill>';
            }
            if (isset($options['tailEnd'])) {
                $outlineContents .= '<a:tailEnd type="'.$options['tailEnd'].'"/>';
            }

            $outlineContents .= '</a:ln>';
        }
        $rotation = '';
        if (isset($options['rotation'])) {
            $rotation = ' rot="'.((int)$options['rotation'] * 60000).'"';
        }

        $prstGeomContents = '<a:prstGeom prst="'.$type.'"><a:avLst/></a:prstGeom>';
        if (isset($options['customGeom'])) {
            $prstGeomContents = '<a:custGeom>'.$options['customGeom'].'</a:custGeom>';
        }
        $styleContents = '<xdr:style><a:lnRef idx="2"><a:schemeClr val="accent1"><a:shade val="15000"/></a:schemeClr></a:lnRef><a:fillRef idx="1"><a:schemeClr val="accent1"/></a:fillRef><a:effectRef idx="0"><a:schemeClr val="accent1"/></a:effectRef><a:fontRef idx="minor"><a:schemeClr val="lt1"/></a:fontRef></xdr:style>';
        $textContents = '<a:p><a:pPr algn="l"/><a:endParaRPr sz="1100"/></a:p>';
        if (isset($options['textContents'])) {
            // default paragraph styles
            if (!isset($options['textStyles'])) {
                $options['textStyles'] = array();
            }
            // allow string as $contents instead of an array. Transform string to array
            if (!is_array($options['textContents'])) {
                $contentsNormalized = array();
                $contentsNormalized['text'] = $options['textContents'];
                $options['textContents'] = $contentsNormalized;
            }
            // if not using a subarray, generate it
            if (isset($options['textContents']['text'])) {
                $options['textContents'] = array($options['textContents']);
            }
            $paragraph = new CreateParagraph();
            $textContents = $paragraph->createElementParagraph($options['textContents'], '0', $options['textStyles']);
        }
        $imageContents = '';
        if (isset($options['imageContent']) && isset($options['rIdImage'])) {
            $imageContents = '<a:blipFill dpi="0" rotWithShape="1"><a:blip r:embed="rId'.$options['rIdImage'].'" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" /><a:srcRect/><a:stretch/></a:blipFill>';
        }
        $anchor = 't';
        if (isset($options['textStyles']) && isset($options['textStyles']['verticalAlign'])) {
            if ($options['textStyles']['verticalAlign'] == 'top') {
                $anchor = 't';
            } else if ($options['textStyles']['verticalAlign'] == 'middle') {
                $anchor = 'ctr';
            } else if ($options['textStyles']['verticalAlign'] == 'bottom') {
                $anchor = 'b';
            }
        }

        $newShape = '<xdr:twoCellAnchor editAs="'.$options['editAs'].'" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing"><xdr:from><xdr:col>'.$colFromValue.'</xdr:col><xdr:colOff>'.$colFromOffsetValue.'</xdr:colOff><xdr:row>'.$rowFromValue.'</xdr:row><xdr:rowOff>'.$rowFromOffsetValue.'</xdr:rowOff></xdr:from><xdr:to><xdr:col>'.$colToValue.'</xdr:col><xdr:colOff>'.$colToOffsetValue.'</xdr:colOff><xdr:row>'.$rowToValue.'</xdr:row><xdr:rowOff>'.$rowToOffsetValue.'</xdr:rowOff></xdr:to><xdr:sp macro="" textlink=""><xdr:nvSpPr><xdr:cNvPr id="'.$cNvPrId.'" name="'.$name.'"></xdr:cNvPr><xdr:cNvSpPr txBox="1"/></xdr:nvSpPr><xdr:spPr><a:xfrm'.$rotation.'><a:off x="0" y="0"/></a:xfrm>'.$prstGeomContents.$imageContents.$fillColorContents.$outlineContents.'</xdr:spPr>'.$styleContents.'<xdr:txBody><a:bodyPr anchor="'.$anchor.'" horzOverflow="clip" rtlCol="0" vertOverflow="clip" wrap="square"/><a:lstStyle/>'.$textContents.'</xdr:txBody></xdr:sp><xdr:clientData/></xdr:twoCellAnchor>';

        return $newShape;
    }
}