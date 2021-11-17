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
    public function reset()
    {
        $this->bag = $this->original;
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

        $sids = [1, 2, 3];
        $sidValidator = new IdValidator($sids);
        $seeder->table('{{surveys}}')->columns(
            [
                'sid' => $faker->valid($sidValidator)->randomElement($sids),
                'owner_id' => 1
            ]
        )->rowQuantity(count($sids));

        $sidValidator = new IdValidator($sids);
        $gids = [4, 5, 6, 7, 8];
        $gidValidator = new IdValidator($gids);
        $seeder->table('{{groups}}')->columns(
            [
                'gid' => $faker->valid($gidValidator)->randomElement($gids),
                'sid' => fn () => rand(1, 99999)
            ]
        )->rowQuantity(count($gids));
        $sidValidator->reset();
        $gidValidator->reset();
        $seeder->refill();
        return;

        $qids = [1, 2, 3, 4, 5, 6, 7];
        $seeder->table('{{questions}}')->columns(
            [
                'qid' => $faker->valid(true)->randomElement($qids),
                'sid' => $faker->valid(true)->randomElement($sids),
                'gid' => $faker->valid(true)->randomElement($gids),
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
