@extends('layouts.layout')

@section('content')
    <div class="jumbotron">
        <h1>Отчет</h1>
        <ul>
            <li>общее количество овечек: {{ $total }}</li>
            <li>количество убитых овечек: {{ $killed }}</li>
            <li>количество живых овечек: {{ $live }}</li>
            <li>номер самого населённого загона: {{ $max_pen }}</li>
            <li>номер самого менее населённого загона: {{ $min_pen }}</li>
        </ul>
    </div>

    <form class="fetch-log">
        <h3>Укажите диапазон дней для формирования отчета</h3>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>С</label>
                    <input name="from" type="number" class="form-control">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>По</label>
                    <input name="to" type="number" class="form-control">
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-default">Сформировать</button>
    </form>

    <hr>

    <table class="table table-striped log-result">
        <tr class="table-header">
            <th>ID</th>
            <th>Действие</th>
            <th>Овечка</th>
            <th>Загон</th>
            <th>День</th>
        </tr>
        @foreach ($logs as $row)
            <tr>
                <td>{{ $row->id }}</td>
                <td>{{ $row->action }}</td>
                <td>{{ $row->sheep_id }}</td>
                <td>{{ $row->pen_id }}</td>
                <td>{{ $row->day }}</td>
            </tr>
        @endforeach
    </table>
@endsection

@section('scripts')
    <script>
        $(function () {

            $(".fetch-log").submit(function (e) {
                e.preventDefault();

                var from = parseInt($(this).find("input[name='from']").val()) || null;
                var to = parseInt($(this).find("input[name='to']").val()) || null;

                $.ajax({
                    url: '/api/log',
                    data: {'from': from, 'to': to},
                    type: 'POST',
                    dataType: 'json',
                    success: function (data) {
                        $(".log-result tr:not(.table-header)").remove();

                        $.each(data, function (index, value) {
                            $(".log-result").append(
                                "<tr>" +
                                "<td>" + value.id + "</td>" +
                                "<td>" + value.action + "</td>" +
                                "<td>" + value.sheep_id + "</td>" +
                                "<td>" + value.pen_id + "</td>" +
                                "<td>" + value.day + "</td>" +
                                "</tr>"
                            );
                        });
                    }
                });

            });
        });
    </script>
@endsection