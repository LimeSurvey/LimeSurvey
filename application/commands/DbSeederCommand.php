<?php

use \tebazil\yii1seeder\Seeder;

class IdValidator
{
    private $bag;
    private $original;
    public function __construct($bag)
    {
        $this->bag = $bag;
        $this->original = $bag;
    }
    public function __invoke($nr)
    {
        if (!isset($this->bag[$nr - 1])) {
            return false;
        } else {
            unset($this->bag[$nr - 1]);
            return true;
        }
    }
}

class DbSeederCommand extends CConsoleCommand
{
    /**
     * @return void
     */
    public function run($args)
    {
        $seeder = new Seeder();
        $generator = $seeder->getGeneratorConfigurator();
        $faker = $generator->getFakerConfigurator();

        // Generate surveys
        $sids = [1, 2, 3];
        $sidValidator = new IdValidator($sids);
        $seeder->table('{{surveys}}')->columns(
            [
                'sid' => $faker->valid($sidValidator)->randomElement($sids),
                'owner_id' => 1,
                'language' => 'en'
            ]
        )->rowQuantity(count($sids));

        // Generate survey text
        $sidValidator = new IdValidator($sids);
        $seeder->table('{{surveys_languagesettings}}')->columns(
            [
                'surveyls_survey_id' => $faker->valid($sidValidator)->randomElement($sids),
                'surveyls_language' => 'en'
            ]
        )->rowQuantity(count($sids));

        // Generate groups
        $gids = [1, 2, 3, 4, 5];
        $gidValidator = new IdValidator($gids);
        $seeder->table('{{groups}}')->columns(
            [
                'gid' => $faker->valid($gidValidator)->randomElement($gids),
                'sid' => $faker->randomElement($sids)
            ]
        )->rowQuantity(count($gids));

        // Generate questions
        $qids = [1, 2, 3, 4, 5, 6, 7];
        $qidValidator = new IdValidator($qids);
        $seeder->table('{{questions}}')->columns(
            [
                'qid' => $faker->valid($qidValidator)->randomElement($qids),
                'sid' => $faker->randomElement($sids),
                'gid' => $faker->randomElement($gids),
            ]
        )->rowQuantity(count($qids));

        $i = 0;
        $seeder->table('{{answers}}')->columns(
            [
                'aid',
                'qid' => function () use (&$i) {
                    return $i++;
                },
                'code' => $faker->uuid,
                'sortorder' => function () use (&$i) {
                    return $i++;
                },
                'assessment_value' => fn () => rand(1, 10)
            ]
        )->rowQuantity(30);

        $seeder->refill();
    }
}
