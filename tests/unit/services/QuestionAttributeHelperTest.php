<?php

namespace ls\tests;

use LimeSurvey\Models\Services\QuestionAttributeHelper;

class QuestionAttributeHelperTest extends TestBaseClass
{
    /**
     * Categories given as the translated name of a core category are replaced with the untranslated name,
     * other categories are kept as they are.
     */
    public function testUntranslateCoreCategories()
    {
        $originalLanguage = App()->getLanguage();
        App()->setLanguage('de');
        try {
            $this->assertNotSame('Logic', gT('Logic'));
            $attributes = [
                'translated' => ['category' => gT('Logic')],
                'untranslated' => ['category' => 'Logic'],
                'custom' => ['category' => 'My plugin'],
                'missing' => [],
            ];
            $result = (new QuestionAttributeHelper())->untranslateCoreCategories($attributes);
        } finally {
            App()->setLanguage($originalLanguage);
        }
        $this->assertSame('Logic', $result['translated']['category']);
        $this->assertSame('Logic', $result['untranslated']['category']);
        $this->assertSame('My plugin', $result['custom']['category']);
        $this->assertArrayNotHasKey('category', $result['missing']);
    }
}
