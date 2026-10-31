<?php

namespace ls\tests\unit\services\SurveyStatistics\Charts\Questions;

use LimeSurvey\Models\Services\SurveyStatistics\Charts\Questions\QuestionStatistics;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Labels of subquestions and answer options in the statistics charts.
 */
class QuestionStatisticsChoiceLabelTest extends TestCase
{
    /**
     * Calls the private QuestionStatistics::getChoiceLabel().
     *
     * @param string|null $text Subquestion or answer option text
     * @param string|null $themeName Question theme name
     * @return string
     */
    private function getChoiceLabel(?string $text, ?string $themeName): string
    {
        $class = new ReflectionClass(QuestionStatistics::class);
        $method = $class->getMethod('getChoiceLabel');
        $method->setAccessible(true);
        return $method->invoke($class->newInstanceWithoutConstructor(), $text, $themeName);
    }

    /**
     * @testdox Image select: An image inserted as HTML is labelled with its image URL
     */
    public function testImageSelectHtmlImageReturnsSource()
    {
        $this->assertSame(
            '/upload/surveys/123456/images/a&b.jpeg',
            $this->getChoiceLabel(
                '<p><img alt="Red car" src="/upload/surveys/123456/images/a&amp;b.jpeg" /></p>',
                'image_select-multiplechoice'
            )
        );
        $this->assertSame(
            'https://example.com/image.png',
            $this->getChoiceLabel("<img src='https://example.com/image.png'>", 'image_select-listradio')
        );
        $this->assertSame(
            '/upload/car.jpeg',
            $this->getChoiceLabel(
                '<img data-src="/upload/placeholder.png" src="/upload/car.jpeg" />',
                'image_select-multiplechoice'
            )
        );
    }

    /**
     * @testdox Image select: A bare image URL is kept as label
     */
    public function testImageSelectBareUrlIsKept()
    {
        $this->assertSame(
            '/upload/surveys/123456/images/car.jpeg',
            $this->getChoiceLabel('/upload/surveys/123456/images/car.jpeg', 'image_select-listradio')
        );
    }

    /**
     * @testdox Image select: Text without an image is flattened
     */
    public function testImageSelectTextWithoutImageIsFlattened()
    {
        $this->assertSame('Red car', $this->getChoiceLabel('<b>Red car</b>', 'image_select-multiplechoice'));
    }

    /**
     * @testdox Other question themes: An image inserted as HTML is not turned into its URL
     */
    public function testOtherThemesAreFlattened()
    {
        $this->assertSame(
            'Red car',
            $this->getChoiceLabel('<img src="/upload/car.jpeg" alt="" />Red car', 'multiplechoice')
        );
        $this->assertSame('Red car', $this->getChoiceLabel('<b>Red car</b>', null));
    }
}
