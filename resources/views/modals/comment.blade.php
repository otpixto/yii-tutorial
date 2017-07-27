{!! Form::open( [ 'url' => route( 'comments.store' ), 'files' => isset( $with_file ) && $with_file ] ) !!}
{!! Form::hidden( 'model_id', $model_id ) !!}
{!! Form::hidden( 'model_name', $model_name ) !!}
<div class="form-group">
    {!! Form::textarea( 'text', null, [ 'class' => 'form-control', 'required' ] ) !!}
</div>
@if ( isset( $with_file ) && $with_file )
    <div class="form-group">
        {!! Form::file( 'file', [ 'class' => 'form-control', 'placeholder' => 'Выберите файл' ] ) !!}
    </div>
@endif
{!! Form::close() !!}