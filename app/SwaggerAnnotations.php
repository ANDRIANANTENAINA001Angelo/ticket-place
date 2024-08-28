<?php

namespace App;

/**
 * @OA\OpenApi(
 *     @OA\Info(
 *         version="1.0.0",
 *         title="API Documentation",
 *         description="API documentation with Swagger"
 *     ),
 *     @OA\Server(
 *         url=L5_SWAGGER_CONST_SERVER_URL, // L5_SWAGGER_CONST_SERVER_URL sera remplacé par la variable d'environnement
 *         description="Production Server"
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
