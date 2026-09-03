<?php
// create and apply multiple table styles in an existing XLSX

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// create the new table styles
$xlsx->createTableStyle('MyTableStyle1', array('wholeTable' => array('bold' => true, 'fontSize' => 12), 'headerRow' => array('italic' => true)));
$xlsx->createTableStyle('My Table Style 2', array('wholeTable' => array('bold' => true, 'fontSize' => 12), 'headerRow' => array('italic' => true, 'underline' => 'single', 'border' => 'dashed', 'borderBottom' => 'double', 'borderColorBottom' => 'FF0000', 'borderColorTop' => 'FF0000'), 'totalRow' => array('backgroundColor' => 'FFFF00')));

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

// contents to be added
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
    'tableStyle' => 'My Table Style 2',
    'totalRow' => true,
);
$options = array(
    'columnNames' => array(
        array(
            'text' => 'Values',
            'bold' => false,
        ),
    ),
    'columnTotals' => array(
        array(
            'type' => 'function',
            'value' => 'sum',
        ),
    ),
);
$xlsx->addTable($contents, 'F5', $tableStyles, $options);

$xlsx->saveXlsx(__DIR__ . '/example_createTableStyle_2');