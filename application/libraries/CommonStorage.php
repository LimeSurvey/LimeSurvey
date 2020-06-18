<?php
use Google\Cloud\Storage\StorageClient;

class CommonStorage {
        public static function upload_file($filename) {
                $bucket_name = Yii::app()->params['bucket'];

                $storage = new StorageClient(array('keyFilePath' => Yii::app()->params['key_file_path']));
                $destination = str_replace(dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR, '', $filename);
                $bucket = $storage->bucket($bucket_name);
                $bucket->upload( fopen($filename, 'r'), array(
                        'name' => $destination,
                ) );
        }
}