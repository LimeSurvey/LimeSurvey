<?php

namespace LimeSurvey\Models\Services;

use Label;
use LabelL10n;
use LabelSet;
use CDbExpression;
use LimeSurvey\Models\Services\Exception\{
    BadRequestException,
    ConflictException,
    NotFoundException,
    PermissionDeniedException,
    PersistErrorException
};
use LimeSurvey\PluginManager\PluginEvent;
use LSYii_Application;
use LSYii_Validators;
use Permission;

/**
 * Reads and writes label sets for the question editor.
 *
 * Labels are passed in and returned in this shape:
 * [
 *     'code' => 'A1',
 *     'assessmentValue' => 0,
 *     'l10ns' => ['en' => 'Yes', 'de' => 'Ja'],
 * ]
 */
class LabelSetService
{
    /** Length of the labels.code column */
    public const MAX_CODE_LENGTH = 20;
    /** Length limits of the label_l10ns.language column (the minimum is LabelL10n's rule) */
    public const MIN_LANGUAGE_LENGTH = 2;
    public const MAX_LANGUAGE_LENGTH = 20;
    /** Bytes the label_l10ns.title column (TEXT on MySQL) can hold; answer texts are MEDIUMTEXT */
    public const MAX_TITLE_BYTES = 65535;
    /** Length of the labelsets.languages column */
    public const MAX_LANGUAGES_LENGTH = 255;
    /** Range of the labels.assessment_value column (integer) */
    public const MIN_ASSESSMENT_VALUE = -2147483648;
    public const MAX_ASSESSMENT_VALUE = 2147483647;

    /** Seconds an overwrite waits for another overwrite of the same set on MySQL */
    protected int $lockTimeout = 10;

    protected LabelSet $labelSet;
    protected Permission $permission;
    protected LSYii_Application $app;

    /**
     * Create the service with its dependencies.
     *
     * @param LabelSet $labelSet Label set model used for queries
     * @param Permission $permission Permission model used for the access checks
     * @param LSYii_Application $app Application, for the database and the current user
     */
    public function __construct(
        LabelSet $labelSet,
        Permission $permission,
        LSYii_Application $app
    ) {
        $this->labelSet = $labelSet;
        $this->permission = $permission;
        $this->app = $app;
    }

    /**
     * List label sets, sorted by name.
     *
     * Using a label set doesn't need a permission (same as the classic editor),
     * so every set is listed unless $onlyUpdatable is set.
     *
     * @param string[] $languages Only list sets that contain all of these languages
     * @param bool $onlyUpdatable Only list sets the current user may overwrite
     * @return array<int, array{lid: int, name: string, languages: string[], labelCount: int, hasAssessmentValues: bool}>
     *         The label sets without their labels
     */
    public function getLabelSets(array $languages = [], bool $onlyUpdatable = false): array
    {
        $languages = $this->normaliseLanguages($languages);
        if ($languages === null) {
            // No set can contain an invalid language code, so the filter matches nothing
            return [];
        }
        $matchingSets = [];
        foreach ($this->labelSet->findAll(['order' => 'label_name']) as $labelSet) {
            if (!array_diff($languages, $labelSet->getLanguageArray())) {
                $matchingSets[(int)$labelSet->lid] = $labelSet;
            }
        }
        if ($onlyUpdatable && !$this->hasGlobalUpdatePermission()) {
            $ownerIds = array_map(static fn (LabelSet $labelSet): int => (int)$labelSet->owner_id, $matchingSets);
            $matchingSets = array_intersect_key($matchingSets, array_flip($this->filterUpdatableIds($ownerIds)));
        }

        $labelStats = $this->getLabelStats(
            array_map(static fn (LabelSet $labelSet): int => (int)$labelSet->lid, $matchingSets)
        );
        $result = [];
        foreach ($matchingSets as $labelSet) {
            $stats = $labelStats[(int)$labelSet->lid] ?? ['labelcount' => 0, 'assessmentcount' => 0];
            $result[] = [
                'lid' => (int)$labelSet->lid,
                'name' => flattenText($labelSet->label_name),
                'languages' => $labelSet->getLanguageArray(),
                'labelCount' => (int)$stats['labelcount'],
                'hasAssessmentValues' => (int)$stats['assessmentcount'] > 0,
            ];
        }
        return $result;
    }

