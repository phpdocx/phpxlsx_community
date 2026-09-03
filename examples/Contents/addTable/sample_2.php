<?php
// add table with columns setting cell types and styles in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// add new contents with custom styles
$cellStyles = array(
    'type' => 'general',
    'backgroundColor' => 'D9E1F2',
);
$contents = array(
    array(
        array(
            'text' => '10',
            'cellStyles' => $cellStyles,
        ),
        array(
            'text' => '50',
            'cellStyles' => $cellStyles,
        ),
        array(
            'text' => '20',
            'cellStyles' => $cellStyles,
        ),
    ),
    array(
        array(
            'text' => '20',
            'cellStyles' => $cellStyles,
        ),
        array(
            'text' => '25.5',
            'cellStyles' => $cellStyles,
        ),
        array(
            'text' => '31',
            'cellStyles' => $cellStyles,
        ),
    ),
    array(
        array(
            'text' => '55',
            'cellStyles' => $cellStyles,
        ),
        array(
            'text' => '12',
            'cellStyles' => $cellStyles,
        ),
        array(
            'text' => '22',
            'cellStyles' => $cellStyles,
        ),
    ),
    array(
        array(
            'text' => '32',
            'cellStyles' => $cellStyles,
        ),
        array(
            'text' => '36',
            'cellStyles' => $cellStyles,
        ),
        array(
            'text' => '10',
            'cellStyles' => $cellStyles,
        ),
    ),
);
$options = array(
    'columnNames' => array(
        array(
            'text' => 'Values 1',
            'bold' => true,
            'color' => 'FFFFFF',
            'cellStyles' => array(
                'backgroundColor' => '4472C4',
            ),
        ),
        array(
            'text' => 'Values 2',
            'bold' => true,
            'color' => 'FFFFFF',
            'cellStyles' => array(
                'backgroundColor' => '4472C4',
            ),
        ),
        array(
            'text' => 'Values 3',
            'bold' => true,
            'color' => 'FFFFFF',
            'cellStyles' => array(
                'backgroundColor' => '4472C4',
            ),
        ),
    ),
);
$xlsx->addTable($contents, 'B3', array(), $options);

// add new contents with table styles
$contents = array(
    array(
        10,
        50,
    ),
    array(
        20,
        25.5,
    ),
    array(
        55,
        12,
    ),
    array(
        32,
        36,
    ),
);
$tableStyles = array(
    'tableStyle' => 'TableStyleMedium2',
);
$options = array(
    'columnNames' => array(
        array(
            'text' => 'Values',
        ),
        array(
            'text' => 'Values',
        ),
    ),
);
$xlsx->addTable($contents, 'G3', $tableStyles, $options);

$xlsx->saveXlsx(__DIR__ . '/example_addTable_2');