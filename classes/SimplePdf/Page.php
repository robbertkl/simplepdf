<?php
namespace SimplePdf;

/**
 * Extension of ZendPdf\Page which allows uses centimeters and works from
 * top to bottom, instead of default PDF/PostScript geometry. It also adds
 * some text formatting utilities, like word wrap and text alignment.
 *
 * @author Robbert Klarenbeek <robbertkl@renbeek.nl>
 * @copyright 2013 Robbert Klarenbeek
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class Page extends \ZendPdf\Page
{
    /**
     * Conversion factor from PDF/PostScript points to centimeters
     * A point is 1/72 of an inch
     * An inch is 2.54 centimeters
     * So a centimeter is 72/2.54 points
     *
     * @var float
     */
    const CM_TO_POINT = 28.34645669291339;

    /**
     * Constant for left aligned text
     *
     * @var string
     */
    const TEXT_ALIGN_LEFT = 'left';

    /**
     * Constant for right aligned text
     *
     * @var string
     */
    const TEXT_ALIGN_RIGHT = 'right';

    /**
     * How far lines should be apart vertically, with 1.0 being 'normal' distance
     *
     * @var float
     */
    protected $lineSpacing = 1.0;

    /**
     * Create a new PDF page, with A4 size and default font Helvetica, size 12
     */
    public function __construct()
    {
        parent::__construct(self::SIZE_A4);
        $this->setFont(\ZendPdf\Font::fontWithName(\ZendPdf\Font::FONT_HELVETICA), 12);
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
     * Get page width in centimeters
     *
     * @return float width of the page in centimeters
     */
    public function getWidth()
    {
        return parent::getWidth() / self::CM_TO_POINT;
    }

    /**
     * Get page height in centimeters
     *
     * @return float height of the page in centimeters
     */
    public function getHeight()
    {
        return parent::getHeight() / self::CM_TO_POINT;
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
     * @param float $x1 x coordinate (in centimeters) of the point from where to draw the line
     * @param float $y1 y coordinate (in centimeters) of the point from where to draw the line
     * @param float $x2 x coordinate (in centimeters) of the point to where to draw the line
     * @param float $y2 y coordinate (in centimeters) of the point to where to draw the line
     */
    public function drawLine($x1, $y1, $x2, $y2)
    {
        $x1 *= self::CM_TO_POINT;
        $y1 = ($this->getHeight() - $y1) * self::CM_TO_POINT;
        $x2 *= self::CM_TO_POINT;
        $y2 = ($this->getHeight() - $y2) * self::CM_TO_POINT;
        parent::drawLine($x1, $y1, $x2, $y2);
    }

    /**
     * Write a (multiline / optionally wrapping) text to the page
     *
     * @param float $x x coordinate (in centimeters) of the anchor point of the text
     * @param float $y y coordinate (in centimeters) of the anchor point of the text
     * @param string $text text to write to the PDF (can contain newlines)
     * @param string $align either Page::TEXT_ALIGN_LEFT or Page::TEXT_ALIGN_RIGHT, for left or right alignment respectively
     * @param float $wrapWidth width (in centimeters) to wrap text at, or leave out for no wrapping
     */
    public function writeText($x, $y, $text, $align = self::TEXT_ALIGN_LEFT, $wrapWidth = 0)
    {
        if ($wrapWidth > 0) {
            $text = $this->wordWrapText($text, $wrapWidth);
        }

        $lineHeight = $this->getLineHeight();
        foreach (explode(PHP_EOL, $text) as $index => $line) {
            if (empty($line)) {
                continue;
            }

            $alignOffset = ($align == self::TEXT_ALIGN_RIGHT ? -$this->getTextWidth($line) : 0);
            $this->writeLine($x + $alignOffset, $y + $index * $lineHeight, $line);
        }
    }

    /**
     * Write a single line of text to the page
     *
     * @param float $x x coordinate (in centimeters) of the top-left corner where the text should start
     * @param float $y y coordinate (in centimeters) of the top-left corner where the text should start
     * @param string $line line to write to the page, should not contain newlines (and will NOT be wrapped)
     */
    public function writeLine($x, $y, $line)
    {
        $x = $x * self::CM_TO_POINT;
        $y = ($this->getHeight() - $y) * self::CM_TO_POINT - $this->getFontSize();
        $this->drawText($line, $x, $y, 'UTF-8');
    }

    /**
     * Word-wrap a text to a certain width, using the current font properties
     *
     * @param string $text text to wrap (can already contain some newlines)
     * @param string $wrapWidth width (in centimeters) to wrap the text to
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
     * @return float distance between consecutive lines in centimeters
     */
    public function getLineHeight()
    {
        return $this->getFontSize() * 1.2 * $this->getLineSpacing() / self::CM_TO_POINT;
    }

    /**
     * Calculates how much (horizontal) space a text would use if written to the page, using
     * the current font properties
     *
     * @param string $text text to calculate the width for (should not contain newlines)
     * @return float width (in centimeters) that the text would use if written to the page
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
        return $fontSize * array_sum($widths) / $font->getUnitsPerEm() / self::CM_TO_POINT;
    }
}
