<?php
namespace SimplePdf;

/**
 * Extension of ZendPdf\Page which allows using arbitrary units (e.g. inches or centimeters) and works from
 * top to bottom, instead of default PDF/PostScript geometry. It also adds some basic formatting functionality,
 * like word wrap, margins and text alignment.
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
     * Margin on the left side of the page, stored in points
     *
     * @var float
     */
    protected $marginLeft = 0;

    /**
     * Margin on the right side of the page, stored in points
     *
     * @var float
     */
    protected $marginRight = 0;

    /**
     * Margin on the top edge, stored in points
     *
     * @var float
     */
    protected $marginTop = 0;

    /**
     * Margin on the bottom edge, stored in points
     *
     * @var float
     */
    protected $marginBottom = 0;

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
     * @return \SimplePdf\Page this page
     */
    public function setUnitConversion($unitConversion)
    {
        $this->unitConversion = $unitConversion;
        return $this;
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
     * Convert (x,y)-coordinates in the user space (top to bottom, in the given units, relative to the margins)
     * to native geometry (points, bottom to top)
     *
     * @param float &$x x-coordinate (in the given units, from the left margin) to convert, BY REF
     * @param float &$y y-coordinate (in the given units, from the top margin) to convert, BY REF
     */
    public function convertCoordinatesFromUserSpace(&$x, &$y)
    {
        $x += $this->getLeftMargin();
        $this->convertToPoints($x);

        $y += $this->getTopMargin();
        $y = $this->getHeight() - $y;
        $this->convertToPoints($y);
    }

    /**
     * Convert (x,y)-coordinates in native geometry (points, bottom to top) to the
     * user space (top to bottom, in the given units, relative to the margins)
     *
     * @param float &$x x-coordinate (in points, from the left) to convert, BY REF
     * @param float &$y y-coordinate (in points, from the bottom) to convert, BY REF
     */
    public function convertCoordinatesToUserSpace(&$x, &$y)
    {
        $this->convertFromPoints($x);
        $x -= $this->getLeftMargin();

        $this->convertFromPoints($y);
        $y = $this->getHeight() - $y;
        $x -= $this->getLeftMargin();
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
     * @return \SimplePdf\Page this page
     */
    public function setLineSpacing($lineSpacing)
    {
        $this->lineSpacing = $lineSpacing;
        return $this;
    }

    /**
     * Get the left margin of the page, in the given units
     *
     * @return float left page margin, in the given units
     */
    public function getLeftMargin()
    {
        $marginLeft = $this->marginLeft;
        $this->convertFromPoints($marginLeft);
        return $marginLeft;
    }

    /**
     * Set a new left margin, in the given units
     *
     * @param float $margin new left margin, in the given units
     * @return \SimplePdf\Page this page
     */
    public function setLeftMargin($margin)
    {
        $this->convertToPoints($margin);
        $this->marginLeft = $margin;
        return $this;
    }

    /**
     * Get the right margin of the page, in the given units
     *
     * @return float right page margin, in the given units
     */
    public function getRightMargin()
    {
        $marginRight = $this->marginRight;
        $this->convertFromPoints($marginRight);
        return $marginRight;
    }

    /**
     * Set a new right margin, in the given units
     *
     * @param float $margin new right margin, in the given units
     * @return \SimplePdf\Page this page
     */
    public function setRightMargin($margin)
    {
        $this->convertToPoints($margin);
        $this->marginRight = $margin;
        return $this;
    }

    /**
     * Get the top margin of the page, in the given units
     *
     * @return float top page margin, in the given units
     */
    public function getTopMargin()
    {
        $marginTop = $this->marginTop;
        $this->convertFromPoints($marginTop);
        return $marginTop;
    }

    /**
     * Set a new top margin, in the given units
     *
     * @param float $margin new top margin, in the given units
     * @return \SimplePdf\Page this page
     */
    public function setTopMargin($margin)
    {
        $this->convertToPoints($margin);
        $this->marginTop = $margin;
        return $this;
    }

    /**
     * Get the bottom margin of the page, in the given units
     *
     * @return float bottom page margin, in the given units
     */
    public function getBottomMargin()
    {
        $marginBottom = $this->marginBottom;
        $this->convertFromPoints($marginBottom);
        return $marginBottom;
    }

    /**
     * Set a new bottom margin, in the given units
     *
     * @param float $margin new bottom margin, in the given units
     * @return \SimplePdf\Page this page
     */
    public function setBottomMargin($margin)
    {
        $this->convertToPoints($margin);
        $this->marginBottom = $margin;
        return $this;
    }

    /**
     * Set new margin, in the given units
     *
     * @param float $marginLeft new left margin, in the given units
     * @param float $marginRight new right margin, in the given units
     * @param float $marginTop new top margin, in the given units
     * @param float $marginBottom new bottom margin, in the given units
     * @return \SimplePdf\Page this page
     */
    public function setMargins($marginLeft, $marginRight, $marginTop, $marginBottom)
    {
        $this->setLeftMargin($marginLeft);
        $this->setRightMargin($marginRight);
        $this->setTopMargin($marginTop);
        $this->setBottomMargin($marginBottom);
        return $this;
    }

    /**
     * Set a new margin for all sides, in the given units
     *
     * @param float $margin new margin to set on all sides, in the given units
     * @return \SimplePdf\Page this page
     */
    public function setAllMargins($margin)
    {
        $this->setMargins($margin, $margin, $margin, $margin);
        return $this;
    }

    /**
     * Get the height (in the given units) of the page area excluding the set margins (if any)
     *
     * @return float page height in the given units, excluding margins
     */
    public function getInnerHeight()
    {
        return $this->getHeight() - $this->getTopMargin() - $this->getBottomMargin();
    }

    /**
     * Get the width (in the given units) of the page area excluding the set margins (if any)
     *
     * @return float page width in the given units, excluding margins
     */
    public function getInnerWidth()
    {
        return $this->getWidth() - $this->getLeftMargin() - $this->getRightMargin();
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

        return parent::setFont($font, $fontSize);
    }

    /**
     * Change the font size, without changing the font family
     *
     * @param float $fontSize new font size to use
     */
    public function setFontSize($fontSize)
    {
        return parent::setFont($this->getFont(), $fontSize);
    }

    /**
     * Draw (and wrap) algined text within a text block, by default within the page margins
     *
     * @param string $text text to draw; will be wrapped, but can also contain newlines already
     * @param float $y vertical offset (from the top, in the given units) to start drawing text
     * @param float $alignment where to align text within the block, defaults to left alignment
     * @param float $x1 left boundary of the text block, defaults to left page margin
     * @param float $x2 right boundary of the text block, defaults to right page margin
     * @return \SimplePdf\Page this page
     */
    public function drawTextBlock($text, $y = 0, $alignment = self::TEXT_ALIGN_LEFT, $x1 = 0, $x2 = null)
    {
        if (is_null($x2)) {
            $x2 = $this->getInnerWidth();
        }

        $width = $x2 - $x1;
        $text = $this->wordWrapText($text, $width);
        $x = $x1 + $alignment * $width;

        $lineHeight = $this->getLineHeight();
        $lines = explode(PHP_EOL, $text);
        foreach ($lines as $index => $line) {
            if (empty($line)) {
                continue;
            }

            $offset = ($alignment == 0) ? 0 : -$alignment * $this->getTextWidth($line);
            $this->drawText($line, $x + $offset, $y + $index * $lineHeight);
        }

        return $this;
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
        $text = iconv('WINDOWS-1252', 'UTF-16BE', $text);
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

    /**
     * Wrapper taking custom units and margins into account
     * @see \ZendPdf\Page::drawCircle() for documentation
     */
    public function drawCircle($x, $y, $radius, $param4 = null, $param5 = null, $param6 = null)
    {
        // Don't convert units and call parent::drawCircle(), since it calls drawEllipse which will convert units twice!
        return $this->drawEllipse($x - $radius, $y - $radius, $x + $radius, $y + $radius, $param4, $param5, $param6);
    }

    /**
     * Wrapper taking custom units and margins into account
     * @see \ZendPdf\Page::drawEllipse() for documentation
     */
    public function drawEllipse($x1, $y1, $x2, $y2, $param5 = null, $param6 = null, $param7 = null)
    {
        $this->convertCoordinatesFromUserSpace($x1, $y1);
        $this->convertCoordinatesFromUserSpace($x2, $y2);
        list($y1, $y2) = array($y2, $y1);
        return parent::drawEllipse($x1, $y1, $x2, $y2, $param5, $param6, $param7);
    }

    public function drawImage(\ZendPdf\Resource\Image\AbstractImage $image, $x1, $y1, $x2, $y2)
    {
        $this->convertCoordinatesFromUserSpace($x1, $y1);
        $this->convertCoordinatesFromUserSpace($x2, $y2);
        list($y1, $y2) = array($y2, $y1);
        return parent::drawImage($image, $x1, $y1, $x2, $y2);
    }

    /**
     * Wrapper taking custom units and margins into account
     * @see \ZendPdf\Page::drawLine() for documentation
     */
    public function drawLine($x1, $y1, $x2, $y2)
    {
        $this->convertCoordinatesFromUserSpace($x1, $y1);
        $this->convertCoordinatesFromUserSpace($x2, $y2);
        list($y1, $y2) = array($y2, $y1);
        return parent::drawLine($x1, $y1, $x2, $y2);
    }

    /**
     * Wrapper taking custom units and margins into account
     * @see \ZendPdf\Page::drawPolygon() for documentation
     */
    public function drawPolygon($x, $y, $fillType = \ZendPdf\Page::SHAPE_DRAW_FILL_AND_STROKE, $fillMethod = \ZendPdf\Page::FILL_METHOD_NON_ZERO_WINDING)
    {
        foreach ($x as $index => &$value) {
            $this->convertCoordinatesFromUserSpace($value, $y[$index]);
        }
        $x = array_reverse($x, true);
        $y = array_reverse($y, true);
        return parent::drawPolygon($x, $y, $fillType, $fillMethod);
    }

    /**
     * Wrapper taking custom units and margins into account
     * @see \ZendPdf\Page::drawRectangle() for documentation
     */
    public function drawRectangle($x1, $y1, $x2, $y2, $fillType = \ZendPdf\Page::SHAPE_DRAW_FILL_AND_STROKE)
    {
        $this->convertCoordinatesFromUserSpace($x1, $y1);
        $this->convertCoordinatesFromUserSpace($x2, $y2);
        list($y1, $y2) = array($y2, $y1);
        return parent::drawRectangle($x1, $y1, $x2, $y2, $fillType);
    }

    /**
     * Wrapper taking custom units and margins into account
     * @see \ZendPdf\Page::drawRoundedRectangle() for documentation
     */
    public function drawRoundedRectangle($x1, $y1, $x2, $y2, $radius, $fillType = \ZendPdf\Page::SHAPE_DRAW_FILL_AND_STROKE)
    {
        $this->convertCoordinatesFromUserSpace($x1, $y1);
        $this->convertCoordinatesFromUserSpace($x2, $y2);
        list($y1, $y2) = array($y2, $y1);
        $this->convertToPoints($radius);
        return parent::drawRoundedRectangle($x1, $y1, $x2, $y2, $radius, $fillType);
    }

    /**
     * Wrapper taking custom units and margins into account
     * @see \ZendPdf\Page::drawText() for documentation
     */
    public function drawText($text, $x, $y, $charEncoding = 'WINDOWS-1252')
    {
        $this->convertCoordinatesFromUserSpace($x, $y);
        $y -= $this->getFontSize();
        return parent::drawText($text, $x, $y, $charEncoding);
    }

    /**
     * Wrapper taking custom units and margins into account
     * @see \ZendPdf\Page::getHeight() for documentation
     */
    public function getHeight()
    {
        $height = parent::getHeight();
        $this->convertFromPoints($height);
        return $height;
    }

    /**
     * Wrapper taking custom units and margins into account
     * @see \ZendPdf\Page::getWidth() for documentation
     */
    public function getWidth()
    {
        $width = parent::getWidth();
        $this->convertFromPoints($width);
        return $width;
    }
}
