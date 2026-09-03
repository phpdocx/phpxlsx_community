<?php
// add a function compatible with MS Excel 2013 and newer in an XLSX created from scratch
// New versions of MS Excel include new functions not compatible with previous versions (https://support.microsoft.com/en-us/office/excel-functions-alphabetical-b3944572-255d-4efb-bb96-c6d90033e188)

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$content = array(
    'text' => 3,
);
$xlsx->addCell($content, 'A2');
$content = array(
    'text' => 5,
);
$xlsx->addCell($content, 'A3');

$contentStyles = array(
    'bold' => true,
    'font' => 'Arial',
);
$cellStyles = array(
    'backgroundColor' => 'FFFF00',
    'verticalAlign' => 'top',
);
$xlsx->addFunction('=ACOT(5)', 'D3', $contentStyles, $cellStyles);

// a raw function can also be added. This raw option won't normalize the function content
$xlsx->addFunction('=_xlfn.GAMMA(A2)', 'E3', $contentStyles, $cellStyles, array('raw' => true));

$xlsx->saveXlsx(__DIR__ . '/example_addFunction_4');