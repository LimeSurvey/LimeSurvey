<?php

/**
 * Heuristic pre-activation estimate of how close a survey's response table is
 * to MySQL/MariaDB's table-size limits. These constants were confirmed
 * empirically against a live MariaDB 10.11 instance (bisecting real CREATE
 * TABLE statements), not derived purely from documentation, and cover four
 * distinct, independent limits:
 *
 *  1. MAX_COLUMNS (4096): the generic MySQL/MariaDB column-count cap. In
 *     practice this is essentially never the binding constraint - one of the
 *     limits below is always hit first.
 *
 *  2. MAX_ROW_BYTES (65,535): the row-size limit behind error 1118
 *     (ER_TOO_BIG_ROWSIZE). Applies at the SQL layer regardless of storage
 *     engine. Fixed-width columns count their full declared width; TEXT/
 *     BLOB/JSON columns count only a small pointer, since their content is
 *     stored off-page.
 *
 *  3. MAX_DEFINITION_SIZE_BYTES (~65,000): a *separate* limit behind error
 *     1117 ("Table definition is too large"), confirmed identical for
 *     MyISAM and InnoDB and completely INDEPENDENT of column data type
 *     (a table of 2,509 TEXT columns and a table of 2,509 INT columns with
 *     the same column names fail at exactly the same point). It scales with
 *     Σ(strlen(fieldname) + ~19) across all columns - i.e. column NAME
 *     length matters here, unlike the other limits. This is what made an
 *     old trick ("copy the survey to a lower survey ID") work back when
 *     field names embedded the survey ID: shortening every field name
 *     shrank this sum. It also means this is typically the FIRST limit a
 *     large, mostly-TEXT LimeSurvey response table actually hits - not the
 *     65,535 byte row-size check most documentation focuses on.
 *
 *  4. Two InnoDB-only restrictions, confirmed to be substantially more
 *     restrictive than MyISAM for a TEXT-heavy table like LimeSurvey's
 *     response tables:
 *     - INNODB_MAX_COLUMNS (1017): InnoDB's own hard column-count cap,
 *       exact and well documented, independent of data type or name length.
 *     - INNODB_MAX_INLINE_ROW_BYTES (8,126): the limit behind InnoDB's own
 *       "Row size too large (> 8126)" error. Under the default DYNAMIC row
 *       format, a variable-length column (TEXT/BLOB/JSON/large VARCHAR) can
 *       be pushed fully off-page, leaving only a small pointer inline, but
 *       every fixed-width column (INTEGER, DECIMAL, DATETIME, ...) still
 *       has to fit inline in full. Confirmed empirically: ~383 TEXT columns
 *       already exceed this, regardless of column name length. This means a
 *       LimeSurvey install using InnoDB (not the shipped default, but a
 *       common admin choice) can hit a wall at only a few hundred to ~1,017
 *       columns - dramatically sooner than MyISAM would for the same
 *       survey. Switching engine is not a universal fix; for a mostly-TEXT
 *       response table it can make things considerably worse.
 *
 * This is a best-effort estimate, not an authoritative check - the exact
 * constants may shift slightly across MySQL/MariaDB versions, and the
 * database remains the final authority at CREATE TABLE time. It currently
 * only applies to MySQL/MariaDB; other drivers have different (and
 * generally much higher) limits and are not evaluated here.
 */
class ResponseTableSizeEstimator
{
    /** Generic MySQL/MariaDB column-count cap (rarely the binding limit in practice). */
    const MAX_COLUMNS = 4096;

    /** Row-size limit behind error 1118 (ER_TOO_BIG_ROWSIZE). */
    const MAX_ROW_BYTES = 65535;

    /** Per-column overhead (name + descriptor) counted toward the table-definition-size limit. */
    const DEFINITION_SIZE_PER_COLUMN_OVERHEAD = 19;

    /** Budget behind error 1117 ("Table definition is too large"), empirically calibrated with margin. */
    const MAX_DEFINITION_SIZE_BYTES = 65000;

    /** InnoDB's own hard column-count cap (exact; confirmed empirically). */
    const INNODB_MAX_COLUMNS = 1017;

    /** Limit behind InnoDB's own "Row size too large (> 8126)" error. */
    const INNODB_MAX_INLINE_ROW_BYTES = 8126;

    /**
     * Bytes InnoDB stores inline for a variable-length column pushed
     * off-page (DYNAMIC/COMPRESSED row format). Empirically fitted at
     * ~21.1 bytes/column (bisecting real CREATE TABLE failures); rounded up
     * to 22 for a small safety margin rather than under-warning.
     */
    const INNODB_OFFPAGE_POINTER_BYTES = 22;

    /** Ratio of a limit at which it is flagged as "getting close". */
    const WARNING_RATIO = 0.8;

