<?php
require_once(__DIR__.'/phpqrcode.php');                 

class TFAQrCodeGenerator implements RobThree\Auth\Providers\Qr\IQRCodeProvider {
  public function getMimeType() {
    return 'image/png';                             // This provider only returns PNG's
  }
  
  public function getQRCodeImage($qrtext, $size) {
    ob_start();                                     // 'Catch' QRCode's output
    QRCode::png($qrtext, null, QR_ECLEVEL_L, 3, 4); // We ignore $size and set it to 3
                                                    // since phpqrcode doesn't support
                                                    // a size in pixels...
    $result = ob_get_contents();                    // 'Catch' QRCode's output
    ob_end_clean();                                 // Cleanup
    return $result;                                 // Return image
  }

  public function getQRCodeSvg($qrtext, $width=null, $htmlId=false, $compress=false) {
    return QRCode::svg($qrtext, $htmlId, false, QR_ECLEVEL_L, $width, 3, 4, $compress);
  }
}