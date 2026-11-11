<?php

namespace LimeSurvey\Models\Services;

use CDbCriteria;
use Label;
use LabelL10n;
use LabelSet;
use LimeSurvey\Models\Services\Exception\{
    BadRequestException,
    NotFoundException,
    PermissionDeniedException,
    PersistErrorException
};
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
     * @return array<int, array{lid: int, name: string, languages: string[], labelCount: int, hasAssessmentValues: bool}> The label sets without their labels
     */
    public function getLabelSets(array $languages = [], bool $onlyUpdatable = false): array
    {
        $languages = $this->normaliseLanguages($languages);
        $criteria = new CDbCriteria(['order' => 'label_name']);
        if ($onlyUpdatable) {
            $criteria->mergeWith($this->getUpdatableCriteria());
        }
        $labelSets = $this->labelSet->findAll($criteria);
        $labelStats = $this->getLabelStats();

        $result = [];
        foreach ($labelSets as $labelSet) {
            $setLanguages = $labelSet->getLanguageArray();
            if (array_diff($languages, $setLanguages)) {
                continue;
            }
            $stats = $labelStats[(int)$labelSet->lid] ?? ['labelcount' => 0, 'assessmentcount' => 0];
            $result[] = [
                'lid' => (int)$labelSet->lid,
                'name' => flattenText($labelSet->label_name),
                'languages' => $setLanguages,
                'labelCount' => (int)$stats['labelcount'],
                'hasAssessmentValues' => (int)$stats['assessmentcount'] > 0,
            ];
        }
        return $result;
    }

    /**
     * Get one label set with its labels and their translations.
     *
     * @param int $lid Label set id
     * @return array{lid: int, name: string, languages: string[], labels: array} The label set with its labels
     * @throws NotFoundException When the label set doesn't exist
     */
    public function getLabelSet(int $lid): array
    {
        $labelSet = $this->labelSet->with('labels', 'labels.labell10ns')->findByPk($lid);
        if (!$labelSet) {
            throw new NotFoundException('Label set not found');
        }

        $labels = [];
        foreach ($labelSet->labels as $label) {
            $l10ns = [];
            foreach ($label->labell10ns as $language => $labelL10n) {
                $l10ns[$language] = (string)$labelL10n->title;
            }
            $labels[] = [
                'code' => (string)$label->code,
                'sortOrder' => (int)$label->sortorder,
                'assessmentValue' => (int)$label->assessment_value,
                'l10ns' => $l10ns,
            ];
        }

        return [
            'lid' => (int)$labelSet->lid,
            'name' => flattenText($labelSet->label_name),
            'languages' => $labelSet->getLanguageArray(),
            'labels' => $labels,
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
        $languages = $this->validateInput($languages, $labels);

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
     *
     * @param int $lid Id of the label set to overwrite
     * @param string[] $languages New languages of the label set
     * @param array $labels Labels to save, in the shape described on the class
     * @return LabelSet The updated label set
     * @throws NotFoundException When the label set doesn't exist
     * @throws PermissionDeniedException When the user may not update the label set
     * @throws BadRequestException When the languages or labels are invalid
     * @throws PersistErrorException When saving fails
     */
    public function overwrite(int $lid, array $languages, array $labels): LabelSet
    {
        $labelSet = $this->labelSet->findByPk($lid);
        if (!$labelSet) {
            throw new NotFoundException('Label set not found');
        }
        if (!$labelSet->hasPermission('labelset', 'update')) {
            throw new PermissionDeniedException('Access denied');
        }
        $languages = $this->validateInput($languages, $labels);

        return $this->inTransaction(function () use ($labelSet, $languages, $labels) {
            $labelSet->languages = implode(' ', $languages);
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
     * Criteria for the label sets the current user may update, without checking each set on its own.
     *
     * Mirrors LabelSet::hasPermission('labelset', 'update'): The global permission,
     * ownership or a "labelset" permission row for the set.
     *
     * @return CDbCriteria Criteria to merge into a label set query
     */
    protected function getUpdatableCriteria(): CDbCriteria
    {
        $criteria = new CDbCriteria();
        if ($this->permission->hasGlobalPermission('labelsets', 'update')) {
            return $criteria;
        }
        // Each placeholder is used once: Native PDO prepares (pgsql, mssql) reject repeated names
        $userId = (int)$this->app->user->getId();
        $criteria->addCondition(
            't.owner_id = :ownerid OR t.lid IN ('
            . 'SELECT entity_id FROM {{permissions}}'
            . ' WHERE entity = :permentity AND permission = :permname AND uid = :permuid AND update_p = 1'
            . ')'
        );
        $criteria->params = [
            ':ownerid' => $userId,
            ':permentity' => 'labelset',
            ':permname' => 'labelset',
            ':permuid' => $userId,
        ];
        return $criteria;
    }

    /**
     * Count the labels, and the labels with an assessment value, of all label sets in one query.
     *
     * @return array<int, array{labelcount: int|string, assessmentcount: int|string}> Counts keyed by label set id
     */
    protected function getLabelStats(): array
    {
        $rows = $this->app->db->createCommand()
            ->select([
                'lid',
                'COUNT(*) AS labelcount',
                'SUM(CASE WHEN assessment_value <> 0 THEN 1 ELSE 0 END) AS assessmentcount',
            ])
            ->from('{{labels}}')
            ->group('lid')
            ->queryAll();

        $stats = [];
        foreach ($rows as $row) {
            $stats[(int)$row['lid']] = $row;
        }
        return $stats;
    }

    /**
     * Check the languages and labels before anything is written.
     *
     * All checks mirror the Label model and the labels table, so a save can't fail
     * half-way: Transactions are no-ops on MyISAM tables.
     *
     * @param string[] $languages Languages to save
     * @param array $labels Labels to save, in the shape described on the class
     * @return string[] The cleaned language list
     * @throws BadRequestException When a language or label is invalid
     */
    protected function validateInput(array $languages, array $labels): array
    {
        $languages = $this->normaliseLanguages($languages);
        if (empty($languages)) {
            throw new BadRequestException('No language selected');
        }
        if (empty($labels)) {
            throw new BadRequestException('Label set has no labels');
        }
        $codes = [];
        foreach ($labels as $label) {
            $code = is_array($label) ? trim((string)($label['code'] ?? '')) : '';
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
            // The unique (lid, code) index uses a case-insensitive collation, so "A" and "a" clash
            $codeKey = strtolower($code);
            if (isset($codes[$codeKey])) {
                throw new BadRequestException(sprintf('Label code "%s" is used twice', $code));
            }
            $codes[$codeKey] = true;
        }
        return $languages;
    }

    /**
     * Delete all labels of the set and create the given ones.
     *
     * Only translations for $languages are saved; a missing translation is saved as empty text.
     *
     * @param int $lid Label set id
     * @param string[] $languages Languages to save translations for
     * @param array $labels Labels to save, in the shape described on the class
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
            $label->code = trim((string)$labelData['code']);
            $label->sortorder = $sortOrder;
            $label->assessment_value = (int)($labelData['assessmentValue'] ?? 0);
            if (!$label->save()) {
                throw new PersistErrorException(
                    'Could not save label: ' . $this->formatErrors($label->getErrors())
                );
            }

            $l10ns = is_array($labelData['l10ns'] ?? null) ? $labelData['l10ns'] : [];
            foreach ($languages as $language) {
                $labelL10n = new LabelL10n();
                $labelL10n->label_id = $label->id;
                $labelL10n->language = $language;
                $labelL10n->title = (string)($l10ns[$language] ?? '');
                if (!$labelL10n->save()) {
                    throw new PersistErrorException(
                        'Could not save label translation: ' . $this->formatErrors($labelL10n->getErrors())
                    );
                }
            }
        }
    }

    /**
     * Clean a list of language codes.
     *
     * @param mixed $languages Array, or space or comma separated string, of language codes
     * @return string[] Unique, filtered language codes
     */
    protected function normaliseLanguages($languages): array
    {
        if (is_string($languages)) {
            $languages = preg_split('/[\s,]+/', $languages);
        }
        if (!is_array($languages)) {
            return [];
        }
        $languages = array_map(
            static fn ($language) => LSYii_Validators::languageCodeFilter(trim((string)$language)),
            $languages
        );
        return array_values(array_unique(array_filter($languages)));
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
