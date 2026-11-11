<?php

namespace LimeSurvey\Models\Services;

use CDbConnection;
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
    protected LabelSet $labelSet;
    protected Permission $permission;
    protected LSYii_Application $app;

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
     * @return array<int, array{lid: int, name: string, languages: string[], labelCount: int}>
     */
    public function getLabelSets(array $languages = [], bool $onlyUpdatable = false): array
    {
        $languages = $this->normaliseLanguages($languages);
        $labelSets = $this->labelSet->with('labels')->findAll(['order' => 'label_name']);

        $result = [];
        foreach ($labelSets as $labelSet) {
            $setLanguages = $labelSet->getLanguageArray();
            if (array_diff($languages, $setLanguages)) {
                continue;
            }
            if ($onlyUpdatable && !$labelSet->hasPermission('labelset', 'update')) {
                continue;
            }
            $result[] = [
                'lid' => (int)$labelSet->lid,
                'name' => flattenText($labelSet->label_name),
                'languages' => $setLanguages,
                'labelCount' => count($labelSet->labels),
            ];
        }
        return $result;
    }

    /**
     * Get one label set with its labels and their translations.
     *
     * @param int $lid
     * @return array{lid: int, name: string, languages: string[], labels: array}
     * @throws NotFoundException
     */
    public function getLabelSet(int $lid): array
    {
        $labelSet = $this->findLabelSet($lid);

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
     * @param string $name
     * @param string[] $languages
     * @param array $labels
     * @return LabelSet
     * @throws PermissionDeniedException
     * @throws BadRequestException
     * @throws PersistErrorException
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
     * @param int $lid
     * @param string[] $languages
     * @param array $labels
     * @return LabelSet
     * @throws NotFoundException
     * @throws PermissionDeniedException
     * @throws BadRequestException
     * @throws PersistErrorException
     */
    public function overwrite(int $lid, array $languages, array $labels): LabelSet
    {
        $labelSet = $this->findLabelSet($lid);
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
     * @param int $lid
     * @return LabelSet
     * @throws NotFoundException
     */
    protected function findLabelSet(int $lid): LabelSet
    {
        $labelSet = $this->labelSet->findByPk($lid);
        if (!$labelSet) {
            throw new NotFoundException('Label set not found');
        }
        return $labelSet;
    }

    /**
     * @param string[] $languages
     * @param array $labels
     * @return string[] The cleaned language list
     * @throws BadRequestException
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
            // Same rule as the Label model; checked up front so nothing is written
            // when a code is invalid (transactions are no-ops on MyISAM tables).
            if (!preg_match('/^[[:alnum:]]+$/', $code)) {
                throw new BadRequestException(
                    sprintf('Label code "%s" may only contain alphanumeric characters', $code)
                );
            }
            if (isset($codes[$code])) {
                throw new BadRequestException(sprintf('Label code "%s" is used twice', $code));
            }
            $codes[$code] = true;
        }
        return $languages;
    }

    /**
     * Delete all labels of the set and create the given ones.
     * Only translations for $languages are saved; a missing translation is saved as empty text.
     *
     * @param int $lid
     * @param string[] $languages
     * @param array $labels
     * @return void
     * @throws PersistErrorException
     */
    protected function replaceLabels(int $lid, array $languages, array $labels): void
    {
        $oldLabelIds = array_map(
            static fn ($label) => (int)$label->id,
            Label::model()->findAllByAttributes(['lid' => $lid])
        );
        if ($oldLabelIds) {
            LabelL10n::model()->deleteAllByAttributes(['label_id' => $oldLabelIds]);
            Label::model()->deleteAllByAttributes(['lid' => $lid]);
        }

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
     * @param mixed $languages Array or space/comma separated string
     * @return string[]
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
            static fn ($language) => \LSYii_Validators::languageCodeFilter(trim((string)$language)),
            $languages
        );
        return array_values(array_unique(array_filter($languages)));
    }

    /**
     * @param callable $callback
     * @return mixed
     * @throws PersistErrorException
     */
    protected function inTransaction(callable $callback)
    {
        /** @var CDbConnection $db */
        $db = $this->app->db;
        $transaction = $db->beginTransaction();
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
     * @param array $errors
     * @return string
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
