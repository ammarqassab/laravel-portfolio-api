<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Http\Request;

class CheckTokenAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {


            if(auth()->user()->tokenCan('server:admin'))
            {
                return response()->json([
                    'status'=>'200',
                    'message'=>'Admin'
                ]);
            }

            if(auth()->user()->tokenCan('server:user'))

            {
                return response()->json([
                    'status'=>'205',
                    'message'=>'User'
                ]);

            }

    }
}
