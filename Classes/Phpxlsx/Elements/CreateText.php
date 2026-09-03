<?php
namespace Phpxlsx\Elements;
/**
 * Create text
 *
 * @category   Phpxlsx
 * @package    elements
 * @copyright  Copyright (c) Narcea Labs SL
 *             (https://www.narcealabs.com)
 * @license    phpxlsx Community License
 * @link       https://www.phpxlsx.com
 */
class CreateText extends CreateElement
{
    /**
     * Create text
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
     *      'subscript' (bool)
     *      'superscript' (bool)
     *      'underline' (string) single, double
     * Theme (Premium licenses):
     * 'theme' (array):
     *      'color' (int) theme value (zero-based index)
     * @param string $position Cell position in the current active sheet
     * @param array $cellStyles Cell styles
     *      'backgroundColor' (string) FFFF00, CCCCCC ...
     *      'border' (string) thin, dashed, double, mediumDashDotDot, hair ... apply for each side with 'borderTop', 'borderRight', 'borderBottom', 'borderLeft' and 'borderDiagonal'
     *      'borderColor' (string) FFFFFF, FF0000... apply for each side with 'borderColorTop', 'borderColorRight', 'borderColorBottom', 'borderColorLeft' and 'borderColorDiagonal'
     *      'cellStyleName' (string) cell style name
     *      'dateFormat' (string) 1900, 1904
     *      'horizontalAlign' (string) left, center, right
     *      'indent' (int)
     *      'isFunction' (bool) set if it's a function
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
     * @return array
     */
    public function createElementText($contents, $position, $cellStyles = array())
    {
        $newContents = array(
            'cellStyles' => array(),
            'sharedStrings' => '',
            'textStyles' => '',
            'type' => array(),
        );

        if (is_array($contents) && count($contents) > 0) {
            if (isset($contents['text'])) {
                // regular text string
                if (!isset($contents['text'])) {
                    $contents['text'] = '';
                }
                // normalize if DateTime format
                if ($contents['text'] instanceof \DateTime) {
                    $contents['text'] = $contents['text']->format('Y-m-d H:i');
                }
                $newContents['sharedStrings'] = '<si><t xml:space="preserve">' . $this->parseAndCleanTextString($contents['text']) . '</t></si>';
                $newStyles = $this->generateTextStyles($contents);
                if (!empty($newStyles)) {
                    // avoid adding empty styles
                    $newContents['textStyles'] = '<font>' . $this->generateTextStyles($contents) . '</font>';
                }
                $newContents['cellStyles'] = $this->generateCellStyles($cellStyles);
                $newContents['type'] = $this->generateType($contents, $cellStyles);
            } else if (is_array($contents)) {
                // rich text string
                $richTextContent = '<si>';
                foreach ($contents as $content) {
                    if (!isset($content['text'])) {
                        $content['text'] = '';
                    }
                    $richTextContent .= '<r>';
                    $newStyles = $this->generateTextStyles($content);
                    $richTextContent .= '<rPr>' . str_replace(array('<name '), array('<rFont '), $newStyles) . '</rPr>';
                    $richTextContent .= '<t xml:space="preserve">' . $this->parseAndCleanTextString($content['text']) . '</t>';
                    $richTextContent .= '</r>';
                }
                $richTextContent .= '</si>';

                $newContents['sharedStrings'] = $richTextContent;
                $newContents['cellStyles'] = $this->generateCellStyles($cellStyles);
                $newContents['type'] = $this->generateType($contents, $cellStyles);
            }
        }

        return $newContents;
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
     * Generate cell styles
     *
     * @access protected
     * @param array $styles
     * @return array
     */
    protected function generateCellStyles($styles)
    {
        $stylesCell =  array();

        if ((isset($styles['backgroundColor']) && !empty($styles['backgroundColor'])) || (isset($styles['theme']) && is_array($styles['theme']) && isset($styles['theme']['backgroundColor']))) {
            $stylesCell['backgroundColor'] = '<fill><patternFill patternType="solid"><fgColor ';
            if (isset($styles['theme']) && is_array($styles['theme']) && isset($styles['theme']['backgroundColor'])) {
                $stylesCell['backgroundColor'] .= 'theme="'.$styles['theme']['backgroundColor'].'" ';
                if (isset($styles['theme']['tint'])) {
                    $stylesCell['backgroundColor'] .= 'tint="'.$styles['theme']['tint'].'" ';
                }
            }
            if (isset($styles['backgroundColor'])) {
                $stylesCell['backgroundColor'] .= 'rgb="'.$styles['backgroundColor'].'" ';
            }
            $stylesCell['backgroundColor'] .= '/></patternFill></fill>';
        }
        if (isset($styles['border']) || isset($styles['borderTop']) || isset($styles['borderRight']) || isset($styles['borderBottom']) || isset($styles['borderLeft']) || isset($styles['borderDiagonal'])) {
            $borderStyle = '<border>';
            $borderStyle .= $this->generateBorderStyles('left', $styles);
            $borderStyle .= $this->generateBorderStyles('right', $styles);
            $borderStyle .= $this->generateBorderStyles('top', $styles);
            $borderStyle .= $this->generateBorderStyles('bottom', $styles);
            $borderStyle .= $this->generateBorderStyles('diagonal', $styles);
            $borderStyle .= '</border>';

            $stylesCell['border'] = $borderStyle;
        }
        if (
            isset($styles['horizontalAlign']) ||
            isset($styles['indent']) ||
            isset($styles['rotation']) ||
            isset($styles['shrinkToFit']) ||
            isset($styles['textDirection']) ||
            isset($styles['verticalAlign']) ||
            isset($styles['wrapText'])
            )
        {
            $alignmentStyle = '<alignment ';
            if (isset($styles['horizontalAlign']) && !empty($styles['horizontalAlign'])) {
                $alignmentStyle .= 'horizontal="' . $styles['horizontalAlign'] . '" ';
            }
            if (isset($styles['indent']) && !empty($styles['indent'])) {
                $alignmentStyle .= 'indent="' . $styles['indent'] . '" ';
            }
            if (isset($styles['rotation']) && !empty($styles['rotation'])) {
                $alignmentStyle .= 'textRotation="' . ((int)$styles['rotation'] * 60000) . '" ';
            }
            if (isset($styles['shrinkToFit']) && $styles['shrinkToFit']) {
                $alignmentStyle .= 'shrinkToFit="1" ';
            }
            if (isset($styles['textDirection']) && !empty($styles['textDirection'])) {
                if ($styles['textDirection'] == 'ltr') {
                    $alignmentStyle .= 'readingOrder="1" ';
                } else if ($styles['textDirection'] == 'rtl') {
                    $alignmentStyle .= 'readingOrder="2" ';
                }
            }
            if (isset($styles['verticalAlign']) && !empty($styles['verticalAlign'])) {
                $alignmentStyle .= 'vertical="' . $styles['verticalAlign'] . '" ';
            }
            if (isset($styles['wrapText']) && $styles['wrapText']) {
                $alignmentStyle .= 'wrapText="1" ';
            }
            $alignmentStyle .= '/>';

            $stylesCell['alignment'] = $alignmentStyle;
        }
        if (isset($styles['locked'])) {
            if ($styles['locked']) {
                $stylesCell['locked'] = '<protection locked="1"/>';
            } else {
                $stylesCell['locked'] = '<protection locked="0"/>';
            }
        }

        if (isset($styles['type']) && !empty($styles['type'])) {
        }

        if (isset($styles['cellStyleName']) && !empty($styles['cellStyleName'])) {
            $stylesCell['cellStyleName'] = $styles['cellStyleName'];
        }

        return $stylesCell;
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
        if (isset($styles['color']) || (isset($styles['theme']) && is_array($styles['theme']) && isset($styles['theme']['color']))) {
            $stylesContent .= '<color ';
            if (isset($styles['theme']) && is_array($styles['theme']) && isset($styles['theme']['color'])) {
                $stylesContent .= 'theme="'.$styles['theme']['color'].'" ';
                if (isset($styles['theme']['tint'])) {
                    $stylesContent .= 'tint="'.$styles['theme']['tint'].'" ';
                }
            }
            if (isset($styles['color'])) {
                $stylesContent .= 'rgb="'.$styles['color'].'" ';
            }
            $stylesContent .= '/>';
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

    /**
     * Generate type
     *
     * @access protected
     * @param array $contents
     * @param array $styles
     * @return array
     */
    protected function generateType($contents, $styles)
    {
        $type = array();
        // default values
        if (!isset($styles['type']) && isset($contents['text'])) {
            // detect content type. Default as general
            if (is_numeric($contents['text'])) {
                // number
                $styles['type'] = 'number';
            } else if (is_bool($contents['text'])) {
                // boolean
                $styles['type'] = 'boolean';
            } else if ($contents['text'] instanceof \DateTime) {
                // date and time
                $styles['type'] = 'date';
            } else if ((substr($contents['text'], 0, 1) == '%' || substr($contents['text'], -1) == '%') && is_numeric(str_replace('%', '', $contents['text']))) {
                // percentage
                $styles['type'] = 'percentage';
            } else if (substr($contents['text'], 0, 1) == '=') {
                // function
                $styles['type'] = 'function';
            } else {
                // default general
                $styles['type'] = 'general';
            }
        }
        // in case of rich text or the type has not been set previously set default general
        if (!isset($styles['type'])) {
            $styles['type'] = 'general';
        }

        $type['defaultCellStyles'] = array();

        switch ($styles['type']) {
            case 'accounting':
            case 'currency':
                $type['sharedString'] = false;
                $type['cellType'] = 't="n"';
                break;
            case 'boolean':
                $type['sharedString'] = false;
                $type['cellType'] = 't="b"';

                // normalize boolean values
                if ($contents['text']) {
                    $type['newContentText'] = 1;
                } else {
                    $type['newContentText'] = 0;
                }
                break;
            case 'date':
                $type['sharedString'] = false;
                $type['cellType'] = 't="n"';
                $type['defaultCellStyles']['numFmtId'] = 14;
                $referenceDate = strtotime('1900-01-01 00:00'); // 1900 date system. Default
                if (isset($styles['dateFormat']) && $styles['dateFormat'] == '1904') {
                    // 1904 date system
                    $referenceDate = strtotime('1904-01-01 00:00');
                }
                $newDate = strtotime($contents['text']);
                $dateDiff = $newDate - $referenceDate;
                $type['newContentText'] = round($dateDiff / 86400);
                // offset hours, minutes and seconds
                $offsetTime = date('H:i:s', strtotime($contents['text']));
                if ($offsetTime != '00:00:00') {
                    // normalize date to get 00:00:00 time
                    $newDate = strtotime(date('Y-m-d 00:00:00', $newDate));
                    $dateDiff = $newDate - $referenceDate;
                    $type['newContentText'] = round($dateDiff / 86400);
                    // default number format when showing date and time
                    $type['defaultCellStyles']['numFmtId'] = 22;
                    // add time offset
                    $newTimeSubs = explode(":", $offsetTime);
                    $type['newContentText'] += (((float)$newTimeSubs[0] + ((float)$newTimeSubs[1]/60) + ((float)$newTimeSubs[2]/3600)) * 0.5) / 12;
                }
                // MS Excel starts from 1. PHP diff first value is 0
                (int)$type['newContentText']++;
                // fix PHP 29 Feb 2019
                if ($type['newContentText'] >= 60) {
                    (int)$type['newContentText']++;
                }
                // 1904 date format doesn't add these increments
                if (isset($styles['dateFormat']) && $styles['dateFormat'] == '1904') {
                    (int)$type['newContentText']--;
                    if ($type['newContentText'] >= 60) {
                        (int)$type['newContentText']--;
                    }
                }
                // dates lower than 0 can't be handled by MS Excel. Add the date as sharedString
                if ((float)$type['newContentText'] < 0) {
                    $type['sharedString'] = true;
                    $type['cellType'] = 't="s"';
                    $type['newContentText'] = $contents['text'];
                }
                break;
            case 'empty':
                $type['sharedString'] = false;
                $type['cellType'] = 'empty';
                break;
            case 'fraction':
                $type['sharedString'] = false;
                $type['cellType'] = 't="n"';
                $type['defaultCellStyles']['numFmtId'] = 12;
                break;
            case 'number':
                $type['sharedString'] = false;
                $type['cellType'] = 't="n"';
                $type['defaultCellStyles']['numFmtId'] = 2;
                break;
            case 'percentage':
                $type['sharedString'] = false;
                $type['cellType'] = 't="n"';
                $type['defaultCellStyles']['numFmtId'] = 9;

                // normalize percentage if the value contains %
                if ((substr($contents['text'], 0, 1) == '%' || substr($contents['text'], -1) == '%') && is_numeric(str_replace('%', '', $contents['text']))) {
                    $type['newContentText'] = str_replace('%', '', $contents['text']) / 100;
                }
                break;
            case 'scientific':
                $type['sharedString'] = false;
                $type['cellType'] = 't="n"';
                $type['defaultCellStyles']['numFmtId'] = 11;
                break;
            case 'special':
                $type['sharedString'] = false;
                $type['cellType'] = '';
                break;
            case 'text':
                $type['sharedString'] = true;
                $type['cellType'] = 't="s"';
                if (isset($contents['text']) && is_numeric($contents['text'])) {
                    // check if the content to be added is numeric or not.
                    // Needed to add the content as shared string or not
                    $type['sharedString'] = false;
                    $type['cellType'] = 't="n"';
                    $type['defaultCellStyles']['numFmtId'] = 49;
                }
                break;
            case 'time':
                $type['sharedString'] = false;
                $type['cellType'] = 't="n"';
                $type['defaultCellStyles']['numFmtId'] = 21;
                $newTime = date('H:i:s', strtotime($contents['text']));
                $newTimeSubs = explode(":", $newTime);
                $type['newContentText'] = (((float)$newTimeSubs[0] + ((float)$newTimeSubs[1]/60) + ((float)$newTimeSubs[2]/3600)) * 0.5) / 12;
                break;
            default:
                $type['sharedString'] = true;
                $type['cellType'] = 't="s"';
                if (isset($contents['text']) && is_numeric($contents['text'])) {
                    // check if the content to be added is numeric or not.
                    // Needed to add the content as shared string or not
                    $type['sharedString'] = false;
                    $type['cellType'] = 't="n"';
                }
                break;
        }

        if ((isset($styles['isFunction']) && $styles['isFunction']) || $styles['type'] == 'function') {
            $type['sharedString'] = false;
            if ($type['cellType'] == 't="s"') {
                $type['cellType'] = 't="str"';
            }

            // normalize function

            $type['newContentText'] = $contents['text'];

            // if the value contains '=' at first character. Remove '='
            if (substr($contents['text'], 0, 1) == '=') {
                $type['newContentText'] = substr($contents['text'], 1);
            }

            // use ; as ,
            $type['newContentText'] = str_replace(';', ',', $type['newContentText']);

            // handle special characters
            $type['newContentText'] = $this->parseAndCleanTextString($type['newContentText']);

            // add _xlfn when needed
            $type['newContentText'] = $this->generateFunctionXlfn($type['newContentText']);

            $type['newTagType'] = 'f';
        }

        // type options
        if (isset($styles['typeOptions'])) {
            $type['typeOptions'] = array();
            if (isset($styles['typeOptions']['formatCode'])) {
                $type['typeOptions']['formatCode'] = $styles['typeOptions']['formatCode'];
            }
        }

        return $type;
    }

    /**
     * Generate xlfn in functions
     *
     * @param string $function
     * @return string
     */
    protected function generateFunctionXlfn($function)
    {
        // https://support.microsoft.com/en-us/office/excel-functions-alphabetical-b3944572-255d-4efb-bb96-c6d90033e188

        // MS Excel functions
        $functionNames = array(
            'ABS', 'ACCRINT', 'ACCRINTM', 'ACOS', 'ACOSH', 'AGGREGATE', 'ADDRESS', 'AMORDEGRC', 'AMORLINC', 'AND', 'AREAS', 'ASC', 'ASIN', 'ASINH', 'ATAN', 'ATAN2', 'ATANH', 'AVEDEV', 'AVERAGE', 'AVERAGEA', 'AVERAGEIF', 'AVERAGEIFS',
            'BAHTTEXT', 'BESSELI', 'BESSELJ', 'BESSELK', 'BESSELY', 'BETADIST', 'BETAINV', 'BIN2DEC', 'BIN2HEX', 'BIN2OCT', 'BINOMDIST',
            'CALL', 'CEILING', 'CEILING.PRECISE', 'CELL', 'CHAR', 'CHIDIST', 'CHIINV', 'CHITEST', 'CHOOSE', 'CLEAN', 'CODE', 'COLUMN', 'COLUMNS', 'COMBIN', 'COMPLEX', 'CONCATENATE', 'CONFIDENCE', 'CONVERT', 'CORREL', 'COS', 'COSH', 'COUNT', 'COUNTA', 'COUNTBLANK', 'COUNTIF', 'COUNTIFS', 'COUPDAYBS', 'COUPDAYS', 'COUPDAYSNC', 'COUPNCD', 'COUPNUM', 'COUPPCD', 'COVAR', 'CRITBINOM', 'CUBEKPIMEMBER', 'CUBEMEMBER', 'CUBEMEMBERPROPERTY', 'CUBERANKEDMEMBER', 'CUBESET', 'CUBESETCOUNT', 'CUBEVALUE', 'CUMIPMT', 'CUMPRINC',
            'DATE', 'DATEDIF', 'DATEVALUE', 'DAVERAGE', 'DAY', 'DAYS360', 'DB', 'DCOUNT', 'DCOUNTA', 'DDB', 'DEC2BIN', 'DEC2HEX', 'DEC2OCT', 'DEGREES', 'DELTA', 'DEVSQ', 'DGET', 'DISC', 'DMAX', 'DMIN', 'DOLLAR', 'DOLLARDE', 'DOLLARFR', 'DPRODUCT', 'DSTDEV', 'DSTDEVP', 'DSUM', 'DURATION', 'DVAR', 'DVARP',
            'EDATE', 'EFFECT', 'EOMONTH', 'ERF', 'ERFC', 'ERROR.TYPE', 'EUROCONVERT', 'EVEN', 'EXACT', 'EXP', 'EXPONDIST',
            'FACT', 'FACTDOUBLE', 'FALSE', 'FDIST', 'FIND', 'FINDB', 'FINV', 'FISHER', 'FISHERINV', 'FIXED', 'FLOOR', 'FLOOR.PRECISE', 'FORECAST', 'FREQUENCY', 'FTEST', 'FV', 'FVSCHEDULE',
            'GAMMADIST', 'GAMMAINV', 'GAMMALN', 'GCD', 'GEOMEAN', 'GESTEP', 'GETPIVOTDATA', 'GROWTH',
            'HARMEAN', 'HEX2BIN', 'HEX2DEC', 'HEX2OCT', 'HLOOKUP', 'HOUR', 'HYPERLINK', 'HYPGEOM.DIST', 'HYPGEOMDIST',
            'IF', 'IFERROR', 'IMABS', 'IMAGINARY', 'IMARGUMENT', 'IMCONJUGATE', 'IMCOS', 'IMDIV', 'IMEXP', 'IMLN', 'IMLOG10', 'IMLOG2', 'IMPOWER', 'IMPRODUCT', 'IMREAL', 'IMSIN', 'IMSQRT', 'IMSUB', 'IMSUM', 'INDEX', 'INDIRECT', 'INFO', 'INT', 'INTERCEPT', 'INTRATE', 'IPMT', 'IRR', 'ISBLANK', 'ISERR', 'ISERROR', 'ISEVEN', 'ISLOGICAL', 'ISNA', 'ISNONTEXT', 'ISNUMBER', 'ISODD', 'ISREF', 'ISTEXT', 'ISPMT',
            'JIS',
            'KURT',
            'LARGE', 'LCM', 'LEFT', 'LEFTB', 'LEN', 'LENB', 'LINEST', 'LN', 'LOG', 'LOG10', 'LOGEST', 'LOGINV', 'LOGNORMDIST', 'LOWER',
            'MATCH', 'MAX', 'MAXA', 'MDETERM', 'MDURATION', 'MEDIAN', 'MID', 'MIDB', 'MIN', 'MINA', 'MINUTE', 'MINVERSE', 'MIRR', 'MMULT', 'MOD', 'MODE', 'MONTH', 'MROUND', 'MULTINOMIAL',
            'N', 'NA', 'NEGBINOMDIST', 'NETWORKDAYS', 'NOMINAL', 'NORMDIST', 'NORMINV', 'NORMSDIST', 'NORMSINV', 'NOT', 'NOW', 'NPER', 'NPV',
            'OCT2BIN', 'OCT2DEC', 'OCT2HEX', 'ODD', 'ODDFPRICE', 'ODDFYIELD', 'ODDLPRICE', 'ODDLYIELD', 'OFFSET', 'OR',
            'PEARSON', 'PERCENTILE', 'PERCENTRANK', 'PERMUT', 'PHONETIC', 'PI', 'PMT', 'POISSON', 'POWER', 'PPMT', 'PRICE', 'PRICEDISC', 'PRICEMAT', 'PROB', 'PRODUCT', 'PROPER', 'PV',
            'QUARTILE', 'QUOTIENT',
            'RADIANS', 'RAND', 'RANDBETWEEN', 'RANK', 'RATE', 'RECEIVED', 'REGISTER.ID', 'REPLACE', 'REPLACEB', 'REPT', 'RIGHT', 'RIGHTB', 'ROMAN', 'ROUND', 'ROUNDDOWN', 'ROUNDUP', 'ROW', 'ROWS', 'RSQ', 'RTD',
            'SEARCH', 'SEARCHB', 'SECOND',
            'SERIESSUM', 'SIGN', 'SIN', 'SINH', 'SKEW', 'SLN', 'SLOPE', 'SMALL', 'SQRT', 'SQRTPI', 'STANDARDIZE', 'STOCKHISTORY', 'STDEV', 'STDEVA', 'STDEVP', 'STDEVPA', 'STEYX', 'SUBSTITUTE', 'SUBTOTAL', 'SUM', 'SUMIF', 'SUMIFS', 'SUMPRODUCT', 'SUMSQ', 'SUMX2MY2', 'SUMX2PY2', 'SUMXMY2', 'SYD',
            'T', 'TAN', 'TANH', 'TBILLEQ', 'TBILLPRICE', 'TBILLYIELD', 'TDIST', 'TEXT', 'TIME', 'TIMEVALUE', 'TINV', 'TODAY', 'TRANSPOSE', 'TREND', 'TRIM', 'TRIMMEAN', 'TRUE', 'TRUNC', 'TTEST', 'TYPE',
            'UPPER',
            'VALUE', 'VAR', 'VARA', 'VARP', 'VARPA', 'VDB', 'VLOOKUP',
            'YEAR', 'YEARFRAC', 'YIELD', 'YIELDDISC', 'YIELDMAT',
            'WEEKDAY', 'WEEKNUM', 'WEIBULL', 'WORKDAY',
            'XIRR', 'XNPV',
            'ZTEST',
        );

        // MS Excel 2010 functions
        $functionNames2010 = array(
            'BASE', 'BETA.DIST', 'BETA.INV', 'BINOM.DIST', 'BINOM.INV',
            'CHISQ.DIST', 'CHISQ.DIST.RT', 'CHISQ.INV', 'CHISQ.INV.RT', 'CHISQ.TEST', 'CONFIDENCE.NORM', 'CONFIDENCE.T', 'COVARIANCE.P', 'COVARIANCE.S',
            'ERF.PRECISE', 'ERFC.PRECISE', 'EXPON.DIST',
            'F.DIST', 'F.DIST.RT', 'F.INV', 'F.INV.RT', 'F.TEST',
            'GAMMA.DIST', 'GAMMA.INV', 'GAMMALN.PRECISE',
            'LOGNORM.DIST', 'LOGNORM.INV', 'LOOKUP',
            'MODE.MULT', 'MODE.SNGL',
            'NEGBINOM.DIST', 'NETWORKDAYS.INTL', 'NORM.DIST', 'NORM.INV', 'NORM.S.DIST', 'NORM.S.INV',
            'PERCENTILE.EXC', 'PERCENTILE.INC', 'PERCENTRANK.EXC', 'PERCENTRANK.INC', 'POISSON.DIST',
            'QUARTILE.EXC', 'QUARTILE.INC',
            'RANK.AVG', 'RANK.EQ',
            'STDEV.P', 'STDEV.S',
            'T.DIST', 'T.DIST.2T', 'T.DIST.RT', 'T.INV', 'T.INV.2T', 'T.TEST',
            'VAR.P', 'VAR.S',
            'WEIBULL.DIST', 'WORKDAY.INTL',
            'Z.TEST',
        );

        // MS Excel 2013 functions
        $functionNames2013 = array(
            'ACOT', 'ACOTH', 'ARABIC',
            'BINOM.DIST.RANGE', 'BITAND', 'BITLSHIFT', 'BITOR', 'BITRSHIFT', 'BITXOR',
            'CEILING.MATH', 'COMBINA', 'COT', 'COTH', 'CSC', 'CSCH',
            'DAYS', 'DBCS', 'DECIMAL',
            'ENCODEURL',
            'FILTERXML', 'FLOOR.MATH', 'FORMULATEXT',
            'GAMMA', 'GAUSS',
            'IFNA', 'IMCOSH', 'IMCOT', 'IMCSC', 'IMCSCH', 'IMSEC', 'IMSECH', 'IMSINH', 'IMTAN', 'ISFORMULA', 'ISO.CEILING', 'ISOWEEKNUM',
            'MUNIT',
            'NUMBERVALUE',
            'PDURATION', 'PERMUTATIONA', 'PHI',
            'RRI',
            'SEC', 'SECH', 'SHEET', 'SHEETS', 'SKEW.P',
            'UNICHAR', 'UNICODE',
            'WEBSERVICE',
            'XOR',
        );

        // MS Excel 2016 functions
        $functionNames2016 = array(
            'FORECAST.ETS', 'FORECAST.ETS.CONFINT', 'FORECAST.ETS.SEASONALITY', 'FORECAST.ETS.STAT', 'FORECAST.LINEAR',
        );

        // MS Excel 2019 functions
        $functionNames2019 = array(
            'CONCAT',
            'IFS',
            'MAXIFS', 'MINIFS',
            'TEXTJOIN',
            'SWITCH',
        );

        // MS Excel 365 functions
        $functionNames365 = array(
            'ARRAYTOTEXT',
            'FILTER',
            'LET',
            'RANDARRAY',
            'SEQUENCE',
            'SORT', 'SORTBY', 'SWITCH',
            'UNIQUE',
            'VALUETOTEXT',
            'XLOOKUP',
            'XMATCH',
        );

        // MS Excel 2024 functions
        $functionNames2024 = array(
            'BYCOL', 'BYROW',
            'CHOOSECOLS', 'CHOOSEROWS',
            'DROP',
            'EXPAND',
            'HSTACK',
            'IMAGE',
            'ISOMITTED',
            'LAMBDA',
            'MAKEARRAY', 'MAP',
            'REDUCE',
            'SCAN',
            'TAKE', 'TEXTAFTER', 'TEXTBEFORE', 'TEXTSPLIT', 'TOCOL', 'TOROW',
            'VSTACK',
            'WRAPCOLS', 'WRAPROWS',
        );

        // clean existing _xlfn. to don't duplicate them
        $function = str_replace('_xlfn.', '', $function);

        // add _xfln. when needed
        foreach (array($functionNames2010, $functionNames2013, $functionNames2016, $functionNames2019, $functionNames365, $functionNames2024) as $functionNamesXlfn) {
            foreach ($functionNamesXlfn as $functionNameXlfn) {
                $function = preg_replace('/\b(' . $functionNameXlfn . ')[(]\b' . '/', '_xlfn.' . $functionNameXlfn . '(', $function);
            }
        }

        return $function;
    }
}