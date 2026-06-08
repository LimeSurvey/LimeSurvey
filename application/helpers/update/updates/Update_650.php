<?php

namespace LimeSurvey\Helpers\Update;

use CException;

class Update_650 extends DatabaseUpdateBase
{
    /**
     * Adds default 'type' and 'type_options' keys to participant attribute descriptions
     * for surveys that were created before AT-1771 (participant attribute types).
     *
     * @throws CException If a database update operation fails.
     */
    public function up()
    {
        // Only fetch sid and attributedescriptions to minimize DB load.
        $rows = $this->db->createCommand()
            ->select('sid, attributedescriptions')
            ->from('{{surveys}}')
            ->where("attributedescriptions IS NOT NULL AND attributedescriptions != :empty", [':empty' => ''])
            ->queryAll();

        foreach ($rows as $row) {
            $raw = $row['attributedescriptions'];

            // Skip non-JSON values (legacy serialized data should not exist at this DB version, but be safe).
            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                continue;
            }

            $modified = false;
            foreach ($decoded as $key => &$attributes) {
                // Only process additional attributes (attribute_1, attribute_2, …).
                if (!preg_match('/^attribute_\d+$/', $key)) {
                    continue;
                }
                if (!is_array($attributes)) {
                    continue;
                }

                // Skip entries that only contain "encrypted" – those are default token fields, not custom attributes.
                if (count($attributes) <= 1 && array_key_exists('encrypted', $attributes)) {
                    continue;
                }

                // Add 'type' with default 'TB' (text box) if missing.
                if (!array_key_exists('type', $attributes)) {
                    $attributes['type'] = 'TB';
                    $modified = true;
                }

                // Add 'type_options' with default empty JSON array if missing.
                if (!array_key_exists('type_options', $attributes)) {
                    $attributes['type_options'] = '[]';
                    $modified = true;
                }
            }
            unset($attributes);

            if ($modified) {
                $this->db->createCommand()->update(
                    '{{surveys}}',
                    ['attributedescriptions' => json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
                    'sid = :sid',
                    [':sid' => $row['sid']]
                );
            }
        }
    }
}