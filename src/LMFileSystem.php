<?php

namespace LogisticMall\FileSystem;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;


class LMFileSystem
{

    /**
     * Store File From HTTP File
     * @param string $folderPath
     * @param File $file
     * @param string|null $fileName if null generate unique filename
     * @return false|string
     * @throws \Throwable
     */
    public function storeFileFromHttpFile(string $folderPath, File $file, string $fileName = null)
    {
        try {
            if (!$fileName) $fileName = $this->generateUniqueFileName($file->getRealPath());
            return Storage::putFileAs($folderPath, $file, $fileName);
        } catch (\Throwable $e) {
            throw $e;
        }


    }

    /**
     * Store file frm HTTP Upload File
     * @param string $folderPath
     * @param UploadedFile $file
     * @param string|null $fileName
     * @return false|string
     * @throws \Throwable
     */
    public function storeFileFromHttpUploadedFile(string $folderPath, UploadedFile $file, string $fileName = null)
    {
        try {
            if (!$fileName) $fileName = $this->generateUniqueFileName($file->getClientOriginalName());
            return Storage::putFileAs($folderPath, $file, $this->generateUniqueFileName($fileName));
        } catch (\Throwable $e) {
            throw $e;
        }


    }

    /**
     * Store File from file content
     * @param string $folderPath
     * @param string $fileContent
     * @param string $fileName
     * @return string
     * @throws \Throwable
     */
    public function storeFileFromFileContent(string $folderPath, string $fileContent, string $fileName): string
    {
        try {
            $filepath = $folderPath . '/' . $this->generateUniqueFileName($fileName);
            Storage::put($filepath, $fileContent);
            return $filepath;
        } catch (\Throwable $e) {
            throw $e;
        }

    }


    /**
     * Store Image from Http File
     * @param string $folderPath
     * @param File $image
     * @param string|null $fileName
     * @param bool $hasThumbnails
     * @param array $thumbnailsSizes
     * @return array
     * @throws \Throwable
     */
    public function storeImageFromHttpFile(string $folderPath, File $image, string $fileName = null, bool $hasThumbnails = true, array $thumbnailsSizes = []): array
    {
        try {
            $thumbnailsPaths = [];
            if (!$fileName) $fileName = $this->generateUniqueFileName($image->getRealPath());
            if ($hasThumbnails) {
                $this->validationOfThumbnailSizes($thumbnailsSizes);
                $mappedSizes = $this->mappedThumbnailSizes($thumbnailsSizes);
                $thumbnailsPaths = $this->storeThumbnailImages($folderPath, $image->getRealPath(), $fileName, $mappedSizes);
            }
            $originalPath = Storage::putFileAs($folderPath, $image, $fileName);

            return [
                'original' => $originalPath,
                'thumbnails' => $thumbnailsPaths
            ];
        } catch (\Throwable $e) {
            throw $e;
        }


    }

    /**
     * Store Image From Http Upload File
     * @param string $folderPath
     * @param UploadedFile $image
     * @param string|null $fileName
     * @param bool $hasThumbnails
     * @param array $thumbnailsSizes
     * @return array
     * @throws \Throwable
     */
    public function storeImageFromHttpUploadFile(string $folderPath, UploadedFile $image, string $fileName = null, bool $hasThumbnails = true, array $thumbnailsSizes = []): array
    {
        try {
            $thumbnailsPaths = [];
            if (!$fileName) $fileName = $this->generateUniqueFileName($image->getRealPath());
            if ($hasThumbnails) {
                $this->validationOfThumbnailSizes($thumbnailsSizes);
                $mappedSizes = $this->mappedThumbnailSizes($thumbnailsSizes);
                $thumbnailsPaths = $this->storeThumbnailImages($folderPath, $image->getRealPath(), $fileName, $mappedSizes);
            }
            $originalPath = Storage::putFileAs($folderPath, $image, $fileName);

            return [
                'original' => $originalPath,
                'thumbnails' => $thumbnailsPaths
            ];
        } catch (\Throwable $e) {
            throw $e;
        }


    }

