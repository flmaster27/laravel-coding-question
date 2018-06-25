<?php

namespace App\Http\Controllers;

use App\Sheep;
use App\Log;
use Illuminate\Support\Facades\DB;

class SheepController extends Controller
{
    const PENS_ID = array(1, 2, 3, 4);

    /**
     * SheepController constructor.
     * Если нет овец, раскидываем по загонам. Загоняем не меньше
     * двух, так как при одной овце в загоне нам необходимо к
     * ней сразу перекинуть другую из самого заполненного загона.
     */
    public function __construct()
    {
        $sheepCount = Sheep::all()->count();

        if (!$sheepCount) {
            $max = 10;
            $minPenCount = 2;
            $insertMatrix = array_fill_keys(self::PENS_ID, $minPenCount);
            $remaining = $max - count($insertMatrix) * $minPenCount;

            while ($remaining > 0) {
                $insertMatrix[self::PENS_ID[rand(0, count(self::PENS_ID) - 1)]]++;
                $remaining--;
            }

            foreach ($insertMatrix as $pen => $count) {
                for ($i = 0; $i < $count; $i++) {
                    $sheep = new Sheep;
                    $sheep->pen_id = $pen;
                    $sheep->save();

                    if ($sheep) {
                        $this->writeLog("CREATE", $sheep, 0);
                    }
                }
            }
        }
    }

    /**
     * Рендеринг
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function welcome()
    {
        $currentSheeps = Sheep::orderBy('pen_id', 'asc')->get();
        foreach ($currentSheeps as $sheep) {
            $farm[$sheep->pen_id][] = $sheep;
        }

        return view('welcome', ["farm" => $farm]);
    }

    /**
     * Добавляем овцу в случайный загон при условии, что их в нем больше 1
     */
    public function add($day)
    {
        $randomPen = Sheep::groupBy('pen_id')
            ->havingRaw('COUNT(*) > 1')
            ->inRandomOrder()
            ->first();

        if ($randomPen) {
            $sheep = new Sheep;
            $sheep->pen_id = $randomPen->pen_id;
            $sheep->save();

            if ($sheep) {
                $arReturn["created"] = $sheep;
                $this->writeLog("CREATE", $sheep, $day);

                $moved = $this->move($day);
                if ($moved) {
                    $arReturn["moved"] = $moved;
                }

                return response()->json($arReturn, 200);
            }
        }

    }

    /**
     * Отправляем овцу на забой
     */
    public function kill($day)
    {
        $randomPen = Sheep::select('*', DB::raw('COUNT(*) as total'))
            ->groupBy('pen_id')
            ->havingRaw('COUNT(*) > 1')
            ->inRandomOrder()
            ->first();

        if ($randomPen) {
            $randomPen->delete();

            if ($randomPen->trashed()) {
                $this->writeLog("DELETE", $randomPen, $day);
                $arReturn["killed"] = $randomPen;

                $moved = $this->move($day);
                if ($moved) {
                    $arReturn["moved"] = $moved;
                }

                return response()->json($arReturn, 200);
            }

        }
    }

    /**
     * Загоняем овцу в загон $to из самого заполненного загона
     *
     * @param $to
     */
    private function move($day)
    {
        $sheepFromTopPen = Sheep::select('*', DB::raw('COUNT(*) as total'))
            ->groupBy('pen_id')
            ->havingRaw('COUNT(*) > 1')
            ->orderByRaw('total DESC')
            ->first();

        $sheepToPen = Sheep::select('*', DB::raw('COUNT(*) as total'))
            ->groupBy('pen_id')
            ->havingRaw('COUNT(*) = 1')
            ->inRandomOrder()
            ->first();

        if ($sheepFromTopPen && $sheepToPen) {
            $sheepFromTopPen->pen_id = $sheepToPen->pen_id;
            $sheepFromTopPen->save();

            if ($sheepFromTopPen) {
                $this->writeLog("MOVE", $sheepFromTopPen, $day);

                return $sheepFromTopPen;
            }
        }
    }


    /**
     * Пишем лог
     *
     * @param $text
     * @param $obj
     */
    private function writeLog($text, $obj, $day)
    {
        $log = new Log;
        $log->sheep_id = $obj->id;
        $log->pen_id = $obj->pen_id;
        $log->action = $text;
        $log->day = $day;
        $log->save();
    }

}
