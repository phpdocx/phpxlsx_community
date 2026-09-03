<?php
// insert text boxes

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$options = array(
    'colSize' => 2,
    'rowSize' => 2,
);
$xlsx->addTextBox('Text box content', 'B2', array(), $options);

$textBoxStyles = array(
    'align' => 'right',
    'backgroundColor' => 'FF0000',
    'verticalAlign' => 'middle',
);
$options = array(
    'colSize' => 6,
    'rowSize' => 8,
);
$xlsx->addTextBox('Text box content', 'H5', $textBoxStyles, $options);

$xlsx->saveXlsx(__DIR__ . '/example_addTextBox_1');