    /**
     * Whether the current user may overwrite at least one label set.
     *
     * The editor asks for it on every load, so it costs a fixed number of queries,
     * however many label sets there are, and doesn't count labels like getLabelSets() does.
     *
     * @return bool True when there is a label set the user may overwrite
     */
    public function hasUpdatableLabelSet(): bool
    {
        if ($this->hasGlobalUpdatePermission()) {
            return $this->labelSet->exists();
        }
        // Only the ids and owners, no model per set
        $rows = $this->app->db->createCommand()
            ->select(['lid', 'owner_id'])
            ->from('{{labelsets}}')
            ->queryAll();
        $ownerIds = [];
        foreach ($rows as $row) {
            $ownerIds[(int)$row['lid']] = (int)$row['owner_id'];
        }
        return !empty($this->filterUpdatableIds($ownerIds));
    }

    /**
     * Get one label set with its labels and their translations.
     *
     * The revision changes whenever the languages, labels or translations change, whoever
     * saved them. Pass it to overwrite() to reject the overwrite when the set changed since.
     *
     * @param int $lid Label set id
     * @return array{lid: int, name: string, languages: string[], labels: array, revision: string}
     *         The label set with its labels
     * @throws NotFoundException When the label set doesn't exist
     */
    public function getLabelSet(int $lid): array
    {
        $labelSet = $this->labelSet->with('labels', 'labels.labell10ns')->findByPk($lid);
        if (!$labelSet) {
            throw new NotFoundException('Label set not found');
        }

        // Labels can share a sort order (e.g. after classic edits or .lsl imports): Sort by id too,
        // so the order and the revision are the same on every read of an unchanged set
        $labelModels = $labelSet->labels;
        usort(
            $labelModels,
            static fn (Label $first, Label $second): int =>
                [(int)$first->sortorder, (int)$first->id] <=> [(int)$second->sortorder, (int)$second->id]
        );

        $labels = [];
        foreach ($labelModels as $label) {
            $l10ns = [];
            foreach ($label->labell10ns as $language => $labelL10n) {
                $l10ns[$language] = (string)$labelL10n->title;
            }
            ksort($l10ns);
            $labels[] = [
                'code' => (string)$label->code,
                'sortOrder' => (int)$label->sortorder,
                'assessmentValue' => (int)$label->assessment_value,
                'l10ns' => $l10ns,
            ];
        }

        $languages = $labelSet->getLanguageArray();
        return [
            'lid' => (int)$labelSet->lid,
            'name' => flattenText($labelSet->label_name),
            'languages' => $languages,
            'labels' => $labels,
            // Not a security token, only a fingerprint of what an overwrite replaces
            'revision' => hash('sha256', serialize([$languages, $labels])),
        ];
    }

    /**
     * Create a new label set.
     *
     * @param string $name Name of the new label set
     * @param string[] $languages Languages of the label set
     * @param array $labels Labels to save, in the shape described on the class
     * @return LabelSet The created label set
     * @throws PermissionDeniedException When the user may not create label sets
     * @throws BadRequestException When the name, languages or labels are invalid
     * @throws PersistErrorException When saving fails
     */
    public function create(string $name, array $languages, array $labels): LabelSet
    {
        if (!$this->permission->hasGlobalPermission('labelsets', 'create')) {
            throw new PermissionDeniedException('Access denied');
        }
        $name = trim($name);
        if ($name === '') {
            throw new BadRequestException('Label set name is empty');
        }
        $languages = $this->validateLanguages($languages);
        $labels = $this->validateLabels($labels, $languages);

        return $this->inTransaction(function () use ($name, $languages, $labels) {
            $labelSet = new LabelSet();
            $labelSet->label_name = $name;
            $labelSet->languages = implode(' ', $languages);
            $labelSet->owner_id = $this->app->user->getId();
            if (!$labelSet->save()) {
                throw new PersistErrorException(
                    'Could not save label set: ' . $this->formatErrors($labelSet->getErrors())
                );
            }
            $this->replaceLabels((int)$labelSet->lid, $languages, $labels);
            return $labelSet;
        });
    }

