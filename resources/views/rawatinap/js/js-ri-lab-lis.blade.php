<script>
    let noLISSudahLoad = false;

    function loadNoLISLab(selectedNoLIS = '') {

        const noRM =
            "{{ $pasien->RegNum ?? '' }}";

        $('#labNoLIS').html(`
                <option value="">
                    Memuat LIS...
                </option>
            `);


        if (!noRM) {

            $('#labNoLIS').html(`
            <option value="">
                -- No RM tidak ditemukan --
            </option>
        `);

            return;
        }


        $.ajax({

            url: "{{ route('lab.lis.list') }}",

            type: "GET",

            data: {
                NoRM: noRM
            },


            success: function(res) {

                let html = `
                <option value="">
                    -- Pilih No LIS --
                </option>
            `;


                (res.data || []).forEach(function(item) {

                    html += `
                    <option value="${item.no_lab}">
                        ${item.no_lab}
                        - ${item.pasien_nama ?? ''}
                    </option>
                `;

                });


                $('#labNoLIS')
                    .html(html);


                // ==============================================
                // HANYA TAMPILKAN JIKA SUDAH TERSIMPAN
                // ==============================================

                if (
                    selectedNoLIS !== null &&
                    selectedNoLIS !== undefined &&
                    String(selectedNoLIS).trim() !== ''
                ) {

                    $('#labNoLIS')
                        .val(
                            String(selectedNoLIS)
                        );

                } else {

                    // BELUM ADA No_Lab_LIS
                    // JANGAN OTOMATIS PILIH LIS TERBARU

                    $('#labNoLIS')
                        .val('');
                }

            },


            error: function(xhr) {

                console.log(
                    xhr.responseText
                );


                $('#labNoLIS').html(`
                <option value="">
                    -- Gagal memuat LIS --
                </option>
            `);
            }

        });
    }

    async function tarikLISLab() {

        const noLIS =
            $('#labNoLIS').val();

        const idLab =
            $('#labIDLab').val();


        if (!noLIS) {

            notifWarning(
                'No LIS belum dipilih',
                'Silakan pilih No LIS terlebih dahulu.'
            );

            return;
        }


        if (!idLab) {

            notifWarning(
                'Laboratorium belum tersimpan',
                'Simpan pemeriksaan Laboratorium terlebih dahulu sebelum menarik hasil LIS.'
            );

            return;
        }


        const result =
            await konfirmasiAksi({

                title: 'Tarik Hasil LIS?',

                text: 'Hasil pemeriksaan dari No LIS <b>' +
                    noLIS +
                    '</b> akan dimasukkan ke pemeriksaan Laboratorium.',

                confirmText: 'Ya, Tarik'
            });


        if (!result.isConfirmed) {
            return;
        }


        const btn =
            $('#labNoLIS')
            .next('button');


        btn.prop('disabled', true)
            .html(
                '<i class="fas fa-spinner fa-spin"></i>'
            );


        $.ajax({

            url: "{{ route('lab.lis.tarik') }}",

            type: "POST",

            data: {

                _token: "{{ csrf_token() }}",

                IDLab: idLab,

                NoLIS: noLIS
            },


            success: function(res) {

                if (!res.success) {

                    notifError(
                        'Gagal',
                        res.message ??
                        'Gagal menarik hasil LIS.'
                    );

                    return;
                }


                // ==========================================
                // RELOAD HASIL PEMERIKSAAN
                // ==========================================

                reloadItemLab(
                    idLab
                );


                notifSuccess(
                    'Hasil LIS berhasil ditarik'
                );

            },


            error: function(xhr) {

                console.log(
                    xhr.responseText
                );


                notifError(
                    'Gagal',
                    xhr.responseJSON?.message ??
                    'Gagal menarik hasil LIS.'
                );

            },


            complete: function() {

                btn.prop('disabled', false)
                    .html(
                        '<i class="fas fa-download"></i>'
                    );
            }

        });
    }

    async function insertLISSaba() {

        const idLab =
            $('#labIDLab').val();


        if (!idLab) {

            notifWarning(
                'Laboratorium belum tersimpan',
                'Simpan pemeriksaan Laboratorium terlebih dahulu.'
            );

            return;
        }


        const result =
            await konfirmasiAksi({

                title: 'Insert ke LIS Saba?',

                text: 'Order Laboratorium ID <b>' +
                    idLab +
                    '</b> akan dikirim ke LIS Saba.',

                confirmText: 'Ya, Kirim'
            });


        if (!result.isConfirmed) {
            return;
        }


        $.ajax({

            url: "{{ route('lab.lis.saba.insert') }}",

            type: "POST",

            data: {

                _token: "{{ csrf_token() }}",

                IDLab: idLab
            },


            beforeSend: function() {

                $('#btnInsertLISSaba')
                    .prop('disabled', true)
                    .html(`
                    <i class="fas fa-spinner fa-spin mr-1"></i>
                    Mengirim...
                `);
            },


            success: function(res) {

                if (!res.success) {

                    notifError(
                        'Gagal',
                        res.message ??
                        'Gagal mengirim ke LIS Saba.'
                    );

                    return;
                }


                notifSuccess(
                    'Order berhasil dikirim ke LIS Saba'
                );

                cekLISSaba();

            },


            error: function(xhr) {

                console.log(
                    xhr.responseText
                );


                notifError(
                    'Gagal',
                    xhr.responseJSON?.message ??
                    'Gagal mengirim order ke LIS Saba.'
                );
            },


            complete: function() {}

        });
    }

    function cekLISSaba(idLab = null) {

        const currentIDLab =
            idLab || $('#labIDLab').val();

        const idReg =
            $('#labIDReg').val();


        if (!currentIDLab || !idReg) {

            $('#btnInsertLISSaba')
                .prop('disabled', true)
                .attr('data-mode', 'insert')
                .removeClass('btn-primary btn-success')
                .addClass('btn-outline-primary')
                .html(`
                <i class="fas fa-paper-plane mr-1"></i>
                Insert LIS Saba
            `);

            return;
        }


        $('#btnInsertLISSaba')
            .prop('disabled', true)
            .html(`
            <i class="fas fa-spinner fa-spin mr-1"></i>
            Cek LIS...
        `);


        $.ajax({

            url: "{{ route('lab.lis.saba.cek') }}",

            type: "GET",

            data: {
                IDReg: idReg,
                IDLab: currentIDLab
            },


            success: function(res) {

                if (!res.success) {
                    return;
                }


                // ====================================================
                // SUDAH PERNAH DIKIRIM
                // ====================================================

                if (parseInt(res.LIS) === 1) {

                    $('#btnInsertLISSaba')
                        .prop('disabled', false)
                        .attr('data-mode', 'update')
                        .removeClass(
                            'btn-outline-primary btn-success'
                        )
                        .addClass('btn-primary')
                        .html(`
                        <i class="fas fa-sync-alt mr-1"></i>
                        Update LIS Saba
                    `);


                    // ====================================================
                    // BELUM PERNAH DIKIRIM
                    // ====================================================

                } else {

                    $('#btnInsertLISSaba')
                        .prop('disabled', false)
                        .attr('data-mode', 'insert')
                        .removeClass(
                            'btn-primary btn-success'
                        )
                        .addClass('btn-outline-primary')
                        .html(`
                        <i class="fas fa-paper-plane mr-1"></i>
                        Insert LIS Saba
                    `);
                }

            },


            error: function(xhr) {

                console.log(
                    xhr.responseText
                );


                $('#btnInsertLISSaba')
                    .prop('disabled', false)
                    .attr('data-mode', 'insert')
                    .removeClass(
                        'btn-primary btn-success'
                    )
                    .addClass('btn-outline-primary')
                    .html(`
                    <i class="fas fa-paper-plane mr-1"></i>
                    Insert LIS Saba
                `);
            }

        });
    }

    function prosesLISSaba() {

        const mode =
            $('#btnInsertLISSaba')
            .attr('data-mode');


        if (mode === 'update') {

            updateLISSaba();

        } else {

            insertLISSaba();
        }
    }

    async function updateLISSaba() {

        const idLab =
            $('#labIDLab').val();


        if (!idLab) {

            notifWarning(
                'Laboratorium belum tersimpan',
                'ID Laboratorium tidak ditemukan.'
            );

            return;
        }


        const result =
            await konfirmasiAksi({

                title: 'Update LIS Saba?',

                text: 'Order Laboratorium ID <b>' +
                    idLab +
                    '</b> akan diperbarui di LIS Saba.',

                confirmText: 'Ya, Update'
            });


        if (!result.isConfirmed) {
            return;
        }


        $.ajax({

            url: "{{ route('lab.lis.saba.update') }}",

            type: "POST",

            data: {

                _token: "{{ csrf_token() }}",

                IDLab: idLab
            },


            beforeSend: function() {

                $('#btnInsertLISSaba')
                    .prop('disabled', true)
                    .html(`
                    <i class="fas fa-spinner fa-spin mr-1"></i>
                    Update...
                `);
            },


            success: function(res) {

                if (!res.success) {

                    notifError(
                        'Gagal',
                        res.message ??
                        'Gagal memperbarui LIS Saba.'
                    );

                    return;
                }


                notifSuccess(
                    'Order LIS Saba berhasil diperbarui'
                );


                cekLISSaba(idLab);

            },


            error: function(xhr) {

                console.log(
                    xhr.responseText
                );


                notifError(
                    'Gagal',
                    xhr.responseJSON?.message ??
                    'Gagal memperbarui LIS Saba.'
                );


                cekLISSaba(idLab);
            }

        });
    }

    async function tarikLISSaba() {

        const idLab =
            $('#labIDLab').val();


        if (!idLab) {

            notifWarning(
                'Laboratorium belum tersimpan',
                'ID Laboratorium tidak ditemukan.'
            );

            return;
        }


        const result =
            await konfirmasiAksi({

                title: 'Tarik Hasil LIS Saba?',

                text: 'Hasil pemeriksaan dari LIS Saba untuk ID Lab <b>' +
                    idLab +
                    '</b> akan dimasukkan ke hasil laboratorium.',

                confirmText: 'Ya, Tarik'
            });


        if (!result.isConfirmed) {
            return;
        }


        $.ajax({

            url: "{{ route('lab.lis.saba.tarik') }}",

            type: "POST",

            data: {

                _token: "{{ csrf_token() }}",

                IDLab: idLab
            },


            beforeSend: function() {

                $('#btnTarikLISSaba')
                    .prop('disabled', true)
                    .html(`
                    <i class="fas fa-spinner fa-spin mr-1"></i>
                    Menarik...
                `);
            },


            success: function(res) {

                if (!res.success) {

                    notifError(
                        'Gagal',
                        res.message ??
                        'Gagal menarik hasil LIS Saba.'
                    );

                    return;
                }


                // ====================================================
                // RELOAD DETAIL HASIL TANPA TUTUP MODAL
                // ====================================================

                reloadItemLab(idLab);


                notifSuccess(
                    'Hasil LIS Saba berhasil ditarik'
                );

            },


            error: function(xhr) {

                console.log(
                    xhr.responseText
                );


                notifError(
                    'Gagal',
                    xhr.responseJSON?.message ??
                    'Gagal menarik hasil LIS Saba.'
                );
            },


            complete: function() {

                $('#btnTarikLISSaba')
                    .prop('disabled', false)
                    .html(`
                    <i class="fas fa-download mr-1"></i>
                    Tarik LIS Saba
                `);
            }

        });
    }

    function loadAnalisLab(selectedID = '') {

        $('#labAnalis').html(`
            <option value="">
                Memuat Verifikator...
            </option>
        `);

        $.ajax({

            url: "{{ route('lab.analis') }}",

            type: "GET",

            success: function(res) {

                let html = `
            <option value="">
                -- Pilih Verifikator --
            </option>
        `;

                (res.data || []).forEach(function(item) {

                    html += `
                <option value="${item.id}">
                    ${item.nama}
                </option>
            `;

                });

                $('#labAnalis').html(html);

                if (selectedID) {
                    $('#labAnalis').val(String(selectedID));
                }
            },

            error: function(xhr) {

                console.log(xhr.responseText);

                $('#labAnalis').html(`
            <option value="">
                -- Gagal memuat Verifikator --
            </option>
        `);
            }
        });
    }
</script>
