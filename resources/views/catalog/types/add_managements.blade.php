{!! Form::open( [ 'method' => 'post', 'url' => route( 'types.managements.add' ), 'class' => 'form-horizontal submit-loading' ] ) !!}
{!! Form::hidden( 'type_id', $type->id ) !!}
<div class="form-group">
    <div class="col-md-12">
        {!! Form::select( 'managements[]', $allowedManagements, null, [ 'class' => 'form-control select2', 'id' => 'management-add', 'multiple' ] ) !!}
    </div>
</div>
<div class="form-group">
    <div class="col-md-12">
        <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
            <input name="select_all_managements" id="select-all-managements" type="checkbox" value="1" />
            <span></span>
            Выбрать все
        </label>
    </div>
</div>
{!! Form::close() !!}