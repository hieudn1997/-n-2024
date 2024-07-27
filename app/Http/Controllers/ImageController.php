<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;

class ImageController extends Controller
{
    protected $store;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FirebaseService $store)
    {
        $this->store = $store;
    }

    public function getImage($uid, $imageName)
    {
        $path = $uid . '/' . $imageName;
        return $this->store->download($path);
    }
}
