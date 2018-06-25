@extends('layouts.layout')

@section('content')
    <div class="jumbotron">
        <h1>Овечки</h1>
        <p class="lead">Дни: <span id="days"></span>. 1 день = 10 секунд</p>
    </div>
    <form>
        <div class="row">
            @foreach ($farm as $pen => $sheeps)
                <div class="col-md-6">
                    <h2>Загон {{ $pen  }}</h2>
                    <select id="pen-{{ $pen  }}" multiple class="form-control">
                        @foreach ($sheeps as $sheep)
                            <option id="sheep-{{ $sheep->id }}">Овечка {{ $sheep->id }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
        <div class="row">
            <div class="col-md-12">
                <hr>
                <div class="alert alert-info" role="alert">Каждый день создается овечка в одном из загонов, имеющих
                    более одной овечки. Каждый 10-й день одну овечку из случайного загона забирают на забой. Можно
                    забрать дополнительную овечку в забой нажав кнопку ниже. При забое овечки проверяем количество
                    овечек в загоне, если осталась только одна, подселяем ей еще одну из самого заселенного загона. Не
                    допускаем забой единственной овечки в загоне.
                </div>
                <button type="button" class="btn btn-primary btn-lg btn-block kill-sheep">Забить овечку</button>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script>
        $(function () {
            setDay(localStorage.getItem('day') ? localStorage.getItem('day') : 0);

            function add() {
                $.ajax({
                    url: '/api/add/' + localStorage.getItem('day'),
                    dataType: 'json',
                    success: function (data) {
                        $('#pen-' + data.created.pen_id).append(
                            '<option id="sheep-' + data.created.id + '">Овечка ' + data.created.id + '</option>'
                        );

                        if (data.moved) {
                            $('#sheep-' + data.moved.id).detach().appendTo('#pen-' + data.moved.pen_id);
                        }
                    }
                });
            }

            function kill() {
                $.ajax({
                    url: '/api/kill/' + localStorage.getItem('day'),
                    dataType: 'json',
                    success: function (data) {
                        $('#sheep-' + data.killed.id).remove();

                        if (data.moved) {
                            $('#sheep-' + data.moved.id).detach().appendTo('#pen-' + data.moved.pen_id);
                        }
                    }
                });
            }

            var timer = setInterval(function () {
                var day = localStorage.getItem('day') ? localStorage.getItem('day') : 0;
                day = parseInt(day) + 1;
                setDay(day);
                add();

                if (day % 10 == 0 && day > 0) {
                    kill();
                }

            }, 10000);

            function setDay(day) {
                localStorage.setItem('day', day);
                $('#days').html(day);
            }

            $(".kill-sheep").click(function () {
                kill();
            });
        });
    </script>
@endsection