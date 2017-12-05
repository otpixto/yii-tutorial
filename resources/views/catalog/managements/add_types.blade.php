{!! Form::open( [ 'method' => 'post', 'url' => route( 'managements.types.add' ), 'class' => 'form-horizontal submit-loading' ] ) !!}
{!! Form::hidden( 'management_id', $management->id ) !!}
<div class="form-group">
    <div class="col-md-12">
        {!! Form::select( 'types[]', $allowedTypes, null, [ 'class' => 'form-control select2', 'id' => 'types-add', 'multiple' ] ) !!}
    </div>
</div>
<div class="form-group">
    <div class="col-md-12">
        <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
            <input name="select_all_types" id="select-all-types" type="checkbox" value="1" />
            <span></span>
            Выбрать все
        </label>
    </div>
</div>
{!! Form::close() !!}