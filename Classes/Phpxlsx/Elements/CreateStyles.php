<?php
namespace Phpxlsx\Elements;
/**
 * Create styles
 *
 * @category   Phpxlsx
 * @package    elements
 * @copyright  Copyright (c) Narcea Labs SL
 *             (https://www.narcealabs.com)
 * @license    phpxlsx Community License
 * @link       https://www.phpxlsx.com
 */
class CreateStyles extends CreateElement
{
    /**
     * Create table styles
     *
     * @access public
     * @param array $styles Text contents and styles
     *      'text' (string)
     *      'bold' (bool)
     *      'color' (string) FFFFFF, FF0000, ...
     *      'font' (string) Arial, Times New Roman...
     *      'fontSize' (8, 9, 10, ...)
     *      'italic' (bool)
     *      'strikethrough' (bool)
     *      'subscript' (bool)
     *      'superscript' (bool)
     *      'underline' (string) single, double
     * @return string
     */
    public function createDxfStyles($styles)
    {
        $stylesContent = '<dxf>';

        // font styles
        $stylesContent .= '<font>';
        if (isset($styles['bold']) && $styles['bold']) {
            $stylesContent .= '<b/>';
        }
        if (isset($styles['color'])) {
            $styles['color'] = strtoupper(str_replace('#', '', $styles['color']));
            $stylesContent .= '<color rgb="'.$styles['color'].'"/>';
        }
        if (isset($styles['font'])) {
            $stylesContent .= '<name val="'.$styles['font'].'"/>';
        }
        if (isset($styles['fontSize'])) {
            $stylesContent .= '<sz val="'.$styles['fontSize'].'"/>';
        }
        if (isset($styles['italic']) && $styles['italic']) {
            $stylesContent .= '<i/>';
        }
        if (isset($styles['strikethrough']) && $styles['strikethrough']) {
            $stylesContent .= '<strike/>';
        }
        if (isset($styles['underline'])) {
            $stylesContent .= '<u val="'.$styles['underline'].'"/>';
        }
        $stylesContent .= '</font>';

        // fill styles
        if (isset($styles['backgroundColor']) && !empty($styles['backgroundColor'])) {
            $stylesContent .= '<fill><patternFill patternType="solid"><bgColor rgb="' . $styles['backgroundColor'] . '"/></patternFill></fill>';
        }

        // border styles
        if (isset($styles['border']) || isset($styles['borderTop']) || isset($styles['borderRight']) || isset($styles['borderBottom']) || isset($styles['borderLeft']) || isset($styles['borderDiagonal'])) {
            $borderStyle = '<border>';
            $borderStyle .= $this->generateBorderStyles('left', $styles);
            $borderStyle .= $this->generateBorderStyles('right', $styles);
            $borderStyle .= $this->generateBorderStyles('top', $styles);
            $borderStyle .= $this->generateBorderStyles('bottom', $styles);
            $borderStyle .= $this->generateBorderStyles('diagonal', $styles);
            $borderStyle .= '</border>';

            $stylesContent .= $borderStyle;
        }

        $stylesContent .= '</dxf>';

        return $stylesContent;
    }

    /**
     * Generate border styles
     *
     * @access protected
     * @param string $target
     * @param array $styles
     * @return string
     */
    protected function generateBorderStyles($target, $styles)
    {
        $borderStyle = '';

        $borderScope = 'border' . ucfirst($target);
        $borderScopeColor = 'borderColor' . ucfirst($target);
        if (isset($styles['border']) || isset($styles[$borderScope])) {
            if (isset($styles[$borderScope])) {
                $borderStyle .= '<' . $target . ' style="' . $styles[$borderScope] . '">';
            } else {
                $borderStyle .= '<' . $target . ' style="' . $styles['border'] . '">';
            }
            if (isset($styles['borderColor']) || isset($styles[$borderScopeColor])) {
                if (isset($styles[$borderScopeColor])) {
                    $borderStyle .= '<color rgb="' . $styles[$borderScopeColor] . '"/>';
                } else {
                    $borderStyle .= '<color rgb="' . $styles['borderColor'] . '"/>';
                }
            } else {
                $borderStyle .= '<color auto="1"/>';
            }
            $borderStyle .= '</' . $target . '>';
        } else {
            $borderStyle .= '<' . $target . '/>';
        }

        return $borderStyle;
    }

    /**
     * Generate text styles
     *
     * @access protected
     * @param array $styles
     * @return string
     */
    protected function generateTextStyles($styles)
    {
        $stylesContent = '';
        if (isset($styles['bold']) && $styles['bold']) {
            $stylesContent .= '<b/>';
        }
        if (isset($styles['color'])) {
            $stylesContent .= '<color rgb="'.$styles['color'].'"/>';
        }
        if (isset($styles['font'])) {
            $stylesContent .= '<name val="'.$styles['font'].'"/>';
        }
        if (isset($styles['fontSize'])) {
            $stylesContent .= '<sz val="'.$styles['fontSize'].'"/>';
        }
        if (isset($styles['italic']) && $styles['italic']) {
            $stylesContent .= '<i/>';
        }
        if (isset($styles['strikethrough']) && $styles['strikethrough']) {
            $stylesContent .= '<strike/>';
        }
        if (isset($styles['subscript']) && $styles['subscript']) {
            $stylesContent .= '<vertAlign val="subscript"/>';
        }
        if (isset($styles['superscript']) && $styles['superscript']) {
            $stylesContent .= '<vertAlign val="superscript"/>';
        }
        if (isset($styles['underline'])) {
            $stylesContent .= '<u val="'.$styles['underline'].'"/>';
        }

        return $stylesContent;
    }
}