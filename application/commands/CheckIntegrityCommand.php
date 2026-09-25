<?php

/*
* LimeSurvey (tm)
* Copyright (C) 2011-2026 The LimeSurvey Project Team
* All rights reserved.
* License: GNU/GPL License v2 or later, see LICENSE.php
* LimeSurvey is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Runs the "Check data integrity" tool (Configuration > Data consistency check) from
 * the command line, applying all safe automatic fixes without the web UI's manual
 * "Yes - Delete Them!" confirmation step.
 *
 * Repeats the same detection-and-cleanup pass the web action runs (orphaned questions,
 * groups, conditions, quotas, attributes, default values, empty/fully orphaned old
 * survey and participant list tables, duplicate group/question sort orders, ...) until
 * a pass finds nothing left to fix, or a safety cap on the number of passes is reached.
 * Repeating matters here: deleting an orphan in one pass can itself orphan other rows
 * that only a fresh scan will catch, and with nobody there to click "Yes" again, the
 * command has to do that itself.
 *
 * Old survey/participant list tables that still contain response data are reported but
 * never deleted here: that is the data redundancy check's job, and it stays an
 * explicit, per-table opt-in (see admin/checkintegrity/fixredundancy) because it
 * destroys real response data.
 */
class CheckIntegrityCommand extends CConsoleCommand
{
    /** Hard cap on repeated passes, in case a pass keeps finding "new" issues forever. */
    const MAX_PASSES = 5;

    /**
     * @param string[] $args
     * @return int 0 on a clean pass, 1 if a fix could not be applied, 2 if the check
     *             did not converge within MAX_PASSES.
     */
    public function run($args)
    {
        Yii::import('application.controllers.admin.CheckIntegrity', true);

        /** @var CheckIntegrity $checkIntegrity */
        $checkIntegrity = new CheckIntegrity($this, 'checkintegrity');

        $hasWarnings = false;
        $pass = 0;
        $aData = array('integrityok' => false);
        while (!$aData['integrityok'] && $pass < self::MAX_PASSES) {
            $pass++;
            $aData = $checkIntegrity->applyAutomaticFixes();

            foreach ($aData['messages'] as $message) {
                echo "[pass {$pass}] " . strip_tags($message) . PHP_EOL;
            }
            foreach ($aData['warnings'] as $warning) {
                echo "[pass {$pass}] WARNING: " . strip_tags($warning) . PHP_EOL;
                $hasWarnings = true;
            }
        }

        // Duplicate group/question sort orders are fixed automatically by
        // applyAutomaticFixes() (see fixGroupOrderDuplicates()/fixQuestionOrderDuplicates()
        // in CheckIntegrity); the corresponding "Fixed duplicate ... sort order" messages
        // were already printed above, per pass, alongside the other fixes.

        $redundantTables = array_merge($aData['redundantsurveytables'] ?? array(), $aData['redundanttokentables'] ?? array());
        if (!empty($redundantTables)) {
            echo PHP_EOL . 'The following old tables still contain response data and were left in place;'
                . ' review and delete them manually via Configuration > Data redundancy check if no longer needed:' . PHP_EOL;
            foreach ($redundantTables as $table) {
                echo '  - ' . $table['table'] . ' (' . strip_tags($table['details']) . ')' . PHP_EOL;
            }
        }

        if (!$aData['integrityok'] && $pass >= self::MAX_PASSES) {
            echo PHP_EOL . 'Reached the maximum of ' . self::MAX_PASSES . ' passes and issues are still being found;'
                . ' stopping here instead of running forever. Re-run the command, or check manually.' . PHP_EOL;
            return 2;
        }

        echo PHP_EOL . ($aData['integrityok']
            ? 'No database action required.'
            : 'Integrity check finished after ' . $pass . ' pass(es).') . PHP_EOL;

        return $hasWarnings ? 1 : 0;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        return <<<EOD
USAGE
  checkintegrity

DESCRIPTION
  Runs LimeSurvey's "Check data integrity" tool (Configuration > Data consistency
  check) and applies all safe automatic fixes, with no manual confirmation step,
  including renumbering duplicate group/question sort orders. Repeats the check
  until nothing is left to fix, or a safety cap is reached.

  Old survey/participant list tables that still contain response data are reported
  but never deleted automatically; use the admin UI's data redundancy check for those.

EXIT CODES
  0  Clean pass, nothing left to fix.
  1  Ran, but at least one fix could not be applied (see WARNING lines).
  2  Did not converge within the pass limit; needs manual investigation.

EOD;
    }
}
