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
            // return date("Y-m-d H:i:s",time());
            $curConst = DB::table('constdata')->where('code','time')->first();
            return $curConst->data;
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
            $number = ($req->number == null?$random:$req->number);
            return self::executeBet((string)$req->gameId,intval($number),intval($req->amount));
        }
        
        private function executeBet($gameId,$number,$amount){
            // unauthorized            
            if (!Auth::user()->email)
                return "auth_fail";

            if ($number < 111 || $number > 888)
                return "invalid";
            
            $email = Auth::user()->email;
            // game expire or not exist
            $curConst = DB::table('constdata')->where('code','gameId')->first();
            $curConst1 = self::getTime();
            $curGameId = $curConst->data;
            $curTime = self::getTime();
            
            $curGame = DB::table('games')->where('gameId',$curGameId)->get()->first();

            $totalTai = $curGame->totalValue1;
            $totalXiu = $curGame->totalValue2;

            $betPhaseEnd = $curGame->betPhaseEnd;
            // game has passed
            if ($gameId !== $curGameId) 
                return "game_expired";
            // bet phase has passed
            if ($curTime > $betPhaseEnd || $curTime == $betPhaseEnd)
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
            if ($number >= 500) $totalXiu += $amount;
            else $totalTai += $amount;

            DB::table('games')->where('gameId',$curGameId)->update(['totalValue1'=>$totalTai],['totalValue2',$totalXiu]);
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

        private function calculatePoint($betAmount, $__choosenNumber, $__resultNumber){
            $middle = 500;
            // tai: 111 - 499
            // xiu: 500 - 888

            $point = 0;
            $winnable = !(($__resultNumber>=$middle) xor ($__choosenNumber>=$middle));

            if ($winnable) $point = $betAmount;
    
            return $point;
        }

        public function distributePrize(){
            $pool = array();
            // $pool[$user][$index]
            // $index = 0: points
            // $index = 1: coins earned
            // $index = 2: username

            $curConst = DB::table('constdata')->where('code','gameId')->first();
            $curGameId = $curConst->data;
            $curTime = self::getTime();

            $curGame = DB::table('games')->where('gameId',$curGameId)->get()->first();
     
            $resultNumber = ($curGame->dice1)*100 + ($curGame->dice2)*10 + ($curGame->dice3);
            // return $curGame->gameId;
            
            $bets = DB::table('bets')->where([['gameId',$curGame->gameId],['done',0]])->get();
            
            $totalTai = $curGame->totalValue1;
            $totalXiu = $curGame->totalValue2;
            $totalPoint = 0;
            // take coin from losers
            $toDistribute = ($resultNumber>=500)?$totalTai:$totalXiu;

            // create array and calculate points
            foreach($bets as $key => $bet){
                $user = $bet->email;
                $choosenNumber = $bet->number;
                $amount = $bet->amount;
                $point = self::calculatePoint($amount,$choosenNumber,$resultNumber);
                $totalPoint += $point;
                if ($point > 0){
                    if (array_key_exists($user,$pool)){
                        $pool[$user][0] += $point;
                        $pool[$user][1] += $amount; 
      
                    }
                    else{
                        $obj = array();
                        $obj[0] = $point;
                        $obj[1] = $amount;
                        $obj[2] = $user;
                        $pool[$user] = $obj; 
                    }
                }
                else if ($point == 0){
                    if (array_key_exists($user,$pool)){

                    }
                    else{
                        $obj = array();
                        $obj[0] = $point;
                        $obj[1] = 0;
                        $obj[2] = $user;
                        $pool[$user] = $obj;
                    }
                }   
            }
            
            // now distribute
            foreach($pool as $key => $wonUser){
                $ratio = $totalPoint?$wonUser[0]/ $totalPoint:0;
                $earned = $ratio*$toDistribute;
                $pool[$key][1] += $earned;
            }
            foreach($pool as $key => $wonUser){
                $curUser = DB::table('users')->where('email',$key)->get()->first();
                
                $curBalance = $curUser->balance;
                $wonAmount = $wonUser[1];
                // var_dump($wonUser);
                DB::table('users')->where('email',$key)->update(['balance'=>$curBalance+$wonAmount]);
            }
            // return DB::table('bets')->where([['gameId',$curGame->gameId],['done',0]])->get();
            DB::table('bets')->where([['gameId',$curGame->gameId],['done',0]])->update(['done'=>1]);
            return $pool;
        }
    }

    // balance_too_low
    // ok
    // auth_fail
    // unknown
    