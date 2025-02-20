<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

trait ApiResponse
{
    public function apiResponse($code, $message, $data, $httpCode)
    {
        return response()->json([
            "statusCode" => $code,
            "message" => $message,
            "data" => $data
        ], $httpCode);
    }
    
    public function paginationResponse($data)
    {

        return [
            "total"=> $data->total(),
            "total_of_pages"=> null,
            "per_page"=> $data->perPage(),
            "sorts"=> [
                "id"
            ],
            "order"=> "desc",
            "current_page"=> $data->currentPage(),
            "next_page"=> $data->nextPageUrl(),
            "previous_page"=> $data->previousPageUrl(),
            "filters"=> null,
            "items"=> $data->items()
        ];
    }
}