    /**
     * Replace the languages and all labels of an existing label set.
     *
     * Like the classic editor, translations of languages that are not passed are removed.
     * Overwrites of the same set run one after the other, also on MyISAM tables.
     *
     * @param int $lid Id of the label set to overwrite
     * @param string[] $languages New languages of the label set
     * @param array $labels Labels to save, in the shape described on the class
     * @param string|null $revision Revision from getLabelSet() the user reviewed; null skips the check
     * @return LabelSet The updated label set
     * @throws NotFoundException When the label set doesn't exist
     * @throws PermissionDeniedException When the user may not update the label set
     * @throws BadRequestException When the languages or labels are invalid
     * @throws ConflictException When the set changed since the revision was read, or another overwrite
     *         of it doesn't finish in time
     * @throws PersistErrorException When saving fails
     */
    public function overwrite(int $lid, array $languages, array $labels, ?string $revision = null): LabelSet
    {
        $labelSet = $this->labelSet->findByPk($lid);
        if (!$labelSet) {
            throw new NotFoundException('Label set not found');
        }
        if (!$this->mayUpdate($labelSet)) {
            throw new PermissionDeniedException('Access denied');
        }
        $languages = $this->validateLanguages($languages);
        $labels = $this->validateLabels($labels, $languages);

        return $this->withLabelSetLock($lid, function () use ($lid, $languages, $labels, $revision) {
            return $this->inTransaction(function () use ($lid, $languages, $labels, $revision) {
                $this->lockLabelSetRow($lid);
                // Read again after the lock: The set may have changed or been deleted meanwhile
                $labelSet = $this->labelSet->findByPk($lid);
                if (!$labelSet) {
                    throw new NotFoundException('Label set not found');
                }
                if ($revision !== null && $this->getLabelSet($lid)['revision'] !== $revision) {
                    throw new ConflictException('The label set was changed in the meantime');
                }

                $labelSet->languages = implode(' ', $languages);
                if (!$labelSet->save()) {
                    throw new PersistErrorException(
                        'Could not save label set: ' . $this->formatErrors($labelSet->getErrors())
                    );
                }
                $this->replaceLabels($lid, $languages, $labels);
                return $labelSet;
            });
        });
    }

    /**
     * Run a callback while holding a per-set lock on MySQL.
     *
     * Transactions don't serialize writes to MyISAM tables, so two overwrites of the same set
     * could otherwise interleave and leave labels of both in the set. Other databases are
     * serialized by lockLabelSetRow() and run the callback right away.
     *
     * @param int $lid Id of the label set to lock
     * @param callable $callback Code to run while the lock is held
     * @return mixed What the callback returns
     * @throws ConflictException When the lock isn't free within the lock timeout
     */
    protected function withLabelSetLock(int $lid, callable $callback)
    {
        $db = $this->app->db;
        if (!in_array($db->getDriverName(), ['mysql', 'mysqli'], true)) {
            return $callback();
        }

        $lockName = $this->getLockName($lid);
        $locked = $db->createCommand('SELECT GET_LOCK(:name, :timeout)')
            ->queryScalar([':name' => $lockName, ':timeout' => $this->lockTimeout]);
        if ((string)$locked !== '1') {
            throw new ConflictException('The label set is being saved by someone else, please try again');
        }
        try {
            return $callback();
        } finally {
            $db->createCommand('SELECT RELEASE_LOCK(:name)')->queryScalar([':name' => $lockName]);
        }
    }

    /**
     * Name of the MySQL lock of a label set.
     *
     * Locks are server-wide, so the name includes the database and table prefix of this installation.
     *
     * @param int $lid Label set id
     * @return string Lock name, short enough for MySQL's 64 character limit
     */
    protected function getLockName(int $lid): string
    {
        $db = $this->app->db;
        $database = (string)$db->createCommand('SELECT DATABASE()')->queryScalar();
        return 'limesurvey_labelset_' . md5($database . '.' . $db->tablePrefix . '.' . $lid);
    }

    /**
     * Lock the label set row until the transaction ends.
     *
     * Updating the row (to its own value) makes a concurrent overwrite of the same set wait
     * on transactional databases, so the revision check and the replacement are atomic.
     *
     * @param int $lid Id of the label set to lock
     * @return void
     */
    protected function lockLabelSetRow(int $lid): void
    {
        $db = $this->app->db;
        $languagesColumn = $db->quoteColumnName('languages');
        $db->createCommand()->update(
            '{{labelsets}}',
            ['languages' => new CDbExpression($languagesColumn)],
            'lid = :lid',
            [':lid' => $lid]
        );
    }

