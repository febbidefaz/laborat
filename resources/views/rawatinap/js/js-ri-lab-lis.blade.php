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
                Insert Saba
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
                        Insert Saba
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
                    Insert Saba
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

    function tampilkanStatusLISNew(noLab) {

        const nomor = String(noLab || '').trim();
        const $btn = $('#btnRegLIS');

        if (nomor !== '') {

            // Sudah ada nomor LIS Baru
            $btn.html(
                '<i class="fas fa-paper-plane mr-1"></i> Ada LIS'
            );

            $btn.attr('title', 'No LIS: ' + nomor);

            $btn.css({
                'color': '#28a745',
                'border': '1px solid #28a745'
            });

        } else {

            // Belum registrasi LIS
            $btn.html(
                '<i class="fas fa-paper-plane mr-1"></i> Reg LIS'
            );

            $btn.attr('title', 'Registrasi pemeriksaan ke LIS ADAM');

            $btn.css({
                'color': '#6f42c1',
                'border': '1px solid #6f42c1'
            });
        }
    }

    // Tarik LIS Adam New
    async function tarikLISNew() {

        const idLab = String(
            $('#labIDLab').val() || ''
        ).trim();

        const $btn = $('#btnTarikLISNew');

        if (!idLab) {
            notifWarning(
                'ID Lab belum tersedia',
                'Simpan pemeriksaan laboratorium dahulu.'
            );
            return;
        }

        if ($btn.prop('disabled')) return;

        const konfirmasi = await konfirmasiAksi({
            title: 'Tarik Hasil LIS Baru?',
            text: 'Ambil hasil pemeriksaan untuk IDLab ' +
                idLab + ' dari LIS ADAM? ' +
                'Hasil pemeriksaan yang cocok akan diperbarui.',
            confirmText: 'Ya, Tarik Hasil'
        });

        if (!konfirmasi.isConfirmed) return;

        $btn.prop('disabled', true).html(
            '<i class="fas fa-spinner fa-spin mr-1"></i> Menarik...'
        );

        try {

            const res = await $.ajax({
                url: "{{ route('lab.lis.api.tarik') }}",
                type: 'POST',
                dataType: 'json',
                data: {
                    _token: "{{ csrf_token() }}",
                    IDLab: idLab
                }
            });

            if (!res.success) {
                notifError(
                    'Tarik LIS Gagal',
                    res.message || 'Hasil LIS gagal diproses.'
                );
                return;
            }

            notifSuccess(
                'Berhasil menarik ' +
                res.JumlahTersinkron +
                ' hasil LIS.'
            );

            // Muat ulang hasil pada tabel pemeriksaan
            reloadItemLab(idLab);

        } catch (xhr) {

            console.error(
                'Tarik LIS Baru gagal:',
                xhr.responseJSON || xhr.responseText
            );

            notifError(
                'Tarik LIS Gagal',
                xhr.responseJSON?.message ||
                'Hasil belum tersedia atau mapping pemeriksaan tidak sesuai.'
            );

        } finally {

            $btn.prop('disabled', false).html(
                '<i class="fas fa-download mr-1"></i> Tarik LIS Baru'
            );
        }
    }
</script>
