<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ApiDocumentation;
use Illuminate\View\View;

class ApiDocsController extends Controller
{
    public function __invoke(): View
    {
        abort_unless(auth('web')->user()?->canViewApiDocs(), 403);

        return view('admin.settings.api-docs', [
            'overview' => ApiDocumentation::overview(),
            'sections' => ApiDocumentation::sections(),
            'roleMatrix' => ApiDocumentation::roleMatrix(),
            'enums' => ApiDocumentation::enums(),
            'schemas' => ApiDocumentation::schemas(),
        ]);
    }
}
