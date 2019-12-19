<?php

namespace App\Console\Commands;

use App\Mail\SendMailable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use DB;

class AvisoActividad extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'actividad:aviso';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envia un email cuando la fecha o fecha de recordatorio es igual a hoy';

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
     * @return mixed
     */
    public function handle()
    {
        //las actividades
        $configuracion = DB::table('configuracion_general')
        ->first();
        $CorreoEmisor = 'bismoservicios@gmail.com';
        $remitente = 'Notificaciones '.$configuracion->nombre_cliente;
        ///Seleccionamos todas las tarea de actividades
        $actividades_pendientes = \DB::table('actividad as a')
        ->join('users as u','a.agente_id','=','u.id','left',false)
        ->join('prospectos as p','a.prospecto_id','=','p.id_prospecto','left',false)
        ->select('a.id_actividad','a.titulo','a.fecha','a.hora','a.descripcion','a.tipo_actividad','a.fecha_recordatorio','u.name as responsable','u.email as correo_responsable','p.nombre as prospecto')
        ->where('a.estatus','Pendiente')
        ->where('a.fecha_recordatorio','>=',date('Y-m-d'))
        ->get();

        foreach ($actividades_pendientes as $key) { 
            $destinatarioCorreo = $key->correo_responsable;

            $data = array(
                'id_actividad' => $key->id_actividad,
                'titulo' => $key->titulo,
                'fecha' => $key->fecha,
                'hora' => $key->hora,
                'fecha_recordatorio' => $key->fecha_recordatorio,
                'descripcion' => $key->descripcion,
                'tipo_actividad' => $key->tipo_actividad,
                'responsable' => $key->responsable,
                'prospecto' => $key->prospecto,
            );
            Mail::send('emails.aviso_actividad', $data, function ($message) use ($CorreoEmisor, $destinatarioCorreo, $remitente) {
                $message->from($CorreoEmisor, $remitente);
                $message->to($destinatarioCorreo)->subject('Actividades pendientes');
            });
        }
        
        ///Mail::to('jgarcia@nextapp.com.mx')->send(new SendMailable($totalActividadesPendientes));
    }
}
