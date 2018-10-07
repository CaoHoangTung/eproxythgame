@extends('layouts.app')

@section('content')
    
    <div id="machine" style="min-height: 10px; overflow: hidden">
        <div class="container" id="machine">
            <div class="slots">
                <div class="item">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
                <div class="item">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
                <div class="item">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>

            <div class="salute"></div>
            <div class="salute two"></div>
            <div class="salute three"></div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-sm-12" style="text-align:center">
                <div id="countdownExample" style="margin-bottom:30px">
                    <b><div class="values"></div></b>
                    <h4 class="phase"></h4>
                    <div class="msg"></div>
                </div>
            </div>
            <div class='col-lg-4 col-sm-12 bet-section'>
                <h4>Make your bet</h4>
                <button style="margin-bottom: 10px" class="btn btn-success">1</button>
                <button style="margin-bottom: 10px" class="btn btn-success">2</button>
                <button style="margin-bottom: 10px" class="btn btn-success">5</button>
                <button style="margin-bottom: 10px" class="btn btn-success">10</button>
                <button style="margin-bottom: 10px" class="btn btn-success">50</button>
                <button style="margin-bottom: 10px" class="btn btn-success">100</button>
                <button style="margin-bottom: 10px" class="btn btn-success">200</button>
                <button style="margin-bottom: 10px" class="btn btn-success">500</button>
                <button style="margin-bottom: 10px" class="btn btn-success">1000</button>
                <button style="margin-bottom: 10px" class="btn btn-success">2000</button>
                <button style="margin-bottom: 10px" class="btn btn-success">5000</button>
                <button style="margin-bottom: 10px" class="btn btn-success">10000</button>
                <button style="margin-bottom: 10px" class="btn btn-success">20000</button>
                <button style="margin-bottom: 10px" class="btn btn-success">50000</button>
            </div>
            <div class='col-lg-4 col-sm-12'>
                <h1>MY BALANCE</h1>
                <h2 class="mbalance">{{ Auth::user()->balance }}</h2>
            </div>
            <div class='col-lg-4 col-sm-12'>
                <h4>Chat</h4>
            </div>
        </div>
    </div>

    <style>
        .values{
            text-align: center;
        }
        .mbalance{
            color: green;
        }

    </style>

    <script>
        $(document).ready(function(){
            function confirmBet(number,amount){
                var prevBalance = ($('.mbalance:last').text());
                $('.mbalance').text("Processing..");
                $.ajax({
                    url: '/game/bet',
                    method: 'post',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        gameId: "{{$gameId}}",
                        amount: amount,
                        number: number,
                    },
                    success: function(res){
                        console.log(res);
                        if(res === "ok"){
                            console.log(prevBalance);
                            console.log(amount);
                        
                            $('.mbalance').text(prevBalance-amount);
                        }
                        else{
                            $('.mbalance').text(prevBalance);
                            var msg = "";
                            if (res == "game_expired")
                                msg = "This game has ended";
                            else if (res == "bet_phase_only")
                                msg = "Bet Phase has passed. Please wait until next game";
                            else if (res == "balance_too_low")
                                msg = "Insufficient balance";
                            else   
                                msg = "Error";
                            alert(msg);
                        }
                    },
                    error: function(res){
                        console.log(res);
                        console.log("Session expired. Refresh the page and try again");
                        $('.mbalance').text(prevBalance);
                        document.reload();
                    }
                });
            }
            
            $('.bet-section button').on('click',function(){
                var amount = parseInt($(this).text());
                var number = 111;

                confirmBet(number,amount);
            });
        });
    </script>

    <script src="{{asset('js/timer.min.js')}}"></script>
    <script>
        function play(){
            var json = {};
            var countdown = 0;
            var msg = "";
            var phse = "";

            $.ajax({
                url: '/api/phase',
                method: 'get',
                async: false,
                success: function(res){
                    var json = JSON.parse(res);
                    phase = json.phase;
                    countdown = json.countdown;
                    msg = json.msg;
                },
                error: function(res){
                    console.log("Connection error");
                }
            });
            
            var timer = new Timer();
            timer.start({countdown: true, startValues: {seconds: countdown}});
            $('#countdownExample .values').html(timer.getTimeValues().toString());
            $('#countdownExample .phase').html(phase);
            $('#countdownExample .msg').html(msg);
            timer.addEventListener('secondsUpdated', function (e) {
                var timerVal = timer.getTimeValues().toString();
                $('#countdownExample .values').html(timerVal);
                if(timerVal === "00:00:00")
                    location.reload();
            });
            // timer.addEventListener('targetAchieved', function (e) {
                // play();
            // });
        }
        play();
        setInterval(() => {
            play();
        },20000);

    </script>

    <script>

        var iframe = document.createElement('iframe');
        iframe.src = '/machine'; 
        iframe.scrolling="no";
        iframe.width="100%";
        iframe.height="200px";
        $('#machine').append(iframe); // add it to wherever you need it in the document
    </script>

    <style>
        body{
            font-family: Arial;
        }
    </style>
@endsection