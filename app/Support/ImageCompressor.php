<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class ImageCompressor
{
    /**
     * Redimensiona (lado mayor a lo más $maxDimension) y recomprime una imagen como JPEG.
     * Nota: no auto-rota por EXIF, una foto tomada de lado con el celular puede quedar de lado.
     *
     * @return array{0: string, 1: string} contenido binario JPEG y su mime type
     */
    public static function comprimir(UploadedFile $archivo, int $maxDimension = 1920, int $calidad = 75): array
    {
        $origen = imagecreatefromstring((string) file_get_contents($archivo->getRealPath()));

        if ($origen === false) {
            return [(string) file_get_contents($archivo->getRealPath()), $archivo->getMimeType() ?? 'image/jpeg'];
        }

        $ancho = imagesx($origen);
        $alto = imagesy($origen);
        $mayor = max($ancho, $alto);

        if ($mayor > $maxDimension) {
            $factor = $maxDimension / $mayor;
            $nuevoAncho = max(1, (int) round($ancho * $factor));
            $nuevoAlto = max(1, (int) round($alto * $factor));

            $redimensionada = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
            imagecopyresampled($redimensionada, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);
            imagedestroy($origen);
            $origen = $redimensionada;
        }

        ob_start();
        imagejpeg($origen, null, $calidad);
        $contenido = (string) ob_get_clean();
        imagedestroy($origen);

        return [$contenido, 'image/jpeg'];
    }
}
