@if ( \Auth::user()->can( 'phone' ) )
    <!-- BEGIN PHONE AUTH -->
    @if(request('test'))
    @if(\Auth::user()->openPhoneSession)
    <div class="margin-right-10" id="number-of-calls">
    <span class="badge badge-danger bold">0</span>&nbsp;&nbsp;
            <i class="fa fa-phone"></i>

        <div id="popup-calls">
            <a href="{{ route( 'profile.phone' ) }}" class="btn btn-xs btn-danger" id="phone-state">
                Разлогинить телефон
                <span class="bold" id="call-phone"></span>
            </a>
            <div id="inner-popup-calls"></div>
        </div>

    </div>
    @else
        <div class="btn-phone btn-group margin-right-10">
            <a href="{{ route( 'profile.phone' ) }}" class="btn btn-xs btn-danger" id="phone-state">
                <i class="fa fa-phone"></i>
                <span class="bold" id="call-phone"></span>
            </a>
        </div>
    @endif
    @else
    <div class="btn-phone btn-group margin-right-10">
        <a href="{{ route( 'profile.phone' ) }}" class="btn btn-xs btn-{{ \Auth::user()->openPhoneSession ? 'success' : 'danger' }}" id="phone-state">
            <i class="fa fa-phone"></i>
            <span class="bold" id="call-phone"></span>
        </a>
    </div>
    @endif
    <!-- END PHONE AUTH -->
@endif
