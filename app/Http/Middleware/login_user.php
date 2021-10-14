<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Route;
use Closure;
use GuzzleHttp\Psr7\Request;

class login_user
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        session_start();
        if(!isset($_SESSION['private'])){
            
            return redirect()->to('login-user')->with(['url'=> url()->current()]);
        }
        else
        return $next($request);
    }
}
