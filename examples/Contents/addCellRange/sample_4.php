<?php
// add multiple cell contents auto detecting content types in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// contents without styles
$contents = array(
    array('Lorem', 20, '10%', TRUE),
);
$xlsx->addCellRange($contents, 'A1');

// contents with styles
$contents = array(
    array(
        array('text' => 'Lorem'),
        array('text' => 20, 'bold' => true),
        array('text' => '21%', 'italic' => true),
        array('text' => FALSE),
    ),
);
$xlsx->addCellRange($contents, 'A2');

// add new contents and functions
$contents = array(
    array(
        array(
            'text' => 'Content', 'cellStyles' => array('backgroundColor' => '8A9977'),
        ),
    ),
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
    array(
        '=SUM(F6:F9)',
        array(
            'text' => '=SUM(G6:G9)',
            'cellStyles' => array(
                'borderTop' => 'dashed',
                'borderColorTop' => '3D643D',
                'backgroundColor' => '98FB98',
            ),
        )
    )
);
// global styles for the cell range
$cellStyles = array(
    'backgroundColor' => 'FFFFBF',
);
$xlsx->addCellRange($contents, 'F5', $cellStyles);

$xlsx->saveXlsx(__DIR__ . '/example_addCellRange_4');