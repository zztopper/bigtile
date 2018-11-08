<?php
define("TILESIZE", 512);
define("SCALE", 20);
define("WIDTH", SCALE*TILESIZE);
define("HEIGHT", SCALE*TILESIZE);
$debug = isset($_GET['debug'])?$_GET['debug']:false;
$lon = isset($_GET['longitude'])?$_GET['longitude']:0;
$lat = isset($_GET['latitude'])?$_GET['latitude']:0;
$zoom = isset($_GET['zoom'])?$_GET['zoom']:6;
$xtile = floor((($lon + 180) / 360) * pow(2, $zoom));
$ytile = floor((1 - log(tan(deg2rad($lat)) + 1 / cos(deg2rad($lat))) / pi()) /2 * pow(2, $zoom));

$dest_image = imagecreatetruecolor(WIDTH, HEIGHT);
$xmin = $xtile-((SCALE/2)-1); 
$xmax = $xtile+(SCALE/2);
$ymin = $ytile-((SCALE/2)-1); 
$ymax = $ytile+(SCALE/2);

if ($debug) {
	echo "X: $xmin — $xmax<br>";
	echo "Y: $ymin — $ymax<br>";
}
$curx=0;
$cury=0;
$fn = $zoom.'-'.$xtile.'-'.$ytile.'.png';
for ($x=$xmin;$x<=$xmax;$x++) 
{
	for ($y=$ymin;$y<=$ymax;$y++) 
	{
		$tile="http://tiles.app.moscow/ru/$zoom/$x/$y.png";
		$tmp = imagecreatefrompng($tile);
		$posx = $curx*TILESIZE;
		$posy = $cury*TILESIZE;	
		if ($debug) {
			echo $tile;
			echo "@$posx:$posy<br>";	
		}
		imagecopy($dest_image, $tmp, $posx, $posy, 0, 0, TILESIZE, TILESIZE);
		imagedestroy($tmp);
		$cury++;
	}
	$cury=0;
	$curx++;
}

if (!$debug) {
#	header('Content-Type: image/png');
	ob_start();
	imagepng($dest_image);
	$image_data = ob_get_contents();
	ob_end_clean();
	$tmpfname = tempnam("/tmp","map");
	$handle = fopen($tmpfname, "w");
	fwrite($handle, $image_data);
	fclose($handle);
	$fsize=filesize($tmpfname);
	header('Content-Transfer-Encoding: binary');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($tmpfname)) . ' GMT');
	header("Cache-Control: private");
	header('Content-Encoding: none');
	$mime = ($mime = getimagesize($tmpfname)) ? $mime['mime'] : $mime;
	header("Content-type: " . $mime);
        header("Content-Length: ".$fsize);
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header("Content-Disposition: Attachment;filename=$fn"); 
	$fp=fopen($tmpfname, "rb");
	fpassthru($fp);
	unlink($tmpfname);

}
imagedestroy($dest_image);
?>
