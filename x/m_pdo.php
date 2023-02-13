<?php

require_once('site_helper.php');

class m_pdo{
  protected $conf_db_pdo = [
  	"main" => [
  		"hostname" => "192.168.100.19",
	    "dbname" 	 => "AXETADEV",
	    "username" => "sa",
	    "password" => "albolabris",
  	],
  ];

  public function pdo_declare(){
		// $host = "192.168.1.5";
    //   $dbname = "xlink";
    //   $username = "root";
    //   $password = "root";

    $host = $this->conf_db_pdo['main']['hostname'];
    $dbname = $this->conf_db_pdo['main']['dbname'];
    $username = $this->conf_db_pdo['main']['username'];
    $password = $this->conf_db_pdo['main']['password'];
    try {
        $db = new PDO("mysql:host={$host};dbname={$dbname}", $username, $password);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e){
        die("Connection error: " . $e->getMessage());
    }
    return $db;
  }

  public function tes($url, $button_id, $fx_name){
    return [$url, $button_id, $fx_name];
  }

  public function printername($url=null, $button_id=null, $fx_name=null){
    $db = $this->pdo_declare();

    $q = "SELECT * FROM xprinter 
      WHERE url=? AND button_id=? AND fx_name=?";

    $query = $db->prepare($q);
    $query->bindValue(1, $url );
    $query->bindValue(2, $button_id );
    $query->bindValue(3, $fx_name );
    $query->execute();
    $val = $query->fetch(PDO::FETCH_ASSOC);
    // return $val;
    if($val)
      return $val['printername'];
    	// return ["metadata"=> ["code"=>200, "status"=>"success", "message"=>"OK"], "response"=>$val];
    else
      return '';
   		// return ["metadata"=> ["code"=>201, "status"=>"failed", "message"=>"Tidak berhasil."], "response"=>null];    
  }
  
  
  public function select_nomor_antridaftar_max($date=null){
    $db = $this->pdo_declare();

    $q = "SELECT nomor from antridaftar WHERE date = ?	ORDER BY nomor desc	LIMIT 1";
    // $query = $this->db->query($q)->result_array();
    // $nominal = (count($query)>0) ?  (int)$query[0]['nomor'] : 0;

    $qu = $db->prepare($q);
    $qu->bindValue(1, $date );
    $qu->execute();
    $query = $qu->fetch(PDO::FETCH_ASSOC);
    // return $query;
    
    $nominal = (count($query)>0) ?  (int)$query['nomor'] : 0;
    // return $nominal;

    $res['now'] = nominal_terbilang($nominal);
    $res['next'] = nominal_terbilang($nominal+1);
    return $res;
  }
  
  
  public function insert_antridaftar($nomor=null, $user=null){
    try {
      $db = $this->pdo_declare();

      $qu = $db->prepare("INSERT INTO antridaftar (lokasi, nomor, mulai, user, date) VALUES (?, ?, ?, ?, ?)");
      $qu->bindValue(1, 110 ); // PDO::PARAM_INT
      $qu->bindValue(2, $nomor); // PDO::PARAM_INT
      $qu->bindValue(3, date('H:i:s') ); // , PDO::PARAM_STR
      $qu->bindValue(4, $user);
      $qu->bindValue(5, date('Y-m-d') ); //, PDO::PARAM_STR
  
      $qu->execute();
    } catch (PDOException $e) {
      // Note The Typecast To An Integer!
      throw new MyDatabaseException( $e->getMessage( ) , (int)$e->getCode( ) );
    }
   
  }
  
  
  public function insert($tablename=null, $arr=null){
    $db = $this->pdo_declare();

    $fields = array_keys($arr);

    $ifi = [];
    $iva = [];
    $ifi_max = 0;

    for ($i=0; $i < count( $fields ); $i++) {
      $fields_ask[] = `?`;
      $ifi[] = $i+2;
      $ifi_max = $ifi[$i];
    }

    for ($i=0; $i < count( $fields ); $i++) {
      $iva[] = $i+$ifi_max+1;
    }
      
    $fields_ask_str = join(',' , $fields_ask);

    $str = "INSERT INTO ? (".$fields_ask_str.") VALUES (".$fields_ask_str.")";
    // exit( json_encode([$str, $ifi, $iva, $fields, $arr ] ) );

    $qu = $db->prepare("INSERT INTO `?` (".$fields_ask_str.") VALUES (".$fields_ask_str.")");
    $qu->bindValue(1, $tablename);
    for ($i=0; $i < count($fields); $i++)
      $qu->bindValue($ifi[$i], $fields[$i]);
        
    for ($i=0; $i < count($fields); $i++)
      $qu->bindValue($iva[$i], $arr[$fields[$i]]);
    

    // insert one row
    // $name = 'one';
    // $value = 1;
    $qu->execute();
  }


