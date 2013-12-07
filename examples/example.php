<?php
/**
 * Creates a simple 1-page PDF with right-aligned, word-wrapped lorem ipsum text
 *
 * Usage: php examples/example.php <output-file>
 *
 * @author Robbert Klarenbeek <robbertkl@renbeek.nl>
 * @copyright 2013 Robbert Klarenbeek
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

// Change this if you're not using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (!isset($argv[1])) {
    echo 'Usage: php ' . $argv[0] . ' <output-file>' . PHP_EOL;
    exit(1);
}

$file = $argv[1];

$units = \SimplePdf\Page::UNITS_INCH;
$pageSize = \SimplePdf\Page::SIZE_LETTER;
$pageMargin = 1.0; // inch
$fontSize = 12;
$lineSpacing = 1.5;

$longText = "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";

// Use given $pageSize and $units, instead of the default A4 / centimeter
$page = new \SimplePdf\Page($pageSize, $units);

$page->setFontSize($fontSize);
$page->setLineSpacing($lineSpacing);

$page->writeText($page->getWidth() - $pageMargin, $pageMargin, $longText, \SimplePdf\Page::TEXT_ALIGN_RIGHT, $page->getWidth () - 2 * $pageMargin);

$pdf = new \ZendPdf\PdfDocument();
$pdf->pages[] = $page;
$pdf->save($file);
