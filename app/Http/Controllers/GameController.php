<?php

    namespace App\Http\Controllers;

    use DB;
    use Illuminate\Console\Scheduling\Schedule;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;

    class GameController extends Controller{

        public function __construct(){
            $this->middleware('auth');
        }

        public function index(){
            $arr = array();

            $gameid = DB::table('games')->orderBy('gameId','desc')->first()->gameId;
            $arr['gameId'] = $gameid;
            return view('slotMachine',$arr);
        }

        private function getTime(){
            $row = DB::table('constdata')->where('code','time')->get()->first();
            return $row->data;
        }

        private function getDices(){
            $arr = DB::table('games')->orderBy('gameId','desc')->first();
            $dices = array();
            $dices[0] = $arr->dice1;
            $dices[1] = $arr->dice2;
            $dices[2] = $arr->dice3;
            return $dices;
        }

        public function bet(Request $req){
            $random = rand(100,888);
            $number = ($req->number == null?$random:$req->number);
            return self::executeBet((string)$req->gameId,intval($number),intval($req->amount));
        }
        
        private function executeBet($gameId,$number,$amount){
            // unauthorized            
            if (!Auth::user()->email)
                return "auth_fail";

            $email = Auth::user()->email;
            // game expire or not exist
            $curConst = DB::table('constdata')->where('code','gameId')->first();
            $curConst1 = DB::table('constdata')->where('code','time')->first();
            $curGameId = $curConst->data;
            $curTime = $curConst1->data;
            
            $curGame = DB::table('games')->where('gameId',$curGameId)->get()->first();
            $betPhaseEnd = $curGame->betPhaseEnd;
            // game has passed
            if ($gameId !== $curGameId) 
                return "game_expired";
            // bet phase has passed
            if ($curTime > $betPhaseEnd)
                return "bet_phase_only";
            

            $user = Auth::user();
            $curAmount = $user->balance;

            if ($curAmount < $amount){
                return "balance_too_low";
            }
            
            DB::table('users')->where('email',$email)->update(['balance'=>($curAmount-$amount)]);
            DB::table('bets')->insert([
                        'email' => $email,
                        'gameId' => $gameId,
                        'amount' => $amount,
                        'number' => $number,
                    ]);
            return "ok";
        }

        private function addBalance($email,$amount){
            if (Auth::user()->email !== $email)
                return "auth_fail";

            $user = DB::table('users')->where('email',$email)->get();
            $curAmount = $user[0]->balance;
            return DB::table('users')->where('email',$email)->update(['balance'=>($curAmount + $amount)]);
        }

        public function viewPhase(){
            $game = DB::table('games')->orderBy('gameId','desc')->first();
            $cur = strtotime(self::getTime());
            $begin = strtotime($game->startTime);
            $end = strtotime($game->endTime);
            $betPhaseEnd = strtotime($game->betPhaseEnd);
            $spinPhaseEnd = strtotime($game->spinPhaseEnd);
            
            $dices = array();
            // if game has ended
            if ($cur > $spinPhaseEnd || $cur > $end){
                $phasename = "This game has ended";
                $msg = "Generating new game. This should only take up to 1 minute";
                $countdown = max(0,$end-$cur);
            }   
            // game is in spin phase
            else if ($cur > $betPhaseEnd){
                $phasename = "Spin Phase";
                $msg = "Result is being shown to players. Don't worry if you exit the page, you will still get your reward)";
                $countdown = max(0,$spinPhaseEnd-$cur);
                $dices = self::getDices();
            }   
            // game is in bet phase
            else if ($cur > $begin){
                $phasename = "Bet Phase";
                $msg = "Select your lucky number and the amount of coin to bet";
                $countdown = max(0,$betPhaseEnd-$cur);
            }  
            // game hasn't started
            else{
                $phasename = "Preparation Phase";
                $msg = "The game will start shortly";
                $countdown = max(0,$begin-$cur);
            }
            $json = [];
            $json['phase'] = $phasename;
            $json['msg'] = $msg;
            $json['countdown'] = $countdown;
            if (sizeof($dices) === 3) 
                $json['dices'] = $dices;
            return json_encode($json);
        }
    }

    // balance_too_low
    // ok
    // auth_fail
    // unknown
    