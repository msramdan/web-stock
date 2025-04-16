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
@stack('js')
</body>

</html>
