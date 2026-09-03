<?php
// choose the last sheet as the active one in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$xlsx->addSheet();
$xlsx->addSheet(array('name'=> 'Other'));

// this method doesn't change the activeTab in the workbook file. It's used as internal active sheet
$xlsx->setActiveSheet(array('position' => -1));

$content = array(
    'text' => 'Lorem ipsum dolor sit amet',
);
$xlsx->addCell($content, 'A1');

$xlsx->saveXlsx(__DIR__ . '/example_setActiveSheet_3');