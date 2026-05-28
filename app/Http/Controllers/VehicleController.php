<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    public function show(Vehicle $vehicle): JsonResponse
    {
        // TODO: return the vehicle and increment its view counter.
        //   This endpoint will be hit ~50 req/sec at peak.
        //   Naive UPDATE … SET views = views + 1 on every request is the wrong answer.


        //SOLUTION
        $bucketStart = now()->startofMinute();
        DB::table('vehicle_views')->upsert(
            [
                [
                    'vehicle_id' => $vehicle->id,
                    'bucket_start' => $bucketStart,
                    'views_count' => 1,
                ]
            ],
            ['vehicle_id', 'bucket_start'],
            [
                'views_count' => DB::raw('views_count + 1') 
            ]
        );
        return response()->json($vehicle);

    }

    public function trending(): JsonResponse
    {
        // TODO: return the top 10 most-viewed vehicles in the last 24h,
        //   each with their vehicle data and view count.
        //   The frontend will poll this every 30s.


        //SOLUTION
        $vehicles = Cache::remember('vehicles.trending.24h', 30, function () {
            $since = now()->subDay();
            return Vehicle::query()
                ->select('vehicles.*')
                ->selectRaw('SUM(vehicle_views.views_count) as views_count')
                ->join('vehicle_views', 'vehicles.id', '=', 'vehicle_views.vehicle_id')
                ->where('vehicle_views.bucket_start', '>=', $since)
                ->groupBy('vehicles.id')
                ->orderByDesc('views_count')
                ->orderBy('vehicles.id')
                ->limit(10)
                ->get();
        });
        return response()->json($vehicles);
    }
}
