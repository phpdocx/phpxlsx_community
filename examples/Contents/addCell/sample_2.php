<?php
// add cell contents applying styles to sheets in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$content = array(
    'text' => 'Lorem ipsum dolor sit amet',
    'bold' => true,
    'font' => 'Arial',
);
$xlsx->addCell($content, 'A1');

$content = array(
    'text' => 'Sed ut perspiciatis unde omnis',
    'italic' => true,
);
$xlsx->addCell($content, 'B5');

// add a new sheet
$xlsx->addSheet(array('removeSelected' => true, 'selected' => true, 'active' => true));

$content = array(
    'text' => 'At vero eos et accusamus et iusto',
);
$xlsx->addCell($content, 'F2');

$content = array(
    'text' => 'Lorem ipsum dolor sit amet',
    'bold' => true,
    'font' => 'Times New Roman',
    'strikethrough' => true,
);
$xlsx->addCell($content, 'A1');

$xlsx->saveXlsx(__DIR__ . '/example_addCell_2');