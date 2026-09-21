<script>
    function openFotoLab(index, prepID) {

        const idLab =
            $('#labIDLab').val();


        if (!idLab) {

            notifWarning(
                'Laboratorium belum tersimpan',
                'Simpan Laboratorium terlebih dahulu sebelum upload foto.'
            );

            return;
        }


        if (!prepID) {

            notifWarning(
                'Data pemeriksaan tidak ditemukan',
                'Prep ID pemeriksaan tidak tersedia.'
            );

            return;
        }


        const item =
            labItems[index] ?? null;


        $('#fotoIDLab')
            .val(idLab);


        $('#fotoPrepID')
            .val(prepID);

        $('#fotoItemIndex').val(index);

        $('#fotoLabFile')
            .val('');


        $('#fotoLabInfo')
            .text(
                'ID Lab : ' +
                idLab +
                ' | Prep ID : ' +
                prepID +
                (item ?
                    ' | ' + (item.Nama ?? '') :
                    '')
            );


        $('#modalFotoLab')
            .modal('show');


        loadFotoLab();
    }

    function loadFotoLab() {

        const idLab =
            $('#fotoIDLab').val();

        const prepID =
            $('#fotoPrepID').val();

        if (!idLab || !prepID) {
            return;
        }

        $('#fotoLabList').html(`
                <div class="text-center text-muted py-4">
                    <i class="fas fa-spinner fa-spin mr-1"></i>
                    Memuat file...
                </div>
            `);

        $.ajax({

            url: "{{ url('/lab/foto') }}/" +
                idLab +
                "/" +
                prepID,

            type: "GET",

            success: function(res) {

                if (!res.success) {

                    notifError(
                        'Gagal',
                        res.message ??
                        'Gagal mengambil file.'
                    );

                    return;
                }

                const data = res.data || [];

                /* Update JML FIle*/
                const index =
                    parseInt(
                        $('#fotoItemIndex').val()
                    );

                if (
                    !isNaN(index) &&
                    labItems[index]
                ) {

                    labItems[index].JumlahFile =
                        data.length;

                    renderLabItems();
                }

                if (data.length === 0) {

                    $('#fotoLabList').html(`
                        <div class="text-center text-muted py-4">

                            <i class="fas fa-file-medical fa-2x mb-2 d-block"></i>

                            Belum ada file pemeriksaan.

                        </div>
                    `);

                    return;
                }

                let html =
                    '<div class="row">';

                data.forEach(function(item) {

                    const ext =
                        (
                            item.name
                            .split('.')
                            .pop() || ''
                        ).toLowerCase();


                    let preview = '';


                    // ====================================================
                    // GAMBAR
                    // ====================================================

                    if (
                        ext === 'jpg' ||
                        ext === 'jpeg' ||
                        ext === 'png' ||
                        ext === 'webp'
                    ) {

                        preview = `
                            <img
                                src="${item.url}"
                                class="img-fluid"
                                style="
                                    width:100%;
                                    height:180px;
                                    object-fit:cover;
                                    cursor:pointer;
                                    border-radius:6px;
                                "
                                onclick="previewFileLab(
                                    '${item.url}',
                                    '${item.name}'
                                )"
                                title="Klik untuk memperbesar">
                        `;
                    }


                    // ====================================================
                    // PDF
                    // ====================================================
                    else if (
                        ext === 'pdf'
                    ) {

                        preview = `
                            <div style="
                                height:180px;
                                border:1px solid #dee2e6;
                                border-radius:6px;
                                overflow:hidden;
                                background:#fff;
                            ">

                                <iframe
                                    src="${item.url}"
                                    style="
                                        width:100%;
                                        height:100%;
                                        border:0;
                                    ">
                                </iframe>

                            </div>
                        `;
                    }


                    // ====================================================
                    // FILE LAIN
                    // ====================================================
                    else {

                        preview = `
                            <div class="text-center py-5">

                                <i class="fas fa-file fa-4x text-secondary"></i>

                            </div>
                        `;
                    }


                    html += `

                        <div class="col-md-4 mb-3">

                            <div class="card h-100 shadow-sm">

                                <div class="card-body p-2">

                                    ${preview}

                                    <div
                                        class="small text-truncate mt-2"
                                        title="${item.name}">

                                        ${item.name}

                                    </div>


                                    <div class="mt-2 d-flex justify-content-between">

                                        <button
                                            type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            onclick="previewFileLab(
                                                '${item.url}',
                                                '${item.name}'
                                            )">

                                            <i class="fas fa-eye mr-1"></i>
                                            Lihat

                                        </button>


                                        <button
                                            type="button"
                                            class="btn btn-outline-danger btn-sm"
                                            onclick='hapusFotoLab(
                                                ${JSON.stringify(item.name)}
                                            )'>

                                            <i class="fas fa-trash"></i>

                                        </button>

                                    </div>

                                </div>

                            </div>

                        </div>
                    `;
                });

                html +=
                    '</div>';

                $('#fotoLabList')
                    .html(html);
            },

            error: function(xhr) {

                console.log(
                    xhr.responseText
                );

                notifError(
                    'Gagal',
                    xhr.responseJSON?.message ??
                    'Gagal mengambil file pemeriksaan.'
                );
            }

        });
    }

    async function uploadFotoLab() {

        const idLab =
            $('#fotoIDLab').val();

        const prepID =
            $('#fotoPrepID').val();

        const files =
            $('#fotoLabFile')[0].files;


        if (!idLab || !prepID) {

            notifWarning(
                'Data tidak lengkap',
                'ID Lab atau Prep ID tidak ditemukan.'
            );

            return;
        }


        if (!files || files.length === 0) {

            notifWarning(
                'Foto belum dipilih',
                'Pilih minimal satu foto.'
            );

            return;
        }


        $('#btnUploadFotoLab')
            .prop('disabled', true)
            .html(`
                <i class="fas fa-spinner fa-spin mr-1"></i>
                Upload...
            `);


        try {

            for (
                let i = 0; i < files.length; i++
            ) {

                const formData =
                    new FormData();


                formData.append(
                    '_token',
                    "{{ csrf_token() }}"
                );

                formData.append(
                    'IDLab',
                    idLab
                );

                formData.append(
                    'Prep_ID',
                    prepID
                );

                formData.append(
                    'foto',
                    files[i]
                );


                await $.ajax({

                    url: "{{ route('lab.foto.upload') }}",

                    type: "POST",

                    data: formData,

                    processData: false,

                    contentType: false

                });
            }


            $('#fotoLabFile')
                .val('');


            loadFotoLab();


            notifSuccess(
                'Foto berhasil diupload'
            );


        } catch (xhr) {

            console.log(
                xhr.responseText
            );


            notifError(
                'Upload gagal',
                xhr.responseJSON?.message ??
                'Gagal upload foto ke NAS.'
            );


        } finally {

            $('#btnUploadFotoLab')
                .prop('disabled', false)
                .html(`
                    <i class="fas fa-upload mr-1"></i>
                    Upload Foto
                `);
        }
    }

    function previewFotoLab(url) {

        Swal.fire({

            imageUrl: url,

            imageAlt: 'Foto pemeriksaan laboratorium',

            width: '80%',

            showConfirmButton: false,

            showCloseButton: true,

            background: '#000'

        });
    }

    function previewFileLab(url, name) {

        const ext =
            (
                name
                .split('.')
                .pop() || ''
            ).toLowerCase();


        // ====================================================
        // PDF
        // ====================================================

        if (ext === 'pdf') {

            Swal.fire({

                width: '90%',

                html: `
                    <div style="
                        width:100%;
                        height:75vh;
                    ">

                        <iframe
                            src="${url}"
                            style="
                                width:100%;
                                height:100%;
                                border:0;
                            ">
                        </iframe>

                    </div>
                `,

                showConfirmButton: false,

                showCloseButton: true

            });

            return;
        }


        // ====================================================
        // GAMBAR
        // ====================================================

        Swal.fire({

            imageUrl: url,

            imageAlt: name,

            width: '80%',

            showConfirmButton: false,

            showCloseButton: true

        });
    }

    async function hapusFotoLab(name) {

        const idLab =
            $('#fotoIDLab').val();

        const prepID =
            $('#fotoPrepID').val();


        const result =
            await konfirmasiAksi({

                title: 'Hapus Foto?',

                text: 'Foto <b>' +
                    name +
                    '</b> akan dihapus permanen.',

                confirmText: 'Ya, Hapus',

                icon: 'warning'

            });


        if (!result.isConfirmed) {
            return;
        }


        $.ajax({

            url: "{{ route('lab.foto.delete') }}",

            type: "DELETE",

            data: {

                _token: "{{ csrf_token() }}",

                IDLab: idLab,

                Prep_ID: prepID,

                name: name
            },


            success: function(res) {

                if (!res.success) {

                    notifError(
                        'Gagal',
                        res.message ??
                        'Gagal menghapus foto.'
                    );

                    return;
                }


                loadFotoLab();


                notifSuccess(
                    'Foto berhasil dihapus'
                );
            },


            error: function(xhr) {

                console.log(
                    xhr.responseText
                );


                notifError(
                    'Gagal',
                    xhr.responseJSON?.message ??
                    'Gagal menghapus foto.'
                );
            }

        });
    }
</script>
