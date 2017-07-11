<!-- BEGIN HEADER SEARCH BOX -->
{!! Form::open( [ 'url' => route( 'tickets.index' ), 'method' => 'get', 'class' => 'search-form' ] ) !!}
    <div class="input-group">
        {!! Form::text( 'search', null, [ 'class' => 'form-control', 'placeholder' => 'Поиск' ] ) !!}
        <span class="input-group-btn">
            <button type="submit" class="btn submit">
                <i class="icon-magnifier"></i>
            </button>
        </span>
    </div>
{!! Form::close() !!}
<!-- END HEADER SEARCH BOX -->