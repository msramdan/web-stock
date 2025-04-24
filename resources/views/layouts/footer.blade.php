<footer>
    <div class="footer clearfix mb-0 text-muted">
        <div class="float-start">
            <p>{{ date('Y') }} &copy; Created by
                <a href="https://tecanusa.com/" target="_blank">Teknologi Cipta Aplikasi Nusantara (TECANUSA)</a>
            </p>
        </div>
    </div>
</footer>
</div>
<script src="{{ asset('assets/jquery/js/jquery.min.js') }}"></script>
<script src="{{ asset('mazer') }}/static/js/components/dark.js"></script>
<script src="{{ asset('mazer') }}/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>
<script src="{{ asset('mazer') }}/compiled/js/app.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#changeCompany').change(function() {
            var selectedValue = $(this).val();
            var csrfToken = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                type: 'POST',
                url: '{{ route('updateSession') }}',
                data: {
                    selectedValue: selectedValue,
                    _token: csrfToken
                },
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Session Perusahaan berhasil diubah!',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat mengubah Session Perusahaan.'
                    });
                    console.error('Error:', error);
                }
            });
        });
    });
</script>

@stack('js')
</body>

</html>
