<?php

namespace LimeSurvey\Models\Services;

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
    /** Length of the label_l10ns.language column */
    public const MAX_LANGUAGE_LENGTH = 20;
    /** Length of the labelsets.languages column */
    public const MAX_LANGUAGES_LENGTH = 255;
    /** Range of the labels.assessment_value column (integer) */
    public const MIN_ASSESSMENT_VALUE = -2147483648;
    public const MAX_ASSESSMENT_VALUE = 2147483647;

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
        $labelSets = $this->labelSet->findAll(['order' => 'label_name']);

        $matchingSets = [];
        foreach ($labelSets as $labelSet) {
            if (array_diff($languages, $labelSet->getLanguageArray())) {
                continue;
            }
            // Same check as overwrite(), so plugins that grant or deny update rights per set
            // (beforeHasPermission) are respected. Label sets are few, and this only runs for
            // the overwrite list, so the per-set check is affordable.
            if ($onlyUpdatable && !$labelSet->hasPermission('labelset', 'update')) {
                continue;
            }
            $matchingSets[] = $labelSet;
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
        $languages = $this->validateLanguages($languages);
        $labels = $this->validateLabels($labels, $languages);

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
                $titles[$language] = (string)$title;
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
            static fn ($language) => is_string($language)
                ? LSYii_Validators::languageCodeFilter(trim($language))
                : '',
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
