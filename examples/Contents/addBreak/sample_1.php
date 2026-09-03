<?php
// add row and column breaks in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// add sample contents
$content = array(
    'text' => 'First content.',
);
$xlsx->addCell($content, 'A1');

$content = array(
    'text' => 'Next content.',
);
$xlsx->addCell($content, 'A5');

$content = array(
    'text' => 'Other content.',
);
$xlsx->addCell($content, 'F10');

// add breaks
$xlsx->addBreak(2, 'row');

$xlsx->addBreak(4, 'row');

$xlsx->addBreak(5, 'col');

$xlsx->saveXlsx(__DIR__ . '/example_addBreak_1');