    /**
     * Whether the current user may overwrite the label set.
     *
     * The one check for the overwrite list and overwrite(), so plugins that grant or
     * deny update rights per set (beforeHasPermission) are respected everywhere.
     *
     * @param LabelSet $labelSet Label set to check
     * @return bool True when the user may overwrite it
     */
    protected function mayUpdate(LabelSet $labelSet): bool
    {
        return $labelSet->hasPermission('labelset', 'update');
    }

    /**
     * Ids of the label sets the current user may overwrite, for a user without the global update right.
     *
     * The same decision LabelSet::hasPermission('labelset', 'update') makes per set (see
     * Permission::hasPermission()): A plugin decision first, else the owner, a superadmin or a
     * label set permission row. It costs one query for all sets instead of two per set.
     *
     * @param array<int, int> $ownerIds Owner id of each label set, keyed by label set id
     * @return int[] Ids of the sets the user may overwrite
     */
    protected function filterUpdatableIds(array $ownerIds): array
    {
        $userId = (int)$this->permission->getUserId();
        $isSuperAdmin = (bool)$this->permission->hasGlobalPermission('superadmin', 'read');
        $grantedIds = $this->app->db->createCommand()
            ->select('entity_id')
            ->from('{{permissions}}')
            ->where(
                'entity = :entity AND permission = :permission AND uid = :uid AND update_p <> 0',
                [':entity' => 'labelset', ':permission' => 'labelset', ':uid' => $userId]
            )
            ->queryColumn();
        $grantedIds = array_flip(array_map('intval', $grantedIds));

        $updatableIds = [];
        foreach ($ownerIds as $lid => $ownerId) {
            $mayUpdate = $this->getPluginPermission($lid)
                ?? ($userId > 0 && ($ownerId === $userId || $isSuperAdmin || isset($grantedIds[$lid])));
            if ($mayUpdate) {
                $updatableIds[] = $lid;
            }
        }
        return $updatableIds;
    }

    /**
     * What plugins decide about updating a label set, asked the way Permission::hasPermission() asks.
     *
     * @param int $lid Label set id
     * @return bool|null The decision of a plugin, null when no plugin decided
     */
    protected function getPluginPermission(int $lid): ?bool
    {
        $pluginManager = $this->app->getPluginManager();
        if (!$pluginManager->hasSubscribers('beforeHasPermission')) {
            return null;
        }
        $event = new PluginEvent('beforeHasPermission');
        $event->set('iEntityID', $lid);
        $event->set('sEntityName', 'labelset');
        $event->set('sPermission', 'labelset');
        $event->set('sCRUD', 'update');
        $event->set('iUserID', null);
        $pluginManager->dispatchEvent($event);
        $permission = $event->get('bPermission');
        return isset($permission) ? (bool)$permission : null;
    }

    /**
     * Whether the current user may update all label sets.
     *
     * LabelSet::hasPermission() grants every set when this is true, so checking it once
     * saves a role and permission query per set.
     *
     * @return bool True when the user has the global label set update right
     */
    protected function hasGlobalUpdatePermission(): bool
    {
        return (bool)$this->permission->hasGlobalPermission('labelsets', 'update');
    }

    /**
     * Count the labels, and the labels with an assessment value, of the given label sets in one query.
     *
     * @param int[] $lids Ids of the label sets to count
     * @return array<int, array{labelcount: int|string, assessmentcount: int|string}> Counts keyed by label set id
     */
    protected function getLabelStats(array $lids): array
    {
        if (empty($lids)) {
            return [];
        }
        $rows = $this->app->db->createCommand()
            ->select([
                'lid',
                'COUNT(*) AS labelcount',
                'SUM(CASE WHEN assessment_value <> 0 THEN 1 ELSE 0 END) AS assessmentcount',
            ])
            ->from('{{labels}}')
            ->where(['in', 'lid', $lids])
            ->group('lid')
            ->queryAll();

        $stats = [];
        foreach ($rows as $row) {
            $stats[(int)$row['lid']] = $row;
        }
        return $stats;
    }

