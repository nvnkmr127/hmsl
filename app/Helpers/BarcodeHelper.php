<?php

namespace App\Helpers;

use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Picqer\Barcode\BarcodeGeneratorHTML;

class BarcodeHelper
{
    /**
     * Generate an SVG barcode for the given text.
     *
     * @param string $text
     * @param string $type
     * @param int $widthFactor
     * @param int $height
     * @param string $color
     * @return string
     */
    public static function generate($text, $type = 'TYPE_CODE_128', $widthFactor = 2, $height = 30, $color = 'black')
    {
        if (!$text) return '';
        
        try {
            $generator = new BarcodeGeneratorSVG();
            $barcodeType = constant("Picqer\Barcode\BarcodeGenerator::{$type}");
            return $generator->getBarcode($text, $barcodeType, $widthFactor, $height, $color);
        } catch (\Exception $e) {
            return 'Barcode Error';
        }
    }

    /**
     * Generate an HTML barcode for the given text (useful for PDFs).
     *
     * @param string $text
     * @param string $type
     * @param int $widthFactor
     * @param int $height
     * @param string $color
     * @return string
     */
    public static function generateHtml($text, $type = 'TYPE_CODE_128', $widthFactor = 2, $height = 30, $color = 'black')
    {
        if (!$text) return '';
        
        try {
            $generator = new BarcodeGeneratorHTML();
            $barcodeType = constant("Picqer\Barcode\BarcodeGenerator::{$type}");
            return $generator->getBarcode($text, $barcodeType, $widthFactor, $height, $color);
        } catch (\Exception $e) {
            return 'Barcode Error';
        }
    }
}
