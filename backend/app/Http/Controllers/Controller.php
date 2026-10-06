<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

abstract class Controller
{
    protected function workspaceView(Request $request, string $view, array $data = []): View
    {
        return view($view, array_merge($data, [
            'membership' => $request->attributes->get('membership'),
            'memberships' => $request->user()->memberships()->with('branch.business')->orderBy('branch_id')->get(),
        ]));
    }
}
