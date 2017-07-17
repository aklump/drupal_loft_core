<?php
namespace AKlump\LoftLib\Component\Pdf;

interface PdfConverterInterface
{
    /**
     * Convert a file to a pdf document.
     *
     *
     * @param string $filePath
     *
     * @return string The filepath to the PDF document.
     *
     * @throws RuntimeException If the conversion cannot take place.
     * @throws InvalidArgumentException If the filepath is not correct.
     */
    public function convert($filePath);

    /**
     * Returns information about the last conversion.
     *
     * @return mixed
     */
    public function getResultCode();

    /**
     * Return pass/fail if the converstion system is functioning.
     *
     * @return bool
     */
    public function testConvert();
}
