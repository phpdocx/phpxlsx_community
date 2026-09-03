<?php
// add a shape with a text content in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$options = array(
    'colSize' => 3,
    'rowSize' => 7,
    'rotation' => 44,
    'textContents' => array(
        'text' => 'Lorem ipsum dolor sit amet',
        'bold' => true,
        'color' => '#FFFFFF'
    ),
    'textStyles' => array(
        'align' => 'center',
        'verticalAlign' => 'middle',
    ),
);
$xlsx->addShape('triangle', 'D4', $options);

$xlsx->saveXlsx(__DIR__ . '/example_addShape_2');