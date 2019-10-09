@if ( \Auth::user()->can( 'phone' ) )
    <!-- BEGIN PHONE AUTH -->
    @if(\Auth::user()->openPhoneSession && request('test'))
    <div class="margin-right-10" id="number-of-calls">
    <span class="badge badge-danger bold">0</span>&nbsp;&nbsp;
            <i class="fa fa-phone"></i>
    </div>
    <div id="popup-calls">
    </div>
    @endif
    <div class="btn-phone btn-group margin-right-10">
        <a href="{{ route( 'profile.phone' ) }}" class="btn btn-xs btn-{{ \Auth::user()->openPhoneSession ? 'success' : 'danger' }}" id="phone-state">
            <i class="fa fa-phone"></i>
            <span class="bold" id="call-phone"></span>
        </a>
    </div>
    <!-- END PHONE AUTH -->
@endif
