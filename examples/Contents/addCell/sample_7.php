<?php
// add cell contents auto detecting content types in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// text as general type
$xlsx->addCell('Lorem ipsum dolor sit amet', 'A1');

// text as general type with styles
$content = array(
    'text' => 'Sed ut perspiciatis unde omnis',
    'italic' => true,
    'underline' => 'single',
);
$xlsx->addCell($content, 'D1');

// number type
$xlsx->addCell(10, 'A2');

// number type with styles
$content = array(
    'text' => 500.5,
    'bold' => true,
);
$xlsx->addCell($content, 'B2');

// date type
$date = new DateTime('2011-03-18');
$xlsx->addCell($date, 'A3');

// date type with text styles
$content = array(
    'text' => new \DateTime('2011-03-18'),
    'italic' => true,
    'underline' => 'single',
);
$xlsx->addCell($content, 'C3');

// date type with text styles and cell styles
$content = array(
    'text' => new \DateTime('2011-03-18 12:28'),
    'bold' => true,
);
$cellStyles = array(
    'typeOptions' => array(
        'formatCode' => 'd\-m\-yy\ h:mm;@',
    ),
);
$xlsx->addCell($content, 'E3', $cellStyles);

// boolean type
$xlsx->addCell(true, 'A4');

// boolean type with styles
$content = array(
    'text' => true,
    'bold' => true,
);
$xlsx->addCell($content, 'B4');

// percentage type
$xlsx->addCell('10%', 'A5');

// percentage type with styles
$content = array(
    'text' => '80%',
    'bold' => true,
);
$xlsx->addCell($content, 'B5');

// function
$xlsx->addCell('=SUM(A2:B2)', 'A6');

// function setting number type applying text styles and cell styles. The recommended method to add functions is addFunction
$content = array(
    'text' => '=SUM(A2:B2)',
    'bold' => true,
    'font' => 'Arial',
);
$cellStyles = array(
    'backgroundColor' => 'FFFF00',
    'type' => 'number',
);
$xlsx->addCell($content, 'B6', $cellStyles);

$xlsx->saveXlsx(__DIR__ . '/example_addCell_7');