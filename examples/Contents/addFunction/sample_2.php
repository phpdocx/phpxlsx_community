<?php
// add number contents and functions in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$content = array(
    'text' => 100,
);
$xlsx->addCell($content, 'A2');
$content = array(
    'text' => 10.50,
);
$xlsx->addCell($content, 'A3');
$content = array(
    'text' => 12,
);
$xlsx->addCell($content, 'A4');
$content = array(
    'text' => 27.2,
);
$xlsx->addCell($content, 'A5');
$content = array(
    'text' => 17,
);
$xlsx->addCell($content, 'B2');
$content = array(
    'text' => 8.50,
);
$xlsx->addCell($content, 'B3');
$content = array(
    'text' => 2,
);
$xlsx->addCell($content, 'B4');
$content = array(
    'text' => 37.2,
);
$xlsx->addCell($content, 'B5');

$cellStyles = array(
    'backgroundColor' => 'F5FFF2',
    'type' => 'number',
);

// SUM function
$content = array(
    'text' => 'SUM:',
    'bold' => true,
);
$xlsx->addCell($content, 'D3');
$xlsx->addFunction('=SUM(A2:B5)', 'E3', array(), $cellStyles);

// MAX function
$content = array(
    'text' => 'MAX:',
    'bold' => true,
);
$xlsx->addCell($content, 'D4');
$xlsx->addFunction('=MAX(A2:B5)', 'E4', array(), $cellStyles);

// MIN function
$content = array(
    'text' => 'MIN:',
    'bold' => true,
);
$xlsx->addCell($content, 'D5');
$xlsx->addFunction('=MIN(A2:B5)', 'E5', array(), $cellStyles);

// PRODUCT function
$content = array(
    'text' => 'PRODUCT:',
    'bold' => true,
);
$xlsx->addCell($content, 'D6');
$xlsx->addFunction('=PRODUCT(E3,E5)', 'E6', array(), $cellStyles);

$xlsx->saveXlsx(__DIR__ . '/example_addFunction_2');