<?php

namespace App\Http\Controllers\Auth;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    /**
     * @OA\Post(
     *      path="/api/send-verification-mail",
     *      tags={"Auth"},
     *      summary="Email Vérification",
     *      description="Send email verification",
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

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        try{
            if ($request->user()->hasVerifiedEmail()) {
                return redirect()->to("already-verified");
                // return redirect()->intended(RouteServiceProvider::HOME);
            }
    
            $request->user()->sendEmailVerificationNotification();
    
            return ApiResponse::success(['status' => 'Lien vérification envoyé']);
            // return response()->json(['status' => 'verification-link-sent']);
        }
        catch(Exception $e){
            return ApiResponse::error("error sending email",500,$e->getMessage());
        }
    }
}
