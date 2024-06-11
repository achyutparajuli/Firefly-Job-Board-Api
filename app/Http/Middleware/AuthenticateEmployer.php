<?php

namespace App\Http\Middleware;

use App\Http\Controllers\API\SendResponseController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateEmployer extends SendResponseController
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::User()->user_type != "employer") {
            return $this->sendError('You are not eligible to access this page.', 401);
        }

        return $next($request);
        return $next($request);
    }
}
