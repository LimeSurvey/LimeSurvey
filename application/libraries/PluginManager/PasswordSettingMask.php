<?php

namespace LimeSurvey\PluginManager;

/**
 * Keeps the stored value of plugin settings of type "password" out of the settings form.
 *
 * Before the settings are rendered, mask() replaces every stored password by a placeholder.
 * When the form is saved, restore() puts the stored password back wherever the placeholder
 * was submitted unchanged, so plugins receive the same values as if the password had been
 * sent to the browser and back.
 *
 * The placeholder is never saved as a password: Where the stored password is not known when
 * the form is saved, the setting is left out of the values to save. This also means that the
 * text of the placeholder can not be used as a password.
 *
 * Plugins that render the settings widget and save the values on their own can use the
 * same two methods.
 */
class PasswordSettingMask
{
    /**
     * Value shown in the form instead of a stored password.
     * Same placeholder as the SMTP and the bounce account password of core.
     */
    public const PLACEHOLDER = 'somepassword';

    /**
     * Replaces the current value of every password setting by the placeholder.
     * Settings without a stored value are left empty, so the form still shows
     * whether a password is set.
     *
     * @param array<string,mixed> $settings Setting definitions including their "current" value
     * @return array<string,mixed> The same definitions, safe to render
     */
    public static function mask(array $settings): array
    {
        foreach ($settings as $name => $setting) {
            if (self::isPasswordSetting($setting) && isset($setting['current'])) {
                $settings[$name]['current'] = self::maskValue($setting['current']);
            }
        }
        return $settings;
    }

    /**
     * Puts the stored value back for every password setting that was submitted
     * with the placeholder, which means the user did not change it.
     *
     * A submitted placeholder is only kept as it is for settings that are known not to be a
     * password. If the stored password is not known, because the setting has no "current"
     * value or no definition at all, the setting is removed from the returned values and
     * therefore stays untouched when the caller saves them.
     *
     * @param array<string,mixed> $submitted Submitted values indexed by setting name
     * @param array<string,mixed> $settings Unmasked setting definitions including their "current" value
     * @return array<string,mixed> The submitted values, ready to be saved
     */
    public static function restore(array $submitted, array $settings): array
    {
        $candidates = [];
        $stored = [];
        foreach ($submitted as $name => $value) {
            $setting = $settings[$name] ?? null;
            if (is_array($setting) && !self::isPasswordSetting($setting)) {
                // Known as another setting type: The placeholder text is a normal value there
                continue;
            }
            $candidates[$name] = $value;
            if (is_array($setting) && array_key_exists('current', $setting)) {
                $stored[$name] = $setting['current'];
            }
        }
        $restored = self::restoreValues($candidates, $stored);
        foreach (array_keys($candidates) as $name) {
            if (array_key_exists($name, $restored)) {
                $submitted[$name] = $restored[$name];
            } else {
                unset($submitted[$name]);
            }
        }
        return $submitted;
    }

    /**
     * Tells if any of the submitted values is the placeholder.
     * Allows callers to skip loading the stored values when nothing has to be restored.
     *
     * @param array<array-key,mixed> $submitted Submitted values, nested arrays are searched too
     * @return boolean True if at least one value is the placeholder
     */
    public static function containsPlaceholder(array $submitted): bool
    {
        $found = false;
        array_walk_recursive($submitted, function ($value) use (&$found) {
            if ($value === self::PLACEHOLDER) {
                $found = true;
            }
        });
        return $found;
    }

    /**
     * Tells if a setting definition is rendered as a password field.
     * The widget finds its render method by name, which is not case-sensitive.
     *
     * @param mixed $setting Setting definition
     * @return boolean True if the setting is of type "password"
     */
    private static function isPasswordSetting($setting): bool
    {
        return is_array($setting)
            && isset($setting['type'])
            && is_string($setting['type'])
            && strtolower($setting['type']) === 'password';
    }

    /**
     * Tells if a stored value is replaced by the placeholder.
     *
     * @param mixed $value Stored value
     * @return boolean True if the value is not empty, so the form shows the placeholder for it
     */
    private static function isMasked($value): bool
    {
        return is_scalar($value) && (string) $value !== '';
    }

    /**
     * Replaces a stored value by the placeholder.
     * A localized setting stores one value per language, each one is replaced.
     *
     * @param mixed $value Stored value
     * @return mixed The placeholder for a stored value, the unchanged value if nothing is stored
     */
    private static function maskValue($value)
    {
        if (is_array($value)) {
            return array_map([self::class, 'maskValue'], $value);
        }
        return self::isMasked($value) ? self::PLACEHOLDER : $value;
    }

    /**
     * Replaces every submitted placeholder by the stored value with the same key.
     * A placeholder without a known stored value is removed, it must never be saved.
     * A localized setting submits one value per language, each one has its own stored value.
     *
     * @param array<array-key,mixed> $values Submitted values
     * @param array<array-key,mixed> $stored Stored values with the same keys, a missing key means not known
     * @return array<array-key,mixed> The values to save
     */
    private static function restoreValues(array $values, array $stored): array
    {
        foreach ($values as $key => $value) {
            $isStoredKnown = array_key_exists($key, $stored);
            if (is_array($value)) {
                $values[$key] = self::restoreValues(
                    $value,
                    $isStoredKnown && is_array($stored[$key]) ? $stored[$key] : []
                );
            } elseif ($value === self::PLACEHOLDER) {
                if ($isStoredKnown && !is_array($stored[$key])) {
                    $values[$key] = $stored[$key];
                } else {
                    unset($values[$key]);
                }
            }
        }
        return $values;
    }
}
