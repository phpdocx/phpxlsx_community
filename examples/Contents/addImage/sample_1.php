<?php
// add an image in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$options = array(
    'colSize' => 3,
    'rowSize' => 7,
);
$xlsx->addImage(__DIR__ . '/../../files/image.png', 'A1', $options);

$options = array(
    'colSize' => 6,
    'colOffset' => array('from' => 129600, 'to' => 137160),
    'rowSize' => 11,
    'rowOffset' => array('from' => 152280, 'to' => 159840),
    'rotation' => 315,
    'transparency' => 60,
);
$xlsx->addImage(__DIR__ . '/../../files/image.webp', 'AB4', $options);

$xlsx->saveXlsx(__DIR__ . '/example_addImage_1');