<?php

namespace App\Console\Commands;

use App\Http\Controllers\AlertasNotificacionesController;
use Illuminate\Console\Command;

class Notificaciones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:fiveM';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Carga de notificaciones cada 5 minutos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
       $notificacionesController= new AlertasNotificacionesController();
       $notificacionesController->notificar();

    }
}
