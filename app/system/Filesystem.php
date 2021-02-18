<?php
namespace App\System;

use App\System\Filesystem\Exception\DirectoryNotWritableException;

class Filesystem {

    /**
     * @param $path
     * @param false $createIfNotExists
     * @return bool
     * @throws DirectoryNotWritableException
     */
    public static function checkDir($path, $createIfNotExists = false) : bool {
        if (!file_exists($path)) {
            if ($createIfNotExists) {
                mkdir($path, 0777, true);
            } else {
                throw new DirectoryNotWritableException("Directory ".$path." not writable");
            }
        }
        if (!is_writable($path)) {
            if ($createIfNotExists) {
                chmod($path, 0777);
            } else {
                throw new DirectoryNotWritableException("Directory ".$path." not writable");
            }
        }
        if (!is_writable($path)) {
            throw new DirectoryNotWritableException("Directory ".$path." not writable");
        }
        return true;
    }

}