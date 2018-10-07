<?php

namespace App\Console;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();

        $schedule->call(function(){
            $dice1 = rand(1,8);
            $dice2 = rand(1,8);
            $dice3 = rand(1,8);

            $str = $dice1.",".$dice2.",".$dice3;
            DB::table('dices')->where('ind'
            ,1)->update(['number' => $str]);
            Log::info("Dices updated");
        })->everyMinute();

        // create new game
        $schedule->call(function(){
            
            $query = "BEGIN 

            INSERT INTO txgame.games VALUES (CURRENT_TIMESTAMP, CURRENT_TIMESTAMP,CURRENT_TIMESTAMP+300,0,FLOOR(RAND()*8+1),FLOOR(RAND()*8+1),FLOOR(RAND()*8+1),CURRENT_TIMESTAMP+200,CURRENT_TIMESTAMP+300);
            
            UPDATE txgame.constdata
            SET data=CURRENT_TIMESTAMP
            WHERE code ='gameId';
            
            END";
            DB::raw($query);
        })->cron("*/3 * * * *");
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
