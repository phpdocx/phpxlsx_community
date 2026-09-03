<?php
namespace Phpxlsx\Elements;
/**
 * Create image
 *
 * @category   Phpxlsx
 * @package    elements
 * @copyright  Copyright (c) Narcea Labs SL
 *             (https://www.narcealabs.com)
 * @license    phpxlsx Community License
 * @link       https://www.phpxlsx.com
 */
class CreateImage extends CreateElement
{
    /**
     * Scale image factor
     */
    const SCALEFACTOR = 360000;

    /**
     * Create image
     *
     * @access public
     * @param string $image
     * @param array $position
     * @param array $options
     *      'colOffset' (array) given in emus (1cm = 360000 emus). 0 as default
     *          'from' (int) from offset
     *          'to' (int) from offset
     *      'colSize' (int) number of cols used by the image
     *      'descr' (string) set a descr value
     *      'dpi' (int) dots per inch
     *      'editAs' (string) oneCell (default) (move but don't size with cells), twoCell (move and size with cells), absolute (don't move or size with cells)
     *      'imageInformation' (array) image information
     *      'mime' (string) forces a mime (image/jpg, image/jpeg, image/png, image/gif, image/webp)
     *      'name' (string) set a name value
     *      'rId' (string) image ID
     *      'rIdHyperlink' (string) hyperlink ID
     *      'rotation' (int) 60.000ths of a degree
     *      'rowOffset' (array) given in emus (1cm = 360000 emus). 0 as default
     *          'from' (int) from offset
     *          'to' (int) from offset
     *      'rowSize' (int) number of rows used by the image
     *      'transparency' (int) 0 to 100
     * @return array
     */
    public function createElementImage($image, $position, $options = array())
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

        $newContents = array(
            'drawingXml' => '',
        );

        $width = null;
        if (isset($options['imageInformation']['width'])) {
            $width = $options['imageInformation']['width'];
            if (isset($options['scaling'])) {
                $width = $width * $options['scaling'] / 100;
            }
        }

        $height = null;
        if (isset($options['imageInformation']['height'])) {
            $height = $options['imageInformation']['height'];
            if (isset($options['scaling'])) {
                $height = $height * $options['scaling'] / 100;
            }
        }

        $dpi = null;
        if (isset($options['dpi'])) {
            $dpi = $options['dpi'];
        }

        $name = 'Picture ' . $options['rId'];
        if (isset($options['name'])) {
            $name = $this->parseAndCleanTextString($options['name']);
        }

        $descr = '';
        if (isset($options['descr'])) {
            $descr = $this->parseAndCleanTextString($options['descr']);
        }

        switch ($options['imageInformation']['mime']) {
            case 'image/png':
                list($dpiX, $dpiY) = $this->getDpiPng($image, $dpi);
                $width = round($width * 2.54 / $dpiX * CreateImage::SCALEFACTOR);
                $height = round($height * 2.54 / $dpiY * CreateImage::SCALEFACTOR);
                break;
            case 'image/jpg':
            case 'image/jpeg':
                list($dpiX, $dpiY) = $this->getDpiJpg($image, $dpi);
                $width = round($width * 2.54 / $dpiX * CreateImage::SCALEFACTOR);
                $height = round($height * 2.54 / $dpiY * CreateImage::SCALEFACTOR);
                break;
            case 'image/gif':
                $dpi = 96;
                $width = round($width * 2.54 / $dpi * CreateImage::SCALEFACTOR);
                $height = round($height * 2.54 / $dpi * CreateImage::SCALEFACTOR);
                break;
            case 'image/bmp':
                $dpi = 96;
                $width = round($width * 2.54 / $dpi * CreateImage::SCALEFACTOR);
                $height = round($height * 2.54 / $dpi * CreateImage::SCALEFACTOR);
                break;
            case 'image/webp':
                $dpi = 96;
                $width = round($width * 2.54 / $dpi * CreateImage::SCALEFACTOR);
                $height = round($height * 2.54 / $dpi * CreateImage::SCALEFACTOR);
                break;
            default:
                break;
        }

        $cNvPrId = rand(999999, 999999999);

        $rotation = '';
        if (isset($options['rotation'])) {
            $rotation = ' rot="'.((int)$options['rotation'] * 60000).'"';
        }

        $hyperlinkContent = '';
        if (isset($options['rIdHyperlink'])) {
            $hyperlinkContent = '<a:hlinkClick r:id="rId'.$options['rIdHyperlink'].'" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/>';
        }

