<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;

class PopulateController extends Controller
{
    public function populate(Request $request)
    {
        $source = $request->get('source', 'jikan');
        $type = $request->get('type', 'anime');
        $limit = $request->get('limit', 50);

        $exitCode = Artisan::call("works:populate", [
            '--source=' => $source,
            '--type=' => $type,
            '--limit=' => $limit,
        ]);

        return response()->json([
            'message' => 'Populate completed',
            'output' => Artisan::output(),
            'exitCode' => $exitCode,
        ]);
    }

    public function populateAll()
    {
        $configs = [
            ['--source' => 'jikan', '--type' => 'anime', '--limit' => 50],
            ['--source' => 'jikan', '--type' => 'manga', '--limit' => 50],
            ['--source' => 'omdb', '--type' => 'movie', '--limit' => 30],
        ];

        $results = [];

        foreach ($configs as $config) {
            Artisan::call("works:populate", $config);
            $results[] = [
                'config' => $config,
                'output' => Artisan::output(),
            ];
        }

        return response()->json([
            'message' => 'All populates completed',
            'results' => $results,
        ]);
    }
}