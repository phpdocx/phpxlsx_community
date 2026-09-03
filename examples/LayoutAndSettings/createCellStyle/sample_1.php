<?php
// create and apply a cell style in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// create the new cell style
$xlsx->createCellStyle('MyStyle1', array('bold' => true, 'fontSize' => 14, 'backgroundColor' => 'FFFF00'));

$content = array(
    'text' => 'Lorem ipsum dolor sit amet',
);
$xlsx->addCell($content, 'A1', array('cellStyleName' => 'MyStyle1'));

$xlsx->saveXlsx(__DIR__ . '/example_createCellStyle_1');