  // public function get_px_rs_by_noka($noka=null){
  public function get_px_rs_by_norm($norm=null){
    $db = $this->pdo_declare();

    $q = "SELECT mp.* 
    , (SELECT IF((SELECT COUNT(td1.nobill) FROM fotrdaftar td1
    	LEFT JOIN fomstpasien mp1 ON mp1.NoRM=td1.NoRM
    	WHERE mp1.NoRM = ?)>0, 'LAMA', 'BARU') ) AS statuspasien
    , mp.AnggotaPerusahaan as penanggung_kd
    , if(w.nama='' or isnull(w.nama),'-',w.nama) as penanggung_ket
    ,	TIMESTAMPDIFF( YEAR, mp.tgllahir, CURDATE()) AS umur
    , IF(mp.PRB='1', 'PRB', '') AS prb_str
		, DATEDIFF(CURDATE(), mp.PDPDate) AS PDPSelisihHari
		, IF(mp.PDP=1, IF( DATEDIFF(CURDATE(), mp.PDPDate)<15,'Z038','') ,'') AS PDPStatus
    FROM fomstpasien mp 
		left join boptmstcustomer w on w.kode=mp.AnggotaPerusahaan
    WHERE mp.NoRM=?";
    $query = $db->prepare($q);
    $query->bindValue(1,$norm );
    $query->bindValue(2,$norm );
    $query->execute();
    $val = $query->fetch(PDO::FETCH_ASSOC);
    // return $val; exit;

    if($val){
    	// $get_db_px_tracer = 
	    $db_px_tracer = [
	    	"nobill_booking" => '??',
	    	"NoBill" => '???',
				"statuspasien" => $val['statuspasien'],
				"norm" => $val['NoRM'],
				"nama" => $val['Nama'],
				"tgllahir" => $val['TglLahir'],
				"jeniskelamin" => $val['Sex'],
				"umur" => $val['umur'],
				"alamat" => $val['Alamat'],
				"keterangan" => $val['Keterangan'],
				"penanggung_ket" => $val['penanggung_ket'],
				"prb_str" => $val['prb_str'],
				"PDPStatus" => $val['PDPStatus'],
	    ];
	    $val['db_px_tracer'] = $db_px_tracer;
    	return ["metadata"=> ["code"=>200, "status"=>"success", "message"=>"OK"], "response"=>$val];
    }
   	else
   		return ["metadata"=> ["code"=>201, "status"=>"failed", "message"=>"Nomor RM tidak terdaftar di rumah sakit."], "response"=>null];
  }
  


