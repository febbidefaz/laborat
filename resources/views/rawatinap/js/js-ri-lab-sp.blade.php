<script>
    function loadInfoSPLab(selectedNoSP = null) {

        const noSP = String(
            selectedNoSP ?? $('#labNoSP').val() ?? ''
        ).trim();

        const idReg = String(
            $('#labIDReg').val() || ''
        ).trim();

        if (!noSP || !idReg) {

            $('#labInfoPemeriksaan').val(
                'Belum ada pemeriksaan dipilih.'
            );

            $('#labDiagnosa').val('');
            $('#labBB').val('');

            return;
        }

        $('#labInfoPemeriksaan').val(
            'Memuat pemeriksaan...'
        );

        return $.ajax({

            url: "{{ route('lab.sp.info') }}",

            type: "GET",

            dataType: "json",

            cache: false,

            data: {
                id: idReg,
                no: noSP
            },

            success: function(res) {

                // Pastikan SP yang aktif tidak berubah
                if (
                    String($('#labNoSP').val()) !== noSP ||
                    String($('#labIDReg').val()) !== idReg
                ) {
                    return;
                }

                if (!res.success) {

                    $('#labInfoPemeriksaan').val(
                        'Data pemeriksaan tidak ditemukan.'
                    );

                    $('#labDiagnosa').val('');
                    $('#labBB').val('');

                    return;
                }

                $('#labInfoPemeriksaan').val(
                    res.isiA || 'Tidak ada detail pemeriksaan.'
                );

                $('#labDiagnosa').val(
                    res.Diag ?? ''
                );

                $('#labBB').val(
                    res.BB ?? ''
                );
            },

            error: function(xhr) {

                console.error(
                    'ERROR SP LAB:',
                    xhr.status
                );

                $('#labInfoPemeriksaan').val(
                    'Gagal mengambil data SP Lab.'
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