        $transparency = '';
        if (isset($options['transparency'])) {
            $alphaValue = 100000;
            if ($options['transparency'] >= 0 && $options['transparency'] <= 100) {
                $alphaValue = 100000 - ($options['transparency'] * 1000);
            }
            $transparency = '<a:alphaModFix amt="'.$alphaValue.'"/>';
        }

        $newContents['drawingXml'] = '<xdr:twoCellAnchor editAs="'.$options['editAs'].'" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing"><xdr:from><xdr:col>'.$colFromValue.'</xdr:col><xdr:colOff>'.$colFromOffsetValue.'</xdr:colOff><xdr:row>'.$rowFromValue.'</xdr:row><xdr:rowOff>'.$rowFromOffsetValue.'</xdr:rowOff></xdr:from><xdr:to><xdr:col>'.$colToValue.'</xdr:col><xdr:colOff>'.$colToOffsetValue.'</xdr:colOff><xdr:row>'.$rowToValue.'</xdr:row><xdr:rowOff>'.$rowToOffsetValue.'</xdr:rowOff></xdr:to><xdr:pic><xdr:nvPicPr><xdr:cNvPr descr="'.$descr.'" id="'.$cNvPrId.'" name="'.$name.'">'.$hyperlinkContent.'</xdr:cNvPr><xdr:cNvPicPr/></xdr:nvPicPr><xdr:blipFill><a:blip r:embed="rId'.$options['rId'].'">'.$transparency.'</a:blip><a:stretch/></xdr:blipFill><xdr:spPr><a:xfrm'.$rotation.'><a:off x="0" y="0"/><a:ext cx="'.$width.'" cy="'.$height.'"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom><a:ln w="0"><a:noFill/></a:ln></xdr:spPr></xdr:pic><xdr:clientData/></xdr:twoCellAnchor>';

        return $newContents;
    }

    /**
     * Get image jpg dpi
     *
     * @access private
     * @param string $image
     * @param int $dpi
     * @return array
     */
    private function getDpiJpg($image, $dpi)
    {
        if (!is_null($dpi)) {
            // preset dpi, do not get it from the image
            return array($dpi, $dpi);
        }

        $a = fopen($image, 'r');
        $string = fread($a, 20);
        fclose($a);
        $type = hexdec(bin2hex(substr($string, 13, 1)));
        $data = bin2hex(substr($string, 14, 4));
        if ($type == 1) {
            $x = substr($data, 0, 4);
            $y = substr($data, 4, 4);
            return array(hexdec($x), hexdec($y));
        } else if ($type == 2) {
            $x = floor(hexdec(substr($data, 0, 4)) / 2.54);
            $y = floor(hexdec(substr($data, 4, 4)) / 2.54);
            return array($x, $y);
        } else {
            // default dpi as 96
            return array(96, 96);
        }
    }

    /**
     * Get image png dpi
     *
     * @access private
     * @param string|\GdImage $image
     * @param int $dpi
     * @return array
     */
    private function getDpiPng($image, $dpi)
    {
        if (!is_null($dpi)) {
            // preset dpi, do not get it from the image
            return array($dpi, $dpi);
        }

        if ($image instanceof \GdImage || (is_resource($image) && get_resource_type($image) == 'gd')) {
            if (function_exists('imageresolution')) {
                $resolutionInfo = imageresolution($image);
            } else {
                $resolutionInfo = array(96, 96);
            }
            return array($resolutionInfo[0], $resolutionInfo[1]);
        }

        $a = fopen($image, 'r');

        $dpi = false;

        $buf = array();

        $x = 0;
        $y = 0;
        $units = 0;

        while (!feof($a)) {
            array_push($buf, ord(fread($a, 1)));
            if (count($buf) > 13) {
                array_shift($buf);
            }
            if (count($buf) < 13) {
                continue;
            }
            if ($buf[0] == ord('p') && $buf[1] == ord('H') && $buf[2] == ord('Y') && $buf[3] == ord('s')) {
                $x = ($buf[4] << 24) + ($buf[5] << 16) + ($buf[6] << 8) + $buf[7];
                $y = ($buf[8] << 24) + ($buf[9] << 16) + ($buf[10] << 8) + $buf[11];
                $units = $buf[12];
                break;
            }
        }

        fclose($a);

        if ($x == $y) {
            $dpi = $x;
        }

        if ($dpi != false && $units == 1) {
            // meters
            $dpi = round($dpi * 0.0254);
        }

        if ($dpi) {
            return array($dpi, $dpi);
        } else {
            return array(96, 96);
        }
    }
}