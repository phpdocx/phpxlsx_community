<?php
// set cell values in cell positions in an existing XLSX

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// set the cell value in B5 position
$content = array(
    'text' => 'New content',
);
$xlsx->setCellValue($content, 'B5');
// the same can be done using addCell, that allows using more options
// $xlsx->addCell($content, 'B5', array(), array('useCellStyles' => true));

$xlsx->saveXlsx(__DIR__ . '/example_setCellValue_1');