    /** Ratio of a limit at which it is flagged as "very likely to fail". */
    const CRITICAL_RATIO = 1.0;

    private int $columnCount = 0;

    private int $estimatedRowBytes = 0;

    private int $estimatedInnoDbInlineBytes = 0;

    private int $definitionSizeBytes = 0;

    private bool $isInnoDb;

    /**
     * @param array $tableDefinition fieldname => type-definition string, as
     *   produced by SurveyActivator::previewTableDefinition()
     * @param string $engine the storage engine the table will actually be
     *   created with (e.g. LimeSurvey's "mysqlEngine" config value). Only
     *   whether this looks like InnoDB is used; anything else is treated
     *   like MyISAM.
     */
    public function __construct(array $tableDefinition, $engine = 'MyISAM')
    {
        $this->isInnoDb = stripos((string) $engine, 'innodb') !== false;

        foreach ($tableDefinition as $fieldName => $typeDefinition) {
            $this->columnCount++;
            $this->definitionSizeBytes += strlen((string) $fieldName) + self::DEFINITION_SIZE_PER_COLUMN_OVERHEAD;

            [$bytes, $isVariableLength] = self::classifyColumn((string) $typeDefinition);
            $this->estimatedRowBytes += $bytes;
            if ($this->isInnoDb) {
                $this->estimatedInnoDbInlineBytes += $isVariableLength
                    ? self::INNODB_OFFPAGE_POINTER_BYTES
                    : $bytes;
            }
        }
    }

    /**
     * Builds the estimate for a given survey via SurveyActivator's read-only
     * previewTableDefinition(), which builds the full response-table field
     * list with no DB side effects (no invalid-question cleanup, no plugin
     * events, no table creation). Returns null when the current database
     * driver is not MySQL/MariaDB, since these limits don't apply there.
     *
     * The storage engine is read from the same "mysqlEngine" config value
     * SurveyActivator/MysqlSchema actually use as an explicit ENGINE=...
     * clause when the response table is created, so this reflects the engine
     * the table will really get - not just whichever engine the server
     * happens to default to.
     *
     * @param Survey $survey
     * @return self|null
     */
    public static function forSurvey(Survey $survey)
    {
        $driverName = Yii::app()->db->getDriverName();
        if ($driverName !== 'mysql' && $driverName !== 'mysqli') {
            return null;
        }

        $engine = trim((string) Yii::app()->getConfig('mysqlEngine'));
        if ($engine === '') {
            $engine = 'MyISAM';
        }

        $activator = new SurveyActivator($survey);
        return new self($activator->previewTableDefinition(), $engine);
    }

    /**
     * Classifies a single column's declared type, returning both its
     * estimated byte contribution toward the generic 65,535 byte row-size
     * limit, and whether it is a variable-length type (TEXT/BLOB/JSON/
     * VARCHAR-like) that InnoDB's DYNAMIC row format can store fully
     * off-page - as opposed to a fixed-width type that always has to be
     * stored inline in full, regardless of engine or row format.
     *
     * @param string $typeDefinition
     * @return array{0: int, 1: bool} [estimatedBytes, isVariableLength]
     */
    private static function classifyColumn($typeDefinition)
    {
        $type = strtolower(trim($typeDefinition));
        // Strip modifiers that don't affect storage width (NOT NULL, COLLATE ...).
        $type = trim((string) preg_replace('/\s+(not\s+null|null|collate\s+\S+).*$/', '', $type));

        if ($type === 'pk') {
            return [4, false]; // auto-increment integer primary key
        }
        if (strpos($type, 'longtext') !== false) {
            return [12, true];
        }
        if (strpos($type, 'mediumtext') !== false) {
            return [11, true];
        }
        if (strpos($type, 'tinytext') !== false) {
            return [9, true];
        }
        if (strpos($type, 'text') !== false) {
            return [11, true]; // plain TEXT: content stored off-page, only a pointer counts here
        }
        if ($type === 'json') {
            return [12, true]; // stored internally as LONGBLOB
        }
        if (strpos($type, 'datetime') !== false) {
            return [8, false];
        }
        if ($type === 'integer' || $type === 'int') {
            return [4, false];
        }
        if (strpos($type, 'float') !== false) {
            return [4, false];
        }
        if (preg_match('/decimal\s*\(\s*(\d+)\s*,\s*(\d+)\s*\)/', $type, $m)) {
            return [self::decimalBytes((int) $m[1], (int) $m[2]), false];
        }
        if (preg_match('/(?:string|(?:var)?char)\s*\(\s*(\d+)\s*\)/', $type, $m)) {
            $len = (int) $m[1];
            return [($len * 4) + ($len > 255 ? 2 : 1), true];
        }

        // Custom/plugin "answertabledefinition" override in an unrecognized
        // format: assume a conservative default (fixed-width, so it's never
        // discounted for the InnoDB inline check) rather than under-counting.
        return [20, false];
    }

