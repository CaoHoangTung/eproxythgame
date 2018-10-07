<script src='//static.codepen.io/assets/editor/live/console_runner-ce3034e6bde3912cc25f83cccb7caa2b0f976196f2f2d52303a462c826d54a73.js'></script>
<script src='//static.codepen.io/assets/editor/live/css_live_reload_init-e9c0cc5bb634d3d14b840de051920ac153d7d3d36fb050abad285779d7e5e8bd.js'></script>

<link rel="stylesheet" href="{{asset('css/slotMachine.css')}}">

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
<script src='//static.codepen.io/assets/common/stopExecutionOnTimeout-41c52890748cd7143004e05d3c5f786c66b19939c4500ce446314d1748483e13.js'></script>
<script src='//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script >

    $(document).ready(function () {
        var spinned = false;
        var isAnimate = false,
        randomNum,
        winNums = [],
        slotsItem = $(".slots .item"),
        salute = $(".salute");

        //spin
        function spinAnimation(data){
                isAnimate = true;
                salute.removeClass("active");
                $(this).addClass("active");
                var dices = data['dices'];
                for (var i = 0; i < 3; i++) {
                    if (window.CP.shouldStopExecution(0)) break;
                    randomNum = dices[i];
        
                    winNums[i] = randomNum;
                    for (var j = 0; j < 9; j++) {
                        if (window.CP.shouldStopExecution(1)) break;
                        slotsItem.eq(i).removeClass('state' + j);
                    }window.CP.exitedLoop(1);
                    slotsItem.eq(i).addClass("animate").addClass('state' + randomNum);
                }window.CP.exitedLoop(0);
                setTimeout(function () {
                    slotsItem.removeClass("animate");
                    isAnimate = false;
                    $(".spin_btn").removeClass("active");
                    if (winNums[0] == winNums[1] && winNums[0] == winNums[2]) {
                    salute.addClass("active");
                    }
                }, 8000);
            spinned = true;
        }

        function spin(data){

            if (!isAnimate) {
                spinAnimation(data);                
            }
        }
        
        setInterval(() => {
            $.ajax({
                url: 'api/phase',
                method: 'get',
                success: function(res){
                    var json = JSON.parse(res);
                    if (json.phase ==="Spin Phase"){
                        let arr = [];
                        arr['dices'] = json.dices;
                        if (arr['dices'] !== null && !spinned)
                            spin(arr);
                    }
                    else if (json.phase === "Bet Phase")
                        spinned = false;
                },
                error: function(res){
                    console.log("CONNECTION ERROR");
                    console.log(res);
                }
            });
        }, 6000);
        
    });

</script>

<style>
    body{
        font-family: Arial;
    }
</style>