  public function laporan_pendaftaran_px_soft_by_bill($segment=null, $nobill=null, $kodelokasi=null){
    $db = $this->pdo_declare();
    
		switch ($segment) {
			case 'UG':
			case 'IGD':
				$q = "SELECT 
					    'IGD' as segment,'' as nourut,
					    trim(y.tanggalmasuk) as tanggalmasuk,
					    trim(y.tanggalkeluar) as tanggalkeluar,
					    time_format(y.jammasuk,'%T') as jammasuk, 
					    time_format(y.jamkeluar,'%T') as jamkeluar,
					    'IGD' as lokasi, 
							'IGD' as lokasikode,
							y.norm, a.nobill, z.Barcode as noka,
							z.nama, z.alamat, z.rt, z.rw, z.tgllahir, z.HP,
					    if(z.pendidikan='' or isnull(z.pendidikan),'-',
								if(z.pendidikan=1,'TK',
									if(z.pendidikan=2,'SD',
										if(z.pendidikan=3,'SLTP',
											if(z.pendidikan=4,'SLTA',
												if(z.pendidikan=5,'D1',
													if(z.pendidikan=6,'D2',
														if(z.pendidikan=7,'D3',
															if(z.pendidikan=8,'S1',
																if(z.pendidikan=9,'S2','S3')))))))))) as pendidikan, 
					    if(z.agama='' or isnull(z.agama),'-',if(z.agama='BD','BUDHA',if(z.agama='HD','HINDU',if(z.agama='IS','ISLAM',if(z.agama='KR','KRISTEN','KATHOLIK'))))) as agama,
					    (f.keterangan) as kelurahan, (g.keterangan) as kecamatan, (h.keterangan) as kota,
					    y.statusbl as statuspasien,
							y.sex as jeniskelamin,
							IF(y.sex='L', 'LAKI-LAKI','PEREMPUAN') as jeniskelamin_str,
							y.umurtahun as umur,
							#w.nama as perusahaanpenanggung,
							y.perusahaanpenanggung as penanggung_kd,
							w.nama as penanggung_ket,
							if(y.flagbill=0,'Aktif',if(y.flagbill=1,'keluar',if(y.flagbill=2,'Pending',if(y.flagbill=3,'Dummy','Batal')))) as statusbill, 
							y.keterangan, j.keterangan as caramasuk,
							y.asalPPK, i.keterangan as asalinstansi, a.user,
							d.keterangan as diagnosa,
							y.nosep, y.noskdp, 
							'' as rujukan, '' as tglrujukan,
							z.sukubangsa,
							'' as dokter_kd, '' as dokter_nama,
							z.PRB,
							IF(z.PRB='1', 'PRB', '') AS prb_str,
							z.PDP, z.PDPDate, 
							DATEDIFF(CURDATE(), z.PDPDate) AS PDPSelisihHari,
							IF(z.PDP=1, IF( DATEDIFF(CURDATE(), z.PDPDate)<15,'Z038','') ,'') AS PDPStatus
					from fotrdaftarugd a
					left join fotrdaftar y on y.nobill=a.nobill 
					left join fomstpasien z on z.norm=y.norm
					left join boptmstcustomer w on w.kode=y.perusahaanpenanggung 
					left join fomstdiagnosaawal d on d.kode=y.diagnosaawal
					left join fowilmstkelurahan f on z.kelurahan=f.kode AND f.KodeKota=z.Kota AND f.KodeKecamatan=z.Kecamatan
					left join fowilmstkecamatan g on z.kecamatan=g.kode AND g.Kodekota=z.Kota
					left join fowilmstkota h on z.kota=h.kode
					left join fotrasalpasien i on i.kode=y.asalinstansi
					left join forimstcaramasuk j on y.caramasuk=j.kode
					where y.diagnosaawal<>283 and y.FlagBill<>4
						and y.nobill = ?
				";
				$bind = [$nobill];
				// $query[$segment] = $this->db->query($q[$segment])->result_array();
				// array_push($query_all, $query[$segment]);

				break;

			case 'RJ':
				$q = "SELECT 
					    'RJ' as segment, 
					    right(nourut,3) as nourut,
					    trim(y.tanggalmasuk) as tanggalmasuk,
					    trim(y.tanggalkeluar) as tanggalkeluar,
					    time_format(y.jammasuk,'%T') as jammasuk,
							time_format(y.jamkeluar,'%T') as jamkeluar, 
							x.keterangan as lokasi,
							x.Kode as lokasikode,
							y.norm, a.nobill, z.Barcode as noka,
							z.nama, z.alamat,
							z.rt, z.rw, z.tgllahir, z.HP,
					    if(z.pendidikan='' or isnull(z.pendidikan),'-',
								if(z.pendidikan=1,'TK',
									if(z.pendidikan=2,'SD',
										if(z.pendidikan=3,'SLTP',
											if(z.pendidikan=4,'SLTA',
												if(z.pendidikan=5,'D1',
													if(z.pendidikan=6,'D2',
														if(z.pendidikan=7,'D3',
															if(z.pendidikan=8,'S1',
																if(z.pendidikan=9,'S2','S3')
								))))))))) as pendidikan,
					    if(z.agama='' or isnull(z.agama),'-',
								if(z.agama='BD','BUDHA',
									if(z.agama='HD','HINDU',
										if(z.agama='IS','ISLAM',
											if(z.agama='KR','KRISTEN','KATHOLIK'))))) as agama,
					    (f.keterangan) as kelurahan,
							(g.keterangan) as kecamatan,
							(h.keterangan) as kota,
					    y.statusbl as statuspasien,
							y.sex as jeniskelamin,
							IF(y.sex='L', 'LAKI-LAKI','PEREMPUAN') as jeniskelamin_str,
							y.umurtahun as umur,
							#if(w.nama='' or isnull(w.nama),'-',w.nama) as perusahaanpenanggung,
							y.perusahaanpenanggung as penanggung_kd,
							if(w.nama='' or isnull(w.nama),'-',w.nama) as penanggung_ket,
								if(y.flagbill=0,'Aktif',
									if(y.flagbill=1,'keluar',
										if(y.flagbill=2,'Pending',
											if(y.flagbill=3,'Dummy','Batal')))) as statusbill, 
							y.keterangan,
							j.keterangan as caramasuk,
							y.asalPPK,
							i.keterangan as asalinstansi,
							a.user,
							d.keterangan as diagnosa, y.nosep, y.noskdp, 
							a.Rujukan as rujukan, a.tglrujukan as tglrujukan,
							z.sukubangsa,
							a.Dokter as dokter_kd, mv.Nama as dokter_nama,
							z.PRB,
							IF(z.PRB='1', 'PRB', '') AS prb_str,
							z.PDP, z.PDPDate, 
							DATEDIFF(CURDATE(), z.PDPDate) AS PDPSelisihHari,
							IF(z.PDP=1, IF( DATEDIFF(CURDATE(), z.PDPDate)<15,'Z038','') ,'') AS PDPStatus
					from fotrdaftarrj a
					left join fomstlokasi x on x.kode=a.lokasi
					left join fotrdaftar y on y.nobill=a.nobill
					left join fomstpasien z on z.norm=y.norm
					left join boptmstcustomer w on w.kode=y.perusahaanpenanggung
					left join fomstdiagnosaawal d on d.kode=y.diagnosaawal
					left join fowilmstkelurahan f on z.kelurahan=f.kode AND f.KodeKota=z.Kota AND f.KodeKecamatan=z.Kecamatan
					left join fowilmstkecamatan g on z.kecamatan=g.kode AND g.Kodekota=z.Kota
					left join fowilmstkota h on z.kota=h.kode
					left join fotrasalpasien i on i.kode=y.asalinstansi
					left join forimstcaramasuk j on y.caramasuk=j.kode
					left join bohtmstvendor mv on mv.Kode=a.Dokter
					where y.diagnosaawal<>283 and y.FlagBill<>4
						and y.nobill = ?
						and a.Lokasi = ?
				";
				$bind = [$nobill, $kodelokasi];
				// $query[$segment] = $this->db->query($q[$segment])->result_array();
				// array_push($query_all, $query[$segment]);

				break;

			case 'RI':
				$q = "SELECT 
					    'RI' as segment, '' as nourut, 
					    trim(y.tanggalmasuk) as tanggalmasuk,
							trim(y.tanggalkeluar) as tanggalkeluar,
							time_format(y.jammasuk,'%T') as jammasuk,
							time_format(y.jamkeluar,'%T') as jamkeluar,
							'Rawat Inap' as lokasi,
							'Rawat Inap' as lokasikode,
							y.norm, a.nobill, z.Barcode as noka,
							z.nama, z.alamat,
							z.rt, z.rw, z.tgllahir, z.HP,
					    if(z.pendidikan='' or isnull(z.pendidikan),'-',
								if(z.pendidikan=1,'TK',
									if(z.pendidikan=2,'SD',
										if(z.pendidikan=3,'SLTP',
											if(z.pendidikan=4,'SLTA',
												if(z.pendidikan=5,'D1',
													if(z.pendidikan=6,'D2',
														if(z.pendidikan=7,'D3',
															if(z.pendidikan=8,'S1',
																if(z.pendidikan=9,'S2','S3')))))))))) as pendidikan,
					    if(z.agama='' or isnull(z.agama),'-',if(z.agama='BD','BUDHA',if(z.agama='HD','HINDU',if(z.agama='IS','ISLAM',if(z.agama='KR','KRISTEN','KATHOLIK'))))) as agama,
					    (f.keterangan) as kelurahan,
							(g.keterangan) as kecamatan, (h.keterangan) as kota,
					    y.statusbl as statuspasien, 
							y.sex as jeniskelamin, 
							IF(y.sex='L', 'LAKI-LAKI','PEREMPUAN') as jeniskelamin_str,
							y.umurtahun as umur,
							y.perusahaanpenanggung as penanggung_kd,
							w.nama as penanggung_ket,						
					    if(y.flagbill=0,'Aktif',if(y.flagbill=1,'keluar',if(y.flagbill=2,'Pending',if(y.flagbill=3,'Dummy','Batal')))) as statusbill,
							y.keterangan, j.keterangan as caramasuk,
							y.asalPPK, i.keterangan as asalinstansi, a.user,
							d.keterangan as diagnosa, y.nosep, y.noskdp, 
							'' as rujukan, '' as tglrujukan,
							z.sukubangsa,
							'' as dokter_kd, '' as dokter_nama,
							z.PRB,
							IF(z.PRB='1', 'PRB', '') AS prb_str,
							z.PDP, z.PDPDate, 
							DATEDIFF(CURDATE(), z.PDPDate) AS PDPSelisihHari,
							IF(z.PDP=1, IF( DATEDIFF(CURDATE(), z.PDPDate)<15,'Z038','') ,'') AS PDPStatus
					from fotrdaftarri a 
					left join fotrdaftar y on y.nobill=a.nobill
					left join fomstpasien z on z.norm=y.norm 
					left join boptmstcustomer w on w.kode=y.perusahaanpenanggung 
					left join fomstdiagnosaawal d on d.kode=y.diagnosaawal 
					left join fowilmstkelurahan f on z.kelurahan=f.kode AND f.KodeKota=z.Kota AND f.KodeKecamatan=z.Kecamatan
					left join fowilmstkecamatan g on z.kecamatan=g.kode AND g.Kodekota=z.Kota
					left join fowilmstkota h on z.kota=h.kode 
					left join fotrasalpasien i on i.kode=y.asalinstansi 
					left join forimstcaramasuk j on y.caramasuk=j.kode 
					where y.diagnosaawal<>283 and y.FlagBill<>4
						and y.nobill = ?
				";
				$bind = [$nobill];

				// $query[$segment] = $this->db->query($q[$segment])->result_array();
				// array_push($query_all, $query[$segment]);

				break;
			
			case 'BOOK_RJ':
				$norm = $nobill; //
				$q = "SELECT 
					    'BOOK_RJ' as segment, 
					    a.norequest as nourut,
					    '' as tanggalmasuk,
					    '' as tanggalkeluar,
					    '' as jammasuk,
							'' as jamkeluar, 
							x.keterangan as lokasi,
							x.Kode as lokasikode,
							a.norm, 
							CONCAT('BOOK', a.tgldaftar) AS nobill, 
							CONCAT('BOOK', a.tgldaftar) AS nobill_booking, 
							z.Barcode as noka,
							z.nama, z.alamat,
							z.rt, z.rw, z.tgllahir, z.HP,
					    if(z.pendidikan='' or isnull(z.pendidikan),'-',
								if(z.pendidikan=1,'TK', if(z.pendidikan=2,'SD',
										if(z.pendidikan=3,'SLTP', if(z.pendidikan=4,'SLTA',
												if(z.pendidikan=5,'D1', if(z.pendidikan=6,'D2',
														if(z.pendidikan=7,'D3', if(z.pendidikan=8,'S1',
																if(z.pendidikan=9,'S2','S3')
								))))))))) as pendidikan,
					    if(z.agama='' or isnull(z.agama),'-',
								if(z.agama='BD','BUDHA',
									if(z.agama='HD','HINDU',
										if(z.agama='IS','ISLAM',
											if(z.agama='KR','KRISTEN','KATHOLIK'))))) as agama,
					    (f.keterangan) as kelurahan,
							(g.keterangan) as kecamatan,
							(h.keterangan) as kota,
					    (SELECT IF((SELECT COUNT(nobill) FROM fotrdaftar WHERE norm = ?)>0, 'LAMA', 'BARU') ) AS statuspasien,
							z.Sex as jeniskelamin,
							TIMESTAMPDIFF( YEAR, z.tgllahir, CURDATE()) AS umur,
							a.penanggung as penanggung_kd,
							if(w.nama='' or isnull(w.nama),'-',w.nama) as penanggung_ket,
							if(a.flag=0,'Aktif', 'Nonaktif') as statusbill, 
							a.keterangan,
							-- j.keterangan as caramasuk,
							-- y.asalPPK,
							a.instansiket as asalinstansi,
							a.user,
							d.keterangan as diagnosa, 
							-- y.nosep, y.noskdp, 
							-- a.Rujukan as rujukan, a.tglrujukan as tglrujukan,
							z.sukubangsa,
							a.dokter as dokter_kd, mv.Nama as dokter_nama,
							z.PRB,
							IF(z.PRB='1', 'PRB', '') AS prb_str,
							z.PDP, z.PDPDate, 
							DATEDIFF(CURDATE(), z.PDPDate) AS PDPSelisihHari,
							IF(z.PDP=1, IF( DATEDIFF(CURDATE(), z.PDPDate)<15,'Z038','') ,'') AS PDPStatus
					from fotrbooking a
					left join fomstlokasi x on x.kode=a.lokasi
					-- left join fotrdaftar y on y.nobill=a.nobill
					left join fomstpasien z on z.norm=a.norm
					left join boptmstcustomer w on w.kode=a.penanggung
					left join fomstdiagnosaawal d on d.kode=a.diagnosa
					left join fowilmstkelurahan f on z.kelurahan=f.kode AND f.KodeKota=z.Kota AND f.KodeKecamatan=z.Kecamatan
					left join fowilmstkecamatan g on z.kecamatan=g.kode AND g.Kodekota=z.Kota
					left join fowilmstkota h on z.kota=h.kode
					-- left join fotrasalpasien i on i.kode=y.asalinstansi
					-- left join forimstcaramasuk j on y.caramasuk=j.kode
					left join bohtmstvendor mv on mv.Kode=a.dokter
					where 
						-- y.diagnosaawal<>283 and y.FlagBill<>4
						a.norm = ?
						and a.flag = 0
				";
				$bind = [$nobill, $nobill];
				break;

			case 'PRINT_FROM_POST':
					$q = "SELECT '' AS result" ;
					$bind=null;
				break;
			
			default:
					$q = "SELECT '' AS result" ;
					$bind=null;
				break;
    }


    $query = $db->prepare($q);
    for ($i=0; $i < count($bind); $i++)
      $query->bindValue(($i+1), $bind[$i] );
    
    // $query->bindValue(1,$norm );
    // $query->bindValue(2,$norm );
    $query->execute();
    $val = $query->fetch(PDO::FETCH_ASSOC);
    // return $val; exit;

    if($val) return ["metadata"=> ["code"=>200, "status"=>"success", "message"=>"OK"], "response"=>$val];
    else return ["metadata"=> ["code"=>201, "status"=>"failed", "message"=>"Nomor RM tidak terdaftar di rumah sakit."], "response"=>null];
  
  }

  
  

  
	// public function get_lokasi_by_kodelokasibpjs($kodelokasi=null){
  //   $db = $this->pdo_declare();

