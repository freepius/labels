<?php
namespace App\Twig;

use App\Util\BarcodeGeneratorSVG;
use Twig\Extension\AbstractExtension;
use Twig\Environment;
use Twig\TwigFilter;

/**
 * Twig extension for generating barcodes.
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
            // usage twig : {{ '329299000123'|barcode('ean13') }}
            new TwigFilter('barcode', [$this, 'barcode'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Generate a barcode SVG image for a given code and type.
     *
     * @param string|int $code   The code to encode
     * @param string|null $type  The barcode type (e.g., 'ean13', 'c39', etc.) If null, the type will be guessed.
     * @param bool $showDigits   Whether to add the digits below the barcode
     * @param int $widthFactor   Thickness of the bars (virtual pixels)
     * @param int $height        Height of the bars (virtual pixels)
     *
     * @return string File path to the generated SVG barcode image, relative to the asset folder.
     *
     * @throws \RuntimeException if the SVG file cannot be written.
     */
    public function barcode(string|int $code, ?string $type = null, bool $showDigits = false, float $widthFactor = 1, float $height = 30): string
    {
        $digits = preg_replace('/\D+/', '', (string) $code);

        if (null === $type) {
            $type = $this->guessBarcodeType($digits);
        }

        if ('ean13' === $type && 12 === strlen($digits)) {
            $digits .= $this->ean13Checksum($digits);
        }

        $filename = sprintf(
            '%s-%s-%d-%s-%s.svg',
            $type,
            $digits,
            (int) $showDigits,
            str_replace('.', '_', $widthFactor),
            str_replace('.', '_', $height)
        );

        if (file_exists($this->realPath . $filename)) {
            return $this->assetFolder . $filename;
        }

        $gen = new BarcodeGeneratorSVG();
        $svg = $gen->getBarcode($digits, $type, $widthFactor, $height);

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

    /**
     * Guess the barcode type based on the length of the digits.
     */
    protected function guessBarcodeType(string $digits): string
    {
        $length = strlen($digits);

        return match ($length) {
            8 => 'ean8',
            12, 13 => 'ean13',
            14 => 'itf14',
            default => 'c39', // default to Code 39 for other lengths
        };
    }
}
