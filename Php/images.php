<?php
/**
 * @author Dar0012
 * @since php 7.4 
 * some GD functions no founded in php 8+ and cause error
 */

 
// * IMAGE DOWNLOAD
/**
 * afficher une image dans le navigateur ou comme reponse de votre api mais au format bunnaire.
 * a utiliser dans <img />;
 * @param string $path le chemin absolu vers le fichier
 * @param string $default Le chemin vers le fichier image par defaut qui sera afficher au cas ou $path ne pas founis ou incorrect (fichier introuvalable)
 * @param int|string $quality la qualite de l'image (resolution en pixel). le valeurs possible sont :
 * - low
 * - hight
 * - -10 a 0 jusqu'a 10
 * @param int|string $size la taille de l'image en % de la largeur et l'hauteur initial de l'image. les valaurs possible sont: 
 * - xs : 12% trop petite
 * - sm : 25% petite
 * - md : 50% moyenne
 * - lg : 75% large
 * - xl : 100% tres large
 * - or : taille original
 * - 0 a n : si vous mettez n à une valeur plus lointin alors le poid du fichier sera lage et peut-etre sans grand chose.
 * @return void affiche l'image dans le "navigateur"
 */

// ? --> path, quality: low|hight|-0-10, size: {xs: 12, sm: 25, md: 50, lg: 75, xl: 100, or: xl}|0-n
// ? <-- image
function print_image(string $path, string $default = NULL, $quality = "hight", $size = "md", bool $base64 = FALSE): void {
  if ($_SERVER['REQUEST_METHOD'] == "GET") {

    # verify il file exist
    if (file_exists($path) == FALSE) {
      if ($default != NULL && file_exists($default) == TRUE) {
        $path = $default;
      } else {
        header ('Content-Type: image/png');
        $im = imagecreatetruecolor(120, 20)
              or die('Impossible de créer un flux d\'image GD');
        $text_color = imagecolorallocate($im, 233, 14, 91);
        imagestring($im, 1, 5, 5,  'AUCUN IMAGE TROUVER', $text_color);
        imagepng($im);
        imagedestroy($im);
        die();
      }
    }
    
    # intializ values
    // $path     = $_GET['path'];
    // $size     = isset($_GET['size']) ? $_GET['size'] : 'md';
    // $quality  = isset($_GET['quality']) ? $_GET['quality'] : 'hight';
    $sizeds     = ['xs', 'sm', 'md', 'lg', 'xl', 'or'];
    $size_type  = 'number';
  
    if (in_array($size, $sizeds)) $size_type = 'sized';
    elseif ((int) $size) { // size > 0
      $size_type = 'number';
      $size = (int) $size;
    } else {
      $size_type = 'number';
      $size = 50; // default if size size == 0
    } 
  
    $imageData = ["path"=> $path, "mime"=> mime_content_type($path)];
    // ---------------------------------------------------
    
    // Content type
    // if ($base64 == TRUE) header('Content-Type: text/plain');
    // else header('Content-Type: '.$imageData['mime']);
    
    // Calcul des nouvelles dimensions
    if ($size_type == 'sized') {
      $sizeds_ = ['xs'=> 0.12, 'sm'=> 0.25, 'md'=> 0.50, 'lg'=> 0.75, 'xl'=> 1, 'or'=> 1];
      $percent = $sizeds_[$size];
  
      list($width, $height) = getimagesize($imageData['path']);
      $new_width = $width * $percent;
      $new_height = $height * $percent;
      
      // Redimensionnement
      $image_p = imagecreatetruecolor($new_width, $new_height) or die("lslsds");
      $image = null;
      switch ($imageData['mime']) {
        case 'image/jpeg':
          $image = imagecreatefromjpeg($imageData['path']);
          break;
        
        case 'image/png':
          $image = imagecreatefrompng($imageData['path']);
          break;
        
        case 'image/gif':
          $image = imagecreatefromgif($imageData['path']);
          break;
        
        default:
          # code...
          break;
      }
      if ($quality == 'low')
        imagecopyresized($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
      else
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
  
    } elseif ($size_type == 'number') {
      // Définition de la largeur et de la hauteur maximale
      list($width_orig, $height_orig) = getimagesize($imageData['path']);
      $width =  $size;
      $height = $width;
  
      // Cacul des nouvelles dimensions
  
      $ratio_orig = $width_orig/$height_orig;
  
      if ($width/$height > $ratio_orig) {
        $width = $height*$ratio_orig;
      } else {
        $height = $width/$ratio_orig;
      }
  
      // Redimensionnement
      $image_p = imagecreatetruecolor($width, $height);
      switch ($imageData['mime']) {
        case 'image/jpeg':
          $image = imagecreatefromjpeg($imageData['path']);
          break;
        
        case 'image/png':
          $image = imagecreatefrompng($imageData['path']);
          break;
        
        case 'image/gif':
          $image = imagecreatefromgif($imageData['path']);
          break;
        
        default:
          # code...
          break;
      }
  
      if ($quality == 'low')
        imagecopyresized($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
      else // if quality is hight or 0-10
        imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
    }
  
    # tempfile if base64 is TRUE
    $base64_tempfile = NULL;
    if ($base64 == TRUE) {
      $base64_tempfile = tempnam(__DIR__, "imageBin2Base64");
    }

    // Affichage
    $compress_val = in_array($quality, ['low', 'hight']) ? 10 : (int) $quality;
    print_r("perpe");
    die();
    switch ($imageData['mime']) {
      case 'image/jpeg':
        $level = $compress_val <= 0 ? 1 : $compress_val;
        $level = $level > 10 ? 10 : $level;
        imagejpeg($image_p, $base64_tempfile, $level * 10);
        imagedestroy($image_p);
        break;
      
      case 'image/png':
        $compress_val -= 1;
        $level = $compress_val < 0 ? -1 : $compress_val;
        $level = $level > 9 ? 9 : $level;
        imagepng($image_p, $base64_tempfile, $level);
        imagedestroy($image_p);
        break;
      
      case 'image/gif':
        imagegif($image_p, $base64_tempfile);
        imagedestroy($image_p);
        break;
      
      default:
        imagedestroy($image_p);
        break;
    }

    # base64 handle
    if ($base64 == TRUE) {
      $file_ = file_get_contents($base64_tempfile);
      echo base64_encode($file_, SODIUM_BASE64_VARIANT_ORIGINAL);
      unlink($base64_tempfile);
    }
  }
}

/**
 * convert image file to base64 format. les format image supporter sont :
 * - jpeg | png | gif
 * @param string $path le chemin vers le fichier image a convertir
 * @param bool $print si definie a TRUE alors l'image est afficher dans le navigateur et la fonction retourne TRUE
 * @return bool|string retourne l'image encoder en base64 string, TRUE si print et TRUE et tout va bien, false si erreur
 */
function image2base64($path, $print = FALSE) {
  if (file_exists($path) == TRUE) {
    $mime = mime_content_type($path);
    if (in_array($mime, ['image/jpeg', 'image/gif', 'image/png'])) {
      header('Content-Type: ' . $mime);
      $file_ = file_get_contents($path);
      $data = base64_encode($file_);
      if ($print == TRUE) {
        echo $data;
        return TRUE;
      } else return $data;
    } else return FALSE;
  } else return FALSE;
}