  //   $q = "SELECT ml.Kode, ml.Keterangan, ml.kdpoli_bpjs, p.nmpoli AS nmpoli_bpjs, p.no AS idpoli_bpjs, ml.durasi
  //   	FROM fomstlokasi ml
  //   	LEFT JOIN xbpjs_ref_poli p ON p.kdpoli=ml.kdpoli_bpjs
  //   	WHERE ml.kdpoli_bpjs=?";
  //   $query = $db->prepare($q);
  //   $query->bindValue(1, $kodelokasi );
  //   $query->execute();
  //   $val = $query->fetch(PDO::FETCH_ASSOC);
  //   // return $val;
  //   if($val)
  //   	return ["metadata"=> ["code"=>200, "status"=>"success", "message"=>"OK"], "response"=>$val];
  //  	else
  //  		return ["metadata"=> ["code"=>201, "status"=>"failed", "message"=>"Kode poli tidak terdaftar di rumah sakit."], "response"=>null];  
	// }


	public function px($i=null){
    $db = $this->pdo_declare();
		$i = intval($i);
		// return $i;
    // $lim = 10000;
    $lim = 1000;
    $q = "SELECT mp.NoRM, mp.Barcode, mp.NoIdentitas AS NIK, mp.Nama, mp.Sex
    , mp.Alamat , mp.TglLahir, mp.Telp, mp.HP
    FROM fomstpasien mp
		ORDER BY mp.NoRM ASC
    LIMIT ?,?
    ";
    // -- LIMIT 10000

    $query = $db->prepare($q);
    $query->bindValue(1, ($i*$lim) , PDO::PARAM_INT);
    $query->bindValue(2, $lim , PDO::PARAM_INT);
    // $query->bindValue(2, $button_id );
    // $query->bindValue(3, $fx_name );
    $query->execute();
    $val = $query->fetchAll(PDO::FETCH_ASSOC);
    // return $val;

    // print_r($val); exit;
    if($val) return $val;
    else return '';   
  }

}


// $p = new m_pdo();

// // cek apakah ada method bernama $uri[0]

// $url = 'bo/menu/receptionist/laporan/lap-daftarrj'; 
// $button_id = 'btn_cetak_antrian'; 
// $fx_name = 'termal_nomor_antrian_new';
// echo json_encode($p->printername($url, $button_id, $fx_name) );

// // echo "<br>";

?>