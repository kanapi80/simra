<style>
    #balik {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 30px;
        z-index: 99;
        font-size: 18px;
        border: none;
        outline: none;
        background: #3fbafe;
        color: white;
        cursor: pointer;
        padding: 15px;
        border-radius: 4px;
    }

    #balik:hover {
        background: radial-gradient(circle, #1fe4f5 10%, #3fbafe 90%);
        font-size: 19px;
    }
</style>
<button onClick="topFunction()" id="balik" title="Go to top"><i class="fa fa-angle-double-up"></i></button>

<?php
include("include/connect.php");
require_once('new_pagination.php');


/*if (!empty($_GET['tahun'])) {
    $tahuns = $_GET['tahun'];
} else {
    $tahuns = date('Y');
}
if (!empty($_GET['bulan'])) {
    $bulans = $_GET['bulan'];
} else {
    $bulans = date('m');
}*/
//$periode="$tahuns-$bulans"; 

$periode = $_GET['periode'];
//$search = ' WHERE f.keluarrs like "'.$periode.'%" ';
//$search = ' WHERE JM ="'.$periode.'" ';

$crb = "";
if (!empty($_GET['crb'])) {
	$crb = $_GET['crb'];
}

if ($crb != "") {
	$search = $search . " AND a.CARABAYAR ='" . $crb . "' ";
}
$dokter = "";
if (!empty($_GET['dokter'])) {
    $dokter = $_GET['dokter'];
}

if ($dokter != "") {
    $search = " WHERE a.KDDOKTER ='" . $dokter . "'  ";
}

?>


<div class="table-responsive">
            <form name="formsearch" method="get" action="<?php $_SERVER['PHP_SELF']; ?>">
            <table width="100%" cellpadding="0px" cellspacing="0" class="tb" style="font-size:10px" border="0px">
												<tr>
                                                <td width="12%" >Asuransi<br><select  name="crb" class="form-cari">
															<option value="">-</option>
															<?
															$mysql 	= mysql_query('select KODE,NAMA from m_carabayar WHERE KODE IN (2,3,4,9)  order by KODE asc');
															if (mysql_num_rows($mysql) > 0) {
																while ($dsql = mysql_fetch_array($mysql)) {

																	if ($crb == $dsql['KODE']) : $zx = 'selected="selected"';
																	else : $zx = '';
																	endif;
																	echo '<option value="' . $dsql['KODE'] . '" ' . $zx . '>' . $dsql['NAMA'] . '</option>';
																}
															}
															?>
														</select>
													</td>
                                                    <td width="12%" valign="bottom">Periode
					<select style="width:100%" name="periode" id="periode" class="form-cari">
						<option value="0">-</option>
						<?
						$datadokter = mysql_query("SELECT periode,ket FROM x_SettingKlaim  order by id ASC ") or die(mysql_error());
						while ($listdokter = mysql_fetch_array($datadokter)) {
						?>
							<option value="<? echo $listdokter['periode']; ?>" <?php if ($listdokter['periode'] == $_REQUEST['periode']) : echo 'selected="selected"';
																				endif; ?>><? echo $listdokter['periode']; ?>-<? echo $listdokter['ket']; ?></option>
						<?
						}
						?>
					</select>
				</td>
                        <!-- <td width="7%">Dokter<br />
                        <select style="height:22px;font-size:12px"  name="dokter" id="dokter" class="text" >
                          <option value="0">-</option>
                          <?
                            $datadokter = mysql_query("SELECT * FROM m_dokter where KDPROFESI IN(0,1) AND st_aktif ='0' AND NAMADOKTER!='Residen, dr' AND KDDOKTER!='99' AND NAMADOKTER!='-' order by NAMADOKTER ASC ") or die(mysql_error());
                            while ($listdokter = mysql_fetch_array($datadokter)) {
                            ?>
                          <option value="<? echo $listdokter['KDDOKTER']; ?>" <?php if ($listdokter['KDDOKTER'] == $_REQUEST['dokter']) : echo 'selected="selected"';
                                                                                endif; ?> ><? echo $listdokter['NAMADOKTER']; ?></option>
                          <?
                            }
                            ?>
                        </select></td>-->
                        <td width="16%" valign="bottom"><input type="hidden" name="link" value="<?= $_GET['link']; ?>" />
                        <button type="submit" class="btn btn-outline-success btn-sm">&nbsp;<i class="fa fa-search"></i>&nbsp;Cari&nbsp;</button>
                        </td>
                        <td width="38%">&nbsp;</td>
                    </tr>
                </table>
            </form>
        </div>
        <br />

        <div class="table-responsive">
        <table width="100%" cellpadding="0px" cellspacing="0"  id="myTable1" style="font-size:10px" border="1px">
                <tr >
                    <th width="2%" rowspan="2">NO</th>
                    <th width="13%" rowspan="2">RUANGAN</th>
                    <th width="7%" rowspan="2">JASA ORIGINAL </th>
                    <th colspan="2">JASA RSUD </th>
                    <th colspan="4">JASA PELAKSANA </th>
                </tr>
                <tr align="center">
                    <th width="7%">Operasional</th>
                    <th width="7%">Manajemen</th>
                    <th width="7%">Medis Sp </th>
                    <th width="7%">Medis<br> Umum </th>
                    <th width="7%">Para<br>medis</th>
                    <th width="7%">Keber<br>samaan</th>
                </tr>
                <?php
                $NO = 0;
                $warnaGenap = "#fafafa";
                $warnaGanjil = "#fff";
                $query = "select 
