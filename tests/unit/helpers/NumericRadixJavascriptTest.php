<?php

namespace ls\tests\unit\helpers;

use PHPUnit\Framework\TestCase;

/**
 * Run the real survey JavaScript in Node with each decimal separator.
 *
 * @see https://bugs.limesurvey.org/view.php?id=16878
 * @group em
 */
class NumericRadixJavascriptTest extends TestCase
{
    private const NODE_SCRIPT = <<<'JS'
        const fs = require('fs'), vm = require('vm');
        const [root, totalsum, radix, value] = process.argv.slice(1);
        let rewritten = null;
        const field = {
            on: () => field,
            trigger: () => field,
            val: (newValue) => {
                if (newValue !== undefined) {
                    rewritten = newValue;
                }
                return field;
            }
        };
        const context = {
            $: () => field,
            window: {},
            document: {getElementById: (id) => id.startsWith('relevance') ? {value: 1} : {value: value}},
            console: {ls: {log() {}, warn() {}, error() {}}},
            setTimeout: (callback) => { callback(); return 1; },
            clearTimeout() {},
            LSvar: {sLEMradix: radix, bFixNumAuto: 1, bNumRealValue: 0},
            bFixNumAuto: 1,
            numRegex: /[^-.,0-9]/g,
            LEMradix: radix,
            LEMmode: 'survey',
            LEMalias2varName: {test: 'javaX1'},
            LEMvarNameAttr: {javaX1: {jsName: 'javaX1', jsName_on: 'javaX1', sgqa: 'X1', qid: '1', type: 'N', default: '', rowdivid: '', onlynum: 1, gseq: 0}}
        };
        vm.createContext(context);
        ['node_modules/decimal.js/decimal.js', 'assets/packages/expressions/em_javascript.js', totalsum].forEach(
            (file) => vm.runInContext(fs.readFileSync(root + '/' + file, 'utf8'), context)
        );
        context.checkconditions = () => {};
        const lemval = context.LEMval('test.NAOK');
        context.fixnum_checkconditions(value, 'X1', 'text', 'keyup');
        const total = new context.Decimal(2).plus(context.getSumValue(value)).toString();
        console.log(JSON.stringify({lemval: lemval, rewritten: rewritten, total: total}));
JS;

    /**
     * @return array[] radix, typed value, LEMval result, value written in the field, total with another cell at 2
     */
    public static function radixProvider(): array
    {
        return [
            'period: comma is refused'    => ['.', '1,1', '1,1', null, '2'],
            'period: period is a number'  => ['.', '1.1', 1.1, null, '3.1'],
            'period: integer keeps zeros' => ['.', '007', '007', '7', '9'],
            'comma: comma is a number'    => [',', '1,1', 1.1, null, '3.1'],
            'comma: period is converted'  => [',', '1.1', 1.1, '1,1', '3.1'],
            'period: no digit yet'        => ['.', '-.', '-.', null, '2'],
            'comma: no digit yet'         => [',', '-,', '-,', null, '2'],
        ];
    }

    /**
     * @dataProvider radixProvider
     * @param string $radix
     * @param string $value
     * @param string|float $lemval
     * @param string|null $rewritten
     * @param string $total
     * @return void
     */
    public function testValueFollowsRadix(string $radix, string $value, $lemval, ?string $rewritten, string $total): void
    {
        foreach (['array-totalsum.js', 'array-totalsum.min.js'] as $file) {
            $result = $this->runNode('assets/packages/questions/arraynumeric/scripts/' . $file, $radix, $value);
            $this->assertSame($lemval, $result['lemval'], $file . ': LEMval');
            $this->assertSame($rewritten, $result['rewritten'], $file . ': value written in the field');
            $this->assertSame($total, $result['total'], $file . ': total');
        }
    }

    /**
     * @param string $totalsum
     * @param string $radix
     * @param string $value
     * @return array
     */
    private function runNode(string $totalsum, string $radix, string $value): array
    {
        $arguments = [self::NODE_SCRIPT, dirname(__DIR__, 3), $totalsum, $radix, $value];
        exec('node -e ' . implode(' ', array_map('escapeshellarg', $arguments)) . ' 2>&1', $output, $exitCode);
        $this->assertSame(0, $exitCode, implode("\n", $output));
        return json_decode(end($output), true);
    }
}
