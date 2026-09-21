<script>
    function loadInfoSPLab() {

        const noSP = $('#labNoSP').val();

        console.log('No SP dipilih:', noSP);
        console.log('ID pasien:', "{{ $pasien->ID }}");

        if (!noSP) {

            $('#labInfoPemeriksaan').val(
                'Belum ada pemeriksaan dipilih.'
            );

            $('#labDiagnosa').val('');
            $('#labBB').val('');

            return;
        }


        $('#labInfoPemeriksaan').html(`
                <span class="text-muted">
                    <i class="fas fa-spinner fa-spin mr-1"></i>
                    Memuat pemeriksaan...
                </span>
            `);


        $.ajax({

            url: "{{ route('lab.sp.info') }}",

            type: "GET",

            data: {
                id: "{{ $pasien->ID }}",
                no: noSP
            },

            success: function(res) {

                console.log('RESPON SP LAB:', res);

                if (!res.success) {

                    $('#labInfoPemeriksaan').html(
                        '<span class="text-danger">Data pemeriksaan tidak ditemukan.</span>'
                    );

                    $('#labDiagnosa').val('');
                    $('#labBB').val('');

                    return;
                }


                const isi = res.isiA ?? '';
                const diag = res.Diag ?? '';
                const bb = res.BB ?? '';

                $('#labInfoPemeriksaan').val(
                    isi ?
                    isi :
                    'Tidak ada detail pemeriksaan.'
                );

                $('#labDiagnosa').val(diag);
                $('#labBB').val(bb);
            },

            error: function(xhr) {

                console.log('ERROR SP LAB:', xhr.responseText);

                $('#labInfoPemeriksaan').html(
                    '<span class="text-danger">Gagal mengambil data SP Lab.</span>'
                );

                $('#labDiagnosa').val('');
                $('#labBB').val('');
            }

        });
    }

    function loadInfoSPPA() {

        const noSPPA =
            $('#labNoSPPA').val();

        if (!noSPPA) {

            $('#labInfoPemeriksaan').val('');

            return;
        }


        $.ajax({

            url: "{{ route('lab.sppa.info') }}",

            type: "GET",

            data: {
                id: "{{ $pasien->ID }}",
                no: noSPPA
            },

            success: function(res) {

                if (!res.success) {

                    $('#labInfoPemeriksaan').val('');

                    return;
                }

                $('#labInfoPemeriksaan').val(
                    res.isiA ?? ''
                );
            },

            error: function(xhr) {

                console.log(
                    'ERROR SPPA:',
                    xhr.responseText
                );

                $('#labInfoPemeriksaan').val('');
            }

        });
    }

    function previewSPLab() {

        const noSP =
            $('#labNoSP').val();

        if (!noSP) {

            notifWarning(
                'No SP belum dipilih',
                'Silakan pilih No SP terlebih dahulu.'
            );

            return;
        }

        window.open(
            "{{ url('/lab/print-sp') }}/" + noSP,
            "_blank",
            "height=800,width=1000"
        );
    }

    function previewSPPA() {

        const noSPPA =
            $('#labNoSPPA').val();

        const idLab =
            $('#labIDLab').val();


        if (!noSPPA) {

            notifWarning(
                'No SPPA belum dipilih',
                'Silakan pilih No SPPA terlebih dahulu.'
            );

            return;
        }


        if (!idLab) {

            notifWarning(
                'Laboratorium belum disimpan',
                'Simpan Laboratorium terlebih dahulu sebelum print SPPA.'
            );

            return;
        }


        window.open(
            "{{ url('/lab/print-sppa') }}/" +
            noSPPA + "/" + idLab,
            "_blank",
            "height=800,width=1000"
        );
    }

    async function tarikSPLab() {

        const noSP =
            $('#labNoSP').val();

        const idReg =
            $('#labIDReg').val();


        if (!noSP) {

            notifWarning(
                'No SP belum dipilih',
                'Silakan pilih No SP terlebih dahulu.'
            );

            return;
        }


        const result =
            await konfirmasiAksi({

                title: 'Tarik Pemeriksaan SP?',

                text: 'Seluruh pemeriksaan dari No SP <b>' +
                    noSP +
                    '</b> akan ditambahkan.',

                confirmText: 'Ya, Tarik'
            });


        if (!result.isConfirmed) {
            return;
        }


        let idLab =
            $('#labIDLab').val();


        // ====================================================
        // LAB BARU - BUAT HEADER OTOMATIS
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

                            NoSP: noSP,

                            SpPK: $('#labSpPK').val(),

                            NoSPPA: $('#labNoSPPA').val(),

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
                        'Gagal membuat Laboratorium.'
                    );

                    return;
                }


                idLab =
                    headerRes.IDLab;


                $('#labIDLab')
                    .val(idLab);


                $('#labMode')
                    .val('update');


                // DOKTER HASIL SP
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

                return;
            }
        }


        // ====================================================
        // TARIK PEMERIKSAAN SP
        // ====================================================

        $.ajax({

            url: "{{ route('lab.tarik.sp') }}",

            type: "POST",

            data: {

                _token: "{{ csrf_token() }}",

                NoSP: noSP,

                IDLab: idLab,

                IDREG: idReg
            },


            success: function(res) {

                if (!res.success) {

                    notifError(
                        'Gagal',
                        res.message ??
                        'Gagal menarik pemeriksaan dari SP.'
                    );

                    return;
                }


                reloadItemLab(
                    idLab
                );


                notifSuccess(
                    'Pemeriksaan SP berhasil ditarik'
                );
            },


            error: function(xhr) {

                console.log(
                    xhr.responseText
                );


                notifError(
                    'Gagal',
                    xhr.responseJSON?.message ??
                    'Gagal menarik pemeriksaan dari SP.'
                );
            }

        });
    }

    async function selesaiSPLab() {

        const noSP =
            $('#labNoSP').val();

        if (!noSP) {

            notifWarning(
                'No SP belum dipilih',
                'Silakan pilih No SP terlebih dahulu.'
            );

            return;
        }


        const result =
            await konfirmasiAksi({

                title: 'Selesaikan SP?',

                text: 'No SP <b>' +
                    noSP +
                    '</b> akan ditandai selesai.',

                confirmText: 'Ya, Selesai'
            });


        if (!result.isConfirmed) {
            return;
        }


        $.ajax({

            url: "{{ route('lab.sp.selesai') }}",

            type: "POST",

            data: {

                _token: "{{ csrf_token() }}",

                NoSP: noSP
            },


            success: function(res) {

                if (!res.success) {

                    notifError(
                        'Gagal',
                        res.message ??
                        'Gagal menyelesaikan SP.'
                    );

                    return;
                }


                const selected =
                    $('#labNoSP option:selected');

                let text =
                    selected.text()
                    .replace(/^✕\s*/, '')
                    .replace(/^✓\s*/, '')
                    .trim();

                selected.text(
                    '✓ ' + text
                );


                notifSuccess(
                    'SP Laboratorium berhasil diselesaikan'
                ).then(() => {

                    location.reload();

                });
            },


            error: function(xhr) {

                console.log(
                    xhr.responseText
                );

                notifError(
                    'Gagal',
                    xhr.responseJSON?.message ??
                    'Gagal menyelesaikan SP Laboratorium.'
                );
            }

        });
    }

    // Print Hasil Lab
    function openLabPrint(url) {

        window.open(
            url,
            'labPrintPopup',
            'width=1000,height=750,resizable=yes,scrollbars=yes'
        );
    }
</script>
