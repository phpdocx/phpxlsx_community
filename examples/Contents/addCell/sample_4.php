<?php
// add cell contents applying cell styles in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$content = array(
    'text' => 'Lorem ipsum',
    'fontSize' => 8,
);
$cellStyles = array(
    'backgroundColor' => 'FFFF00',
    'verticalAlign' => 'top',
);
$xlsx->addCell($content, 'A1', $cellStyles);

$content = array(
    'text' => 'Lorem ipsum dolor',
);
$cellStyles = array(
    'horizontalAlign' => 'center',
    'wrapText' => true,
);
$xlsx->addCell($content, 'B1', $cellStyles);

$content = array(
    'text' => 'Border',
);
$cellStyles = array(
    'border' => 'dashed',
    'borderBottom' => 'double',
    'borderColorBottom' => 'FF0000',
    'borderColorTop' => 'FF0000',
);
$xlsx->addCell($content, 'C4', $cellStyles);

$xlsx->saveXlsx(__DIR__ . '/example_addCell_4');