<div class="row">
    <div class="col-md-5">
        <img src="{{ $user->getPhoto() }}" class="img-responsive" alt="" />
    </div>
    <div class="col-md-7">
        <h1 class="h3">
            {!! $user->getShortName() !!}
        </h1>
        <div>
            {!! $user->getPhone( true ) !!}
        </div>
        <div class="text-muted small">
            <a href="mailto: {{ $user->email }}">
                {!! $user->email !!}
            </a>
        </div>
        @if ( ( \Auth::user()->admin || \Auth::user()->can( 'messages' ) ) && $user->id != \Auth::user()->id )
            <hr />
            <div>
                <button class="btn btn-info" data-message="{{ $user->id }}" disabled="disabled">
                    Отправить сообщение
                </button>
            </div>
        @endif
    </div>
</div>