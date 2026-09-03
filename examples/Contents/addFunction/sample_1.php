<?php
// add number contents and a function to sum them in an XLSX created from scratch

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

$contentStyles = array(
    'bold' => true,
    'font' => 'Arial',
);
$cellStyles = array(
    'backgroundColor' => 'FFFF00',
    'verticalAlign' => 'top',
);
$xlsx->addFunction('=SUM(A2:A3)', 'D3', $contentStyles, $cellStyles);

$xlsx->saveXlsx(__DIR__ . '/example_addFunction_1');