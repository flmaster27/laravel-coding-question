<?php

namespace App\Http\Controllers;

use App\Sheep;
use App\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class ReportController extends Controller
{
    /**
     * Показываем основную информацию в логе
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show()
    {
        //GLOBAL STATS
        $result["total"] = Sheep::withTrashed()->count();
        $result["live"] = Sheep::count();
        $result["killed"] = Sheep::onlyTrashed()->count();

        $getMaxPen = Sheep::select('*', DB::raw('COUNT(*) as total'))
            ->groupBy('pen_id')
            ->orderByRaw('total DESC')
            ->first();
        $result["max_pen"] = $getMaxPen->pen_id;

        $getMinPen = Sheep::select('*', DB::raw('COUNT(*) as total'))
            ->groupBy('pen_id')
            ->orderByRaw('total ASC')
            ->first();
        $result["min_pen"] = $getMinPen->pen_id;

        //LOG'S LAST 20 ROWS
        $logs = Log::orderBy('id', 'desc')->limit(20)->get();
        $result["logs"] = $logs;

        return view('report', $result);
    }

    /**
     * Выгружаем лог
     *
     * @param $from
     * @param $to
     * @return \Illuminate\Http\JsonResponse
     */
    public function log(Request $request)
    {
        $log = [];

        if ($request->filled('from') && $request->filled('to')) {
            $log = Log::whereBetween('day', array($request->input('from'), $request->input('to')))->get();
        } elseif ($request->filled('from')) {
            $log = Log::where('day', '>=', $request->input('from'))->get();
        } elseif ($request->filled('to')) {
            $log = Log::where('day', '<=', $request->input('to'))->get();
        }

        return response()->json($log, 200);
    }
}
