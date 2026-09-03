<?php
// add comments in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// if no author is set, assign the first author as the default one
$xlsx->addCell('Cell B2', 'B2');
$xlsx->addComment('New comment', 'B2');

// add a comment with an author
$xlsx->addCommentAuthor('myauthor');
$xlsx->addCell('Cell D2', 'D2');
$xlsx->addComment('My comment', 'D2', array('author' => 'myauthor'));

// add a comment with styles
$comment = array(
    'text' => 'Comment with styles',
    'bold' => true,
    'italic' => true,
    'underline' => 'single',
);
$xlsx->addCell('Cell E6', 'E6');
$xlsx->addComment($comment, 'E6', array('author' => 'phpxlsx'));

// add a comment with multiple contents and styles
$content = array(
    array(
        'text' => 'Another comment',
        'bold' => true,
        'font' => 'Arial',
        'fontSize' => 16,
    ),
    array(
        'text' => ' with',
        'italic' => true,
    ),
    array(
        'text' => ' multiple styles',
        'underline' => 'single',
    ),
);
$xlsx->addCell('Cell G3', 'G3');
$xlsx->addComment($content, 'G3', array('author' => 'phpxlsx'));

// add a new sheet as the internal active sheet
$xlsx->addSheet(array('removeSelected' => true, 'selected' => true, 'active' => true));

// comments don't need cell contents
$xlsx->addComment('Comment in Sheet2.', 'B2');

$content = array(
    'text' => 'Lorem ipsum dolor sit amet',
);
$xlsx->addCell($content, 'A1');

$xlsx->saveXlsx(__DIR__ . '/example_addComment_1');