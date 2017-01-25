<?php
namespace CFPropertyList;
error_reporting( E_ALL );
ini_set( 'display_errors', 'on' );

require_once(__DIR__.'/libs/CFPropertyList/classes/CFPropertyList/CFPropertyList.php');

set_time_limit(0);
$workDir = './work';
$outputDir = './output';
$files = scandir($workDir);

foreach($files as $file){
  $extPos = strpos($file,'.plist');
  if(!$extPos){ continue; }
  $fileName = substr($file,0,$extPos);
  @mkdir($outputDir.'/'.$fileName);
  @chmod($outputDir.'/'.$fileName, 0777);
  $plist = new CFPropertyList( $workDir."/".$fileName.".plist", CFPropertyList::FORMAT_XML );
  $plistArray = $plist->toArray();
  $frames = $plistArray["frames"];
  //$metaData = $plistArray["metaData"];
  $fullImage = imagecreatefrompng($workDir.'/'.$fileName.'.png');
  foreach($frames as $imageName => $frameProperties){
    $textureRectStr = $frameProperties['textureRect'];
    $textureRotated = $frameProperties['textureRotated'];
    $textureRect = array();
    preg_match('/\{\{(\d+),(\d+)\},\{(\d+),(\d+)\}\}/', $textureRectStr, $textureRect);

    $x = $textureRect[1];
    $y = $textureRect[2];
    $w = $textureRotated ? $textureRect[4] : $textureRect[3];
    $h = $textureRotated ? $textureRect[3] : $textureRect[4];
    $newImage = imagecreatetruecolor($w,$h);
    imagesavealpha($newImage, true);
    $transparent = imagecolorallocatealpha($newImage, 0, 0, 0);
    imagecolortransparent($newImage, $transparent);
    imagealphablending($newImage, false);
    imagesavealpha($newImage, true);
    imagecopy($newImage, $fullImage, 0, 0, $x, $y, $w, $h);
    if($textureRotated){
      $newImage = imagerotate($newImage, 90, 0);
    }
    imagepng($newImage, $outputDir.'/'.$fileName.'/'.$imageName);
    @chmod($outputDir.'/'.$fileName.'/'.$imageName, 0777);
  }
}