<?php
// get cells information from an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// add contents as sample
$content = array(
    'text' => 'Lorem ipsum dolor sit amet',
);
$xlsx->addCell($content, 'A1');

$content = array(
    'text' => 100,
);
$xlsx->addCell($content, 'B1');
$content = array(
    'text' => 10.50,
);
$xlsx->addCell($content, 'B2');
$xlsx->addFunction('=SUM(B1:B2)', 'B3');

$content = array(
    'text' => 10.50,
);
$xlsx->addCell($content, 'C1');

// get cells information
var_dump($xlsx->getCell('A1'));

// return null because the position doesn't exist in the sheet
var_dump($xlsx->getCell('A2'));

// get cell information
var_dump($xlsx->getCell('B1'));

// get cell information
var_dump($xlsx->getCell('B3'));

// get cell information
var_dump($xlsx->getCell('C1'));