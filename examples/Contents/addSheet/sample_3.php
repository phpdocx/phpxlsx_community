<?php
// add a new sheet setting custom margins in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$margins = array(
    'left' => 1.1,
    'right' => 1.1,
    'bottom' => 1.1,
    'top' => 1.1,
    'footer' => 1.1,
    'header' => 1.1,
);
$xlsx->addSheet(array('pageMargins'=> $margins));

$xlsx->saveXlsx(__DIR__ . '/example_addSheet_3');