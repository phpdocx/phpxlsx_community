<?php
// add a table with filters in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// add new contents
$contents = array(
    array(
        'Apples',
        1200,
        510,
        870,
        600,
        '=SUM(C4:F4)',
    ),
    array(
        'Pears',
        250,
        630,
        410,
        140,
        '=SUM(C5:F5)',
    ),
    array(
        'Bananas',
        500,
        330,
        220,
        170,
        '=SUM(C6:F6)',
    ),
);

$tableStyles = array(
    'totalRow' => true,
);

// totals
$options = array(
    'columnNames' => array(
        array(
            'text' => 'Prod',
            'bold' => true,
            'color' => 'FFFFFF',
            'cellStyles' => array(
                'backgroundColor' => '4472C4',
            ),
        ),
        array(
            'text' => 'Q1',
            'bold' => true,
            'color' => 'FFFFFF',
            'cellStyles' => array(
                'backgroundColor' => '4472C4',
            ),
        ),
        array(
            'text' => 'Q2',
            'bold' => true,
            'color' => 'FFFFFF',
            'cellStyles' => array(
                'backgroundColor' => '4472C4',
            ),
        ),
        array(
            'text' => 'Q3',
            'bold' => true,
            'color' => 'FFFFFF',
            'cellStyles' => array(
                'backgroundColor' => '4472C4',
            ),
        ),
        array(
            'text' => 'Q4',
            'bold' => true,
            'color' => 'FFFFFF',
            'cellStyles' => array(
                'backgroundColor' => '4472C4',
            ),
        ),
        array(
            'text' => 'Year',
            'bold' => true,
            'color' => 'FFFFFF',
            'cellStyles' => array(
                'backgroundColor' => '4472C4',
            ),
        ),
    ),
    'columnTotals' => array(
        array(
            'type' => 'label',
            'value' => 'Totals',
        ),
        array(
            'type' => 'function',
            'value' => 'sum',
        ),
        array(
            'type' => 'function',
            'value' => 'sum',
        ),
        array(
            'type' => 'function',
            'value' => 'sum',
        ),
        array(
            'type' => 'function',
            'value' => 'sum',
        ),
        array(
            'type' => 'function',
            'value' => 'sum',
        ),
    ),
    'filters' => array(
        array(
            'values' => array(
                'Apples',
            ),
        ),
    ),
);
$xlsx->addTable($contents, 'B3', $tableStyles, $options);

$xlsx->saveXlsx(__DIR__ . '/example_addTable_8');