SUM(a.TARIFRS) AS JUM,
SUM(a.JASA_SARANA) AS JS_SR,
SUM(a.JASA_PELAYANAN) AS JS_PL,
SUM(a.jasa_rs) AS JS_RS,SUM(a.jasa_pelaksana) AS JS_PEL,
SUM(a.operasional) AS OPERASIONALS,SUM(a.manajemen) AS MANAJEMENS,
SUM(a.MEDIS) AS MEDISS,
SUM(a.MEDIS_UM) AS MEDIS_UM,
SUM(a.PARAMEDIS) AS PARAMEDIS,
SUM(a.COSTSHARING) AS KEBERSAMAAN,c.ket_ruang
from t_billranap a
left join m_pasien b ON b.NOMR = a.NOMR 
left join m_ruang c ON c.no =a.KDPOLY 
left join m_dokter d ON d.KDDOKTER = a.KDDOKTER
left join m_tarif2012 e ON e.kode_tindakan = a.KODETARIF
left join t_admission f ON f.id_admission = a.IDXDAFTAR
WHERE   f.JM = '" . $periode . "' and  a.CARABAYAR = '" . $crb . "'   and  a.UNIT ='19' and a.JASA_PELAYANAN !='0.00'  and a.KODETARIF !='07' GROUP BY c.ket_ruang order by c.ket_ruang ASC";
                $jalankan = mysql_query($query) or die('Error');
                while ($hasil = mysql_fetch_array($jalankan)) {
                    $no++;
                ?>
                    <tr <? echo "class =";
                        $count++;
                        if ($count % 2) {
                            echo "tr4";
                        } else {
                            echo "tr2";
                        }
                        ?>>
                        <td align="center"><? echo $no; ?></td>

                        <td align="left"><? echo $hasil['ket_ruang']; ?></td>
                        <td align="right"><? echo number_format($hasil['JS_PL'], 2, ',', '.'); ?></td>
                        <td align="right"><? echo number_format($hasil['OPERASIONALS'], 2, ',', '.');  ?></td>
                        <td align="right"><? echo number_format($hasil['MANAJEMENS'], 2, ',', '.');  ?></td>
                        <td align="right"><? echo number_format($hasil['MEDISS'], 2, ',', '.'); ?></td>
                        <td align="right"><? echo number_format($hasil['MEDIS_UM'], 2, ',', '.'); ?></td>
                        <td align="right"><? echo number_format($hasil['PARAMEDIS'], 2, ',', '.');  ?></td>
                        <td align="right"><? echo number_format($hasil['KEBERSAMAAN'], 2, ',', '.');  ?></td>
                    </tr> <?    } ?>
                <?php
                $data_rekap2 = mysql_query("select SUM(a.TARIFRS) AS JUM,SUM(a.JASA_SARANA) AS JS_SR,SUM(a.JASA_PELAYANAN) AS JS_PL,SUM(a.jasa_rs) AS JS_RS,SUM(a.jasa_pelaksana) AS JS_PEL,
SUM(a.operasional) AS OPERASIONAL,SUM(a.manajemen) AS MANAJEMEN,
SUM(a.MEDIS) AS MEDIS,
SUM(a.MEDIS_UM) AS MEDIS_UM,
SUM(a.PARAMEDIS) AS PARAMEDIS,
SUM(a.COSTSHARING) AS KEBERSAMAAN
				 from t_billranap a
				 left join m_pasien b ON b.NOMR = a.NOMR 
				 left join m_ruang c ON c.no =a.KDPOLY 
				 left join m_dokter d ON d.KDDOKTER = a.KDDOKTER
				 left join m_tarif2012 e ON e.kode_tindakan = a.KODETARIF
				 left join t_admission f ON f.id_admission = a.IDXDAFTAR
				WHERE   f.JM ='" . $periode . "'  and  a.CARABAYAR = '" . $crb . "'   and  a.UNIT ='19' and a.JASA_PELAYANAN !='0.00' and a.KODETARIF !='07'");
                $hasil2 = mysql_fetch_array($data_rekap2);

                ?>

<tr style="font-weight:bold;background:#36bea6;color:#FFFFFF">
                    <td align="center"></td>
                    <td align="center">&nbsp;</td>
                    <td align="right"><? echo number_format($hasil2['JS_PL'], 2, ',', '.'); ?></td>
                    <td align="right"><? echo number_format($hasil2['OPERASIONAL'], 2, ',', '.');  ?></td>
                    <td align="right"><? echo number_format($hasil2['MANAJEMEN'], 2, ',', '.');  ?></td>
                    <td align="right"><? echo number_format($hasil2['MEDIS'], 2, ',', '.'); ?></td>
                    <td align="right"><? echo number_format($hasil2['MEDIS_UM'], 2, ',', '.'); ?></td>
                    <td align="right"><? echo number_format($hasil2['PARAMEDIS'], 2, ',', '.'); ?></td>
                    <td align="right"><? echo number_format($hasil2['KEBERSAMAAN'], 2, ',', '.'); ?></td>
                </tr>
            </table>
            <!-- <blockquote><a href="kasir_rekap/export_detail_jasa_poli.php?poly=<?= $_GET['poly'] ?>&dokter=<?= $_GET['dokter'] ?>&carabayar=<?= $_GET['carabayar'] ?>&tgl_reg=<?= $_GET['tgl_reg'] ?>&tgl_reg2=<?= $_GET['tgl_reg2'] ?>">Export Excel</a>  </blockquote>-->
        </div>
    </div>
</div>
</div>