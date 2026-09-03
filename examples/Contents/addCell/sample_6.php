<?php
// add cell contents into the same position in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$content = array(
    'text' => 'Lorem ipsum dolor sit amet',
);
$xlsx->addCell($content, 'A1');

$content = array(
    'text' => 'Sed ut perspiciatis unde omnis',
    'italic' => true,
    'underline' => 'single',
);
$xlsx->addCell($content, 'B5');

// add a new content in A1 position. By default the previous content is replaced by the new one
$content = array(
    'text' => 'At vero eos et accusamus et iusto',
    'color' => '13775F',
);
$xlsx->addCell($content, 'A1');

// add a new content in B5 position. Setting insertMode as 'ignore' the new content is not added if there's a content in the same position
$content = array(
    'text' => 'Donec porttitor ullamcorper metus a pharetra',
    'bold' => true,
);
$xlsx->addCell($content, 'B5', array(), array('insertMode' => 'ignore'));

$xlsx->saveXlsx(__DIR__ . '/example_addCell_6');