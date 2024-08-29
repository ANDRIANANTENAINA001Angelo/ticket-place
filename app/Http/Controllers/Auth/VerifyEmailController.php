<?php

namespace App\Http\Controllers\Auth;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    /**
     * @OA\Get(
     *      path="/verify-email/{id}/{hash}",
     *      tags={"Auth"},
     *      summary="Verify Email with token",
     *      description="Verify Email using token",
     *       @OA\Parameter(
     *              name="id",
     *              in="path",
     *              required=true,
     *              @OA\Schema(
     *                  type="string"
     *              ),
     *              description="ID user"
     *            ),
     *       @OA\Parameter(
     *              name="hash",
     *              in="path",
     *              required=true,
     *              @OA\Schema(
     *                  type="string"
     *              ),
     *              description="hash"
     *            ),
     *          @OA\Response(
     *              response=200,
     *              description="successful operation"
     *          ),
     *          @OA\Response(
     *              response=401,
     *              description="action unauthorized"
     *          ),
     *          @OA\Response(
     *              response=403,
     *              description="action forbiden"
     *          ),
     *          @OA\Response(
     *              response=404,
     *              description="aucun résultat trouvé"
     *          ),
     *          @OA\Response(
     *              response=500,
     *              description="erreur serveur"
     *          )
     *)  
     */
    
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->to("already-verified");
            // return redirect()->intended(
                //     config('app.frontend_url').RouteServiceProvider::HOME.'?verified=1'
                // );
            }
            
        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }
        
        return redirect()->to("home");    
        // return redirect()->intended(
        //     config('app.frontend_url').RouteServiceProvider::HOME.'?verified=1'
        // );
    }


    /**
     * @OA\Get(
     *      path="/api/verify-email/{id}/{hash}",
     *      tags={"Auth"},
     *      summary="Verify Email no token",
     *      description="Verify Email no token",
     *       @OA\Parameter(
     *              name="id",
     *              in="path",
     *              required=true,
     *              @OA\Schema(
     *                  type="string"
     *              ),
     *              description="ID user"
     *            ),
     *       @OA\Parameter(
     *              name="hash",
     *              in="path",
     *              required=true,
     *              @OA\Schema(
     *                  type="string"
     *              ),
     *              description="hash"
     *            ),
     *          @OA\Response(
     *              response=200,
     *              description="successful operation"
     *          ),
     *          @OA\Response(
     *              response=401,
     *              description="action unauthorized"
     *          ),
     *          @OA\Response(
     *              response=403,
     *              description="action forbiden"
     *          ),
     *          @OA\Response(
     *              response=404,
     *              description="aucun résultat trouvé"
     *          ),
     *          @OA\Response(
     *              response=500,
     *              description="erreur serveur"
     *          )
     *)  
     */
    public function verify(Request $request,string $id,string $hash)
    {
        $user = User::findOrFail($id);
        
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            // return response()->json(['message' => 'Invalid verification link'], 403);
            return ApiResponse::error('Invalid verification link', 403);
        }
        
        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success([],'Email already verified');
            // return response()->json(['message' => 'Email already verified'], 200);
        }

        
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            return ApiResponse::success(["user"=>$user],"Email verified successfully");
        }

        
        // return redirect()->to("home");    
        // return redirect()->intended(
        //     config('app.frontend_url').RouteServiceProvider::HOME.'?verified=1'
        // );
    }
}
