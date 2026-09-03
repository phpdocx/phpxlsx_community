<?php
// add contents and functions in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$content = array(
    'text' => 'Lorem ipsum',
);
$xlsx->addCell($content, 'A1');

$content = array(
    'text' => 'Sed ut',
    'italic' => true,
    'underline' => 'single',
);
$xlsx->addCell($content, 'A2');
$content = array(
    'text' => ' perspiciatis',
    'bold' => true,
);
$xlsx->addCell($content, 'B2');

$content = array(
    'text' => 92,
);
$xlsx->addCell($content, 'A3');
$content = array(
    'text' => 'Option A',
);
$xlsx->addCell($content, 'B3');
$content = array(
    'text' => 'Option B',
);
$xlsx->addCell($content, 'C3');

$cellStyles = array(
    'backgroundColor' => 'F5FFF2',
);

// UPPER function
$content = array(
    'text' => 'UPPER:',
    'bold' => true,
);
$xlsx->addCell($content, 'F3');
$xlsx->addFunction('=UPPER(A1)', 'G3', array(), $cellStyles);

// CONCATENATE function
$content = array(
    'text' => 'CONCATENATE:',
    'bold' => true,
);
$xlsx->addCell($content, 'F4');
$xlsx->addFunction('=CONCATENATE(A2,B2)', 'G4', array(), $cellStyles);

// SUBSTITUTE function
$content = array(
    'text' => 'SUBSTITUTE:',
    'bold' => true,
);
$xlsx->addCell($content, 'F5');
$xlsx->addFunction('=SUBSTITUTE(A1,"ipsum","replaced")', 'G5', array(), $cellStyles);

// IF function
$content = array(
    'text' => 'IF:',
    'bold' => true,
);
$xlsx->addCell($content, 'F6');
$xlsx->addFunction('=IF(A3 > 100,B3,C3)', 'G6', array(), $cellStyles);

$xlsx->saveXlsx(__DIR__ . '/example_addFunction_3');