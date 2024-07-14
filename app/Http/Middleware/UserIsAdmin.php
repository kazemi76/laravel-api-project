<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Gate;

class UserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // dd(Gate::allows('admin_only',$request->user()));
        // dd($request->user());
        if (! Gate::allows('admin_only',$request->user())) {
            return response()->json([
                'status' => 'error',
                'message' => 'شما به این بخش دسترسی ندارید',
            ], 403);
        }
        return $next($request);
    }
}
