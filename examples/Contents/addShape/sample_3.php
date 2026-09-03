<?php
// add a shape with an image content in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$options = array(
    'colSize' => 3,
    'rowSize' => 7,
    'imageContent' => __DIR__ . '/../../files/image.png',
);
$xlsx->addShape('triangle', 'D4', $options);

$xlsx->saveXlsx(__DIR__ . '/example_addShape_3');