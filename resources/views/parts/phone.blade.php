@if ( \Auth::user()->can( 'phone' ) )
    <!-- BEGIN PHONE AUTH -->
    @if(\Auth::user()->openPhoneSession)
        <div class="margin-right-10" id="number-of-calls">
            <div id="number-of-calls-button">
                <b id="number-of-calls-badge">0</b>
                <i class="fa fa-phone"></i>
            </div>
            <div id="popup-calls">
                <div class="al-logout-phone">
                    <a href="{{ route( 'profile.phone' ) }}" class="btn btn-xs btn-danger" id="phone-state">
                        Разлогинить телефон
                        <span class="bold" id="call-phone"></span>
                    </a>
                </div>
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
    <!-- END PHONE AUTH -->
@endif
