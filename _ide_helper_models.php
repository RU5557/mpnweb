<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string|null $kd_kanwil
 * @property string|null $kpp_adm
 * @property string|null $npwp
 * @property string|null $kpp
 * @property string|null $cabang
 * @property string|null $npwp15
 * @property string|null $nama_wp
 * @property string|null $no_pbk
 * @property string|null $ntpn
 * @property string|null $tgl_setor
 * @property int|null $thn_setor
 * @property int|null $bln_setor
 * @property int|null $thn_pajak
 * @property string|null $masa_pajak
 * @property numeric $jml_setor
 * @property string|null $kd_map
 * @property string|null $kd_bayar
 * @property string|null $fungsi
 * @property string|null $jenis
 * @property string|null $flag_skp
 * @property string|null $id_sbr_data
 * @property string|null $tipe
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereBlnSetor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereCabang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereFlagSkp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereFungsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereIdSbrData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereJmlSetor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereKdBayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereKdKanwil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereKdMap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereKpp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereKppAdm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereMasaPajak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereNamaWp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereNoPbk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereNpwp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereNpwp15($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereNtpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereTglSetor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereThnPajak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereThnSetor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DetilTransaksiWp whereTipe($value)
 */
	class DetilTransaksiWp extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kdmap newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kdmap newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kdmap query()
 */
	class Kdmap extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Klu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Klu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Klu query()
 */
	class Klu extends \Eloquent {}
}

namespace App\Models{
/**
 * @property string $npwp15
 * @property string|null $admin
 * @property string|null $npwp
 * @property string|null $kpp
 * @property string|null $cabang
 * @property string|null $nama
 * @property string|null $alamat
 * @property string|null $kelurahan
 * @property string|null $kecamatan
 * @property string|null $kota
 * @property string|null $propinsi
 * @property string|null $jenis
 * @property string|null $bentuk_hukum
 * @property string|null $status
 * @property string|null $klu
 * @property string|null $tanggal_daftar
 * @property string|null $tanggal_pkp
 * @property string|null $tanggal_pkp_cabut
 * @property string|null $nik
 * @property string|null $telp
 * @property string|null $nip_ar
 * @property string|null $nip_eks
 * @property string|null $nip_js
 * @property string|null $npwp16
 * @property-read \App\Models\Pegawai|null $ar
 * @property-read mixed $jenis_wp
 * @property-read mixed $nama_ar
 * @property-read mixed $nama_js
 * @property-read mixed $nama_wp
 * @property-read mixed $status_wp
 * @property-read \App\Models\Pegawai|null $js
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\DetilTransaksiWp> $transaksi
 * @property-read int|null $transaksi_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereBentukHukum($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereCabang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereKecamatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereKelurahan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereKlu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereKota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereKpp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereNipAr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereNipEks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereNipJs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereNpwp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereNpwp15($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereNpwp16($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp wherePropinsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereTanggalDaftar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereTanggalPkp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereTanggalPkpCabut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterfileWp whereTelp($value)
 */
	class MasterfileWp extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nip
 * @property int $tahun
 * @property string|null $kantor
 * @property string|null $nip2
 * @property string|null $nama
 * @property string|null $pangkat
 * @property string|null $seksi
 * @property string|null $jabatan
 * @property string|null $plh
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereKantor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereNip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereNip2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai wherePangkat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai wherePlh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereSeksi($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pegawai whereTahun($value)
 */
	class Pegawai extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $tanggal
 * @property numeric $nko
 * @property int $ranking_nasional
 * @property int $ranking_kanwil
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RollingText newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RollingText newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RollingText query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RollingText whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RollingText whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RollingText whereNko($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RollingText whereRankingKanwil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RollingText whereRankingNasional($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RollingText whereTanggal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RollingText whereUpdatedAt($value)
 */
	class RollingText extends \Eloquent {}
}

namespace App\Models{
/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seksi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seksi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Seksi query()
 */
	class Seksi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $thn_setor
 * @property int $bln_setor
 * @property string $kpp_adm
 * @property string $kd_map
 * @property string $kd_bayar
 * @property numeric $total_setor
 * @property int $total_transaksi
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SummaryPenerimaanKpp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SummaryPenerimaanKpp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SummaryPenerimaanKpp query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SummaryPenerimaanKpp whereBlnSetor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SummaryPenerimaanKpp whereKdBayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SummaryPenerimaanKpp whereKdMap($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SummaryPenerimaanKpp whereKppAdm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SummaryPenerimaanKpp whereThnSetor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SummaryPenerimaanKpp whereTotalSetor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SummaryPenerimaanKpp whereTotalTransaksi($value)
 */
	class SummaryPenerimaanKpp extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $tahun
 * @property numeric $target_kantor
 * @property numeric $target_ppm
 * @property numeric $target_pkm
 * @property numeric $target_pbp
 * @property numeric $target_pkm_pengawasan
 * @property numeric $target_pkm_pemeriksaan
 * @property numeric $target_pkm_penagihan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target whereTargetKantor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target whereTargetPbp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target whereTargetPkm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target whereTargetPkmPemeriksaan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target whereTargetPkmPenagihan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target whereTargetPkmPengawasan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target whereTargetPpm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Target whereUpdatedAt($value)
 */
	class Target extends \Eloquent {}
}

namespace App\Models{
/**
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 */
	class User extends \Eloquent {}
}

