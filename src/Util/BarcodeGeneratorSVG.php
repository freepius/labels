<?php

namespace App\Util;

use Picqer\Barcode\BarcodeGenerator;
use Picqer\Barcode\Exceptions\UnknownTypeException;
use Picqer\Barcode\Helpers\ColorHelper;
use Picqer\Barcode\Renderers\SvgRenderer;

/**
 * This class is a copy of the Picqer\Barcode\BarcodeGeneratorSVG class
 * with modifications to add:
 * - a background color option,
 * - the ability to generate an SVG with a specific type (inline or standalone).
 */
class BarcodeGeneratorSVG extends BarcodeGenerator
{
    /**
     * Return a SVG string representation of barcode.
     *
     * @param $barcode (string) code to print
     * @param BarcodeGenerator::TYPE_* $type (string) type of barcode
     * @param $widthFactor (float) Minimum width of a single bar in user units.
     * @param $height (float) Height of barcode in user units.
     * @param $foregroundColor (string) Foreground color (in SVG format) for bar elements (background is transparent).
     * @param $backgroundColor (string|null) Background color (in SVG format) for the barcode.
     * @param SvgRenderer::TYPE_SVG_* $svgType (string) Type of SVG output, either inline or standalone.
     * @return string SVG code.
     * @public
     * @throws UnknownTypeException
     */
    public function getBarcode(
        string $barcode, $type,
        float $widthFactor = 2,
        float $height = 30,
        string $foregroundColor = 'black',
        ?string $backgroundColor = null,
        string $svgType = SvgRenderer::TYPE_SVG_INLINE
    ): string
    {
        $barcodeData = $this->getBarcodeData($barcode, $type);

        $width = round(($barcodeData->getWidth() * $widthFactor), 3);

        $renderer = new SvgRenderer();
        $renderer->setForegroundColor(ColorHelper::getArrayFromColorString($foregroundColor));
        $renderer->setBackgroundColor(
            $backgroundColor ? ColorHelper::getArrayFromColorString($backgroundColor) : null
        );
        $renderer->setSvgType($svgType);

        return $renderer->render($barcodeData, $width, $height);
    }
}
