<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>Arabic Countries Information</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<link rel="stylesheet" type="text/css" href="css/style.css" media="all" />
</head>

<body>

<div class="Paragraph">
<h2 dir="ltr">SimpleXML Example Output:</h2>
<?php

error_reporting(E_STRICT);

// set name of XML file
$file = '../src/data/ar_countries.xml';

// load XML file
$xml = simplexml_load_file($file) or die ('Unable to load XML file!');

if ($_GET['lang'] == 'arabic') {
    $lang = 'arabic';
    $dir  = 'rtl';
    echo '<a href="Info.php?lang=english">English</a>';
} else {
    $lang = 'english';
    $dir  = 'ltr';
    echo '<a href="Info.php?lang=arabic">Arabic</a>';
}

echo '<table width="98%" cellpadding="5" cellspacing="2" dir="'.$dir.'">';

echo '<tr>';
echo '<td><b><u>Country</u></b></td>';
echo '<td><b><u>Capital</u></b></td>';
echo '<td><b><u>Time Zone</u></b></td>';
echo '<td><b><u>Time</u></b></td>';
echo '<td><b><u>Currency</u></b></td>';
echo '<td><b><u>Local Domain</u></b></td>';
echo '<td><b><u>Dial Codes</u></b></td>';
echo '</tr>';

// iterate over <country> element collection
foreach ($xml as $country) {
    echo ($i++ % 2)? '<tr bgcolor="#F5F5F5">' : '<tr bgcolor="#E5E5E5">';
    
    echo '<td><a href="../src/data/flags/'.$country->name->english.'.svg" target="_blank">'.$country->name->$lang.'</a>';
    echo ' ('.$country->longname->$lang.')</td>';

    $lat = $country->capital->latitude;
    $lon = $country->capital->longitude;

    echo '<td><a href="http://maps.google.com/maps?ll='.$lat.','.$lon.'&t=h&z=10" target="_blank">'.$country->capital->$lang.'</a></td>';

    $timezone = $country->timezone;
    if ($country->summertime['used'] == 'true') {
        $start = strtotime($country->summertime->start);
        $end   = strtotime($country->summertime->end);
        if (time() > $start && time() < $end) {
            $timezone = $timezone + 1;
            $timezone = '+' . $timezone;
        }
    }
    
    // convert current time to GMT based on time zone offset
    $gmtime = time() - (int)substr(date('O'), 0, 3)*60*60; 
    
    echo '<td>'.$timezone.' GMT</td>';
    echo '<td>'.date('G:i', $gmtime+$timezone*3600).'</td>';
    echo '<td><a href="http://www.xe.com/ucc/convert.cgi?Amount=1&From=USD&To='.$country->currency->iso.'" target="_blank">'.$country->currency->$lang.'</a></td>';
    echo '<td>http://www.example.com.'.strtolower($country->iso3166->a2).'</td>';
    echo '<td>+'.$country->dialcode.'</td>';
    echo '</tr>';
}

echo '</table>';
$xml = null;
?>
</div><br />

<div class="Paragraph">
<h2>SimpleXML Example Code:</h2>
<?php
$code = <<< END
<?php
    // set name of XML file
    \$file = '../src/data/ar_countries.xml';
    
    // load XML file
    \$xml = simplexml_load_file(\$file) or die ('Unable to load XML file!');

    if (\$_GET['lang'] == 'arabic') {
        \$lang = 'arabic';
        \$dir  = 'rtl';
        echo '<a href="Info.php?lang=english">English</a>';
    } else {
        \$lang = 'english';
        \$dir  = 'ltr';
        echo '<a href="Info.php?lang=arabic">Arabic</a>';
    }
    
    echo '<table width="98%" cellpadding="5" cellspacing="2" dir="'.\$dir.'">';

    echo '<tr>';
    echo '<td><b><u>Country</u></b></td>';
    echo '<td><b><u>Capital</u></b></td>';
    echo '<td><b><u>Time Zone</u></b></td>';
    echo '<td><b><u>Time</u></b></td>';
    echo '<td><b><u>Currency</u></b></td>';
    echo '<td><b><u>Local Domain</u></b></td>';
    echo '<td><b><u>Dial Codes</u></b></td>';
    echo '</tr>';
    
    // iterate over <country> element collection
    foreach (\$xml as \$country) {
        echo (\$i++ % 2)? '<tr bgcolor="#F5F5F5">' : '<tr bgcolor="#E5E5E5">';
        
        echo '<td><a href="../src/data/flags/'.\$country->name->english.'.svg" target="_blank">'.\$country->name->\$lang.'</a>';
        echo ' ('.\$country->longname->\$lang.')</td>';

        \$lat = \$country->capital->latitude;
        \$lon = \$country->capital->longitude;

        echo '<td><a href="http://maps.google.com/maps?ll='.\$lat.','.\$lon.'&t=h&z=10" target="_blank">'.\$country->capital->\$lang.'</a></td>';

        \$timezone = \$country->timezone;
        if (\$country->summertime['used'] == 'true') {
            \$start = strtotime(\$country->summertime->start);
            \$end   = strtotime(\$country->summertime->end);
            if (time() > \$start && time() < \$end) {
                \$timezone = \$timezone + 1;
                \$timezone = '+' . \$timezone;
            }
        }
        
        // convert current time to GMT based on time zone offset
        \$gmtime = time() - (int)substr(date('O'),0,3)*60*60; 

        echo '<td>'.\$timezone.' GMT</td>';
        echo '<td>'.date('G:i', \$gmtime+\$timezone*3600).'</td>';
        echo '<td><a href="http://www.xe.com/ucc/convert.cgi?Amount=1&From=USD&To='.\$country->currency->iso.'" target="_blank">'
                  .\$country->currency->\$lang.'</a></td>';
        echo '<td>http://www.example.com.'.strtolower(\$country->iso3166->a2).'</td>';
        echo '<td>+'.\$country->dialcode.'</td>';
        echo '</tr>';
    }

    echo '</table>';
    \$xml = null;
END;

highlight_string($code);
?>
</div>
<footer><i><a href="https://github.com/khaled-alshamaa/ar-php">Ar-PHP</a>, an open-source library for website developers to process Arabic content</i></footer>
</body>
</html>
