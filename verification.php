<?php

// make sure you include this file only if the ImageCreate function does exist since it is an optional library
// Lets get into the session
session_start();

// header for png
Header("Content-Type: image/png");

// Create Image
$im = ImageCreate(75, 20); 
$white = ImageColorAllocate($im, 255, 255, 255);
$black = ImageColorAllocate($im, 0, 0, 0);
$red = ImageColorAllocate($im, 255, 0, 0);
$blue = ImageColorAllocate($im, 0, 0, 255);
$grey_shade = ImageColorAllocate($im, 204, 204, 204);

// Create the random numberes
srand((double)microtime()*1000000); 

$num1 = rand(1,5);
$found = false;
while ($found == false)
{
	$num2 = rand(1,100);
	if (preg_match('/^[0-9]+$/', $num2/5))
	{
		$found = true;
		break;
	}
}
$font_c_rand = rand(1,3);
if ($font_c_rand == 1)
{
	$font_color = $black;
} else if ($font_c_rand == 2) 
{
	$font_color = $red;
} else if ($font_c_rand == 3) 
{
	$font_color = $blue;
}

$font_rand = rand(1,3);
if ($font_rand == 1)
{
	$font = "fonts/verabd.ttf";
} else if ($font_rand == 2) {
	$font = "fonts/vera.ttf";
} else if ($font_rand == 3)
{
	$font = "fonts/verait.ttf";
}

$line_rand = rand(1,3);
if ($line_rand == 1)
{
	$line_color = $black;
} else if ($line_rand == 2) 
{
	$line_color = $red;
} else if ($line_rand == 3) 
{
	$line_color = $blue;
}

// Fill image, make transparent
ImageFill($im, 0, 0, $grey_shade);
//imagecolortransparent ($im, $white);
imageline($im,0,0,0,20,$line_color); 
imageline($im,74,0,74,19,$line_color); 
imageline($im,0,0,74,0,$line_color); 
imageline($im,0,19,74,19,$line_color); 
// Write math question in a nice TTF Font
ImageTTFText($im, 10, 0, 3, 16,$font_color, $font,  $num1." + ".$num2." =" );

// Display Image
ImagePNG($im);
ImageDestroy($im); 

// Add the answer to the session
$_SESSION['secanswer'] = $num1+$num2;
?>