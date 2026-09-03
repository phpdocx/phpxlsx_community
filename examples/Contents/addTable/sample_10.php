<?php
// add a table applying content types in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// add new contents
$cellStyles = array(
    'type' => 'currency',
    'typeOptions' => array(
        'formatCode' => '[$$-409]#,##0.00', // $
    ),
);
$contents = array(
    array(
        array(
            'text' => 10,
            'bold' => true,
            'cellStyles' => $cellStyles,
        ),
    ),
    array(
        array(
            'text' => 20,
            'bold' => true,
            'cellStyles' => $cellStyles,
        ),
    ),
    array(
        array(
            'text' => 30,
            'bold' => true,
            'cellStyles' => $cellStyles,
        ),
    ),
    array(
        array(
            'text' => 40,
            'bold' => true,
            'cellStyles' => $cellStyles,
        ),
    ),
);

$tableStyles = array(
    'totalRow' => true,
);

$options = array(
    'columnNames' => array(
        array(
            'text' => 'Values',
            'bold' => true,
        ),
    ),
    'columnTotals' => array(
        array(
            'type' => 'function',
            'value' => 'sum',
            'contentStyles' => array(
                'bold' => true,
            ),
            'cellStyles' => array(
                'backgroundColor' => 'FFFF00',
                'type' => 'currency',
                'typeOptions' => array(
                    'formatCode' => '[$$-409]#,##0.00', // $
                ),
            ),
        ),
    ),
);
$xlsx->addTable($contents, 'B3', $tableStyles, $options);

$xlsx->saveXlsx(__DIR__ . '/example_addTable_10');