<?php
// add comment authors in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$xlsx->addCommentAuthor('phpxlsxauthor');

// add a new sheet as the internal active sheet
$xlsx->addSheet(array('removeSelected' => true, 'selected' => true, 'active' => true));

// each sheet has its own authors
$xlsx->addCommentAuthor('phpxlsxauthor');
$xlsx->addCommentAuthor('myauthor');

$xlsx->saveXlsx(__DIR__ . '/example_addCommentAuthor_1');