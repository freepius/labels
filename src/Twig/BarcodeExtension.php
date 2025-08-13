<?php
namespace App\Twig;

use App\Util\BarcodeGeneratorSVG;
use Twig\Extension\AbstractExtension;
use Twig\Environment;
use Twig\TwigFilter;

/**
 * Twig extension for generating barcodes.
 * Currently, only EAN-13 barcodes are supported.
 */
class BarcodeExtension extends AbstractExtension
{
    protected string $assetFolder = '/barcodes/';
    protected string $realPath;

    public function __construct(protected Environment $twig)
    {
        $path = realpath(__DIR__ . '/../../assets') . $this->assetFolder;

        // Ensure the barcode image folder exists
        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }

        $this->realPath = $path;
    }

    public function getFilters(): array
    {
        return [
            // usage twig : {{ '329299000123'|ean13_barcode }}
            new TwigFilter('ean13_barcode', [$this, 'ean13'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Generate an EAN-13 barcode SVG image.
     *
     * @param string|int $code  12 or 13 digits (if 12, the checksum is calculated)
     * @param bool $showDigits  Whether to add the digits below the barcode
     * @param int $widthFactor  Thickness of the bars (virtual pixels)
     * @param int $height       Height of the bars (virtual pixels)
     *
     * @return string File path to the generated SVG barcode image, relative to the asset folder.
     *
     * @throws \InvalidArgumentException if the code is not valid (not 12 or 13 digits).
     * @throws \RuntimeException if the SVG file cannot be written.
     */

    public function ean13(string|int $code, bool $showDigits = false, float $widthFactor = 1, float $height = 30): string
    {
        $digits = preg_replace('/\D+/', '', (string) $code);

        if (12 === strlen($digits)) {
            $digits .= $this->ean13Checksum($digits);
        }

        if (13 !== strlen($digits)) {
            throw new \InvalidArgumentException("{$digits} is not a valid EAN-13 code. It must be 12 or 13 digits long.");
        }

        $filename = sprintf(
            '%s-%d-%s-%s.svg',
            $digits,
            (int) $showDigits,
            str_replace('.', '_', $widthFactor),
            str_replace('.', '_', $height)
        );

        if (file_exists($this->realPath . $filename)) {
            return $this->assetFolder . $filename;
        }

        $gen = new BarcodeGeneratorSVG();
        $svg = $gen->getBarcode($digits, $gen::TYPE_EAN_13, $widthFactor, $height);

        $out = $this->twig->render(
            'label/element/barcode-plus-digits.svg.twig',
            [
                'svg' => $svg,
                'widthFactor' => $widthFactor,
                'height' => $height,
                'digits' => $showDigits ? $digits : null,
            ]
        );

        // Save the SVG to a file
        dump($out);
        if (false === file_put_contents($this->realPath . $filename, $out)) {
            throw new \RuntimeException("Failed to write {$digits} barcode SVG to {$this->realPath}{$filename}");
        }

        return $this->assetFolder . $filename;
    }

    /**
     * Calculate the EAN-13 checksum digit.
     */
    protected function ean13Checksum(string $twelveDigits): int
    {
        // EAN13 key calculation is (10 - ((sumOdd + 3*sumEven) % 10)) % 10
        $sumOdd = 0;  // positions 1,3,5,7,9,11 (index 0,2,...)
        $sumEven = 0; // positions 2,4,6,8,10,12 (index 1,3,...)

        for ($i = 0; $i < 12; $i++) {
            $n = (int) $twelveDigits[$i];
            if ($i % 2 === 0) {
                $sumOdd += $n;
            } else {
                $sumEven += $n;
            }
        }
        $total = $sumOdd + 3 * $sumEven;
        return (10 - ($total % 10)) % 10;
    }
}
