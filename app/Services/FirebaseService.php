<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Factory;

class FirebaseService extends BaseService
{
    private $storage;

    public function __construct()
    {
        $this->storage = (new Factory())->withServiceAccount(Config('firebase'))->createStorage();
    }

    public function upload($file)
    {
        try {
            $uid = Auth::id();
            $fileName = getRandomString() . '.' . $file->extension();
            $filePath = $uid . '/' . $fileName;
            $this->storage->getBucket()->upload(
                file_get_contents($file),
                ['name' => $filePath]
            );

            return $filePath;
        } catch (Exception $e) {
            return throw $e;
        }
    }

    public function download($filePath)
    {
        $bucket = $this->storage->getBucket();
        $object = $bucket->object($filePath);
        $url = $object->downloadAsString();

        return $url;
    }
}
