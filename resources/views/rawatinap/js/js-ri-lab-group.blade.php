<script>
    let grupLabSudahLoad = false;

    function loadGrupLab() {

        if (grupLabSudahLoad) {
            return;
        }

        $('#labGrup').html(`
            <option value="">
                Memuat grup...
            </option>
        `);


        $.ajax({

            url: "{{ route('lab.grup') }}",

            type: "GET",

            success: function(res) {

                if (!res.success) {

                    notifError(
                        'Gagal',
                        res.message ??
                        'Master grup laboratorium gagal dimuat.'
                    );

                    return;
                }


                let html = `
                    <option value="">
                        Grup Pemeriksaan
                    </option>
                `;


                (res.data || []).forEach(function(item) {

                    html += `
                        <option value="${item.grpID}">
                            ${item.grpName}
                        </option>
                    `;

                });


                $('#labGrup').html(html);

                grupLabSudahLoad = true;
            },


            error: function(xhr) {

                console.log(xhr.responseText);

                $('#labGrup').html(`
                    <option value="">
                        Gagal memuat grup
                    </option>
                `);
            }

        });
    }

    async function pilihGrupLab() {

        const grpID =
            $('#labGrup').val();

        if (!grpID) {
            return;
        }


        const idReg =
            $('#labIDReg').val();

        const noSP =
            $('#labNoSP').val();

        const dokterID =
            $('#labDokter').val();


        // ====================================================
        // JIKA TIDAK ADA SP → DOKTER WAJIB DIPILIH
        // ====================================================

        if (!noSP && !dokterID) {

            notifWarning(
                'Dokter belum dipilih',
                'Dokter wajib dipilih jika No SP tidak digunakan.'
            );

            $('#labGrup')
                .val('');

            return;
        }


        const result =
            await konfirmasiAksi({

                title: 'Tambahkan Grup?',

                text: 'Seluruh pemeriksaan dari grup ini akan ditambahkan.',

                confirmText: 'Ya, Tambahkan'
            });


        if (!result.isConfirmed) {

            $('#labGrup')
                .val('');

            return;
        }


        $('#labGrup')
            .prop('disabled', true);


        let idLab =
            $('#labIDLab').val();


        // ====================================================
        // JIKA BELUM ADA IDLab → BUAT HEADER OTOMATIS
        // ====================================================

        if (!idLab) {

            try {

                const headerRes =
                    await $.ajax({

                        url: "{{ route('lab.simpan.header') }}",

                        type: "POST",

                        data: {

                            _token: "{{ csrf_token() }}",

                            IDREG: idReg,

                            Tanggal: $('#labTgl').val(),

                            DokterID: $('#labDokter').val(),

                            SpPK: $('#labSpPK').val(),

                            NoSP: noSP,

                            NoSPPA: $('#labNoSPPA').val(),

                            NoLIS: $('#labNoLIS').val(),

                            JamAmbil: $('#labJamAmbil').val(),

                            JamCheck: $('#labJamCheck').val(),

                            NoteSpPK: $('#labNoteSpPk').val()
                        }

                    });


                if (
                    !headerRes.success ||
                    !headerRes.IDLab
                ) {

                    notifError(
                        'Gagal',
                        headerRes.message ??
                        'Gagal membuat header Laboratorium.'
                    );


                    $('#labGrup')
                        .prop('disabled', false)
                        .val('');

                    return;
                }


                idLab =
                    headerRes.IDLab;


                $('#labIDLab')
                    .val(idLab);


                $('#labMode')
                    .val('update');


                if (headerRes.DokterID) {

                    $('#labDokter')
                        .val(headerRes.DokterID);
                }


                $('#modalLabTitle')
                    .html(
                        '<i class="fas fa-vials mr-2"></i>' +
                        'Edit Laboratorium ' +
                        '<small class="ml-2 font-weight-normal">' +
                        'ID Lab : ' +
                        idLab +
                        '</small>'
                    );


                $('#btnHapusLab')
                    .show();


                $('#btnSimpanLab')
                    .html(
                        '<i class="fas fa-save mr-1"></i> Simpan Perubahan'
                    );


            } catch (xhr) {

                console.log(
                    xhr.responseText
                );


                notifError(
                    'Gagal',
                    xhr.responseJSON?.message ??
                    'Gagal membuat header Laboratorium.'
                );


                $('#labGrup')
                    .prop('disabled', false)
                    .val('');


                return;
            }
        }


        // ====================================================
        // GENERATE GRUP
        // ====================================================

        $.ajax({

            url: "{{ route('lab.grup.generate') }}",

            type: "POST",

            data: {

                _token: "{{ csrf_token() }}",

                grpID: grpID,

                IDLab: idLab,

                IDREG: idReg
            },


            success: function(res) {

                if (!res.success) {

                    notifError(
                        'Gagal',
                        res.message ??
                        'Gagal menambahkan grup pemeriksaan.'
                    );

                    return;
                }


                reloadItemLab(
                    idLab
                );


                notifSuccess(
                    'Grup pemeriksaan berhasil ditambahkan'
                );

            },


            error: function(xhr) {

                console.log(
                    xhr.responseText
                );


                notifError(
                    'Gagal',
                    xhr.responseJSON?.message ??
                    'Gagal menambahkan grup pemeriksaan.'
                );

            },


            complete: function() {

                $('#labGrup')
                    .prop('disabled', false)
                    .val('');

            }

        });
    }

    let grupKelompokLabSudahLoad = false;

    function loadGrupKelompokLab() {

        if (grupKelompokLabSudahLoad) {
            return;
        }

        $('#labGrupKelompok').html(`
            <option value="">
                Memuat kelompok...
            </option>
        `);


        $.ajax({

            url: "{{ route('lab.grup.kelompok') }}",

            type: "GET",


            success: function(res) {

                if (!res.success) {

                    alert(
                        res.message ??
                        'Master grup kelompok gagal dimuat.'
                    );

                    return;
                }


                let html = `
                    <option value="">
                       Grup Kel
                    </option>
                `;


                (res.data || []).forEach(
                    function(item) {

                        html += `
                        <option value="${item.grpID}">
                            ${item.grpName}
                        </option>
                    `;

                    }
                );


                $('#labGrupKelompok')
                    .html(html);


                grupKelompokLabSudahLoad = true;
            },


            error: function(xhr) {

                console.log(
                    xhr.responseText
                );

                $('#labGrupKelompok').html(`
                    <option value="">
                        -- Gagal memuat kelompok --
                    </option>
                `);

            }

        });
    }

    async function pilihGrupKelompokLab() {

        const kelGrpID =
            $('#labGrupKelompok').val();

        if (!kelGrpID) {
            return;
        }


        const idReg =
            $('#labIDReg').val();

        const noSP =
            $('#labNoSP').val();

        const dokterID =
            $('#labDokter').val();


        // ====================================================
        // JIKA TIDAK ADA SP → DOKTER WAJIB
        // ====================================================

        if (!noSP && !dokterID) {

            notifWarning(
                'Dokter belum dipilih',
                'Dokter wajib dipilih jika No SP tidak digunakan.'
            );


            $('#labGrupKelompok')
                .val('');


            return;
        }


        const result =
            await konfirmasiAksi({

                title: 'Tambahkan Kelompok?',

                text: 'Seluruh pemeriksaan dalam kelompok ini akan ditambahkan.',

                confirmText: 'Ya, Tambahkan'
            });


        if (!result.isConfirmed) {

            $('#labGrupKelompok')
                .val('');

            return;
        }


        $('#labGrupKelompok')
            .prop('disabled', true);


        let idLab =
            $('#labIDLab').val();


        // ====================================================
        // JIKA BELUM ADA IDLab → BUAT HEADER OTOMATIS
        // ====================================================

        if (!idLab) {

            try {

                const headerRes =
                    await $.ajax({

                        url: "{{ route('lab.simpan.header') }}",

                        type: "POST",

                        data: {

                            _token: "{{ csrf_token() }}",

                            IDREG: idReg,

                            Tanggal: $('#labTgl').val(),

                            DokterID: $('#labDokter').val(),

                            SpPK: $('#labSpPK').val(),

                            NoSP: noSP,

                            NoSPPA: $('#labNoSPPA').val(),

                            NoLIS: $('#labNoLIS').val(),

                            JamAmbil: $('#labJamAmbil').val(),

                            JamCheck: $('#labJamCheck').val(),

                            NoteSpPK: $('#labNoteSpPk').val()

                        }

                    });


                if (
                    !headerRes.success ||
                    !headerRes.IDLab
                ) {

                    notifError(
                        'Gagal',
                        headerRes.message ??
                        'Gagal membuat header Laboratorium.'
                    );


                    $('#labGrupKelompok')
                        .prop('disabled', false)
                        .val('');


                    return;
                }


                idLab =
                    headerRes.IDLab;


                $('#labIDLab')
                    .val(idLab);


                $('#labMode')
                    .val('update');


                if (headerRes.DokterID) {

                    $('#labDokter')
                        .val(headerRes.DokterID);
                }


                $('#modalLabTitle')
                    .html(
                        '<i class="fas fa-vials mr-2"></i>' +
                        'Edit Laboratorium ' +
                        '<small class="ml-2 font-weight-normal">' +
                        'ID Lab : ' +
                        idLab +
                        '</small>'
                    );


                $('#btnHapusLab')
                    .show();


                $('#btnSimpanLab')
                    .html(
                        '<i class="fas fa-save mr-1"></i> Simpan Perubahan'
                    );


            } catch (xhr) {

                console.log(
                    xhr.responseText
                );


                notifError(
                    'Gagal',
                    xhr.responseJSON?.message ??
                    'Gagal membuat header Laboratorium.'
                );


                $('#labGrupKelompok')
                    .prop('disabled', false)
                    .val('');


                return;
            }
        }


        // ====================================================
        // GENERATE GRUP KELOMPOK
        // ====================================================

        $.ajax({

            url: "{{ route('lab.grup.kelompok.generate') }}",

            type: "POST",

            data: {

                _token: "{{ csrf_token() }}",

                KelgrpID: kelGrpID,

                IDLab: idLab,

                IDREG: idReg
            },


            success: function(res) {

                if (!res.success) {

                    notifError(
                        'Gagal',
                        res.message ??
                        'Gagal menambahkan kelompok pemeriksaan.'
                    );

                    return;
                }


                reloadItemLab(
                    idLab
                );


                notifSuccess(
                    'Kelompok pemeriksaan berhasil ditambahkan'
                );

            },


            error: function(xhr) {

                console.log(
                    xhr.responseText
                );


                notifError(
                    'Gagal',
                    xhr.responseJSON?.message ??
                    'Gagal generate grup kelompok.'
                );

            },


            complete: function() {

                $('#labGrupKelompok')
                    .prop('disabled', false)
                    .val('');

            }

        });
    }

    async function registrasiLISLab() {

        const idLab = $('#labIDLab').val();
        const idReg = $('#labIDReg').val();
        const $btn = $('#btnRegLIS');

        console.log('========== REG LIS ==========');
        console.log('IDLab:', idLab);
        console.log('IDReg:', idReg);
        console.log('URL:', "{{ route('lab.lis.api.registrasi') }}");

        if (!idLab || !/^\d+$/.test(idLab)) {
            notifWarning(
                'ID Lab belum tersedia',
                'Simpan pemeriksaan laboratorium terlebih dahulu.'
            );
            return;
        }

        if ($btn.prop('disabled')) {
            return;
        }

        const url = "{{ route('lab.lis.api.registrasi') }}";

        async function kirimRegistrasi(ulang, nomorLama = '') {
            return $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: {
                    _token: "{{ csrf_token() }}",
                    IDLab: idLab,
                    konfirmasi_ulang: ulang ? 1 : 0,
                    no_lab_sebelumnya: nomorLama,
                    alasan: ulang ?
                        'Registrasi ulang melalui modal Laboratorium' : ''
                }
            });
        }

        $btn.prop('disabled', true)
            .html(
                '<i class="fas fa-spinner fa-spin mr-1"></i> Memproses...'
            );

        try {

            let res;

            try {
                // Cek/registrasi pertama
                res = await kirimRegistrasi(false);

            } catch (xhr) {

                const error = xhr.responseJSON || {};

                if (!error.confirmation_required) {
                    throw xhr;
                }

                const nomorLama = error.no_lab_lama;

                // =====================================
                // KONFIRMASI REGISTRASI ULANG
                // =====================================
                const konfirmasi = await konfirmasiAksi({
                    title: 'Registrasi LIS Sudah Ada',
                    text: 'ID Lab: ' + idLab +
                        '\nNo LIS sebelumnya: ' + nomorLama +
                        '\n\nRegistrasi ulang akan mengganti ' +
                        'nomor LIS aktif dan menyimpan nomor ' +
                        'sebelumnya dalam riwayat.',
                    confirmText: 'Ya, Registrasi Ulang'
                });

                if (!konfirmasi.isConfirmed) {
                    return;
                }

                // Kirim ulang dengan persetujuan petugas
                res = await kirimRegistrasi(true, nomorLama);
            }

            if (!res.success) {
                throw {
                    responseJSON: res
                };
            }

            const noLab = res.no_lab || '-';

            tampilkanStatusLISNew(res.no_lab);

            if (res.registrasi_ulang) {
                notifSuccess(
                    'Registrasi ulang berhasil. No LIS baru: ' + noLab
                );
            } else {
                notifSuccess(
                    'Registrasi LIS berhasil. No LIS: ' + noLab
                );
            }

            console.log('Registrasi LIS berhasil:', res);

            // Jangan isi #labNoLIS di sini karena
            // dropdown tersebut digunakan untuk LIS lama.
            // Nomor LIS ADAM disimpan pada No_Lab_LIS_New.

        } catch (xhr) {

            console.error(
                'Registrasi LIS gagal:',
                xhr.responseJSON || xhr
            );

            const error = xhr.responseJSON || {};

            const pesan = Array.isArray(error.message) ?
                error.message.map(item =>
                    item.msg || JSON.stringify(item)
                ).join('\n') :
                (error.message ||
                    'Registrasi gagal atau status belum pasti.');

            notifError(
                'Registrasi LIS Gagal',
                pesan
            );

        } finally {

            $btn.prop('disabled', false);

            cekStatusLISNew();
        }
    }

    function cekStatusLISNew() {

        const idLab = String($('#labIDLab').val() || '').trim();

        if (!idLab) {
            tampilkanStatusLISNew('');
            return;
        }

        $.ajax({
            url: "{{ route('lab.detail', ['idLab' => '__IDLAB__']) }}"
                .replace('__IDLAB__', encodeURIComponent(idLab)),

            type: 'GET',
            dataType: 'json',

            success: function(res) {

                // Abaikan respons jika modal sudah pindah IDLab.
                if (String($('#labIDLab').val()) !== idLab) {
                    return;
                }

                if (res.success && res.header) {
                    tampilkanStatusLISNew(
                        res.header.No_Lab_LIS_New
                    );
                } else {
                    $('#btnRegLIS')
                        .attr('title', 'Gagal membaca status LIS');
                }
            },

            error: function(xhr) {

                console.error(
                    'Gagal membaca status LIS:',
                    xhr.responseJSON || xhr.responseText
                );

                $('#btnRegLIS')
                    .attr('title', 'Status LIS belum dapat diperiksa');
            }
        });
    }
</script>
