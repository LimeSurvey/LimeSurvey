<?php

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
*/

/**
*
*  Generate a chart for a question
*  @param int $iQuestionID      ID of the question
*  @param int $iSurveyID        ID of the survey
*  @param int|bool|null $type   Type of the chart to be created, as in the statistics_graphtype question attribute:
*                               0 (or null/false) bar chart, 1 (or true) pie chart, 2 radar chart, 3 line chart,
*                               4 polar area chart and 5 doughnut chart. Polar area and doughnut charts are drawn as pie charts.
*  @param array $lbl            An array containing the labels for the chart items
*  @param mixed $gdata          An array containing the percentages for the chart items
*  @param mixed $grawdata       An array containing the raw count for the chart items
*  @param pCache $cache          An object containing [Hashkey] and [CacheFolder]
*  @param mixed $sLanguageCode  Language Code
*  @param string $sQuestionType The question type
*  @return                false|string
*/
function createChart($iQuestionID, $iSurveyID, $type, $lbl, $gdata, $grawdata, $cache, $sLanguageCode, $sQuestionType)
{
    /* This is a lazy solution to bug #6389. A better solution would be to find out how
    the "T" gets passed to this function from the statistics.js file in the first place! */
    if (substr($iSurveyID, 0, 1) == "T") {
        $iSurveyID = substr($iSurveyID, 1);
    }
    static $bErrorGenerate = false;

    if ($bErrorGenerate) {
        return false;
    }
    $rootdir = Yii::app()->getConfig("rootdir");
    $chartfontfile = Yii::app()->getConfig("chartfontfile");
    $chartfontsize = Yii::app()->getConfig("chartfontsize");
    $alternatechartfontfile = Yii::app()->getConfig("alternatechartfontfile");
    $cachefilename = "";

    $adminThemePath = AdminTheme::getInstance()->path;

    /* Set the fonts for the chart */
    if ($chartfontfile == 'auto') {
        // Tested with ar,be,el,fa,hu,he,is,lt,mt,sr, and en (english)
        // Not working for hi, si, zh, th, ko, ja : see $config['alternatechartfontfile'] to add some specific language font
        $chartfontfile = 'DejaVuSans.ttf';
        if (array_key_exists($sLanguageCode, $alternatechartfontfile)) {
            $neededfontfile = $alternatechartfontfile[$sLanguageCode];
            if (is_file($rootdir . "/assets/fonts/" . $neededfontfile)) {
                $chartfontfile = $neededfontfile;
            } else {
                Yii::app()->setFlashMessage(sprintf(gT('The chart font file %s was not found in the folder %s of your LimeSurvey installation. Please copy it there to generate the charts.'), $neededfontfile, 'assets/fonts'), 'error');
                $bErrorGenerate = true; // Don't do a graph again.
                return false;
            }
        }
    }
    if (count($lbl) > 72) {
        $DataSet = array(1 => array(1 => 1));
        if ($cache->IsInCache("graph" . $iSurveyID . $sLanguageCode . $iQuestionID, $DataSet) && Yii::app()->getConfig('debug') < 2) {
            $cachefilename = basename((string) $cache->GetFileFromCache("graph" . $iSurveyID . $sLanguageCode . $iQuestionID, $DataSet));
        } else {
            $graph = new pChart(690, 200);
            $graph->loadColorPalette($adminThemePath . DIRECTORY_SEPARATOR . 'images/limesurvey.pal');
            $graph->setFontProperties($rootdir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $chartfontfile, $chartfontsize);
            $graph->setFontProperties($rootdir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $chartfontfile, $chartfontsize);
            $graph->drawTitle(0, 0, gT('Sorry, but this question has too many answer options to be shown properly in a graph.', 'unescaped'), 30, 30, 30, 690, 200);
            $cache->WriteToCache("graph" . $iSurveyID . $sLanguageCode . $iQuestionID, $DataSet, $graph);
            $cachefilename = basename((string) $cache->GetFileFromCache("graph" . $iSurveyID . $sLanguageCode . $iQuestionID, $DataSet));
            unset($graph);
        }
        return  $cachefilename;
    }
    if (array_sum($gdata) == 0) {
        $DataSet = array(1 => array(1 => 1));
        if ($cache->IsInCache("graph" . $iSurveyID . $sLanguageCode . $iQuestionID, $DataSet) && Yii::app()->getConfig('debug') < 2) {
            $cachefilename = basename((string) $cache->GetFileFromCache("graph" . $iSurveyID . $sLanguageCode . $iQuestionID, $DataSet));
        } else {
            $graph = new pChart(690, 200);
            $graph->loadColorPalette($adminThemePath . DIRECTORY_SEPARATOR . 'images/limesurvey.pal');
            $graph->setFontProperties($rootdir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $chartfontfile, $chartfontsize);
            $graph->setFontProperties($rootdir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $chartfontfile, $chartfontsize);
            $graph->drawTitle(0, 0, gT('Sorry, but this question has no responses yet so a graph cannot be shown.', 'unescaped'), 30, 30, 30, 690, 200);
            $cache->WriteToCache("graph" . $iSurveyID . $sLanguageCode . $iQuestionID, $DataSet, $graph);
            $cachefilename = basename((string) $cache->GetFileFromCache("graph" . $iSurveyID . $sLanguageCode . $iQuestionID, $DataSet));
            unset($graph);
        }
        return  $cachefilename;
    }

    if (array_sum($gdata) > 0) {
        //Make sure that the percentages add up to more than 0
        $i = 0;
        foreach ($gdata as $data) {
            if ($data != 0) {
                $i++;
            }
        }

        /* Totatllines is the number of entries to show in the key and we need to reduce the font
        and increase the size of the chart if there are lots of them (ie more than 15) */
        $totallines = $i;
        if ($totallines > 15) {
            $gheight = 320 + (6.7 * ($totallines - 15));
        } else {
            $gheight = 320;
        }

        $iChartType = (int) $type;
        if ($iChartType == 2 || $iChartType == 3) {
            // Radar and line chart: One serie with one point per answer option
            $lblout = array();
            if ($sLanguageCode == 'ar') {
                $Arabic = new \ArPHP\I18N\Arabic('Glyphs');
            }
            foreach ($lbl as $kkey => $kval) {
                if (getLanguageRTL($sLanguageCode)) {
                    $sLabel = ellipsize(html_entity_decode($kkey . ' )' . $kval . '(', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'), 30, 1, '...');
                    if ($sLanguageCode == 'ar') {
                        $lblout[] = $Arabic->utf8Glyphs($sLabel);
                    } else {
                        $lblout[] = UTF8Strrev($sLabel);
                    }
                } else {
                    $lblout[] = ellipsize(html_entity_decode($kkey . ' (' . $kval . ')', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'), 30, 1, '...');
                }
            }

            $DataSet = new pData();
            $DataSet->AddPoint(array_values($grawdata), 'Serie1');
            $DataSet->AddPoint($lblout, 'Serie2');
            $DataSet->AddSerie('Serie1');
            $DataSet->SetSerieName(gT('Count', 'unescaped'), 'Serie1');
            $DataSet->SetAbsciseLabelSerie('Serie2');

            // Line and radar chart of the same data must not share the cached image
            $sCacheID = "graph" . $iSurveyID . $sLanguageCode . $iQuestionID . "_" . $iChartType;
            if ($cache->IsInCache($sCacheID, $DataSet->GetData()) && Yii::app()->getConfig('debug') < 2) {
                $cachefilename = basename((string) $cache->GetFileFromCache($sCacheID, $DataSet->GetData()));
            } else {
                $sFontFile = $rootdir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $chartfontfile;
                // The labels are drawn around the radar or below the line chart, so the chart needs room for the longest one
                $iMaxLabelWidth = 0;
                foreach ($lblout as $sLabel) {
                    $aBox = imagettfbbox($chartfontsize, 0, $sFontFile, $sLabel);
                    $iMaxLabelWidth = max($iMaxLabelWidth, $aBox[2] - $aBox[6]);
                }
                if ($iChartType == 2) {
                    $iWidth = 380 + 2 * ($iMaxLabelWidth + 20);
                    $iHeight = 440;
                } else {
                    $iWidth = 690;
                    $iHeight = 300 + $iMaxLabelWidth;
                }
                $graph = new pChart($iWidth, $iHeight);
                $graph->drawFilledRectangle(0, 0, $iWidth, $iHeight, 254, 254, 254, false);
                $graph->loadColorPalette($adminThemePath . DIRECTORY_SEPARATOR . 'images/limesurvey.pal');
                $graph->setFontProperties($sFontFile, $chartfontsize);
                $graph->drawFilledRoundedRectangle(7, 7, $iWidth - 7, $iHeight - 7, 5, 254, 255, 254);
                $graph->drawRoundedRectangle(5, 5, $iWidth - 5, $iHeight - 5, 5, 230, 230, 230);
                if ($iChartType == 2) {
                    // Use 1, 2 or 5 times a power of ten as distance between the rings, so that there are at most 10 rings
                    $iMaxValue = max(1, max($grawdata));
                    $iStep = 1;
                    $aStepFactors = array(2, 2.5, 2);
                    for ($i = 0; $iMaxValue > 10 * $iStep; $i++) {
                        $iStep = (int) round($iStep * $aStepFactors[$i % 3]);
                    }
                    $iScaleMax = (int) ceil($iMaxValue / $iStep) * $iStep;
                    $graph->setGraphArea($iMaxLabelWidth + 30, 30, $iWidth - $iMaxLabelWidth - 30, $iHeight - 30);
                    $graph->drawRadarAxis($DataSet->GetData(), $DataSet->GetDataDescription(), true, 10, 60, 60, 60, 200, 200, 200, $iScaleMax, $iStep);
                    $graph->drawFilledRadar($DataSet->GetData(), $DataSet->GetDataDescription(), 50, 10, $iScaleMax);
                } else {
                    $graph->setGraphArea(50, 30, $iWidth - 30, $iHeight - $iMaxLabelWidth - 30);
                    $graph->drawGraphArea(254, 254, 254, true);
                    $graph->drawScale($DataSet->GetData(), $DataSet->GetDataDescription(), SCALE_START0, 150, 150, 150, true, 90, 0, true);
                    $graph->drawGrid(4, true, 230, 230, 230, 50);
                    $graph->drawLineGraph($DataSet->GetData(), $DataSet->GetDataDescription());
                    $graph->drawPlotGraph($DataSet->GetData(), $DataSet->GetDataDescription(), 4, 2, 255, 255, 255);
                }

                $cache->WriteToCache($sCacheID, $DataSet->GetData(), $graph);
                $cachefilename = basename((string) $cache->GetFileFromCache($sCacheID, $DataSet->GetData()));
                unset($graph);
            }
        } elseif (!in_array($iChartType, array(1, 4, 5))) {
            // Bar chart
            $DataSet = new pData();
            $counter = 0;
            $maxyvalue = 0;
            foreach ($grawdata as $datapoint) {
                $DataSet->AddPoint(array($datapoint), 'Serie' . $counter);
                $DataSet->AddSerie("Serie" . $counter);

                $counter++;
                if ($datapoint > $maxyvalue) {
                    $maxyvalue = $datapoint;
                }
            }

            $lblout = array();
            if ($sLanguageCode == 'ar') {
                $Arabic = new \ArPHP\I18N\Arabic('Glyphs');

                foreach ($lbl as $kkey => $kval) {
                    if (preg_match("^[A-Za-z]^", $kkey)) {
                        //auto detect if english
                        $lblout[] = $kkey . ' (' . $kval . ')';
                    } else {
                        $lblout[] = $Arabic->utf8Glyphs($kkey . ' )' . $kval . '(');
                    }
                }
            } elseif (getLanguageRTL($sLanguageCode)) {
                foreach ($lbl as $kkey => $kval) {
                    $lblout[] = UTF8Strrev($kkey . ' )' . $kval . '(');
                }
            } else {
                foreach ($lbl as $kkey => $kval) {
                    $lblout[] = $kkey . ' (' . $kval . ')';
                }
            }

            $counter = 0;
            foreach ($lblout as $sLabelName) {
                $DataSet->SetSerieName(html_entity_decode($sLabelName, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8'), "Serie" . $counter);
                $counter++;
            }

            // The labels are only stored as serie names, which the cache hash ignores
            $sCacheID = "graph" . $iSurveyID . $sLanguageCode . $iQuestionID . "_" . md5(implode("\n", $lblout));
            if ($cache->IsInCache($sCacheID, $DataSet->GetData()) && Yii::app()->getConfig('debug') < 2) {
                $cachefilename = basename((string) $cache->GetFileFromCache($sCacheID, $DataSet->GetData()));
            } else {
                $graph = new pChart(1, 1);
                $graph->setFontProperties($rootdir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $chartfontfile, $chartfontsize);
                $legendsize = $graph->getLegendBoxSize($DataSet->GetDataDescription());

                if ($legendsize[1] < 320) {
                    $gheight = 420;
                } else {
                    $gheight = $legendsize[1] + 100;
                }
                $graph = new pChart(690 + $legendsize[0], $gheight);
                $graph->drawFilledRectangle(0, 0, 690 + $legendsize[0], $gheight, 254, 254, 254, false);
                $graph->loadColorPalette($adminThemePath . DIRECTORY_SEPARATOR . 'images/limesurvey.pal');
                $graph->setFontProperties($rootdir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $chartfontfile, $chartfontsize);
                $graph->setGraphArea(50, 30, 500, $gheight - 60);
                $graph->drawFilledRoundedRectangle(7, 7, 523 + $legendsize[0], $gheight - 7, 5, 254, 255, 254);
                $graph->drawRoundedRectangle(5, 5, 525 + $legendsize[0], $gheight - 5, 5, 230, 230, 230);
                $graph->drawGraphArea(254, 254, 254, true);
                $graph->drawScale($DataSet->GetData(), $DataSet->GetDataDescription(), SCALE_START0, 150, 150, 150, true, 90, 0, true, 5, false);
                $graph->drawGrid(4, true, 230, 230, 230, 50);
                // Draw the 0 line
                $graph->setFontProperties($rootdir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $chartfontfile, $chartfontsize);
                $graph->drawTreshold(0, 143, 55, 72, true, true);

                // Draw the bar graph
                $graph->drawBarGraph($DataSet->GetData(), $DataSet->GetDataDescription(), false);
                //$Test->setLabel($DataSet->GetData(),$DataSet->GetDataDescription(),"Serie4","1","Important point!");
                // Finish the graph
                $graph->setFontProperties($rootdir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $chartfontfile, $chartfontsize);
                $graph->drawLegend(510, 30, $DataSet->GetDataDescription(), 250, 250, 250);

                $cache->WriteToCache($sCacheID, $DataSet->GetData(), $graph);
                $cachefilename = basename((string) $cache->GetFileFromCache($sCacheID, $DataSet->GetData()));
                unset($graph);
            }
        }    //end if (bar chart)

        //Pie Chart
        else {
            // this block is to remove the items with value == 0
            // and an inelegant way to remove comments from List with Comments questions
            $i = 0;
            $j = 0;
            $labelTmp = array();
            while (isset($gdata[$i])) {
                $aHelperArray = array_keys($lbl);
                if ($gdata[$i] == 0 || ($sQuestionType == Question::QT_O_LIST_WITH_COMMENT && substr($aHelperArray[$i], 0, strlen(gT("Comments"))) == gT("Comments"))) {
                    array_splice($gdata, $i, 1);
                } else {
                    $i++;
                    $labelTmp = $labelTmp + array_slice($lbl, $j, 1, true); // Preserve numeric keys for the labels!
                }
                $j++;
            }
            $lbl = $labelTmp;

            if ($sLanguageCode == 'ar') {
                $Arabic = new \ArPHP\I18N\Arabic('Glyphs');
                foreach ($lbl as $kkey => $kval) {
                    if (preg_match("^[A-Za-z]^", $kkey)) {
                        //auto detect if english
                        $lblout[] = $kkey . ' (' . $kval . ')';
                    } else {
                        $lblout[] = $Arabic->utf8Glyphs($kkey . ' )' . $kval . '(');
                    }
                }
            } elseif (getLanguageRTL($sLanguageCode)) {
                foreach ($lbl as $kkey => $kval) {
                    $lblout[] = UTF8Strrev(html_entity_decode($kkey, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8') . ' )' . $kval . '(');
                }
            } else {
                foreach ($lbl as $kkey => $kval) {
                    $lblout[] = html_entity_decode($kkey, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8') . ' (' . $kval . ')';
                }
            }


            //create 3D pie chart
            $DataSet = new pData();
            $DataSet->AddPoint($gdata, "Serie1");
            $DataSet->AddPoint($lblout, "Serie2");
            $DataSet->AddAllSeries();
            $DataSet->SetAbsciseLabelSerie("Serie2");

            if ($cache->IsInCache("graph" . $iSurveyID . $sLanguageCode . $iQuestionID, $DataSet->GetData()) && Yii::app()->getConfig('debug') < 2) {
                $cachefilename = basename((string) $cache->GetFileFromCache("graph" . $iSurveyID . $sLanguageCode . $iQuestionID, $DataSet->GetData()));
            } else {
                $gheight = ceil($gheight);
                $graph = new pChart(690, $gheight);
                $graph->drawFilledRectangle(0, 0, 690, $gheight, 254, 254, 254, false);
                $graph->loadColorPalette($adminThemePath . DIRECTORY_SEPARATOR . 'images/limesurvey.pal');
                $graph->drawFilledRoundedRectangle(7, 7, 687, $gheight - 3, 5, 254, 255, 254);
                $graph->drawRoundedRectangle(5, 5, 689, $gheight - 1, 5, 230, 230, 230);

                // Draw the pie chart
                $graph->setFontProperties($rootdir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $chartfontfile, $chartfontsize);
                $graph->drawPieGraph($DataSet->GetData(), $DataSet->GetDataDescription(), 225, round($gheight / 2), 170, PIE_PERCENTAGE, true, 50, 20, 5);
                $graph->setFontProperties($rootdir . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . $chartfontfile, $chartfontsize);
                $graph->drawPieLegend(430, 12, $DataSet->GetData(), $DataSet->GetDataDescription(), 250, 250, 250);
                $cache->WriteToCache("graph" . $iSurveyID . $sLanguageCode . $iQuestionID, $DataSet->GetData(), $graph);
                $cachefilename = basename((string) $cache->GetFileFromCache("graph" . $iSurveyID . $sLanguageCode . $iQuestionID, $DataSet->GetData()));
                unset($graph);
            }
        }    //end else -> pie charts
    }

    return $cachefilename;
}
