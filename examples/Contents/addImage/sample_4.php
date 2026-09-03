<?php
// add images with hyperlink in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// external hyperlink
$options = array(
    'hyperlink' => 'https://www.phpxlsx.com',
    'colSize' => 3,
    'rowSize' => 7,
);
$xlsx->addImage(__DIR__ . '/../../files/image.png', 'A1', $options);

// add a new sheet
$xlsx->addSheet(array('name'=> 'Sheet2'));

// add an image with hyperlink referenced to #Sheet2!A1
$options = array(
    'hyperlink' => '#Sheet2!A1',
    'colSize' => 3,
    'rowSize' => 7,
);
$xlsx->addImage(__DIR__ . '/../../files/image.png', 'A10', $options);

$xlsx->saveXlsx(__DIR__ . '/example_addImage_4');