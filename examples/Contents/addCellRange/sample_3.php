<?php
// add multiple cell contents applying cell types and styles in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// dates added horizontally
$contents = array(
    array(
        array('text' => '2010-02-26'),
        array('text' => '1980-08-08'),
        array('text' => '2022-01-01'),
    ),
);
$cellStyles = array(
    'type' => 'date',
    'typeOptions' => array(
        'formatCode' => 'yyyy\-mm;@',
    ),
);
$xlsx->addCellRange($contents, 'C1', $cellStyles);

// text content, numbers and a function added vertically
$contents = array(
    array(
        array('text' => 'Content', 'cellStyles' => array('backgroundColor' => '8A9977')),
    ),
    array(
        array('text' => 10),
    ),
    array(
        array('text' => 20),
    ),
    array(
        array('text' => 55),
    ),
    array(
        array('text' => 32.5),
    ),
    array(
        array(
            'text' => '=SUM(F6:F9)',
            'cellStyles' => array(
                'type' => 'number',
                'borderTop' => 'dashed',
                'borderColorTop' => '3D643D',
                'backgroundColor' => '98FB98',
            ),
        ),
    )
);
// global styles for the cell range
$cellStyles = array(
    'backgroundColor' => 'FFFFBF',
);
$xlsx->addCellRange($contents, 'F5', $cellStyles);

$xlsx->saveXlsx(__DIR__ . '/example_addCellRange_3');