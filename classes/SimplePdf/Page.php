<?php
namespace SimplePdf;

/**
 * Extension of ZendPdf\Page which allows using arbitrary units (e.g. inches or centimeters) and works from
 * top to bottom, instead of default PDF/PostScript geometry. It also adds some text formatting utilities,
 * like word wrap and text alignment.
 *
 * @author Robbert Klarenbeek <robbertkl@renbeek.nl>
 * @copyright 2013 Robbert Klarenbeek
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Page extends \ZendPdf\Page
{
    /**
     * Constant for left aligned text
     *
     * @var float
     */
    const TEXT_ALIGN_LEFT = 0;

    /**
     * Constant for center aligned text
     *
     * @var float
     */
    const TEXT_ALIGN_CENTER = 0.5;

    /**
     * Constant for right aligned text
     *
     * @var float
     */
    const TEXT_ALIGN_RIGHT = 1;

    /**
     * Constant for point (native) units
     *
     * @var string
     */
    const UNITS_POINT = 1;

    /**
     * Constant for inch units (a point is 1/72 of an inch)
     *
     * @var string
     */
    const UNITS_INCH = 72;

    /**
     * Constant for centimeter units (72/2.54)
     *
     * @var string
     */
    const UNITS_CENTIMETER = 28.34645669291339;

    /**
     * Constant for millimeter units (7.2/2.54)
     *
     * @var string
     */
    const UNITS_MILLIMETER = 2.834645669291339;

    /**
     * Factor to convert native units (points) from/to user specified units
     *
     * @var float
     */
    protected $unitConversion = 1;

    /**
     * How far lines should be apart vertically, with 1.0 being 'normal' distance
     *
     * @var float
     */
    protected $lineSpacing = 1.0;

    /**
     * Create a new PDF page, with A4 size and default font Helvetica, size 12
     *
     * @param string $size page size (see \ZendPdf\Page), default A4 size
     * @param float $unitConversion conversion factor for custom units, default self::UNITS_CENTIMETER
     */
    public function __construct($size = self::SIZE_A4, $unitConversion = self::UNITS_CENTIMETER)
    {
        parent::__construct($size);
        $this->setUnitConversion($unitConversion);
        $this->setFont(\ZendPdf\Font::fontWithName(\ZendPdf\Font::FONT_HELVETICA), 12);
    }

    /**
     * Get the current conversion factor to convert from/to native units (points)
     *
     * @return float current conversion factor
     */
    public function getUnitConversion()
    {
        return $this->unitConversion;
    }

    /**
     * Sets the conversion factor to use to convert from/to native units (points)
     *
     * @param float $unitConversion new conversion factor
     */
    public function setUnitConversion($unitConversion)
    {
        $this->unitConversion = $unitConversion;
    }

    /**
     * Convert a value in the given units to points
     *
     * @param float &$number number (in the given units) to convert, BY REF
     */
    public function convertToPoints(&$number)
    {
        $number *= $this->getUnitConversion();
    }

    /**
     * Convert a value in points to the given units
     *
     * @param float &$number number (in points) to convert, BY REF
     */
    public function convertFromPoints(&$number)
    {
        $number /= $this->getUnitConversion();
    }

    /**
     * Convert (x,y)-coordinates in the user space (top to bottom, in the given units)
     * to native geometry (points, bottom to top)
     *
     * @param float &$x x-coordinate (in the given units, from the left) to convert, BY REF
     * @param float &$y y-coordinate (in the given units, from the top) to convert, BY REF
     */
    public function convertCoordinatesFromUserSpace(&$x, &$y)
    {
        $this->convertToPoints($x);

        $y = $this->getHeight() - $y;
        $this->convertToPoints($y);
    }

    /**
     * Convert (x,y)-coordinates in native geometry (points, bottom to top) to the
     * user space (top to bottom, in the given units)
     *
     * @param float &$x x-coordinate (in points, from the left) to convert, BY REF
     * @param float &$y y-coordinate (in points, from the bottom) to convert, BY REF
     */
    public function convertCoordinatesToUserSpace(&$x, &$y)
    {
        $this->convertFromPoints($x);

        $this->convertFromPoints($y);
        $y = $this->getHeight() - $y;
    }

    /**
     * Get the current line spacing
     *
     * @return float line spacing value, 1.0 being 'normal' distance
     */
    public function getLineSpacing()
    {
        return $this->lineSpacing;
    }

    /**
     * Sets the line spacing to use for future writeText() / writeLine() calls
     *
     * @param float $lineSpacing new line spacing value to use, 1.0 being 'normal' distance
     */
    public function setLineSpacing($lineSpacing)
    {
        $this->lineSpacing = $lineSpacing;
    }

    /**
     * Get page width in the given units
     *
     * @return float width of the page in the given units
     */
    public function getWidth()
    {
        $width = parent::getWidth();
        $this->convertFromPoints($width);
        return $width;
    }

    /**
     * Get page height in the given units
     *
     * @return float height of the page in the given units
     */
    public function getHeight()
    {
        $height = parent::getHeight();
        $this->convertFromPoints($height);
        return $height;
    }

    /**
     * Sets a new font family and, optionally, a new font size as well
     *
     * @param \ZendPdf\Resource\Font\AbstractFont $font font object to use
     * @param float $fontSize new font size, leave it out to keep the current font size
     */
    public function setFont(\ZendPdf\Resource\Font\AbstractFont $font, $fontSize = null)
    {
        if (is_null($fontSize)) {
            $fontSize = $this->getFontSize();
        }

        parent::setFont($font, $fontSize);
    }

    /**
     * Change the font size, without changing the font family
     *
     * @param float $fontSize new font size to use
     */
    public function setFontSize($fontSize)
    {
        $this->setFont($this->getFont(), $fontSize);
    }

    /**
     * Draw a line from 1 point to another
     *
     * @param float $x1 x-coordinate (in the given units) of the point from where to draw the line
     * @param float $y1 y-coordinate (in the given units) of the point from where to draw the line
     * @param float $x2 x-coordinate (in the given units) of the point to where to draw the line
     * @param float $y2 y-coordinate (in the given units) of the point to where to draw the line
     */
    public function drawLine($x1, $y1, $x2, $y2)
    {
        $this->convertCoordinatesFromUserSpace($x1, $y1);
        $this->convertCoordinatesFromUserSpace($x2, $y2);
        parent::drawLine($x1, $y1, $x2, $y2);
    }

    /**
     * Write a (multiline / optionally wrapping) text to the page
     *
     * @param float $x x-coordinate (in the given units) of the anchor point of the text
     * @param float $y y-coordinate (in the given units) of the anchor point of the text
     * @param string $text text to write to the PDF (can contain newlines)
     * @param float $anchorPoint horizontal position (0..1) to anchor each line, defaults to self::TEXT_ALIGN_LEFT
     * @param float $wrapWidth width (in the given units) to wrap text at, or leave out for no wrapping
     */
    public function writeText($x, $y, $text, $anchorPoint = self::TEXT_ALIGN_LEFT, $wrapWidth = 0)
    {
        if ($wrapWidth > 0) {
            $text = $this->wordWrapText($text, $wrapWidth);
        }

        $lineHeight = $this->getLineHeight();
        foreach (explode(PHP_EOL, $text) as $index => $line) {
            if (empty($line)) {
                continue;
            }

            $anchorOffset = ($anchorPoint == 0) ? 0 : -$anchorPoint * $this->getTextWidth($line);
            $this->writeLine($x + $anchorOffset, $y + $index * $lineHeight, $line);
        }
    }

    /**
     * Write a single line of text to the page
     *
     * @param float $x x-coordinate (in the given units) of the top-left corner where the text should start
     * @param float $y y-coordinate (in the given units) of the top-left corner where the text should start
     * @param string $line line to write to the page, should not contain newlines (and will NOT be wrapped)
     */
    public function writeLine($x, $y, $line)
    {
        $this->convertCoordinatesFromUserSpace($x, $y);
        $y -= $this->getFontSize();
        $this->drawText($line, $x, $y, 'UTF-8');
    }

    /**
     * Word-wrap a text to a certain width, using the current font properties
     *
     * @param string $text text to wrap (can already contain some newlines)
     * @param string $wrapWidth width (in the given units) to wrap the text to
     * @return string the same text but with newlines inserted at the specified $wrapWidth
     */
    public function wordWrapText($text, $wrapWidth)
    {
        $wrappedText = '';
        foreach (explode(PHP_EOL, $text) as $line) {
            $words = explode(' ', $line);
            $currentLine = array_shift($words);
            while (count($words) > 0) {
                $word = array_shift($words);
                if ($this->getTextWidth($currentLine . ' ' . $word) > $wrapWidth) {
                    $wrappedText .= PHP_EOL . $currentLine;
                    $currentLine = $word;
                } else {
                    $currentLine .= ' ' . $word;
                }
            }
            $wrappedText .= PHP_EOL . $currentLine;
        }
        return ltrim($wrappedText, PHP_EOL);
    }

    /**
     * Get the line height (the offset between consecutive lines)
     *
     * @return float distance between consecutive lines in the given units
     */
    public function getLineHeight()
    {
        $lineHeight = $this->getFontSize() * 1.2 * $this->getLineSpacing();
        $this->convertFromPoints($lineHeight);
        return $lineHeight;
    }

    /**
     * Calculates how much (horizontal) space a text would use if written to the page, using
     * the current font properties
     *
     * @param string $text text to calculate the width for (should not contain newlines)
     * @return float width (in the given units) that the text would use if written to the page
     */
    public function getTextWidth($text)
    {
        $font = $this->getFont();
        $fontSize = $this->getFontSize();
        $text = iconv('UTF-8', 'UTF-16BE', $text);
        $chars = array();
        for ($i = 0; $i < strlen($text); $i++) {
            $chars[] = (ord($text[$i++]) << 8) | ord($text[$i]);
        }
        $glyphs = $font->glyphNumbersForCharacters($chars);
        $widths = $font->widthsForGlyphs($glyphs);
        $textWidth = $fontSize * array_sum($widths) / $font->getUnitsPerEm();
        $this->convertFromPoints($textWidth);
        return $textWidth;
    }
}
