<?php
// remove cells

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$content = array(
    'text' => 'Lorem ipsum dolor sit amet',
);
$xlsx->addCell($content, 'A1');
$content = array(
    'text' => 'Lorem ipsum',
);
$xlsx->addCell($content, 'A2');

$content = array(
    'text' => 'Sed ut perspiciatis unde omnis',
    'italic' => true,
    'underline' => 'single',
);
$xlsx->addCell($content, 'B5');

$content = array(
    'text' => 'At vero eos et accusamus et iusto',
    'color' => '13775F',
);
$xlsx->addCell($content, 'F2');

$content = array(
    'text' => 'Lorem ipsum dolor sit amet',
    'bold' => true,
    'font' => 'Times New Roman',
    'strikethrough' => true,
);
$xlsx->addCell($content, 'AA3');

// remove cells
$xlsx->removeCell('A2');

$xlsx->saveXlsx(__DIR__ . '/example_removeCell_1');