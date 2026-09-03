<?php
// add a table with columns setting a formula in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

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
    'totalRow' => true,
);

// table with labels and count as row total
$options = array(
    'columnNames' => array(
        array(
            'text' => 'Values',
        ),
    ),
    'columnTotals' => array(
        array(
            'type' => 'formula',
            'value' => 'AVERAGEA([Values])',
            'contentStyles' => array(
                'bold' => true,
            ),
            'cellStyles' => array(
                'backgroundColor' => 'FFFF00',
            ),
        ),
    ),
);
$xlsx->addTable($contents, 'B3', $tableStyles, $options);

$xlsx->saveXlsx(__DIR__ . '/example_addTable_6');