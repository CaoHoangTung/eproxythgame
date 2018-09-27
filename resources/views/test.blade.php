
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title></title>
    <link rel="stylesheet" href="{{asset('css/dice.css')}}">
</head>
<body>
    <!-- <audio id="dice3d-sound" src="dist/nc93322.mp3"></audio> -->
    <input id="number" type="number" value=2>
    <button id="button-roll">roll</button>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

    <script src="{{asset('js/dice.js')}}"></script>
    <script>
var button = document.getElementById('button-roll');
var input = document.getElementById('button-roll');
button.addEventListener('click', function(e) {
    e.preventDefault();
    var n = +document.getElementById('number').value;
    var log = [];
    for (var i = 0; i < n; ++i) {
        var r = Math.floor(Math.random() * 6) + 1;
        log.push(r);
        dice3d(6, 1); // Animate 6 faces dice
    }
    console.log(log);
});
    </script>
</body>
</html>
