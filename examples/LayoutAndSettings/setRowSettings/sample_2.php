<?php
// set hidden rows in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$content = array(
    'text' => 'Lorem ipsum dolor sit amet',
);
$xlsx->addCell($content, 'A2');

// hide rows. The position is set using row numbers
$xlsx->setRowSettings('1', array('hidden' => true));
$xlsx->setRowSettings('3', array('hidden' => true));
$xlsx->setRowSettings('4', array('hidden' => true));

// unhide a row
$xlsx->setRowSettings('3', array('hidden' => false));

$xlsx->saveXlsx(__DIR__ . '/example_setRowSettings_2');