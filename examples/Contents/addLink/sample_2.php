<?php
// add bookmark link contents in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// add new sheets
$xlsx->addSheet(array('name'=> 'Sheet2'));
$xlsx->addSheet(array('name'=> 'Sheet>'));
$xlsx->addSheet(array('name'=> 'Sheet!'));

$content = array(
    'text' => 'Link to Sheet2',
);
$xlsx->addLink('#Sheet2!A1', 'A1', $content);

// if the sheet name contains protected characters or !, the sheet name must be wrapped using '
$content = array(
    'text' => 'Link to Sheet>',
);
$xlsx->addLink("#'Sheet>'!A1", 'A3', $content);

$content = array(
    'text' => 'Link to Sheet!',
);
$xlsx->addLink('#\'Sheet!\'!A1', 'A5', $content);

$xlsx->saveXlsx(__DIR__ . '/example_addLink_2');