    /**
     * Check the languages before anything is written.
     *
     * Any well-formed code is accepted, not only the ones LimeSurvey ships, because
     * surveys can carry other codes (e.g. from an import). All checks mirror the
     * tables, so a save can't fail half-way: Transactions are no-ops on MyISAM tables.
     *
     * @param array $languages Languages to save, as sent by the client
     * @return string[] The cleaned, unique language list
     * @throws BadRequestException When a language is invalid
     */
    protected function validateLanguages(array $languages): array
    {
        $cleanLanguages = [];
        foreach ($languages as $language) {
            $language = is_string($language) ? trim($language) : null;
            if (
                $language === null
                || $language === ''
                || LSYii_Validators::languageCodeFilter($language) !== $language
                || strlen($language) < self::MIN_LANGUAGE_LENGTH
                || strlen($language) > self::MAX_LANGUAGE_LENGTH
            ) {
                throw new BadRequestException('Invalid language code');
            }
            $cleanLanguages[$language] = true;
        }
        $cleanLanguages = array_keys($cleanLanguages);
        if (empty($cleanLanguages)) {
            throw new BadRequestException('No language selected');
        }
        if (strlen(implode(' ', $cleanLanguages)) > self::MAX_LANGUAGES_LENGTH) {
            throw new BadRequestException('Too many languages');
        }
        return $cleanLanguages;
    }

    /**
     * Check the labels before anything is written and bring them into a fixed shape.
     *
     * All checks mirror the Label model and the labels table, so a save can't fail
     * half-way: Transactions are no-ops on MyISAM tables.
     *
     * @param array $labels Labels to save, in the shape described on the class
     * @param string[] $languages Validated languages; translations of other languages are ignored
     * @return array<int, array{code: string, assessmentValue: int, l10ns: array<string, string>}> The cleaned labels
     * @throws BadRequestException When a label is invalid
     */
    protected function validateLabels(array $labels, array $languages): array
    {
        if (empty($labels)) {
            throw new BadRequestException('Label set has no labels');
        }
        $cleanLabels = [];
        $codes = [];
        foreach ($labels as $label) {
            if (!is_array($label)) {
                throw new BadRequestException('Invalid label');
            }
            $code = $this->validateCode($label['code'] ?? null);
            // The unique (lid, code) index uses a case-insensitive collation, so "A" and "a" clash
            $codeKey = strtolower($code);
            if (isset($codes[$codeKey])) {
                throw new BadRequestException(sprintf('Label code "%s" is used twice', $code));
            }
            $codes[$codeKey] = true;

            $l10ns = $label['l10ns'] ?? [];
            if (!is_array($l10ns)) {
                throw new BadRequestException(sprintf('Invalid translations for label "%s"', $code));
            }
            $titles = [];
            foreach ($languages as $language) {
                $title = $l10ns[$language] ?? '';
                if (!is_string($title) && !is_int($title) && !is_float($title)) {
                    throw new BadRequestException(sprintf('Invalid translation for label "%s"', $code));
                }
                $title = (string)$title;
                // Rejected rather than truncated; strict MySQL would fail the insert after the delete
                if (strlen($title) > self::MAX_TITLE_BYTES) {
                    throw new BadRequestException(sprintf('Translation for label "%s" is too long', $code));
                }
                $titles[$language] = $title;
            }

            $cleanLabels[] = [
                'code' => $code,
                'assessmentValue' => $this->validateAssessmentValue($label['assessmentValue'] ?? null, $code),
                'l10ns' => $titles,
            ];
        }
        return $cleanLabels;
    }

    /**
     * Check one label code.
     *
     * @param mixed $code Code as sent by the client
     * @return string The trimmed code
     * @throws BadRequestException When the code is missing or invalid
     */
    protected function validateCode($code): string
    {
        if (!is_string($code) && !is_int($code)) {
            throw new BadRequestException('Every label needs a code');
        }
        $code = trim((string)$code);
        if ($code === '') {
            throw new BadRequestException('Every label needs a code');
        }
        if (!preg_match('/^[[:alnum:]]+$/', $code)) {
            throw new BadRequestException(
                sprintf('Label code "%s" may only contain alphanumeric characters', $code)
            );
        }
        if (strlen($code) > self::MAX_CODE_LENGTH) {
            throw new BadRequestException(
                sprintf('Label code "%s" is longer than %d characters', $code, self::MAX_CODE_LENGTH)
            );
        }
        return $code;
    }

