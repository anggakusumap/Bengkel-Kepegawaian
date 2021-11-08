<?php

namespace App\Http\Controllers\Kepegawaian\Masterdata;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kepegawaian\Pegawairequest;
use App\Model\Inventory\Retur\Retur;
use App\Model\Kepegawaian\Jabatan;
use App\Model\Kepegawaian\Pegawai;
use App\Model\Payroll\MasterPTKP;
use App\Model\SingleSignOn\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MasterdatapegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pegawai = Pegawai::with([
            'Jabatan'
        ])->join('tb_kepeg_master_jabatan', 'tb_kepeg_master_pegawai.id_jabatan', 'tb_kepeg_master_jabatan.id_jabatan')
        ->where('nama_jabatan', '!=', 'Owner')->get();

        $jumlah_pegawai = Pegawai::with([
            'Jabatan'
        ])->join('tb_kepeg_master_jabatan', 'tb_kepeg_master_pegawai.id_jabatan', 'tb_kepeg_master_jabatan.id_jabatan')
        ->where('nama_jabatan', '!=', 'Owner')->count();

        return view('pages.kepegawaian.masterdata.pegawai.pegawai', compact('pegawai','jumlah_pegawai'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pegawai = Pegawai::with([
            'jabatan'])->get();

        $jabatan = Jabatan::where('nama_jabatan', '!=', 'Owner')->get();
        $ptkp = MasterPTKP::get();

        $id = Pegawai::getId();
        foreach($id as $value);
        $idlama = $value->id_pegawai;
        $idbaru = $idlama + 1;
        $blt = date('Ym');

        $kode_pegawai = $blt.$idbaru;
        
        return view('pages.kepegawaian.masterdata.pegawai.create', compact('pegawai','jabatan','kode_pegawai','ptkp')); 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Pegawairequest $request)
    {
        $pegawai = new Pegawai;
        $pegawai->id_bengkel = $request['id_bengkel'] = Auth::user()->id_bengkel;
        $pegawai->nik_pegawai = $request->nik_pegawai;
        $pegawai->npwp_pegawai = $request->npwp_pegawai;
        $pegawai->id_jabatan = $request->id_jabatan;
        $pegawai->nama_pegawai = $request->nama_pegawai;
        $pegawai->nama_panggilan = $request->nama_panggilan;
        $pegawai->tempat_lahir = $request->tempat_lahir;
        $pegawai->tanggal_lahir = $request->tanggal_lahir;
        $pegawai->jenis_kelamin = $request->jenis_kelamin;
        $pegawai->alamat = $request->alamat;
        $pegawai->kota_asal = $request->kota_asal;
        $pegawai->no_telp = $request->no_telp;
        $pegawai->agama = $request->agama;
        $pegawai->pendidikan_terakhir = $request->pendidikan_terakhir;
        $pegawai->tanggal_masuk = $request->tanggal_masuk;
        $pegawai->id_ptkp= $request->id_ptkp;

        $pegawai->save();

        return redirect()->route('pegawai.index')->with('messageberhasil','Data Pegawai Berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id_pegawai)
    {
        $item = Pegawai::with('ptkp')->findOrFail($id_pegawai);
        $jabatan = Jabatan::all();
        
        return view('pages.kepegawaian.masterdata.pegawai.detail',[
            'item' => $item,
            'jabatan' => $jabatan
        ]);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id_pegawai)
    {
        $item = Pegawai::with('ptkp')->findOrFail($id_pegawai);
        $jabatan = Jabatan::all();
        $ptkp = MasterPTKP::get();

        return view('pages.kepegawaian.masterdata.pegawai.edit',[
            'item' => $item,
            'jabatan' => $jabatan,
            'ptkp' => $ptkp
        ]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id_pegawai)
    {
        $item = Pegawai::findOrFail($id_pegawai);
        $data = $request->all();

        $item->update($data);

        
        return redirect()->route('pegawai.index')->with('messageberhasil','Data Pegawai Berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id_pegawai)
    {
        $pegawai = Pegawai::findOrFail($id_pegawai);
        $user = User::where('id_pegawai', $id_pegawai)->first();
        $role = Role::where('id_user','=', $user->id)->first();

        $pegawai->delete();
        $user->delete();
        $role->delete();

        return redirect()->route('pegawai.index')->with('messagehapus','Data Pegawai Berhasil dihapus');
    }
}
