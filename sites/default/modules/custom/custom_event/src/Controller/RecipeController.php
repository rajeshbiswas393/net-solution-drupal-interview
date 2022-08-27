<?php
namespace Drupal\recipe_branding\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManager;
class RecipeController extends ControllerBase  {


  public function getImageIdentifier($imgfile='')
  {
    list($width, $height, $type, $attr) = getimagesize($imgfile);
    switch ($type) 
      {

        case 1:
            $im = imagecreatefromgif($imgfile);
            break;

        case 2:
            $im = imagecreatefromjpeg($imgfile);
            break;

        case 3:
            $im = imagecreatefrompng($imgfile);
            break;
        default:
          $im = imagecreatefrompng($imgfile);

    }
    return $im;
    
  }

  function scaleImage($filePath,$pctHight=100,$pctWidth=100)
  {
    $src = $this->getImageIdentifier($filePath);
    $width = ImageSx($src);
    $height = ImageSy($src);
    $x = ($width*$pctWidth)/100; $y = ($height*$pctHight)/100;
    $dst = ImageCreateTrueColor($x,$y);
    ImageCopyResampled($dst,$src,0,0,0,0,$x,$y,$width,$height);
    return $dst;
  }
  public function recipeImageWithBrandLogo($id=null)
  {
    $node = \Drupal::EntityTypeManager()->getStorage('node')->load($id);
    $fileBasePath='sites//poc_site1/files/herbalife/';
    $noImageFoundURI = $fileBasePath.'no-image.jpg';
    if(!$node)
    {
      $dest = imagecreatefromjpeg($noImageFoundURI);
      header('Content-Type: image/png');
      imagepng($dest);
      return;
    }
    $image = $node->get('field_image')->getValue();
    $fid = $image[0]['target_id'];
    $file = \Drupal\file\Entity\File::load($fid);
    $imageURI = $file->getFileUri();
    if(file_exists($imageURI))
    {
      $fileBasePath='sites//poc_site1/files/herbalife/';
      $whiteBgPath = $fileBasePath.'white-background.jpg';
      $recipiimagePath = $imageURI;
      $logoImagePath = $fileBasePath.'logo.jpeg';
      $logo2ImagePath = $fileBasePath.'logo2.jpg';
       
           
            $logoSize = getimagesize($logoImagePath);
            $logoHeight = $logoSize[1];
            $logoWidth =  $logoSize[0];
            $logo2Size = getimagesize($logo2ImagePath);
            $logo2width = $logo2Size[0];
            $logo2heigth = $logo2Size[1];
            $backgroundSize = getimagesize($whiteBgPath);
            $backgroundWidth = $backgroundSize[0];
            $backgroundHeight = $backgroundSize[1];

            $recipeImageSize = getimagesize($recipiimagePath);
            $recipeImageHeight = $recipeImageSize[0];
            $recipeImageWidth = $recipeImageSize[1];


            if($recipeImageWidth >  $backgroundWidth)
            {
               $recipePositionX = 10.0;
               $recipePositionY = 10.0;
               $recipeWidthScale = (($recipeImageWidth - $backgroundWidth+20)/$recipeImageWidth)*100;
               $recipeWidth = $backgroundWidth-20;
            }
            else
            {
              $recipePositionX = ($backgroundWidth - $recipeImageWidth)/2;
              $recipePositionY = 10;
              $recipeWidthScale = 100.0;
              $recipeWidth = $recipeImageWidth-20;
            }



            if($recipeImageHeight > ($backgroundHeight -$logoHeight-250))
            {
              $reciceHeightScale = (($recipeImageHeight-$backgroundHeight+$logoHeight+250)/$recipeImageHeight)*100;
              $recipeHeight = ($backgroundHeight-$logoHeight-250);
            }
            else
            {
              $reciceHeightScale = 100.0;
              $recipeHeight = $recipeImageHeight;
            }

            $imageFinalScale = min($reciceHeightScale,$recipeWidthScale);

            $dest = $this->getImageIdentifier($whiteBgPath);
            $recipe = $this->scaleImage($imageURI,$imageFinalScale,$imageFinalScale);
            $src = $this->scaleImage($logoImagePath,100,100);
            $src2 = $this->scaleImage($logo2ImagePath,100,100);

            $destinationLogoPositionX = 10;
            $destinationLogoPositionY = ($backgroundHeight -  $logoHeight)-100;

            $destinationLogo2PositionX = ($backgroundWidth - $logo2width)-10;
            $destinationLogo2PositionY = ($backgroundHeight -  $logo2heigth)-100;

           

            imagealphablending($dest, false);
            imagesavealpha($dest, true);

            imagecopymerge($dest, $src, $destinationLogoPositionX,$destinationLogoPositionY-10, 0, 0,$logoWidth,$logoHeight,100);
            imagecopymerge($dest, $src2, $destinationLogo2PositionX,$destinationLogo2PositionY, 0, 0,$logo2width,$logo2heigth, 100);
            imagecopymerge($dest, $recipe,$recipePositionX,$recipePositionY, 0, 0,$recipeWidth,$recipeHeight,100);
            $color = imagecolorallocate($dest,0,0,0);
            $string = "These products are not intended to diagnose, treat or prevent any diseases. When using Herbalife Nurtition products, please follow the instructiond on the product";
            $fontSize = 2;
            $x = 10;
            $y = ($backgroundHeight-50);
            imagestring($dest, $fontSize, $x, $y, $string, $color);
            header('Content-Type: image/png');
            imagepng($dest);
            
            imagedestroy($dest);
            imagedestroy($src);
            imagedestroy($src2);
            return;
    }
    else
    {
      $dest = imagecreatefromjpeg($noImageFoundURI);
      header('Content-Type: image/png');
      imagepng($dest);
      return;
    }
   
  }
}
