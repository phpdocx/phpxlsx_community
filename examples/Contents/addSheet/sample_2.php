<?php
// add two new sheets setting custom names in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$xlsx->addSheet(array('name'=> 'My 1'));

// select the new sheet as the active one
$xlsx->addSheet(array('name'=> 'Other', 'removeSelected' => true, 'selected' => true, 'active' => true));

// add a new sheet in the second position
$xlsx->addSheet(array('name'=> 'My 2', 'position' => 2));

// add a new sheet in the last position using a custom color
$xlsx->addSheet(array('name'=> 'My Sheet', 'color' => 'FF0000'));

$xlsx->saveXlsx(__DIR__ . '/example_addSheet_2');