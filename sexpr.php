<?php

$sc = <<<LISP
(test-class remotecontrol_handle
  (constructor (new AdminController "dummyid"))
  (test-method add_participants
      (set (participant (list (map ("firstname" "John")))))
      (set (surveyId 1))
      (set (sessionKey "abc123"))
      (arguments sessionKey surveyId participant 'false)
      (result (list (map ("firstname" "John")))))
)
LISP;

abstract class SexprBase
{
    /**
     * @return SplStack<string>
     */
    public function parse(string $sc)
    {
        // Remove comments
        $sc = preg_replace('/;.*$/m', '', $sc);
        // Normalize string
        $sc = trim((string) preg_replace('/[\t\n\r\s]+/', ' ', $sc));
        $current = new SplStack();
        $base = $current;
        $prev = null;
        $history = new SplStack();
        $buffer = '';
        $inside_quote = 0;
        for ($i = 0; $i < strlen($sc); $i++) {
            $char = $sc[$i];
            if ($char === '(') {
                $prev = $current;
                $history->push($current);
                $current = new SplStack();
                $prev->push($current);
            } elseif ($char === ')') {
                if ($buffer) {
                    $current->push($buffer);
                    $buffer = '';
                }
                $current = $history->pop();
            } elseif ($char === '"') {
                $inside_quote = 1 - $inside_quote;
            } elseif ($char === ' ' && !$inside_quote) {
                if ($buffer !== '') {
                    $current->push($buffer);
                    $buffer = '';
                }
            } else {
                $buffer .= $char;
            }
        } 
        return $base;
    }
}

class MathSexpr extends SexprBase
{
    /**
     * @param SplStack<mixed>|string $sexpr
     */
    public function mathEval($sexpr): int
    {
        if (is_string($sexpr)) {
            return intval($sexpr);
        }
        $result = 0;
        $op = $sexpr->shift();
        if ($op instanceof SplStack) {
            return $this->mathEval($op);
        }
        switch ($op) {
            case '+':
                $arg1 = $sexpr->shift();
                $arg2 = $sexpr->shift();
                return $this->mathEval($arg1) + $this->mathEval($arg2);
            case '-':
                $arg1 = $sexpr->shift();
                $arg2 = $sexpr->shift();
                return $this->mathEval($arg1) - $this->mathEval($arg2);
            default:
                return intval($op);
        }
    }
}

class PhpunitSexpr extends SexprBase
{
    /**
     * @param SplStack<mixed> $sexp
     * @return ?SplStack<mixed>
     */
    public function findFirst(SplStack $sexp, string $symbol): ?SplStack
    {
        foreach ($sexp as $s) {
            if ($s instanceof SplStack) {
                if ($s->bottom() === $symbol) {
                    return $s;
                }
            }
        }
        return null;
    }

    /**
     * @param SplStack<mixed> $sexp
     * @return array<mixed>
     */
    public function findAll(SplStack $sexp, string $symbol): array
    {
        $result = [];
        foreach ($sexp as $s) {
            if ($s instanceof SplStack) {
                if ($s->bottom() === $symbol) {
                    $result[] = $s;
                }
            }
        }
        return $result;
    }
}

$report = new PhpunitSexpr();
$sexp = $report->parse($sc);
echo $report->getQuery($sexp);
echo "\n";
// todo Use query to get data
$data = [
    'rows' => [
        [
            'id' => 1,
            'diff' => 2,
            'diff_perc' => 11
        ],
        [
            'id' => 2,
            'diff' => 4,
            'diff_perc' => 12
        ],
        [
            'id' => 3,
            'diff' => 6,
            'diff_perc' => 13
        ]
    ]
];
//print_r($report->getTotals($sexp, $data));
echo "\n";
echo $report->getHtml($sexp, $data);
echo "\n";
