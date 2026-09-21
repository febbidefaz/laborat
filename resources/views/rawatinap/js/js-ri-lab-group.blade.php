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
                        -- Pilih Grup Pemeriksaan --
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
                        -- Gagal memuat grup --
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
                        -- Grup Kelompok --
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
</script>
