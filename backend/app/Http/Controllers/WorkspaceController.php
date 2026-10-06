<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function home(Request $request): RedirectResponse
    {
        $membership = $request->user()->memberships()->orderBy('branch_id')->first();
        abort_if($membership === null, 403, 'tu cuenta no tiene una sucursal asignada.');

        return redirect()->route('workspace', ['branch' => $membership->branch_id]);
    }

    public function show(Request $request): View
    {
        return $this->workspaceView($request, 'workspace');
    }
}
