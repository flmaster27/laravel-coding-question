<?php

namespace App\Http\Controllers;

use App\Sheep;
use App\Log;
use Illuminate\Http\Request;

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
                }
            }
        }
    }

    /**
     * Рендеринг
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function welcome()
    {
        $currentSheeps = Sheep::where('killed', false)->orderBy('pen_id', 'asc')->get();
        foreach ($currentSheeps as $sheep) {
            $farm[$sheep->pen_id][] = $sheep;
        }

        return view('welcome', ["farm" => $farm]);
    }

    /**
     * Добавляем овцу в случайный загон при условии, что их в нем больше 1
     */
    public function add()
    {

        // TODO: Здесь бы лучше применить запрос с HAVING и COUNT, со случайной сортировкой, но пока не разобрался, как это сделать в Laravel
        $pens = self::PENS_ID;
        shuffle($pens);

        foreach ($pens as $pen) {
            $sheepCountByPen = Sheep::where([
                'killed' => false,
                'pen_id' => $pen
            ])->count();


            if ($sheepCountByPen > 1) {
                $sheep = new Sheep;
                $sheep->pen_id = $pen;
                $sheep->save();

                $this->writeLog("Добавили в загон", $sheep);

                return response()->json($sheep, 200);
            }
        }

    }

    /**
     * Отправляем овцу на забой
     */
    public function kill()
    {
        $pens = self::PENS_ID;
        shuffle($pens);

        foreach ($pens as $pen) {

            $sheepCountByPen = Sheep::where([
                'killed' => false,
                'pen_id' => $pen
            ])->count();

            if ($sheepCountByPen > 1) {
                $killed = Sheep::where([
                    'killed' => false,
                    'pen_id' => $pen
                ])->first();
                $killed->killed = true;
                $killed->save();

                $this->writeLog("Убрали из загона", $killed);

                $arReturn["killed"] = $killed;

                //Если количество овец было 2 (стало 1 после забоя), то мы должны докинуть ей в загон овцу из самого заполненного загона
                if ($sheepCountByPen == 2) {
                    $moved = $this->move($pen);

                    if ($moved) {
                        $arReturn["moved"] = $moved;
                    }
                }

                return response()->json($arReturn, 200);
            }
        }
    }

    /**
     * Загоняем овцу в загон $to из самого заполненного загона
     * @param $to
     */
    private function move($to)
    {

        foreach (self::PENS_ID as $pen) {
            $sheepCountByPen = Sheep::where([
                'killed' => false,
                'pen_id' => $pen
            ])->count();
            $pensCount[$pen] = $sheepCountByPen;
        }

        $biggestPen = array_search(max($pensCount), $pensCount);

        if ($biggestPen > 1) {
            $moved = Sheep::where([
                'killed' => false,
                'pen_id' => $biggestPen
            ])->first();
            $moved->pen_id = $to;
            $moved->save();

            $this->writeLog("Переместили", $moved);

            return $moved;
        }
    }


    /**
     * @param $text
     * @param $obj
     */
    private function writeLog($text, $obj)
    {
        $log = new Log;
        $log->text = $text . " ID:" . $obj->id . "; " . $obj->pen_id . ";";
        $log->save();
    }

}
