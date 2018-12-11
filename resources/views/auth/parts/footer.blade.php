    <div class="copyright">
        {{ date( 'Y' ) }} &copy; {{ \Config::get( 'app.name' ) }} {{ config( 'app.version' ) }}
    </div>
    @include( 'auth.parts.js' )
</body>
</html>