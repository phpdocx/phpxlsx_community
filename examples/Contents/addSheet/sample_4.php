<?php
// set the sheet name of the default sheet and add a new sheet in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx(array('sheetName' => 'My sheet'));

$xlsx->addSheet();

$xlsx->saveXlsx(__DIR__ . '/example_addSheet_4');