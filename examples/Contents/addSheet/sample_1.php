<?php
// add a new sheet in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$xlsx->addSheet();

$xlsx->saveXlsx(__DIR__ . '/example_addSheet_1');