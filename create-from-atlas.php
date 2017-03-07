<?php
namespace CFPropertyList;
error_reporting( E_ALL );
ini_set( 'display_errors', 'on' );

set_time_limit(0);
$workDir = './work';
$outputDir = './output';
$files = scandir($workDir);

echo '<pre>';

foreach($files as $file){
  $extPos = strpos($file,'.atlas');
  if(!$extPos){ continue; }
  $fileName = substr($file,0,$extPos);
  @mkdir($outputDir.'/'.$fileName);
  @chmod($outputDir.'/'.$fileName, 0777);
  $str = file_get_contents( $workDir."/".$fileName.".atlas" );
  $arr = explode("\n", $str);
  unset( $arr[0], $arr[1], $arr[2], $arr[3], $arr[4], $arr[5] );
  array_pop( $arr );
  $fullImage = imagecreatefrompng($workDir.'/'.$fileName.'.png');
  $imageName = "";
  foreach( $arr as $line ){
    $line = trim( $line );
    if( !strpos($line, ":") ){
      if( $imageName != "" ){
        echo "CREATING IMG \"".$imageName."\" with params: ";
        print_r($params);

        $size = explode( ",", $params["size"] );
        $position = explode( ",", $params["xy"] );
        $w = trim( $size[0] );
        $h = trim( $size[1] );
        $x = trim( $position[0] );
        $y = trim( $position[1] );
        $textureRotated = ( $params['rotate'] == "true" ) ? true : false;

        $newImage = imagecreatetruecolor($w,$h);
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 0);
        imagecolortransparent($newImage, $transparent);
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        imagecopyresampled($newImage, $fullImage, 0, 0, $x, $y, $w, $h, $w, $h);
        if($textureRotated){
          $newImage = imagerotate($newImage, 90, 0);
        }
        imagepng($newImage, $outputDir.'/'.$fileName.'/'.$imageName);
        @chmod($outputDir.'/'.$fileName.'/'.$imageName, 0777);



      }
      $imageName = $line.".png";
      $params = array();
    }
    else{
      $paramParts = explode( ":", $line );
      $params[ $paramParts[0] ] = $paramParts[1];
    }
  }
}