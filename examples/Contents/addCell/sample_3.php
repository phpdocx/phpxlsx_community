<?php
// add a rich text contents applying styles in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$content = array(
    array(
        'text' => 'Lorem ipsum',
        'bold' => true,
        'font' => 'Arial',
    ),
    array(
        'text' => ' dolor sit',
        'italic' => true,
    ),
    array(
        'text' => ' amet',
        'underline' => 'single',
    ),
);
$xlsx->addCell($content, 'A1');

$content = array(
    array(
        'text' => 'Lorem ipsum',
        'bold' => true,
    ),
    array(
        'text' => ' dolor sit',
        'italic' => true,
    ),
    array(
        'text' => ' amet',
        'underline' => 'single',
    ),
);
$xlsx->addCell($content, 'B4');

$xlsx->saveXlsx(__DIR__ . '/example_addCell_3');