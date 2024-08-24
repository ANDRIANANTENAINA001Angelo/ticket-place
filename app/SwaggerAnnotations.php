<?php

namespace App;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="API Documentation",
 *         description="API documentation with Swagger"
 *     ),
 *     @OA\Components(
 *         @OA\SecurityScheme(
 *             securityScheme="bearerAuth",
 *             type="http",
 *             scheme="bearer",
 *             bearerFormat="JWT",
 *             description="Enter your Bearer token in the format **Bearer &lt;token&gt;**"
 *         )
 *     )
 * )
 */
