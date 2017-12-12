@if ( \Auth::user()->can( 'catalog.managements.edit' ) )
    {!! Form::open( [ 'method' => 'post', 'url' => route( 'managements.addresses.add' ), 'class' => 'form-horizontal submit-loading' ] ) !!}
    {!! Form::hidden( 'management_id', $management->id ) !!}
    <div class="form-group">
        <div class="col-md-12">
            {!! Form::select( 'addresses[]', $allowedAddresses, null, [ 'class' => 'form-control select2', 'id' => 'addresses-add', 'multiple' ] ) !!}
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-12">
            <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                <input name="select_all_addresses" id="select-all-addresses" type="checkbox" value="1" />
                <span></span>
                Выбрать все
            </label>
        </div>
    </div>
    {!! Form::close() !!}
@else
    @include( 'parts.error', [ 'error' => 'Доступ запрещен' ] )
@endif