<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PartnerHomeController extends Controller
{
    public function __invoke(): View
    {
        return view('partner.home');
    }
}
