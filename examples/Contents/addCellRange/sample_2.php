<?php
// add multiple cell contents and rich text contents in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// contents added horizontally
$contents = array(
    array(
        array('text' => 'Lorem', 'bold' => true),
        array('text' => 'ipsum dolor'),
        array('text' => 'sit,', 'italic' => true),
        array(
            array(
                'text' => 'consectetur',
                'bold' => true,
            ),
            array(
                'text' => ' adipiscing',
                'italic' => true,
            ),
            array(
                'text' => ' elit',
                'underline' => 'single',
            ),
        ),
    ),
);
$xlsx->addCellRange($contents, 'B2');

$xlsx->saveXlsx(__DIR__ . '/example_addCellRange_2');