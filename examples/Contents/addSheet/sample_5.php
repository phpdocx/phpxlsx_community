<?php
// add a new sheet and a hidden new sheet in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// hidden sheet
$xlsx->addSheet(array('name'=> 'My 1', 'state' => 'hidden'));
// select the new sheet as the active one
$xlsx->addSheet(array('name'=> 'Other', 'removeSelected' => true, 'selected' => true, 'active' => true));

$xlsx->saveXlsx(__DIR__ . '/example_addSheet_5');