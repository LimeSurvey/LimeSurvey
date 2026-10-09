<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
/*
* LimeSurvey
* Copyright (C) 2007-2026 The LimeSurvey Project Team
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*
*/

/**
 * General helper class for generating pdf.
 */
class pdfHelper
{
    /**
     * getPdfLanguageSettings
     *
     * Usage: getPdfLanguageSettings($language)
     *
     * @return array ('pdffont','pdffontsize','lg'=>array('a_meta_charset','a_meta_dir','a_meta_language','w_page')
     * @param string $language : language code for the PDF
     */
    public static function getPdfLanguageSettings($language)
    {
        Yii::import('application.libraries.admin.pdf', true);
        Yii::import('application.helpers.surveytranslator_helper', true);

        $pdffont = Yii::app()->getConfig('pdfdefaultfont');
        if ($pdffont == 'auto') {
            $pdffont = PDF_FONT_NAME_DATA;
        }
        $pdfcorefont = array("freesans", "dejavusans", "courier", "helvetica", "freemono", "symbol", "times", "zapfdingbats");
        if (in_array($pdffont, $pdfcorefont)) {
            $alternatepdffontfile = Yii::app()->getConfig('alternatepdffontfile');
            if (array_key_exists($language, $alternatepdffontfile)) {
                $pdffont = $alternatepdffontfile[$language]; // Actually use only core font
            }
        }
        $pdffontsize = Yii::app()->getConfig('pdffontsize');
        if ($pdffontsize == 'auto') {
            $pdffontsize = PDF_FONT_SIZE_MAIN;
        }
        $lg = array();
        $lg['a_meta_charset'] = 'UTF-8';
        if (getLanguageRTL($language)) {
            $lg['a_meta_dir'] = 'rtl';
        } else {
            $lg['a_meta_dir'] = 'ltr';
        }
        $lg['a_meta_language'] = $language;
        $lg['w_page'] = gT("Page");

        return array('pdffont' => $pdffont, 'pdffontsize' => $pdffontsize, 'lg' => $lg);
    }

