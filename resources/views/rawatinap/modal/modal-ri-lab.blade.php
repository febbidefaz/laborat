    {{-- MODAL INSERT / UPDATE LAB --}}
    <div class="modal fade" id="modalFormLab" tabindex="-1">

        <div class="modal-dialog modal-xl modal-dialog-scrollable">

            <div class="modal-content border-0 shadow-lg">

                {{-- HEADER --}}
                <div class="modal-header modal-modern">

                    <div>
                        <h5 class="modal-title font-weight-bold mb-0" id="modalLabTitle">
                            <i class="fas fa-vials mr-2"></i>
                            Laboratorium
                        </h5>

                        <small id="modalLabSubTitle">
                            Input pemeriksaan laboratorium
                        </small>
                    </div>

                    <button type="button" class="close text-white" data-dismiss="modal">

                        <span>&times;</span>

                    </button>

                </div>


                {{-- BODY --}}
                <div class="modal-body bg-light">

                    <input type="hidden" id="labMode" value="insert">

                    <input type="hidden" id="labIDLab">


                    {{-- ====================================================== --}}
                    {{-- BAGIAN ATAS --}}
                    {{-- ====================================================== --}}

                    <div class="card border-0 shadow-sm mb-3">

                        <div class="card-body py-3">

                            <div class="row">

                                {{-- =========================
                                     KOLOM 1 - PASIEN
                                ========================== --}}
                                <div class="col-md-3">

                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label col-form-label-sm">
                                            ID Reg
                                        </label>

                                        <div class="col-sm-5">
                                            <input type="text" id="labIDReg" class="form-control form-control-sm"
                                                value="{{ $pasien->ID ?? '' }}" readonly>
                                        </div>
                                    </div>


                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label col-form-label-sm">
                                            No RM
                                        </label>

                                        <div class="col-sm-5">
                                            <input type="text" class="form-control form-control-sm"
                                                value="{{ $pasien->RegNum ?? '' }}" readonly>
                                        </div>
                                    </div>


                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label col-form-label-sm">
                                            Pasien
                                        </label>

                                        <div class="col-sm-9">
                                            <input type="text" class="form-control form-control-sm"
                                                value="{{ $pasien->Nama ?? '' }}" readonly>
                                        </div>
                                    </div>


                                    <div class="form-group row mb-0">
                                        <label class="col-sm-3 col-form-label col-form-label-sm">
                                            Alamat
                                        </label>

                                        <div class="col-sm-9">
                                            <input type="text" id="labAlamat" class="form-control form-control-sm"
                                                value="{{ $pasien->Addr ?? '' }}" readonly>
                                        </div>
                                    </div>

                                    <div class="form-group row mb-3">

                                        <label class="col-sm-3 col-form-label col-form-label-sm">
                                            Umur
                                        </label>

                                        <div class="col-sm-8">

                                            @php
                                                $umurTahun = '';
                                                $umurBulan = '';
                                                $umurHari = '';

                                                if (!empty($pasien->Tanggal_Lahir)) {
                                                    $lahir = \Carbon\Carbon::parse($pasien->Tanggal_Lahir);

                                                    $sekarang = \Carbon\Carbon::now();

                                                    $diff = $lahir->diff($sekarang);

                                                    $umurTahun = $diff->y;
                                                    $umurBulan = $diff->m;
                                                    $umurHari = $diff->d;
                                                }
                                            @endphp


                                            <div class="umur-modern">

                                                <div class="umur-box">
                                                    <div class="umur-value" id="labUmurTahun">
                                                        {{ $umurTahun }}
                                                    </div>
                                                    <div class="umur-label">
                                                        Tahun
                                                    </div>
                                                </div>

                                                <div class="umur-box">
                                                    <div class="umur-value" id="labUmurBulan">
                                                        {{ $umurBulan }}
                                                    </div>
                                                    <div class="umur-label">
                                                        Bulan
                                                    </div>
                                                </div>

                                                <div class="umur-box">
                                                    <div class="umur-value" id="labUmurHari">
                                                        {{ $umurHari }}
                                                    </div>
                                                    <div class="umur-label">
                                                        Hari
                                                    </div>
                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>


                                {{-- =========================
                                     KOLOM 2 - DOKTER / WAKTU
                                ========================== --}}
                                <div class="col-md-3 border-left">

                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label col-form-label-sm">
                                            Dokter
                                        </label>

                                        <div class="col-sm-9">
                                            <select id="labDokter" class="form-control form-control-sm">

                                                <option value="">
                                                    -- Pilih Dokter --
                                                </option>

                                                @foreach ($dokterList ?? [] as $d)
                                                    <option value="{{ $d->ID }}">
                                                        {{ $d->DokterAlias }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>


                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label col-form-label-sm">
                                            Dr. Sp.PK
                                        </label>

                                        <div class="col-sm-9">
                                            <select id="labSpPK" class="form-control form-control-sm">

                                                <option value="">
                                                    -- Pilih Dokter Sp.PK --
                                                </option>

                                                @foreach ($dokterSpPKList ?? [] as $d)
                                                    <option value="{{ $d->ID }}">
                                                        {{ $d->Dokter }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>


                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label col-form-label-sm">
                                            Tanggal
                                        </label>

                                        <div class="col-sm-5">
                                            <input type="date" id="labTgl" class="form-control form-control-sm"
                                                value="{{ date('Y-m-d') }}" readonly>
                                        </div>
                                    </div>


                                    <div class="form-group row mb-2">
                                        <label class="col-sm-3 col-form-label col-form-label-sm">
                                            Jam Ambil
                                        </label>

                                        <div class="col-sm-5">
                                            <input type="time" id="labJamAmbil" class="form-control form-control-sm">
                                        </div>
                                    </div>


                                    <div class="form-group row mb-0">
                                        <label class="col-sm-3 col-form-label col-form-label-sm">
                                            Jam Check
                                        </label>

                                        <div class="col-sm-5">
                                            <input type="time" id="labJamCheck" class="form-control form-control-sm">
                                        </div>
                                    </div>

                                </div>


                                {{-- =========================
                                     KOLOM 3 - ORDER
                                ========================== --}}
                                <div class="col-md-3 border-left">

                                    <div class="form-group row mb-2">

                                        <div class="col-sm-4">

                                            <button type="button"
                                                class="btn btn-outline-primary btn-sm w-100 text-left"
                                                onclick="previewSPLab()" title="Print SP Laboratorium">

                                                <i class="fas fa-print mr-1"></i>
                                                No SP

                                            </button>

                                        </div>

                                        <div class="col-sm-8">

                                            <div class="d-flex">

                                                <select id="labNoSP" class="form-control form-control-sm mr-1"
                                                    onchange="loadInfoSPLab()">

                                                    <option value="">
                                                        -- Pilih No SP --
                                                    </option>

                                                    @foreach ($spLabList ?? [] as $sp)
                                                        <option value="{{ $sp->NO }}">

                                                            {{ ($sp->Proses ?? 0) == 1 ? '✓' : '✕' }}

                                                            {{ $sp->NO }} -
                                                            {{ $sp->Nama }} -
                                                            {{ $sp->Register }}

                                                        </option>
                                                    @endforeach

                                                </select>

                                                <button type="button" class="btn btn-outline-success btn-sm"
                                                    onclick="tarikSPLab()" title="Tarik pemeriksaan dari SP">

                                                    <i class="fas fa-download"></i>

                                                </button>

                                                {{-- SELESAI --}}
                                                <button type="button" class="btn btn-outline-danger btn-sm"
                                                    onclick="selesaiSPLab()" title="Selesaikan SP Laboratorium">

                                                    <i class="fas fa-check"></i>

                                                </button>

                                            </div>

                                        </div>

                                    </div>

                                    <div class="form-group row mb-2">

                                        <div class="col-sm-4">

                                            <button type="button"
                                                class="btn btn-outline-primary btn-sm w-100 text-left"
                                                onclick="previewSPPA()" title="Print SPPA">

                                                <i class="fas fa-print mr-1"></i>
                                                No SPPA

                                            </button>

                                        </div>

                                        <div class="col-sm-8">

                                            <select id="labNoSPPA" class="form-control form-control-sm"
                                                onchange="loadInfoSPPA()">

                                                <option value="">
                                                    -- Pilih No SPPA --
                                                </option>

                                                @foreach ($spPaList ?? [] as $sp)
                                                    <option value="{{ $sp->NO }}">
                                                        {{ $sp->NO }} -
                                                        {{ $sp->Nama }} -
                                                        {{ $sp->Register }}
                                                    </option>
                                                @endforeach

                                            </select>

                                        </div>

                                    </div>

                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label col-form-label-sm">
                                            Kelas/Ruang
                                        </label>

                                        <div class="col-sm-8">
                                            <input type="text" id="labRuang" class="form-control form-control-sm"
                                                value="{{ $pasien->RoomName ?? '' }}" readonly>
                                        </div>
                                    </div>

                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label col-form-label-sm">
                                            PX Rujukan
                                        </label>

                                        <div class="col-sm-8">
                                            <input type="text" id="labRuang" class="form-control form-control-sm"
                                                value="{{ $pasien->Rujukan ?? '' }}" readonly>
                                        </div>
                                    </div>

                                    {{-- BB --}}
                                    <div class="form-group row mb-2 align-items-center">

                                        <label class="col-sm-4 col-form-label col-form-label-sm mb-0">
                                            BB
                                        </label>

                                        <div class="col-sm-4">

                                            <div class="input-group input-group-sm">

                                                <input type="text" id="labBB"
                                                    class="form-control form-control-sm text-center" readonly>

                                                <div class="input-group-append">

                                                    <span class="input-group-text">
                                                        kg
                                                    </span>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </div>


                                {{-- =========================
                                     KOLOM 4 - INFORMASI
                                ========================== --}}
                                <div class="col-md-3 border-left">

                                    <div class="form-group mb-1">

                                        <div class="font-weight-bold text-primary mb-1">
                                            INFO PEMERIKSAAN
                                        </div>

                                        <textarea id="labInfoPemeriksaan" class="form-control form-control-sm" rows="5"
                                            placeholder="Belum ada pemeriksaan dipilih."></textarea>

                                    </div>


                                    {{-- DIAGNOSA --}}
                                    <div class="form-group row mb-1 align-items-center">

                                        <label class="col-sm-3 col-form-label col-form-label-sm mb-0">
                                            Diagnosa
                                        </label>

                                        <div class="col-sm-9">

                                            <textarea id="labDiagnosa" class="form-control form-control-sm" rows="2"
                                                placeholder="Diagnosa / keterangan klinis"></textarea>
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>



                    {{-- ====================================================== --}}
                    {{-- MASTER / TAMBAH PEMERIKSAAN --}}
                    {{-- ====================================================== --}}

                    <div class="card border-0 shadow-sm mb-3">

                        <div class="card-header py-2 bg-white">


                            <div class="d-flex align-items-center flex-wrap" style="gap:5px;">

                                <strong>
                                    <i class="fas fa-microscope text-primary mr-1"></i>
                                    Pemeriksaan
                                </strong>

                                {{-- GRUP KELOMPOK --}}
                                <select id="labGrupKelompok" class="form-control form-control-sm mr-2"
                                    style="width:150px;" onchange="pilihGrupKelompokLab()">

                                    <option value="">
                                        -- Group Kelompok --
                                    </option>

                                </select>


                                {{-- GRUP PEMERIKSAAN --}}
                                <select id="labGrup" class="form-control form-control-sm mr-2" style="width:200px;"
                                    onchange="pilihGrupLab()">

                                    <option value="">
                                        -- Group Pemeriksaan --
                                    </option>

                                </select>

                                {{-- NO LIS --}}
                                <select id="labNoLIS" class="form-control form-control-sm mr-1"
                                    style="width:220px;">

                                    <option value="">
                                        -- Pilih No LIS --
                                    </option>

                                </select>


                                {{-- TARIK LIS --}}
                                <button type="button" class="btn btn-outline-info btn-sm mr-2"
                                    onclick="tarikLISLab()" title="Tarik hasil dari LIS">

                                    <i class="fas fa-download mr-1"></i>
                                    Tarik LIS

                                </button>

                                {{-- INSERT LIS SABA --}}
                                <button type="button" id="btnInsertLISSaba"
                                    class="btn btn-outline-primary btn-sm mr-2" data-mode="insert"
                                    onclick="prosesLISSaba()" title="Kirim order pemeriksaan ke LIS Saba">

                                    <i class="fas fa-paper-plane mr-1"></i>
                                    Insert LIS Saba

                                </button>

                                {{-- TARIK LIS SABA --}}
                                <button type="button" id="btnTarikLISSaba"
                                    class="btn btn-outline-primary btn-sm mr-2" onclick="tarikLISSaba()"
                                    title="Tarik hasil pemeriksaan dari LIS Saba">

                                    <i class="fas fa-download mr-1"></i>
                                    Tarik LIS Saba

                                </button>

                                {{-- MANUAL --}}
                                <button type="button" class="btn btn-success btn-sm" id="btnTambahPemeriksaanLab"
                                    onclick="tambahBarisLab()">

                                    <i class="fas fa-plus-circle mr-1"></i>
                                    Tambah

                                </button>

                            </div>


                        </div>


                        <div class="card-body p-0">

                            <div class="table-responsive lab-table-wrap">

                                <table class="table table-bordered table-sm mb-0" id="tableInputLab">

                                    <thead>

                                        <tr>
                                            <th width="40" class="text-center">
                                                No
                                            </th>

                                            <th style="min-width:180px">
                                                Pemeriksaan
                                            </th>

                                            <th style="min-width:150px">
                                                Hasil
                                            </th>

                                            <th style="min-width:390px">
                                                Nilai Normal
                                            </th>

                                            <th width="50" class="text-center">
                                                Nilai Kritis
                                            </th>

                                            <th width="100" class="text-right">
                                                Biaya
                                            </th>

                                            <th width="50" class="text-right">
                                                Pot %
                                            </th>

                                            <th width="50" class="text-right">
                                                Pot
                                            </th>

                                            <th width="70" class="text-center">
                                                File
                                            </th>

                                            <th width="45" class="text-center">
                                                Aksi
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody id="labItemBody">

                                        <tr id="labEmptyRow">

                                            <td colspan="9" class="text-center text-muted py-4">

                                                <i class="fas fa-vials fa-2x mb-2 d-block"></i>

                                                Belum ada pemeriksaan laboratorium.

                                            </td>

                                        </tr>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>



                    {{-- ====================================================== --}}
                    {{-- BAGIAN BAWAH --}}
                    {{-- ====================================================== --}}

                    <div class="card border-0 shadow-sm mb-0">

                        <div class="card-body py-2">

                            <div class="row align-items-center">

                                {{-- NOTE SP.PK --}}
                                <div class="col-md-5">

                                    <div class="form-group row mb-0 align-items-center">

                                        <label class="col-sm-3 col-form-label col-form-label-sm mb-0">
                                            Note dr. Sp.PK
                                        </label>

                                        <div class="col-sm-9">

                                            <input type="text" id="labNoteSpPk"
                                                class="form-control form-control-sm"
                                                placeholder="Catatan dokter Sp.PK">

                                        </div>

                                    </div>

                                </div>


                                {{-- VERIFIKATOR / ANALIS --}}
                                <div class="col-md-3">

                                    <div class="form-group row mb-0 align-items-center">

                                        <label class="col-sm-3 col-form-label col-form-label-sm mb-0">
                                            Verif
                                        </label>

                                        <div class="col-sm-9">

                                            <select id="labAnalis" class="form-control form-control-sm">

                                                <option value="">
                                                    -- Pilih Verif --
                                                </option>

                                            </select>

                                        </div>

                                    </div>

                                </div>


                                {{-- TOTAL POTONGAN --}}
                                <div class="col-md-2">

                                    <div class="d-flex align-items-center">

                                        <small class="text-muted mr-2">
                                            Total Potongan
                                        </small>

                                        <div class="font-weight-bold text-danger" id="labTotalPotongan">

                                            Rp 0

                                        </div>

                                    </div>

                                </div>


                                {{-- TOTAL BIAYA --}}
                                <div class="col-md-2">

                                    <div class="d-flex align-items-center">

                                        <small class="text-muted mr-2">
                                            Total Biaya
                                        </small>

                                        <div class="font-weight-bold text-primary" id="labTotalBiaya">

                                            Rp 0

                                        </div>

                                    </div>

                                </div>

                            </div>
                        </div>

                    </div>

                </div>


                {{-- FOOTER --}}
                <div class="modal-footer bg-white d-flex justify-content-end">

                    <button type="button" class="btn btn-secondary btn-sm mr-2" data-dismiss="modal">

                        <i class="fas fa-times mr-1"></i>
                        Tutup

                    </button>

                    <button type="button" class="btn btn-info btn-sm mr-2" id="btnPrintLab"
                        onclick="printLabModal()">

                        <i class="fas fa-print mr-1"></i>
                        Print Lab

                    </button>


                    <button type="button" class="btn btn-danger btn-sm mr-2" id="btnHapusLab" style="display:none;"
                        onclick="hapusLab()">

                        <i class="fas fa-trash mr-1"></i>
                        Hapus

                    </button>


                    <button type="button" class="btn btn-success btn-sm" id="btnSimpanLab" onclick="simpanLab()">

                        <i class="fas fa-save mr-1"></i>
                        Simpan

                    </button>

                </div>

            </div>

        </div>

    </div>
