<?php
// create and apply a table style in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// create the new table style
$xlsx->createTableStyle('MyTableStyle1', array('wholeTable' => array('bold' => true, 'fontSize' => 14, 'italic' => true, 'backgroundColor' => 'FFFF00')));

// add new contents
$contents = array(
    array(
        10,
    ),
    array(
        20,
    ),
    array(
        55,
    ),
    array(
        32,
    ),
);
$tableStyles = array(
    'tableStyle' => 'MyTableStyle1',
);
$options = array(
    'columnNames' => array(
        array(
            'text' => 'Values',
            'bold' => true,
        ),
    ),
);
$xlsx->addTable($contents, 'B3', $tableStyles, $options);

$xlsx->saveXlsx(__DIR__ . '/example_createTableStyle_1');