<?php

// Lets get into the session
session_start();

// header for png
Header("Content-Type: image/png");

// Create Image
$im = ImageCreate(75, 20); 
$white = ImageColorAllocate($im, 255, 255, 255);
$black = ImageColorAllocate($im, 0, 0, 0);

// Create the random numberes
srand((double)microtime()*1000000); 

$num1 = rand(1,5);
$found == false;
while ($found == false)
{
	$num2 = rand(1,100);
	if (preg_match('/^[0-9]+$/', $num2/5))
	{
		$found = true;
		break;
	}
}

// Fill image, make transparent
ImageFill($im, 0, 0, $white);
imagecolortransparent ($im, $white);
// Write math question in a nice TTF Font
ImageTTFText($im, 10, 0, 2, 16,$black, "../fonts/verabd.ttf",  $num1." + ".$num2." =" );

// Display Image
ImagePNG($im);
ImageDestroy($im); 

// Add the answer to the session
$_SESSION['secanswer'] = $num1+$num2;
?>