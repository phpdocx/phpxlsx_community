<?php
// add URL link contents using rich text contents and cell styles in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

$content = array(
    array(
        'text' => 'Link to',
        'bold' => true,
    ),
    array(
        'text' => ' phpxlsx',
        'italic' => true,
    ),
);
$xlsx->addLink('https://www.phpxlsx.com', 'B2', $content);

$content = array(
    'text' => 'phpdocx',
    'bold' => true,
    'color' => '000000',
    'underline' => 'none',
);
$cellStyles = array(
    'border' => 'dashed',
    'borderBottom' => 'double',
    'borderColorBottom' => 'FF0000',
    'borderColorTop' => 'FF0000',
);
$xlsx->addLink('https://www.phpdocx.com', 'D5', $content, $cellStyles);

$xlsx->saveXlsx(__DIR__ . '/example_addLink_3');