    /**
     * Convert the Bootstrap grid (div.row > div.col-*) of the print answers HTML into tables.
     *
     * TCPDF does not support floats, so every grid column ends up as a full width block,
     * which leaves a lot of white space in the PDF. Each line of columns in a row becomes a
     * table row with one cell per column, sized by its column width (offsets are ignored to use
     * the whole page width). Rows with only full width columns are replaced by their columns
     * as simple blocks, so that TCPDF can still split them across pages.
     * Rows with the class question-container-printanswers are marked as non-breakable so a
     * single question never gets split across two pages.
     *
     * @param string $html The HTML rendered by the printanswers views
     * @return string The HTML to be used with TCPDF::writeHTML()
     */
    public static function convertGridToTables($html)
    {
        $html = (string) $html;
        if (strpos($html, 'row') === false) {
            return $html;
        }
        $dom = self::loadHtmlDocument($html);
        if (!$dom) {
            return $html;
        }
        $xpath = new DOMXPath($dom);
        $rows = $xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " row ")]');
        // Deepest rows first, so that the parent row still holds its columns when it gets converted
        foreach (array_reverse(iterator_to_array($rows)) as $row) {
            self::convertGridRowToTable($dom, $row);
        }
        // Leading and trailing white space in a cell would be printed as a space
        foreach ($xpath->query('//td|//th') as $cell) {
            self::trimCellContent($xpath, $cell);
        }
        return self::saveHtmlDocument($dom);
    }

    /**
     * Remove the transparent backgrounds from the styles of the print answers HTML.
     *
     * TCPDF draws a "transparent" background color as gray, and transparent is the default anyway.
     *
     * @param string $html
     * @return string
     */
    public static function removeTransparentBackgrounds($html)
    {
        return (string) preg_replace('/background(-color)?\s*:\s*transparent\s*(!important\s*)?;?/i', '', (string) $html);
    }

    /**
     * Embed the local images of the print answers HTML as data URIs, and remove the images that can't be found.
     *
     * TCPDF resolves image paths from the document root of the server, which fails when LimeSurvey is not
     * installed at the root, and an image that can't be loaded breaks the layout of the whole PDF.
     * Images on another server are left unchanged.
     *
     * @param string $html The HTML rendered by the printanswers views
     * @param string $publicDir The directory of the public files of LimeSurvey
     * @param string $baseUrl The base URL of LimeSurvey (path part, e.g. /limesurvey)
     * @param string $hostInfo The scheme and host of the current request (e.g. https://example.org)
     * @return string
     */
    public static function embedLocalImages($html, $publicDir, $baseUrl, $hostInfo = '')
    {
        $html = (string) $html;
        if (stripos($html, '<img') === false) {
            return $html;
        }
        $dom = self::loadHtmlDocument($html);
        if (!$dom) {
            return $html;
        }
        $publicDir = realpath($publicDir);
        foreach (iterator_to_array($dom->getElementsByTagName('img')) as $image) {
            $src = trim($image->getAttribute('src'));
            if (strpos($src, 'data:') === 0 || strpos($src, '@') === 0) {
                continue;
            }
            if ($hostInfo !== '' && stripos($src, $hostInfo . '/') === 0) {
                $src = substr($src, strlen($hostInfo));
            } elseif (preg_match('#^[a-z][a-z0-9+.-]*://#i', $src) || strpos($src, '//') === 0) {
                // Image on another server
                continue;
            }
            $dataUri = $publicDir ? self::getLocalImageDataUri($src, $publicDir, $baseUrl) : null;
            if ($dataUri !== null) {
                $image->setAttribute('src', $dataUri);
            } else {
                $alt = $image->getAttribute('alt');
                $image->parentNode->replaceChild($dom->createTextNode($alt), $image);
            }
        }
        return self::saveHtmlDocument($dom);
    }

    /**
     * Get the data URI of an image of the LimeSurvey public directory.
     *
     * @param string $src The path of the image, relative to the LimeSurvey base URL or to the server root
     * @param string $publicDir The real path of the public directory of LimeSurvey
     * @param string $baseUrl The base URL of LimeSurvey
     * @return string|null The data URI (or TCPDF data stream for SVG), null if the file is not an image of the public directory
     */
    private static function getLocalImageDataUri($src, $publicDir, $baseUrl)
    {
        $path = rawurldecode((string) parse_url($src, PHP_URL_PATH));
        if ($path === '') {
            return null;
        }
        $baseUrl = rtrim((string) $baseUrl, '/');
        $candidates = [];
        if ($baseUrl !== '' && strpos($path, $baseUrl . '/') === 0) {
            $candidates[] = substr($path, strlen($baseUrl));
        }
        $candidates[] = $path;
        foreach ($candidates as $candidate) {
            $file = realpath($publicDir . DIRECTORY_SEPARATOR . ltrim($candidate, '/'));
            // Only image files inside the public directory
            if (!$file || strpos($file, $publicDir . DIRECTORY_SEPARATOR) !== 0 || !is_file($file)) {
                continue;
            }
            $imageInfo = @getimagesize($file);
            // Since PHP 8.5, getimagesize() recognizes SVG images too
            $isSvg = ($imageInfo && $imageInfo['mime'] === 'image/svg+xml')
                || strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'svg';
            if ($isSvg) {
                $content = (string) file_get_contents($file);
                if (preg_match('/<svg[\s>]/i', $content)) {
                    // TCPDF reads SVG images from an "@" data stream, not from a data URI
                    return '@' . base64_encode($content);
                }
                continue;
            }
            if ($imageInfo) {
                return 'data:' . $imageInfo['mime'] . ';base64,' . base64_encode((string) file_get_contents($file));
            }
        }
        return null;
    }

    /**
     * Load an HTML document (UTF-8) into a DOMDocument, without reporting the HTML errors.
     *
     * @param string $html
     * @return DOMDocument|null Null if the HTML can't be loaded
     */
    private static function loadHtmlDocument($html)
    {
        $dom = new DOMDocument();
        $previousUseErrors = libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previousUseErrors);
        return $loaded ? $dom : null;
    }

    /**
     * Get the HTML of a document loaded with loadHtmlDocument().
     *
     * @param DOMDocument $dom
     * @return string
     */
    private static function saveHtmlDocument(DOMDocument $dom)
    {
        return str_replace('<?xml encoding="UTF-8">', '', (string) $dom->saveHTML());
    }

    /**
     * Replace a grid row by a table (or by the content of its columns), if all its children are grid columns.
     *
     * @param DOMDocument $dom
     * @param DOMElement $row The div.row element
     * @return void
     */
    private static function convertGridRowToTable(DOMDocument $dom, DOMElement $row)
    {
        $columns = [];
        foreach ($row->childNodes as $child) {
            if ($child instanceof DOMElement) {
                if (!self::isGridColumn($child->getAttribute('class'))) {
                    // Not a grid column: Leave the row as is
                    return;
                }
                $columns[] = ['element' => $child, 'size' => self::getGridColumnSize($child->getAttribute('class'))];
            } elseif ($child instanceof DOMText && trim($child->textContent) !== '') {
                return;
            }
        }
        if (empty($columns)) {
            return;
        }
        // Split the columns into lines, the same way the grid wraps them
        $lines = [];
        $lineSize = 0;
        foreach ($columns as $column) {
            $size = (int) $column['size'];
            if (empty($lines) || $lineSize + $size > 12) {
                $lines[] = [];
                $lineSize = 0;
            }
            $lines[count($lines) - 1][] = $column;
            $lineSize += $size;
        }
        $isQuestion = strpos(' ' . $row->getAttribute('class') . ' ', ' question-container-printanswers ') !== false;
        if (!$isQuestion && count($lines) == count($columns)) {
            // Only full width columns: Keep them as simple blocks, so that TCPDF can split them across pages
            foreach ($columns as $column) {
                $column['element']->removeAttribute('class');
                $row->parentNode->insertBefore($column['element'], $row);
            }
            $row->parentNode->removeChild($row);
            return;
        }
        if (count($lines) == 1 && !$isQuestion) {
            $table = self::createGridLineTable($dom, $lines[0]);
        } else {
            // One line per table row: Lines with several columns get their own table, since TCPDF needs the
            // same number of cells in each row
            $table = self::createTable($dom);
            if ($isQuestion) {
                $table->setAttribute('class', 'question-container-printanswers');
                $table->setAttribute('nobr', 'true');
                // Some space between the questions
                $spacer = $dom->createElement('td');
                $spacer->setAttribute('height', '8');
                $table->appendChild($dom->createElement('tr'))->appendChild($spacer);
            }
            foreach ($lines as $line) {
                $td = $dom->createElement('td');
                $td->setAttribute('width', '100%');
                if (count($line) == 1) {
                    self::moveColumnContent($line[0]['element'], $td);
                } else {
                    $td->appendChild(self::createGridLineTable($dom, $line));
                }
                $table->appendChild($dom->createElement('tr'))->appendChild($td);
            }
        }
        $row->parentNode->replaceChild($table, $row);
    }

    /**
     * Create an empty full width table.
     *
     * @param DOMDocument $dom
     * @return DOMElement
     */
    private static function createTable(DOMDocument $dom)
    {
        $table = $dom->createElement('table');
        $table->setAttribute('width', '100%');
        $table->setAttribute('cellspacing', '0');
        $table->setAttribute('cellpadding', '2');
        return $table;
    }

    /**
     * Create a one-row table with one cell per grid column.
     *
     * @param DOMDocument $dom
     * @param array $columns The columns, as ['element' => DOMElement, 'size' => int|null]
     * @return DOMElement
     */
    private static function createGridLineTable(DOMDocument $dom, array $columns)
    {
        // Columns without a fixed size share the remaining space
        $usedSpace = 0;
        $autoColumns = 0;
        foreach ($columns as $column) {
            $usedSpace += (int) $column['size'];
            $autoColumns += $column['size'] === null ? 1 : 0;
        }
        $autoSize = $autoColumns ? max(12 - $usedSpace, $autoColumns) / $autoColumns : 0;
        $totalSpace = max(12, $usedSpace + $autoSize * $autoColumns);

        $table = self::createTable($dom);
        $tr = $table->appendChild($dom->createElement('tr'));
        foreach ($columns as $column) {
            $td = $dom->createElement('td');
            $td->setAttribute('width', round(($column['size'] ?? $autoSize) * 100 / $totalSpace, 2) . '%');
            $columnClass = ' ' . $column['element']->getAttribute('class') . ' ';
            if (preg_match('/ text-(end|right) /', $columnClass)) {
                $td->setAttribute('align', 'right');
            } elseif (strpos($columnClass, ' text-center ') !== false) {
                $td->setAttribute('align', 'center');
            }
            self::moveColumnContent($column['element'], $td);
            $tr->appendChild($td);
        }
        return $table;
    }

    /**
     * Move the content of a grid column into a table cell.
     *
     * @param DOMElement $column
     * @param DOMElement $cell
     * @return void
     */
    private static function moveColumnContent(DOMElement $column, DOMElement $cell)
    {
        while ($column->firstChild) {
            $cell->appendChild($column->firstChild);
        }
    }

    /**
     * Remove the white space at the start and at the end of the text of a table cell.
     *
     * @param DOMXPath $xpath
     * @param DOMElement $cell
     * @return void
     */
    private static function trimCellContent(DOMXPath $xpath, DOMElement $cell)
    {
        $textNodes = iterator_to_array($xpath->query('.//text()', $cell));
        foreach ($textNodes as $textNode) {
            $textNode->nodeValue = ltrim($textNode->nodeValue);
            if ($textNode->nodeValue !== '') {
                break;
            }
        }
        foreach (array_reverse($textNodes) as $textNode) {
            $textNode->nodeValue = rtrim($textNode->nodeValue);
            if ($textNode->nodeValue !== '') {
                break;
            }
        }
    }

    /**
     * Check if an element is a grid column, from its classes.
     *
     * @param string $class The class attribute of the element
     * @return bool
     */
    private static function isGridColumn($class)
    {
        return (bool) preg_match('/(^|\s)col(-(xs|sm|md|lg|xl|xxl))?(-(\d+|auto))?(\s|$)/', (string) $class);
    }

    /**
     * Get the size of a grid column on a large screen, from its classes.
     *
     * @param string $class The class attribute of the element
     * @return int|null Number of grid columns (out of 12), null if the column has no fixed size
     */
    private static function getGridColumnSize($class)
    {
        // Breakpoints that apply on a large screen, from the smallest to the largest
        $breakpoints = ['', 'xs', 'sm', 'md', 'lg'];
        $sizes = [];
        foreach (preg_split('/\s+/', trim((string) $class)) as $className) {
            if (preg_match('/^col(?:-(xs|sm|md|lg))?-(\d+)$/', $className, $matches)) {
                $sizes[$matches[1]] = (int) $matches[2];
            }
        }
        $size = null;
        foreach ($breakpoints as $breakpoint) {
            $size = $sizes[$breakpoint] ?? $size;
        }
        return $size;
    }
}
