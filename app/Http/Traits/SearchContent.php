<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Http;

trait SearchContent
{
    protected function initSource() : array
    {
        $response = Http::get(config('app.source_url'));

        if ($response->failed()) {
            return response()->json([
                'status' => [
                    'message' => 'Failed to fetch data.',
                    'code' => 500
                ]
            ], 500);
        }

        $data = $response->json();

        if (!isset($data['DATA']) || empty(trim($data['DATA']))) {
            return [];
        }

        $lines = explode("\n", trim($data['DATA']));
        
        $header = explode("|", array_shift($lines));
        
        $index_nim  = array_search('NIM', $header);
        $index_name = array_search('NAMA', $header);
        $index_ymd  = array_search('YMD', $header);

        if ($index_nim === false || $index_name === false || $index_ymd === false) {
            return [];
        }

        $result = [];

        foreach ($lines as $line) {
            $columns = explode("|", $line);
            if (count($columns) >= 3) {
                $result[] = [
                    'name' => trim($columns[$index_name]),
                    'nim'  => trim($columns[$index_nim]),
                    'ymd'  => trim($columns[$index_ymd]),
                ];
            }
        }
        
        return $result;
    }
}
