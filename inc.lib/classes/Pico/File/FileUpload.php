<?php

namespace Pico\File;

class FileUpload
{
    private $maxLength = 25000000;
    public function upload($files, $name, $targetDir, $targetName)
    {
        $errors = array();
        $file_name = $files[$name]['name'];
        $file_size = $files[$name]['size'];
        $file_tmp = $files[$name]['tmp_name'];
        $file_type = $files[$name]['type'];

        $arr = explode('.', $files[$name]['name']);
        $file_ext = strtolower(end($arr));
        $path = rtrim($targetDir, "/") . "/" . $targetName. ".".$file_ext;

        $extensions = array("mp3");

        if (in_array($file_ext, $extensions) === false) {
            $errors[] = "extension not allowed, please choose a JPEG or PNG file.";
        }

        if ($file_size > $this->maxLength) {
            $errors[] = 'File size must be excately 2 MB';
        }

        if (empty($errors)) {
            move_uploaded_file($file_tmp, $path);
        }

        return $path;
    }

    
}
