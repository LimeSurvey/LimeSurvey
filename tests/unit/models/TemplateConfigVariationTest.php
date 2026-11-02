<?php

namespace ls\tests;

use TemplateConfiguration;
use Yii;

/**
 * Tests for the colour variation handling of extended themes (mantis #14980).
 */
class TemplateConfigVariationTest extends TestBaseClass
{
    /** @var string[] packages registered by the tests */
    private $packageNames = [];

    /**
     * Remove the packages registered by the tests.
     */
    public function tearDown(): void
    {
        foreach ($this->packageNames as $packageName) {
            unset(App()->clientScript->packages[$packageName]);
        }
        $this->packageNames = [];
        parent::tearDown();
    }

    /**
     * The variation of a child theme takes the place of the variations in the mother package,
     * including a variation that another child already added there.
     */
    public function testVariationReplacesMotherVariationInPlace()
    {
        $child = $this->getChildTemplate([
            'css/variations/theme_apple.css',
            'css/base.css',
            'css/custom.css',
            'css/variations/theme_grape.css',
        ]);

        $settings = $this->replaceAncestorVariations($child, ['css/variations/theme_mango.css', 'css/base.css']);

        $this->assertSame([1 => 'css/base.css'], $settings, 'The variation must be loaded with the mother package.');
        $this->assertSame(
            ['css/variations/theme_mango.css', 'css/base.css', 'css/custom.css'],
            array_values(App()->clientScript->packages[$child->oMotherTemplate->sPackageName]['css']),
            'Only the variation of the child must be loaded, at the place of the mother variation.'
        );
    }

    /**
     * A variation that can't be found leaves the variation of the mother template in place.
     */
    public function testMissingVariationKeepsMotherVariation()
    {
        $motherCss = ['css/variations/theme_apple.css', 'css/base.css'];
        $child = $this->getChildTemplate($motherCss);

        $settings = $this->replaceAncestorVariations($child, ['css/variations/theme_missing.css']);

        $this->assertSame(['css/variations/theme_missing.css'], $settings);
        $this->assertSame($motherCss, App()->clientScript->packages[$child->oMotherTemplate->sPackageName]['css']);
    }

    /**
     * removeFileFromPackage() removes every entry of the file.
     */
    public function testRemoveFileFromPackageRemovesAllEntries()
    {
        $this->addPackage('test-14980-duplicates', 'application', ['a.css', 'b.css', 'a.css']);

        App()->clientScript->removeFileFromPackage('test-14980-duplicates', 'css', 'a.css');

        $this->assertSame(['b.css'], array_values(App()->clientScript->packages['test-14980-duplicates']['css']));
    }

    /**
     * Get a child template whose mother template is fruity_twentythree with the given css files.
     * The child template folder doesn't contain any variation file.
     * @param string[] $motherCss css files of the mother package
     * @return TemplateConfiguration
     */
    private function getChildTemplate(array $motherCss)
    {
        Yii::setPathOfAlias(
            'survey.template-test14980mother.path',
            Yii::app()->getConfig('standardthemerootdir') . DIRECTORY_SEPARATOR . 'fruity_twentythree'
        );
        $mother = new TemplateConfiguration();
        $mother->sPackageName = 'survey-template-test14980mother';
        $this->addPackage($mother->sPackageName, 'survey.template-test14980mother.path', $motherCss);

        $child = new TemplateConfiguration();
        $child->sPackageName = 'survey-template-test14980child';
        $child->path = Yii::app()->getConfig('tempdir') . DIRECTORY_SEPARATOR;
        $child->oMotherTemplate = $mother;
        return $child;
    }

    /**
     * Register a package with the given css files.
     * @param string $packageName name of the package
     * @param string $basePath path alias of the package
     * @param string[] $css css files of the package
     * @return void
     */
    private function addPackage($packageName, $basePath, array $css)
    {
        App()->clientScript->addPackage($packageName, ['basePath' => $basePath, 'css' => $css]);
        $this->packageNames[] = $packageName;
    }

    /**
     * Call the protected TemplateConfig::replaceAncestorVariations().
     * @param TemplateConfiguration $template the child template
     * @param array $settings css files of the child template
     * @return array
     */
    private function replaceAncestorVariations(TemplateConfiguration $template, array $settings)
    {
        $method = new \ReflectionMethod($template, 'replaceAncestorVariations');
        $method->setAccessible(true);
        return $method->invoke($template, $settings);
    }
}
