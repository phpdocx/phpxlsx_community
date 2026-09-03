<?php
// add a remote image in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$options = array(
    'colSize' => 16,
    'rowSize' => 11,
);
$xlsx->addImage('https://www.phpdocx.com/img/logo_badge.png', 'D6', $options);

$xlsx->saveXlsx(__DIR__ . '/example_addImage_2');