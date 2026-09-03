<?php
// add multiple cell contents in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// contents added horizontally
$contents = array(
    array(
        array('text' => 'Lorem'),
        array('text' => 'ipsum dolor', 'bold' => true),
        array('text' => 'sit', 'italic' => true),
        array('text' => 'amet'),
    ),
);
$rangeInfo = $xlsx->addCellRange($contents, 'A1');
// addCellRange returns the cell range of the contents
// var_dump($rangeInfo);

// contents added vertically
$contents = array(
    array(
        array('text' => 'Sed ut  '),
    ),
    array(
        array('text' => 'perspiciatis', 'italic' => true, 'underline' => 'single'),
    ),
    array(
        array('text' => 'unde omnis'),
    ),
);
$xlsx->addCellRange($contents, 'C3');

// contents added horizontally and vertically
$contents = array(
    array(
        array('text' => 'At'),
        array('text' => 'vero eos'),
    ),
    array(
        array('text' => 'et accusamus', 'italic' => true),
        array('text' => 'et iusto'),
    ),
);
$xlsx->addCellRange($contents, 'F5');

$xlsx->saveXlsx(__DIR__ . '/example_addCellRange_1');