<?php
// create and apply multiple cell styles in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// create the new cell styles
$xlsx->createCellStyle('MyStyle1', array('bold' => true, 'fontSize' => 14, 'backgroundColor' => 'FFFF00'));
$xlsx->createCellStyle('My Style 2', array('italic' => true, 'fontSize' => 7, 'backgroundColor' => 'FF0000'));

$content = array(
    'text' => 'Style 1',
);
$xlsx->addCell($content, 'A1', array('cellStyleName' => 'MyStyle1'));
$content = array(
    'text' => 'Style 2',
);
$xlsx->addCell($content, 'A3', array('cellStyleName' => 'My Style 2'));
$content = array(
    'text' => 'Style 3',
);
$xlsx->addCell($content, 'A5', array('cellStyleName' => 'MyStyle1'));
$content = array(
    'text' => 'Good',
);
$xlsx->addCell($content, 'A7', array('cellStyleName' => 'Good'));

$xlsx->saveXlsx(__DIR__ . '/example_createCellStyle_3');