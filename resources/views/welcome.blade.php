<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body>
<div class="container">
    <div class="jumbotron">
        <h1>Овечки</h1>
        <p class="lead">Дни: <span id="days"></span></p>
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
    </form>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anondayymous"></script>
<script>
    $(function () {
        var day = localStorage.getItem('day') ? localStorage.getItem('day') : 0;
        setDay(day);

        function add() {
            $.ajax({
                url: '/api/add/',
                dataType: 'json',
                success: function (data) {
                    $('#pen-' + data.pen_id).append(
                        '<option id="sheep-' + data.id + '">Овечка ' + data.id + '</option>'
                    );
                }
            });
        }

        function kill() {
            $.ajax({
                url: '/api/kill/',
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
    });
</script>
</body>
</html>
