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
  $fullImage = imagecreatefrompng($workDir.'/'.$fileName.'.png');
  $fullImageRotated = imagecreatefrompng($workDir.'/'.$fileName.'.png');
  $fullImageRotated = imagerotate($fullImage, 90, 0);
  $normalImage = imagecreatefrompng($workDir.'/'.$fileName.'_n.png');
  $normalImageRotated = imagecreatefrompng($workDir.'/'.$fileName.'_n.png');
  $normalImageRotated = imagerotate($normalImage, 90, 0);
  $sizeOrigin = getimagesize($workDir.'/'.$fileName.'.png');
  foreach($frames as $imageName => $frameProperties){
    $textureRotated = $frameProperties['textureRotated'];
    
    $spriteOffsetStr = $frameProperties['spriteOffset'];
    $spriteOffset = array();
    preg_match('/\{([\-\.0-9]{0,}),([\-\.0-9]{0,})\}/', $spriteOffsetStr, $spriteOffset);
    
    $spriteSourceSizeStr = $frameProperties['spriteSourceSize'];
    $spriteSourceSize = array();
    preg_match('/\{(\d+),(\d+)\}/', $spriteSourceSizeStr, $spriteSourceSize);
    
    $textureRectStr = $frameProperties['textureRect'];
    $textureRect = array();
    preg_match('/\{\{(\d+),(\d+)\},\{(\d+),(\d+)\}\}/', $textureRectStr, $textureRect);
      
    $wOrigin = $textureRect[3];
    $hOrigin = $textureRect[4];
    $xOrigin = $textureRect[1];
    $yOrigin = $textureRect[2];
      
    $wDestination = $wOrigin;
    $hDestination = $hOrigin;
    $xDestination = $spriteSourceSize[1]/2 + $spriteOffset[1] - $wOrigin/2;
    $yDestination = $spriteSourceSize[2] - ($spriteSourceSize[2]/2 + $spriteOffset[2]) - $hOrigin/2;
      
    $newImage = imagecreatetruecolor($spriteSourceSize[1], $spriteSourceSize[2]);
    $newNormal = imagecreatetruecolor($spriteSourceSize[1], $spriteSourceSize[2]);
    
    if($textureRotated){
      $imageReference = $fullImageRotated;
      $normalReference = $normalImageRotated;
      $temp = $xOrigin;
      $xOrigin = $yOrigin;
      $yOrigin = $sizeOrigin[0] - $temp - $hOrigin;
    }
    else{
      $imageReference = $fullImage;
      $normalReference = $normalImage;
    }
    
    $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
    imagefill($newImage, 0, 0, $transparent);
    imagealphablending($newImage, false);
    imagesavealpha($newImage, true);
    imagecopyresampled($newImage, $imageReference, $xDestination, $yDestination, $xOrigin, $yOrigin, $wDestination, $hDestination, $wOrigin, $hOrigin);
    imagepng($newImage, $outputDir.'/'.$fileName.'/'.$imageName);
    imagedestroy($newImage);
    @chmod($outputDir.'/'.$fileName.'/'.$imageName, 0777);

    $purple = imagecolorallocate($newNormal, 127, 127, 255);
    imagefill($newNormal, 0, 0, $purple);
    imagecopyresampled($newNormal, $normalReference, $xDestination, $yDestination, $xOrigin, $yOrigin, $wDestination, $hDestination, $wOrigin, $hOrigin);
    imagepng($newNormal, $outputDir.'/'.$fileName.'/'. str_replace(".png","_n.png",$imageName) );
    imagedestroy($newNormal);

  }
  imagedestroy($fullImage);
  imagedestroy($fullImageRotated);
}
