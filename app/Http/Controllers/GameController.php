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

        public function getDices(){
            $arr = DB::table('dices')->get();
            $dices = $arr[0]->number;
            return $dices;
        }

        public function bet(Request $req){
            // if user is not authorized
            return self::executeBet($req->gameId,$req->email,$req->amount);
        }
        
        private function executeBet($gameId,$email,$amount){
            // unauthorized            
            if ($email !== Auth::user()->email)
                return "auth_fail";

            // game expire or not exist
            $curConst = DB::table('constdata')->get()->first();
            $curGameId = $curConst->gameId;

            if ($gameId !== $curGameId) 
                return "game_expire";

            $user = DB::table('users')->where('email',$email)->get()->toArray();
            $curAmount = $user[0]->balance;

            if ($curAmount < $amount){
                return "balance_too_low";
            }

            if (DB::table('users')->where('email',$email)->update(['balance'=>($curAmount-$amount)])){
                if (DB::table('bets')->insert([
                    'email' => $email,
                    'gameId' => $gameId,
                    'amount' => $amount,
                ]))
                    return "ok";
                else
                    return self::addBalance($email,$amount);
            } 
            else return "bet_fail";
        }

        private function addBalance($email,$amount){
            if (Auth::user()->email !== $email)
                return "auth_fail";

            $user = DB::table('users')->where('email',$email)->get();
            $curAmount = $user[0]->balance;
            return DB::table('users')->where('email',$email)->update(['balance'=>($curAmount + $amount)]);
        }
    }

    // balance_too_low
    // ok
    // auth_fail
    // unknown
    