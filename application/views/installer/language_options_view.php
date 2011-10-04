<?php
/**
 * subview to list all languages as option tags (to be used within <select></select>
 *
 * Used By:
 *    - optconfig_view.php
 *    - welcome_view.php
 */
    $langauges = getlanguagedata(true,true);
    foreach($langauges as $langkey => $languagekind)
    {
        $selected = $langkey === 'en';
        $value = $langkey;
        $label = sprintf('%s - %s', $languagekind['nativedescription'], $languagekind['description']);
        echo '<option value="', htmlspecialchars($langkey), '"', $selected ? ' selected="selected"' : '', ">",
            $label, '</option>', "\n";
    }