<?php
namespace Phpxlsx\Elements;
/**
 * Create paragraph
 *
 * @category   Phpxlsx
 * @package    elements
 * @copyright  Copyright (c) Narcea Labs SL
 *             (https://www.narcealabs.com)
 * @license    phpxlsx Community License
 * @link       https://www.phpxlsx.com
 */
class CreateParagraph extends CreateElement
{
    /**
     * Create paragraph
     *
     * @access public
     * @param array $contents Text contents and styles
     *      'text' (string)
     *      'bold' (bool)
     *      'color' (string) FFFFFF, FF0000, ...
     *      'font' (string) Arial, Times New Roman...
     *      'fontSize' (8, 9, 10, ...)
     *      'italic' (bool)
     *      'strikethrough' (bool)
     *      'transparency' (int) 0 to 100
     *      'underline' (string) single, double
     * @param string $position Cell position in the current active sheet
     * @param array $paragraphStyles Paragraph styles
     * @return string
     */
    public function createElementParagraph($contents, $position, $paragraphStyles = array())
    {
        $paragraphContents = '';

        if (is_array($contents) && count($contents) > 0) {
            $paragraphContents .= '<a:p xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">';

            // a:p styles
            if (count($paragraphStyles) > 0) {
                $paragraphContents .= '<a:pPr ' . $this->generateInlinePprStyles($paragraphStyles) . '>';
                $paragraphContents .= '</a:pPr>';
            }

            foreach ($contents as $content) {
                if (!is_array($content)) {
                    $content = array('text' => $content);
                }

                // get breaks from the text content
                $contentsBreaks = explode('<a:br xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" />', $content['text']);
                foreach ($contentsBreaks as $indexContentsBreak => $contentsBreak) {
                    $paragraphContents .= '<a:r xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">';

                    // a:r styles
                    $paragraphContents .= '<a:rPr ' . $this->generateInlineRprStyles($content) . '>';
                    $paragraphContents .= $this->generateExternalRprStyles($content);
                    $paragraphContents .= '</a:rPr>';

                    // text content
                    $paragraphContents .= '<a:t>' . $this->parseAndCleanTextString($contentsBreak) . '</a:t>';

                    $paragraphContents .= '</a:r>';

                    // generate break tag
                    if (isset($contentsBreaks[$indexContentsBreak + 1])) {
                        $paragraphContents .= '<a:br xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">';
                        $paragraphContents .= '<a:rPr ' . $this->generateInlineRprStyles($content) . '>';
                        $paragraphContents .= $this->generateExternalRprStyles($content);
                        $paragraphContents .= '</a:rPr>';
                        $paragraphContents .= '</a:br>';
                    }
                }
            }

            $paragraphContents .= '</a:p>';
        }

        return $paragraphContents;
    }

    /**
     * Generate external rPr styles
     *
     * @access protected
     * @param array $styles
     * @return string
     */
    protected function generateExternalRprStyles($styles)
    {
        $stylesContent = array();

        // keep a:rPr correct order
        if (isset($styles['color'])) {
            $color = '<a:solidFill><a:srgbClr val="'.str_replace('#', '', $styles['color']).'">';
            if (isset($styles['transparency'])) {
                $alphaValue = 100000;
                if ($styles['transparency'] >= 0 && $styles['transparency'] <= 100) {
                    $alphaValue = 100000 - ($styles['transparency'] * 1000);
                }
                $color .= '<a:alpha val="'.$alphaValue.'"/>';
            }
            $color .= '</a:srgbClr></a:solidFill>';
            $stylesContent[] = $color;
        }
        if (isset($styles['highlight'])) {
            $stylesContent[] = '<a:highlight><a:srgbClr val="'.str_replace('#', '', $styles['highlight']).'"/></a:highlight>';
        }
        if (isset($styles['font'])) {
            $stylesContent[] = '<a:latin typeface="'.$styles['font'].'"/><a:cs typeface="'.$styles['font'].'"/>';
        }

        $newStyles = implode('', $stylesContent);

        return $newStyles;
    }

    /**
     * Generate inline pPr styles
     *
     * @access protected
     * @param array $styles
     * @return string
     */
    protected function generateInlinePprStyles($styles)
    {
        $stylesContent = array();

        if (isset($styles['align'])) {
            if ($styles['align'] == 'center') {
                $stylesContent[] = 'algn="ctr"';
            } else if ($styles['align'] == 'right') {
                $stylesContent[] = 'algn="r"';
            } else if ($styles['align'] == 'justify') {
                $stylesContent[] = 'algn="just"';
            }
        }
        if (isset($styles['rtl']) && $styles['rtl']) {
            $stylesContent[] = 'rtl="1"';
        } else if (isset($styles['rtl']) && !$styles['rtl']) {
            $stylesContent[] = 'rtl="0"';
        }

        $newStyles = implode(' ', $stylesContent);

        return $newStyles;
    }

    /**
     * Generate inline rPr styles
     *
     * @access protected
     * @param array $styles
     * @return string
     */
    protected function generateInlineRprStyles($styles)
    {
        $stylesContent = array();

        if (isset($styles['bold']) && $styles['bold']) {
            $stylesContent[] = 'b="1"';
        }
        if (isset($styles['characterSpacing'])) {
            $stylesContent[] = 'spc="'.$styles['characterSpacing'].'"';
        }
        if (isset($styles['fontSize'])) {
            $stylesContent[] = 'sz="'.((int)$styles['fontSize']*100).'"';
        }
        if (isset($styles['italic']) && $styles['italic']) {
            $stylesContent[] = 'i="1"';
        }
        if (isset($styles['lang'])) {
            $stylesContent[] = 'lang="'.$styles['lang'].'"';
        }
        if (isset($styles['strikethrough']) && $styles['strikethrough']) {
            $stylesContent[] = 'strike="sngStrike"';
        }
        if (isset($styles['underline']) && $styles['underline'] == 'single') {
            $stylesContent[] = 'u="sng"';
        }

        $newStyles = implode(' ', $stylesContent);

        return $newStyles;
    }
}