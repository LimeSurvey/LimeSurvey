<?php

namespace ls\tests\unit\helpers;

use ls\tests\TestBaseClass;

/**
 * Participant data exported before DB version 800 uses the 'blacklisted' column name,
 * which was renamed to 'blocklisted'. Importing such files must keep the value.
 */
class XMLImportTokensLegacyColumnTest extends TestBaseClass
{
    /**
     * Import a participant XML file that uses the legacy 'blacklisted' column and
     * check that the value ends up in the 'blocklisted' column.
     *
     * @return void
     */
    public function testLegacyBlacklistedColumnIsImportedAsBlocklisted()
    {
        // Survey archive with an existing participant table
        self::importSurvey(self::$surveysFolder . '/survey_archive_993688_participantBlocklist.lsa');

        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<document>
 <LimeSurveyDocType>Tokens</LimeSurveyDocType>
 <DBVersion>708</DBVersion>
 <tokens>
  <fields>
   <fieldname>tid</fieldname>
   <fieldname>firstname</fieldname>
   <fieldname>token</fieldname>
   <fieldname>blacklisted</fieldname>
  </fields>
  <rows>
   <row>
    <tid><![CDATA[2]]></tid>
    <firstname><![CDATA[Legacy]]></firstname>
    <token><![CDATA[legacy1]]></token>
    <blacklisted><![CDATA[Y]]></blacklisted>
   </row>
  </rows>
 </tokens>
</document>
XML;
        $filename = tempnam(sys_get_temp_dir(), 'lst');
        file_put_contents($filename, $xml);

        \Yii::app()->loadHelper('admin.import');
        $result = \XMLImportTokens($filename, self::$surveyId);
        unlink($filename);

        $this->assertSame(1, $result['tokens'], 'Participant was imported: ' . json_encode($result['warnings']));
        $token = \Token::model(self::$surveyId)->findByPk(2);
        $this->assertNotNull($token);
        $this->assertSame('Y', $token->blocklisted);
    }
}