    /**
     * @param $thumbnailsSizes
     * @throws \Throwable
     */
    private function validationOfThumbnailSizes($thumbnailsSizes)
    {
        try {
            if (count($thumbnailsSizes) > 0) {
                if (!$this->isAssoc($thumbnailsSizes)) throw new \Exception('The array must be associative array type.');
                $arraySizes = ['sm', 'lg', 'md'];
                foreach ($thumbnailsSizes as $size => $sizeArray) {
                    if (!in_array((string)$size, $arraySizes)) throw new \Exception('The thumbnail size (' . $size . ') is invalid. Supports only [' . implode(',', $arraySizes) . '].');
                    if (!is_array($sizeArray)) throw new \Exception('The thumbnail size should be in array contains with width or height.');
                    if (count($sizeArray) > 2 || count($sizeArray) <= 0) {
                        throw new \Exception('The thumbnail size array length should not be greater than 2 or less or zero');
                    }

                    if (isset($sizeArray[0]) && !is_int($sizeArray[0])) {
                        throw new \Exception('The width value should not be string in ' . $size . ' key in array.');
                    }
                    if (isset($sizeArray[1]) && !is_int($sizeArray[1])) {
                        throw new \Exception('The height value should not be string in ' . $size . ' key in array.');
                    }


                }
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * @param $thumbnailsSizes
     * @return \int[][]
     */
    private function mappedThumbnailSizes($thumbnailsSizes): array
    {
        $sizes = [
            'sm' => [100, 100],
            'md' => [200, 200],
            'lg' => [400, 400],
        ];
        foreach ($thumbnailsSizes as $size => $sizeArray) {
            if ($size == 'sm') $sizes['sm'] = $sizeArray;
            if ($size == 'md') $sizes['md'] = $sizeArray;
            if ($size == 'lg') $sizes['lg'] = $sizeArray;
        }
        return $sizes;
    }

    private function storeThumbnailImages($folderPath, $realPathFile, $fileName, $sizes): array
    {
        try {

            $thumbnailsPaths = [];
            foreach ($sizes as $size => $values) {
                $h = null;
                $w = null;
                switch ($size) {
                    case 'sm':
                        $h = @$sizes['sm'][1] ? $sizes['sm'][1] : null;
                        $w = @$sizes['sm'][0] ? $sizes['sm'][0] : null;
                        $hasSize = true;
                        break;
                    case 'md':
                        $h = @$sizes['md'][1] ? $sizes['md'][1] : null;
                        $w = @$sizes['md'][0] ? $sizes['md'][0] : null;
                        $hasSize = true;
                        break;
                    case 'lg':
                        $h = @$sizes['lg'][1] ? $sizes['lg'][1] : null;
                        $w = @$sizes['lg'][0] ? $sizes['lg'][0] : null;
                        $hasSize = true;
                        break;
                    default:
                        $hasSize = false;
                }
                if ($hasSize) {
                    $thumbnailsName = $this->generateThumbnailsFilename($fileName, $w, $h);
                    $convertImage = $this->convertImage($realPathFile, $w, $h);
                    $filePath = $folderPath . '/thumbnails/' . $thumbnailsName;
                    Storage::put($filePath, $convertImage->__toString());
                    array_push($thumbnailsPaths, $filePath);

                }

            }
            return $thumbnailsPaths;


        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * @param $realPath
     * @param int|null $height
     * @param int|null $width
     * @return \Psr\Http\Message\StreamInterface
     * @throws \Throwable
     */
    private function convertImage($realPath, int $height = null, int $width = null): \Psr\Http\Message\StreamInterface
    {
        try {
            return Image::make($realPath)->resize($width, $height)->stream();
        } catch (\Throwable $e) {
            throw $e;
        }
    }


    /**
     * Get File as string
     * @param string $filepath
     * @return string
     * @throws \Throwable
     */
    public function getFileAsString(string $filepath): string
    {
        try {
            if (!$this->checkFile($filepath)) throw new \Exception('No file(' . $filepath . ') found to get as string.');
            return Storage::get($filepath);
        } catch (\Throwable $e) {
            throw $e;
        }


    }

    /**
     * Get File as URL
     * @param string $filepath
     * @return string
     * @throws \Throwable
     */
    public function getFileAsURL(string $filepath): string
    {

        try {
            if (!$this->checkFile($filepath)) throw new \Exception('No file(' . $filepath . ') found to get as URl.');
            return Storage::get($filepath);
        } catch (\Throwable $e) {
            throw $e;
        }


    }

    /**
     * Download file
     * @param string $filepath
     * @return mixed
     * @throws \Throwable
     */
    public function downloadFile(string $filepath, string $name = null, array $headers = [])
    {

        try {
            if (!$this->checkFile($filepath)) throw new \Exception('No file(' . $filepath . ') found to download.');
            return Storage::download($filepath, $name, $headers);

        } catch (\Throwable $e) {
            throw $e;
        }


    }


    /**
     * @param $filepath
     * @return bool
     * @throws \Exception
     */
    public function deleteFile($filepath): bool
    {
        try {

            if (!$this->checkFile($filepath)) throw new \Exception('No file(' . $filepath . ') found to remove.');
            return Storage::delete($filepath);

        } catch (\Throwable $e) {
            throw $e;
        }


    }


    /**
     * check file
     * @param string $path
     * @return bool
     */
    public function checkFile(string $path): bool
    {
        return Storage::exists($path);
    }

    /**
     *  get list of files
     * @param string $folderPath
     * @return array
     */
    public function getFiles(string $folderPath): array
    {
        try {
            return Storage::files($folderPath);
        } catch (\Throwable $e) {
            throw $e;
        }

    }

    /**
     * get list of files with sub folders too
     * @param string $folderPath
     * @return array
     */
    public function getFilesWithSubFiles(string $folderPath): array
    {
        try {
            return Storage::allFiles($folderPath);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * check file
     * @param string $filepath
     * @return bool
     */
    public function checkFolder(string $path): bool
    {
        return Storage::exists($path);
    }

    /**
     * make or create folder
     * @param string $path
     */
    public function makeFolder(string $path)
    {
        try {
//            if ($this->checkFolder($path)) throw new \Exception('The folder ' . $path . ' has been already make..');
            Storage::makeDirectory($path);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * Delete Folder
     * @param $path
     * @return bool
     * @throws \Throwable
     */
    public function deleteFolder($path): bool
    {
        try {

            if (!$this->checkFile($path)) throw new \Exception('No folder(' . $path . ') found to remove.');
            return Storage::deleteDirectory($path);

        } catch (\Throwable $e) {
            throw $e;
        }


    }


    /**
     *  get list of folders
     * @param string $path
     * @return array
     */
    public function getDirectories(string $path): array
    {
        try {
            return Storage::directories($path);
        } catch (\Throwable $e) {
            throw $e;
        }

    }

    /**
     *  get list of folders with sub folders
     * @param string $path
     * @return array
     */
    public function getDirectoriesWithSubDirectories(string $path): array
    {
        try {
            return Storage::allDirectories($path);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * Generate Unique Filename
     * @param $fileName
     * @return string
     */
    public function generateUniqueFileName($fileName): string
    {
        $fileInfo = pathinfo($fileName); //Get just filename info
        $timestamp = date('YmdHis');

        return substr($fileInfo['filename'], 0, 10) . '_' . $timestamp . '.' . $fileInfo['extension'];
    }

    /**
     * generate thumbnail name
     * @param $fileName
     * @param $w
     * @param $h
     * @return string
     */
    public function generateThumbnailsFilename($fileName, $w, $h): string
    {
        $fileInfo = pathinfo($fileName); //Get just filename info
        if (!$w) $w = 'auto';
        if (!$h) $h = 'auto';
        $sizeInString = $w . 'x' . $h;
        return $fileInfo['filename'] . '_' . $sizeInString . '.' . $fileInfo['extension'];
    }

    /**
     * check array is associative array or not
     * @param array $arr
     * @return bool
     */
    private function isAssoc(array $arr): bool
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

}
