<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests; /// por lo tanto aqui debe ir el nombre que seria App y no sisVentas
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use App\Empleado;
use App\Empresa;
use App\Plaza;
use App\Proveedor;
use App\OrdenCompra;
use App\Inventario;
use App\Sucursal;
use DB;
use Orchestra\Parser\Xml\Facade as XmlParser;

class PolizaWSController extends Controller
{
    public function consultarRECNOPolizas(){
      
      $url_poliza = 'http://201.116.148.68:8095/ValorMaximoPF.asmx/getMax?n_tbl=ZC1000';
      $ch = curl_init($url_poliza); 
      curl_setopt($ch, CURLOPT_HEADER, 0); 
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);

      $response = curl_exec($ch);
      $err = curl_error($ch);
      curl_close($ch);
      error_log('RECNO API RESPOONSE: '.json_encode($response));
      error_log('RECNO API ERROR: '.json_encode($err));
      //$fileXML = str_replace('\\', '/',  'temp'.'\\' ."alSuperGetMax.xml");
      /* Resultado 
      *<?xml version="1.0" encoding="utf-8"?>
      *<int xmlns="http://tempuri.org/">8258172</int>
      */
      $contenido = str_replace('<?xml version="1.0" encoding="utf-8"?>',"",$response);
      $contenido = trim($contenido);
      $xml =  simplexml_load_string($contenido);
      $RECCNOPLUS = $xml + 1;
      return $RECCNOPLUS;
    }
    public function consultarRECNOFinanciero($numeroEmpresa = null)
    {
        if ($numeroEmpresa != null) {
            $sourceXML = file_get_contents('http://ws.alsuper.com:8095/ValorMaximoPF.asmx/getMax?n_tbl='.$numeroEmpresa, true );
            $fileXML = str_replace('\\', '/',  'temp'.'\\' ."alSuperGetMax.xml");
            /* Resultado 
            *<?xml version="1.0" encoding="utf-8"?>
            *<int xmlns="http://tempuri.org/">8258172</int>
            */
            $contenido = str_replace('<?xml version="1.0" encoding="utf-8"?>',"",$sourceXML);
            $contenido = trim($contenido);
            $xml =  simplexml_load_string($contenido);
            $RECCNOPLUS = $xml + 1;
            return $RECCNOPLUS;
        }else{///No mando el numero de empresa
            return false;
        }
    }
    public function crearRegistroPoliza($data, $tipo_poliza)
    {
        $url_poliza= 'http://ws.alsuper.com:8095/ValorMaximoPF.asmx/insertaPoliza';
        //error_log('DATA: '.json_encode($data));
        $fields_string ='';
        foreach($data as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        rtrim($fields_string, '&');
        //error_log('FIELDSTRING: '.$fields_string);
        $cURLConnection = curl_init();
     
        // definimos la URL a la que hacemos la petición
        curl_setopt($cURLConnection, CURLOPT_URL,"http://ws.alsuper.com:8095/ValorMaximoPF.asmx/insertaPoliza");
        // indicamos el tipo de petición: POST
        curl_setopt($cURLConnection, CURLOPT_POST, TRUE);
        curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($cURLConnection, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($cURLConnection, CURLOPT_TIMEOUT, 120);
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

        $apiResponse = curl_exec($cURLConnection);
        $err = curl_error($cURLConnection);
        curl_close($cURLConnection);

        $this->crearEvidenciaPoliza($data, $tipo_poliza);
        // $apiResponse - available data from the API request
          file_put_contents('error_CURL.txt', json_encode($apiResponse).json_encode($err));
          error_log('RECNO API RESPOONSE: '.json_encode($apiResponse));
          error_log('RECNO API ERROR: '.json_encode($err));
        if ($apiResponse === FALSE) 
        { 
          return false;
        } 
        else{
            return true;
        }
    }
    public function crearRegistroFinanciero($data, $tipo_poliza)
    {
        $url_poliza= 'http://ws.alsuper.com:8095/ValorMaximoPF.asmx/insertaFinanciero';
        $header = "Content-type: application/x-www-form-urlencoded\r\n";
        
        $options = array( 'http' => array( 'header' => "Content-type: application/x-www-form-urlencoded\r\n", 'method' => 'POST', 'content' => http_build_query($data) ) );
        $context = stream_context_create($options);
        file_put_contents('data_error.txt', json_encode($data));
        $result = file_get_contents($url_poliza, false, $context);
        $this->crearEvidenciaFinanciero($data, $tipo_poliza);
        if ($result === FALSE) 
        { 
          return false;
        } 
        else{
          return true;
        }
    }
    public function crearEvidenciaPoliza($data, $tipo_poliza){
        ///////////////////////CONEXION A LA BASE DE DATOS//////////////////////////////
        
        ///Hacemos un insert a ZC1000 local con los mismo datos que mandamos a alsuper
        $p2_DATA = $data['p2_DATA'];
        $p5_DC = $data['p5_DC'];
        $p6_DOC = $data['p6_DOC'];
        $p7_LINHA = $data['p7_LINHA'];
        $p12_HIST = $data['p12_HIST'];
        $p13_VALOR = $data['p13_VALOR'];
        $p14_PKO = $data['p14_PKO'];
        $p17_LOTE = $data['p17_LOTE'];
        $p18_SBLOTE = $data['p18_SBLOTE'];
        $p19_CTCD = $data['p19_CTCD'];
        $p20_CTAD = $data['p20_CTAD'];
        $p21_CCCD = $data['p21_CCCD'];
        $p22_CCAD = $data['p22_CCAD'];
        $p23_ITCD = $data['p23_ITCD'];
        $p24_ITAD = $data['p24_ITAD'];
        $p25_CLCD = $data['p25_CLCD'];
        $p26_CLAD = $data['p26_CLAD'];
        $p27_EMPORI = $data['p27_EMPORI'];
        $p31_RECNO = $data['p31_RECNO'];

        DB::table('zc1000_poliza')->insert(
            ['tipo_poliza' => $tipo_poliza, 'p2_DATA' => $p2_DATA, 'p5_DC' => $p5_DC, 'p6_DOC' => $p6_DOC, 'p7_LINHA' => $p7_LINHA, 'p12_HIST' => $p12_HIST, 'p13_VALOR' => $p13_VALOR, 'p14_PKO' => $p14_PKO, 'p17_LOTE' => $p17_LOTE, 'p18_SBLOTE' => $p18_SBLOTE, 'p19_CTCD' => $p19_CTCD, 'p20_CTAD' => $p20_CTAD, 'p21_CCCD' => $p21_CCCD, 'p22_CCAD' => $p22_CCAD, 'p23_ITCD' => $p23_ITCD, 'p24_ITAD' => $p24_ITAD, 'p25_CLCD' => $p25_CLCD, 'p26_CLAD' => $p26_CLAD, 'p27_EMPORI' => $p27_EMPORI, 'p31_RECNO' => $p31_RECNO ]
        );
        
        ////////////////////////////////////////////////////////////////////////////////
    }

    public function crearEvidenciaFinanciero($data, $tipo_poliza){

        $t_nmb = $data['t_nmb'];
        $p2_DATA = $data['p2_DATA'];
        $p5_VALOR = $data['p5_VALOR'];
        $p7_BANCO = $data['p7_BANCO'];
        $p8_AGENCI = $data['p8_AGENCI'];
        $p9_CONTA = $data['p9_CONTA'];
        $p11_DOCUME = $data['p11_DOCUME'];
        $p12_VENCTO = $data['p12_VENCTO'];
        $p13_RECPAG = $data['p13_RECPAG'];
        $p14_BENEF = $data['p14_BENEF'];
        $p15_HISTOR = $data['p15_HISTOR'];
        $p22_NUMERO = $data['p22_NUMERO'];
        $p24_CLIFOR = $data['p24_CLIFOR'];
        $p26_DTDIGI = $data['p26_DTDIGI'];
        $p34_DTDISP = $data['p34_DTDISP'];
        $p64_CLIENT = $data['p64_CLIENT'];
        $p97_RECNO = $data['p97_RECNO'];
        $p99_SERIE = $data['p99_SERIE'];
        $p100_RFC = $data['p100_RFC'];

        DB::table('ze5xxx_financiero')->insert(
            ['tipo_poliza' => $tipo_poliza, 't_nmb' => $t_nmb, 'p2_DATA' => $p2_DATA, 'p5_VALOR' => $p5_VALOR, 'p7_BANCO' => $p7_BANCO, 'p8_AGENCI' => $p8_AGENCI, 'p9_CONTA' => $p9_CONTA, 'p11_DOCUME' => $p11_DOCUME, 'p12_VENCTO' => $p12_VENCTO, 'p13_RECPAG' => $p13_RECPAG, 'p14_BENEF' => $p14_BENEF, 'p15_HISTOR' => $p15_HISTOR, 'p22_NUMERO' => $p22_NUMERO, 'p24_CLIFOR' => $p24_CLIFOR, 'p26_DTDIGI' => $p26_DTDIGI, 'p34_DTDISP' => $p34_DTDISP, 'p64_CLIENT' => $p64_CLIENT, 'p97_RECNO' => $p97_RECNO, 'p99_SERIE' => $p99_SERIE, 'p100_RFC' => $p100_RFC ]
        );
    }

    public function ws_poliza(request $request)
    {
      //set_time_limit(0);        

      $reccnoanterior = null;
      $polizasinsertadas = null;
      $reccnoultimo = null;
      $Dep_garantía_cobrar_cliente = '210501035';
      $Dep_garantía_clientes = '110401005';
      $Dep_garantía_nombre = '210501002';
      $cuenta_deudores_diversos = '110701001';
      $otros_pasivos_cuenta = '210501036'; ///'210501016';

      $tipo_poliza =  null;
      $empresa_id = null;
      $fecha_inicio = null; 
      $fecha_fin = null;
      $empresas = DB::table('SI_FORMS_BAPP02F92DB8_FRM_92604F36')
      ->select('id_FRM_92604F36 as id_empresa', 'FFRMS_2BB860B1 as nombre_comercial', 'FFRMS_40F34F0E as razon_social')
      ->get();

      $tipos_polizas = array( array('4763', 'Factura'), array('4808', 'Nota de Credito'), array('4809', 'Bancos'), array('4831', 'Pagos'), array('4975', 'Excedentes aplicados'), array('4976', 'Excedentes sin aplicar'), array('4977', 'Depositos en garantia'), array('5020', 'Bancos Excedentes'), array('5051', 'Juridicos'), array('5052', 'Pagos Juridicos') );

      if ($request->get('empresa_id') == null) {
        $menuPoliza= DB::table('SI_FORMS_BAPP02F92DB8_FRM_B6B5F9F7')
        ->select('FFRMS_9760C657 as tipo_poliza','FFRMS_E80A068F as empresa_id','FFRMS_E815C248 as fecha_inicio','FFRMS_714B219A as fecha_fin','FFRMS_4CDA52E8 as consecutivo','FFRMS_1039DA11 as fecha_poliza_generador')
        ->where('FFRMS_C10D87B8','Falta enviar')
        ->first();
        if ($menuPoliza) {
          $tipo_poliza = $menuPoliza->tipo_poliza;
          $empresa_id = $menuPoliza->empresa_id;
          $fecha_inicio = $menuPoliza->fecha_inicio;
          $fecha_fin = $menuPoliza->fecha_fin;
          $fecha_poliza_generador = $menuPoliza->fecha_poliza_generador;
          $doc_num = $menuPoliza->consecutivo;
        }

      }else{
        $tipo_poliza = $request->get('tipo_poliza');
        $empresa_id = $request->get('empresa_id');
        $fecha_inicio = $request->get('fecha_inicio');
        $fecha_fin = $request->get('fecha_fin');
        $fecha_poliza_generador = $request->get('fecha_poliza_generador');
        $menuPoliza= DB::table('SI_FORMS_BAPP02F92DB8_FRM_B6B5F9F7')
        ->select('FFRMS_4CDA52E8 as consecutivo')
        ->first();
        $doc_num = $menuPoliza->consecutivo;
      }

      $evidencia = DB::table('ZC1000_poliza')->whereBetween('p31_RECNO',[$reccnoanterior, $reccnoultimo])->where('tipo_poliza', 'Nota de Credito')->paginate(10);
      $evidencia_financiero = DB::table('ze5xxx_financiero')->whereBetween('p97_RECNO',[$reccnoanterior, $reccnoultimo])->where('tipo_poliza', 'Bancos excedente')->paginate(10);
    
      if ( $tipo_poliza != null and $empresa_id != null and $fecha_inicio != null and $fecha_fin != null and $fecha_inicio != '' and $fecha_fin != '') {
        $reccnoanterior = 0;
        $polizasinsertadas = 0;
        $reccnoultimo = 0;
        if ($tipo_poliza == 4763) {
          $reccnoanterior = $this->consultarRECNOPolizas() - 1;
          $polizasinsertadas = $this->enviarFactura( $fecha_inicio, $fecha_fin, $empresa_id, $doc_num, $fecha_poliza_generador );
          $reccnoultimo = $this->consultarRECNOPolizas() - 1;
          $nuevoConsecutivo = $doc_num +1;
          $consecutivo_poliza = DB::table('SI_FORMS_BAPP02F92DB8_FRM_B6B5F9F7')->update(['FFRMS_4CDA52E8' => $nuevoConsecutivo ]);
          $evidencia = DB::table('ZC1000_poliza')->whereBetween('p31_RECNO',[$reccnoanterior, $reccnoultimo])->where('tipo_poliza', 'Factura')->paginate(10);
        }
        elseif ($tipo_poliza == 4808) {
          $reccnoanterior = $this->consultarRECNOPolizas() - 1;
          $polizasinsertadas = $this->enviarNotaCredito( $fecha_inicio, $fecha_fin, $empresa_id, $doc_num, $fecha_poliza_generador );
          $reccnoultimo = $this->consultarRECNOPolizas() - 1;
          $nuevoConsecutivo = $doc_num +1;
          $consecutivo_poliza = DB::table('SI_FORMS_BAPP02F92DB8_FRM_B6B5F9F7')->update(['FFRMS_4CDA52E8' => $nuevoConsecutivo ]);
          $evidencia = DB::table('ZC1000_poliza')->whereBetween('p31_RECNO',[$reccnoanterior, $reccnoultimo])->where('tipo_poliza', 'Nota de Credito')->paginate(10);
        }
        elseif ($tipo_poliza == 4831) {
          $reccnoanterior = $this->consultarRECNOPolizas() - 1;
          $polizasinsertadas = $this->enviarPagos( $fecha_inicio, $fecha_fin, $empresa_id, $Dep_garantía_cobrar_cliente, $Dep_garantía_clientes, $Dep_garantía_nombre, $otros_pasivos_cuenta, $doc_num, $fecha_poliza_generador );
          $reccnoultimo = $this->consultarRECNOPolizas() - 1;
          $nuevoConsecutivo = $doc_num +1;
          $consecutivo_poliza = DB::table('SI_FORMS_BAPP02F92DB8_FRM_B6B5F9F7')->update(['FFRMS_4CDA52E8' => $nuevoConsecutivo ]);
          $evidencia = DB::table('ZC1000_poliza')->whereBetween('p31_RECNO',[$reccnoanterior, $reccnoultimo])->where('tipo_poliza', 'Pagos')->paginate(10);
        }
        elseif ($tipo_poliza == 4975) {
          $reccnoanterior = $this->consultarRECNOPolizas() - 1;
          $polizasinsertadas = $this->enviarExcedentesAplicados( $fecha_inicio, $fecha_fin, $empresa_id, $Dep_garantía_cobrar_cliente, $Dep_garantía_clientes, $Dep_garantía_nombre,  $otros_pasivos_cuenta, $doc_num, $fecha_poliza_generador );
          $reccnoultimo = $this->consultarRECNOPolizas() - 1;
          $nuevoConsecutivo = $doc_num +1;
          $consecutivo_poliza = DB::table('SI_FORMS_BAPP02F92DB8_FRM_B6B5F9F7')->update(['FFRMS_4CDA52E8' => $nuevoConsecutivo ]);
          $evidencia = DB::table('ZC1000_poliza')->whereBetween('p31_RECNO',[$reccnoanterior, $reccnoultimo])->where('tipo_poliza', 'Excedentes aplicados')->paginate(10);
        }
        elseif ($tipo_poliza == 4976) {
          $reccnoanterior = $this->consultarRECNOPolizas() - 1;
          $polizasinsertadas = $this->enviarExcedentesSinAplicar( $fecha_inicio, $fecha_fin, $empresa_id,$otros_pasivos_cuenta, $doc_num, $fecha_poliza_generador );
          $reccnoultimo = $this->consultarRECNOPolizas() - 1;
          $nuevoConsecutivo = $doc_num +1;
          $consecutivo_poliza = DB::table('SI_FORMS_BAPP02F92DB8_FRM_B6B5F9F7')->update(['FFRMS_4CDA52E8' => $nuevoConsecutivo ]);
          $evidencia = DB::table('ZC1000_poliza')->whereBetween('p31_RECNO',[$reccnoanterior, $reccnoultimo])->where('tipo_poliza', 'Excedentes sin aplicar')->paginate(10);
        }
        elseif ($tipo_poliza == 4977) {
          $reccnoanterior = $this->consultarRECNOPolizas() - 1;
          $polizasinsertadas = $this->enviarDepositosGarantia( $fecha_inicio, $fecha_fin, $empresa_id, $Dep_garantía_clientes, $Dep_garantía_cobrar_cliente, $doc_num, $fecha_poliza_generador );
          $reccnoultimo = $this->consultarRECNOPolizas() - 1;
          $nuevoConsecutivo = $doc_num +1;
          $consecutivo_poliza = DB::table('SI_FORMS_BAPP02F92DB8_FRM_B6B5F9F7')->update(['FFRMS_4CDA52E8' => $nuevoConsecutivo ]);
          $evidencia = DB::table('ZC1000_poliza')->whereBetween('p31_RECNO',[$reccnoanterior, $reccnoultimo])->where('tipo_poliza', 'Deposito en garantia')->paginate(10);
        }
        elseif ($tipo_poliza == 5051) {
          $reccnoanterior = $this->consultarRECNOPolizas() - 1;
          $polizasinsertadas =  $this->enviarJuridicos( $fecha_inicio, $fecha_fin, $empresa_id, $cuenta_deudores_diversos, $doc_num, $fecha_poliza_generador );
          $reccnoultimo = $this->consultarRECNOPolizas() - 1;
          $nuevoConsecutivo = $doc_num +1;
          $consecutivo_poliza = DB::table('SI_FORMS_BAPP02F92DB8_FRM_B6B5F9F7')->update(['FFRMS_4CDA52E8' => $nuevoConsecutivo ]);
          $evidencia = DB::table('ZC1000_poliza')->whereBetween('p31_RECNO',[$reccnoanterior, $reccnoultimo])->where('tipo_poliza', 'Juridicos')->paginate(10);
        }
        elseif ($tipo_poliza == 5052) {
          $reccnoanterior = $this->consultarRECNOPolizas() - 1;
          $polizasinsertadas = $this->enviarPagosJuridicos( $fecha_inicio, $fecha_fin, $empresa_id, $cuenta_deudores_diversos ,$doc_num, $fecha_poliza_generador );
          $reccnoultimo = $this->consultarRECNOPolizas() - 1;
          $nuevoConsecutivo = $doc_num +1;
          $consecutivo_poliza = DB::table('SI_FORMS_BAPP02F92DB8_FRM_B6B5F9F7')->update(['FFRMS_4CDA52E8' => $nuevoConsecutivo ]);
          $evidencia = DB::table('ZC1000_poliza')->whereBetween('p31_RECNO',[$reccnoanterior, $reccnoultimo])->where('tipo_poliza', 'Pagos Juridicos')->paginate(10);
        }
        elseif ($tipo_poliza == 4809) { 
          $reccnoanterior = $this->consultarRECNOPolizas() - 1;
          $polizasinsertadas = $this->enviarBancos( $fecha_inicio, $fecha_fin, $empresa_id, $doc_num, $fecha_poliza_generador );
          $reccnoultimo = $this->consultarRECNOPolizas() - 1;
          $nuevoConsecutivoBancos = $doc_num +1;
          $consecutivo_poliza = DB::table('SI_FORMS_BAPP02F92DB8_FRM_B6B5F9F7')->update(['FFRMS_FFD26E6B' => $nuevoConsecutivoBancos ]);
          $evidencia_financiero = DB::table('ze5xxx_financiero')->whereBetween('p97_RECNO',[$reccnoanterior, $reccnoultimo])->where('tipo_poliza', 'Bancos')->paginate(10);
        }
        elseif ($tipo_poliza == 5020) {
          $reccnoanterior = $this->consultarRECNOPolizas() - 1;
          $polizasinsertadas = $this->enviarBancosExcedentes( $fecha_inicio, $fecha_fin, $empresa_id, $doc_num, $fecha_poliza_generador );
          $reccnoultimo = $this->consultarRECNOPolizas() - 1;
          $nuevoConsecutivoBancos = $doc_num +1;
          $consecutivo_poliza = DB::table('SI_FORMS_BAPP02F92DB8_FRM_B6B5F9F7')->update(['FFRMS_FFD26E6B' => $nuevoConsecutivoBancos ]);
          $evidencia_financiero = DB::table('ze5xxx_financiero')->whereBetween('p97_RECNO',[$reccnoanterior, $reccnoultimo])->where('tipo_poliza', 'Bancos excedente')->paginate(10);
        }

        $updatePoliza = DB::table('SI_FORMS_BAPP02F92DB8_FRM_B6B5F9F7')
              ->update(['FFRMS_D1192FE0' => 4819, 'FFRMS_C10D87B8' => 'Off']);
        
      }


      return view('webservices.polizas.index', compact('request','tipos_polizas','empresas','reccnoanterior','polizasinsertadas', 'reccnoultimo', 'evidencia','evidencia_financiero'));

    }

    ///////////////// FUNCIONES POR SEPARADO
    public function enviarFactura($fecha_inicio, $fecha_fin, $empresa_id, $doc_num, $FechaActual )
    {
      
      $a = 1;
      $c = 1;
      $foliof= null;
      $Ncliente= null;
      $Arrendatario= null;
      $NumeroEmpresa = null;
      $Nempresa = null;
      $CuentaContable = ''; 
      $CuentaContableA = ''; 
      $contrato_id = '';
      $ItemContable = '';
      $NumeroEmpresa = '';
      $NombreEmpresa = '';
      $CentroCosto = '';
      $codigo_unidad = '';
      $registros = 0;
      $EmpresaPoliza = $empresa_id;
      //Variables DATA
      $p2_DATA = date('Ymd',strtotime($FechaActual));
      $p5_DC = '';
      $p6_DOC = $doc_num;
      $p7_LINHA = '';
      $p12_HIST = '';
      $p13_VALOR = '';
      $p14_PKO = '';
      $p17_LOTE = '';
      $p18_SBLOTE = 'REN';
      $p19_CTCD = '';
      $p20_CTAD = '';
      $p21_CCCD = '';
      $p22_CCAD = '';
      $p23_ITCD = '';
      $p24_ITAD = '';
      $p25_CLCD = '';
      $p26_CLAD = '';
      $p27_EMPORI = '';
      $p31_RECNO = '';

      $selectFacturas = DB::table('SI_FORMS_BAPP02F92DB8_FRM_0A48F90C')
      ->where('FFRMS_B53C0145', 426)
      ->where('FFRMS_8F2D2180', $empresa_id)
      ->where('FFRMS_80C0CBBF', '!=', 2)
      ->whereNotIn('FFRMS_90A4BCE5', ['Deposito en garantia', 'Nota de credito'])
      ->whereRaw('FFRMS_D34DBFC0 = 5356')
      ->whereBetween('FFRMS_E73C86DE', [$fecha_inicio, $fecha_fin])
      ->get();

      foreach ($selectFacturas as $filaFactura) {
        if($a <= 999){
          $tipoComprobante = $filaFactura->FFRMS_80C0CBBF;
          $id_Factura = $filaFactura->id_FRM_0A48F90C;
          $IVA = $filaFactura->FFRMS_8EB4E52A;
          $impuesto = $filaFactura->FFRMS_D589F6C6;
          $foliof = $filaFactura->FFRMS_F6E17AB2;
          $UnidadF = $filaFactura->FFRMS_B497EAD9;
          $Arrendatario_id = $filaFactura->FFRMS_C54453B0;
          $TotalFactura = $filaFactura->FFRMS_00B26ED7;
          $SubtotalFactura = $filaFactura->FFRMS_D6356B12;
          $FacturaArrendatario = $filaFactura->FFRMS_C54453B0;
          $moneda = $filaFactura->FFRMS_E90D619F;
          $contrato_id = $filaFactura->FFRMS_2A2EAA0F;
          $domicilio_predefinida_id = $filaFactura->FFRMS_EC831C2A;
          $sucursal_predefinida_id = $filaFactura->FFRMS_6AC356E0;
          $EmpresaF = $filaFactura->FFRMS_8F2D2180;
          $FechaFs = $filaFactura->FFRMS_A50AB74A; //fecha certiifcacion
          $p14_PKO_llave_referencia = '';
          $ClaseValor = null;
          if ($FechaFs == null or $FechaFs == '') {
            $FechaFs = $filaFactura->FFRMS_E73C86DE; //fecha
          }

          if($tipoComprobante == 1){
            $p17_LOTE_tipo_comprobante = 3;//tipo de poliza Ingresos
          }
          elseif($tipoComprobante == 2){
            $p17_LOTE_tipo_comprobante = 2;//tipo de poliza Egreso
          }
          elseif ($tipoComprobante == 6) {
            $p17_LOTE_tipo_comprobante = 1;//tipo de poliza Diarios
          }

          $SelectSucursal = DB::table('SI_FORMS_BAPP02F92DB8_FRM_045A4975')
          ->where('id_FRM_045A4975', $sucursal_predefinida_id)
          ->first();
          if ($SelectSucursal) {
            $CuentaContableA = $SelectSucursal->FFRMS_25103FA5;
            $ClaseValor = $SelectSucursal->FFRMS_CECFEB82;
            $Ncliente = $SelectSucursal->FFRMS_0B6479A0;
            $Arrendatario = $SelectSucursal->FFRMS_32B278A2;
          }

          if ($ClaseValor == null) {
            $ClaseValor = '';
          }
          if ($CuentaContableA) {
            $SelectCuentaContable = DB::table('SI_FORMS_BAPP02F92DB8_FRM_229230AF')->select('FFRMS_87A95699')
            ->where('id_FRM_229230AF', $CuentaContableA)
            ->first();
            if ($SelectCuentaContable) {
              $CuentaContable = $SelectCuentaContable->FFRMS_87A95699;
            }
          }

          $SelectUnidad = DB::table('SI_FORMS_BAPP02F92DB8_FRM_E2241755')
          ->select('FFRMS_649B0EF1','FFRMS_79965C05','FFRMS_DB97FE86','FFRMS_85D52E6B','FFRMS_FB5AFCAC')
          ->where('id_FRM_E2241755', $UnidadF)
          ->first();
          $ItemContable = '';
          $NumeroEmpresa = '';
          $NombreEmpresa = '';
          $CentroCosto = '';
          $codigo_unidad = '';
          if ($SelectUnidad) {
            $ItemContable = $SelectUnidad->FFRMS_649B0EF1;
            $NumeroEmpresa = $SelectUnidad->FFRMS_79965C05;
            $NombreEmpresa = $SelectUnidad->FFRMS_DB97FE86;
            $CentroCosto = $SelectUnidad->FFRMS_85D52E6B;
            $codigo_unidad = $SelectUnidad->FFRMS_FB5AFCAC;
          }
          
          $FechaF=date('Ymd',strtotime($FechaFs));
 
          $SelectEmpresa = DB::table('SI_FORMS_BAPP02F92DB8_FRM_92604F36')
          ->select('FFRMS_40F34F0E','FFRMS_7F569111')
          ->where('id_FRM_92604F36', $EmpresaPoliza)
          ->first();
          $p14_PKO_llave_referencia = '';
          if ($SelectEmpresa) {
            $Empresa = $SelectEmpresa->FFRMS_40F34F0E;
            $Nempresa = $SelectEmpresa->FFRMS_7F569111;
            $p14_PKO_llave_referencia = $Nempresa.$FechaF.$foliof;//Llave de referencia para identificar el movimiento ZC1_PKO
          }

          ///Emiezan los asientos por cada partida
          $selectUnidadPartidas = DB::table('SI_FORMS_BAPP02F92DB8_FRM_8276943C')
          ->join('SI_FORMS_BAPP02F92DB8_FRM_E2241755','FFRMS_984B940B','=','id_FRM_E2241755','left',false)
          ->where('FFRMS_1D8401A5', $id_Factura)
          ->groupBy('FFRMS_984B940B')
          ->get();
          
          foreach ($selectUnidadPartidas as $filapartidaunidad) {
            $unidad_id = $filapartidaunidad->FFRMS_984B940B;
            if ($unidad_id == null or $unidad_id == '') {
              $unidad_id = $UnidadF;
            }
            if ($unidad_id == null or $unidad_id == '') {
              $query = '';
            }else{
              $query = ' AND FFRMS_984B940B = '.$unidad_id;
            }

            $selectUnidadInfo = DB::table('SI_FORMS_BAPP02F92DB8_FRM_E2241755')
            ->where('id_FRM_E2241755', $unidad_id)
            ->get();

            $numUnidad ='';
            $nombreUnidad='';
            foreach ($selectUnidadInfo as $filainfounidad) {
              $nombreUnidad = $filainfounidad->FFRMS_FD8B8320;
              $numUnidad = $filainfounidad->FFRMS_FB5AFCAC;
              $ItemContable = $filainfounidad->FFRMS_649B0EF1;
              $NumeroEmpresa = $filainfounidad->FFRMS_79965C05;
              $NombreEmpresa = $filainfounidad->FFRMS_DB97FE86;
              $CentroCosto = $filainfounidad->FFRMS_85D52E6B;
            }
            $p12_HIST_concatenado = 'F-'.$foliof.' L-'.$numUnidad.' CTE-'.$Ncliente." ".$Arrendatario;//La referencia o concepto del movimiento ZC1_HIST

            // ------------------------------------------SE CREAN 4 POLIZAS POR CADA FACTURA ENCONTRADA------------------------------------

            $selectPartidas = DB::table('SI_FORMS_BAPP02F92DB8_FRM_8276943C')
            ->whereRaw('FFRMS_1D8401A5 = '.$id_Factura.$query)
            ->get();
            foreach ($selectPartidas as $filaPartidas) {
              $c++;
              //$a++;
              $conceptopartida = $filaPartidas->FFRMS_680851A6;
              $totalPartida = $filaPartidas->FFRMS_57C46DD3;
              $ivaPartida = $filaPartidas->FFRMS_7C9A07A7;
              $SubtotalPartida = $filaPartidas->FFRMS_72DAC42D;
              $impuesto = $filaPartidas->FFRMS_564F3B89;

              $CuentaContableImpuestos = null;
              $filasImpuestos = DB::table('SI_FORMS_BAPP02F92DB8_FRM_B11C5D02')
              ->where('id_FRM_B11C5D02', $impuesto)
              ->first();
              if ($filasImpuestos) {
                $CuentaContableImpuestos = $filasImpuestos->FFRMS_B28B7E32;

                $filaCuenta = DB::table('SI_FORMS_BAPP02F92DB8_FRM_229230AF')
                ->where('id_FRM_229230AF',$CuentaContableImpuestos)
                ->first();
                if ($filaCuenta) {
                  $CuentaContableImpuestos = $filaCuenta->FFRMS_87A95699;
                }
              }
              $cuentaIngreso = '';
              $SelectCuentaIngresos = DB::table('SI_FORMS_BAPP02F92DB8_FRM_E2F0D604')
              ->where('id_FRM_E2F0D604', $conceptopartida)
              ->first();

              if ($SelectCuentaIngresos) {
                $agregarItem = $SelectCuentaIngresos->FFRMS_4265BD5A;
                $agregarcentro = $SelectCuentaIngresos->FFRMS_2FCF47C1;
                $agregarclase = $SelectCuentaIngresos->FFRMS_C37C0676;
                $cuentaIngreso = $SelectCuentaIngresos->FFRMS_EFFC9C36;
              }
              if ($CentroCosto == null) {
                $CentroCosto = '';
              }
              if ($ItemContable == null) {
                $ItemContable = '';
              }
              $ItemContable_add = '';
              $centroCasto_add = '';
              if ($agregarItem == 'Si') {
                $ItemContable_add = $ItemContable;
              }
              if ($agregarcentro == 'Si') {
                $centroCasto_add = $CentroCosto;
              }
              ///// PRIMER ASIENTO 1/4 
              $p31_RECNO_max = $this->consultarRECNOPolizas();

              if ($p31_RECNO_max != null) {
                
                $p5_DC_debitoOrCredito = '1';
                $data = array(
                'p2_DATA' => $p2_DATA, /*Fijo*/
                'p5_DC' => $p5_DC_debitoOrCredito,
                'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                'p7_LINHA' => $a,
                'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                'p13_VALOR' => $totalPartida,
                'p14_PKO' => $p14_PKO_llave_referencia,
                'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                'p19_CTCD' => $CuentaContable,
                'p20_CTAD' => '',
                'p21_CCCD' => '',
                'p22_CCAD' => '',
                'p23_ITCD' => '',
                'p24_ITAD' => '',
                'p25_CLCD' => $ClaseValor,
                'p26_CLAD' => '',
                'p27_EMPORI' => $NumeroEmpresa,
                'p31_RECNO' => $p31_RECNO_max
                );
                
                
                ///SI RESULT === TRUE
                $result_enviar = $this->crearRegistroPoliza($data, 'Factura');
                if ($result_enviar === False) {
                  file_put_contents('error_factura.txt', json_encode($data));
                  exit(1);
                }else{
                  $a++;
                  $registros = $registros + 1;
                  error_log('SUCCESS: '.json_encode($data), 0);
                }
              }
              if ($cuentaIngreso == null) {
                $cuentaIngreso = '';
              }
              if ($centroCasto_add == null) {
                $centroCasto_add = '';
              }
              if ($ItemContable_add == null) {
                $ItemContable_add = '';
              }
              ///// TERCER ASIENTO 2/3 PARTIDAS
              $p31_RECNO_max = $this->consultarRECNOPolizas();
              if ($p31_RECNO_max != null) {
                $p5_DC_debitoOrCredito = '2';
                $data = array(
                'p2_DATA' => $p2_DATA, /*Fijo*/
                'p5_DC' => $p5_DC_debitoOrCredito,
                'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                'p7_LINHA' => $a,
                'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                'p13_VALOR' => $SubtotalPartida,
                'p14_PKO' => $p14_PKO_llave_referencia,
                'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                'p19_CTCD' => '',
                'p20_CTAD' => $cuentaIngreso,
                'p21_CCCD' => '',
                'p22_CCAD' => $centroCasto_add,
                'p23_ITCD' => '',
                'p24_ITAD' => $ItemContable_add,
                'p25_CLCD' => '',
                'p26_CLAD' => $ClaseValor,
                'p27_EMPORI' => $NumeroEmpresa,
                'p31_RECNO' => $p31_RECNO_max
                );
                 
                ///SI RESULT === TRUE
                $result_enviar = $this->crearRegistroPoliza($data, 'Factura');
                if ($result_enviar === False) {
                  error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                  //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                  exit(1);
                }else{
                  $a++;
                  $registros = $registros + 1;
                  error_log('SUCCESS: '.json_encode($data), 0);
                  //echo "<br/> SUCCESS: ".json_encode($data);
                }
              }
              ///// SEGUNDO ASIENTO 3/3 IVA
              $p31_RECNO_max = $this->consultarRECNOPolizas();
              if ($p31_RECNO_max != null) {
                $p5_DC_debitoOrCredito = '2';
                $data = array(
                'p2_DATA' => $p2_DATA, /*Fijo*/
                'p5_DC' => $p5_DC_debitoOrCredito,
                'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                'p7_LINHA' => $a,
                'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                'p13_VALOR' => $ivaPartida,
                'p14_PKO' => $p14_PKO_llave_referencia,
                'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                'p19_CTCD' => '',
                'p20_CTAD' => $CuentaContableImpuestos,
                'p21_CCCD' => '',
                'p22_CCAD' => '',
                'p23_ITCD' => '',
                'p24_ITAD' => '',
                'p25_CLCD' => '',
                'p26_CLAD' => '',
                'p27_EMPORI' => $NumeroEmpresa,
                'p31_RECNO' => $p31_RECNO_max
                );
                 
                ///SI RESULT === TRUE
                $result_enviar = $this->crearRegistroPoliza($data, 'Factura');
                if ($result_enviar === False) {
                  error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                  //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                  exit(1);
                }else{
                  $a++;
                  $registros = $registros + 1;
                  error_log('SUCCESS: '.json_encode($data), 0);
                  //echo "<br/> SUCCESS: ".json_encode($data);
                }
              }

            }
          }
          $updateFactura = DB::table('SI_FORMS_BAPP02F92DB8_FRM_0A48F90C') 
          ->where('id_FRM_0A48F90C', $id_Factura)
          ->update(['FFRMS_D34DBFC0' => 4488]);
        }
      }
      return $registros;
    }
    public function enviarNotaCredito($fecha_inicio, $fecha_fin, $empresa_id , $doc_num, $FechaActual )
    {
      
      $a = 1;
      $c = 1;
      $foliof= null;
      $Ncliente= null;
      $Arrendatario= null;
      $NumeroEmpresa = null;
      $Nempresa = null;
      $CuentaContable = ''; 
      $CuentaContableA = ''; 
      $contrato_id = '';
      $ItemContable = '';
      $NumeroEmpresa = '';
      $NombreEmpresa = '';
      $CentroCosto = '';
      $codigo_unidad = '';
      $registros = 0;
      $EmpresaPoliza = $empresa_id;
      //Variables DATA
      $p2_DATA = date('Ymd',strtotime($FechaActual));
      $p5_DC = '';
      $p6_DOC = $doc_num;
      $p7_LINHA = '';
      $p12_HIST = '';
      $p13_VALOR = '';
      $p14_PKO = '';
      $p17_LOTE = '';
      $p18_SBLOTE = 'REN';
      $p19_CTCD = '';
      $p20_CTAD = '';
      $p21_CCCD = '';
      $p22_CCAD = '';
      $p23_ITCD = '';
      $p24_ITAD = '';
      $p25_CLCD = '';
      $p26_CLAD = '';
      $p27_EMPORI = '';
      $p31_RECNO = '';

      $selectFacturas = DB::table('SI_FORMS_BAPP02F92DB8_FRM_0A48F90C')
      ->where('FFRMS_B53C0145', 426)
      ->where('FFRMS_8F2D2180', $empresa_id)
      ->whereIn('FFRMS_90A4BCE5', ['Nota de credito'])
      ->whereRaw('FFRMS_D34DBFC0 = 5356')
      ->whereBetween('FFRMS_E73C86DE', [$fecha_inicio, $fecha_fin])
      ->get();

      foreach ($selectFacturas as $filaFactura) {
        if($a <= 999){
          $tipoComprobante = $filaFactura->FFRMS_80C0CBBF;
          $id_Factura = $filaFactura->id_FRM_0A48F90C;
          $IVA = $filaFactura->FFRMS_8EB4E52A;
          $impuesto = $filaFactura->FFRMS_D589F6C6;
          $foliof = $filaFactura->FFRMS_F6E17AB2;
          $UnidadF = $filaFactura->FFRMS_B497EAD9;
          $Arrendatario_id = $filaFactura->FFRMS_C54453B0;
          $TotalFactura = $filaFactura->FFRMS_00B26ED7;
          $SubtotalFactura = $filaFactura->FFRMS_D6356B12;
          $FacturaArrendatario = $filaFactura->FFRMS_C54453B0;
          $moneda = $filaFactura->FFRMS_E90D619F;
          $contrato_id = $filaFactura->FFRMS_2A2EAA0F;
          $domicilio_predefinida_id = $filaFactura->FFRMS_EC831C2A;
          $sucursal_predefinida_id = $filaFactura->FFRMS_6AC356E0;
          $EmpresaF = $filaFactura->FFRMS_8F2D2180;
          $FechaFs = $filaFactura->FFRMS_A50AB74A; //fecha certiifcacion
          $p14_PKO_llave_referencia = '';
          $ClaseValor = null;
          if ($FechaFs == null or $FechaFs == '') {
            $FechaFs = $filaFactura->FFRMS_E73C86DE; //fecha
          }

          if($tipoComprobante == 1){
            $p17_LOTE_tipo_comprobante = 3;//tipo de poliza Ingresos
          }
          elseif($tipoComprobante == 2){
            $p17_LOTE_tipo_comprobante = 2;//tipo de poliza Egreso
          }
          elseif ($tipoComprobante == 6) {
            $p17_LOTE_tipo_comprobante = 1;//tipo de poliza Diarios
          }

          $SelectSucursal = DB::table('SI_FORMS_BAPP02F92DB8_FRM_045A4975')
          ->where('id_FRM_045A4975', $sucursal_predefinida_id)
          ->first();
          if ($SelectSucursal) {
            $CuentaContableA = $SelectSucursal->FFRMS_25103FA5;
            $ClaseValor = $SelectSucursal->FFRMS_CECFEB82;
            $Ncliente = $SelectSucursal->FFRMS_0B6479A0;
            $Arrendatario = $SelectSucursal->FFRMS_32B278A2;
          }

          if ($ClaseValor == null) {
            $ClaseValor = '';
          }
          if ($CuentaContableA) {
            $SelectCuentaContable = DB::table('SI_FORMS_BAPP02F92DB8_FRM_229230AF')->select('FFRMS_87A95699')
            ->where('id_FRM_229230AF', $CuentaContableA)
            ->first();
            if ($SelectCuentaContable) {
              $CuentaContable = $SelectCuentaContable->FFRMS_87A95699;
            }
          }

          $SelectUnidad = DB::table('SI_FORMS_BAPP02F92DB8_FRM_E2241755')
          ->select('FFRMS_649B0EF1','FFRMS_79965C05','FFRMS_DB97FE86','FFRMS_85D52E6B','FFRMS_FB5AFCAC')
          ->where('id_FRM_E2241755', $UnidadF)
          ->first();
          if ($SelectUnidad) {
            $ItemContable = $SelectUnidad->FFRMS_649B0EF1;
            $NumeroEmpresa = $SelectUnidad->FFRMS_79965C05;
            $NombreEmpresa = $SelectUnidad->FFRMS_DB97FE86;
            $CentroCosto = $SelectUnidad->FFRMS_85D52E6B;
            $codigo_unidad = $SelectUnidad->FFRMS_FB5AFCAC;
          }
          
          $FechaF=date('Ymd',strtotime($FechaFs));
 
          $SelectEmpresa = DB::table('SI_FORMS_BAPP02F92DB8_FRM_92604F36')
          ->select('FFRMS_40F34F0E','FFRMS_7F569111')
          ->where('id_FRM_92604F36', $EmpresaPoliza)
          ->first();
          $p14_PKO_llave_referencia = '';
          if ($SelectEmpresa) {
            $Empresa = $SelectEmpresa->FFRMS_40F34F0E;
            $Nempresa = $SelectEmpresa->FFRMS_7F569111;
            $p14_PKO_llave_referencia = $Nempresa.$FechaF.$foliof;//Llave de referencia para identificar el movimiento ZC1_PKO
          }

          ///Emiezan los asientos por cada partida
          $selectUnidadPartidas = DB::table('SI_FORMS_BAPP02F92DB8_FRM_8276943C')
          ->join('SI_FORMS_BAPP02F92DB8_FRM_E2241755','FFRMS_984B940B','=','id_FRM_E2241755','left',false)
          ->where('FFRMS_1D8401A5', $id_Factura)
          ->groupBy('FFRMS_984B940B')
          ->get();
          
          foreach ($selectUnidadPartidas as $filapartidaunidad) {
            $unidad_id = $filapartidaunidad->FFRMS_984B940B;
            if ($unidad_id == null or $unidad_id == '') {
              $unidad_id = $UnidadF;
            }
            if ($unidad_id == null or $unidad_id == '') {
              $query = '';
            }else{
              $query = ' AND FFRMS_984B940B = '.$unidad_id;
            }

            $selectUnidadInfo = DB::table('SI_FORMS_BAPP02F92DB8_FRM_E2241755')
            ->where('id_FRM_E2241755', $unidad_id)
            ->get();

            $numUnidad ='';
            $nombreUnidad='';
            foreach ($selectUnidadInfo as $filainfounidad) {
              $nombreUnidad = $filainfounidad->FFRMS_FD8B8320;
              $numUnidad = $filainfounidad->FFRMS_FB5AFCAC;
              $ItemContable = $filainfounidad->FFRMS_649B0EF1;
              $NumeroEmpresa = $filainfounidad->FFRMS_79965C05;
              $NombreEmpresa = $filainfounidad->FFRMS_DB97FE86;
              $CentroCosto = $filainfounidad->FFRMS_85D52E6B;
            }
            $p12_HIST_concatenado = 'NC-'.$foliof.' L-'.$numUnidad.' CTE-'.$Ncliente." ".$Arrendatario;//La referencia o concepto del movimiento ZC1_HIST

            // ------------------------------------------SE CREAN 4 POLIZAS POR CADA FACTURA ENCONTRADA------------------------------------
            //error_log("$unidad_id -- SELECT * FROM SI_FORMS_BAPP02F92DB8_FRM_8276943C WHERE FFRMS_1D8401A5 = $id_Factura ".$query);

            $selectPartidas = DB::table('SI_FORMS_BAPP02F92DB8_FRM_8276943C')
            ->whereRaw('FFRMS_1D8401A5 = '.$id_Factura.$query)
            ->get();
            foreach ($selectPartidas as $filaPartidas) {
              $c++;
              //$a++;
              $conceptopartida = $filaPartidas->FFRMS_680851A6;
              $totalPartida = $filaPartidas->FFRMS_57C46DD3;
              $ivaPartida = $filaPartidas->FFRMS_7C9A07A7;
              $SubtotalPartida = $filaPartidas->FFRMS_72DAC42D;
              $impuesto = $filaPartidas->FFRMS_564F3B89;

              $CuentaContableImpuestos = null;
              $filasImpuestos = DB::table('SI_FORMS_BAPP02F92DB8_FRM_B11C5D02')
              ->where('id_FRM_B11C5D02', $impuesto)
              ->first();
              if ($filasImpuestos) {
                $CuentaContableImpuestos = $filasImpuestos->FFRMS_B28B7E32;

                $filaCuenta = DB::table('SI_FORMS_BAPP02F92DB8_FRM_229230AF')
                ->where('id_FRM_229230AF',$CuentaContableImpuestos)
                ->first();
                if ($filaCuenta) {
                  $CuentaContableImpuestos = $filaCuenta->FFRMS_87A95699;
                }
              }
              $cuentaIngreso = '';
              $SelectCuentaIngresos = DB::table('SI_FORMS_BAPP02F92DB8_FRM_E2F0D604')
              ->where('id_FRM_E2F0D604', $conceptopartida)
              ->first();

              if ($SelectCuentaIngresos) {
                $agregarItem = $SelectCuentaIngresos->FFRMS_4265BD5A;
                $agregarcentro = $SelectCuentaIngresos->FFRMS_2FCF47C1;
                $agregarclase = $SelectCuentaIngresos->FFRMS_C37C0676;
                $cuentaIngreso = $SelectCuentaIngresos->FFRMS_EFFC9C36;
              }
              $ItemContable_add = '';
              $centroCasto_add = '';
              if ($agregarItem == 'Si') {
                $ItemContable_add = $ItemContable;
              }
              if ($agregarcentro == 'Si') {
                $centroCasto_add = $CentroCosto;
              }

              ///// PRIMER ASIENTO 1/4 
              $p31_RECNO_max = $this->consultarRECNOPolizas();

              if ($p31_RECNO_max != null) {
                
                $p5_DC_debitoOrCredito = '1';
                $data = array(
                'p2_DATA' => $p2_DATA, /*Fijo*/
                'p5_DC' => $p5_DC_debitoOrCredito,
                'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                'p7_LINHA' => $a,
                'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                'p13_VALOR' => $totalPartida,
                'p14_PKO' => $p14_PKO_llave_referencia,
                'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                'p19_CTCD' => '',
                'p20_CTAD' => $CuentaContable,
                'p21_CCCD' => '',
                'p22_CCAD' => '',
                'p23_ITCD' => '',
                'p24_ITAD' => '',
                'p25_CLCD' => '',
                'p26_CLAD' => $ClaseValor,
                'p27_EMPORI' => $NumeroEmpresa,
                'p31_RECNO' => $p31_RECNO_max
                );
                
                
                ///SI RESULT === TRUE
                $result_enviar = $this->crearRegistroPoliza($data, 'Nota de Credito');
                if ($result_enviar === False) {
                  file_put_contents('error_factura.txt', json_encode($data));
                  exit(1);
                }else{
                  $a++;
                  $registros = $registros + 1;
                  error_log('SUCCESS: '.json_encode($data), 0);
                }
              }

              ///// TERCER ASIENTO 2/3 PARTIDAS
              $p31_RECNO_max = $this->consultarRECNOPolizas();
              if ($p31_RECNO_max != null) {
                $p5_DC_debitoOrCredito = '2';
                $data = array(
                'p2_DATA' => $p2_DATA, /*Fijo*/
                'p5_DC' => $p5_DC_debitoOrCredito,
                'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                'p7_LINHA' => $a,
                'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                'p13_VALOR' => $SubtotalPartida,
                'p14_PKO' => $p14_PKO_llave_referencia,
                'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                'p19_CTCD' => $cuentaIngreso,
                'p20_CTAD' => '',
                'p21_CCCD' => $centroCasto_add,
                'p22_CCAD' => '',
                'p23_ITCD' => $ItemContable_add,
                'p24_ITAD' => '',
                'p25_CLCD' => $ClaseValor,
                'p26_CLAD' => '',
                'p27_EMPORI' => $NumeroEmpresa,
                'p31_RECNO' => $p31_RECNO_max
                );
                 
                ///SI RESULT === TRUE
                $result_enviar = $this->crearRegistroPoliza($data, 'Nota de Credito');
                if ($result_enviar === False) {
                  error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                  //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                  exit(1);
                }else{
                  $a++;
                  $registros = $registros + 1;
                  error_log('SUCCESS: '.json_encode($data), 0);
                  //echo "<br/> SUCCESS: ".json_encode($data);
                }
              }
              ///// SEGUNDO ASIENTO 3/3 IVA
              $p31_RECNO_max = $this->consultarRECNOPolizas();
              if ($p31_RECNO_max != null) {
                $p5_DC_debitoOrCredito = '2';
                $data = array(
                'p2_DATA' => $p2_DATA, /*Fijo*/
                'p5_DC' => $p5_DC_debitoOrCredito,
                'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                'p7_LINHA' => $a,
                'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                'p13_VALOR' => $ivaPartida,
                'p14_PKO' => $p14_PKO_llave_referencia,
                'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                'p19_CTCD' => $CuentaContableImpuestos,
                'p20_CTAD' => '',
                'p21_CCCD' => '',
                'p22_CCAD' => '',
                'p23_ITCD' => '',
                'p24_ITAD' => '',
                'p25_CLCD' => '',
                'p26_CLAD' => '',
                'p27_EMPORI' => $NumeroEmpresa,
                'p31_RECNO' => $p31_RECNO_max
                );
                 
                ///SI RESULT === TRUE
                $result_enviar = $this->crearRegistroPoliza($data, 'Nota de Credito');
                if ($result_enviar === False) {
                  error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                  //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                  exit(1);
                }else{
                  $a++;
                  $registros = $registros + 1;
                  error_log('SUCCESS: '.json_encode($data), 0);
                  //echo "<br/> SUCCESS: ".json_encode($data);
                }
              }

            }
          }
          $updateFactura = DB::table('SI_FORMS_BAPP02F92DB8_FRM_0A48F90C') 
          ->where('id_FRM_0A48F90C', $id_Factura)
          ->update(['FFRMS_D34DBFC0' => 4488]);
        }
      }
      return $registros;
    }
    public function enviarPagos($fecha_inicio, $fecha_fin, $empresa_id, $Dep_garantía_cobrar_cliente, $Dep_garantía_clientes, $Dep_garantía_nombre, $otros_pasivos_cuenta, $doc_num, $FechaActual )
    {
      
      $a = 0;
      $c = 1;
      $foliof= null;
      $Ncliente= null;
      $Arrendatario= null;
      $NumeroEmpresa = null;
      $Nempresa = null;
      $CuentaContable = ''; 
      $CuentaContableA = ''; 
      $contrato_id = '';
      $ItemContable = '';
      $NumeroEmpresa = '';
      $NombreEmpresa = '';
      $CentroCosto = '';
      $codigo_unidad = '';
      $registros = 0;
      $EmpresaPoliza = $empresa_id;
      //Variables DATA
      $p2_DATA = date('Ymd',strtotime($FechaActual));
      $p5_DC = '';
      $p6_DOC = $doc_num;
      $p7_LINHA = '';
      $p12_HIST = '';
      $p13_VALOR = '';
      $p14_PKO = '';
      $p17_LOTE = '';
      $p18_SBLOTE = 'REN';
      $p19_CTCD = '';
      $p20_CTAD = '';
      $p21_CCCD = '';
      $p22_CCAD = '';
      $p23_ITCD = '';
      $p24_ITAD = '';
      $p25_CLCD = '';
      $p26_CLAD = '';
      $p27_EMPORI = '';
      $p31_RECNO = '';

      $fila = 0;

      $selectPagos = DB::table('SI_FORMS_BAPP02F92DB8_FRM_D4E87BCD')
      ->where('FFRMS_EDC7A690', $empresa_id)
      ->whereBetween('FFRMS_340F164A', [$fecha_inicio, $fecha_fin])
      ->whereRaw('(FFRMS_8A6CBF68 = 1718 or FFRMS_8A6CBF68 = 2891)')
      ->where('FFRMS_BB70BE4B','!=','Pago excedente')
      ->where('FFRMS_0999B7C0', 5357)
      ->get();
      foreach ($selectPagos as $filaPagos) {
        if($a <= 999){
          $a++;
          $id_pago = $filaPagos->id_FRM_D4E87BCD;

          $Complemento = $filaPagos->FFRMS_FD5BF009;
          $id_Factura = $filaPagos->FFRMS_AE0C9BD0;

          $Complemento = $filaPagos->FFRMS_FD5BF009;
          $TotalPago = $filaPagos->FFRMS_F542936B;
          $TotalPago_eX = $filaPagos->FFRMS_F542936B;
          $BancoP = $filaPagos->FFRMS_FF5C676C;
          $excedente_relacionado = $filaPagos->FFRMS_E32D8335;
          $Arrendatario_id = $filaPagos->FFRMS_A91655C6;
          $Emisor_id = $filaPagos->FFRMS_EDC7A690;
          $UnidadF = $filaPagos->FFRMS_83443295;
          $sucursal_predefinida_id = $filaPagos->FFRMS_446A19CF;
          $ClaseValor = '';
          $FechaFs = $filaPagos->FFRMS_340F164A;
          $FechaF=date('Ymd',strtotime($FechaFs));

          $CuentaContabelB = null;

          $selectComplemento = DB::table('SI_FORMS_BAPP02F92DB8_FRM_B20F14A3')
          ->where('id_FRM_B20F14A3', $Complemento)
          ->first();

          if ($selectComplemento) {
            $id_complemento = $selectComplemento->id_FRM_B20F14A3;
            $FolioComplemento = $selectComplemento->FFRMS_54D2F781;
          }

          //error_log('Factura encontrada: '.$id_Factura);
          $CuentaContable ='';
          $filaSucursal = DB::table('SI_FORMS_BAPP02F92DB8_FRM_045A4975')
          ->where('id_FRM_045A4975', $sucursal_predefinida_id)
          ->first();
          if ($filaSucursal) {
            $CuentaContableA = $filaSucursal->FFRMS_25103FA5;
            $ClaseValor = $filaSucursal->FFRMS_CECFEB82;
            $Ncliente = $filaSucursal->FFRMS_0B6479A0;
            $Arrendatario = $filaSucursal->FFRMS_32B278A2;

            if ($CuentaContableA != null) {
              $filaCuenta = DB::table('SI_FORMS_BAPP02F92DB8_FRM_229230AF')
              ->where('id_FRM_229230AF', $CuentaContableA)
              ->first();
              $CuentaContable = $filaCuenta->FFRMS_87A95699;
            }
          }

          if ($excedente_relacionado != null and $excedente_relacionado != '') {
            /// ir a excedenes pr ese monto ARRE
            $crear_asientos_excedente = 'Si';
            $Excedentes = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
            ->select('FFRMS_250EB570', 'FFRMS_8226F715')
            ->whereRaw("id_FRM_CEA84379 = $excedente_relacionado AND FFRMS_435DD1E5 = 'Si' AND FFRMS_7D843481 = 'Entrada' AND (FFRMS_E56345B5 = 'Revisada' )")
            ->first();
            if ($Excedentes) {
              $monto_excedente = $Excedentes->FFRMS_250EB570;
              $TotalPago_eX = $TotalPago_eX + $monto_excedente;
              $folio_excedente = $Excedentes->FFRMS_8226F715;
              $columna_historico_excedente = substr( 'REC '.$FolioComplemento.' EXT '.$folio_excedente.' CTE '.$Ncliente.' '.$Arrendatario, 0,40);
            }
          }else{
            $crear_asientos_excedente = 'No';
          }

          if ($ClaseValor == null) {
            $ClaseValor = '';
          }
          $CuentaContabelB = null;

          if($BancoP != null or $BancoP != ''){

            $SelectCuentaBancaria = DB::table('SI_FORMS_BAPP02F92DB8_FRM_FA24357A')->where('id_FRM_FA24357A', $BancoP)->first();
            if ($SelectCuentaBancaria) {
              $CuentaContabelB = $SelectCuentaBancaria->FFRMS_989765FB;
            }
          }

          $filaEmpresa = DB::table('SI_FORMS_BAPP02F92DB8_FRM_92604F36')
          ->where('id_FRM_92604F36',$empresa_id)
          ->first();

          if ($filaEmpresa) {
            $Empresa = $filaEmpresa->FFRMS_40F34F0E;
            $Nempresa = $filaEmpresa->FFRMS_7F569111;
            $p14_PKO_llave_referencia = $Nempresa.$FechaF.$foliof;//Llave de referencia para identificar el movimiento ZC1_PKO<
          }

          $selectDocumentos = DB::table('SI_FORMS_BAPP02F92DB8_FRM_39920E64')
          ->where('FFRMS_C4A5FEEC', $id_pago)
          ->take(1)
          ->get();
          foreach ($selectDocumentos as $filadoc) {
            $factura_id = $filadoc->FFRMS_70FC5145;

            $SelectFactura = DB::table('SI_FORMS_BAPP02F92DB8_FRM_0A48F90C')
            ->where('id_FRM_0A48F90C', $factura_id)
            ->take(1)
            ->get();
            foreach ($SelectFactura as $filaFactura) {
              $Factura_tipo = $filaFactura->FFRMS_90A4BCE5;
              $foliof = $filaFactura->FFRMS_F6E17AB2;
              $tipoComprobante = $filaFactura->FFRMS_80C0CBBF;
              $moneda = $filaFactura->FFRMS_E90D619F;
              if ($Factura_tipo == 'Deposito en garantia') {
                $ClaseValor = '';
                $p12_HIST_concatenado = 'REC '.$FolioComplemento.' DEP-'.$foliof.' CTE '.$Ncliente.' '.$Arrendatario;
              }else{
                $p12_HIST_concatenado = 'REC '.$FolioComplemento.' FAC-'.$foliof.' CTE '.$Ncliente.' '.$Arrendatario;
              }
            }
          }

          if($tipoComprobante == 1){
            $p17_LOTE_tipo_comprobante = 3;//tipo de poliza Ingresos
          }
          elseif($tipoComprobante == 2){
            $p17_LOTE_tipo_comprobante = 2;//tipo de poliza Egreso
          }
          elseif ($tipoComprobante == 6) {
            $p17_LOTE_tipo_comprobante = '1';//tipo de poliza Diarios
          }

          ///// PRIMER ASIENTO 1/4 
          $p31_RECNO_max = $this->consultarRECNOPolizas();
          if ($p31_RECNO_max != null) {
            if($Factura_tipo == 'Deposito en garantia'){
              $ZC1_CLCD = '';
            }else{
              $ZC1_CLCD = $ClaseValor;//clase de valor debito destino ZC1_CLCD
            }

            $p5_DC_debitoOrCredito = '1';
            $data = array(
            'p2_DATA' => $p2_DATA, /*Fijo*/
            'p5_DC' => $p5_DC_debitoOrCredito,
            'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
            'p7_LINHA' => $a,
            'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
            'p13_VALOR' => $TotalPago_eX,
            'p14_PKO' => $p14_PKO_llave_referencia,
            'p17_LOTE' => $p17_LOTE_tipo_comprobante,
            'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
            'p19_CTCD' => $CuentaContabelB,
            'p20_CTAD' => '',
            'p21_CCCD' => '',
            'p22_CCAD' => '',
            'p23_ITCD' => '',
            'p24_ITAD' => '',
            'p25_CLCD' => $ZC1_CLCD,
            'p26_CLAD' => '',
            'p27_EMPORI' => $Nempresa,
            'p31_RECNO' => $p31_RECNO_max
            );
             
            ///SI RESULT === TRUE
            $result_enviar = $this->crearRegistroPoliza($data, 'Pagos');
            if ($result_enviar === False) {
              error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
              //echo '<br/> FAIL: No inserte el registro poliza numero __1 '. $result_enviar. ' -- <br/>'.json_encode($data);
              exit(1);
            }else{
              $a++;
              $registros = $registros + 1;
              error_log('SUCCESS: '.json_encode($data), 0);
              //echo "<br/> SUCCESS: ".json_encode($data);
            }
          }

          /////////// RECOERRER LAS FACTURAS QUE PAGO
          /// Ya que el pago puede aplicarse a mas de una factura vamos a ir primero por los documentos reacionadso
          $selectDocumentos = DB::table('SI_FORMS_BAPP02F92DB8_FRM_39920E64')
          ->where('FFRMS_C4A5FEEC', $id_pago)
          ->get();
          foreach ($selectDocumentos as $filadoc) {
            $factura_id = $filadoc->FFRMS_70FC5145;
            $importe_pagado_factura = $filadoc->FFRMS_FD065CA1;

            $SelectFactura = DB::table('SI_FORMS_BAPP02F92DB8_FRM_0A48F90C')
            ->where('id_FRM_0A48F90C', $factura_id)
            ->take(1)
            ->get();
            foreach ($SelectFactura as $filaFactura) {
              $tipoComprobante = $filaFactura->FFRMS_80C0CBBF;
              $IVA = $filaFactura->FFRMS_8EB4E52A;
              $impuesto = $filaFactura->FFRMS_D589F6C6;
              $foliof = $filaFactura->FFRMS_F6E17AB2;
              $TotalFactura = $filaFactura->FFRMS_00B26ED7;
              $SubtotalFactura = $filaFactura->FFRMS_D6356B12;
              $FacturaArrendatario = $filaFactura->FFRMS_C54453B0;
              $moneda = $filaFactura->FFRMS_E90D619F;
              $Factura_tipo = $filaFactura->FFRMS_90A4BCE5;
              if ($Factura_tipo == 'Deposito en garantia') {
                $ClaseValor = '';
              }

              if ($Factura_tipo == 'Deposito en garantia') {
                $ClaseValor = '';
                $p12_HIST_concatenado = 'REC '.$FolioComplemento.' DEP-'.$foliof.' CTE '.$Ncliente.' '.$Arrendatario;
              }else{
                $p12_HIST_concatenado = 'REC '.$FolioComplemento.' FAC-'.$foliof.' CTE '.$Ncliente.' '.$Arrendatario;
              }

              /// CREAR POLIZAS
              if ($Factura_tipo == 'Deposito en garantia') {
                ///// SEGUNDO ASIENTO 2/4 DEPOSITO GARANTIA
                $p31_RECNO_max = $this->consultarRECNOPolizas();
                if ($p31_RECNO_max != null) {
                  if($Factura_tipo == 'Deposito en garantia'){
                    $ZC1_CLCD = '';
                  }else{
                    $ZC1_CLCD = $ClaseValor;//clase de valor debito destino ZC1_CLCD
                  }
                  $p5_DC_debitoOrCredito = '1';
                  $data = array(
                  'p2_DATA' => $p2_DATA, /*Fijo*/
                  'p5_DC' => $p5_DC_debitoOrCredito,
                  'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                  'p7_LINHA' => $a,
                  'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                  'p13_VALOR' => $importe_pagado_factura,
                  'p14_PKO' => $p14_PKO_llave_referencia,
                  'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                  'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                  'p19_CTCD' => $Dep_garantía_cobrar_cliente,
                  'p20_CTAD' => '',
                  'p21_CCCD' => '',
                  'p22_CCAD' => '',
                  'p23_ITCD' => '',
                  'p24_ITAD' => '',
                  'p25_CLCD' => '',
                  'p26_CLAD' => '',
                  'p27_EMPORI' => $Nempresa,
                  'p31_RECNO' => $p31_RECNO_max
                  );
                   
                  ///SI RESULT === TRUE
                  $result_enviar = $this->crearRegistroPoliza($data, 'Pagos Deposito');
                  if ($result_enviar === False) {
                    error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                    //echo '<br/> FAIL: No inserte el registro poliza numero D2'. $a;
                    exit(1);
                  }else{
                    $a++;
                    $registros = $registros + 1;
                    error_log('SUCCESS: '.json_encode($data), 0);
                    //echo "<br/> SUCCESS: ".json_encode($data);
                  }
                }

                ///// TERCER ASIENTO 3/4 DEPOSITO GARANTIA
                $p31_RECNO_max = $this->consultarRECNOPolizas();
                if ($p31_RECNO_max != null) {
                  if($Factura_tipo == 'Deposito en garantia'){
                    $ZC1_CLCD = '';
                  }else{
                    $ZC1_CLCD = $ClaseValor;//clase de valor debito destino ZC1_CLCD
                  }
                  $p5_DC_debitoOrCredito = '2';
                  $data = array(
                  'p2_DATA' => $p2_DATA, /*Fijo*/
                  'p5_DC' => $p5_DC_debitoOrCredito,
                  'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                  'p7_LINHA' => $a,
                  'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                  'p13_VALOR' => $importe_pagado_factura,
                  'p14_PKO' => $p14_PKO_llave_referencia,
                  'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                  'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                  'p19_CTCD' => '',
                  'p20_CTAD' => $Dep_garantía_clientes,
                  'p21_CCCD' => '',
                  'p22_CCAD' => '',
                  'p23_ITCD' => '',
                  'p24_ITAD' => '',
                  'p25_CLCD' => '',
                  'p26_CLAD' => '',
                  'p27_EMPORI' => $Nempresa,
                  'p31_RECNO' => $p31_RECNO_max
                  );
                   
                  ///SI RESULT === TRUE
                  $result_enviar = $this->crearRegistroPoliza($data, 'Pagos Deposito');
                  if ($result_enviar === False) {
                    error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                    //echo '<br/> FAIL: No inserte el registro poliza numero D3'. $a;
                    exit(1);
                  }else{
                    $a++;
                    $registros = $registros + 1;
                    error_log('SUCCESS: '.json_encode($data), 0);
                    ///echo "<br/> SUCCESS: ".json_encode($data);
                  }
                }
                ///// CUARTO ASIENTO 4/4 DEPOSITO GARANTIA
                $p31_RECNO_max = $this->consultarRECNOPolizas();
                if ($p31_RECNO_max != null) {
                  
                  $p5_DC_debitoOrCredito = '2';
                  $data = array(
                  'p2_DATA' => $p2_DATA, /*Fijo*/
                  'p5_DC' => $p5_DC_debitoOrCredito,
                  'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                  'p7_LINHA' => $a,
                  'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                  'p13_VALOR' => $importe_pagado_factura,
                  'p14_PKO' => $p14_PKO_llave_referencia,
                  'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                  'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                  'p19_CTCD' => '',
                  'p20_CTAD' => $Dep_garantía_nombre,
                  'p21_CCCD' => '',
                  'p22_CCAD' => '',
                  'p23_ITCD' => '',
                  'p24_ITAD' => '',
                  'p25_CLCD' => '',
                  'p26_CLAD' => '',
                  'p27_EMPORI' => $Nempresa,
                  'p31_RECNO' => $p31_RECNO_max
                  );
                   
                  ///SI RESULT === TRUE
                  $result_enviar = $this->crearRegistroPoliza($data, 'Pagos Deposito');
                  if ($result_enviar === False) {
                    error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                    //echo '<br/> FAIL: No inserte el registro poliza numero D4'. $a;
                    exit(1);
                  }else{
                    $a++;
                    $registros = $registros + 1;
                    error_log('SUCCESS: '.json_encode($data), 0);
                    //echo "<br/> SUCCESS: ".json_encode($data);
                  }
                }
              }else{ ///PAGO NORMAL
                ///// SEGUNDO ASIENTO 2/4 PAGO NORMAL
                $p31_RECNO_max = $this->consultarRECNOPolizas();
                if ($p31_RECNO_max != null) {
                  if($Factura_tipo == 'Deposito en garantia'){
                    $ZC1_CLCD = '';
                  }else{
                    $ZC1_CLCD = $ClaseValor;//clase de valor debito destino ZC1_CLCD
                  }
                  $p5_DC_debitoOrCredito = '2';
                  $data = array(
                  'p2_DATA' => $p2_DATA, /*Fijo*/
                  'p5_DC' => $p5_DC_debitoOrCredito,
                  'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                  'p7_LINHA' => $a,
                  'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                  'p13_VALOR' => $importe_pagado_factura,
                  'p14_PKO' => $p14_PKO_llave_referencia,
                  'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                  'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                  'p19_CTCD' => '',
                  'p20_CTAD' => $CuentaContable,
                  'p21_CCCD' => '',
                  'p22_CCAD' => '',
                  'p23_ITCD' => '',
                  'p24_ITAD' => '',
                  'p25_CLCD' => '',
                  'p26_CLAD' => '',
                  'p27_EMPORI' => $Nempresa,
                  'p31_RECNO' => $p31_RECNO_max
                  );
                   
                  ///SI RESULT === TRUE
                  $result_enviar = $this->crearRegistroPoliza($data, 'Pagos');
                  if ($result_enviar === False) {
                    error_log('FAIL: No inserte el registro poliza numero 2'. $a, 0);
                    //echo '<br/> FAIL: No inserte el registro poliza numero N2'. $a;
                    exit(1);
                  }else{
                    $a++;
                    $registros = $registros + 1;
                    error_log('SUCCESS: '.json_encode($data), 0);
                    //echo "<br/> SUCCESS: ".json_encode($data);
                  }
                }

                // ----------------------------------------Poliza IVA----------------------------
                ///SUMAR LOS IMPUESTOS POR PARTIDA
                $selectPartidasImpuestos = DB::table('SI_FORMS_BAPP02F92DB8_FRM_8276943C')
                ->select('FFRMS_564F3B89', 'id_FRM_8276943C', 'FFRMS_89817A23 as tasaIva', 'FFRMS_C40DAAFD as tasaRetIva', 'FFRMS_3ACD6BAA as tasaIsr', 'FFRMS_61C54875 as tasaIeps', DB::raw('SUM(FFRMS_7C9A07A7) as sumaIVA') )
                ->where('FFRMS_1D8401A5', $factura_id)
                ->groupBy('FFRMS_564F3B89')
                ->get();
                foreach ($selectPartidasImpuestos as $filapartidasImpuestos) {
                  $impuesto = $filapartidasImpuestos->FFRMS_564F3B89;
                  $totalIVA = $filapartidasImpuestos->sumaIVA;
                  $tasaIva = $filapartidasImpuestos->tasaIva;
                  if ($tasaIva > 0 ) {
                    $subtotal_partida_temp = round($importe_pagado_factura / ($tasaIva + 1), 2);
                    $iva_proporcional_pago = round($subtotal_partida_temp * $tasaIva , 2);
                  }else{
                    $iva_proporcional_pago = 0;
                  }
                  $CuentaContableImpuestos = null;
                  $SelecImpuesto= DB::table('SI_FORMS_BAPP02F92DB8_FRM_B11C5D02')
                  ->where('id_FRM_B11C5D02', $impuesto)
                  ->get();
                  foreach ($SelecImpuesto as $filasImpuestos) {
                    $CuentaContableImpuestos = $filasImpuestos->FFRMS_B28B7E32;

                    $SelectCuentaContable = DB::table('SI_FORMS_BAPP02F92DB8_FRM_229230AF')
                    ->where('id_FRM_229230AF', $CuentaContableImpuestos)
                    ->first();
                    if ($SelectCuentaContable) {
                      $CuentaContableImpuestos = $SelectCuentaContable->FFRMS_87A95699;
                    }

                    $CuentaContableImpuestos_cobrado = $filasImpuestos->FFRMS_80DCA203;
                    $SelectCuentaContable_cobrado = DB::table('SI_FORMS_BAPP02F92DB8_FRM_229230AF')
                    ->where('id_FRM_229230AF', $CuentaContableImpuestos_cobrado)
                    ->first();
                    if ($SelectCuentaContable_cobrado) {
                      $CuentaContableImpuestos_cobrado = $SelectCuentaContable_cobrado->FFRMS_87A95699;
                    }
                  }
                  ///// TERCER ASIENTO 3/4 PAGO NORMAL
                  $p31_RECNO_max = $this->consultarRECNOPolizas();
                  if ($p31_RECNO_max != null) {
                    $p5_DC_debitoOrCredito = '1';
                    $data = array(
                    'p2_DATA' => $p2_DATA, /*Fijo*/
                    'p5_DC' => $p5_DC_debitoOrCredito,
                    'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                    'p7_LINHA' => $a,
                    'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                    'p13_VALOR' => $iva_proporcional_pago,
                    'p14_PKO' => $p14_PKO_llave_referencia,
                    'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                    'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                    'p19_CTCD' => $CuentaContableImpuestos,
                    'p20_CTAD' => '',
                    'p21_CCCD' => '',
                    'p22_CCAD' => '',
                    'p23_ITCD' => '',
                    'p24_ITAD' => '',
                    'p25_CLCD' => '',
                    'p26_CLAD' => '',
                    'p27_EMPORI' => $Nempresa,
                    'p31_RECNO' => $p31_RECNO_max
                    );
                     
                    ///SI RESULT === TRUE
                    $result_enviar = $this->crearRegistroPoliza($data, 'Pagos');
                    if ($result_enviar === False) {
                      //error_log('FAIL: No inserte el registro poliza numero N3 iva'. $a, 0);
                      exit(1);
                    }else{
                      $a++;
                      $registros = $registros + 1;
                      //error_log('SUCCESS: '.json_encode($data), 0);
                    }
                  }
                  
                  // ---------------------------------Poliza COBRADO------------------------------
                  ///// CUARTO ASIENTO 4/4 PAGO NORMAL
                  $p31_RECNO_max = $this->consultarRECNOPolizas();
                  if ($p31_RECNO_max != null) {
                    $p5_DC_debitoOrCredito = '2';
                    $data = array(
                    'p2_DATA' => $p2_DATA, /*Fijo*/
                    'p5_DC' => $p5_DC_debitoOrCredito,
                    'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                    'p7_LINHA' => $a,
                    'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                    'p13_VALOR' => $iva_proporcional_pago,
                    'p14_PKO' => $p14_PKO_llave_referencia,
                    'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                    'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                    'p19_CTCD' => '',
                    'p20_CTAD' => $CuentaContableImpuestos_cobrado,
                    'p21_CCCD' => '',
                    'p22_CCAD' => '',
                    'p23_ITCD' => '',
                    'p24_ITAD' => '',
                    'p25_CLCD' => '',
                    'p26_CLAD' => '',
                    'p27_EMPORI' => $Nempresa,
                    'p31_RECNO' => $p31_RECNO_max
                    );
                     
                    ///SI RESULT === TRUE
                    $result_enviar = $this->crearRegistroPoliza($data, 'Pagos');
                    if ($result_enviar === False) {
                      error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                      //echo '<br/> FAIL: No inserte el registro poliza numero N4cobrado'. $a;
                      exit(1);
                    }else{
                      $a++;
                      $registros = $registros + 1;
                      error_log('SUCCESS: '.json_encode($data), 0);
                      //echo "<br/> SUCCESS: ".json_encode($data);
                    }
                  }
                }
              }
              
            }
          }
          if ($crear_asientos_excedente == 'Si') {
            ///// QUINTO ASIENTO 5/4 PAGO NORMAL
            $p31_RECNO_max = $this->consultarRECNOPolizas();
            if ($p31_RECNO_max != null) {
              if($Factura_tipo == 'Deposito en garantia'){
                $ZC1_CLCD = '';
              }else{
                $ZC1_CLCD = $ClaseValor;//clase de valor debito destino ZC1_CLCD
              }
              $p5_DC_debitoOrCredito = '2';
              $data = array(
              'p2_DATA' => $p2_DATA, /*Fijo*/
              'p5_DC' => $p5_DC_debitoOrCredito,
              'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
              'p7_LINHA' => $a,
              'p12_HIST' => substr($columna_historico_excedente, 0, 40),
              'p13_VALOR' => $monto_excedente,
              'p14_PKO' => $p14_PKO_llave_referencia,
              'p17_LOTE' => $p17_LOTE_tipo_comprobante,
              'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
              'p19_CTCD' => '',
              'p20_CTAD' => $otros_pasivos_cuenta,
              'p21_CCCD' => '',
              'p22_CCAD' => '',
              'p23_ITCD' => '',
              'p24_ITAD' => '',
              'p25_CLCD' => '',
              'p26_CLAD' => '',
              'p27_EMPORI' => $Nempresa,
              'p31_RECNO' => $p31_RECNO_max
              );
               
              ///SI RESULT === TRUE
              $result_enviar = $this->crearRegistroPoliza($data, 'Pagos');
              if ($result_enviar === False) {
                error_log('FAIL: No inserte el registro poliza numero 2'. $a, 0);
                //echo '<br/> FAIL: No inserte el registro poliza numero N2'. $a;
                exit(1);
              }else{
                $a++;
                $registros = $registros + 1;
                $updatePago = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
                ->where('id_FRM_CEA84379', $excedente_relacionado)
                ->update(['FFRMS_E56345B5' => 'Revisada', 'FFRMS_55F3FDFB' => $FechaActual, 'FFRMS_A6B27B41' => $p6_DOC]);
                error_log('SUCCESS: '.json_encode($data), 0);
              }
            }
          }
          
          $updatePago = DB::table('SI_FORMS_BAPP02F92DB8_FRM_D4E87BCD')
          ->where('id_FRM_D4E87BCD', $id_pago)
          ->update(['FFRMS_0999B7C0' => 4668]);
            
          
        }//If <=999
      }

      ////////////////////////////////  EXCEDENTES QUE NO ENCONTRARON FACTURA
      $fila = 0;
      $selectExcedentes = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
      ->join('SI_FORMS_BAPP02F92DB8_FRM_54CDED61','FFRMS_4BF4D64F','=','id_FRM_54CDED61')
      ->where('FFRMS_5D828359', $empresa_id)
      ->whereBetween('FFRMS_B3B3AF51', [$fecha_inicio, $fecha_fin])
      ->where('FFRMS_7D843481', 'Entrada')
      ->where('FFRMS_E56345B5', 'Revisada')
      ->where('FFRMS_435DD1E5', 'No')
      ->get();
      foreach ($selectExcedentes as $filaExcedentes) {
        /*Obtiene el folio del rubro padre*/
        $id_excedente = $filaExcedentes->id_FRM_CEA84379;
        $Arrendatario_id = $filaExcedentes->FFRMS_4BF4D64F;
        $array[$fila] = $Arrendatario_id;
        $fila++; 
      }

      if($a <= 999){
        $a++;
        if (!empty($array)){
          /// Cuenta elementos del array
          $cont=count($array);
          /// Recorre Array
           $folioAnterior = 0;
          for ($row=0;$row<$cont;$row++){
            $FolioPadre=$array[$row];
            if($folioAnterior != $FolioPadre){
              $SelectArrendatario = DB::table('SI_FORMS_BAPP02F92DB8_FRM_54CDED61')
              ->where('id_FRM_54CDED61', $FolioPadre)
              ->get();
              foreach ($SelectArrendatario as $filaArrendatario) {
                $validador = 0;
                $excedentes = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
                ->where('FFRMS_4BF4D64F', $FolioPadre)
                ->where('FFRMS_7D843481', 'Entrada')
                ->where('FFRMS_E56345B5', 'Revisada')
                ->where('FFRMS_5D828359', $empresa_id)
                ->whereBetween('FFRMS_B3B3AF51', [$fecha_inicio, $fecha_fin])
                ->get();
                foreach ($excedentes as $filaExcedente) {
                  $id_excedente = $filaExcedente->id_FRM_CEA84379;
                  $TotalExcedente = $filaExcedente->FFRMS_250EB570;
                  $cuenta_bancaria_id = $filaExcedente->FFRMS_86C556C0;
                  $Arrendatario_id = $filaExcedente->FFRMS_4BF4D64F;
                  $FechaFs = $filaExcedente->FFRMS_B3B3AF51;
                  $FechaF=date('Ymd',strtotime($FechaFs));
                  
                  $sucursal_predefinida_id = $filaArrendatario->FFRMS_42DC00D1;
                  $Arrendatario = $filaArrendatario->FFRMS_5FEEA39C;
                  $validador ++;
                  $tipoComprobante = '1';

                  $CuentaContabelB = null;
                  if($cuenta_bancaria_id != null or $cuenta_bancaria_id != ''){
                    $SelectCuentaBancaria = DB::table('SI_FORMS_BAPP02F92DB8_FRM_FA24357A')
                    ->where('id_FRM_FA24357A', $cuenta_bancaria_id)
                    ->first();
                    if ($SelectCuentaBancaria) {
                      $CuentaContabelB = $SelectCuentaBancaria->FFRMS_989765FB;
                    }
                  }

                  $SelectSucursal = DB::table('SI_FORMS_BAPP02F92DB8_FRM_045A4975')
                  ->where('id_FRM_045A4975', $sucursal_predefinida_id)->first();
                  if ($SelectSucursal) {
                    $ClaseValor_arrendatario = $SelectSucursal->FFRMS_CECFEB82;
                    $Ncliente = $SelectSucursal->FFRMS_0B6479A0;
                    $Nempresa = $SelectSucursal->FFRMS_E78CC8FF;
                  }

                  $SelectEmpresa = DB::table('SI_FORMS_BAPP02F92DB8_FRM_92604F36')
                  ->where('id_FRM_92604F36', $empresa_id)
                  ->first();
                  if ($SelectEmpresa) {
                    $Empresa_text = $SelectEmpresa->FFRMS_40F34F0E;
                    $ClaseValor_empresa = $SelectEmpresa->FFRMS_353D60E2;
                    $NEmpresa = $SelectEmpresa->FFRMS_7F569111;
                    $cuentaBancaria_empresa = $SelectEmpresa->FFRMS_F71C342B;
                  }
                  $p12_HIST_concatenado = 'EX '.$id_excedente.' CTE '.$Ncliente.' '.$Arrendatario;
                  if($tipoComprobante == 1){
                    $p17_LOTE_tipo_comprobante = 3;//tipo de poliza Ingresos
                  }
                  elseif($tipoComprobante == 2){
                    $p17_LOTE_tipo_comprobante = 2;//tipo de poliza Egreso
                  }
                  elseif ($tipoComprobante == 6) {
                    $p17_LOTE_tipo_comprobante = '1';//tipo de poliza Diarios
                  }
                  $p14_PKO_llave_referencia = $NEmpresa.$FechaF.$Ncliente;//Llave de referencia para identificar el movimiento ZC1_PKO
                  ///// PRIMER ASIENTO 1/2 
                  $p31_RECNO_max = $this->consultarRECNOPolizas();
                  if ($p31_RECNO_max != null) {
                    $p5_DC_debitoOrCredito = '1';
                    $data = array(
                    'p2_DATA' => $p2_DATA, /*Fijo*/
                    'p5_DC' => $p5_DC_debitoOrCredito,
                    'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                    'p7_LINHA' => $a,
                    'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                    'p13_VALOR' => $TotalExcedente,
                    'p14_PKO' => $p14_PKO_llave_referencia,
                    'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                    'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                    'p19_CTCD' => $CuentaContabelB,
                    'p20_CTAD' => '',
                    'p21_CCCD' => '',
                    'p22_CCAD' => '',
                    'p23_ITCD' => '',
                    'p24_ITAD' => '',
                    'p25_CLCD' => '',
                    'p26_CLAD' => '',
                    'p27_EMPORI' => $NEmpresa,
                    'p31_RECNO' => $p31_RECNO_max
                    );
                     
                    ///SI RESULT === TRUE
                    $result_enviar = $this->crearRegistroPoliza($data, 'Excedentes sin aplicar');
                    if ($result_enviar === False) {
                      error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                      //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                      exit(1);
                    }else{
                      $a++;
                      $registros = $registros + 1;
                      error_log('SUCCESS: '.json_encode($data), 0);
                      //echo "<br/> SUCCESS: ".json_encode($data);
                    }
                  }

                  ///// PRIMER ASIENTO 2/2 
                  $p31_RECNO_max = $this->consultarRECNOPolizas();
                  if ($p31_RECNO_max != null) {
                    $p5_DC_debitoOrCredito = '2';
                    $data = array(
                    'p2_DATA' => $p2_DATA, /*Fijo*/
                    'p5_DC' => $p5_DC_debitoOrCredito,
                    'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                    'p7_LINHA' => $a,
                    'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                    'p13_VALOR' => $TotalExcedente,
                    'p14_PKO' => $p14_PKO_llave_referencia,
                    'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                    'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                    'p19_CTCD' => '',
                    'p20_CTAD' => $otros_pasivos_cuenta,
                    'p21_CCCD' => '',
                    'p22_CCAD' => '',
                    'p23_ITCD' => '',
                    'p24_ITAD' => '',
                    'p25_CLCD' => '',
                    'p26_CLAD' => '',
                    'p27_EMPORI' => $NEmpresa,
                    'p31_RECNO' => $p31_RECNO_max
                    );
                     
                    ///SI RESULT === TRUE
                    $result_enviar = $this->crearRegistroPoliza($data, 'Excedentes sin aplicar');
                    if ($result_enviar === False) {
                      error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                      //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                      exit(1);
                    }else{
                      $a++;
                      $registros = $registros + 1;
                      error_log('SUCCESS: '.json_encode($data), 0);
                      //echo "<br/> SUCCESS: ".json_encode($data);
                    }
                  }
                  $updateExcedente = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
                  ->where('id_FRM_CEA84379', $id_excedente)
                  ->update(['FFRMS_E56345B5' => 'Contabilizada']);
                }
              }
            }
          }
        }
      }

      return $registros;
    }
    public function enviarExcedentesAplicados($fecha_inicio, $fecha_fin, $empresa_id, $Dep_garantía_cobrar_cliente, $Dep_garantía_clientes, $Dep_garantía_nombre,  $otros_pasivos_cuenta , $doc_num, $FechaActual )
    {
      
      $a = 0;
      $c = 1;
      $foliof= null;
      $Ncliente= null;
      $Arrendatario= null;
      $NumeroEmpresa = null;
      $Nempresa = null;
      $CuentaContable = ''; 
      $CuentaContableA = ''; 
      $contrato_id = '';
      $ItemContable = '';
      $NumeroEmpresa = '';
      $NombreEmpresa = '';
      $CentroCosto = '';
      $codigo_unidad = '';
      $registros = 0;
      $EmpresaPoliza = $empresa_id;
      //Variables DATA
      $p2_DATA = date('Ymd',strtotime($FechaActual));
      $p5_DC = '';
      $p6_DOC = $doc_num;
      $p7_LINHA = '';
      $p12_HIST = '';
      $p13_VALOR = '';
      $p14_PKO = '';
      $p17_LOTE = '';
      $p18_SBLOTE = 'REN';
      $p19_CTCD = '';
      $p20_CTAD = '';
      $p21_CCCD = '';
      $p22_CCAD = '';
      $p23_ITCD = '';
      $p24_ITAD = '';
      $p25_CLCD = '';
      $p26_CLAD = '';
      $p27_EMPORI = '';
      $p31_RECNO = '';

      $fila = 0;
      $selectExcedentes = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
      ->join('SI_FORMS_BAPP02F92DB8_FRM_7D9635DF','FFRMS_80C177D3','=','id_FRM_7D9635DF','left', false)
      ->where('FFRMS_DC110457', $empresa_id)
      ->where('FFRMS_7D843481','Salida')
      ->whereBetween('FFRMS_B3B3AF51',[$fecha_inicio, $fecha_fin])
      ->where('FFRMS_E56345B5', 'Revisada')
      ->get();
      foreach ($selectExcedentes as $filaExcedentes) {
        /*Obtiene el folio del rubro padre*/
        $id_excedente = $filaExcedentes->id_FRM_CEA84379;
        $contrato_id = $filaExcedentes->FFRMS_4BF4D64F;
        $array[$fila] = $contrato_id;
        $fila++; 
      }//while Pagos
      if($a <= 999){
        $a++;
        if (!empty($array)){

          /// Cuenta elementos del array
          $cont=count($array);

          /// Recorre Array
           $folioAnterior = 0;
          for ($row=0;$row<$cont;$row++){
            $FolioPadre=$array[$row];
            if($folioAnterior != $FolioPadre){
              $SelectArrendatario = DB::table('SI_FORMS_BAPP02F92DB8_FRM_54CDED61')
              ->where('id_FRM_54CDED61', $FolioPadre )
              ->get();
              foreach ($SelectArrendatario as $filaArrendatario) {
                $validador = 0;
                $excedentes = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
                ->where('FFRMS_4BF4D64F', $FolioPadre)
                ->where('FFRMS_7D843481', 'Salida')
                ->where('FFRMS_E56345B5','Revisada')
                ->get();

                foreach ($excedentes as $filaExcedente) {
                  $id_excedente = $filaExcedente->id_FRM_CEA84379;
                  $TotalExcedente = $filaExcedente->FFRMS_250EB570 *-1;
                  $cuenta_bancaria_id = $filaExcedente->FFRMS_86C556C0;
                  $factura_id = $filaExcedente->FFRMS_44BD172E;
                  //$folioCto = $filaContratos->FFRMS_2FD38D2C;
                  $tipoComprobante = '1';

                  $filaFactura = DB::table('SI_FORMS_BAPP02F92DB8_FRM_0A48F90C')
                  ->where('id_FRM_0A48F90C', $factura_id)
                  ->first();

                  if ($filaFactura) {
                    $subtotalFactura = $filaFactura->FFRMS_D6356B12;
                    $totalFactura = $filaFactura->FFRMS_00B26ED7;
                    $iva_factura = $filaFactura->FFRMS_8EB4E52A;
                    $contrato_id = $filaFactura->FFRMS_2A2EAA0F;
                    $Arrendatario_id = $filaFactura->FFRMS_C54453B0;
                    $UnidadF = $filaFactura->FFRMS_B497EAD9;
                    $moneda = $filaFactura->FFRMS_E90D619F;
                    $EmpresaPoliza = $filaFactura->FFRMS_8F2D2180;
                    $Factura_tipo = $filaFactura->FFRMS_90A4BCE5;
                    $folio_timbre = $filaFactura->FFRMS_F6E17AB2;
                    $sucursal_predefinida_id = $filaFactura->FFRMS_6AC356E0;
                    $domicilio_predefinida_id = $filaFactura->FFRMS_EC831C2A;
                    $FechaFs = $filaFactura->FFRMS_E73C86DE;
                    $FechaF=date('Ymd',strtotime($FechaFs));
                    if ($iva_factura > 0 ) {
                      $crear_asientos_iva = 'Si';
                    }else{
                      $crear_asientos_iva = "No";
                    }
                    $filaContratos = DB::table('SI_FORMS_BAPP02F92DB8_FRM_0A48F90C')
                    ->where('id_FRM_0A48F90C', $factura_id)
                    ->first();
                    $folioCto = '';
                    if ($filaContratos) {
                      $folioCto = $filaContratos->FFRMS_2FD38D2C;
                    }
                  }

                  $CuentaContable ='';
                  $filaSucursal = DB::table('SI_FORMS_BAPP02F92DB8_FRM_045A4975')
                  ->where('id_FRM_045A4975', $sucursal_predefinida_id)
                  ->first();
                  if ($filaSucursal) {
                    $CuentaContableA = $filaSucursal->FFRMS_25103FA5;
                    $ClaseValor_arrendatario = $filaSucursal->FFRMS_CECFEB82;
                    $Ncliente = $filaSucursal->FFRMS_0B6479A0;
                    $Arrendatario = $filaSucursal->FFRMS_32B278A2;
                    if ($CuentaContableA != null) {
                      $filaCuenta = DB::table('SI_FORMS_BAPP02F92DB8_FRM_229230AF')
                      ->where('id_FRM_229230AF', $CuentaContableA)
                      ->first();
                      if ($filaCuenta) {
                        $CuentaContable = $filaCuenta->FFRMS_87A95699;
                      }
                    } 
                  }

                  $validador ++;
                  $CuentaContabelB = null;
                  if($cuenta_bancaria_id != null or $cuenta_bancaria_id != ''){
                    $SelectCuentaBancaria = DB::table('SI_FORMS_BAPP02F92DB8_FRM_FA24357A')
                    ->where('id_FRM_FA24357A', $cuenta_bancaria_id)
                    ->first();
                    if ($SelectCuentaBancaria) {
                      $CuentaContabelB = $SelectCuentaBancaria->FFRMS_989765FB;
                    }
                  }

                  $SelectEmpresa = DB::table('SI_FORMS_BAPP02F92DB8_FRM_92604F36')
                  ->where('id_FRM_92604F36', $empresa_id)
                  ->first();
                  if ($SelectEmpresa) {
                    $Empresa_text = $SelectEmpresa->FFRMS_40F34F0E;
                    $ClaseValor_empresa = $SelectEmpresa->FFRMS_353D60E2;
                    $NEmpresa = $SelectEmpresa->FFRMS_7F569111;
                    $cuentaBancaria_empresa = $SelectEmpresa->FFRMS_F71C342B;
                    $p14_PKO_llave_referencia = $Nempresa.$FechaF.$Ncliente;//Llave de referencia para identificar el movimiento ZC1_PKO
                  }
                  if ($Factura_tipo == 'Deposito en garantia') {
                    $ClaseValor_arrendatario = '';
                    $p12_HIST_concatenado = 'EX '.$id_excedente.' CTO '.$folioCto.' DEP-'.$folio_timbre.' CTE '.$Ncliente.' '.$Arrendatario;
                  }else{
                    $p12_HIST_concatenado = 'EX '.$id_excedente.' CTO '.$folioCto.' FAC-'.$folio_timbre.' CTE '.$Ncliente.' '.$Arrendatario;
                  }

                  if($tipoComprobante == 1){
                    $p17_LOTE_tipo_comprobante = 3;//tipo de poliza Ingresos
                  }
                  elseif($tipoComprobante == 2){
                    $p17_LOTE_tipo_comprobante = 2;//tipo de poliza Egreso
                  }
                  elseif ($tipoComprobante == 6) {
                    $p17_LOTE_tipo_comprobante = '1';//tipo de poliza Diarios
                  }
                  if ($ClaseValor_arrendatario == null) {
                    $ClaseValor_arrendatario = '';
                  }

                  ///// PRIMER ASIENTO 1/4 
                  $p31_RECNO_max = $this->consultarRECNOPolizas();
                  if ($p31_RECNO_max != null) {
                    $p5_DC_debitoOrCredito = '1';
                    $data = array(
                    'p2_DATA' => $p2_DATA, /*Fijo*/
                    'p5_DC' => $p5_DC_debitoOrCredito,
                    'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                    'p7_LINHA' => $a,
                    'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                    'p13_VALOR' => $TotalExcedente,
                    'p14_PKO' => $p14_PKO_llave_referencia,
                    'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                    'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                    'p19_CTCD' => $otros_pasivos_cuenta,
                    'p20_CTAD' => '',
                    'p21_CCCD' => '',
                    'p22_CCAD' => '',
                    'p23_ITCD' => '',
                    'p24_ITAD' => '',
                    'p25_CLCD' => '',
                    'p26_CLAD' => '',
                    'p27_EMPORI' => $NEmpresa,
                    'p31_RECNO' => $p31_RECNO_max
                    );
                     
                    ///SI RESULT === TRUE
                    $result_enviar = $this->crearRegistroPoliza($data, 'Excedentes aplicados');
                    if ($result_enviar === False) {
                      error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                      //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                      exit(1);
                    }else{
                      $a++;
                      $registros = $registros + 1;
                      error_log('SUCCESS: '.json_encode($data), 0);
                      //echo "<br/> SUCCESS: ".json_encode($data);
                    }
                  }
                  
                  //------APARTI DE AQUI PUDE SER FACTURA NORMAL O DEPOSITO EN GARANTIA------------
                  if ($Factura_tipo == 'Deposito en garantia') {
                    ///// SEGUNDO ASIENTO 2/4 DEPOSITO GARANTIA
                    $p31_RECNO_max = $this->consultarRECNOPolizas();
                    if ($p31_RECNO_max != null) {
                      $p5_DC_debitoOrCredito = '1';
                      $data = array(
                      'p2_DATA' => $p2_DATA, /*Fijo*/
                      'p5_DC' => $p5_DC_debitoOrCredito,
                      'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                      'p7_LINHA' => $a,
                      'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                      'p13_VALOR' => $TotalExcedente,
                      'p14_PKO' => $p14_PKO_llave_referencia,
                      'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                      'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                      'p19_CTCD' => $Dep_garantía_cobrar_cliente,
                      'p20_CTAD' => '',
                      'p21_CCCD' => '',
                      'p22_CCAD' => '',
                      'p23_ITCD' => '',
                      'p24_ITAD' => '',
                      'p25_CLCD' => '',
                      'p26_CLAD' => '',
                      'p27_EMPORI' => $NEmpresa,
                      'p31_RECNO' => $p31_RECNO_max
                      );
                       
                      ///SI RESULT === TRUE
                      $result_enviar = $this->crearRegistroPoliza($data, 'Excedentes aplicados depositos');
                      if ($result_enviar === False) {
                        //error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                        //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                        exit(1);
                      }else{
                        $a++;
                        $registros = $registros + 1;
                        //error_log('SUCCESS: '.json_encode($data), 0);
                        //echo "<br/> SUCCESS: ".json_encode($data);
                      }
                    }
                    ///// TERCER ASIENTO 3/4 DEPOSITO GARANTIA
                    $p31_RECNO_max = $this->consultarRECNOPolizas();
                    if ($p31_RECNO_max != null) {
                      $p5_DC_debitoOrCredito = '2';
                      
                      $data = array(
                      'p2_DATA' => $p2_DATA, /*Fijo*/
                      'p5_DC' => $p5_DC_debitoOrCredito,
                      'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                      'p7_LINHA' => $a,
                      'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                      'p13_VALOR' => $totalFactura,
                      'p14_PKO' => $p14_PKO_llave_referencia,
                      'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                      'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                      'p19_CTCD' => '',
                      'p20_CTAD' => $Dep_garantía_clientes,
                      'p21_CCCD' => '',
                      'p22_CCAD' => '',
                      'p23_ITCD' => '',
                      'p24_ITAD' => '',
                      'p25_CLCD' => '',
                      'p26_CLAD' => '',
                      'p27_EMPORI' => $NEmpresa,
                      'p31_RECNO' => $p31_RECNO_max
                      );
                       
                      ///SI RESULT === TRUE
                      $result_enviar = $this->crearRegistroPoliza($data, 'Excedentes aplicados depositos');
                      if ($result_enviar === False) {
                        error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                        //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                        exit(1);
                      }else{
                        $a++;
                        $registros = $registros + 1;
                        error_log('SUCCESS: '.json_encode($data), 0);
                        //echo "<br/> SUCCESS: ".json_encode($data);
                      }
                    }
                    ///// CUARTO ASIENTO 4/4 DEPOSITO GARANTIA
                    $p31_RECNO_max = $this->consultarRECNOPolizas();
                    if ($p31_RECNO_max != null) {
                      $p5_DC_debitoOrCredito = '2';
                      
                      $data = array(
                      'p2_DATA' => $p2_DATA, /*Fijo*/
                      'p5_DC' => $p5_DC_debitoOrCredito,
                      'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                      'p7_LINHA' => $a,
                      'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                      'p13_VALOR' => $totalFactura,
                      'p14_PKO' => $p14_PKO_llave_referencia,
                      'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                      'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                      'p19_CTCD' => '',
                      'p20_CTAD' => $Dep_garantía_nombre,
                      'p21_CCCD' => '',
                      'p22_CCAD' => '',
                      'p23_ITCD' => '',
                      'p24_ITAD' => '',
                      'p25_CLCD' => '',
                      'p26_CLAD' => '',
                      'p27_EMPORI' => $NEmpresa,
                      'p31_RECNO' => $p31_RECNO_max
                      );
                       
                      ///SI RESULT === TRUE
                      $result_enviar = $this->crearRegistroPoliza($data, 'Excedentes aplicados depositos');
                      if ($result_enviar === False) {
                        error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                        //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                        exit(1);
                      }else{
                        $a++;
                        $registros = $registros + 1;
                        error_log('SUCCESS: '.json_encode($data), 0);
                        //echo "<br/> SUCCESS: ".json_encode($data);
                      }
                    }

                  }else{///Factua normal
                    ///// SEGUNDO ASIENTO 2/4 FACTURA NORMAL
                    $p31_RECNO_max = $this->consultarRECNOPolizas();
                    if ($p31_RECNO_max != null) {
                      $p5_DC_debitoOrCredito = '2';
                      $data = array(
                      'p2_DATA' => $p2_DATA, /*Fijo*/
                      'p5_DC' => $p5_DC_debitoOrCredito,
                      'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                      'p7_LINHA' => $a,
                      'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                      'p13_VALOR' => $TotalExcedente,
                      'p14_PKO' => $p14_PKO_llave_referencia,
                      'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                      'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                      'p19_CTCD' => '',
                      'p20_CTAD' => $CuentaContable,
                      'p21_CCCD' => '',
                      'p22_CCAD' => '',
                      'p23_ITCD' => '',
                      'p24_ITAD' => '',
                      'p25_CLCD' => '',
                      'p26_CLAD' => $ClaseValor_arrendatario,
                      'p27_EMPORI' => $NEmpresa,
                      'p31_RECNO' => $p31_RECNO_max
                      );
                       
                      ///SI RESULT === TRUE
                      $result_enviar = $this->crearRegistroPoliza($data, 'Excedentes aplicados');
                      if ($result_enviar === False) {
                        error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                        //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                        exit(1);
                      }else{
                        $a++;
                        $registros = $registros + 1;
                        error_log('SUCCESS: '.json_encode($data), 0);
                        //echo "<br/> SUCCESS: ".json_encode($data);
                      }
                    }

                    ///// IVAS
                    if ($crear_asientos_iva == 'Si') {
                      ///SUMAR LOS IMPUESTOS POR PARTIDA
                      $selectPartidasImpuestos = DB::table('SI_FORMS_BAPP02F92DB8_FRM_8276943C')
                      ->select('FFRMS_564F3B89', 'id_FRM_8276943C', 'FFRMS_89817A23 as tasaIva', 'FFRMS_C40DAAFD as tasaRetIva', 'FFRMS_3ACD6BAA as tasaIsr', 'FFRMS_61C54875 as tasaIeps', DB::raw('SUM(FFRMS_7C9A07A7) as sumaIVA') )
                      ->where('FFRMS_1D8401A5', $factura_id)
                      ->groupBy('FFRMS_564F3B89')
                      ->get();
                      foreach ($selectPartidasImpuestos as $filapartidasImpuestos) {
                        $impuesto = $filapartidasImpuestos->FFRMS_564F3B89;
                        $totalIVA = $filapartidasImpuestos->sumaIVA;
                        $tasaIva = $filapartidasImpuestos->tasaIva;
                        if ($tasaIva > 0 ) {
                          $subtotal_partida_temp = round($TotalExcedente / ($tasaIva + 1), 2);
                          $iva_proporcional_pago = round($subtotal_partida_temp * $tasaIva , 2);
                        }else{
                          $iva_proporcional_pago = 0;
                        }
                        $CuentaContableImpuestos = null;
                        $SelecImpuesto= DB::table('SI_FORMS_BAPP02F92DB8_FRM_B11C5D02')
                        ->where('id_FRM_B11C5D02', $impuesto)
                        ->get();
                        foreach ($SelecImpuesto as $filasImpuestos) {
                          $CuentaContableImpuestos = $filasImpuestos->FFRMS_B28B7E32;

                          $SelectCuentaContable = DB::table('SI_FORMS_BAPP02F92DB8_FRM_229230AF')
                          ->where('id_FRM_229230AF', $CuentaContableImpuestos)
                          ->first();
                          if ($SelectCuentaContable) {
                            $CuentaContableImpuestos = $SelectCuentaContable->FFRMS_87A95699;
                          }

                          $CuentaContableImpuestos_cobrado = $filasImpuestos->FFRMS_80DCA203;
                          $SelectCuentaContable_cobrado = DB::table('SI_FORMS_BAPP02F92DB8_FRM_229230AF')
                          ->where('id_FRM_229230AF', $CuentaContableImpuestos_cobrado)
                          ->first();
                          if ($SelectCuentaContable_cobrado) {
                            $CuentaContableImpuestos_cobrado = $SelectCuentaContable_cobrado->FFRMS_87A95699;
                          }
                        }
                        ///// TERCER ASIENTO 3/4 FACTURA NORMAL
                        $p31_RECNO_max = $this->consultarRECNOPolizas();
                        if ($p31_RECNO_max != null) {
                          $p5_DC_debitoOrCredito = '1';
                          $data = array(
                          'p2_DATA' => $p2_DATA, /*Fijo*/
                          'p5_DC' => $p5_DC_debitoOrCredito,
                          'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                          'p7_LINHA' => $a,
                          'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                          'p13_VALOR' => $iva_proporcional_pago,
                          'p14_PKO' => $p14_PKO_llave_referencia,
                          'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                          'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                          'p19_CTCD' => $CuentaContableImpuestos,
                          'p20_CTAD' => '',
                          'p21_CCCD' => '',
                          'p22_CCAD' => '',
                          'p23_ITCD' => '',
                          'p24_ITAD' => '',
                          'p25_CLCD' => '',
                          'p26_CLAD' => '',
                          'p27_EMPORI' => $NEmpresa,
                          'p31_RECNO' => $p31_RECNO_max
                          );
                           
                          ///SI RESULT === TRUE
                          $result_enviar = $this->crearRegistroPoliza($data, 'Excedentes aplicados');
                          if ($result_enviar === False) {
                            error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                            //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                            exit(1);
                          }else{
                            $a++;
                            $registros = $registros + 1;
                            error_log('SUCCESS: '.json_encode($data), 0);
                            //echo "<br/> SUCCESS: ".json_encode($data);
                          }
                        }
                        ///// CUARTO ASIENTO 4/4 FACTURA NORMAL
                        // ---------------------------------Poliza COBRADO--------------------------+
                        $p31_RECNO_max = $this->consultarRECNOPolizas();
                        if ($p31_RECNO_max != null) {
                          $p5_DC_debitoOrCredito = '2';
                          $data = array(
                          'p2_DATA' => $p2_DATA, /*Fijo*/
                          'p5_DC' => $p5_DC_debitoOrCredito,
                          'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                          'p7_LINHA' => $a,
                          'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                          'p13_VALOR' => $iva_proporcional_pago,
                          'p14_PKO' => $p14_PKO_llave_referencia,
                          'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                          'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                          'p19_CTCD' => '',
                          'p20_CTAD' => $CuentaContableImpuestos_cobrado,
                          'p21_CCCD' => '',
                          'p22_CCAD' => '',
                          'p23_ITCD' => '',
                          'p24_ITAD' => '',
                          'p25_CLCD' => '',
                          'p26_CLAD' => '',
                          'p27_EMPORI' => $NEmpresa,
                          'p31_RECNO' => $p31_RECNO_max
                          );
                           
                          ///SI RESULT === TRUE
                          $result_enviar = $this->crearRegistroPoliza($data, 'Excedentes aplicados');
                          if ($result_enviar === False) {
                            error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                            //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                            exit(1);
                          }else{
                            $a++;
                            $registros = $registros + 1;
                            error_log('SUCCESS: '.json_encode($data), 0);
                            //echo "<br/> SUCCESS: ".json_encode($data);
                          }
                        }
                      }
                    }
                  }
                  $c++;

                  $validador = 1;
                  $updateExcedente = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
                  ->where('id_FRM_CEA84379', $id_excedente)
                  ->update(['FFRMS_E56345B5' => 'Contabilizada' ]);
                }
              }
            }//if validar
            $folioAnterior = $FolioPadre;
          }//For
        }//If
        
      }//If <=999
      return $registros;
    }
    public function enviarExcedentesSinAplicar($fecha_inicio, $fecha_fin, $empresa_id, $otros_pasivos_cuenta, $doc_num, $FechaActual )
    {
      
      $a = 0;
      $c = 1;
      $foliof= null;
      $Ncliente= null;
      $Arrendatario= null;
      $NumeroEmpresa = null;
      $Nempresa = null;
      $CuentaContable = ''; 
      $CuentaContableA = ''; 
      $contrato_id = '';
      $ItemContable = '';
      $NumeroEmpresa = '';
      $NombreEmpresa = '';
      $CentroCosto = '';
      $codigo_unidad = '';
      $registros = 0;
      $EmpresaPoliza = $empresa_id;
      //Variables DATA
      $p2_DATA = date('Ymd',strtotime($FechaActual));
      $p5_DC = '';
      $p6_DOC = $doc_num;
      $p7_LINHA = '';
      $p12_HIST = '';
      $p13_VALOR = '';
      $p14_PKO = '';
      $p17_LOTE = '';
      $p18_SBLOTE = 'REN';
      $p19_CTCD = '';
      $p20_CTAD = '';
      $p21_CCCD = '';
      $p22_CCAD = '';
      $p23_ITCD = '';
      $p24_ITAD = '';
      $p25_CLCD = '';
      $p26_CLAD = '';
      $p27_EMPORI = '';
      $p31_RECNO = '';

      $fila = 0;
      $selectExcedentes = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
      ->join('SI_FORMS_BAPP02F92DB8_FRM_54CDED61','FFRMS_4BF4D64F','=','id_FRM_54CDED61')
      ->where('FFRMS_5D828359', $empresa_id)
      ->whereBetween('FFRMS_B3B3AF51', [$fecha_inicio, $fecha_fin])
      ->where('FFRMS_7D843481', 'Entrada')
      ->where('FFRMS_E56345B5', 'Revisada')
      ->get();
      foreach ($selectExcedentes as $filaExcedentes) {
        /*Obtiene el folio del rubro padre*/
        $id_excedente = $filaExcedentes->id_FRM_CEA84379;
        $Arrendatario_id = $filaExcedentes->FFRMS_4BF4D64F;
        $array[$fila] = $Arrendatario_id;
        $fila++; 
      }

      if($a <= 999){
        $a++;
        if (!empty($array)){
          /// Cuenta elementos del array
          $cont=count($array);
          /// Recorre Array
           $folioAnterior = 0;
          for ($row=0;$row<$cont;$row++){
            $FolioPadre=$array[$row];
            if($folioAnterior != $FolioPadre){
              $SelectArrendatario = DB::table('SI_FORMS_BAPP02F92DB8_FRM_54CDED61')
              ->where('id_FRM_54CDED61', $FolioPadre)
              ->get();
              foreach ($SelectArrendatario as $filaArrendatario) {
                $validador = 0;
                $excedentes = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
                ->where('FFRMS_4BF4D64F', $FolioPadre)
                ->where('FFRMS_7D843481', 'Entrada')
                ->where('FFRMS_E56345B5', 'Revisada')
                ->where('FFRMS_5D828359', $empresa_id)
                ->whereBetween('FFRMS_B3B3AF51', [$fecha_inicio, $fecha_fin])
                ->get();
                foreach ($excedentes as $filaExcedente) {
                  $id_excedente = $filaExcedente->id_FRM_CEA84379;
                  $TotalExcedente = $filaExcedente->FFRMS_250EB570;
                  $cuenta_bancaria_id = $filaExcedente->FFRMS_86C556C0;
                  $Arrendatario_id = $filaExcedente->FFRMS_4BF4D64F;
                  $FechaFs = $filaExcedente->FFRMS_B3B3AF51;
                  $FechaF=date('Ymd',strtotime($FechaFs));
                  
                  $sucursal_predefinida_id = $filaArrendatario->FFRMS_42DC00D1;
                  $Arrendatario = $filaArrendatario->FFRMS_5FEEA39C;
                  $validador ++;
                  $tipoComprobante = '1';

                  $CuentaContabelB = null;
                  if($cuenta_bancaria_id != null or $cuenta_bancaria_id != ''){
                    $SelectCuentaBancaria = DB::table('SI_FORMS_BAPP02F92DB8_FRM_FA24357A')
                    ->where('id_FRM_FA24357A', $cuenta_bancaria_id)
                    ->first();
                    if ($SelectCuentaBancaria) {
                      $CuentaContabelB = $SelectCuentaBancaria->FFRMS_989765FB;
                    }
                  }

                  $SelectSucursal = DB::table('SI_FORMS_BAPP02F92DB8_FRM_045A4975')
                  ->where('id_FRM_045A4975', $sucursal_predefinida_id)->first();
                  if ($SelectSucursal) {
                    $ClaseValor_arrendatario = $SelectSucursal->FFRMS_CECFEB82;
                    $Ncliente = $SelectSucursal->FFRMS_0B6479A0;
                    $Nempresa = $SelectSucursal->FFRMS_E78CC8FF;
                  }

                  $SelectEmpresa = DB::table('SI_FORMS_BAPP02F92DB8_FRM_92604F36')
                  ->where('id_FRM_92604F36', $empresa_id)
                  ->first();
                  if ($SelectEmpresa) {
                    $Empresa_text = $SelectEmpresa->FFRMS_40F34F0E;
                    $ClaseValor_empresa = $SelectEmpresa->FFRMS_353D60E2;
                    $NEmpresa = $SelectEmpresa->FFRMS_7F569111;
                    $cuentaBancaria_empresa = $SelectEmpresa->FFRMS_F71C342B;
                  }
                  $p12_HIST_concatenado = 'EX '.$id_excedente.' CTE '.$Ncliente.' '.$Arrendatario;
                  if($tipoComprobante == 1){
                    $p17_LOTE_tipo_comprobante = 3;//tipo de poliza Ingresos
                  }
                  elseif($tipoComprobante == 2){
                    $p17_LOTE_tipo_comprobante = 2;//tipo de poliza Egreso
                  }
                  elseif ($tipoComprobante == 6) {
                    $p17_LOTE_tipo_comprobante = '1';//tipo de poliza Diarios
                  }
                  $p14_PKO_llave_referencia = $NEmpresa.$FechaF.$Ncliente;//Llave de referencia para identificar el movimiento ZC1_PKO
                  ///// PRIMER ASIENTO 1/2 
                  $p31_RECNO_max = $this->consultarRECNOPolizas();
                  if ($p31_RECNO_max != null) {
                    $p5_DC_debitoOrCredito = '1';
                    $data = array(
                    'p2_DATA' => $p2_DATA, /*Fijo*/
                    'p5_DC' => $p5_DC_debitoOrCredito,
                    'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                    'p7_LINHA' => $a,
                    'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                    'p13_VALOR' => $TotalExcedente,
                    'p14_PKO' => $p14_PKO_llave_referencia,
                    'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                    'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                    'p19_CTCD' => $CuentaContabelB,
                    'p20_CTAD' => '',
                    'p21_CCCD' => '',
                    'p22_CCAD' => '',
                    'p23_ITCD' => '',
                    'p24_ITAD' => '',
                    'p25_CLCD' => '',
                    'p26_CLAD' => '',
                    'p27_EMPORI' => $NEmpresa,
                    'p31_RECNO' => $p31_RECNO_max
                    );
                     
                    ///SI RESULT === TRUE
                    $result_enviar = $this->crearRegistroPoliza($data, 'Excedentes sin aplicar');
                    if ($result_enviar === False) {
                      error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                      //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                      exit(1);
                    }else{
                      $a++;
                      $registros = $registros + 1;
                      error_log('SUCCESS: '.json_encode($data), 0);
                      //echo "<br/> SUCCESS: ".json_encode($data);
                    }
                  }

                  ///// PRIMER ASIENTO 2/2 
                  $p31_RECNO_max = $this->consultarRECNOPolizas();
                  if ($p31_RECNO_max != null) {
                    $p5_DC_debitoOrCredito = '2';
                    $data = array(
                    'p2_DATA' => $p2_DATA, /*Fijo*/
                    'p5_DC' => $p5_DC_debitoOrCredito,
                    'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                    'p7_LINHA' => $a,
                    'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                    'p13_VALOR' => $TotalExcedente,
                    'p14_PKO' => $p14_PKO_llave_referencia,
                    'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                    'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                    'p19_CTCD' => '',
                    'p20_CTAD' => $otros_pasivos_cuenta,
                    'p21_CCCD' => '',
                    'p22_CCAD' => '',
                    'p23_ITCD' => '',
                    'p24_ITAD' => '',
                    'p25_CLCD' => '',
                    'p26_CLAD' => '',
                    'p27_EMPORI' => $NEmpresa,
                    'p31_RECNO' => $p31_RECNO_max
                    );
                     
                    ///SI RESULT === TRUE
                    $result_enviar = $this->crearRegistroPoliza($data, 'Excedentes sin aplicar');
                    if ($result_enviar === False) {
                      error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                      //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                      exit(1);
                    }else{
                      $a++;
                      $registros = $registros + 1;
                      error_log('SUCCESS: '.json_encode($data), 0);
                      //echo "<br/> SUCCESS: ".json_encode($data);
                    }
                  }
                  $updateExcedente = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
                  ->where('id_FRM_CEA84379', $id_excedente)
                  ->update(['FFRMS_E56345B5' => 'Contabilizada']);
                }
              }
            }
          }
        }
      }
      return $registros;
    }
    public function enviarDepositosGarantia($fecha_inicio, $fecha_fin, $empresa_id, $Dep_garantía_clientes, $Dep_garantía_cobrar_cliente, $doc_num, $FechaActual )
    {
      
      $a = 0;
      $c = 1;
      $foliof= null;
      $Ncliente= null;
      $Arrendatario= null;
      $NumeroEmpresa = null;
      $Nempresa = null;
      $CuentaContable = ''; 
      $CuentaContableA = ''; 
      $contrato_id = '';
      $ItemContable = '';
      $NumeroEmpresa = '';
      $NombreEmpresa = '';
      $CentroCosto = '';
      $codigo_unidad = '';
      $registros = 0;
      $EmpresaPoliza = $empresa_id;
      //Variables DATA
      $p2_DATA = date('Ymd',strtotime($FechaActual));
      $p5_DC = '';
      $p6_DOC = $doc_num;
      $p7_LINHA = '';
      $p12_HIST = '';
      $p13_VALOR = '';
      $p14_PKO = '';
      $p17_LOTE = '';
      $p18_SBLOTE = 'REN';
      $p19_CTCD = '';
      $p20_CTAD = '';
      $p21_CCCD = '';
      $p22_CCAD = '';
      $p23_ITCD = '';
      $p24_ITAD = '';
      $p25_CLCD = '';
      $p26_CLAD = '';
      $p27_EMPORI = '';
      $p31_RECNO = '';
      $fila = 0;
      $selectDepositosGarantia = DB::table('SI_FORMS_BAPP02F92DB8_FRM_0A48F90C')
      ->where('FFRMS_80C0CBBF','!=', 2)
      ->where('FFRMS_8F2D2180', $empresa_id)
      ->where('FFRMS_D34DBFC0', 5356)
      ->where('FFRMS_90A4BCE5', 'Deposito en garantia')
      ->whereBetween('FFRMS_E73C86DE', [$fecha_inicio, $fecha_fin])
      ->get();
      foreach ($selectDepositosGarantia as $filaFactura) {
        if($a <= 999){
          $c++;
          $a++;

          $id_Factura = $filaFactura->id_FRM_0A48F90C;
          $tipoComprobante = $filaFactura->FFRMS_80C0CBBF;
          $IVA = $filaFactura->FFRMS_8EB4E52A;
          $impuesto = $filaFactura->FFRMS_D589F6C6;
          $foliof = $filaFactura->FFRMS_F6E17AB2; 
          $UnidadF = $filaFactura->FFRMS_B497EAD9;
          $Arrendatario_id = $filaFactura->FFRMS_C54453B0;
          $TotalFactura = $filaFactura->FFRMS_00B26ED7;
          $SubtotalFactura = $filaFactura->FFRMS_D6356B12;
          $FacturaArrendatario = $filaFactura->FFRMS_C54453B0;
          $moneda = $filaFactura->FFRMS_E90D619F;
          $FechaFs = $filaFactura->FFRMS_E73C86DE;
          $sucursal_predefinida_id = $filaFactura->FFRMS_6AC356E0;
          $domicilio_predefinida_id = $filaFactura->FFRMS_EC831C2A;
          $contrato_id = $filaFactura->FFRMS_2A2EAA0F;
          $CuentaContable = '';

          //error_log('Factura encontrada: '.$id_Factura);
          $CuentaContable ='';
          $SelectSucursal = DB::table('SI_FORMS_BAPP02F92DB8_FRM_045A4975')
          ->where('id_FRM_045A4975', $sucursal_predefinida_id)
          ->first();
          if ($SelectSucursal) {
            $CuentaContableA = $SelectSucursal->FFRMS_25103FA5;
            $ClaseValor = $SelectSucursal->FFRMS_CECFEB82;
            $Ncliente = $SelectSucursal->FFRMS_0B6479A0;
            $Arrendatario = $SelectSucursal->FFRMS_32B278A2;
            if ($CuentaContableA != null) {
              $SelectCuentaContable = DB::table('SI_FORMS_BAPP02F92DB8_FRM_229230AF')
              ->where('id_FRM_229230AF', $CuentaContableA)->first();
              if ($SelectCuentaContable) {
                $CuentaContable = $SelectCuentaContable->FFRMS_87A95699;
              }
            }
          }

          $ItemContable = '';
          $NumeroEmpresa = '';
          $NombreEmpresa = '';
          $CentroCosto = '';
          $codigo_unidad = '';
          $SelectUnidad = DB::table('SI_FORMS_BAPP02F92DB8_FRM_E2241755')
          ->where('id_FRM_E2241755', $UnidadF)
          ->first();
          if ($SelectUnidad) {
            $ItemContable = $SelectUnidad->FFRMS_649B0EF1;
            $NumeroEmpresa = $SelectUnidad->FFRMS_79965C05;
            $NombreEmpresa = $SelectUnidad->FFRMS_DB97FE86;
            $CentroCosto = $SelectUnidad->FFRMS_85D52E6B;
            $codigo_unidad = $SelectUnidad->FFRMS_FB5AFCAC;
          }
 
          $EmpresaF = $filaFactura->FFRMS_8F2D2180;
          $FechaFs = $filaFactura->FFRMS_A50AB74A; //fecha certiifcacion
          if ($FechaFs == null or $FechaFs == '') {
            $FechaFs = $filaFactura->FFRMS_E73C86DE; //fecha
          }

          $p5_DC_debitoOrCredito = '1'; //DEBITO
          $FechaF=date('Ymd',strtotime($FechaFs));

          $SelectEmpresa = DB::table('SI_FORMS_BAPP02F92DB8_FRM_92604F36')
          ->where('id_FRM_92604F36', $empresa_id)
          ->first();
          if ($SelectEmpresa) {
            $Empresa = $SelectEmpresa->FFRMS_40F34F0E;
            $Nempresa = $SelectEmpresa->FFRMS_7F569111;
            $p14_PKO_llave_referencia = $Nempresa.$FechaF.$foliof;//Llave de referencia para identificar el movimiento ZC1_PKO
          }
          if($tipoComprobante == 1){
            $p17_LOTE_tipo_comprobante = 3;//tipo de poliza Ingresos
          }
          elseif($tipoComprobante == 2){
            $p17_LOTE_tipo_comprobante = 2;//tipo de poliza Egreso
          }
          elseif ($tipoComprobante == 6) {
            $p17_LOTE_tipo_comprobante = 1;//tipo de poliza Diarios
          }

          $p12_HIST_concatenado = 'DEP-'.$foliof.' L-'.$codigo_unidad.' CTE-'.$Ncliente." ".utf8_encode($Arrendatario);
          $p27_EMPORI = $NumeroEmpresa;

          $p31_RECNO_max = $this->consultarRECNOPolizas();
          if ($p31_RECNO_max != null) {
            $data = array(
            'p2_DATA' => $p2_DATA, /*Fijo*/
            'p5_DC' => $p5_DC_debitoOrCredito,
            'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
            'p7_LINHA' => $a,
            'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
            'p13_VALOR' => $TotalFactura,
            'p14_PKO' => $p14_PKO_llave_referencia,
            'p17_LOTE' => $p17_LOTE_tipo_comprobante,
            'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
            'p19_CTCD' => $Dep_garantía_clientes,
            'p20_CTAD' => '',
            'p21_CCCD' => '',
            'p22_CCAD' => '',
            'p23_ITCD' => '',
            'p24_ITAD' => '',
            'p25_CLCD' => '',
            'p26_CLAD' => '',
            'p27_EMPORI' => $NumeroEmpresa,
            'p31_RECNO' => $p31_RECNO_max
            );
            
            ///SI RESULT === TRUE
            $result_enviar = $this->crearRegistroPoliza($data, 'Deposito en garantia');
            if ($result_enviar === False) {
              error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
              //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
              exit(1);
            }else{
              $a++;
              $registros = $registros + 1;
              error_log('SUCCESS: '.json_encode($data), 0);
              //echo "<br/> SUCCESS: ".json_encode($data);
            }
          }
          ////SEGUNDO ASIENTO
          $p31_RECNO_max = $this->consultarRECNOPolizas();
          $p5_DC_debitoOrCredito = 2;
          if ($p31_RECNO_max != null) {
            $data = array(
            'p2_DATA' => $p2_DATA, /*Fijo*/
            'p5_DC' => $p5_DC_debitoOrCredito,
            'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
            'p7_LINHA' => $a,
            'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
            'p13_VALOR' => $TotalFactura,
            'p14_PKO' => $p14_PKO_llave_referencia,
            'p17_LOTE' => $p17_LOTE_tipo_comprobante,
            'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
            'p19_CTCD' => '',
            'p20_CTAD' => $Dep_garantía_cobrar_cliente,
            'p21_CCCD' => '',
            'p22_CCAD' => '',
            'p23_ITCD' => '',
            'p24_ITAD' => '',
            'p25_CLCD' => '',
            'p26_CLAD' => '',
            'p27_EMPORI' => $NumeroEmpresa,
            'p31_RECNO' => $p31_RECNO_max
            );
            $result_enviar = $this->crearRegistroPoliza($data, 'Deposito en garantia');
            if ($result_enviar === False) {
              error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
              //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
              exit(1);
            }else{
              $registros = $registros + 1;
              error_log('SUCCESS: '.json_encode($data), 0);
              //echo "<br/> SUCCESS: ".json_encode($data);
            }
          }

        }//if contador
        $updateFactura = DB::table('SI_FORMS_BAPP02F92DB8_FRM_0A48F90C')
        ->where('id_FRM_0A48F90C', $id_Factura)
        ->update(['FFRMS_D34DBFC0' => 4488]);
      }
      return $registros;
    }
    public function enviarJuridicos($fecha_inicio, $fecha_fin, $empresa_id, $cuenta_deudores_diversos, $doc_num, $FechaActual )
    {
      
      $a = 1;
      $c = 1;
      $foliof= null;
      $Ncliente= null;
      $Arrendatario= null;
      $NumeroEmpresa = null;
      $Nempresa = null;
      $CuentaContable = ''; 
      $CuentaContableA = ''; 
      $contrato_id = '';
      $ItemContable = '';
      $NumeroEmpresa = '';
      $NombreEmpresa = '';
      $CentroCosto = '';
      $codigo_unidad = '';
      $registros = 0;
      $EmpresaPoliza = $empresa_id;
      //Variables DATA
      $p2_DATA = date('Ymd',strtotime($FechaActual));
      $p5_DC = '';
      $p6_DOC = $doc_num;
      $p7_LINHA = '';
      $p12_HIST = '';
      $p13_VALOR = '';
      $p14_PKO = '';
      $p17_LOTE = '';
      $p18_SBLOTE = 'REN';
      $p19_CTCD = '';
      $p20_CTAD = '';
      $p21_CCCD = '';
      $p22_CCAD = '';
      $p23_ITCD = '';
      $p24_ITAD = '';
      $p25_CLCD = '';
      $p26_CLAD = '';
      $p27_EMPORI = '';
      $p31_RECNO = '';
      $fila = 0;

      $SelectFacturas = DB::table('SI_FORMS_BAPP02F92DB8_FRM_0A48F90C')
      ->where('FFRMS_B53C0145', 426)
      ->where('FFRMS_80C0CBBF','!=',2)
      ->where('FFRMS_8F2D2180', $empresa_id)
      ->whereBetween('FFRMS_E73C86DE', [$fecha_inicio, $fecha_fin])
      ->where('FFRMS_3CDCA5E5', 5047)
      ->where('FFRMS_8D061D82', 'Revisada')
      ->where('FFRMS_90A4BCE5','!=', 'Deposito en garantia')
      ->where('FFRMS_90A4BCE5','!=', 'Nota de credito')
      ->get();
      foreach ($SelectFacturas as $filaFactura) {
        if($a <= 999){
          $c++;
          //$a++;
          $tipoComprobante = $filaFactura->FFRMS_80C0CBBF;
          $id_Factura = $filaFactura->id_FRM_0A48F90C;
          $IVA = $filaFactura->FFRMS_8EB4E52A;
          $impuesto = $filaFactura->FFRMS_D589F6C6;
          $foliof = $filaFactura->FFRMS_F6E17AB2; 
          $UnidadF = $filaFactura->FFRMS_B497EAD9;
          $Arrendatario_id = $filaFactura->FFRMS_C54453B0;
          $TotalFactura = $filaFactura->FFRMS_00B26ED7;
          $SaldoFactura = $filaFactura->FFRMS_0BE6209E;
          $SubtotalFactura = $filaFactura->FFRMS_D6356B12;
          $FacturaArrendatario = $filaFactura->FFRMS_C54453B0;
          $moneda = $filaFactura->FFRMS_E90D619F;
          $sucursal_predefinida_id = $filaFactura->FFRMS_6AC356E0;
          $domicilio_predefinida_id = $filaFactura->FFRMS_EC831C2A;

          $EmpresaF = $filaFactura->FFRMS_8F2D2180;
          $SelectEmpresa = DB::table('SI_FORMS_BAPP02F92DB8_FRM_92604F36')
          ->where('id_FRM_92604F36', $empresa_id)
          ->first();
          if ($SelectEmpresa) {
            $Empresa = $SelectEmpresa->FFRMS_40F34F0E;
            $Nempresa = $SelectEmpresa->FFRMS_7F569111;
            if($domicilio_predefinida_id == null or $domicilio_predefinida_id == ''){
              $domicilio_predefinida_id = $SelectEmpresa->FFRMS_A314EE4A;
            }
          }

          if ($sucursal_predefinida_id == null or $sucursal_predefinida_id == '') {
            $SelectArrendatario = DB::table('SI_FORMS_BAPP02F92DB8_FRM_54CDED61')
            ->where('id_FRM_54CDED61', $Arrendatario_id)->first();
            if ($SelectArrendatario) {
              $sucursal_predefinida_id = $filaArrendatario->FFRMS_42DC00D1;
            }
          }
          $FechaFs = $filaFactura->FFRMS_A50AB74A; //fecha certiifcacion
          if ($FechaFs == null or $FechaFs == '') {
            $FechaFs = $filaFactura->FFRMS_E73C86DE; //fecha
          }
          if($tipoComprobante == 1){
            $p17_LOTE_tipo_comprobante = 3;//tipo de poliza Ingresos
          }
          elseif($tipoComprobante == 2){
            $p17_LOTE_tipo_comprobante = 2;//tipo de poliza Egreso
          }
          elseif ($tipoComprobante == 6) {
            $p17_LOTE_tipo_comprobante = 1;//tipo de poliza Diarios
          }

          //error_log('Factura encontrada: '.$id_Factura);
          $CuentaContable ='';
          $SelectSucursal = DB::table('SI_FORMS_BAPP02F92DB8_FRM_045A4975')
          ->where('id_FRM_045A4975', $sucursal_predefinida_id)
          ->first();
          $CuentaContableA = '';
          $ClaseValor = '';
          $Ncliente = '';
          $Arrendatario = '';
          if ($SelectSucursal) {
            $CuentaContableA = $SelectSucursal->FFRMS_25103FA5;
            $ClaseValor = $SelectSucursal->FFRMS_CECFEB82;
            $Ncliente = $SelectSucursal->FFRMS_0B6479A0;
            $Arrendatario = $SelectSucursal->FFRMS_32B278A2;

            $SelectCuentaContable = DB::table('SI_FORMS_BAPP02F92DB8_FRM_229230AF')
            ->where('id_FRM_229230AF', $CuentaContableA)->first();
            if($SelectCuentaContable){
              $CuentaContable = $SelectCuentaContable->FFRMS_87A95699;
            }
            
          }
          if ($ClaseValor == null) {
            $ClaseValor = '';
          }

          $ItemContable = '';
          $NumeroEmpresa = '';
          $NombreEmpresa = '';
          $CentroCosto = '';
          $codigo_unidad = '';

          $SelectUnidad = DB::table('SI_FORMS_BAPP02F92DB8_FRM_E2241755')
          ->where('id_FRM_E2241755', $UnidadF)->first();
          if ($SelectUnidad) {
            $ItemContable = $SelectUnidad->FFRMS_649B0EF1;
            $NumeroEmpresa = $SelectUnidad->FFRMS_79965C05;
            $NombreEmpresa = $SelectUnidad->FFRMS_DB97FE86;
            $CentroCosto = $SelectUnidad->FFRMS_85D52E6B;
            $codigo_unidad = $SelectUnidad->FFRMS_FB5AFCAC;
          }

          $FechaF=date('Ymd',strtotime($FechaFs));

          $SelectEmpresa = DB::table('SI_FORMS_BAPP02F92DB8_FRM_92604F36')
          ->where('id_FRM_92604F36', $empresa_id)->first();
          if ($SelectEmpresa) {
            $Empresa = $SelectEmpresa->FFRMS_40F34F0E;
            $Nempresa = $SelectEmpresa->FFRMS_7F569111;
            $p14_PKO_llave_referencia = $Nempresa.$FechaF.$foliof;//Llave de referencia para identificar el movimiento ZC1_PKO
          }

          $p12_HIST_concatenado = 'F-'.$foliof.' L-'.$codigo_unidad.' CTE-'.$Ncliente." ".$Arrendatario;
          
          ///// PRIMER ASIENTO 1/2 DEUDORES DIVERSOS
          $p31_RECNO_max = $this->consultarRECNOPolizas();
          if ($p31_RECNO_max != null) {
            $p5_DC_debitoOrCredito = '1';
            $data = array(
            'p2_DATA' => $p2_DATA, /*Fijo*/
            'p5_DC' => $p5_DC_debitoOrCredito,
            'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
            'p7_LINHA' => $a,
            'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
            'p13_VALOR' => $SaldoFactura,
            'p14_PKO' => $p14_PKO_llave_referencia,
            'p17_LOTE' => $p17_LOTE_tipo_comprobante,
            'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
            'p19_CTCD' => $cuenta_deudores_diversos,
            'p20_CTAD' => '',
            'p21_CCCD' => '',
            'p22_CCAD' => '',
            'p23_ITCD' => '',
            'p24_ITAD' => '',
            'p25_CLCD' => $ClaseValor,
            'p26_CLAD' => '',
            'p27_EMPORI' => $NumeroEmpresa,
            'p31_RECNO' => $p31_RECNO_max
            );
             
            ///SI RESULT === TRUE
            $result_enviar = $this->crearRegistroPoliza($data, 'Juridicos');
            if ($result_enviar === False) {
              error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
              //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
              exit(1);
            }else{
              $a++;
              $registros = $registros + 1;
              error_log('SUCCESS: '.json_encode($data), 0);
              //echo "<br/> SUCCESS: ".json_encode($data);
            }
          }

          ///// PRIMER ASIENTO 1/2 DEUDORES DIVERSOS
          $p31_RECNO_max = $this->consultarRECNOPolizas();
          if ($p31_RECNO_max != null) {
            $p5_DC_debitoOrCredito = '2';
            $data = array(
            'p2_DATA' => $p2_DATA, /*Fijo*/
            'p5_DC' => $p5_DC_debitoOrCredito,
            'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
            'p7_LINHA' => $a,
            'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
            'p13_VALOR' => $SaldoFactura,
            'p14_PKO' => $p14_PKO_llave_referencia,
            'p17_LOTE' => $p17_LOTE_tipo_comprobante,
            'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
            'p19_CTCD' => '',
            'p20_CTAD' => $CuentaContable,
            'p21_CCCD' => '',
            'p22_CCAD' => '',
            'p23_ITCD' => '',
            'p24_ITAD' => '',
            'p25_CLCD' => '',
            'p26_CLAD' => $ClaseValor,
            'p27_EMPORI' => $NumeroEmpresa,
            'p31_RECNO' => $p31_RECNO_max
            );
             
            ///SI RESULT === TRUE
            $result_enviar = $this->crearRegistroPoliza($data, 'Juridicos');
            if ($result_enviar === False) {
              error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
              //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
              exit(1);
            }else{
              $a++;
              $registros = $registros + 1;
              error_log('SUCCESS: '.json_encode($data), 0);
              //echo "<br/> SUCCESS: ".json_encode($data);
            }
          }
        }//if contador
        $updateFactura = DB::table('SI_FORMS_BAPP02F92DB8_FRM_0A48F90C')
        ->where('id_FRM_0A48F90C', $id_Factura)
        ->update(['FFRMS_8D061D82' => 'Contabilizada']);
      }//while facturas
      return $registros;
    }
    public function enviarPagosJuridicos($fecha_inicio, $fecha_fin, $empresa_id, $cuenta_deudores_diversos, $doc_num, $FechaActual )
    {
      
      $a = 0;
      $c = 1;
      $foliof= null;
      $Ncliente= null;
      $Arrendatario= null;
      $NumeroEmpresa = null;
      $Nempresa = null;
      $CuentaContable = ''; 
      $CuentaContableA = ''; 
      $contrato_id = '';
      $ItemContable = '';
      $NumeroEmpresa = '';
      $NombreEmpresa = '';
      $CentroCosto = '';
      $codigo_unidad = '';
      $registros = 0;
      $EmpresaPoliza = $empresa_id;
      //Variables DATA
      $p2_DATA = date('Ymd',strtotime($FechaActual));
      $p5_DC = '';
      $p6_DOC = $doc_num;
      $p7_LINHA = '';
      $p12_HIST = '';
      $p13_VALOR = '';
      $p14_PKO = '';
      $p17_LOTE = '';
      $p18_SBLOTE = 'REN';
      $p19_CTCD = '';
      $p20_CTAD = '';
      $p21_CCCD = '';
      $p22_CCAD = '';
      $p23_ITCD = '';
      $p24_ITAD = '';
      $p25_CLCD = '';
      $p26_CLAD = '';
      $p27_EMPORI = '';
      $p31_RECNO = '';
      $fila = 0;

      $selectPagos = DB::table('SI_FORMS_BAPP02F92DB8_FRM_D4E87BCD')
      ->join('SI_FORMS_BAPP02F92DB8_FRM_0A48F90C','id_FRM_0A48F90C','=','FFRMS_AE0C9BD0')
      ->where('FFRMS_EDC7A690', $empresa_id)
      ->whereBetween('FFRMS_340F164A', [$fecha_inicio, $fecha_fin])
      ->whereRaw('(FFRMS_8A6CBF68 = 1718 or FFRMS_8A6CBF68 = 2891)')
      ->where('FFRMS_BB70BE4B', '!=','Pago excedente')
      ->where('FFRMS_0999B7C0', 5357)
      ->where('FFRMS_3CDCA5E5',5047)
      ->get();
      foreach ($selectPagos as $filaPagos) {
        $id_Factura = $filaPagos->FFRMS_AE0C9BD0;
        $array[$fila] = $id_Factura;
        $fila++; 
      }
      if($a <= 999){
        $a++;
        if (!empty($array)){

          /// Cuenta elementos del array
          $cont=count($array);

          /// Recorre Array
          $folioAnterior = 0;
          for ($row=0;$row<$cont;$row++){
            $FolioPadre=$array[$row];
            if($folioAnterior != $FolioPadre){
              $selectFacturas = DB::table('SI_FORMS_BAPP02F92DB8_FRM_0A48F90C')
              ->where('id_FRM_0A48F90C', $FolioPadre)
              ->get();
              foreach ($selectFacturas as $filaFactura) {
                $validador = 0;
                $selectPagos = DB::table('SI_FORMS_BAPP02F92DB8_FRM_D4E87BCD')
                ->where('FFRMS_AE0C9BD0', $FolioPadre)
                ->get();
                foreach ($selectPagos as $filaPagos) {
                  $id_pago = $filaPagos->id_FRM_D4E87BCD;

                  $Complemento = $filaPagos->FFRMS_FD5BF009;
                  $id_Factura = $filaPagos->FFRMS_AE0C9BD0;

                  $validador ++;
                  $Complemento = $filaPagos->FFRMS_FD5BF009;
                  $id_pago = $filaPagos->id_FRM_D4E87BCD;
                  $TotalPago = $filaPagos->FFRMS_F542936B;
                  $BancoP = $filaPagos->FFRMS_FF5C676C;
                  $tipoComprobante = $filaFactura->FFRMS_80C0CBBF;
                  $IVA = $filaFactura->FFRMS_8EB4E52A;
                  $impuesto = $filaFactura->FFRMS_D589F6C6;
                  $foliof = $filaFactura->FFRMS_F6E17AB2;
                  $Arrendatario_id = $filaFactura->FFRMS_C54453B0;
                  $UnidadF = $filaFactura->FFRMS_B497EAD9;
                  $TotalFactura = $filaFactura->FFRMS_00B26ED7;
                  $SubtotalFactura = $filaFactura->FFRMS_D6356B12;
                  $FacturaArrendatario = $filaFactura->FFRMS_C54453B0;
                  $moneda = $filaFactura->FFRMS_E90D619F;
                  $Factura_tipo = $filaFactura->FFRMS_90A4BCE5;
                  $contrato_id = $filaFactura->FFRMS_2A2EAA0F;
                  $sucursal_predefinida_id = $filaFactura->FFRMS_6AC356E0;
                  $domicilio_predefinida_id = $filaFactura->FFRMS_EC831C2A;
                  $EmpresaF = $filaFactura->FFRMS_8F2D2180;
                  $FechaFs = $filaFactura->FFRMS_A50AB74A; //fecha certiifcacion
                  if ($FechaFs == null or $FechaFs == '') {
                    $FechaFs = $filaFactura->FFRMS_E73C86DE; //fecha
                  }
                  $FechaF=date('Ymd',strtotime($FechaFs));
                  $CuentaContabelB = null;
                  $CuentaContabelB = null;
                  $CuentaContable = '';

                  //error_log('Factura encontrada: '.$id_Factura);
                  $CuentaContable ='';

                  $SelectSucursal = DB::table('SI_FORMS_BAPP02F92DB8_FRM_045A4975')
                  ->where('id_FRM_045A4975', $sucursal_predefinida_id)
                  ->first();
                  if ($SelectSucursal) {
                    $CuentaContableA = $SelectSucursal->FFRMS_25103FA5;
                    $ClaseValor = $SelectSucursal->FFRMS_CECFEB82;
                    $Ncliente = $SelectSucursal->FFRMS_0B6479A0;
                    $Arrendatario = $SelectSucursal->FFRMS_32B278A2;
                    if ($CuentaContableA != null) {
                      $SelectCuentaContable = DB::table('SI_FORMS_BAPP02F92DB8_FRM_229230AF')
                      ->where('id_FRM_229230AF', $CuentaContableA)
                      ->first();
                      if ($SelectCuentaContable) {
                        $CuentaContable = $SelectCuentaContable->FFRMS_87A95699;
                      }
                    }
                  }

                  $ItemContable = '';
                  $NumeroEmpresa = '';
                  $NombreEmpresa = '';
                  $CentroCosto = '';
                  $codigo_unidad = '';

                  $SelectUnidad = DB::table('SI_FORMS_BAPP02F92DB8_FRM_E2241755')
                  ->where('id_FRM_E2241755', $UnidadF)
                  ->first();
                  if ($SelectUnidad) {
                    $ItemContable = $SelectUnidad->FFRMS_649B0EF1;
                    $NumeroEmpresa = $SelectUnidad->FFRMS_79965C05;
                    $NombreEmpresa = $SelectUnidad->FFRMS_DB97FE86;
                    $CentroCosto = $SelectUnidad->FFRMS_85D52E6B;
                    $codigo_unidad = $SelectUnidad->FFRMS_FB5AFCAC;
                  }

                  
                  $SelectEmpresa = DB::table('SI_FORMS_BAPP02F92DB8_FRM_92604F36')
                  ->where('id_FRM_92604F36', $empresa_id)
                  ->first();
                  if ($SelectEmpresa) {
                    $Empresa = $SelectEmpresa->FFRMS_40F34F0E;
                    $Nempresa = $SelectEmpresa->FFRMS_7F569111;
                    $p14_PKO_llave_referencia = $Nempresa.$FechaF.$foliof;//Llave de referencia para identificar el movimiento ZC1_PKO
                  }
                  if($BancoP != null or $BancoP != ''){
                    $SelectCuentaBancaria = DB::table('SI_FORMS_BAPP02F92DB8_FRM_FA24357A')
                    ->where('id_FRM_FA24357A', $BancoP)->first();
                    if ($SelectCuentaBancaria) {
                      $CuentaContabelB = $SelectCuentaBancaria->FFRMS_989765FB;
                    }
                  }

                  $selectComplemento = DB::table('SI_FORMS_BAPP02F92DB8_FRM_B20F14A3')
                  ->where('id_FRM_B20F14A3', $Complemento)->first();
                  if ($selectComplemento){
                    $id_complemento = $selectComplemento->id_FRM_B20F14A3;
                    $FolioComplemento = $selectComplemento->FFRMS_54D2F781;
                  }

                  if ($Factura_tipo == 'Deposito en garantia') {
                    $ClaseValor = '';
                  }
                  
                  if($tipoComprobante == 1){
                    $p17_LOTE_tipo_comprobante = 3;//tipo de poliza Ingresos
                  }
                  elseif($tipoComprobante == 2){
                    $p17_LOTE_tipo_comprobante = 2;//tipo de poliza Egreso
                  }
                  elseif ($tipoComprobante == 6) {
                    $p17_LOTE_tipo_comprobante = 1;//tipo de poliza Diarios
                  }
                  $p5_DC_debitoOrCredito = '2';
                  $p12_HIST_concatenado = 'REC '.$FolioComplemento.' FAC '.$foliof.' CTE '.$Ncliente.' '.$Arrendatario;
                  //// PRIMER POLIZA 1/4
                  $p31_RECNO_max = $this->consultarRECNOPolizas();
                  if ($p31_RECNO_max != null) {
                    $data = array(
                    'p2_DATA' => $p2_DATA, /*Fijo*/
                    'p5_DC' => $p5_DC_debitoOrCredito,
                    'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                    'p7_LINHA' => $a,
                    'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                    'p13_VALOR' => $TotalPago,
                    'p14_PKO' => $p14_PKO_llave_referencia,
                    'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                    'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                    'p19_CTCD' => '',
                    'p20_CTAD' => $cuenta_deudores_diversos,
                    'p21_CCCD' => '',
                    'p22_CCAD' => '',
                    'p23_ITCD' => '',
                    'p24_ITAD' => '',
                    'p25_CLCD' => '',
                    'p26_CLAD' => '',
                    'p27_EMPORI' => $Nempresa,
                    'p31_RECNO' => $p31_RECNO_max
                    );
                     
                    ///SI RESULT === TRUE
                    $result_enviar = $this->crearRegistroPoliza($data, 'Pagos Juridicos');
                    if ($result_enviar === False) {
                      error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                      //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                      exit(1);
                    }else{
                      $a++;
                      $registros = $registros + 1;
                      error_log('SUCCESS: '.json_encode($data), 0);
                      //echo "<br/> SUCCESS: ".json_encode($data);
                    }
                  }

                  ///// SEGUNDO POLIZA 2/4
                  $p31_RECNO_max = $this->consultarRECNOPolizas();
                  if ($p31_RECNO_max != null) {
                    $p5_DC_debitoOrCredito = '1';
                    $data = array(
                    'p2_DATA' => $p2_DATA, /*Fijo*/
                    'p5_DC' => $p5_DC_debitoOrCredito,
                    'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                    'p7_LINHA' => $a,
                    'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                    'p13_VALOR' => $TotalPago,
                    'p14_PKO' => $p14_PKO_llave_referencia,
                    'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                    'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                    'p19_CTCD' => $CuentaContabelB,
                    'p20_CTAD' => '',
                    'p21_CCCD' => '',
                    'p22_CCAD' => '',
                    'p23_ITCD' => '',
                    'p24_ITAD' => '',
                    'p25_CLCD' => '',
                    'p26_CLAD' => '',
                    'p27_EMPORI' => $Nempresa,
                    'p31_RECNO' => $p31_RECNO_max
                    );
                     
                    ///SI RESULT === TRUE
                    $result_enviar = $this->crearRegistroPoliza($data, 'Pagos Juridicos');
                    if ($result_enviar === False) {
                      error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                      //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                      exit(1);
                    }else{
                      $a++;
                      $registros = $registros + 1;
                      error_log('SUCCESS: '.json_encode($data), 0);
                      //echo "<br/> SUCCESS: ".json_encode($data);
                    }
                  }

                  // ----------------------------------------Poliza IVA----------------------------
                  ///SUMAR LOS IMPUESTOS POR PARTIDA
                  $selectPartidasImpuestos = DB::table('SI_FORMS_BAPP02F92DB8_FRM_8276943C')
                  ->select('FFRMS_564F3B89', 'id_FRM_8276943C', DB::raw('SUM(FFRMS_7C9A07A7) as sumaIVA'),'FFRMS_89817A23 as tasaIva', 'FFRMS_C40DAAFD as tasaRetIva',  'FFRMS_3ACD6BAA as tasaIsr', 'FFRMS_61C54875 as tasaIeps' )
                  ->where('FFRMS_1D8401A5', $id_Factura)
                  ->groupBy('FFRMS_564F3B89')
                  ->get();
                  foreach ($selectPartidasImpuestos as $filapartidasImpuestos) {
                    $impuesto = $filapartidasImpuestos->FFRMS_564F3B89;
                    $totalIVA = $filapartidasImpuestos->sumaIVA;
                    $tasaIva = $filapartidasImpuestos->tasaIva;
                    if ($tasaIva > 0 ) {
                      $subtotal_partida_temp = round($TotalPago / ($tasaIva + 1), 2);
                      $iva_proporcional_pago = round($subtotal_partida_temp * $tasaIva , 2);
                    }else{
                      $iva_proporcional_pago = 0;
                    }
                    $CuentaContableImpuestos = null;
                    $SelecImpuesto= DB::table('SI_FORMS_BAPP02F92DB8_FRM_B11C5D02')
                    ->where('id_FRM_B11C5D02', $impuesto)
                    ->first();
                    if ($SelecImpuesto) {
                      $CuentaContableImpuestos =$SelecImpuesto->FFRMS_B28B7E32;
                      $SelectCuentaContable = DB::table('SI_FORMS_BAPP02F92DB8_FRM_229230AF')
                      ->where('id_FRM_229230AF', $CuentaContableImpuestos)
                      ->first();
                      if ($SelectCuentaContable) {
                        $CuentaContableImpuestos = $SelectCuentaContable->FFRMS_87A95699;
                      }

                      $CuentaContableImpuestos_cobrado =$SelecImpuesto->FFRMS_80DCA203;
                      $SelectCuentaContable_cobrado = DB::table('SI_FORMS_BAPP02F92DB8_FRM_229230AF')
                      ->where('id_FRM_229230AF', $CuentaContableImpuestos_cobrado)->first();
                      if ($SelectCuentaContable_cobrado) {
                        $CuentaContableImpuestos_cobrado = $SelectCuentaContable_cobrado->FFRMS_87A95699;
                      }
                    }
                    $p31_RECNO_max = $this->consultarRECNOPolizas();
                    if ($p31_RECNO_max != null) {
                      $p5_DC_debitoOrCredito = '1';
                      $data = array(
                      'p2_DATA' => $p2_DATA, /*Fijo*/
                      'p5_DC' => $p5_DC_debitoOrCredito,
                      'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                      'p7_LINHA' => $a,
                      'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                      'p13_VALOR' => $iva_proporcional_pago,
                      'p14_PKO' => $p14_PKO_llave_referencia,
                      'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                      'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                      'p19_CTCD' => $CuentaContableImpuestos,
                      'p20_CTAD' => '',
                      'p21_CCCD' => '',
                      'p22_CCAD' => '',
                      'p23_ITCD' => '',
                      'p24_ITAD' => '',
                      'p25_CLCD' => '',
                      'p26_CLAD' => '',
                      'p27_EMPORI' => $Nempresa,
                      'p31_RECNO' => $p31_RECNO_max
                      );
                       
                      ///SI RESULT === TRUE
                      $result_enviar = $this->crearRegistroPoliza($data, 'Pagos Juridicos');
                      if ($result_enviar === False) {
                        error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                        //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                        exit(1);
                      }else{
                        $a++;
                        $registros = $registros + 1;
                        error_log('SUCCESS: '.json_encode($data), 0);
                        //echo "<br/> SUCCESS: ".json_encode($data);
                      }
                    }
                    
                    // ---------------------------------Poliza COBRADO------------------------------
                    $p31_RECNO_max = $this->consultarRECNOPolizas();
                    if ($p31_RECNO_max != null) {
                      $p5_DC_debitoOrCredito = '2';
                      $data = array(
                      'p2_DATA' => $p2_DATA, /*Fijo*/
                      'p5_DC' => $p5_DC_debitoOrCredito,
                      'p6_DOC' => $p6_DOC, /*Lo trae de la forma poliza*/
                      'p7_LINHA' => $a,
                      'p12_HIST' => substr($p12_HIST_concatenado, 0, 40),
                      'p13_VALOR' => $iva_proporcional_pago,
                      'p14_PKO' => $p14_PKO_llave_referencia,
                      'p17_LOTE' => $p17_LOTE_tipo_comprobante,
                      'p18_SBLOTE' => $p18_SBLOTE, /*Fijo*/
                      'p19_CTCD' => '',
                      'p20_CTAD' => $CuentaContableImpuestos_cobrado,
                      'p21_CCCD' => '',
                      'p22_CCAD' => '',
                      'p23_ITCD' => '',
                      'p24_ITAD' => '',
                      'p25_CLCD' => '',
                      'p26_CLAD' => '',
                      'p27_EMPORI' => $Nempresa,
                      'p31_RECNO' => $p31_RECNO_max
                      );
                       
                      ///SI RESULT === TRUE
                      $result_enviar = $this->crearRegistroPoliza($data, 'Pagos Juridicos');
                      if ($result_enviar === False) {
                        error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                        //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                        exit(1);
                      }else{
                        $a++;
                        $registros = $registros + 1;
                        error_log('SUCCESS: '.json_encode($data), 0);
                        //echo "<br/> SUCCESS: ".json_encode($data);
                      }
                    }
                  }
                  $updatePago = DB::table('SI_FORMS_BAPP02F92DB8_FRM_D4E87BCD')
                  ->where('id_FRM_D4E87BCD', $id_pago)->update(['FFRMS_0999B7C0'=>4668]);
                }
              }
            }//if validar
            $folioAnterior = $FolioPadre;
          }//For
        }//If
        
      }//If <=999
      return $registros;
    }
    public function enviarBancos($fecha_inicio, $fecha_fin, $empresa_id, $doc_num, $FechaActual )
    {
        ////VARIABLES DATA FINANCIERO
        $a = 0;
        $c = 1;
        $t_nmb = '';
        $p2_DATA = date('Ymd',strtotime($FechaActual));
        $p5_VALOR = '';
        $p7_BANCO = '';
        $p8_AGENCI = '';
        $p9_CONTA = '';
        $p11_DOCUME = '';
        $p12_VENCTO = '';
        $p13_RECPAG = '';
        $p14_BENEF = '';
        $p15_HISTOR = '';
        $p22_NUMERO = '';
        $p24_CLIFOR = '';
        $p26_DTDIGI = '';
        $p34_DTDISP = '';
        $p64_CLIENT = '';
        $p97_RECNO = '';
        $p99_SERIE = '';
        $p100_RFC = '';
        $NumeroBanco = null;
        $NumeroAgencia = null;
        $NumeroCuenta = null;
        $NombreArrendatario = null;
        $registros = 0;
        $RFC = null;
        $FolioC = null;
        $CodigoCliente = null;

        $SelectPagos = DB::table('SI_FORMS_BAPP02F92DB8_FRM_D4E87BCD')
        ->where('FFRMS_BB70BE4B', '!=', 'Pago excedente')
        ->where('FFRMS_EDC7A690', $empresa_id)
        ->whereBetween('FFRMS_340F164A',[$fecha_inicio, $fecha_fin])
        ->where('FFRMS_12611BC8', 'Revisada')
        ->get();

        foreach ($SelectPagos as $filaPagos) {
          $c++;
          $ContadorB = $doc_num +1;
          $Empresapago = $filaPagos->FFRMS_EDC7A690;
          $MontoP = $filaPagos->FFRMS_F542936B;
          $ArrendatarioP = $filaPagos->FFRMS_A91655C6;
          $EstatusPago = $filaPagos->FFRMS_8A6CBF68;
          $Complemento = $filaPagos->FFRMS_FD5BF009;
          $BancoP =  $filaPagos->FFRMS_FF5C676C;
          $FechaPago = $filaPagos->FFRMS_340F164A;
          $excedente_relacionado = $filaPagos->FFRMS_E32D8335;
          $id_pago = $filaPagos->id_FRM_D4E87BCD;
          $factura_id = $filaPagos->FFRMS_AE0C9BD0;
          $fecha_poliza = $filaPagos->FFRMS_1481AB25;
          $numero_poliza = $filaPagos->FFRMS_99DB4CEA;
          if($EstatusPago == 1718 /*ACTIVO*/ OR $EstatusPago == 2891/*ACTIVO*/){
            $p13_RECPAG_colD = 'R';//vacio ZE5_RECPAG
          }
          elseif($EstatusPago == 1720 /*CANCELADO*/){
            $p13_RECPAG_colD = 'P';//vacio ZE5_RECPAG
          }
          else{
            $p13_RECPAG_colD = 'R';//vacio ZE5_RECPAG
          }
          
          $an = substr($FechaPago,0,4);
          $me = substr($FechaPago,5,2);
          $di = substr($FechaPago,8,2);
          
          $FechaMovimiento =date('Ymd', mktime(0,0,0, $me, $di, $an));

          if ($excedente_relacionado != null and $excedente_relacionado != '') {
            /// ir a excedenes pr ese monto ARRE
            $crear_asientos_excedente = 'Si';
            $Excedentes = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
            ->select('FFRMS_250EB570')
            ->whereRaw("id_FRM_CEA84379 = $excedente_relacionado AND FFRMS_435DD1E5 = 'Si' AND FFRMS_7D843481 = 'Entrada' AND (FFRMS_3A4B3C79 = 'Pendiente' OR FFRMS_3A4B3C79 IS NULL )")
            ->first();
            if ($Excedentes) {
              $monto_excedente = $Excedentes->FFRMS_250EB570;
              $MontoP = $MontoP + $monto_excedente;
              $updatePago = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
              ->where('id_FRM_CEA84379', $excedente_relacionado)
              ->update(['FFRMS_3A4B3C79' => 'Contabilizada']);
            }
          }else{
            $crear_asientos_excedente = 'No';
          }

          $contrato_id = null;
          $filaFactura = DB::table('SI_FORMS_BAPP02F92DB8_FRM_0A48F90C')->where('id_FRM_0A48F90C', $factura_id)->first();
          if ($filaFactura){
            $subtotalFactura = $filaFactura->FFRMS_D6356B12;
            $totalFactura = $filaFactura->FFRMS_00B26ED7;
            $iva_factura = $filaFactura->FFRMS_8EB4E52A;
            $contrato_id = $filaFactura->FFRMS_2A2EAA0F;
            $sucursal_predefinida_id = $filaFactura->FFRMS_6AC356E0;
            $domicilio_predefinida_id = $filaFactura->FFRMS_EC831C2A;
          }

          //error_log('Factura encontrada: '.$id_Factura);
          $CuentaContable ='';
          $SelectSucursal = DB::table('SI_FORMS_BAPP02F92DB8_FRM_045A4975')->where('id_FRM_045A4975',$sucursal_predefinida_id )->first();
          if ($SelectSucursal){
            $CuentaContableA = $SelectSucursal->FFRMS_25103FA5;
            $ClaseValor = $SelectSucursal->FFRMS_CECFEB82;
            $CodigoCliente = $SelectSucursal->FFRMS_0B6479A0;
            $Arrendatario = $SelectSucursal->FFRMS_32B278A2;

            $SelectCuentaContable = DB::table('SI_FORMS_BAPP02F92DB8_FRM_229230AF')->where('id_FRM_229230AF', $CuentaContableA)->first();
            if ($SelectCuentaContable) {
              $CuentaContable = $SelectCuentaContable->FFRMS_87A95699;
            }
          }
          $SelectArrendatario = DB::table('SI_FORMS_BAPP02F92DB8_FRM_54CDED61')
          ->where('id_FRM_54CDED61', $ArrendatarioP )->first();
          if ($SelectArrendatario) {
            $NombreArrendatario = $SelectArrendatario->FFRMS_5FEEA39C;
            $RFC = $SelectArrendatario->FFRMS_54DBE137;
            $CodigoCliente = $SelectArrendatario->FFRMS_758B2022;
          } 

          $SelectCuentasB = DB::table('SI_FORMS_BAPP02F92DB8_FRM_FA24357A')
          ->where('id_FRM_FA24357A', $BancoP)->first();
          if ($SelectCuentasB) {
            $NumeroBanco = $SelectCuentasB->FFRMS_3DDDB508;
            $NumeroAgencia = $SelectCuentasB->FFRMS_ABA0610B;
            $NumeroCuenta = $SelectCuentasB->FFRMS_77FEB497;
          }
          if ($NumeroBanco == null) {
            $NumeroBanco = '';
          }
          if ($NumeroAgencia == null) {
            $NumeroAgencia = '';
          }
          if ($NumeroCuenta == null) {
            $NumeroCuenta = '';
          }
          if ($numero_poliza == null) {
            $numero_poliza = '';
          }
          if ($CodigoCliente == null) {
            $CodigoCliente = '';
          }
          if ($RFC == null) {
            $RFC = '';
          }
          if ($NombreArrendatario == null) {
            $NombreArrendatario = '';
          }
          if ($fecha_poliza == null) {
            $fecha_poliza = '';
          }
          $SerieC = '';
          $FolioC = '';
          $selectComplemento = DB::table('SI_FORMS_BAPP02F92DB8_FRM_B20F14A3')
          ->where('id_FRM_B20F14A3', $Complemento)->first();
          if ($selectComplemento) {
            $id_complemento = $selectComplemento->id_FRM_B20F14A3;
            $FolioC = $selectComplemento->FFRMS_54D2F781;
            $SerieC = $selectComplemento->FFRMS_2DA04BB0;
          }

          $SERIE_FOLIO_PAGO = $SerieC.''.$FolioC;

          $selectEmpresa = DB::table('SI_FORMS_BAPP02F92DB8_FRM_92604F36')->where('id_FRM_92604F36', $empresa_id)->first();
          if ($selectEmpresa) {
            $id_empresa = $selectEmpresa->id_FRM_92604F36;
            $NEmpresa = $selectEmpresa->FFRMS_7F569111;
          }
          $fecha_poliza = date('Ymd', strtotime($fecha_poliza));
          $nombre_tabla_protheus = 'ZE5'.$NEmpresa.'0';
          ///// PRIMER ASIENTO 1/1
          $p97_RECNO_max = $this->consultarRECNOFinanciero($nombre_tabla_protheus);
          if ($p97_RECNO_max != null) {
            $data = array(
            't_nmb' => $nombre_tabla_protheus,
            'p2_DATA' => $fecha_poliza,/*E*/
            'p5_VALOR' => $MontoP, /*Q*/
            'p7_BANCO' => $NumeroBanco,/*F*/
            'p8_AGENCI' => $NumeroAgencia,/*G*/
            'p9_CONTA' => $NumeroCuenta,/*H*/
            'p11_DOCUME' => substr($numero_poliza, 0, 50),/*S*/
            'p12_VENCTO' => $fecha_poliza,/*AB*/
            'p13_RECPAG' => $p13_RECPAG_colD,/*D*/
            'p14_BENEF' => substr($NombreArrendatario, 0, 30),/*U*/
            'p15_HISTOR' => substr($FolioC."-".$MontoP, 0, 40),/*V*/
            'p22_NUMERO' => substr($FolioC, 0, 20),/*AH*/
            'p24_CLIFOR' => $CodigoCliente,/*AK*/
            'p26_DTDIGI' => $fecha_poliza,/*AM*/
            'p34_DTDISP' => $fecha_poliza,/*AT*/
            'p64_CLIENT' => $CodigoCliente,/*BR*/
            'p97_RECNO' => $p97_RECNO_max,/*CU*/
            'p99_SERIE' => substr($SERIE_FOLIO_PAGO, 0, 6),/*T*/
            'p100_RFC' => substr($RFC, 0, 15)/*W*/
            );
             
            ///SI RESULT === TRUE
            $result_enviar = $this->crearRegistroFinanciero($data, 'Bancos');
            if ($result_enviar === False) {
              error_log('FAIL: No inserte el registro financiero numero ', 3, "my-errors.log");
              exit(1);
            }else{
              $a++;
              $registros = $registros + 1;
              error_log('SUCCESS: '.json_encode($data), 0);
            }
          }

          $updatePago = DB::table('SI_FORMS_BAPP02F92DB8_FRM_D4E87BCD')
          ->where('id_FRM_D4E87BCD', $id_pago)
          ->update(['FFRMS_12611BC8'=> 'Contabilizada']);
        }//while Pagos

        $fila = 0;
        $ContadorB = $doc_num +1;
        $selectExcedentes = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
        ->where('FFRMS_5D828359', $empresa_id)
        ->whereBetween('FFRMS_B3B3AF51', [$fecha_inicio, $fecha_fin])
        ->where('FFRMS_7D843481', 'Entrada')
        ->where('FFRMS_3A4B3C79', 'Revisada')
        ->where('FFRMS_435DD1E5', 'No')
        ->get();
        foreach ($selectExcedentes as $filaExcedentes) {
          $Arrendatario_id = $filaExcedentes->FFRMS_4BF4D64F;
          $array[$fila] = $Arrendatario_id;
          $fila++; 
        }//while Pagos
        if($a <= 999){
          $a++;
          if (!empty($array)){

            /// Cuenta elementos del array
            $cont=count($array); 

            /// Recorre Array
             $folioAnterior = 0;
            for ($row=0;$row<$cont;$row++){
              $FolioPadre=$array[$row];
              if($folioAnterior != $FolioPadre){
                $SelectArrendatario = DB::table('SI_FORMS_BAPP02F92DB8_FRM_54CDED61')->where('id_FRM_54CDED61', $FolioPadre)->get();
                foreach ($SelectArrendatario as $filaArrendatario) {
                  $validador = 0;
                  $excedentes = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
                  ->where('FFRMS_4BF4D64F', $FolioPadre)
                  ->where('FFRMS_7D843481', 'Entrada')
                  ->where('FFRMS_3A4B3C79', 'Revisada')->get();
                  foreach ($excedentes as $filaExcedente) {
                    $id_excedente = $filaExcedente->id_FRM_CEA84379;
                    $TotalExcedente = $filaExcedente->FFRMS_250EB570;
                    $cuenta_bancaria_id = $filaExcedente->FFRMS_86C556C0;
                    $factura_id = $filaExcedente->FFRMS_44BD172E;
                    $fecha_poliza = $filaExcedente->FFRMS_55F3FDFB;
                    $numero_poliza = $filaExcedente->FFRMS_A6B27B41;

                    $Arrendatario_id = $filaArrendatario->id_FRM_54CDED61;
                    $sucursal_predefinida_id = $filaArrendatario->FFRMS_42DC00D1;
                    $NombreArrendatario = $filaArrendatario->FFRMS_5FEEA39C;
                    $RFC = $filaArrendatario->FFRMS_54DBE137;
                    $contrato_id = null;
                    $moneda = 1;
                    $p13_RECPAG_colD = 'R';
                    $SelectSucursal = DB::table('SI_FORMS_BAPP02F92DB8_FRM_045A4975')
                    ->where('id_FRM_045A4975', $sucursal_predefinida_id)
                    ->first();
                    $CodigoCliente = '';
                    if ($SelectSucursal) {
                      $CodigoCliente = $SelectSucursal->FFRMS_0B6479A0;
                    }

                    $CuentaContable ='';
                    $validador ++;
                    $c++;

                    $tipoComprobante = '1';

                    $SelectEmpresa = DB::table('SI_FORMS_BAPP02F92DB8_FRM_92604F36')
                    ->where('id_FRM_92604F36', $empresa_id)
                    ->first();
                    $Empresa_text = '';
                    $ClaseValor_empresa = '';
                    $NEmpresa = '';
                    $cuentaBancaria_empresa = '';
                    if ($SelectEmpresa) {
                      $Empresa_text = $SelectEmpresa->FFRMS_40F34F0E;
                      $ClaseValor_empresa = $SelectEmpresa->FFRMS_353D60E2;
                      $NEmpresa = $SelectEmpresa->FFRMS_7F569111;
                      $cuentaBancaria_empresa = $SelectEmpresa->FFRMS_F71C342B;
                    }
                    
                    $tipoComprobante = '1';
                    $FechaPago = $filaExcedentes->FFRMS_B3B3AF51;
                    
                    $an = substr($FechaPago,0,4);
                    $me = substr($FechaPago,5,2);
                    $di = substr($FechaPago,8,2);
                    
                    $FechaMovimiento =date('Ymd', mktime(0,0,0, $me, $di, $an));
                    $SelectCuentasB = DB::table('SI_FORMS_BAPP02F92DB8_FRM_FA24357A')
                    ->where('id_FRM_FA24357A', $cuenta_bancaria_id)
                    ->first();
                    $NumeroBanco = '';
                    $NumeroAgencia = '';
                    $NumeroCuenta = '';
                    if ($SelectCuentasB) {
                      $NumeroBanco = $SelectCuentasB->FFRMS_3DDDB508;
                      $NumeroAgencia = $SelectCuentasB->FFRMS_ABA0610B;
                      $NumeroCuenta = $SelectCuentasB->FFRMS_77FEB497;
                    }

                    $nombre_tabla_protheus = 'ZE5'.$NEmpresa.'0';
                    $SERIE_FOLIO_PAGO = $CodigoCliente.''.$id_excedente;
                    ///// PRIMER ASIENTO 1/1
                    $p97_RECNO_max = $this->consultarRECNOFinanciero($nombre_tabla_protheus);
                    if ($p97_RECNO_max != null) {
                      $data = array(
                      't_nmb' => $nombre_tabla_protheus,
                      'p2_DATA' => $fecha_poliza,/*E*/
                      'p5_VALOR' => $TotalExcedente, /*Q*/
                      'p7_BANCO' => $NumeroBanco,/*F*/
                      'p8_AGENCI' => $NumeroAgencia,/*G*/
                      'p9_CONTA' => $NumeroCuenta,/*H*/
                      'p11_DOCUME' => substr($numero_poliza, 0, 50),/*S*/
                      'p12_VENCTO' => $fecha_poliza,/*AB*/
                      'p13_RECPAG' => $p13_RECPAG_colD,/*D*/
                      'p14_BENEF' => substr($NombreArrendatario, 0, 30),/*U*/
                      'p15_HISTOR' => substr($id_excedente."-CTE-".$CodigoCliente."-".$TotalExcedente, 0, 40),/*V*/
                      'p22_NUMERO' => substr($id_excedente, 0, 20),/*AH*/
                      'p24_CLIFOR' => $CodigoCliente,/*AK*/
                      'p26_DTDIGI' => $fecha_poliza,/*AM*/
                      'p34_DTDISP' => $fecha_poliza,/*AT*/
                      'p64_CLIENT' => $CodigoCliente,/*BR*/
                      'p97_RECNO' => $p97_RECNO_max,/*CU*/
                      'p99_SERIE' => substr($SERIE_FOLIO_PAGO,0,6),/*T*/
                      'p100_RFC' => substr($RFC,0,15)/*W*/
                      );

                      ///SI RESULT === TRUE
                      $result_enviar = $this->crearRegistroFinanciero($data, 'Bancos');
                      if ($result_enviar === False) {
                        error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                        //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                        exit(1);
                      }else{
                        $a++;
                        $registros = $registros + 1;
                        error_log('SUCCESS: '.json_encode($data), 0);
                        //echo "<br/> SUCCESS: ".json_encode($data);
                      }
                    }
                    ///Actualizmao excendnete
                    $updateExcedente = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
                    ->where('id_FRM_CEA84379', $id_excedente)
                    ->update(['FFRMS_3A4B3C79' => 'Contabilizada']);

                  }
                }
              }
            }
          }
        } //999
        return $registros;
    }
    public function enviarBancosExcedentes($fecha_inicio, $fecha_fin, $empresa_id, $doc_num, $FechaActual )
    {
        ////VARIABLES DATA FINANCIERO
        $a = 0;
        $c = 1;
        $t_nmb = '';
        $p2_DATA = date('Ymd',strtotime($FechaActual));
        $p5_VALOR = '';
        $p7_BANCO = '';
        $p8_AGENCI = '';
        $p9_CONTA = '';
        $p11_DOCUME = '';
        $p12_VENCTO = '';
        $p13_RECPAG = '';
        $p14_BENEF = '';
        $p15_HISTOR = '';
        $p22_NUMERO = '';
        $p24_CLIFOR = '';
        $p26_DTDIGI = '';
        $p34_DTDISP = '';
        $p64_CLIENT = '';
        $p97_RECNO = '';
        $p99_SERIE = '';
        $p100_RFC = '';
        $NumeroBanco = null;
        $NumeroAgencia = null;
        $NumeroCuenta = null;
        $NombreArrendatario = null;
        $registros = 0;
        $RFC = null;
        $FolioC = null;
        $CodigoCliente = null;
        $fila = 0;
        $ContadorB = $doc_num +1;
        $selectExcedentes = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
        ->where('FFRMS_5D828359', $empresa_id)
        ->whereBetween('FFRMS_B3B3AF51', [$fecha_inicio, $fecha_fin])
        ->where('FFRMS_7D843481', 'Entrada')
        ->where('FFRMS_3A4B3C79', 'Revisada')
        ->get();
        foreach ($selectExcedentes as $filaExcedentes) {
          $Arrendatario_id = $filaExcedentes->FFRMS_4BF4D64F;
          $array[$fila] = $Arrendatario_id;
          $fila++; 
        }//while Pagos
        if($a <= 999){
          $a++;
          if (!empty($array)){

            /// Cuenta elementos del array
            $cont=count($array); 

            /// Recorre Array
             $folioAnterior = 0;
            for ($row=0;$row<$cont;$row++){
              $FolioPadre=$array[$row];
              if($folioAnterior != $FolioPadre){
                $SelectArrendatario = DB::table('SI_FORMS_BAPP02F92DB8_FRM_54CDED61')->where('id_FRM_54CDED61', $FolioPadre)->get();
                foreach ($SelectArrendatario as $filaArrendatario) {
                  $validador = 0;
                  $excedentes = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
                  ->where('FFRMS_4BF4D64F', $FolioPadre)
                  ->where('FFRMS_7D843481', 'Entrada')
                  ->where('FFRMS_3A4B3C79', 'Revisada')->get();
                  foreach ($excedentes as $filaExcedente) {
                    $id_excedente = $filaExcedente->id_FRM_CEA84379;
                    $TotalExcedente = $filaExcedente->FFRMS_250EB570;
                    $cuenta_bancaria_id = $filaExcedente->FFRMS_86C556C0;
                    $factura_id = $filaExcedente->FFRMS_44BD172E;
                    $fecha_poliza = $filaExcedente->FFRMS_55F3FDFB;
                    $numero_poliza = $filaExcedente->FFRMS_A6B27B41;

                    $Arrendatario_id = $filaArrendatario->id_FRM_54CDED61;
                    $sucursal_predefinida_id = $filaArrendatario->FFRMS_42DC00D1;
                    $NombreArrendatario = $filaArrendatario->FFRMS_5FEEA39C;
                    $RFC = $filaArrendatario->FFRMS_54DBE137;
                    $contrato_id = null;
                    $moneda = 1;
                    $p13_RECPAG_colD = 'R';
                    $SelectSucursal = DB::table('SI_FORMS_BAPP02F92DB8_FRM_045A4975')
                    ->where('id_FRM_045A4975', $sucursal_predefinida_id)
                    ->first();
                    $CodigoCliente = '';
                    if ($SelectSucursal) {
                      $CodigoCliente = $SelectSucursal->FFRMS_0B6479A0;
                    }

                    $CuentaContable ='';
                    $validador ++;
                    $c++;

                    $tipoComprobante = '1';

                    $SelectEmpresa = DB::table('SI_FORMS_BAPP02F92DB8_FRM_92604F36')
                    ->where('id_FRM_92604F36', $empresa_id)
                    ->first();
                    $Empresa_text = '';
                    $ClaseValor_empresa = '';
                    $NEmpresa = '';
                    $cuentaBancaria_empresa = '';
                    if ($SelectEmpresa) {
                      $Empresa_text = $SelectEmpresa->FFRMS_40F34F0E;
                      $ClaseValor_empresa = $SelectEmpresa->FFRMS_353D60E2;
                      $NEmpresa = $SelectEmpresa->FFRMS_7F569111;
                      $cuentaBancaria_empresa = $SelectEmpresa->FFRMS_F71C342B;
                    }
                    
                    $tipoComprobante = '1';
                    $FechaPago = $filaExcedentes->FFRMS_B3B3AF51;
                    
                    $an = substr($FechaPago,0,4);
                    $me = substr($FechaPago,5,2);
                    $di = substr($FechaPago,8,2);
                    
                    $FechaMovimiento =date('Ymd', mktime(0,0,0, $me, $di, $an));
                    $SelectCuentasB = DB::table('SI_FORMS_BAPP02F92DB8_FRM_FA24357A')
                    ->where('id_FRM_FA24357A', $cuenta_bancaria_id)
                    ->first();
                    $NumeroBanco = '';
                    $NumeroAgencia = '';
                    $NumeroCuenta = '';
                    if ($SelectCuentasB) {
                      $NumeroBanco = $SelectCuentasB->FFRMS_3DDDB508;
                      $NumeroAgencia = $SelectCuentasB->FFRMS_ABA0610B;
                      $NumeroCuenta = $SelectCuentasB->FFRMS_77FEB497;
                    }

                    $nombre_tabla_protheus = 'ZE5'.$NEmpresa.'0';
                    $SERIE_FOLIO_PAGO = $CodigoCliente.''.$id_excedente;
                    ///// PRIMER ASIENTO 1/1
                    $p97_RECNO_max = $this->consultarRECNOFinanciero($nombre_tabla_protheus);
                    if ($p97_RECNO_max != null) {
                      $data = array(
                      't_nmb' => $nombre_tabla_protheus,
                      'p2_DATA' => $fecha_poliza,/*E*/
                      'p5_VALOR' => $TotalExcedente, /*Q*/
                      'p7_BANCO' => $NumeroBanco,/*F*/
                      'p8_AGENCI' => $NumeroAgencia,/*G*/
                      'p9_CONTA' => $NumeroCuenta,/*H*/
                      'p11_DOCUME' => substr($numero_poliza, 0, 50),/*S*/
                      'p12_VENCTO' => $fecha_poliza,/*AB*/
                      'p13_RECPAG' => $p13_RECPAG_colD,/*D*/
                      'p14_BENEF' => substr($NombreArrendatario, 0, 30),/*U*/
                      'p15_HISTOR' => substr($id_excedente."-CTE-".$CodigoCliente."-".$TotalExcedente, 0, 40),/*V*/
                      'p22_NUMERO' => substr($id_excedente, 0, 20),/*AH*/
                      'p24_CLIFOR' => $CodigoCliente,/*AK*/
                      'p26_DTDIGI' => $fecha_poliza,/*AM*/
                      'p34_DTDISP' => $fecha_poliza,/*AT*/
                      'p64_CLIENT' => $CodigoCliente,/*BR*/
                      'p97_RECNO' => $p97_RECNO_max,/*CU*/
                      'p99_SERIE' => substr($SERIE_FOLIO_PAGO,0,6),/*T*/
                      'p100_RFC' => substr($RFC,0,15)/*W*/
                      );

                      ///SI RESULT === TRUE
                      $result_enviar = $this->crearRegistroFinanciero($data, 'Bancos Excedentes');
                      if ($result_enviar === False) {
                        error_log('FAIL: No inserte el registro poliza numero '. $a, 0);
                        //echo '<br/> FAIL: No inserte el registro poliza numero '. $a;
                        exit(1);
                      }else{
                        $a++;
                        $registros = $registros + 1;
                        error_log('SUCCESS: '.json_encode($data), 0);
                        //echo "<br/> SUCCESS: ".json_encode($data);
                      }
                    }
                    ///Actualizmao excendnete
                    $updateExcedente = DB::table('SI_FORMS_BAPP02F92DB8_FRM_CEA84379')
                    ->where('id_FRM_CEA84379', $id_excedente)
                    ->update(['FFRMS_3A4B3C79' => 'Contabilizada']);

                  }
                }
              }
            }
          }
        } //999
        return $registros;
    }
}
