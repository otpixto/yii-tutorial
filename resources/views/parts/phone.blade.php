@can( 'phone' )
    <!-- BEGIN PHONE AUTH -->
    <div class="btn-phone btn-group margin-right-10">
        <a href="{{ route( 'profile.phone' ) }}" class="btn btn-sm btn-{{ \Auth::user()->phoneSession ? 'success' : 'danger' }}" id="phone">
            <i class="fa fa-phone"></i>
        </a>
    </div>
    <!-- END PHONE AUTH -->
@endcan