<?php
// get column settings in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$content = array(
    'text' => 'Lorem ipsum dolor sit amet',
);
$xlsx->addCell($content, 'A2');

// set column settings. The position is set using column letters
$xlsx->setColumnSettings('B', array('width' => 40));
$xlsx->setColumnSettings('C', array('width' => 30.25));

$xlsx->addSheet(array('name'=> 'Other', 'removeSelected' => true, 'selected' => true, 'active' => true));

$content = array(
    'text' => 'Sed ut perspiciatis unde omnis',
);
$xlsx->addCell($content, 'A1');

// set column settings. The position is set using cell positions
$xlsx->setColumnSettings('A', array('width' => 20));
$xlsx->setColumnSettings('B', array('width' => 30));
$xlsx->setColumnSettings('C', array('width' => 25));

// get column settings
$columnSettings = $xlsx->getColumnSettings('A');
print_r($columnSettings);
$columnSettings = $xlsx->getColumnSettings('B');
print_r($columnSettings);
$columnSettings = $xlsx->getColumnSettings('C');
print_r($columnSettings);