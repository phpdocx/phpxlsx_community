<?php
// get row settings in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$content = array(
    'text' => 'Lorem ipsum dolor sit amet',
);
$xlsx->addCell($content, 'A2');

// set row settings. The position is set using row numbers
$xlsx->setRowSettings('4', array('height' => 90.25));
$xlsx->setRowSettings('2', array('height' => 60));
$xlsx->setRowSettings('3', array('height' => 50.25));

$xlsx->addSheet(array('name'=> 'Other', 'removeSelected' => true, 'selected' => true, 'active' => true));

$content = array(
    'text' => 'Sed ut perspiciatis unde omnis',
);
$xlsx->addCell($content, 'A1');

// set row settings. The position is set using cell positions
$xlsx->setRowSettings('1', array('height' => 40));
$xlsx->setRowSettings('2', array('height' => 50));
$xlsx->setRowSettings('3', array('height' => 40));

// get row settings
$rowSettings = $xlsx->getRowSettings('1');
print_r($rowSettings);
$rowSettings = $xlsx->getRowSettings('2');
print_r($rowSettings);
$rowSettings = $xlsx->getRowSettings('3');
print_r($rowSettings);