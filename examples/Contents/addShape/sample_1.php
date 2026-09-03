<?php
// add shapes in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$options = array(
    'colSize' => 3,
    'rowSize' => 7,
    'fillColor' => '#FF0000',
    'outlineColor' => '#0000FF',
);
$xlsx->addShape('triangle', 'C3', $options);

$options = array(
    'colSize' => 6,
    'colOffset' => array('from' => 129600, 'to' => 137160),
    'rowSize' => 11,
    'rowOffset' => array('from' => 152280, 'to' => 159840),
    'outlineColor' => '#000000',
    'tailEnd' => 'triangle',
);
$xlsx->addShape('straightConnector1', 'N20', $options);

$xlsx->saveXlsx(__DIR__ . '/example_addShape_1');