    /**
     * @param int $precision
     * @param int $scale
     * @return int
     */
    private static function decimalBytes($precision, $scale)
    {
        $integerDigits = $precision - $scale;
        return self::digitGroupBytes($integerDigits) + self::digitGroupBytes($scale);
    }

    /**
     * MySQL packs DECIMAL digits in groups of 9 per 4 bytes; a partial
     * trailing group of 1-8 digits needs 1-4 bytes.
     *
     * @param int $digits
     * @return int
     */
    private static function digitGroupBytes($digits)
    {
        $partialBytes = [0, 1, 1, 2, 2, 3, 3, 4, 4];
        $fullGroups = intdiv($digits, 9);
        $remainder = $digits % 9;
        return ($fullGroups * 4) + $partialBytes[$remainder];
    }

    /** @return bool */
    public function isInnoDb()
    {
        return $this->isInnoDb;
    }

    /** @return int */
    public function getColumnCount()
    {
        return $this->columnCount;
    }

    /** The applicable column-count cap: InnoDB's stricter 1017 when InnoDB, else the generic 4096. @return int */
    public function getColumnCountMax()
    {
        return $this->isInnoDb ? self::INNODB_MAX_COLUMNS : self::MAX_COLUMNS;
    }

    /** @return int */
    public function getEstimatedRowBytes()
    {
        return $this->estimatedRowBytes;
    }

    /**
     * Estimated bytes stored inline per row under InnoDB, or null when the
     * table isn't (or won't be) InnoDB and the metric doesn't apply.
     *
     * @return int|null
     */
    public function getEstimatedInnoDbInlineBytes()
    {
        return $this->isInnoDb ? $this->estimatedInnoDbInlineBytes : null;
    }

    /**
     * Estimated size, in bytes, of the table's metadata/definition - the
     * quantity behind error 1117 ("Table definition is too large"). Depends
     * on column count and name length, not on data type or engine.
     *
     * @return int
     */
    public function getDefinitionSizeBytes()
    {
        return $this->definitionSizeBytes;
    }

    /** @return float */
    public function getColumnCountRatio()
    {
        return $this->columnCount / $this->getColumnCountMax();
    }

    /** @return float */
    public function getRowByteRatio()
    {
        return $this->estimatedRowBytes / self::MAX_ROW_BYTES;
    }

    /** @return float */
    public function getDefinitionSizeRatio()
    {
        return $this->definitionSizeBytes / self::MAX_DEFINITION_SIZE_BYTES;
    }

    /**
     * @return float|null null when the table isn't (or won't be) InnoDB
     */
    public function getInnoDbInlineRatio()
    {
        if (!$this->isInnoDb) {
            return null;
        }
        return $this->estimatedInnoDbInlineBytes / self::INNODB_MAX_INLINE_ROW_BYTES;
    }

    /**
     * Highest of the applicable limit ratios, i.e. whichever limit is
     * closest to being hit.
     *
     * @return float
     */
    public function getWorstRatio()
    {
        $ratios = [
            $this->getColumnCountRatio(),
            $this->getRowByteRatio(),
            $this->getDefinitionSizeRatio(),
        ];
        if ($this->isInnoDb) {
            $ratios[] = $this->getInnoDbInlineRatio();
        }
        return max($ratios);
    }

    /**
     * @return string|null 'critical', 'warning', or null if comfortably under every applicable limit
     */
    public function getSeverity()
    {
        $ratio = $this->getWorstRatio();
        if ($ratio >= self::CRITICAL_RATIO) {
            return 'critical';
        }
        if ($ratio >= self::WARNING_RATIO) {
            return 'warning';
        }
        return null;
    }

    /** @return array */
    public function toArray()
    {
        return [
            'engine' => $this->isInnoDb ? 'InnoDB' : 'MyISAM (or other)',
            'columnCount' => $this->columnCount,
            'columnCountMax' => $this->getColumnCountMax(),
            'columnCountRatio' => $this->getColumnCountRatio(),
            'estimatedRowBytes' => $this->estimatedRowBytes,
            'rowByteMax' => self::MAX_ROW_BYTES,
            'rowByteRatio' => $this->getRowByteRatio(),
            'definitionSizeBytes' => $this->definitionSizeBytes,
            'definitionSizeMax' => self::MAX_DEFINITION_SIZE_BYTES,
            'definitionSizeRatio' => $this->getDefinitionSizeRatio(),
            'estimatedInnoDbInlineBytes' => $this->getEstimatedInnoDbInlineBytes(),
            'innoDbInlineMax' => $this->isInnoDb ? self::INNODB_MAX_INLINE_ROW_BYTES : null,
            'innoDbInlineRatio' => $this->getInnoDbInlineRatio(),
            'severity' => $this->getSeverity(),
        ];
    }
}
