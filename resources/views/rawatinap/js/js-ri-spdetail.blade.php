<script>
    function cekSPDetail() {

        const noSP = @js($noSP ?? '');

        console.log('NO SP:', noSP);

        if (!noSP) {
            alert('No SP tidak tersedia');
            return;
        }

        window.open(
            "{{ url('/lab/print-sp') }}/" + encodeURIComponent(noSP),
            "previewSP",
            "height=800,width=1000,resizable=yes,scrollbars=yes"
        );
    }

    $(document).on('change', '#ketCancel', function() {

        const ketCancel = $(this).val();
        const noSP = @js($sp->NO ?? '');

        if (!noSP) {
            Swal.fire(
                'Gagal',
                'No SP tidak tersedia.',
                'error'
            );
            return;
        }

        $.ajax({
            url: "{{ route('sp.update.ketcancel') }}",
            type: "POST",

            data: {
                _token: "{{ csrf_token() }}",
                no: noSP,
                ketCancel: ketCancel
            },

            success: function(res) {

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message,
                    timer: 1200,
                    showConfirmButton: false
                });

            },

            error: function(xhr) {

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: xhr.responseJSON?.message ??
                        'Gagal memperbarui status.'
                });

            }
        });

    });
</script>
