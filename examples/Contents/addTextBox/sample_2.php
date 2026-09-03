<?php
// insert text boxes with styles

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$textContent = array(
    array(
        'text' => 'At vero',
        'font' => 'Arial',
        'highlight' => '628A54',
    ),
    array(
        'text' => ' eos et ',
        'bold' => true,
        'italic' => true,
        'font' => 'Times New Roman',
    ),
    array(
        'text' => 'accusamus et iusto.',
        'strikethrough' => true,
        'color' => '628A54',
        'italic' => true,
    ),
);
$textBoxStyles = array(
    'backgroundColor' => 'FFFF00',
    'backgroundTransparency' => 60,
    'border' => array(
        'color' => '0000FF',
        'dashType' => 'dash',
        'transparency' => 30,
        'width' => 12700,
    ),
    'rotation' => 330,
);
$options = array(
    'colSize' => 4,
    'rowSize' => 2,
);
$xlsx->addTextBox($textContent, 'B20', $textBoxStyles, $options);

$textContent = array(
    'text' => "Text box: \nnew content.",
    'fontSize' => 36,
    'italic' => true,
);
$textBoxStyles = array(
    'align' => 'right',
    'backgroundColor' => 'none',
    'verticalAlign' => 'bottom',
);
$options = array(
    'colSize' => 6,
    'rowSize' => 8,
);
$xlsx->addTextBox($textContent, 'H5', $textBoxStyles, $options);

$xlsx->saveXlsx(__DIR__ . '/example_addTextBox_2');