<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $membership = $request->user()->memberships()
            ->with('branch.business')
            ->where('branch_id', $request->route('branch'))
            ->firstOrFail();
        $request->attributes->set('membership', $membership);

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
