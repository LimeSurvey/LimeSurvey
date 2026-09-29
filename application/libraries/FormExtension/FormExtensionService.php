<?php

namespace LimeSurvey\Libraries\FormExtension;

use Exception;
use InvalidArgumentException;
use CHttpRequest;
use Yii;

class FormExtensionService
{
    /** @var array<string, array<Inputs\RawHtmlInput|Inputs\BaseInput>> List of inputs, mapped by formname.tabname => input list */
    private $inputs = [];

    // Required by Yii
    public function init(): void
    {
    }

    /**
     * @param string $position The form position, e.g. "globalsettings" or "globalsettings.email_settings"
     * @param Inputs\RawHtmlInput|Inputs\BaseInput $input
     * @return void
     */
    public function add(string $position, $input): void
    {
        if ($this->validatePosition($position)) {
            if (empty($this->inputs[$position])) {
                $this->inputs[$position] = [];
            }
            $this->inputs[$position][] = $input;
        } else {
            throw new InvalidArgumentException('Position is not supported: ' . $position);
        }
    }

    /**
     * Apply all save-functions for all inputs for this $position (including all tabs).
     * Returns true if all save was successful; else false
     * Will add warning flash messages for each failed input save.
     */
    public function applySave(string $position, CHttpRequest $request): bool
    {
        $db = Yii::app()->db;
        $inputs = $this->getAllForPosition($position);
        $success = true;
        foreach ($inputs as $input) {
            try {
                if ($input instanceof Inputs\BaseInput) {
                    $input->save($request, $db);
                }
            } catch (SaveFailedException $ex) {
                $success = false;
                Yii::app()->setFlashMessage($ex->getMessage(), 'warning');
            }
        }
        return $success;
    }

    /**
     * Used by widget to render all inputs for a certain position.
     */
    public function getAll(string $position): array
    {
        return $this->inputs[$position] ?? [];
    }

    /**
     * Returns false if position is not yet supported by LS.
     * This works as a allowlist of supported forms.
     */
    private function validatePosition(string $position): bool
    {
        $allowed = [
            'globalsettings.general' => 1
        ];
        return isset($allowed[$position]);
    }

    /**
     * Get all tabs for a position
     *
     * @return array<Inputs\BaseInput|Inputs\RawHtmlInput>
     */
    private function getAllForPosition(string $position): array
    {
        $return = [];
        foreach ($this->inputs as $pos => $inputList) {
            if (strpos($pos, $position) === 0) {
                $return = array_merge($return, $inputList);
            }
        }
        return $return;
    }
}
