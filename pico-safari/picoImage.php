<?php
/*
How to use:

http://www.porganized.com/projects/2011/pico-sdh/picoImage.php?id=1&size=75

Required parameters:
id - the master creature id.

Optional parameters:
baseURL - the location of the folder where the images come from.
size - the width/height of a square image, in pixels.
hidden - Set to true to make into a silhouette.

*/ 
$picoImage["baseURL"] = isset($_GET["baseURL"]) ? $_GET["baseURL"] : "http://www.porganized.com/projects/2011/pico-sdh/images/";
$picoImage["size"] = isset($_GET["size"]) ? $_GET["size"] : null;
$picoImage["id"] = isset($_GET["id"]) ? $_GET["id"] : null;

if (isset($picoImage["id"])) {
	header('content-type: image/png'); 
	$imageQuery = $picoImage["baseURL"].$picoImage["id"]."_600.png";
	#echo $imageQuery;
	$im = imagecreatefrompng($imageQuery);
	if (isset($_GET["hidden"])) {
		imagefilter($im, IMG_FILTER_CONTRAST, 10);
		imagefilter($im, IMG_FILTER_GRAYSCALE);
	}
	imagealphablending($im, true);
	imagesavealpha($im, true);
	
	if (isset($_GET["trans"])) {
		$image_p = imagecreatetruecolor($picoImage["size"], $picoImage["size"]);
		imagealphablending($image_p, false);
 	   $color = imagecolortransparent($image_p, imagecolorallocatealpha($image_p, 0, 0, 0, 127));
	    imagefill($image_p, 0, 0, $color);
	    imagesavealpha($image_p, true);
		imagecopyresampled($image_p, $im, 0, 0, 0, 0, $picoImage["size"], $picoImage["size"], 600, 600);
		imagealphablending($image_p, true);
		imagesavealpha($image_p, true);
		imagepng($image_p);
		
		$image_s = imagecreatetruecolor(600, 600);
		imagealphablending($image_s, false);
 	   $color = imagecolortransparent($image_p, imagecolorallocatealpha($image_p, 0, 0, 0, 127));
	    imagefill($image_s, 0, 0, $color);
	    imagesavealpha($image_s, true);
		imagecopymerge($image_s, $image_p, 0, 0, 0, 0, 600, 600, 50);
		imagealphablending($image_s, true);
		imagesavealpha($image_s, true);
		imagepng($image_s);
		echo ($image_s);
	}
	
	if (isset($picoImage["size"])) {
		$image_p = imagecreatetruecolor($picoImage["size"], $picoImage["size"]);
		imagealphablending($image_p, false);
 	   $color = imagecolortransparent($image_p, imagecolorallocatealpha($image_p, 0, 0, 0, 127));
	    imagefill($image_p, 0, 0, $color);
	    imagesavealpha($image_p, true);
		imagecopyresampled($image_p, $im, 0, 0, 0, 0, $picoImage["size"], $picoImage["size"], 600, 600);
		imagealphablending($image_p, true);
		imagesavealpha($image_p, true);
		imagepng($image_p);
		echo ($image_p);
	} else {
		imagepng($im);
		echo ($im);
	} 

} else {
	echo "A parameter is missing. Are you sure you have included the id?"; 
}
?>