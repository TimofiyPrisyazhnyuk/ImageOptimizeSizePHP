<?php

namespace app\commands;

use bigbrush\tinypng\TinyPng;
use yii\console\Controller;

/**
 * Class CacheController
 *
 * @author Prisyazhnyuk Timofiy
 * @package app\commands
 */
class OptimizeImageController extends Controller
{

    /**
     * Quality optimize gif images
     */
    const OPTIMIZE_GIF = 60;

    /**
     * Quality optimize jpeg images
     */
    const OPTIMIZE_JPEG = 60;

    /**
     * Link for account TinyPng https://tinypng.com
     */
    const TINY_API_KEY = "bglMg4xmzCnK1CkT8T5XYpt3tr54LY5j";

    /**
     *  Min file size for compress
     */
    const MIN_FILE_SIZE = 2000;

    /**
     * Define array paths to store images for fo yii2 project
     */
    const IMAGE_PATHS = [
        'web/themes/transitional/images/',
        'web/themes/transitional/imgs/',
        'web/themes/bo/images/',
        'web/themes/basic/images/',
        'web/imgs/prettyPhoto/facebook/',
        'web/imgs/',
    ];

    /**
     * Optimize image size in directories
     *
     * @return bool
     * @throws \Exception
     */
    public function actionOptimizeImageSize()
    {
        try {
            foreach (self::IMAGE_PATHS as $path) {
                // get all files and directories from paths
                $allFilesInDirectory = scandir($path, null);
                foreach ($allFilesInDirectory as $file) {
                    // check files without base directories (. ..)
                    if ($file !== '.' && $file !== '..') {
                        $fileName = $path . $file;
                        $this->getImageFiles($fileName);
                    }
                }
                // show message finish compress current path images
                print_r('Path ' . $path . ' finish compress images');
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed get optimize files by path ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Get image if it file is directory get all
     * command run: php yii optimize-image/optimize-image-size
     *
     * @param string $fileName
     *
     * @return string
     * @throws \Exception
     */
    public function getImageFiles($fileName)
    {
        // if $file is directory
        if (is_dir($fileName) && !is_file($fileName)) {
            $twoStepsDepthDirectory = scandir($fileName, null);
            // get all files from directory
            foreach ($twoStepsDepthDirectory as $item) {
                $fileNameByDirectory = $fileName . '/' . $item;
                if (is_file($fileNameByDirectory)) {
                    $this->compressImageFiles($fileNameByDirectory);
                }
            }
            // if $file is file
        } else {
            $this->compressImageFiles($fileName);
        }
    }

    /**
     * Compress images for types png, gif, jpeg
     *
     * @param string $fileName
     *
     * @return mixed
     * @throws \Exception
     */
    public function compressImageFiles($fileName)
    {
        try {
            $fileInfo = getimagesize($fileName);
            $fileSize = filesize($fileName);

            // compress image by file type
            if (!empty($fileInfo['mime']) && $fileSize >= self::MIN_FILE_SIZE) {
                if ($fileInfo['mime'] == 'image/jpeg') {

                    $imageJpeg = imagecreatefromjpeg($fileName);
                    imagejpeg($imageJpeg, $fileName, self::OPTIMIZE_JPEG);
                } elseif ($fileInfo['mime'] == 'image/gif') {

                    $imageGif = imagecreatefromgif($fileName);
                    imagegif($imageGif, $fileName, self::OPTIMIZE_GIF);
                } elseif ($fileInfo['mime'] == 'image/png') {

                    $imagePng = new TinyPng(['apiKey' => self::TINY_API_KEY]);
                    $imagePng->compress($fileName);
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed compress image ' . $e->getMessage());
        }
    }

}
