<?php
namespace Phpxlsx\Elements;
/**
 * Create drawing legacy
 *
 * @category   Phpxlsx
 * @package    elements
 * @copyright  Copyright (c) Narcea Labs SL
 *             (https://www.narcealabs.com)
 * @license    phpxlsx Community License
 * @link       https://www.phpxlsx.com
 */
class CreateDrawingLegacy extends CreateElement
{
    /**
     * Create drawing legacy comment
     *
     * @access public
     * @param array $options
     *      'position' (array)
     * @return array
     */
    public function createElementDrawingComment($options = array())
    {
        $rowValue = (int)$options['position']['number'] - 1;
        $colValue = $this->wordToInt($options['position']['text']);

        $newContents['position'] = array(
            'row' => $rowValue,
            'column' => $colValue,
        );

        $newContents['drawingVml'] = '<v:shape fillcolor="infoBackground [80]" id="_x0000_s1026" o:insetmode="auto" strokecolor="none [81]" style="position:absolute;width:100.2pt;height:60.6pt;z-index:2;visibility:hidden" type="#_x0000_t202" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:x="urn:schemas-microsoft-com:office:excel"><v:fill color2="infoBackground [80]"/><v:shadow color="none [81]" obscured="t"/><v:path o:connecttype="none"/><v:textbox style="mso-direction-alt:auto"><div style="text-align:left"/></v:textbox><x:ClientData ObjectType="Note"><x:MoveWithCells/><x:SizeWithCells/><x:Anchor>2, 12, 0, 10, 4, 17, 4, 14</x:Anchor><x:AutoFill>False</x:AutoFill><x:Row>'.$rowValue.'</x:Row><x:Column>'.$colValue.'</x:Column></x:ClientData></v:shape>';

        return $newContents;
    }

    /**
     * Create drawing legacy for images in headers and footers
     *
     * @access public
     * @param array $options
     *      'src' (string) image
     *      'alt' (string) alt text
     *      'brightness' (string)
     *      'color' (string) automatic (default), grayscale, blackAndWhite, washout
     *      'contrast' (string)
     *      'height' (int) pt size
     *      'imageInformation' (array) width and height values
     *      'mime' (string) forces a mime (image/jpg, image/jpeg, image/png, image/gif, image/webp)
     *      'position' (string) L, C, R
     *      'scope' (string) header, footer
     *      'target' (string) first, default, even
     *      'title' (string) image as default
     *      'width' (int) pt size
     * @return array
     */
    public function createElementDrawingImage($options = array())
    {
        // rId
        $rId = $options['rId'];

        // width
        $width = '';
        if (isset($options['width'])) {
            $width = $options['width'];
        } else {
            $width = (int)$options['imageInformation']['width'] * 0.75;
        }

        // height
        $height = '';
        if (isset($options['height'])) {
            $height = $options['height'];
        } else {
            $height = (int)$options['imageInformation']['height'] * 0.75;
        }

        // title
        $title = 'image';
        if (isset($options['title'])) {
            $title = $options['title'];
        }
        $alt = '';
        if (isset($options['alt'])) {
            $alt = ' alt="'.$options['alt'].'"';
        }

        // ID
        $id = $options['position'];
        if ($options['scope'] == 'header') {
            $id .= 'H';
        } else if ($options['scope'] == 'footer') {
            $id .= 'F';
        }
        if ($options['target'] == 'first') {
            $id .= 'FIRST';
        } else if ($options['target'] == 'even') {
            $id .= 'EVEN';
        }

        // styles
        $styles = '';
        if (isset($options)) {
            $appliedStyles = array();
            if (isset($options['color'])) {
                if ($options['color'] == 'washout') {
                    $appliedStyles['blacklevel'] = '22938f';
                    $appliedStyles['gain'] = '19661f';
                } else if ($options['color'] == 'blackAndWhite') {
                    $appliedStyles['bilevel'] = 't';
                    $appliedStyles['grayscale'] = 't';
                } else if ($options['color'] == 'grayscale') {
                    $appliedStyles['gain'] = '52429f';
                    $appliedStyles['grayscale'] = 't';
                }
            }
            if (isset($options['brightness'])) {
                $appliedStyles['blacklevel'] = $options['brightness'];
            }
            if (isset($options['contrast'])) {
                $appliedStyles['gain'] = $options['contrast'];
            }

            if (count($appliedStyles) > 0) {
                foreach ($appliedStyles as $appliedStyleKey => $appliedStyleValue) {
                    $styles .= ' '  . $appliedStyleKey . '="' . $appliedStyleValue . '" ';
                }
            }
        }

        $newContents['id'] = $id;
        $newContents['drawingVml'] = '<v:shape '.$alt.' id="'.$id.'" style="position:absolute;margin-left:0;margin-top:0;width:'.$width.'pt;height:'.$height.'pt;z-index:1" type="#_x0000_t75" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml"><v:imagedata '.$styles.' o:relid="rId'.$rId.'" o:title="'.$title.'"/><o:lock rotation="t" v:ext="edit"/></v:shape>';

        return $newContents;
    }
}