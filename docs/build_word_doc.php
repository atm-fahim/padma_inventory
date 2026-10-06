<?php

/**
 * Build Word-openable .doc (HTML) from docs/modules/*.md and STRUCTURE.md
 * Run: php docs/build_word_doc.php
 */

$base = dirname(__DIR__);
$out = __DIR__ . '/word/Padma_Inventory_Module_Documentation.doc';

$files = [
    'README.md',
    'STRUCTURE.md',
    'TECHNICAL_SYSTEM_DOCUMENTATION.md',
];
foreach (glob(__DIR__ . '/modules/*.md') as $m) {
    $files[] = 'modules/' . basename($m);
}

if (!is_dir(__DIR__ . '/word')) {
    mkdir(__DIR__ . '/word', 0755, true);
}

$html = <<<'HDR'
<html xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:w="urn:schemas-microsoft-com:office:word"
 xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<title>Padma Inventory — Module Documentation</title>
<!--[if gte mso 9]><xml><w:WordDocument><w:View>Print</w:View></w:WordDocument></xml><![endif]-->
<style>
body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; line-height: 1.4; margin: 2cm; }
h1 { font-size: 18pt; color: #1a5276; border-bottom: 2px solid #1a5276; padding-bottom: 6px; }
h2 { font-size: 14pt; color: #2874a6; margin-top: 18px; }
h3 { font-size: 12pt; color: #333; }
pre, code { font-family: Consolas, monospace; font-size: 9pt; background: #f4f4f4; }
table { border-collapse: collapse; width: 100%; margin: 12px 0; }
td, th { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
th { background: #eaecee; }
hr { border: none; border-top: 1px solid #ccc; margin: 24px 0; }
.page-break { page-break-before: always; }
</style>
</head>
<body>
<h1>Padma Inventory — Full Module Documentation</h1>
<p><strong>Generated:</strong> Padma Bank IT Inventory System (padma_inventory)</p>
<p><strong>Source:</strong> docs/modules/*.md, STRUCTURE.md</p>
<hr>
HDR;

foreach ($files as $rel) {
    $path = __DIR__ . '/' . str_replace(['modules/', 'README.md', 'STRUCTURE.md', 'TECHNICAL_SYSTEM_DOCUMENTATION.md'], [
        'modules' . DIRECTORY_SEPARATOR,
        'README.md',
        'STRUCTURE.md',
        'TECHNICAL_SYSTEM_DOCUMENTATION.md',
    ], $rel);

    if ($rel === 'README.md') {
        $path = __DIR__ . '/README.md';
    } elseif ($rel === 'STRUCTURE.md') {
        $path = __DIR__ . '/STRUCTURE.md';
    } elseif ($rel === 'TECHNICAL_SYSTEM_DOCUMENTATION.md') {
        $path = __DIR__ . '/TECHNICAL_SYSTEM_DOCUMENTATION.md';
    } elseif (str_starts_with($rel, 'modules/')) {
        $path = __DIR__ . '/modules/' . basename($rel);
    }

    if (!is_file($path)) {
        continue;
    }

    $md = file_get_contents($path);
    $html .= '<div class="page-break"></div>';
    $html .= mdToHtml($md);
}

$html .= '</body></html>';

file_put_contents($out, $html);
echo "Written: {$out}\n";

function mdToHtml(string $md): string
{
    $lines = preg_split('/\r\n|\r|\n/', $md);
    $out = '';
    $inCode = false;
    $inTable = false;
    $buf = [];

    $flushPara = function () use (&$buf, &$out) {
        if ($buf === []) {
            return;
        }
        $text = trim(implode(' ', $buf));
        $buf = [];
        if ($text === '') {
            return;
        }
        $out .= '<p>' . inline($text) . '</p>' . "\n";
    };

    foreach ($lines as $line) {
        if (str_starts_with($line, '```')) {
            $flushPara();
            if ($inCode) {
                $out .= '</pre>' . "\n";
                $inCode = false;
            } else {
                $out .= '<pre>' . "\n";
                $inCode = true;
            }
            continue;
        }
        if ($inCode) {
            $out .= htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . "\n";
            continue;
        }

        if (preg_match('/^#{1}\s+(.+)$/', $line, $m)) {
            $flushPara();
            if ($inTable) {
                $out .= '</table>' . "\n";
                $inTable = false;
            }
            $out .= '<h1>' . inline($m[1]) . '</h1>' . "\n";
            continue;
        }
        if (preg_match('/^#{2}\s+(.+)$/', $line, $m)) {
            $flushPara();
            if ($inTable) {
                $out .= '</table>' . "\n";
                $inTable = false;
            }
            $out .= '<h2>' . inline($m[1]) . '</h2>' . "\n";
            continue;
        }
        if (preg_match('/^#{3}\s+(.+)$/', $line, $m)) {
            $flushPara();
            $out .= '<h3>' . inline($m[1]) . '</h3>' . "\n";
            continue;
        }
        if (preg_match('/^---+\s*$/', $line)) {
            $flushPara();
            $out .= '<hr>' . "\n";
            continue;
        }

        if (str_contains($line, '|') && preg_match('/^\|/', trim($line))) {
            $flushPara();
            $cells = array_map('trim', explode('|', trim($line, '|')));
            if (preg_match('/^[\-\:\|\s]+$/', implode('', $cells))) {
                continue;
            }
            if (!$inTable) {
                $out .= '<table>' . "\n";
                $inTable = true;
                $out .= '<tr>';
                foreach ($cells as $c) {
                    $out .= '<th>' . inline($c) . '</th>';
                }
                $out .= '</tr>' . "\n";
            } else {
                $out .= '<tr>';
                foreach ($cells as $c) {
                    $out .= '<td>' . inline($c) . '</td>';
                }
                $out .= '</tr>' . "\n";
            }
            continue;
        }

        if ($inTable) {
            $out .= '</table>' . "\n";
            $inTable = false;
        }

        if (trim($line) === '') {
            $flushPara();
            continue;
        }

        if (preg_match('/^[\-\*]\s+(.+)$/', $line, $m)) {
            $flushPara();
            $out .= '<p>• ' . inline($m[1]) . '</p>' . "\n";
            continue;
        }

        $buf[] = trim($line);
    }

    $flushPara();
    if ($inCode) {
        $out .= '</pre>' . "\n";
    }
    if ($inTable) {
        $out .= '</table>' . "\n";
    }

    return $out;
}

function inline(string $s): string
{
    $s = htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    $s = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $s);
    $s = preg_replace('/`([^`]+)`/', '<code>$1</code>', $s);
    $s = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2">$1</a>', $s);

    return $s ?? $s;
}
