<?php
// add cell contents applying cell types in an XLSX created from scratch

require_once __DIR__ . '/../../../Classes/Phpxlsx/Create/CreateXlsx.php';

$xlsx = new Phpxlsx\Create\CreateXlsx();

// general type is the default one
$content = array(
    'text' => 'Lorem',
);
$xlsx->addCell($content, 'A1');

// numeric contents are detected as numbers if no custom type is set
$content = array(
    'text' => 100,
);
$xlsx->addCell($content, 'A2');
$content = array(
    'text' => 10.50,
);
$cellStyles = array(
    'type' => 'general',
);
$xlsx->addCell($content, 'B2', $cellStyles);

// number content
$cellStyles = array(
    'type' => 'number',
);
$content = array(
    'text' => 100,
);
$xlsx->addCell($content, 'A3', $cellStyles);
$content = array(
    'text' => 10.50,
);
$xlsx->addCell($content, 'B3', $cellStyles);

// number content with a custom format
$content = array(
    'text' => 1200,
);
$cellStyles = array(
    'type' => 'number',
    'typeOptions' => array(
        'formatCode' => '#,##0.00',
    ),
);
$xlsx->addCell($content, 'A4', $cellStyles);

// currency content
$content = array(
    'text' => 1200,
);
$cellStyles = array(
    'type' => 'currency',
    'typeOptions' => array(
        'formatCode' => '[$$-409]#,##0.00', // $
    ),
);
$xlsx->addCell($content, 'A5', $cellStyles);

$content = array(
    'text' => 1200,
);
$cellStyles = array(
    'type' => 'currency',
    'typeOptions' => array(
        'formatCode' => '[$£-809]#,##0.00', // £
    ),
);
$xlsx->addCell($content, 'B5', $cellStyles);

$content = array(
    'text' => 1200,
);
$cellStyles = array(
    'type' => 'currency',
    'typeOptions' => array(
        'formatCode' => '#,##0.00\ &quot;€&quot;', // €
    ),
);
$xlsx->addCell($content, 'C5', $cellStyles);

// accounting content
$content = array(
    'text' => 1200,
);
$cellStyles = array(
    'type' => 'accounting',
    'typeOptions' => array(
        'formatCode' => '_-[$$-409]* #,##0.00_ ;_-[$$-409]* \-#,##0.00\ ;_-[$$-409]* &quot;-&quot;??_ ;_-@_ ', // $
    ),
);
$xlsx->addCell($content, 'A6', $cellStyles);

$content = array(
    'text' => 1200,
);
$cellStyles = array(
    'type' => 'accounting',
    'typeOptions' => array(
        'formatCode' => '_-[$£-809]* #,##0.00_-;\-[$£-809]* #,##0.00_-;_-[$£-809]* &quot;-&quot;??_-;_-@_-', // £
    ),
);
$xlsx->addCell($content, 'B6', $cellStyles);

$content = array(
    'text' => 1200,
);
$cellStyles = array(
    'type' => 'accounting',
    'typeOptions' => array(
        'formatCode' => '_-* #,##0.00\ &quot;€&quot;_-;\-* #,##0.00\ &quot;€&quot;_-;_-* &quot;-&quot;??\ &quot;€&quot;_-;_-@_-', // €
    ),
);
$xlsx->addCell($content, 'C6', $cellStyles);

// date and time content
$content = array(
    'text' => '2010-02-26',
);
$cellStyles = array(
    'type' => 'date',
);
$xlsx->addCell($content, 'A7', $cellStyles);

$content = array(
    'text' => '1980-08-08',
);
$cellStyles = array(
    'type' => 'date',
    'typeOptions' => array(
        'formatCode' => 'yyyy\-mm\-dd;@',
    ),
);
$xlsx->addCell($content, 'B7', $cellStyles);

$content = array(
    'text' => '10:30',
);
$cellStyles = array(
    'type' => 'time',
);
$xlsx->addCell($content, 'C7', $cellStyles);

$content = array(
    'text' => '12:14:31',
);
$cellStyles = array(
    'type' => 'time',
    'typeOptions' => array(
        'formatCode' => '[$-409]h:mm:ss\ AM/PM;@',
    ),
);
$xlsx->addCell($content, 'D7', $cellStyles);

$content = array(
    'text' => '1980-08-08 22:31',
);
$cellStyles = array(
    'type' => 'date',
    'typeOptions' => array(
        'formatCode' => 'd\-m\-yy\ h:mm;@',
    ),
);
$xlsx->addCell($content, 'E7', $cellStyles);

// percentage content
$content = array(
    'text' => 0.9,
);
$cellStyles = array(
    'type' => 'percentage',
);
$xlsx->addCell($content, 'A8', $cellStyles);

$content = array(
    'text' => 0.263,
);
$cellStyles = array(
    'type' => 'percentage',
    'typeOptions' => array(
        'formatCode' => '0.00%',
    ),
);
$xlsx->addCell($content, 'B8', $cellStyles);

// fraction content
$content = array(
    'text' => 1/2,
);
$cellStyles = array(
    'type' => 'fraction',
);
$xlsx->addCell($content, 'A9', $cellStyles);

$content = array(
    'text' => 2/4,
);
$cellStyles = array(
    'type' => 'fraction',
    'typeOptions' => array(
        'formatCode' => '#\ ?/4',
    ),
);
$xlsx->addCell($content, 'B9', $cellStyles);

$content = array(
    'text' => 1/3,
);
$cellStyles = array(
    'type' => 'fraction',
);
$xlsx->addCell($content, 'C9', $cellStyles);

// scientific content
$cellStyles = array(
    'type' => 'scientific',
);
$content = array(
    'text' => 150,
);
$xlsx->addCell($content, 'A10', $cellStyles);

$content = array(
    'text' => 5000,
);
$xlsx->addCell($content, 'B10', $cellStyles);

// text content
$cellStyles = array(
    'type' => 'text',
);
$content = array(
    'text' => 'Lorem',
);
$xlsx->addCell($content, 'A11', $cellStyles);
$content = array(
    'text' => 100,
);
$xlsx->addCell($content, 'B11', $cellStyles);
$content = array(
    'text' => 10.50,
);
$xlsx->addCell($content, 'C11', $cellStyles);

// special content
$content = array(
    'text' => '2802',
);
$cellStyles = array(
    'type' => 'special',
    'typeOptions' => array(
        'formatCode' => '00000',
    ),
);
$xlsx->addCell($content, 'A12', $cellStyles);

// boolean content
$cellStyles = array(
    'type' => 'boolean',
);
$content = array(
    'text' => true,
);
$xlsx->addCell($content, 'A13', $cellStyles);

$content = array(
    'text' => false,
);
$xlsx->addCell($content, 'B13', $cellStyles);
$content = array(
    'text' => 1,
);
$xlsx->addCell($content, 'C13', $cellStyles);

$content = array(
    'text' => 0,
);
$xlsx->addCell($content, 'D13', $cellStyles);

// empty content
$content = array(
);
$cellStyles = array(
    'type' => 'empty',
);
$xlsx->addCell($content, 'A14', $cellStyles);

$xlsx->saveXlsx(__DIR__ . '/example_addCell_5');