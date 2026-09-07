<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ChangelogController extends Controller
{
    public function __invoke(): View
    {
        $releases = config('changelog.releases', []);
        $currentVersion = config('app.version');

        return view('changelog', [
            'releases' => $releases,
            'currentVersion' => $currentVersion,
        ]);
    }
}