    /**
     * Check one assessment value, like the Label model does (integer, may be empty).
     *
     * @param mixed $value Assessment value as sent by the client
     * @param string $code Code of the label, for the error message
     * @return int The assessment value; empty means 0, as in the classic editor
     * @throws BadRequestException When the value isn't an integer or doesn't fit the column
     */
    protected function validateAssessmentValue($value, string $code): int
    {
        if ($value === null || (is_string($value) && trim($value) === '')) {
            return 0;
        }
        $isInteger = is_int($value)
            || (is_float($value) && floor($value) === $value)
            || (is_string($value) && preg_match('/^\s*[+-]?\d+\s*$/', $value));
        // Float comparison, so values beyond PHP_INT_MAX are caught too
        if (
            !$isInteger
            || (float)$value < self::MIN_ASSESSMENT_VALUE
            || (float)$value > self::MAX_ASSESSMENT_VALUE
        ) {
            throw new BadRequestException(sprintf('Invalid assessment value for label "%s"', $code));
        }
        return (int)$value;
    }

    /**
     * Delete all labels of the set and create the given ones.
     *
     * @param int $lid Label set id
     * @param string[] $languages Languages to save translations for
     * @param array<int, array{code: string, assessmentValue: int, l10ns: array<string, string>}> $labels
     *        Labels as returned by validateLabels()
     * @return void
     * @throws PersistErrorException When a label or translation can't be saved
     */
    protected function replaceLabels(int $lid, array $languages, array $labels): void
    {
        $this->app->db->createCommand()->delete(
            '{{label_l10ns}}',
            'label_id IN (SELECT id FROM {{labels}} WHERE lid = :lid)',
            [':lid' => $lid]
        );
        Label::model()->deleteAllByAttributes(['lid' => $lid]);

        foreach (array_values($labels) as $sortOrder => $labelData) {
            $label = new Label();
            $label->lid = $lid;
            $label->code = $labelData['code'];
            $label->sortorder = $sortOrder;
            $label->assessment_value = $labelData['assessmentValue'];
            if (!$label->save()) {
                throw new PersistErrorException(
                    'Could not save label: ' . $this->formatErrors($label->getErrors())
                );
            }

            foreach ($languages as $language) {
                $labelL10n = new LabelL10n();
                $labelL10n->label_id = $label->id;
                $labelL10n->language = $language;
                $labelL10n->title = $labelData['l10ns'][$language];
                if (!$labelL10n->save()) {
                    throw new PersistErrorException(
                        'Could not save label translation: ' . $this->formatErrors($labelL10n->getErrors())
                    );
                }
            }
        }
    }

    /**
     * Clean a list of language codes used as a filter.
     *
     * Empty entries are dropped, so an empty list (or [''] from an empty query param)
     * means no filter. An invalid entry isn't dropped, as that would widen the filter.
     *
     * @param array $languages Language codes, as sent by the client
     * @return string[]|null Unique language codes, or null when an entry isn't a valid code
     */
    protected function normaliseLanguages(array $languages): ?array
    {
        $cleanLanguages = [];
        foreach ($languages as $language) {
            if (!is_string($language)) {
                return null;
            }
            $language = trim($language);
            if ($language === '') {
                continue;
            }
            if (LSYii_Validators::languageCodeFilter($language) !== $language) {
                return null;
            }
            $cleanLanguages[$language] = true;
        }
        return array_keys($cleanLanguages);
    }

    /**
     * Run a callback in a database transaction and roll back when it throws.
     *
     * @param callable $callback Code to run
     * @return mixed What the callback returns
     * @throws \Exception Whatever the callback throws, after the rollback
     */
    protected function inTransaction(callable $callback)
    {
        $transaction = $this->app->db->beginTransaction();
        try {
            $result = $callback();
            $transaction->commit();
            return $result;
        } catch (\Exception $exception) {
            $transaction->rollback();
            throw $exception;
        }
    }

    /**
     * Join the validation errors of a model into one message.
     *
     * @param array $errors Errors as returned by CModel::getErrors()
     * @return string All messages separated by spaces
     */
    protected function formatErrors(array $errors): string
    {
        $messages = [];
        foreach ($errors as $attributeErrors) {
            foreach ((array)$attributeErrors as $message) {
                $messages[] = $message;
            }
        }
        return implode(' ', $messages);
    }
}
