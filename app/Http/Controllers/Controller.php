<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="API Documentation",
 *      description="API documentation for the Ticket Place App",
 *      @OA\Contact(
 *          email="ticket.place@gmail.com"
 *      ),
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * ),
 * @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT",
 *         description="Enter your Bearer token in the format **Bearer &lt;token&gt;**"
 *     )
 */


class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
