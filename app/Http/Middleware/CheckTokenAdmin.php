<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Http\Request;
use App\Models\User;

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
   // $role=User::Auth()->role_as;


         if(auth()->user()->role_as==0)

            {
                return response()->json([
                    'status'=>'205',
                    'message'=>'User'
                ]);

            }

          else
            {
                return response()->json([
                    'status'=>'200',
                    'message'=>'Admin'
                ]);
            }

            

    }
}
