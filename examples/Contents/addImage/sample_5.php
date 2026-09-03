<?php
// add an image resource in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$imageResource = imagecreatefromjpeg(__DIR__ . '/../../files/image.jpg');
$options = array(
    'colSize' => 3,
    'rowSize' => 7,
);
$xlsx->addImage($imageResource, 'A1', $options);

$xlsx->saveXlsx(__DIR__ . '/example_addImage_5');