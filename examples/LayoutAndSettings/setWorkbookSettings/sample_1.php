<?php
// change tabSelected and activeCell options in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// add new sheets
$xlsx->addSheet(array('name'=> 'My 1'));
$xlsx->addSheet(array('name'=> 'Other', 'removeSelected' => true, 'selected' => true, 'active' => true));

// change sheet settings
$xlsx->setSheetSettings(array('tabSelected' => false));

$xlsx->setActiveSheet(array('position' => 1));
$xlsx->setSheetSettings(array('tabSelected' => true));

// set the second sheet as the active tab in the workbook
$xlsx->setWorkbookSettings(array('activeTab' => 1));

$xlsx->saveXlsx(__DIR__ . '/example_setWorkbookSettings_1');