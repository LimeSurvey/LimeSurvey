<?php

/**
 * File extensions by MIME types.
 *
 * Include Yii and extend with new one
 * @version 1.0
 */

return array_merge(
    require(Yii::getPathOfAlias('system.utils.fileExtensions') . '.php'), // Yii framework
    array(
        /* iphone image : issue #15624 */
        'image/heif' => 'heif',
        'image/heif-sequence' => 'heifs',
        'image/heic' => 'heic',
        'image/heic-sequence' => 'heics',
    )
);
