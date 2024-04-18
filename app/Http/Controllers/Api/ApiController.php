<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

 #[OA\Info(version: "1.0", title: "Merchant")]
 #[OA\SecurityScheme(securityScheme: "bearerAuth",type: "http",scheme: "bearer")]
 #[OA\SecurityScheme(securityScheme: "apiKeyAuth", type: "apiKey", name: "X-Api-Key", in: "header")]
class ApiController extends Controller
{
}