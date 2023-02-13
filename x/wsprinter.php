<?php
require_once('site_helper.php');
require_once('m_pdo.php');
// class wsprinter extends m_pdo {
class wsprinter {
	protected $m_pdo = null;
	protected $uri = null;

  public function __construct(){
		// parent::__construct();
		date_default_timezone_set("Asia/Bangkok");
		// $this->mainlib->logged_in();
		header('Access-Control-Allow-Origin: *'); 
		header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
		$this->m_pdo = new m_pdo();
		$this->uri = my_uri();
	}
	
  public function printername(){
		// // echo "tes";
		$url = 'bo/menu/receptionist/laporan/lap-daftarrj'; 
		$button_id = 'btn_cetak_antrian'; 
		$fx_name = 'termal_nomor_antrian_new';
		// $qu = $this->printername($url, $button_id, $fx_name);
		$qu = $this->m_pdo->printername($url, $button_id, $fx_name);

		echo json_encode([$qu, __FUNCTION__, my_ip()]); exit;
		// // $qu = $this->m_pdo->tes("url", "button_id", "fx_name");
		// // $qu = $this->tes("url", "button_id", "fx_name");
		// print_r($qu);


		// $db = $this->pdo_declare();
		// var_dump($db);
  }

  public function termal_nomor_antrian_new(){
		// $data = $this->input->post(NULL, TRUE);
		// $data['methodname']  = $this->router->fetch_method();
		// $data['printername'] = $this->m_it->printername($data['url'], $data['button_id'], $data['methodname']);
		
		// post_cetak_noantrian = 
		// {
			// 	"billing": "BL210609.0005",
			// 	"button_id": "btn_cetak_antrian",
			// 	"dpjp": "dr. I Gusti Njoman Teguh Budhi Bimantara, Sp.OG",
			// 	"nama": "PENA DWI FEBRIANI",
			// 	"no_antrian": "002",
			// 	"nosep": "0195R0280621V001125",
			// 	"tglrujukan": "2021-04-10",
			//  "url": "ws"
			// }
			
		$data = json_decode(file_get_contents( 'php://input' ),1);
		// if(my_ip()['client']== '192.168.1.93' ) $data['url'] = 'consolebox';
		if($data['ipclient']== '192.168.1.93' ) $data['url'] = 'consolebox';
		$data['methodname']  = __FUNCTION__;
		$data['printername'] = $this->m_pdo->printername($data['url'], $data['button_id'], $data['methodname']);
		// die($data['printername'] );
		if($data['printername'] == ''){ echo json_encode(["message"=>"Printername tidak ditemukan di database."]); exit; }
		// echo json_encode($data); exit;

		// // /* write the text to the print job */
		$handle = printer_open( $data['printername'] );

		printer_set_option($handle, PRINTER_MODE, "RAW");
		printer_set_option($handle, PRINTER_PAPER_FORMAT, PRINTER_FORMAT_CUSTOM);
		printer_set_option($handle, PRINTER_PAPER_WIDTH, 70);
		printer_set_option($handle, PRINTER_SCALE, 50);
		//FIX//$handle = printer_open();
		printer_start_doc( $handle, $data['methodname'] );
		printer_start_page($handle);



		$margin_left_page = 50;
		$margin_top_page = 50;
		$font_height = 30;

		//printer_create_font(face, height, width, font_weight, italic, underline, strikeout, orientation);
		//$font = printer_create_font("Tahoma", 150, 80, 400, false, false, false, 0);
		$font = printer_create_font("Tahoma", 30, 12, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = "NOMOR ANTRIAN";
		printer_draw_text($handle, $text, $margin_left_page, $margin_top_page);	////printer_draw_text(printer_handle, text, x, y);
		printer_delete_font($font);


		$font = printer_create_font("Tahoma", 30, 12, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = "RS. Citra Medika";
		printer_draw_text($handle, $text, $margin_left_page, ($margin_top_page+$font_height*1) );	////printer_draw_text(printer_handle, text, x, y);
		printer_delete_font($font);

		/*BILLING*/
		$font = printer_create_font("Tahoma", 30, 12, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = $data['billing'];
		printer_draw_text($handle, $text, $margin_left_page, ($margin_top_page+$font_height*2) );	////printer_draw_text(printer_handle, text, x, y);
		printer_delete_font($font);


		/*BARCODE SEP*/
		$font = printer_create_font("Free 3 of 9", 40, 25, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = "*".$data['nosep']."*";
		//printer_draw_text($handle, $text, $margin_left_page, 170);
		printer_draw_text($handle, $text, 5, 170);
		printer_delete_font($font);


		/*NO.ANTRIAN*/
		$font = printer_create_font("Lucida Console", 120, 80, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = $data['no_antrian'];
		printer_draw_text($handle, $text, $margin_left_page*5+20, 50);
		////printer_draw_text($handle, $text, $margin_left_page+50, 130); //posisi X CENTER
		printer_delete_font($font);


		/* ====== DATA PASIEN ====== */
		$font = printer_create_font("Lucida Console", 28, 14, PRINTER_FW_NORMAL, false, false, false, 0);
		printer_select_font($handle, $font);

		function tulis($handle_my, $row_dt_pasien, $text_my){
			$x = 20;
			$y = 250;
			printer_draw_text($handle_my, $text_my, $x, $y+(28*($row_dt_pasien-1)) );
		}

		$tulisan = array(
			"Tgl.Rujukan: ".$data['tglrujukan'],
			"Nama   : ".$data['nama'],
			"Dokter : ".$data['dpjp'],
			"Cetak  : ".date('Y-m-d h:i:s')
		);

		$tulisan_i = 0;
		foreach($tulisan as $tulisans){
			tulis($handle, $tulisan_i, $tulisans);
			$tulisan_i++;
		}


		printer_delete_font($font);
		/* ======\DATA PASIEN ====== */

		// echo "<pre>",print_r($handle),"</pre>"; //Resource id : 601 (ini echo webservice)

		$printer_end_page = printer_end_page($handle);
		$printer_end_doc = printer_end_doc($handle);
		$printer_close = printer_close($handle);

		$val = [
			"response" => [
				"status" => "selesai",
				"datapost" => $data,
			],
			"printer_end_page"=> $printer_end_page,
			"printer_end_doc" => $printer_end_doc,
			"printer_close" 	=> $printer_close,
		];

		echo json_encode($val); exit;

	}
	

	
	public function tulis_loop($handle_my, $x, $y, $row_dt_pasien, $text_my, $spacing_enter){
		//printer_draw_text($handle_my, $text_my, $x, $y+(75*($row_dt_pasien-1)) );
		printer_draw_text($handle_my, $text_my, $x, $y+($spacing_enter*($row_dt_pasien-1)) );
	}


	// public function cetak_skdp_langsung_termal(){
	public function skdp(){
		// $data = $this->input->post(NULL, TRUE);
		// $data['methodname']  = $this->router->fetch_method();
		// $data['printername'] = $this->m_it->printername($data['url'], $data['button_id'], $data['methodname']);

		$data = json_decode(file_get_contents( 'php://input' ),1);
		// if(my_ip()['client']== '192.168.1.93' ) $data['url'] = 'consolebox';
		if($data['ipclient']== '192.168.1.93' ) $data['url'] = 'consolebox';
		$data['methodname']  = __FUNCTION__;
		$data['printername'] = $this->m_pdo->printername($data['url'], $data['button_id'], $data['methodname']);
		
		// /* write the text to the print job */
		$handle = printer_open( $data['printername'] );
		// var_dump($handle);

		printer_set_option($handle, PRINTER_MODE, "RAW");
		printer_set_option($handle, PRINTER_PAPER_FORMAT, PRINTER_FORMAT_CUSTOM);
		printer_set_option($handle, PRINTER_PAPER_WIDTH, 70);
		printer_set_option($handle, PRINTER_SCALE, 50);
		//FIX//$handle = printer_open();
		printer_start_doc($handle, $data['methodname']);
		printer_start_page($handle);



		
		/* ====== data_skdp ====== */
		$font_h_pasien = 23; //25
		$font = printer_create_font("Lucida Console", $font_h_pasien, 12, PRINTER_FW_NORMAL, false, false, false, 0);
		printer_select_font($handle, $font);

		
		
		
		//jika  'U G D', dokter kosong
		// if($data['dpjp'] == "dr. Lucky Dana Victory"){
			// if($data['dpjp'] == "dr. Titia Rahmania,M.H.Kes"){
		if($data['dpjp'] == $this->dokter_igd) $data['dpjp'] = " ";		

		$tulisan = array(
				"                             ".$data['billing'],
				"",
				"",
				"",
				"",
				"",
				"",
				"No.RM     : ".$data['norm']."    (S.Kontrol/SKDP)**", //.$data['noSep'],
				"Nama      : ".$data['nama'],
				"Tgl.Lahir : ".$data['tglLahir'],
				"Klinik    : ".$data['provPerujuk'],
				"Diagnosa  : ",
				"",
				"1. Belum dapat dikembalikan ke FKTP:",
				"   1.",
				"   2.",
				"   3.",
				"2. Rencana tindak lanjut pada kunjungan",
				"   selanjutnya:",
				"   1.",
				"   2.",
				"   3.",
				"[ ] Surat keterangan ini digunakan untuk",
				"    kunjungan selanjutnya pada tanggal:",
				"",
				"[ ] Pasien dalam kondisi stabil dan dapat",
				"    melanjutkan perawatan kembali di FKTP",
				"",
				"[ ] Mohon skrining dan evaluasi ulang di FKTP",
				"    untuk kunjungan ke .......... pada tanggal:",
				"    ....... dengan diagnosa:...................",
				"",
				"           Sidoarjo, ".$data['tglSep'],
				"                  DPJP",
				"",
				"",
				"          ".$data['dpjp'],
				"",
				"* Mohon surat ini selalu dibawa saat berobat",
				"**Lingkari yang perlu",
			);

		$tulisan_i = 0;
		foreach($tulisan as $tulisans){
			// tulis($handle, 100, 200, $tulisan_i, $tulisans);
			$this->tulis_loop($handle, 10, 50, $tulisan_i, $tulisans, 28);
			$tulisan_i++;
		}

		printer_delete_font($font);
		/* ======\data_skdp ====== */

		//=====
		$margin_left_page = 50;
		$margin_top_page = 50;
		$font_height = 30;

		//printer_create_font(face, height, width, font_weight, italic, underline, strikeout, orientation);
		//$font = printer_create_font("Tahoma", 150, 80, 400, false, false, false, 0);
		$font = printer_create_font("Tahoma", 30, 12, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = "RS. Citra Medika";
		printer_draw_text($handle, $text, 30, 0 );	////printer_draw_text(printer_handle, text, x, y);
		printer_delete_font($font);


		$font = printer_create_font("Tahoma", 30, 12, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = "           SURAT KETERANGAN*"; //"NOMOR ANTRIAN";
		printer_draw_text($handle, $text, $margin_left_page, $margin_top_page);	////printer_draw_text(printer_handle, text, x, y);
		printer_delete_font($font);


		$font = printer_create_font("Tahoma", 30, 12, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = "       No : ".$data['noskdp'];
		printer_draw_text($handle, $text, $margin_left_page, ($margin_top_page+$font_height*1) );
		printer_delete_font($font);


		/* BARCODE NORM */
		$font = printer_create_font("Free 3 of 9", 40, 30, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = "*".$data['norm']."*";
		printer_draw_text($handle, $text, 5, 160);
		printer_delete_font($font);

		
		$font = printer_create_font("Tahoma", 60, 30, 600, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = $data['norm'];
		printer_draw_text($handle, $text, 280, 150 );
		printer_delete_font($font);
		//=====
		


		$printer_end_page = printer_end_page($handle);
		$printer_end_doc = printer_end_doc($handle);
		$printer_close = printer_close($handle);

		$val = [
			"response" => [
				"status" => "selesai",
				"datapost" => $data,
			],
			"printer_end_page"=> $printer_end_page,
			"printer_end_doc" => $printer_end_doc,
			"printer_close" 	=> $printer_close,
		];

		echo json_encode($val); exit;
	}
		
	
	public function antrian_rc(){
		$db = $this->m_pdo->select_nomor_antridaftar_max(date('Y-m-d'));
		// $db = $this->m_pdo->select_nomor_antridaftar_max(date($this->uri[1]));
		// echo json_encode([$db, my_uri()]); exit;
		
		$data = json_decode(file_get_contents( 'php://input' ),1);

		$consolebox = false;
		if($data['ipclient']== '192.168.1.93' ) $consolebox = true;

		if( $consolebox ) $data['url'] = 'consolebox';

		$data['methodname']  = __FUNCTION__;
		$data['printername'] = $this->m_pdo->printername($data['url'], $data['button_id'], $data['methodname']);
		$data['noantrian'] = $db['next']['nominal'];

		$ymd = date('Y-m-d');
		$his = date('H:i:s');
		
		// $data['user'] = 'admin';
		if(!isset($data['user'])) $data['user']='';
		$user = ( $consolebox )? 'consolebox' : $data['user'];

		// $tes = [my_ip(),$consolebox, $user, $data];
		// exit( json_encode($tes) );
		
		// GET USER SESSION
		// $insert = [
		// 	"lokasi" => 110,
		// 	"nomor"  => $data['noantrian'],
		// 	"mulai"  => $his,
		// 	"user"   => $user, // $this->session->userdata("username"), // cz yang klik px, maka user=''
		// 	"date"   => $ymd,
		// ];
		// $res_ins = $this->m_pdo->insert('antridaftar', $insert);
		
		
		// $insert = [
		// 	"nomor"  => $data['noantrian'],
		// 	"user"   => $user, // $this->session->userdata("username"), // cz yang klik px, maka user=''
		// ];

		// $res_ins = $this->m_pdo->insert('antridaftar', $insert);
		// // echo json_encode([$insert, $data, $_SESSION]); exit;
		// exit( json_encode([$insert, $data, $res_ins]) );


		$res_ins = $this->m_pdo->insert_antridaftar($data['noantrian'], $user);
		// exit( json_encode([$data, $res_ins]) );
		


		// // /* write the text to the print job */
		$handle = printer_open( $data['printername'] );

		printer_set_option($handle, PRINTER_MODE, "RAW");
		printer_set_option($handle, PRINTER_PAPER_FORMAT, PRINTER_FORMAT_CUSTOM);
		printer_set_option($handle, PRINTER_PAPER_WIDTH, 70);
		printer_set_option($handle, PRINTER_SCALE, 50);
		//FIX//$handle = printer_open();
		printer_start_doc($handle, $data['methodname']);
		printer_start_page($handle);



		$margin_left_page = 25;
		$margin_top_page = 50;
		$font_height = 30;

		//printer_create_font(face, height, width, font_weight, italic, underline, strikeout, orientation);
		//$font = printer_create_font("Tahoma", 150, 80, 400, false, false, false, 0);
		$font = printer_create_font("Tahoma", 30, 12, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = "NOMOR ANTRIAN PENDAFTARAN";
		printer_draw_text($handle, $text, $margin_left_page, 0);	////printer_draw_text(printer_handle, text, x, y);
		printer_delete_font($font);
		
		
		$font = printer_create_font("Tahoma", 30, 12, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = "RS. CITRA MEDIKA";
		printer_draw_text($handle, $text, $margin_left_page, (30*2));	////printer_draw_text(printer_handle, text, x, y);
		printer_delete_font($font);


		/*NO.RM*/
		// $font = printer_create_font("Lucida Console", 40, 25, 700, false, false, false, 0);
		$font = printer_create_font("Lucida Console", 120, 75, 700, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = $data['noantrian'];
		printer_draw_text($handle, $text, $margin_left_page, 120+(30*1) );
		printer_delete_font($font);



		$font = printer_create_font("Tahoma", 25, 12, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		// $text = "CETAK: ".$data['datetime']; //date('Y-m-d h:i:s')."  ".
		$text = "CETAK: ".$ymd." ".$his;
		printer_draw_text($handle, $text, $margin_left_page, 120+(30*6));	
		printer_delete_font($font);


		
		$printer_end_page = printer_end_page($handle);
		$printer_end_doc = printer_end_doc($handle);
		$printer_close = printer_close($handle);

		$val = [
			"response" => [
				"status" => "selesai",
				"datapost" => $data,
			],
			"printer_end_page"=> $printer_end_page,
			"printer_end_doc" => $printer_end_doc,
			"printer_close" 	=> $printer_close,
		];

		echo json_encode($val); exit;
	}


	public function tracer(){
		$data = json_decode(file_get_contents( 'php://input' ),1);

		if(!isset($data['user'])) $data['user'] = '';
		// get_px_rs_by_noka ==>>> dari wsrscm/model/get_px_rs_by_noka. utk variable tracer
		// px_rs = get_px_rs_by_noka
		$db_px = $this->m_pdo->laporan_pendaftaran_px_soft_by_bill($data['segment'], $data['NoBill'], $data['lokasikode']);
		// die(json_encode($db_px));
		if($db_px['metadata']['code']==200) $db_px = $db_px['response'];

		// $px_rs = $this->m_pdo->get_px_rs_by_norm($data['norm']); // BISA
		

		// if(isset($data['db_px_tracer'])) $db_px = $data['db_px_tracer'];

		$consolebox = false;
		if(isset($data['ipclient'])){
			if($data['ipclient']== '192.168.1.93' ) $consolebox = true;
			if( $consolebox ) $data['url'] = 'consolebox';
		}else $data['ipclient'] = '';
		
		$data['methodname']  = __FUNCTION__;
		$data['printername'] = $this->m_pdo->printername($data['url'], $data['button_id'], $data['methodname']);
		// die(json_encode([$data, $px_rs]));
		// die(json_encode($data));

		// // /* write the text to the print job */
		$handle = printer_open( $data['printername'] );

		printer_set_option($handle, PRINTER_MODE, "RAW");
		printer_set_option($handle, PRINTER_PAPER_FORMAT, PRINTER_FORMAT_CUSTOM);
		printer_set_option($handle, PRINTER_PAPER_WIDTH, 70);
		printer_set_option($handle, PRINTER_SCALE, 50);
		//FIX//$handle = printer_open();
		printer_start_doc($handle, $data['methodname']);
		printer_start_page($handle);
		
		// echo "<pre>",print_r($handle),"</pre>"; // exit; //#TEST


		$margin_left_page = 25;
		$margin_top_page = 50;
		$font_height = 30;

		//printer_create_font(face, height, width, font_weight, italic, underline, strikeout, orientation);
		//$font = printer_create_font("Tahoma", 150, 80, 400, false, false, false, 0);
		$font = printer_create_font("Tahoma", 30, 12, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		// $text = $data['NoBill']." / RAWAT JALAN / ".$data['st_px_baru_lama'];
		$nobill_lbl = ($data['segment'] == 'BOOK_RJ')? $db_px['nobill_booking'] : $data['NoBill'];
		$text = $nobill_lbl." / RAWAT JALAN / ".$db_px['statuspasien'];
		printer_draw_text($handle, $text, $margin_left_page, 0);	////printer_draw_text(printer_handle, text, x, y);
		printer_delete_font($font);


		/*NO.RM*/
		$font = printer_create_font("Lucida Console", 40, 25, 700, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = $db_px['norm']; // $data['NoRM'];
		printer_draw_text($handle, $text, $margin_left_page, 40);
		printer_delete_font($font);

		$font = printer_create_font("Tahoma", 25, 12, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = 'Antrian: '.$data['no_antrian'];
		printer_draw_text($handle, $text, $margin_left_page+280, 50);	
		printer_delete_font($font);


		/*BARCODE RM*/
		// $font = printer_create_font("Free 3 of 9", 40, 25, 400, false, false, false, 0);
		// printer_select_font($handle, $font);
		// $text = "*".$data['NoRM']."*";
		// printer_draw_text($handle, $text, $margin_left_page*6, 40); //($handle, $text, $margin_left_page, 170)
		// printer_delete_font($font);



		/* ====== DATA PASIEN ====== */
		$font_h_pasien = 30;
		$font = printer_create_font("Lucida Console", $font_h_pasien, 17, PRINTER_FW_NORMAL, false, false, false, 0);
		printer_select_font($handle, $font);

		function tulis($handle_my, $x, $y, $row_dt_pasien, $text_my){
			printer_draw_text($handle_my, $text_my, $x, $y+(30*($row_dt_pasien-1)) );
		}

		$tulisan = array(
				// $data['Nama'],
				// $data['TglLahir']." / ".$data['Sex']." / ".$data['umur']." TH"
				$db_px['nama'],
				$db_px['tgllahir']." / ".$db_px['jeniskelamin']." / ".$db_px['umur']." TH"
			);

		$tulisan_i = 0;
		foreach($tulisan as $tulisans){
			tulis($handle, 25, 120, $tulisan_i, $tulisans);
			$tulisan_i++;
		}

		printer_delete_font($font);
		/* ======\DATA PASIEN ====== */


		/* ====== ALAMAT PASIEN ====== */
		$font_h_pasien = 25;
		$font = printer_create_font("Lucida Console", $font_h_pasien, 12, PRINTER_FW_NORMAL, false, false, false, 0);
		printer_select_font($handle, $font);

		//$alamat_pasien = alamat_split("Jl. Melati, No.196, Ds. Padangan RT.10,RW.04, Kec. Tulangan, Kab. Sidoarjo.");
		$js_alamat =  alamat_split($db_px['alamat']); // alamat_split($data['Alamat']);
		$alamat_row1 = $js_alamat[0]['val'];
		///////////$alamat_row2 = $js_alamat[1]->val; //ini bikin error kalau kosong!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
		$alamat_row2 = "";
		$tulisan = [$alamat_row1, $alamat_row2];

		$tulisan_i = 0;
		foreach($tulisan as $tulisans){
			tulis($handle, 25, 180, $tulisan_i, $tulisans);
			$tulisan_i++;
		}

		printer_delete_font($font);
		/* ======\ALAMAT PASIEN ====== */



		/* ====== DATA KLINIK TUJUAN ====== */
		$font_h_pasien = 30;
		$font = printer_create_font("Lucida Console", $font_h_pasien, 17, PRINTER_FW_NORMAL, false, false, false, 0);
		printer_select_font($handle, $font);
		
		
		$tulisan = array(
				"------------------------------",
				$data['klinik'],
				$data['dokter']
			);

		$tulisan_i = 0;
		foreach($tulisan as $tulisans){
			tulis($handle, 25, 240, $tulisan_i, $tulisans);
			$tulisan_i++;
		}

		printer_delete_font($font);
		/* ======\DATA KLINIK TUJUAN ====== */


		$font = printer_create_font("Tahoma", 25, 12, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = date('Y-m-d h:i:s')."  ".$data['user'];
		// $text = date('Y-m-d h:i:s')."  ".$this->session->userdata("username");
		printer_draw_text($handle, $text, $margin_left_page, 120+(30*9));	
		printer_delete_font($font);


		/*BARCODE BILLING*/
		$font = printer_create_font("Free 3 of 9", 40, 25, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = "*".$data['NoBill']."*";
		printer_draw_text($handle, $text, $margin_left_page, 120+(30*10));
		printer_delete_font($font);

		$font = printer_create_font("Tahoma", 25, 12, 400, false, false, false, 0);
		printer_select_font($handle, $font);
		// $text = "Ket:".$data['ket'];
		$text = "Ket:".$db_px['keterangan'];
		printer_draw_text($handle, $text, $margin_left_page, 120+(30*11)+15);	
		printer_delete_font($font);

		// $font = printer_create_font("Tahoma", 25, 12, 400, false, false, false, 0);
		$font = printer_create_font("Lucida Console", 40, 25, 700, false, false, false, 0);
		printer_select_font($handle, $font);
		$text = $db_px['penanggung_ket']; // $data['penanggung_cm'];
		
		if($text == "" || $text == "-") $text = "UMUM";

		// $text .= " / ".$data['prb']." / ".$data['pdp'];
		$text .= " / ".$db_px['prb_str']." / ".$db_px['PDPStatus'];
		
		printer_draw_text($handle, $text, $margin_left_page, 120+(30*13)+15);	
		printer_delete_font($font);
		
		
		$printer_end_page = printer_end_page($handle);
		$printer_end_doc = printer_end_doc($handle);
		$printer_close = printer_close($handle);

		$val = [
			"response" => [
				"status" => "selesai",
				"datapost" => $data,
			],
			"printer_end_page"=> $printer_end_page,
			"printer_end_doc" => $printer_end_doc,
			"printer_close" 	=> $printer_close,
		];

		echo json_encode($val); exit;
	}

	public function px(){
		// $data = $this->input->post(NULL, TRUE);
		$data = json_decode(file_get_contents( 'php://input' ),1);
		// $val = $this->m_pdo->px($data['i']);
		$val = $this->m_pdo->px($_GET['i']);
		// print_r( $val);
		exit(json_encode($val));
		// echo json_encode(["iki_i"=>$data['i'] ]); exit;
	}
	
	
	public function px_get(){
		// $data = $this->input->post(NULL, TRUE);
		// $data = json_decode(file_get_contents( 'php://input' ),1);
		
		// $data['methodname']  = __FUNCTION__;
		// $data['printername'] = $this->m_pdo->printername($data['url'], $data['button_id'], $data['methodname']);
		// exit($_GET['i']);
		$val = $this->m_pdo->px($_GET['i']);
		// print_r( $val);
		echo json_encode($val); exit;
		// echo json_encode(["iki_i"=>$data['i'] ]); exit;
	}
}



$wsprinter = new wsprinter();
$uri = my_uri();
// cek apakah ada method bernama $uri[0]
if(method_exists($wsprinter, $uri[0] )) $wsprinter->$uri[0]();
else die("not found.");

?>