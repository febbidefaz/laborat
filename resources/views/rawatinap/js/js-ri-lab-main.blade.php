<script>
    // Operasi Lab
    function openInsertLab() {

        if (sudahBayar) {

            notifWarning(
                'PX sudah bayar di Kasir, Silahkan hub Kasir',
                'Tambah pemeriksaan laboratorium tidak dapat dilakukan karena transaksi sudah dibayar.'
            );

            return;
        }

        // lanjut kode lama...

        setLabDetailMode(false);
        loadGrupLab();
        loadGrupKelompokLab();
        loadNoLISLab('');
        loadAnalisLab('');

        $('#labMode').val('insert');
        $('#labIDLab').val('');

        $('#modalLabTitle').html(
            '<i class="fas fa-vials mr-2"></i>' +
            'Tambah Laboratorium ' +
            '<span class="badge badge-light ml-2">' +
            'Input pemeriksaan baru' +
            '</span>'
        );

        $('#modalLabSubTitle').text('');

        $('#labTgl').val('{{ date('Y-m-d') }}');
        $('#labDokter').val('');
        $('#labSpPK').val('');
        $('#labNoSP').val('');
        $('#labBB').val('');
        $('#labNoSPPA').val('');
        $('#labInfoPemeriksaan').val('');
        $('#labNote').val('');
        $('#labDiagnosa').val('');
        $('#labNoteSpPk').val('');

        labItems = [];

        renderLabItems();

        $('#btnHapusLab').hide();

        $('#modalFormLab').modal('show');

        const now = new Date();

        const jamSekarang =
            String(now.getHours()).padStart(2, '0') +
            ':' +
            String(now.getMinutes()).padStart(2, '0');

        $('#labJamAmbil').val(jamSekarang);
        $('#labJamCheck').val('');
    }

    function openDetailLab(idLab) {

        // ============================================================
        // MODE UPDATE
        // ============================================================

        loadGrupLab();
        loadGrupKelompokLab();
        loadNoLISLab();

        $('#labMode').val('update');
        $('#labIDLab').val(idLab);


        $('#modalLabTitle').html(
            '<i class="fas fa-vials mr-2"></i>' +
            'Edit Laboratorium ' +
            '<small class="ml-2 font-weight-normal">' +
            'ID Lab : ' + idLab +
            '</small>'
        );

        $('#modalLabSubTitle').text('');


        labItems = [];
        renderLabItems();


        $('#btnSimpanLab').hide();
        $('#btnHapusLab').hide();
        $('#btnTambahPemeriksaanLab').hide();


        $('#modalFormLab').modal('show');


        $.ajax({

            url: "{{ url('/lab/detail') }}/" + idLab,

            type: "GET",


            success: function(res) {

                // ====================================================
                // VALIDASI RESPONSE
                // ====================================================

                if (!res.success) {

                    notifError(
                        'Gagal',
                        res.message ??
                        'Data laboratorium tidak ditemukan.'
                    );

                    return;
                }


                const h = res.header;


                // ====================================================
                // HEADER
                // ====================================================

                $('#labTgl').val(
                    formatTanggalInputLab(h.TLab)
                );

                $('#labDokter').val(
                    h.DokterID ?? ''
                );

                $('#labSpPK').val(
                    h.spPK ?? ''
                );

                $('#labNoSP').val(
                    h.NoSP ?? ''
                );

                $('#labNoSPPA').val(
                    h.NoSPPA ?? ''
                );

                loadNoLISLab(
                    h.No_Lab_LIS ?? ''
                );

                loadAnalisLab(
                    h.verif ?? ''
                );

                // ====================================================
                // INFO PEMERIKSAAN
                // ====================================================

                $('#labInfoPemeriksaan').val('');
                $('#labDiagnosa').val('');
                $('#labBB').val('');


                if (h.NoSP) {

                    loadInfoSPLab();

                } else if (h.NoSPPA) {

                    loadInfoSPPA();
                }


                // ====================================================
                // JAM AMBIL
                // ====================================================

                if (h.Jam_ambil) {

                    let jamAmbil =
                        String(h.Jam_ambil);


                    if (jamAmbil.includes('T')) {

                        jamAmbil =
                            jamAmbil
                            .split('T')[1]
                            .substring(0, 5);

                    } else {

                        const match =
                            jamAmbil.match(
                                /(\d{2}):(\d{2})/
                            );


                        jamAmbil =
                            match ?
                            match[1] + ':' + match[2] :
                            '';
                    }


                    $('#labJamAmbil').val(
                        jamAmbil
                    );

                } else {

                    $('#labJamAmbil').val('');
                }


                // ====================================================
                // JAM CHECK
                // ====================================================

                if (h.Jam_check) {

                    let jamCheck =
                        String(h.Jam_check);


                    if (jamCheck.includes('T')) {

                        jamCheck =
                            jamCheck
                            .split('T')[1]
                            .substring(0, 5);

                    } else {

                        const match =
                            jamCheck.match(
                                /(\d{2}):(\d{2})/
                            );


                        jamCheck =
                            match ?
                            match[1] + ':' + match[2] :
                            '';
                    }


                    $('#labJamCheck').val(
                        jamCheck
                    );

                } else {

                    $('#labJamCheck').val('');
                }


                $('#labNoteSpPk').val(
                    h.Note ?? ''
                );


                // ====================================================
                // DETAIL PEMERIKSAAN
                // ====================================================

                labItems =
                    (res.items || []).map(
                        function(item) {

                            return {

                                LabID: item.LabID,

                                Prep_ID: item.Prep_ID,

                                Nama: item.Nama ?? '-',

                                Levels: item.Levels ?? '',

                                IsOk: Number(
                                    item.IsOk || 0
                                ),

                                NorL: item.NorL ?? '',

                                Biaya: parseFloat(
                                    item.Biaya || 0
                                ),

                                Pot: parseFloat(
                                    item.Pot || 0
                                ) * 100,

                                JasaPelayanan: parseFloat(
                                    item.JasaPelayanan || 0
                                ),

                                JasaPerujuk: parseFloat(
                                    item.JasaPerujuk || 0
                                ),

                                JasaRS: parseFloat(
                                    item.JasaRS || 0
                                ),

                                Pembaca: parseFloat(
                                    item.Pembaca || 0
                                ),

                                Administrasi: parseFloat(
                                    item.Administrasi || 0
                                ),

                                Reagen: parseFloat(
                                    item.Reagen || 0
                                ),

                                JumlahFile: parseInt(item.JumlahFile || 0),

                            };
                        }
                    );


                renderLabItems();


                // ====================================================
                // STATUS TRANSAKSI
                // ====================================================

                if (sudahBayar) {

                    // =================================================
                    // SUDAH BAYAR -> VIEW ONLY
                    // =================================================

                    setLabDetailMode(true);


                    $('#btnSimpanLab').hide();

                    $('#btnHapusLab').hide();

                    $('#btnTambahPemeriksaanLab').hide();


                    $('#labGrupKelompok')
                        .prop('disabled', true);

                    $('#labGrup')
                        .prop('disabled', true);

                    $('#labNoLIS')
                        .prop('disabled', true);


                    $('#btnInsertLISSaba')
                        .prop('disabled', true);

                    $('#btnTarikLISSaba')
                        .prop('disabled', true);


                    // TIDAK PERLU CEK LIS SABA
                    // karena transaksi sudah terkunci


                    $('#modalLabTitle').html(
                        '<i class="fas fa-lock mr-2"></i>' +
                        'Detail Laboratorium ' +
                        '<small class="ml-2 font-weight-normal">' +
                        'ID Lab : ' +
                        idLab +
                        ' - Sudah Dibayar' +
                        '</small>'
                    );


                } else {

                    // =================================================
                    // BELUM BAYAR -> EDIT BOLEH
                    // =================================================

                    setLabDetailMode(false);


                    $('#btnSimpanLab')
                        .show()
                        .html(
                            '<i class="fas fa-save mr-1"></i> Simpan Perubahan'
                        );


                    $('#btnHapusLab')
                        .show();


                    $('#btnTambahPemeriksaanLab')
                        .show();


                    $('#labGrupKelompok')
                        .prop('disabled', false);

                    $('#labGrup')
                        .prop('disabled', false);

                    $('#labNoLIS')
                        .prop('disabled', false);


                    // CEK STATUS LIS SABA HANYA JIKA BELUM BAYAR
                    cekLISSaba(idLab);
                }

            },


            error: function(xhr) {

                console.log(
                    xhr.responseText
                );


                notifError(
                    'Gagal',
                    xhr.responseJSON?.message ??
                    'Gagal mengambil data laboratorium.'
                );
            }

        });
    }

    function setLabDetailMode(detail = true) {

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        $('#labDokter').prop(
            'disabled',
            detail
        );

        $('#labSpPK').prop(
            'disabled',
            detail
        );

        $('#labNoSP').prop(
            'disabled',
            detail
        );

        $('#labNoSPPA').prop(
            'disabled',
            detail
        );

        $('#labJamAmbil').prop(
            'disabled',
            detail
        );

        $('#labJamCheck').prop(
            'disabled',
            detail
        );

        $('#labDiagnosa').prop(
            'disabled',
            detail
        );

        $('#labInfoPemeriksaan').prop(
            'disabled',
            detail
        );

        $('#labNoteSpPk').prop(
            'disabled',
            detail
        );


        /*
        |--------------------------------------------------------------------------
        | TOMBOL
        |--------------------------------------------------------------------------
        */

        if (detail) {

            $('#btnSimpanLab').hide();
            $('#btnHapusLab').hide();

            $('#btnTambahPemeriksaanLab').hide();

        } else {

            $('#btnSimpanLab').show();

            $('#btnTambahPemeriksaanLab').show();
        }
    }

    function openEditLab(idLab, tanggal, dokterID, note) {

        $('#labMode').val('update');
        $('#labIDLab').val(idLab);

        $('#modalLabTitle').html(
            '<i class="fas fa-vials mr-2"></i>Edit Laboratorium'
        );

        $('#modalLabSubTitle').text(
            'ID Lab : ' + idLab
        );

        $('#labTgl').val(tanggal);
        $('#labDokter').val(dokterID);
        $('#labNote').val(note || '');

        $('#btnHapusLab').show();

        $('#modalFormLab').modal('show');
    }

    function renderLabItems() {

        const body = $('#labItemBody');
        const isDetail = $('#labMode').val() === 'detail';

        body.empty();

        if (labItems.length === 0) {

            body.html(`
                <tr id="labEmptyRow">
                    <td colspan="10"
                        class="text-center text-muted py-4">

                        <i class="fas fa-vials fa-2x mb-2 d-block"></i>

                        Belum ada pemeriksaan laboratorium.

                    </td>
                </tr>
            `);

            $('#labTotalBiaya').text('Rp 0');
            $('#labTotalPotongan').text('Rp 0');

            return;
        }


        let totalBiaya = 0;
        let totalPotongan = 0;

        labItems.forEach(function(item, index) {

            let biaya = parseFloat(item.Biaya || 0);
            let pot = parseFloat(item.Pot || 0);

            let potongan = biaya * (pot / 100);

            totalBiaya += biaya;
            totalPotongan += potongan;


            body.append(`
                <tr>

                    <td class="text-center">
                        ${index + 1}
                    </td>

                    <td>
                        ${item.Nama ?? '-'}
                    </td>

                    <td>

            ${
                isDetail

                ? `<div class="font-weight-bold">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            ${item.Levels ?? '-'}
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        </div>`

                : `<input type="text"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            class="form-control form-control-sm"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            value="${item.Levels ?? ''}"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            onchange="ubahLabItem(${index}, 'Levels', this.value)">`
            }

            </td>

        <td>
            ${item.NorL ?? '-'}
        </td>

        <td class="text-center">

            <input
                type="checkbox"
                class="checkbox-kritis"
                ${Number(item.IsOk || 0) === 1 ? 'checked' : ''}
                onchange="ubahLabItem(
                    ${index},
                    'IsOk',
                    this.checked ? 1 : 0
                )"
            >

            ${
                Number(item.IsOk || 0) === 1
                    ? '<div class="small text-danger font-weight-bold">KRITIS</div>'
                    : ''
            }

        </td>

        <td class="text-right">
            Rp ${formatRupiahLab(biaya)}
        </td>
        <td>

        ${
            isDetail

            ? `<div class="text-right">
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    ${Number(pot).toLocaleString('id-ID')} %
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                </div>`

            : `<input type="number"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    class="form-control form-control-sm text-right"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    value="${pot}"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    min="0"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    max="100"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    step="0.01"
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    onchange="ubahLabItem(${index}, 'Pot', this.value)">`
        }

        </td>

        <td class="text-right">
            Rp ${formatRupiahLab(potongan)}
        </td>

        {{-- FOTO --}}
        <td class="text-center">

        ${
            $('#labIDLab').val()

            ? (
                parseInt(item.JumlahFile || 0) > 0

                ? `
                                                                        <button type="button"
                                                                            class="btn btn-success btn-sm"
                                                                            onclick="openFotoLab(
                                                                                ${index},
                                                                                ${item.Prep_ID ?? (index + 1)}
                                                                            )"
                                                                            title="${item.JumlahFile} file tersimpan">

                                                                            <i class="fas fa-file-alt mr-1"></i>
                                                                            ${item.JumlahFile}

                                                                        </button>
                                                                    `

                : `
                                                                        <button type="button"
                                                                        class="btn btn-outline-primary btn-sm"
                                                                            onclick="openFotoLab(
                                                                                ${index},
                                                                                ${item.Prep_ID ?? (index + 1)}
                                                                            )"
                                                                            title="Belum ada file">

                                                                            <i class="fas fa-file-upload"></i>

                                                                        </button>
                                                                    `
            )

            : `
                                                                    <button type="button"
                                                                        class="btn btn-outline-secondary btn-sm"
                                                                        disabled
                                                                        title="Simpan Laboratorium terlebih dahulu">

                                                                        <i class="fas fa-file-upload"></i>

                                                                    </button>
                                                                `
        }

                    </td>
                    <td class="text-center">

                        <button type="button"
                            class="btn btn-danger btn-sm"
                            onclick="hapusLabItem(${index})">

                            <i class="fas fa-trash"></i>

                        </button>

                    </td>

                </tr>
            `);

        });


        $('#labTotalBiaya').text(
            'Rp ' + formatRupiahLab(totalBiaya)
        );

        $('#labTotalPotongan').text(
            'Rp ' + formatRupiahLab(totalPotongan)
        );
    }

    function ubahLabItem(index, field, value) {

        if (!labItems[index]) return;

        labItems[index][field] = value;

        renderLabItems();
    }

    function hapusLabItem(index) {
        labItems.splice(index, 1);

        renderLabItems();
    }

    function tambahBarisLab() {

        $('#modalPilihLab').modal('show');

        $('#masterLabBody').html(`
            <tr>
                <td colspan="4" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin mr-1"></i>
                    Memuat pemeriksaan...
                </td>
            </tr>
        `);

        $.ajax({

            url: "{{ route('lab.master') }}",
            type: "GET",

            success: function(res) {

                if (!res.success) {
                    notifError(
                        'Gagal',
                        'Master laboratorium tidak ditemukan.'
                    );
                    return;
                }

                let html = '';

                res.data.forEach(function(item) {

                    html += `
                <tr class="master-lab-row">

                    <td class="text-center">

                        <button type="button"
                            class="btn btn-success btn-sm"
                            onclick='pilihPemeriksaanLab(
                                ${JSON.stringify(item)}
                            )'>

                            <i class="fas fa-plus"></i>

                        </button>

                    </td>

                    <td class="nama-pemeriksaan">
                        ${item.Perik ?? '-'}
                    </td>

                    <td>
                        ${item.NorL ?? '-'}
                    </td>

                    <td class="text-right">
                        <span id="hargaMasterLab_${item.ID}">
                            -
                        </span>
                    </td>

                </tr>
            `;
                });

                $('#masterLabBody').html(html);

            },

            error: function(xhr) {

                console.log(xhr.responseText);

                notifError(
                    'Gagal',
                    xhr.responseJSON?.message ??
                    'Gagal mengambil master pemeriksaan.'
                );

            }

        });
    }

    function pilihPemeriksaanLab(item) {

        // Jangan memasukkan pemeriksaan yang sama dua kali
        const sudahAda = labItems.some(function(x) {
            return Number(x.LabID) === Number(item.ID);
        });

        if (sudahAda) {

            notifWarning(
                'Sudah Ditambahkan',
                'Pemeriksaan ' + item.Perik + ' sudah ditambahkan.'
            );

            return;
        }

        $.ajax({

            url: "{{ route('lab.harga') }}",

            type: "GET",

            data: {
                IDREG: $('#labIDReg').val(),
                LabID: item.ID
            },

            success: function(res) {

                if (!res.success || !res.data) {

                    notifWarning(
                        'Tarif Tidak Ditemukan',
                        'Tarif pemeriksaan tidak ditemukan.'
                    );

                    return;
                }

                const h = res.data;

                labItems.push({

                    LabID: item.ID,

                    Nama: item.Perik,

                    NorL: item.NorL ?? '',

                    Levels: '',

                    IsOk: 0,

                    Biaya: parseFloat(h.Biaya || 0),

                    Pot: 0,

                    JasaPelayanan: parseFloat(h.JasaPelayanan || 0),

                    JasaPerujuk: parseFloat(h.JasaPerujuk || 0),

                    JasaRS: parseFloat(h.JasaRS || 0),

                    Pembaca: parseFloat(h.Pembaca || 0),

                    Administrasi: parseFloat(h.Administrasi || 0),

                    Reagen: parseFloat(h.Reagen || 0)

                });

                renderLabItems();

                $('#modalPilihLab').modal('hide');
            },

            error: function(xhr) {

                console.log(xhr.responseText);

                notifError(
                    'Gagal',
                    xhr.responseJSON?.message ??
                    'Gagal mengambil tarif pemeriksaan.'
                );
            }

        });
    }

    // Search Master Pemeriksaan Lab
    $(document).on(
        'keyup',
        '#cariMasterLab',
        function() {

            const keyword =
                $(this).val().toLowerCase();

            $('#masterLabBody .master-lab-row')
                .each(function() {

                    const nama =
                        $(this)
                        .find('.nama-pemeriksaan')
                        .text()
                        .toLowerCase();

                    $(this).toggle(
                        nama.includes(keyword)
                    );

                });
        }
    );

    function simpanLab() {

        if (labItems.length === 0) {

            notifWarning(
                'Pemeriksaan belum dipilih',
                'Pilih minimal satu pemeriksaan laboratorium.'
            );

            return;
        }


        const noSP =
            $('#labNoSP').val();

        const dokterID =
            $('#labDokter').val();


        // ====================================================
        // DOKTER WAJIB HANYA JIKA TIDAK ADA NoSP
        // ====================================================

        if (!noSP && !dokterID) {

            notifWarning(
                'Dokter belum dipilih',
                'Dokter wajib dipilih jika No SP tidak digunakan.'
            );

            return;
        }


        const mode =
            $('#labMode').val();

        const idLab =
            $('#labIDLab').val();


        if (
            mode === 'update' &&
            !idLab
        ) {

            notifWarning(
                'Data tidak lengkap',
                'ID Laboratorium tidak ditemukan.'
            );

            return;
        }


        let urlSimpan;


        if (mode === 'update') {

            urlSimpan =
                "{{ route('lab.update') }}";

        } else {

            urlSimpan =
                "{{ route('lab.simpan') }}";
        }


        $('#btnSimpanLab')
            .prop('disabled', true)
            .html(`
                <i class="fas fa-spinner fa-spin mr-1"></i>
                Menyimpan...
            `);


        $.ajax({

            url: urlSimpan,

            type: "POST",

            contentType: "application/json",

            data: JSON.stringify({

                _token: "{{ csrf_token() }}",

                IDLab: idLab,

                IDREG: $('#labIDReg').val(),

                Tanggal: $('#labTgl').val(),

                DokterID: $('#labDokter').val(),

                SpPK: $('#labSpPK').val(),

                NoSP: $('#labNoSP').val(),

                NoSPPA: $('#labNoSPPA').val(),

                NoLIS: $('#labNoLIS').val(),

                JamAmbil: $('#labJamAmbil').val(),

                JamCheck: $('#labJamCheck').val(),

                Diagnosa: $('#labDiagnosa').val(),

                NoteSpPK: $('#labNoteSpPk').val(),

                Verif: $('#labAnalis').val(),

                items: labItems

            }),


            success: function(res) {

                if (!res.success) {

                    notifError(
                        'Gagal',
                        res.message ??
                        'Gagal menyimpan laboratorium.'
                    );

                    return;
                }


                // ====================================================
                // INSERT BARU
                // ====================================================

                if (mode === 'insert') {

                    if (!res.IDLab) {

                        notifError(
                            'Gagal',
                            'ID Laboratorium tidak diterima dari server.'
                        );

                        return;
                    }


                    $('#labIDLab')
                        .val(res.IDLab);


                    $('#labMode')
                        .val('update');


                    if (res.DokterID) {

                        $('#labDokter')
                            .val(res.DokterID);
                    }


                    $('#modalLabTitle')
                        .html(
                            '<i class="fas fa-vials mr-2"></i>' +
                            'Edit Laboratorium ' +
                            '<small class="ml-2 font-weight-normal">' +
                            'ID Lab : ' +
                            res.IDLab +
                            '</small>'
                        );


                    $('#btnHapusLab')
                        .show();


                    notifSuccess(
                        'Laboratorium berhasil ditambahkan'
                    );

                    $('#modalFormLab').modal('hide');

                    setTimeout(function() {
                        location.reload();
                    }, 500);

                    return;

                } else {

                    if (res.DokterID) {

                        $('#labDokter')
                            .val(res.DokterID);
                    }


                    notifSuccess(
                        'Laboratorium berhasil diperbarui'
                    );

                    $('#modalFormLab').modal('hide');

                    setTimeout(function() {
                        location.reload();
                    }, 500);
                }

            },


            error: function(xhr) {

                console.log(
                    xhr.responseText
                );


                notifError(
                    'Gagal',
                    xhr.responseJSON?.message ??
                    'Gagal menyimpan data laboratorium.'
                );
            },


            complete: function() {

                const currentMode =
                    $('#labMode').val();


                $('#btnSimpanLab')
                    .prop('disabled', false)
                    .html(
                        currentMode === 'update' ?
                        '<i class="fas fa-save mr-1"></i> Simpan Perubahan' :
                        '<i class="fas fa-save mr-1"></i> Simpan'
                    );
            }

        });
    }

    async function hapusLab() {

        const idLab =
            $('#labIDLab').val();

        if (!idLab) {

            notifWarning(
                'Data tidak ditemukan',
                'ID Laboratorium tidak ditemukan.'
            );

            return;
        }


        const result =
            await konfirmasiAksi({

                title: 'Hapus Laboratorium?',

                text: 'Data Laboratorium <b>' +
                    idLab +
                    '</b> akan dihapus.',

                confirmText: 'Ya, Hapus',

                icon: 'warning'
            });


        if (!result.isConfirmed) {
            return;
        }


        // AJAX hapus Lab Anda di sini
    }

    function reloadItemLab(idLab) {

        $.ajax({

            url: "{{ url('/lab/detail') }}/" + idLab,

            type: "GET",

            success: function(res) {

                if (!res.success) {

                    notifError(
                        'Gagal',
                        'Gagal memuat ulang pemeriksaan.'
                    );

                    return;
                }


                labItems =
                    (res.items || []).map(
                        function(item) {

                            return {

                                LabID: item.LabID,

                                Prep_ID: item.Prep_ID,

                                Nama: item.Nama ?? '-',

                                Levels: item.Levels ?? '',

                                NorL: item.NorL ?? '',

                                IsOk: Number(
                                    item.IsOk || 0
                                ),

                                Biaya: parseFloat(
                                    item.Biaya || 0
                                ),

                                Pot: parseFloat(
                                    item.Pot || 0
                                ) * 100,

                                JasaPelayanan: parseFloat(
                                    item.JasaPelayanan || 0
                                ),

                                JasaPerujuk: parseFloat(
                                    item.JasaPerujuk || 0
                                ),

                                JasaRS: parseFloat(
                                    item.JasaRS || 0
                                ),

                                Pembaca: parseFloat(
                                    item.Pembaca || 0
                                ),

                                Administrasi: parseFloat(
                                    item.Administrasi || 0
                                ),

                                Reagen: parseFloat(
                                    item.Reagen || 0
                                ),

                                JumlahFile: parseInt(
                                    item.JumlahFile || 0
                                ),

                            };

                        }
                    );


                renderLabItems();

            },


            error: function(xhr) {

                console.log(
                    xhr.responseText
                );

                notifWarning(
                    'Tampilan belum diperbarui',
                    'Pemeriksaan berhasil ditambahkan, tetapi tampilan gagal diperbarui.'
                );
            }

        });
    }

    function printLabModal() {

        const idLab = $('#labIDLab').val();

        if (!idLab) {

            notifWarning(
                'ID Lab belum tersedia',
                'Silakan pilih data laboratorium terlebih dahulu.'
            );

            return;
        }


        let url = @json(route('lab.print', [
                'idLab' => '__IDLAB__',
            ]));


        url = url.replace(
            '__IDLAB__',
            encodeURIComponent(idLab)
        );


        openLabPrint(url);
    }
</script>
