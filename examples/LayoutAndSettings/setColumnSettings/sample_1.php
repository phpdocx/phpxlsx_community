<?php
// set custom column width values in an XLSX created from scratch

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
$xlsx->setColumnSettings('A1', array('width' => 20));
$xlsx->setColumnSettings('B2', array('width' => 30));
$xlsx->setColumnSettings('C1', array('width' => 25));

$xlsx->saveXlsx(__DIR__ . '/example_setColumnSettings_1');